@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <h2 class="mb-3">جارٍ تحويلك إلى صفحة الدفع</h2>
    <p class="text-muted mb-4">لو لم يتم التحويل تلقائيًا، اضغط الزر التالي.</p>
    <a class="btn btn-primary" href="{{ $redirectUrl }}">الانتقال إلى صفحة الدفع</a>
</div>
<script nonce="{{ $cspNonce }}">
    window.location.replace(@json($redirectUrl));
</script>
@endsection
