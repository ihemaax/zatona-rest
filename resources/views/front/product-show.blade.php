@extends('layouts.app')

@section('content')
<style>
    .elite-product-page{
        max-width: 1120px;
        margin: 0 auto;
        padding-bottom: 90px;
    }

    .elite-product-wrap{
        display: grid;
        grid-template-columns: minmax(0, 1.02fr) minmax(0, .98fr);
        gap: 22px;
        align-items: start;
    }

    .elite-product-media-card,
    .elite-product-info-card,
    .elite-option-card,
    .elite-summary-card,
    .elite-success-card{
        background: var(--fb-card);
        border: 1px solid var(--fb-border);
        border-radius: 26px;
        box-shadow: var(--fb-shadow);
        overflow: hidden;
    }

    .elite-product-media-card{
        padding: 14px;
    }

    .elite-product-image{
        width: 100%;
        min-height: 520px;
        max-height: 700px;
        object-fit: cover;
        display: block;
        border-radius: 22px;
        background: #f3efe8;
    }

    .elite-product-info-card{
        padding: 22px;
    }

    .elite-product-category{
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
        border-radius: 999px;
        background: #eef2e8;
        color: var(--fb-primary);
        font-size: .76rem;
        font-weight: 900;
        margin-bottom: 14px;
    }

    .elite-product-title{
        margin: 0 0 10px;
        font-size: 2rem;
        line-height: 1.18;
        font-weight: 900;
        color: var(--fb-text);
        letter-spacing: -.02em;
    }

    .elite-product-desc{
        margin: 0 0 18px;
        color: var(--fb-muted);
        font-size: .94rem;
        line-height: 1.9;
        font-weight: 700;
    }

    .elite-price-box{
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-radius: 20px;
        background: linear-gradient(180deg, #fffdfa 0%, #f8f4ee 100%);
        border: 1px solid #ebe3d7;
        margin-bottom: 18px;
    }

    .elite-price-box-label{
        color: #8a847a;
        font-size: .76rem;
        font-weight: 800;
    }

    .elite-price-box-value{
        color: var(--fb-text);
        font-size: 1.2rem;
        font-weight: 900;
    }

    .elite-options-stack{
        display: grid;
        gap: 14px;
    }

    .elite-option-card{
        padding: 16px;
    }

    .elite-option-head{
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .elite-option-title{
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        color: var(--fb-text);
    }

    .elite-option-note{
        color: #8a847a;
        font-size: .78rem;
        font-weight: 800;
    }

    .elite-option-list{
        display: grid;
        gap: 8px;
    }

    .elite-option-item{
        position: relative;
        border: 1px solid #ece5da;
        background: #fcfaf7;
        border-radius: 16px;
        padding: 12px 14px;
        transition: .18s ease;
    }

    .elite-option-item:hover{
        border-color: #d9d1c5;
        background: #fffdfa;
    }

    .elite-option-item .form-check{
        margin: 0;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .elite-option-item .form-check-input{
        margin-top: .25rem;
        flex-shrink: 0;
    }

    .elite-option-item .form-check-label{
        flex: 1;
        color: #4d4a45;
        font-size: .88rem;
        font-weight: 800;
        line-height: 1.7;
    }

    .elite-option-price{
        color: #b45309;
        font-weight: 900;
        white-space: nowrap;
    }

    .elite-qty-wrap{
        margin-top: 6px;
    }

    .elite-qty-label{
        display: block;
        margin-bottom: 8px;
        color: var(--fb-text);
        font-size: .88rem;
        font-weight: 900;
    }

    .elite-qty-input{
        height: 52px;
        border-radius: 16px;
        border: 1px solid #ddd7cc;
        background: #faf7f2;
        font-weight: 800;
        box-shadow: none !important;
    }

    .elite-qty-input:focus{
        background: #fff;
        border-color: #c6d2ba;
    }

    .elite-summary-card{
        padding: 16px 18px;
        margin-top: 16px;
        background: linear-gradient(180deg, #fffdfa 0%, #f8f4ee 100%);
    }

    .elite-summary-row{
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .elite-summary-label{
        color: #6f6a61;
        font-size: .88rem;
        font-weight: 800;
    }

    .elite-summary-value{
        color: var(--fb-text);
        font-size: 1.06rem;
        font-weight: 900;
    }

    .elite-submit-btn{
        width: 100%;
        min-height: 54px;
        border: none;
        border-radius: 16px;
        margin-top: 16px;
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
        color: #fff;
        font-size: .92rem;
        font-weight: 900;
        box-shadow: 0 14px 26px rgba(111,127,95,.16);
    }

    .elite-submit-btn:hover{
        color: #fff;
        opacity: .98;
    }

    .elite-success-card{
        padding: 16px;
        margin-top: 16px;
        border-color: #d7ebdc;
        background: #f7fcf8;
    }

    .elite-success-title{
        margin: 0 0 12px;
        color: #166534;
        font-size: .96rem;
        font-weight: 900;
    }

    .elite-success-actions{
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .elite-secondary-link,
    .elite-primary-link{
        min-height: 44px;
        padding: 10px 14px;
        border-radius: 14px;
        font-size: .84rem;
        font-weight: 900;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .elite-secondary-link{
        background: #f3efe8;
        border: 1px solid #e7e0d4;
        color: var(--fb-text);
    }

    .elite-secondary-link:hover{
        color: var(--fb-text);
        background: #ebe4d9;
    }

    .elite-primary-link{
        background: linear-gradient(135deg, var(--fb-primary-dark) 0%, var(--fb-primary) 100%);
        color: #fff;
    }

    .elite-primary-link:hover{
        color: #fff;
        opacity: .98;
    }

    @media (max-width: 991.98px){
        .elite-product-wrap{
            grid-template-columns: 1fr;
        }

        .elite-product-image{
            min-height: 360px;
            max-height: 460px;
        }
    }

    @media (max-width: 767.98px){
        .elite-product-page{
            padding-bottom: 70px;
        }

        .elite-product-wrap{
            gap: 16px;
        }

        .elite-product-media-card,
        .elite-product-info-card,
        .elite-option-card,
        .elite-summary-card,
        .elite-success-card{
            border-radius: 20px;
        }

        .elite-product-media-card{
            padding: 10px;
        }

        .elite-product-image{
            min-height: 240px;
            max-height: 320px;
            border-radius: 16px;
        }

        .elite-product-info-card{
            padding: 16px;
        }

        .elite-product-title{
            font-size: 1.24rem;
            line-height: 1.3;
        }

        .elite-product-desc{
            font-size: .84rem;
            line-height: 1.8;
        }

        .elite-price-box{
            padding: 12px 14px;
            border-radius: 16px;
        }

        .elite-price-box-value{
            font-size: 1rem;
        }

        .elite-option-card{
            padding: 14px;
        }

        .elite-option-title{
            font-size: .92rem;
        }

        .elite-option-note{
            font-size: .72rem;
        }

        .elite-option-item{
            border-radius: 14px;
            padding: 10px 12px;
        }

        .elite-option-item .form-check-label{
            font-size: .82rem;
        }

        .elite-qty-input{
            height: 48px;
            border-radius: 14px;
        }

        .elite-summary-card{
            padding: 14px;
        }

        .elite-summary-value{
            font-size: .96rem;
        }

        .elite-submit-btn{
            min-height: 50px;
            font-size: .86rem;
            border-radius: 14px;
        }

        .elite-success-actions{
            display: grid;
            grid-template-columns: 1fr;
        }

        .elite-secondary-link,
        .elite-primary-link{
            width: 100%;
        }
    }
</style>

<div class="elite-product-page">
    <div class="elite-product-wrap">
        <div class="elite-product-media-card">
            <img
                src="{{ $product->image ? \App\Support\MediaUrl::fromPath( $product->image) : 'https://via.placeholder.com/700x500?text=Food' }}"
                class="elite-product-image"
                alt="{{ $product->name }}"
            >
        </div>

        <div class="elite-product-info-card">
            @if($product->category?->name)
                <span class="elite-product-category">{{ $product->category?->name }}</span>
            @endif

            <h1 class="elite-product-title">{{ $product->name }}</h1>

            @if($product->description)
                <p class="elite-product-desc">{{ $product->description }}</p>
            @endif

            <div class="elite-price-box">
                <div>
                    <div class="elite-price-box-label">{{ __('product.product_price') }}</div>
                    <div class="elite-price-box-value">{{ number_format($product->price, 2) }} {{ __('product.currency_egp') }}</div>
                </div>
            </div>

            <form action="{{ route('cart.add', $product->id) }}" method="POST" id="productOptionsForm">
                @csrf

                <div class="elite-options-stack">
                    @foreach($product->optionGroups as $group)
                        <div class="elite-option-card">
                            <div class="elite-option-head">
                                <h5 class="elite-option-title">{{ $group->name }}</h5>
                                <span class="elite-option-note">
                                    {{ $group->type === 'single' ? __('product.single_choice') : __('product.multiple_choices') }}
                                    {{ $group->is_required ? '• ' . __('product.required') : '' }}
                                </span>
                            </div>

                            <div class="elite-option-list">
                                @if($group->type === 'single')
                                    @foreach($group->items as $item)
                                        <div class="elite-option-item">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input option-input"
                                                    type="radio"
                                                    name="group_{{ $group->id }}"
                                                    id="item_{{ $item->id }}"
                                                    value="{{ $item->id }}"
                                                    data-price="{{ $item->price }}"
                                                    {{ $item->is_default ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="item_{{ $item->id }}">
                                                    {{ $item->name }}
                                                    @if($item->price > 0)
                                                        <span class="elite-option-price">(+{{ number_format($item->price, 2) }} {{ __('product.currency_egp') }})</span>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    @foreach($group->items as $item)
                                        <div class="elite-option-item">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input option-input group-multiple-{{ $group->id }}"
                                                    type="checkbox"
                                                    name="group_{{ $group->id }}[]"
                                                    id="item_{{ $item->id }}"
                                                    value="{{ $item->id }}"
                                                    data-price="{{ $item->price }}"
                                                    data-max="{{ $group->max_selection }}"
                                                >
                                                <label class="form-check-label" for="item_{{ $item->id }}">
                                                    {{ $item->name }}
                                                    @if($item->price > 0)
                                                        <span class="elite-option-price">(+{{ number_format($item->price, 2) }} {{ __('product.currency_egp') }})</span>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="elite-qty-wrap mt-3">
                    <label class="elite-qty-label">{{ __('product.quantity') }}</label>
                    <input type="number" name="quantity" id="quantityInput" class="form-control elite-qty-input" value="1" min="1" required>
                </div>

                <div class="elite-summary-card">
                    <div class="elite-summary-row">
                        <span class="elite-summary-label">{{ __('product.final_price') }}</span>
                        <strong class="elite-summary-value" id="finalPrice">{{ number_format($product->price, 2) }} {{ __('product.currency_egp') }}</strong>
                    </div>
                </div>

                <button class="elite-submit-btn">{{ __('product.add_to_cart') }}</button>

                @if(session('item_added_to_cart'))
                    <div class="elite-success-card">
                        <div class="elite-success-title">{{ __('product.product_added_successfully') }}</div>

                        <div class="elite-success-actions">
                            <a href="{{ route('home') }}" class="elite-secondary-link">
                                {{ __('product.add_more_products') }}
                            </a>

                            <a href="{{ route('cart.index') }}" class="elite-primary-link">
                                {{ __('product.go_to_cart') }}
                            </a>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<script nonce="{{ $cspNonce }}">
    const basePrice = {{ (float) $product->price }};
    const quantityInput = document.getElementById('quantityInput');
    const finalPriceElement = document.getElementById('finalPrice');
    const optionInputs = document.querySelectorAll('.option-input');
    const currencyText = @json(__('product.currency_egp'));
    const maxSelectionText = @json(__('product.max_selection_reached'));

    function formatPrice(price) {
        return price.toFixed(2) + ' ' + currencyText;
    }

    function calculatePrice() {
        let extra = 0;

        optionInputs.forEach(input => {
            if (input.checked) {
                extra += parseFloat(input.dataset.price || 0);
            }
        });

        const quantity = parseInt(quantityInput.value || 1);
        const finalPrice = (basePrice + extra) * quantity;

        finalPriceElement.textContent = formatPrice(finalPrice);
    }

    optionInputs.forEach(input => {
        input.addEventListener('change', function () {
            const classes = Array.from(this.classList);
            const multiClass = classes.find(c => c.startsWith('group-multiple-'));

            if (multiClass) {
                const groupInputs = document.querySelectorAll('.' + multiClass);
                const max = parseInt(this.dataset.max || 0);

                if (max > 0) {
                    const checkedCount = Array.from(groupInputs).filter(el => el.checked).length;
                    if (checkedCount > max) {
                        this.checked = false;
                        alert(maxSelectionText);
                    }
                }
            }

            calculatePrice();
        });
    });

    quantityInput.addEventListener('input', calculatePrice);

    calculatePrice();
</script>
@endsection