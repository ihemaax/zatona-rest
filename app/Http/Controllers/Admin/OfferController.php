<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::query()
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderByDesc('id')
            ->paginate(12);

        return view('admin.offers.index', compact('offers'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateOffer($request);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('offers', 'public');
        }

        Offer::create($validated);

        return redirect()->route('admin.offers.index')->with('success', 'تم إضافة العرض بنجاح.');
    }

    public function edit(Offer $offer)
    {
        $offers = Offer::query()
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderByDesc('id')
            ->paginate(12);

        return view('admin.offers.index', compact('offers', 'offer'));
    }

    public function update(Request $request, Offer $offer)
    {
        $validated = $this->validateOffer($request);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($offer->getRawOriginal('image')) {
                Storage::disk('public')->delete($offer->getRawOriginal('image'));
            }
            $validated['image'] = $request->file('image')->store('offers', 'public');
        }

        $offer->update($validated);

        return redirect()->route('admin.offers.index')->with('success', 'تم تحديث العرض بنجاح.');
    }

    public function destroy(Offer $offer)
    {
        if ($offer->getRawOriginal('image')) {
            Storage::disk('public')->delete($offer->getRawOriginal('image'));
        }

        $offer->delete();

        return redirect()->route('admin.offers.index')->with('success', 'تم حذف العرض بنجاح.');
    }

    protected function validateOffer(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'max:4096'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'new_price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
    }
}
