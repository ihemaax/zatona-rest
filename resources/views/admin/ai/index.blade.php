@php
    $isEmbeddedAi = request()->query('embed') == 1;
    $pageTitle = 'المساعد الذكي';
    $pageSubtitle = 'اسأل عن النظام أو اطلب مساعدة عامة بطريقة مباشرة وسريعة';
@endphp

@extends($isEmbeddedAi ? 'layouts.blank' : 'layouts.admin')

@section('content')
<style>
    @if($isEmbeddedAi)
    :root{
        --cgpt-bg:#ffffff;
        --cgpt-surface:#ffffff;
        --cgpt-surface-soft:#f7f7f8;
        --cgpt-border:#e5e7eb;
        --cgpt-text:#111827;
        --cgpt-text-soft:#6b7280;
        --cgpt-user:#f3f4f6;
        --cgpt-assistant:#ffffff;
        --cgpt-primary:#10a37f;
        --cgpt-primary-dark:#0d8b6c;
        --cgpt-shadow:0 8px 28px rgba(17,24,39,.06);
        --font:'Cairo', Tahoma, Arial, sans-serif;
    }

    html, body{
        margin:0;
        padding:0;
        width:100%;
        height:100%;
        background:var(--cgpt-bg);
        font-family:var(--font);
        color:var(--cgpt-text);
        overflow:hidden;
    }

    body{
        display:flex;
        min-height:100%;
    }
    @else
    :root{
        --cgpt-bg:#f8fafc;
        --cgpt-surface:#ffffff;
        --cgpt-surface-soft:#f8fafc;
        --cgpt-border:#e5e7eb;
        --cgpt-text:#111827;
        --cgpt-text-soft:#6b7280;
        --cgpt-user:#f3f4f6;
        --cgpt-assistant:#ffffff;
        --cgpt-primary:#10a37f;
        --cgpt-primary-dark:#0d8b6c;
        --cgpt-shadow:0 10px 30px rgba(17,24,39,.05);
        --font:'Cairo', Tahoma, Arial, sans-serif;
    }
    @endif

    .cgpt-shell{
        width:100%;
        max-width:1100px;
        margin:0 auto;
        @if($isEmbeddedAi)
        height:100%;
        display:flex;
        flex-direction:column;
        @endif
    }

    .cgpt-card{
        background:var(--cgpt-surface);
        border:1px solid var(--cgpt-border);
        border-radius:{{ $isEmbeddedAi ? '0' : '28px' }};
        box-shadow:{{ $isEmbeddedAi ? 'none' : 'var(--cgpt-shadow)' }};
        overflow:hidden;
        display:flex;
        flex-direction:column;
        @if($isEmbeddedAi)
        flex:1;
        height:100%;
        min-height:0;
        @endif
    }

    .cgpt-header{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:14px;
        padding:16px 20px;
        background:rgba(255,255,255,.92);
        border-bottom:1px solid var(--cgpt-border);
        backdrop-filter:blur(10px);
        -webkit-backdrop-filter:blur(10px);
        flex-shrink:0;
    }

    .cgpt-header-info{
        min-width:0;
    }

    .cgpt-title{
        margin:0;
        font-size:1.02rem;
        font-weight:900;
        color:var(--cgpt-text);
        line-height:1.2;
    }

    .cgpt-subtitle{
        margin:4px 0 0;
        color:var(--cgpt-text-soft);
        font-size:.8rem;
        font-weight:700;
        line-height:1.6;
    }

    .cgpt-header-badge{
        flex-shrink:0;
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:8px 12px;
        border-radius:999px;
        background:#ecfdf5;
        color:#047857;
        border:1px solid #c7f0de;
        font-size:.75rem;
        font-weight:900;
        white-space:nowrap;
    }

    .cgpt-header-badge-dot{
        width:8px;
        height:8px;
        border-radius:50%;
        background:#10b981;
        box-shadow:0 0 0 4px rgba(16,185,129,.12);
    }

    .cgpt-quickbar{
        padding:12px 20px;
        border-bottom:1px solid var(--cgpt-border);
        background:var(--cgpt-surface);
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        flex-shrink:0;
    }

    .cgpt-chip{
        border:1px solid var(--cgpt-border);
        background:var(--cgpt-surface-soft);
        color:#374151;
        border-radius:999px;
        padding:9px 14px;
        font-size:.8rem;
        font-weight:800;
        font-family:var(--font);
        cursor:pointer;
        transition:.18s ease;
    }

    .cgpt-chip:hover{
        background:#eef2f7;
        border-color:#d8dee8;
        color:#111827;
    }

    .cgpt-body{
        flex:1 1 auto;
        min-height:0;
        overflow:auto;
        background:
            linear-gradient(180deg, #ffffff 0%, #fbfbfc 100%);
        scroll-behavior:smooth;
    }

    .cgpt-thread{
        width:100%;
    }

    .cgpt-empty{
        max-width:760px;
        margin:0 auto;
        padding:72px 20px;
        text-align:center;
    }

    .cgpt-empty-logo{
        width:64px;
        height:64px;
        border-radius:20px;
        margin:0 auto 18px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:linear-gradient(135deg, #10a37f 0%, #0d8b6c 100%);
        color:#fff;
        box-shadow:0 18px 30px rgba(16,163,127,.18);
    }

    .cgpt-empty-logo svg{
        width:28px;
        height:28px;
        stroke:currentColor;
    }

    .cgpt-empty-title{
        margin:0 0 8px;
        font-size:1.28rem;
        font-weight:900;
        color:var(--cgpt-text);
        letter-spacing:-.02em;
    }

    .cgpt-empty-text{
        margin:0;
        font-size:.92rem;
        color:var(--cgpt-text-soft);
        font-weight:700;
        line-height:1.9;
    }

    .cgpt-message-row{
        border-bottom:1px solid #f0f2f5;
    }

    .cgpt-message-row.assistant{
        background:var(--cgpt-assistant);
    }

    .cgpt-message-row.user{
        background:#fafafa;
    }

    .cgpt-message-wrap{
        max-width:900px;
        margin:0 auto;
        padding:22px 20px;
        display:flex;
        gap:14px;
        align-items:flex-start;
    }

    .cgpt-avatar{
        width:34px;
        height:34px;
        border-radius:10px;
        flex-shrink:0;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:.82rem;
        font-weight:900;
        box-shadow:inset 0 0 0 1px rgba(255,255,255,.12);
    }

    .cgpt-avatar.assistant{
        background:linear-gradient(135deg, #10a37f 0%, #0d8b6c 100%);
        color:#fff;
    }

    .cgpt-avatar.user{
        background:#111827;
        color:#fff;
    }

    .cgpt-message-main{
        min-width:0;
        flex:1;
    }

    .cgpt-message-head{
        display:flex;
        align-items:center;
        gap:8px;
        margin-bottom:8px;
        flex-wrap:wrap;
    }

    .cgpt-message-author{
        font-size:.84rem;
        font-weight:900;
        color:var(--cgpt-text);
    }

    .cgpt-message-time{
        font-size:.72rem;
        font-weight:800;
        color:#9ca3af;
    }

    .cgpt-message-text{
        color:#1f2937;
        font-size:.93rem;
        font-weight:700;
        line-height:1.95;
        white-space:pre-wrap;
        word-break:break-word;
    }

    .cgpt-message-text p:last-child{
        margin-bottom:0;
    }

    .cgpt-typing-box{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 0;
    }

    .cgpt-typing-box span{
        width:8px;
        height:8px;
        border-radius:50%;
        background:#9ca3af;
        display:inline-block;
        animation:cgptTyping 1.2s infinite ease-in-out;
    }

    .cgpt-typing-box span:nth-child(2){ animation-delay:.15s; }
    .cgpt-typing-box span:nth-child(3){ animation-delay:.30s; }

    @keyframes cgptTyping{
        0%,80%,100%{ transform:scale(.7); opacity:.45; }
        40%{ transform:scale(1); opacity:1; }
    }

    .cgpt-footer{
        background:var(--cgpt-surface);
        border-top:1px solid var(--cgpt-border);
        padding:16px 18px 18px;
        flex-shrink:0;
    }

    .cgpt-composer-shell{
        max-width:900px;
        margin:0 auto;
    }

    .cgpt-composer{
        background:#ffffff;
        border:1px solid #d9dee7;
        border-radius:26px;
        box-shadow:0 10px 24px rgba(17,24,39,.05);
        padding:10px;
    }

    .cgpt-composer-inner{
        display:flex;
        align-items:flex-end;
        gap:10px;
    }

    .cgpt-textarea{
        flex:1;
        border:none;
        background:transparent;
        resize:none;
        min-height:52px;
        max-height:180px;
        padding:10px 12px;
        font-size:.95rem;
        line-height:1.8;
        font-family:var(--font);
        color:#111827;
        overflow:auto;
    }

    .cgpt-textarea:focus{
        outline:none;
    }

    .cgpt-textarea::placeholder{
        color:#9ca3af;
        font-weight:700;
    }

    .cgpt-send{
        width:48px;
        height:48px;
        border:none;
        border-radius:16px;
        background:linear-gradient(135deg, var(--cgpt-primary) 0%, var(--cgpt-primary-dark) 100%);
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;
        cursor:pointer;
        box-shadow:0 14px 22px rgba(16,163,127,.18);
        transition:.18s ease;
    }

    .cgpt-send:hover{
        transform:translateY(-1px);
    }

    .cgpt-send:disabled{
        opacity:.6;
        cursor:not-allowed;
        transform:none;
    }

    .cgpt-send svg{
        width:20px;
        height:20px;
        stroke:currentColor;
    }

    .cgpt-footer-note{
        max-width:900px;
        margin:10px auto 0;
        display:flex;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
        color:#9ca3af;
        font-size:.75rem;
        font-weight:800;
        padding:0 4px;
    }

    .cgpt-clear-wrap{
        max-width:900px;
        margin:0 auto 10px;
        display:flex;
        justify-content:flex-end;
    }

    .cgpt-clear-btn{
        border:none;
        background:transparent;
        color:#6b7280;
        font-size:.78rem;
        font-weight:900;
        cursor:pointer;
        padding:6px 8px;
        border-radius:10px;
        transition:.18s ease;
        font-family:var(--font);
    }

    .cgpt-clear-btn:hover{
        background:#f3f4f6;
        color:#111827;
    }

    @media (max-width: 767.98px){
        .cgpt-card{
            border-radius:{{ $isEmbeddedAi ? '0' : '22px' }};
        }

        .cgpt-header{
            padding:14px;
        }

        .cgpt-subtitle,
        .cgpt-header-badge{
            display:none;
        }

        .cgpt-quickbar{
            padding:10px 14px;
            overflow-x:auto;
            flex-wrap:nowrap;
            scrollbar-width:none;
        }

        .cgpt-quickbar::-webkit-scrollbar{
            display:none;
        }

        .cgpt-chip{
            white-space:nowrap;
        }

        .cgpt-message-wrap{
            padding:18px 14px;
            gap:12px;
        }

        .cgpt-avatar{
            width:30px;
            height:30px;
            border-radius:9px;
            font-size:.75rem;
        }

        .cgpt-message-text{
            font-size:.89rem;
            line-height:1.9;
        }

        .cgpt-footer{
            padding:12px;
        }

        .cgpt-composer{
            border-radius:22px;
            padding:8px;
        }

        .cgpt-composer-inner{
            flex-direction:column;
            align-items:stretch;
        }

        .cgpt-send{
            width:100%;
            height:50px;
            border-radius:14px;
        }

        .cgpt-footer-note{
            font-size:.72rem;
        }

        .cgpt-empty{
            padding:56px 18px;
        }

        .cgpt-empty-title{
            font-size:1.08rem;
        }

        .cgpt-empty-text{
            font-size:.86rem;
        }
    }
</style>

<div class="cgpt-shell">
    <div class="cgpt-card">
        <div class="cgpt-header">
            <div class="cgpt-header-info">
                <h2 class="cgpt-title">المساعد الذكي</h2>
                <p class="cgpt-subtitle">واجهة محادثة سريعة وواضحة لمساعدة فريق التشغيل داخل لوحة الإدارة.</p>
            </div>

            <div class="cgpt-header-badge">
                <span class="cgpt-header-badge-dot"></span>
                جاهز الآن
            </div>
        </div>

        <div class="cgpt-quickbar">
            <button type="button" class="cgpt-chip quick-question" data-question="قولي ملخص سريع عن السيستم دلوقتي">ملخص السيستم</button>
            <button type="button" class="cgpt-chip quick-question" data-question="كم طلب جديد النهارده؟">طلبات جديدة</button>
            <button type="button" class="cgpt-chip quick-question" data-question="مبيعات النهارده كام؟">مبيعات اليوم</button>
            <button type="button" class="cgpt-chip quick-question" data-question="إزاي أزود مبيعات المطعم؟">زيادة المبيعات</button>
            <button type="button" class="cgpt-chip quick-question" data-question="اكتبلي رسالة تسويقية لعرض جديد">رسالة تسويقية</button>
        </div>

        <div class="cgpt-body" id="aiChatBody">
            <div class="cgpt-thread" id="cgptThread">
                <div class="cgpt-empty" id="aiEmptyState">
                    <div class="cgpt-empty-logo">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                            <path d="M12 3l1.9 3.86L18 8.75l-2.95 2.88.7 4.07L12 13.95 8.25 15.7l.7-4.07L6 8.75l4.1-1.89L12 3z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="cgpt-empty-title">كيف أقدر أساعدك اليوم؟</h3>
                    <p class="cgpt-empty-text">اسأل عن الطلبات، المبيعات، التشغيل، أو اطلب صياغة محتوى ورسائل تسويقية للمطعم.</p>
                </div>
            </div>
        </div>

        <div class="cgpt-footer">
            <div class="cgpt-clear-wrap">
                <button type="button" class="cgpt-clear-btn" id="clearChatBtn">مسح المحادثة</button>
            </div>

            <div class="cgpt-composer-shell">
                <div class="cgpt-composer">
                    <div class="cgpt-composer-inner">
                        <textarea
                            id="aiQuestion"
                            class="cgpt-textarea"
                            placeholder="اسأل المساعد أي شيء..."
                            rows="1"
                        ></textarea>

                        <button id="askAiBtn" class="cgpt-send" type="button" aria-label="إرسال">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2">
                                <path d="M5 12h12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M13 6l6 6-6 6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="cgpt-footer-note">
                <span>Enter للإرسال</span>
                <span>Shift + Enter لسطر جديد</span>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', function () {
    const chatBody = document.getElementById('aiChatBody');
    const thread = document.getElementById('cgptThread');
    const emptyState = document.getElementById('aiEmptyState');
    const textarea = document.getElementById('aiQuestion');
    const sendBtn = document.getElementById('askAiBtn');
    const quickButtons = document.querySelectorAll('.quick-question');
    const clearChatBtn = document.getElementById('clearChatBtn');

    const CHAT_STORAGE_KEY = 'admin_ai_chat_history_v1';
    let isSending = false;

    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function getNowTime() {
        return new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function autoResizeTextarea() {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 180) + 'px';
    }

    function scrollToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function getStoredMessages() {
        try {
            return JSON.parse(localStorage.getItem(CHAT_STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    function storeMessages(messages) {
        localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(messages));
    }

    function pushMessageToStorage(type, text, metaText) {
        const current = getStoredMessages();
        current.push({ type, text, metaText });

        if (current.length > 100) {
            current.splice(0, current.length - 100);
        }

        storeMessages(current);
    }

    function buildMessageRow(type, text, metaText = '') {
        const isUser = type === 'user';
        const row = document.createElement('div');
        row.className = `cgpt-message-row ${isUser ? 'user' : 'assistant'}`;

        row.innerHTML = `
            <div class="cgpt-message-wrap">
                <div class="cgpt-avatar ${isUser ? 'user' : 'assistant'}">
                    ${isUser ? 'أنت' : 'AI'}
                </div>

                <div class="cgpt-message-main">
                    <div class="cgpt-message-head">
                        <span class="cgpt-message-author">${isUser ? 'أنت' : 'المساعد'}</span>
                        <span class="cgpt-message-time">${escapeHtml(metaText)}</span>
                    </div>

                    <div class="cgpt-message-text">${escapeHtml(text)}</div>
                </div>
            </div>
        `;

        return row;
    }

    function appendMessage(type, text, metaText = '', save = true) {
        if (emptyState) {
            emptyState.style.display = 'none';
        }

        const row = buildMessageRow(type, text, metaText);
        thread.appendChild(row);
        scrollToBottom();

        if (save) {
            pushMessageToStorage(type, text, metaText);
        }

        return row;
    }

    function restoreStoredMessages() {
        const messages = getStoredMessages();
        if (!messages.length) return;

        if (emptyState) {
            emptyState.style.display = 'none';
        }

        messages.forEach(item => {
            appendMessage(item.type, item.text, item.metaText || '', false);
        });

        scrollToBottom();
    }

    function appendTypingMessage() {
        if (emptyState) {
            emptyState.style.display = 'none';
        }

        const row = document.createElement('div');
        row.className = 'cgpt-message-row assistant';
        row.id = 'aiTypingMessage';
        row.innerHTML = `
            <div class="cgpt-message-wrap">
                <div class="cgpt-avatar assistant">AI</div>

                <div class="cgpt-message-main">
                    <div class="cgpt-message-head">
                        <span class="cgpt-message-author">المساعد</span>
                        <span class="cgpt-message-time">جاري الكتابة...</span>
                    </div>

                    <div class="cgpt-message-text">
                        <span class="cgpt-typing-box">
                            <span></span><span></span><span></span>
                        </span>
                    </div>
                </div>
            </div>
        `;

        thread.appendChild(row);
        scrollToBottom();
        return row;
    }

    function removeTypingMessage() {
        const typing = document.getElementById('aiTypingMessage');
        if (typing) typing.remove();
    }

    async function sendQuestion(prefilledQuestion = null) {
        if (isSending) return;

        const question = (prefilledQuestion ?? textarea.value).trim();
        if (!question) return;

        isSending = true;
        sendBtn.disabled = true;

        const userMeta = getNowTime();
        appendMessage('user', question, userMeta, true);

        textarea.value = '';
        autoResizeTextarea();

        appendTypingMessage();

        try {
            const response = await fetch("{{ route('admin.ai.ask') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ question })
            });

            let data = {};
            try {
                data = await response.json();
            } catch (e) {}

            removeTypingMessage();

            const assistantMeta = getNowTime();

            if (!response.ok) {
                const msg = data.message || 'حدث خطأ أثناء تجهيز الرد.';
                appendMessage('assistant', msg, assistantMeta, true);
            } else {
                appendMessage('assistant', data.answer ?? 'لا يوجد رد واضح حالياً.', assistantMeta, true);
            }

            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    source: 'admin-ai-assistant',
                    type: 'assistant-replied'
                }, '*');
            }
        } catch (error) {
            removeTypingMessage();

            const assistantMeta = getNowTime();
            appendMessage('assistant', 'حدثت مشكلة في الاتصال بالمساعد الذكي.', assistantMeta, true);

            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    source: 'admin-ai-assistant',
                    type: 'assistant-replied'
                }, '*');
            }
        } finally {
            isSending = false;
            sendBtn.disabled = false;
            textarea.focus();
        }
    }

    sendBtn.addEventListener('click', function () {
        sendQuestion();
    });

    textarea.addEventListener('input', autoResizeTextarea);

    textarea.addEventListener('focus', function () {
        if (window.parent && window.parent !== window) {
            window.parent.postMessage({
                source: 'admin-ai-assistant',
                type: 'assistant-focus'
            }, '*');
        }
    });

    textarea.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendQuestion();
        }
    });

    quickButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const q = this.getAttribute('data-question') || '';
            textarea.value = q;
            autoResizeTextarea();
            textarea.focus();
        });
    });

    clearChatBtn?.addEventListener('click', function () {
        localStorage.removeItem(CHAT_STORAGE_KEY);
        thread.innerHTML = '';

        if (emptyState) {
            emptyState.style.display = '';
            thread.appendChild(emptyState);
        }

        textarea.value = '';
        autoResizeTextarea();
        textarea.focus();
    });

    restoreStoredMessages();
    autoResizeTextarea();
});
</script>
@endsection