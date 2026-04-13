<div class="modal fade quick-modal" id="productQuickAddModal" tabindex="-1" aria-labelledby="productQuickAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="quickAddToCartForm" method="POST">
                @csrf
                <div class="quick-modal-head">
                    <button type="button" class="quick-close-btn" data-bs-dismiss="modal" aria-label="{{ __('home.close') }}">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <h5 class="modal-title quick-modal-title" id="productQuickAddModalLabel">{{ __('home.add_to_cart') }}</h5>
                </div>
                <div class="modal-body quick-modal-body">
                    <div class="quick-product-media">
                        <img id="quickProductImage" src="" alt="">
                    </div>

                    <div class="quick-main-details">
                        <h3 id="quickProductName" class="quick-product-name"></h3>
                        <div id="quickProductPrice" class="quick-product-price"></div>
                        <p id="quickProductDescription" class="quick-product-desc"></p>
                    </div>

                    <div class="quick-section">
                        <div class="quick-section-head">
                            <h6>{{ __('home.quantity') }}</h6>
                        </div>
                        <div class="quick-qty-control">
                            <button type="button" class="quick-qty-btn" id="quickQtyMinus" aria-label="Decrease quantity">−</button>
                            <span id="quickQtyValue" class="quick-qty-value">1</span>
                            <button type="button" class="quick-qty-btn" id="quickQtyPlus" aria-label="Increase quantity">+</button>
                        </div>
                    </div>

                    <input type="hidden" name="quantity" id="quickQtyInput" value="1" min="1" required>

                    <div id="quickProductOptions"></div>

                    <div class="quick-section">
                        <div class="quick-section-head">
                            <h6>ملاحظات خاصة</h6>
                        </div>
                        <textarea name="notes" id="quickProductNotes" class="quick-notes-input" rows="3" placeholder="مثال: بدون بصل أو الصوص على الجانب"></textarea>
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <div class="quick-total-block">
                        <span>الإجمالي</span>
                        <strong id="quickFinalPrice"></strong>
                    </div>
                    <button type="submit" class="quick-submit-btn" id="quickAddSubmitBtn">
                        <span>{{ __('home.confirm_addition') }}</span>
                        <strong id="quickSubmitPrice"></strong>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
