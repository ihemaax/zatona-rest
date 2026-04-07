<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AiAssistantController extends Controller
{
    protected string $sessionKey = 'admin_ai_chat_history';

    protected function ensureAiAccess(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && $user->canAccessDashboard(),
            403,
            'المساعد الذكي متاح للمدير والإدمن والسوبر أدمن فقط.'
        );
    }

    public function index()
    {
        $this->ensureAiAccess();

        return view('admin.ai.index');
    }

    public function ask(Request $request, GeminiService $gemini)
    {
        $this->ensureAiAccess();

        $request->validate([
            'question' => ['required', 'string', 'max:3000'],
        ]);

        $user = auth()->user();
        $question = trim($request->input('question'));
        $normalizedQuestion = $this->normalizeArabicText(mb_strtolower($question));

        $history = session($this->sessionKey, []);
        $trimmedHistory = $this->getTrimmedHistory($history);

        $analysis = $this->analyzeQuestionWithHistory($normalizedQuestion, $trimmedHistory);
        $isSystemQuestion = $analysis['is_system_question'];

        $historyText = $this->buildHistoryText($trimmedHistory);

        if ($isSystemQuestion) {
            $systemContext = $this->buildSmartSystemContext(
                question: $question,
                normalizedQuestion: $normalizedQuestion,
                user: $user,
                analysis: $analysis
            );

            $prompt = "
أنت مساعد ذكي شغال جوه نظام مطعم.

طريقة الكلام:
- اتكلم بالمصري العادي جدًا
- خليك طبيعي وودي
- مش رسمي زيادة
- اختصر من غير ما تبقى ناقص
- افهم إن المستخدم ممكن يكون بيكمل على آخر كلام
- اربط السؤال الحالي بسياق المحادثة لو واضح إنه continuation

قواعد مهمة:
- لو السؤال متعلق بالسيستم، اعتمد فقط على بيانات السيستم اللي تحت
- ما تخترعش أرقام أو أحداث أو أسماء
- لو المستخدم بيسأل على نفس الموضوع اللي في آخر الرسائل، كمّل عليه طبيعي
- لو في معلومة ناقصة قولها بصراحة بشكل بسيط

سياق المحادثة الأخيرة:
{$historyText}

سؤال المستخدم الحالي:
{$question}

تحليل داخلي للسؤال:
- نوع السؤال الأساسي: {$analysis['intent']}
- هل فيه رقم طلب محدد؟ " . ($analysis['order_number'] ?: 'لا') . "
- هل فيه فرع محدد؟ " . ($analysis['branch_name'] ?: 'لا') . "
- هل فيه منتج محدد؟ " . ($analysis['product_name'] ?: 'لا') . "

بيانات السيستم:
{$systemContext}

جاوب بالمصري العادي.
";
        } else {
            $prompt = "
أنت مساعد ذكي داخل نظام مطعم.

طريقة الكلام:
- اتكلم بالمصري العادي جدًا
- خليك طبيعي وودي
- افهم سياق آخر الرسائل لو المستخدم بيكمل
- رد بشكل واضح ومفيد

تعليمات:
- لو السؤال عام جاوب عادي
- لو المستخدم بيكمل على سؤال قبل كده، اربط الإجابة بالسياق
- لو السؤال محتاج بيانات سيستم ومش موجودة، قوله ده ببساطة

سياق المحادثة الأخيرة:
{$historyText}

سؤال المستخدم الحالي:
{$question}

جاوب بالمصري العادي.
";
        }

        $answer = $gemini->ask($prompt);

        $history[] = [
            'role' => 'user',
            'text' => $question,
        ];

        $history[] = [
            'role' => 'assistant',
            'text' => $answer,
        ];

        session([$this->sessionKey => $this->limitHistory($history)]);

        return response()->json([
            'answer' => $answer,
            'mode' => $isSystemQuestion ? 'system' : 'general',
            'intent' => $analysis['intent'],
        ]);
    }

    public function clear()
    {
        $this->ensureAiAccess();

        session()->forget($this->sessionKey);

        return response()->json([
            'success' => true,
        ]);
    }

    protected function getTrimmedHistory(array $history): array
    {
        return array_slice($history, -8);
    }

    protected function limitHistory(array $history): array
    {
        return array_slice($history, -20);
    }

    protected function buildHistoryText(array $history): string
    {
        if (empty($history)) {
            return 'لا يوجد سياق سابق.';
        }

        return collect($history)->map(function ($item) {
            $role = $item['role'] === 'user' ? 'المستخدم' : 'المساعد';
            return "{$role}: {$item['text']}";
        })->implode("\n");
    }

    protected function analyzeQuestionWithHistory(string $question, array $history): array
    {
        $analysis = $this->analyzeQuestion($question);

        if ($analysis['is_system_question']) {
            return $analysis;
        }

        $continuationWords = [
            'طب', 'طيب', 'كمان', 'فيه', 'في', 'بس', 'طب و', 'طب امبارح', 'طب النهارده', 'قصدك', 'يعني', 'و',
            'then', 'what about', 'and', 'also'
        ];

        $isContinuation = $this->containsAny($question, $continuationWords) || mb_strlen($question) < 25;

        if (!$isContinuation || empty($history)) {
            return $analysis;
        }

        $lastContextText = $this->buildHistoryText($history);
        $normalizedHistory = $this->normalizeArabicText(mb_strtolower($lastContextText));

        if ($this->containsAny($normalizedHistory, [
            'طلب', 'طلبات', 'اوردر', 'order', 'orders',
            'فرع', 'فروع', 'branch',
            'مبيعات', 'sales',
            'منتج', 'products',
            'موظف', 'staff',
            'اعدادات', 'settings',
            'السيستم', 'النظام'
        ])) {
            $analysis['is_system_question'] = true;

            if ($analysis['intent'] === 'general') {
                if ($this->containsAny($normalizedHistory, ['طلب', 'طلبات', 'اوردر', 'order'])) {
                    $analysis['intent'] = 'orders';
                } elseif ($this->containsAny($normalizedHistory, ['مبيعات', 'sales'])) {
                    $analysis['intent'] = 'sales';
                } elseif ($this->containsAny($normalizedHistory, ['فرع', 'فروع', 'branch'])) {
                    $analysis['intent'] = 'branches';
                } elseif ($this->containsAny($normalizedHistory, ['منتج', 'products'])) {
                    $analysis['intent'] = 'products';
                } elseif ($this->containsAny($normalizedHistory, ['موظف', 'staff'])) {
                    $analysis['intent'] = 'staff';
                }
            }

            if (!$analysis['branch_name']) {
                $analysis['branch_name'] = $this->detectBranchNameFromQuestion($normalizedHistory);
            }

            if (!$analysis['product_name']) {
                $analysis['product_name'] = $this->detectProductNameFromQuestion($normalizedHistory);
            }

            if (!$analysis['order_number']) {
                $analysis['order_number'] = $this->extractOrderNumber($normalizedHistory);
            }
        }

        return $analysis;
    }

    protected function analyzeQuestion(string $question): array
    {
        $intent = 'general';

        if ($this->containsAny($question, ['ملخص', 'الوضع ايه', 'احكيلي', 'overview', 'summary', 'تقرير'])) {
            $intent = 'summary';
        }

        if ($this->containsAny($question, ['طلب', 'طلبات', 'اوردر', 'أوردر', 'order', 'orders'])) {
            $intent = 'orders';
        }

        if ($this->containsAny($question, ['مبيعات', 'sales', 'دخل', 'ايراد', 'إيراد'])) {
            $intent = 'sales';
        }

        if ($this->containsAny($question, ['فرع', 'فروع', 'branch', 'branches'])) {
            $intent = 'branches';
        }

        if ($this->containsAny($question, ['منتج', 'منتجات', 'product', 'products', 'صنف', 'اصناف', 'أصناف'])) {
            $intent = 'products';
        }

        if ($this->containsAny($question, ['قسم', 'اقسام', 'أقسام', 'category', 'categories'])) {
            $intent = 'categories';
        }

        if ($this->containsAny($question, ['موظف', 'موظفين', 'staff', 'employee', 'employees', 'صلاحيات', 'permissions'])) {
            $intent = 'staff';
        }

        if ($this->containsAny($question, ['اعدادات', 'إعدادات', 'settings', 'setting', 'المطعم', 'restaurant'])) {
            $intent = 'settings';
        }

        if ($this->containsAny($question, ['امبارح', 'مقارنه', 'مقارنة', 'أحسن من', 'اقل من', 'اكتر من', 'زي امبارح'])) {
            $intent = 'comparison';
        }

        if ($this->containsAny($question, ['متاخر', 'متأخر', 'متأخره', 'متاخره', 'واقف', 'متعلق من زمان'])) {
            $intent = 'delays';
        }

        if ($this->containsAny($question, ['اكتر منتج', 'أكتر منتج', 'top product', 'اعلى منتج', 'أفضل منتج'])) {
            $intent = 'top_product';
        }

        if ($this->containsAny($question, ['اكتر فرع', 'أكتر فرع', 'اعلى فرع', 'أفضل فرع', 'ضغط', 'مضغوط'])) {
            $intent = 'top_branch';
        }

        $orderNumber = $this->extractOrderNumber($question);
        if ($orderNumber) {
            $intent = 'specific_order';
        }

        $branchName = $this->detectBranchNameFromQuestion($question);
        $productName = $this->detectProductNameFromQuestion($question);

        $isSystemQuestion = $this->containsAny($question, [
            'طلب', 'طلبات', 'اوردر', 'أوردر', 'order', 'orders',
            'فرع', 'فروع', 'branch', 'branches',
            'مبيعات', 'sales',
            'منتج', 'منتجات', 'product', 'products',
            'قسم', 'اقسام', 'أقسام', 'category', 'categories',
            'موظف', 'موظفين', 'staff', 'employee', 'employees',
            'dashboard', 'السيستم', 'النظام', 'admin',
            'حالة', 'حاله', 'status',
            'عميل', 'عملاء', 'customer',
            'today', 'النهارده', 'اليوم', 'امبارح',
            'اعدادات', 'إعدادات', 'setting', 'settings',
            'منيو', 'menu', 'qr',
            'delivery', 'pickup', 'pending', 'confirmed', 'preparing',
            'out for delivery', 'delivered', 'cancelled',
            'متاخر', 'متأخر', 'مقارنه', 'مقارنة', 'ضغط'
        ]) || !empty($orderNumber) || !empty($branchName) || !empty($productName);

        return [
            'intent' => $intent,
            'is_system_question' => $isSystemQuestion,
            'order_number' => $orderNumber,
            'branch_name' => $branchName,
            'product_name' => $productName,
        ];
    }

    protected function buildSmartSystemContext(string $question, string $normalizedQuestion, $user, array $analysis): string
    {
        $sections = [];

        $sections[] = $this->buildGeneralSummary($user);
        $sections[] = $this->buildComparisonSection($user);
        $sections[] = $this->buildDelayedOrdersSection($user);
        $sections[] = $this->buildTopInsightsSection($user);

        if ($analysis['order_number']) {
            $sections[] = $this->buildSpecificOrderSection($analysis['order_number'], $user);
        }

        if (in_array($analysis['intent'], ['orders', 'summary', 'sales', 'comparison', 'delays'], true) || $this->needsOrdersData($normalizedQuestion)) {
            $sections[] = $this->buildOrdersSection($user, $analysis['branch_name']);
        }

        if (in_array($analysis['intent'], ['branches', 'summary', 'sales', 'top_branch'], true) || $this->needsBranchesData($normalizedQuestion)) {
            $sections[] = $this->buildBranchesSection($user);
        }

        if (in_array($analysis['intent'], ['products', 'summary', 'top_product'], true) || $this->needsProductsData($normalizedQuestion)) {
            $sections[] = $this->buildProductsSection($analysis['product_name']);
        }

        if (in_array($analysis['intent'], ['categories', 'summary'], true) || $this->needsCategoriesData($normalizedQuestion)) {
            $sections[] = $this->buildCategoriesSection();
        }

        if (
            ($analysis['intent'] === 'staff' || $this->needsStaffData($normalizedQuestion))
            && ($user->hasPermission('manage_staff') || $user->isSuperAdmin())
        ) {
            $sections[] = $this->buildStaffSection($analysis['branch_name']);
        }

        if (
            ($analysis['intent'] === 'settings' || $this->needsSettingsData($normalizedQuestion))
            && ($user->hasPermission('manage_settings') || $user->isSuperAdmin())
        ) {
            $sections[] = $this->buildSettingsSection();
        }

        return implode("\n\n", array_filter($sections));
    }

    protected function baseOrdersQuery($user)
    {
        $query = Order::query();

        if (!$user->isSuperAdmin() && !$user->hasPermission('view_all_branches_orders')) {
            if ($user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    protected function applyScopeToOrderItemsQuery($query, $user)
    {
        if (!$user->isSuperAdmin() && !$user->hasPermission('view_all_branches_orders')) {
            if ($user->branch_id) {
                $query->whereExists(function ($sub) use ($user) {
                    $sub->select(DB::raw(1))
                        ->from('orders')
                        ->whereColumn('orders.id', 'order_items.order_id')
                        ->where('orders.branch_id', $user->branch_id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    protected function buildGeneralSummary($user): string
    {
        $ordersQuery = $this->baseOrdersQuery($user);

        $todayOrders = (clone $ordersQuery)->whereDate('created_at', today())->count();
        $newOrders = (clone $ordersQuery)->where('is_seen_by_admin', false)->count();
        $pendingOrders = (clone $ordersQuery)->where('status', 'pending')->count();
        $confirmedOrders = (clone $ordersQuery)->where('status', 'confirmed')->count();
        $preparingOrders = (clone $ordersQuery)->where('status', 'preparing')->count();
        $outForDeliveryOrders = (clone $ordersQuery)->where('status', 'out_for_delivery')->count();
        $deliveredOrders = (clone $ordersQuery)->where('status', 'delivered')->count();
        $cancelledOrders = (clone $ordersQuery)->where('status', 'cancelled')->count();
        $deliveryOrders = (clone $ordersQuery)->where('order_type', 'delivery')->count();
        $pickupOrders = (clone $ordersQuery)->where('order_type', 'pickup')->count();
        $todaySales = (float) (clone $ordersQuery)->whereDate('created_at', today())->sum('total');
        $totalOrders = (clone $ordersQuery)->count();

        return "
[ملخص عام]
- إجمالي الطلبات في النطاق الحالي: {$totalOrders}
- عدد طلبات اليوم: {$todayOrders}
- الطلبات الجديدة: {$newOrders}
- الطلبات المعلقة: {$pendingOrders}
- الطلبات المؤكدة: {$confirmedOrders}
- الطلبات تحت التحضير: {$preparingOrders}
- الطلبات الخارجة للتوصيل: {$outForDeliveryOrders}
- الطلبات التي تم تسليمها: {$deliveredOrders}
- الطلبات الملغية: {$cancelledOrders}
- طلبات التوصيل: {$deliveryOrders}
- طلبات الاستلام: {$pickupOrders}
- مبيعات اليوم: {$todaySales}
";
    }

    protected function buildComparisonSection($user): string
    {
        $ordersQuery = $this->baseOrdersQuery($user);

        $todayOrders = (clone $ordersQuery)->whereDate('created_at', today())->count();
        $yesterdayOrders = (clone $ordersQuery)->whereDate('created_at', today()->subDay())->count();

        $todaySales = (float) (clone $ordersQuery)->whereDate('created_at', today())->sum('total');
        $yesterdaySales = (float) (clone $ordersQuery)->whereDate('created_at', today()->subDay())->sum('total');

        $ordersDiff = $todayOrders - $yesterdayOrders;
        $salesDiff = $todaySales - $yesterdaySales;

        return "
[مقارنة اليوم بامبارح]
- طلبات النهارده: {$todayOrders}
- طلبات امبارح: {$yesterdayOrders}
- الفرق في الطلبات: {$ordersDiff}

- مبيعات النهارده: {$todaySales}
- مبيعات امبارح: {$yesterdaySales}
- الفرق في المبيعات: {$salesDiff}
";
    }

    protected function buildDelayedOrdersSection($user): string
    {
        $base = $this->baseOrdersQuery($user);

        $delayedOrders = (clone $base)
            ->with('branch')
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'out_for_delivery'])
            ->where('created_at', '<=', now()->subMinutes(30))
            ->latest()
            ->take(8)
            ->get();

        $count = $delayedOrders->count();

        $lines = $delayedOrders->map(function ($order) {
            $minutes = now()->diffInMinutes($order->created_at);
            return "- {$order->order_number} | الحالة: {$order->status} | بقاله تقريبًا {$minutes} دقيقة | الفرع: " . ($order->branch?->name ?? '-');
        })->implode("\n");

        return "
[الطلبات المتأخرة]
- عدد الطلبات المفتوحة أو غير المنتهية وبقالها أكتر من 30 دقيقة: {$count}

[أمثلة على الطلبات المتأخرة]
" . ($lines ?: '- لا توجد طلبات متأخرة واضحة حاليًا');
    }

    protected function buildTopInsightsSection($user): string
    {
        $topBranchText = $this->getTopBranchInsight($user);
        $topProductText = $this->getTopProductInsight($user);

        return "
[أهم Insights]
- أعلى فرع حاليًا: {$topBranchText}
- أكتر منتج ظاهر في الطلبات: {$topProductText}
";
    }

    protected function getTopBranchInsight($user): string
    {
        if ($user->isSuperAdmin() || $user->hasPermission('view_all_branches_orders')) {
            $topBranch = Branch::withCount('orders')
                ->orderByDesc('orders_count')
                ->first();

            if ($topBranch) {
                return "{$topBranch->name} بعدد طلبات {$topBranch->orders_count}";
            }

            return 'مش ظاهر فرع متصدر دلوقتي';
        }

        if ($user->branch_id) {
            $branch = Branch::withCount('orders')->find($user->branch_id);
            if ($branch) {
                return "{$branch->name} بعدد طلبات {$branch->orders_count} في نطاقك الحالي";
            }
        }

        return 'مش ظاهر فرع متصدر دلوقتي';
    }

    protected function getTopProductInsight($user): string
    {
        if (!Schema::hasTable('order_items')) {
            return 'جدول order_items مش موجود';
        }

        $query = DB::table('order_items')
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty');

        $this->applyScopeToOrderItemsQuery($query, $user);

        $topProduct = $query->first();

        if ($topProduct) {
            return "{$topProduct->product_name} بإجمالي كميات {$topProduct->total_qty}";
        }

        return 'مش ظاهر منتج متصدر دلوقتي';
    }

    protected function buildSpecificOrderSection(string $orderNumber, $user): string
    {
        $query = $this->baseOrdersQuery($user)->with(['branch', 'items'])->latest();

        $order = (clone $query)
            ->whereRaw('UPPER(order_number) = ?', [strtoupper($orderNumber)])
            ->first();

        if (!$order) {
            return "
[طلب محدد]
- رقم الطلب المطلوب: {$orderNumber}
- النتيجة: الطلب ده مش ظاهرلي في نطاق الصلاحية الحالي.
";
        }

        $itemsText = collect($order->items)->map(function ($item) {
            $selectedOptions = '';

            if (!empty($item->selected_options) && is_array($item->selected_options)) {
                $parts = [];
                foreach ($item->selected_options as $group => $values) {
                    $parts[] = $group . ': ' . (is_array($values) ? implode('، ', $values) : $values);
                }
                $selectedOptions = implode(' | ', $parts);
            }

            return "- {$item->product_name} | الكمية: {$item->quantity} | السعر: {$item->price} | الإجمالي: {$item->total}" .
                ($selectedOptions ? " | اختيارات: {$selectedOptions}" : '');
        })->implode("\n");

        return "
[تفاصيل طلب محدد]
- رقم الطلب: {$order->order_number}
- العميل: {$order->customer_name}
- الهاتف: {$order->customer_phone}
- الحالة: {$order->status}
- نوع الطلب: {$order->order_type}
- الفرع: " . ($order->branch?->name ?? '-') . "
- الإجمالي الفرعي: {$order->subtotal}
- التوصيل: {$order->delivery_fee}
- الإجمالي النهائي: {$order->total}
- التاريخ: " . ($order->created_at?->format('Y-m-d h:i A') ?? '-') . "
- الملاحظات: " . ($order->notes ?: 'لا توجد') . "

[أصناف الطلب]
" . ($itemsText ?: '- لا توجد أصناف');
    }

    protected function buildOrdersSection($user, ?string $branchName = null): string
    {
        $ordersQuery = $this->baseOrdersQuery($user)->with('branch')->latest();

        if ($branchName) {
            $branch = Branch::whereRaw('LOWER(name) = ?', [mb_strtolower($branchName)])->first();
            if ($branch) {
                $ordersQuery->where('branch_id', $branch->id);
            }
        }

        $latestOrders = (clone $ordersQuery)->take(8)->get();

        $latestOrdersText = $latestOrders->map(function ($order) {
            return "- {$order->order_number} | العميل: {$order->customer_name} | الحالة: {$order->status} | النوع: {$order->order_type} | الفرع: " . ($order->branch?->name ?? '-') . " | الإجمالي: {$order->total}";
        })->implode("\n");

        $base = $this->baseOrdersQuery($user);
        if ($branchName) {
            $branch = Branch::whereRaw('LOWER(name) = ?', [mb_strtolower($branchName)])->first();
            if ($branch) {
                $base->where('branch_id', $branch->id);
            }
        }

        $statusCounts = [
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'confirmed' => (clone $base)->where('status', 'confirmed')->count(),
            'preparing' => (clone $base)->where('status', 'preparing')->count(),
            'out_for_delivery' => (clone $base)->where('status', 'out_for_delivery')->count(),
            'delivered' => (clone $base)->where('status', 'delivered')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
        ];

        return "
[بيانات الطلبات]
- Pending: {$statusCounts['pending']}
- Confirmed: {$statusCounts['confirmed']}
- Preparing: {$statusCounts['preparing']}
- Out for delivery: {$statusCounts['out_for_delivery']}
- Delivered: {$statusCounts['delivered']}
- Cancelled: {$statusCounts['cancelled']}

[آخر الطلبات]
" . ($latestOrdersText ?: '- لا توجد طلبات حديثة');
    }

    protected function buildBranchesSection($user): string
    {
        if ($user->isSuperAdmin() || $user->hasPermission('view_all_branches_orders')) {
            $branches = Branch::withCount('orders')->orderBy('name')->get();
        } elseif ($user->branch_id) {
            $branches = Branch::where('id', $user->branch_id)->withCount('orders')->orderBy('name')->get();
        } else {
            $branches = collect();
        }

        $branchLines = collect($branches)->map(function ($branch) {
            $branchOrders = Order::where('branch_id', $branch->id);
            $todaySales = (float) (clone $branchOrders)->whereDate('created_at', today())->sum('total');
            $pending = (clone $branchOrders)->where('status', 'pending')->count();

            return "- {$branch->name} | عدد الطلبات: {$branch->orders_count} | Pending: {$pending} | مبيعات اليوم: {$todaySales} | الهاتف: " . ($branch->phone ?? '-') . " | العنوان: " . ($branch->address ?? '-');
        })->implode("\n");

        return "
[بيانات الفروع]
" . ($branchLines ?: '- لا توجد بيانات فروع متاحة');
    }

    protected function buildProductsSection(?string $productName = null): string
    {
        $totalProducts = Product::count();
        $availableProducts = Product::where('is_available', 1)->count();
        $unavailableProducts = Product::where('is_available', 0)->count();

        $latestProducts = Product::with('category')->latest()->take(10)->get();

        $latestProductsText = $latestProducts->map(function ($product) {
            return "- {$product->name} | القسم: " . ($product->category?->name ?? '-') . " | السعر: {$product->price} | متاح: " . ($product->is_available ? 'نعم' : 'لا');
        })->implode("\n");

        $section = "
[بيانات المنتجات]
- إجمالي المنتجات: {$totalProducts}
- المنتجات المتاحة: {$availableProducts}
- المنتجات غير المتاحة: {$unavailableProducts}

[آخر المنتجات]
" . ($latestProductsText ?: '- لا توجد منتجات');

        if ($productName) {
            $product = Product::with('category')
                ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($productName) . '%'])
                ->first();

            if ($product) {
                $section .= "

[منتج محدد]
- الاسم: {$product->name}
- القسم: " . ($product->category?->name ?? '-') . "
- السعر: {$product->price}
- الوصف: " . ($product->description ?: 'لا يوجد وصف') . "
- متاح: " . ($product->is_available ? 'نعم' : 'لا');
            }
        }

        return $section;
    }

    protected function buildCategoriesSection(): string
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        $categoriesText = $categories->map(function ($category) {
            return "- {$category->name} | عدد المنتجات: {$category->products_count} | نشط: " . ($category->is_active ? 'نعم' : 'لا');
        })->implode("\n");

        return "
[بيانات الأقسام]
" . ($categoriesText ?: '- لا توجد أقسام');
    }

    protected function buildStaffSection(?string $branchName = null): string
    {
        $staffQuery = User::where('user_type', User::TYPE_STAFF)->with('branch')->latest();

        if ($branchName) {
            $branch = Branch::whereRaw('LOWER(name) = ?', [mb_strtolower($branchName)])->first();
            if ($branch) {
                $staffQuery->where('branch_id', $branch->id);
            }
        }

        $staff = (clone $staffQuery)->take(20)->get();

        $activeStaff = (clone $staffQuery)->where('is_active', 1)->count();
        $inactiveStaff = (clone $staffQuery)->where('is_active', 0)->count();

        $staffText = $staff->map(function ($member) {
            return "- {$member->name} | الدور: {$member->role} | الفرع: " . ($member->branch?->name ?? '-') . " | الحالة: " . ($member->is_active ? 'نشط' : 'موقوف');
        })->implode("\n");

        return "
[بيانات الموظفين]
- عدد الموظفين النشطين: {$activeStaff}
- عدد الموظفين الموقوفين: {$inactiveStaff}

[البيانات المتاحة عن الموظفين]
" . ($staffText ?: '- لا توجد بيانات موظفين');
    }

    protected function buildSettingsSection(): string
    {
        $setting = Setting::first();

        if (!$setting) {
            return "[الإعدادات]\n- لا توجد إعدادات محفوظة";
        }

        return "
[الإعدادات]
- اسم المطعم: " . ($setting->restaurant_name ?? '-') . "
- الهاتف: " . ($setting->restaurant_phone ?? '-') . "
- العنوان: " . ($setting->restaurant_address ?? '-') . "
- رسوم التوصيل: " . ($setting->delivery_fee ?? 0) . "
- حالة المطعم: " . (!empty($setting->is_open) ? 'مفتوح' : 'مغلق');
    }

    protected function extractOrderNumber(string $question): ?string
    {
        if (preg_match('/ord[-\s]?\d+/i', $question, $matches)) {
            return strtoupper(str_replace(' ', '', $matches[0]));
        }

        return null;
    }

    protected function detectBranchNameFromQuestion(string $question): ?string
    {
        $branches = Branch::select('name')->get();

        foreach ($branches as $branch) {
            if (mb_stripos($question, mb_strtolower($branch->name)) !== false) {
                return $branch->name;
            }
        }

        return null;
    }

    protected function detectProductNameFromQuestion(string $question): ?string
    {
        $products = Product::select('name')->take(150)->get();

        foreach ($products as $product) {
            if (mb_stripos($question, mb_strtolower($product->name)) !== false) {
                return $product->name;
            }
        }

        return null;
    }

    protected function needsOrdersData(string $question): bool
    {
        $keywords = ['طلب', 'طلبات', 'اوردر', 'أوردر', 'order', 'orders', 'pending', 'confirmed', 'preparing', 'delivery', 'pickup', 'ord-'];
        return $this->containsAny($question, $keywords);
    }

    protected function needsBranchesData(string $question): bool
    {
        $keywords = ['فرع', 'فروع', 'branch', 'branches', 'ضغط', 'مضغوط'];
        return $this->containsAny($question, $keywords);
    }

    protected function needsProductsData(string $question): bool
    {
        $keywords = ['منتج', 'منتجات', 'product', 'products', 'سعر', 'اسعار', 'أسعار', 'صنف', 'اصناف', 'أصناف'];
        return $this->containsAny($question, $keywords);
    }

    protected function needsCategoriesData(string $question): bool
    {
        $keywords = ['قسم', 'اقسام', 'أقسام', 'category', 'categories'];
        return $this->containsAny($question, $keywords);
    }

    protected function needsStaffData(string $question): bool
    {
        $keywords = ['موظف', 'موظفين', 'staff', 'employee', 'employees', 'صلاحيات', 'permissions'];
        return $this->containsAny($question, $keywords);
    }

    protected function needsSettingsData(string $question): bool
    {
        $keywords = ['اعدادات', 'إعدادات', 'settings', 'setting', 'المطعم', 'restaurant', 'رسوم التوصيل', 'delivery fee'];
        return $this->containsAny($question, $keywords);
    }

    protected function containsAny(string $question, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (mb_stripos($question, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeArabicText(string $text): string
    {
        $search = ['أ', 'إ', 'آ', 'ة', 'ى'];
        $replace = ['ا', 'ا', 'ا', 'ه', 'ي'];

        $text = str_replace($search, $replace, $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
