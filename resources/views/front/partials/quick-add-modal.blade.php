<div class="modal fade quick-modal" id="productQuickAddModal" tabindex="-1" aria-labelledby="productQuickAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="quickAddToCartForm" method="POST">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="productQuickAddModalLabel">{{ __('home.add_to_cart') }}</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-12 col-md-5"><div class="quick-product-media"><img id="quickProductImage" src="" alt=""></div></div>
                        <div class="col-12 col-md-7">
                            <div id="quickProductName" class="quick-product-name"></div>
                            <div id="quickProductPrice" class="quick-product-price"></div>
                            <div id="quickProductDescription" class="quick-product-desc"></div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('home.quantity') }}</label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                            </div>
                            <div id="quickProductOptions"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">{{ __('home.cancel') }}</button>
                    <button type="submit" class="btn btn-success px-4">{{ __('home.confirm_addition') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
