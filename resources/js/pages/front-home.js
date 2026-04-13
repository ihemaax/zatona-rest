document.addEventListener('DOMContentLoaded', function () {
    const mobileSearch = document.getElementById('menuSearchInput');
    const desktopSearch = document.getElementById('menuSearchInputDesktop');
    const sections = document.querySelectorAll('.product-section');
    const stories = document.querySelectorAll('[data-story-target]');
    const form = document.getElementById('quickAddToCartForm');
    const productName = document.getElementById('quickProductName');
    const productPrice = document.getElementById('quickProductPrice');
    const productDescription = document.getElementById('quickProductDescription');
    const productImage = document.getElementById('quickProductImage');
    const optionsWrap = document.getElementById('quickProductOptions');
    const quantityInput = document.getElementById('quickQtyInput');
    const quantityValue = document.getElementById('quickQtyValue');
    const qtyPlusBtn = document.getElementById('quickQtyPlus');
    const qtyMinusBtn = document.getElementById('quickQtyMinus');
    const finalPriceNode = document.getElementById('quickFinalPrice');
    const submitPriceNode = document.getElementById('quickSubmitPrice');
    const submitBtn = document.getElementById('quickAddSubmitBtn');
    const floatingCheckout = document.getElementById('floatingCheckout');
    const floatingCheckoutCount = document.getElementById('floatingCheckoutCount');
    const headerCartButton = document.getElementById('headerCartButton');
    let headerCartCount = document.getElementById('headerCartCount');
    const modalElement = document.getElementById('productQuickAddModal');
    const productModal = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;

    const config = window.frontHomeConfig || {};
    const fallbackImage = config.productFallbackImage || 'https://via.placeholder.com/600x400?text=Food';
    const currency = config.currency || 'EGP';

    let currentCartCount = Number(config.cartCount || 0);
    let activeCategory = 'all';
    let currentProduct = null;

    function formatMoney(value) {
        const number = Number(value || 0);
        return `${number.toFixed(2)} ${currency}`;
    }

    function ensureHeaderBadge(count) {
        if (!headerCartCount && headerCartButton) {
            headerCartCount = document.createElement('span');
            headerCartCount.id = 'headerCartCount';
            headerCartCount.className = 'cart-count';
            headerCartButton.appendChild(headerCartCount);
        }

        if (!headerCartCount) return;
        if (count > 0) {
            headerCartCount.textContent = count;
            headerCartCount.style.display = '';
        } else {
            headerCartCount.style.display = 'none';
        }
    }

    function getSearchValue() {
        return (mobileSearch?.value || desktopSearch?.value || '').toLowerCase().trim();
    }

    function syncSearchInputs(source) {
        const value = source?.value || '';
        if (mobileSearch && source !== mobileSearch) mobileSearch.value = value;
        if (desktopSearch && source !== desktopSearch) desktopSearch.value = value;
    }

    function filterMenu() {
        const searchValue = getSearchValue();
        sections.forEach(section => {
            const sectionCategory = section.dataset.category;
            const cards = section.querySelectorAll('.product-card-item');
            let visibleCards = 0;

            cards.forEach(card => {
                const searchable = card.dataset.name || '';
                const searchMatch = !searchValue || searchable.includes(searchValue);
                const categoryMatch = activeCategory === 'all' || activeCategory === sectionCategory;
                if (searchMatch && categoryMatch) {
                    card.style.display = '';
                    visibleCards += 1;
                } else {
                    card.style.display = 'none';
                }
            });

            section.style.display = visibleCards ? '' : 'none';
        });
    }

    function setActiveStory(target) {
        activeCategory = target || 'all';
        stories.forEach(story => {
            story.classList.toggle('active', story.dataset.storyTarget === activeCategory);
        });
        filterMenu();
    }

    function updateCartUI(cartCount) {
        currentCartCount = Number(cartCount || 0);
        ensureHeaderBadge(currentCartCount);

        if (floatingCheckout) {
            if (currentCartCount > 0) {
                floatingCheckout.style.display = '';
                if (floatingCheckoutCount) floatingCheckoutCount.textContent = currentCartCount;
            } else {
                floatingCheckout.style.display = 'none';
            }
        }
    }

    function getSelectedExtras() {
        if (!currentProduct || !Array.isArray(currentProduct.options)) return 0;

        return currentProduct.options.reduce((total, group) => {
            const isMultiple = group.type === 'multiple';
            const selectedValues = [];

            if (isMultiple) {
                const checked = form.querySelectorAll(`input[name="options[${group.id}][]"]:checked`);
                checked.forEach(input => selectedValues.push(Number(input.value)));
            } else {
                const selected = form.querySelector(`input[name="options[${group.id}]"]:checked`);
                if (selected) selectedValues.push(Number(selected.value));
            }

            const groupExtra = (group.items || []).reduce((groupTotal, item) => {
                if (selectedValues.includes(Number(item.id))) {
                    return groupTotal + Number(item.price || 0);
                }
                return groupTotal;
            }, 0);

            return total + groupExtra;
        }, 0);
    }

    function updateFinalPrice() {
        if (!currentProduct) {
            finalPriceNode.textContent = formatMoney(0);
            submitPriceNode.textContent = formatMoney(0);
            return;
        }

        const quantity = Math.max(1, Number(quantityInput.value || 1));
        const unitPrice = Number(currentProduct.price || 0) + getSelectedExtras();
        const total = unitPrice * quantity;

        finalPriceNode.textContent = formatMoney(total);
        submitPriceNode.textContent = formatMoney(total);
    }

    function syncQuantity(nextValue) {
        const quantity = Math.max(1, Number(nextValue || 1));
        quantityInput.value = quantity;
        quantityValue.textContent = String(quantity);
        updateFinalPrice();
    }

    function renderOptions(groups) {
        optionsWrap.innerHTML = '';

        if (!Array.isArray(groups) || !groups.length) return;

        groups.forEach(group => {
            const section = document.createElement('div');
            section.className = 'quick-section';

            const title = document.createElement('div');
            title.className = 'quick-section-head';
            title.innerHTML = `<h6>${group.name || ''}</h6><span class="quick-option-note">${group.is_required ? 'إجباري' : 'اختياري'}</span>`;

            const list = document.createElement('div');
            list.className = 'quick-option-list';

            (group.items || []).forEach(item => {
                const itemPrice = Number(item.price || 0);
                const type = group.type === 'multiple' ? 'checkbox' : 'radio';
                const inputName = group.type === 'multiple' ? `options[${group.id}][]` : `options[${group.id}]`;
                const inputId = `opt_${group.id}_${item.id}`;
                const optionRow = document.createElement('div');
                optionRow.className = 'quick-option-item';

                optionRow.innerHTML = `
                    <label for="${inputId}">
                        <input type="${type}" id="${inputId}" name="${inputName}" value="${item.id}" ${group.type === 'single' && group.is_required ? 'required' : ''}>
                        <span>${item.name || ''}</span>
                    </label>
                    <span class="quick-option-price">${itemPrice > 0 ? `+${formatMoney(itemPrice)}` : 'بدون تكلفة إضافية'}</span>
                `;

                list.appendChild(optionRow);
            });

            section.appendChild(title);
            section.appendChild(list);
            optionsWrap.appendChild(section);
        });
    }

    function resetQuickModal() {
        form.reset();
        currentProduct = null;
        productName.textContent = '';
        productPrice.textContent = '';
        productDescription.textContent = '';
        productImage.src = fallbackImage;
        productImage.alt = 'Product image';
        optionsWrap.innerHTML = '';
        form.action = '';
        submitBtn.disabled = true;
        syncQuantity(1);
    }

    stories.forEach(story => {
        story.addEventListener('click', function () {
            const target = this.dataset.storyTarget || 'all';
            setActiveStory(target);
            const destination = document.getElementById(`section-${target}`);
            if (destination) destination.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    mobileSearch?.addEventListener('input', function () { syncSearchInputs(mobileSearch); filterMenu(); });
    desktopSearch?.addEventListener('input', function () { syncSearchInputs(desktopSearch); filterMenu(); });

    modalElement?.addEventListener('show.bs.modal', () => document.body.classList.add('mobile-modal-open'));
    modalElement?.addEventListener('hidden.bs.modal', () => {
        document.body.classList.remove('mobile-modal-open');
        resetQuickModal();
    });

    qtyPlusBtn?.addEventListener('click', () => syncQuantity(Number(quantityInput.value || 1) + 1));
    qtyMinusBtn?.addEventListener('click', () => syncQuantity(Number(quantityInput.value || 1) - 1));

    form?.addEventListener('change', function (event) {
        if (event.target.matches('input[name^="options["]')) {
            updateFinalPrice();
        }
    });

    document.querySelectorAll('.open-product-modal').forEach(button => {
        button.addEventListener('click', function () {
            let product = {};
            try {
                product = JSON.parse(this.dataset.product || '{}');
            } catch (e) {
                product = {};
            }

            if (!product.id) {
                resetQuickModal();
                return;
            }

            currentProduct = product;
            productName.textContent = product.name || '';
            productPrice.textContent = `سعر البداية: ${formatMoney(product.price || 0)}`;
            productDescription.textContent = product.description || 'لا يوجد وصف لهذا المنتج.';
            productImage.src = product.image || fallbackImage;
            productImage.alt = product.name || 'Product image';
            productImage.onerror = function () {
                this.onerror = null;
                this.src = fallbackImage;
            };

            form.action = `${config.cartAddBase || '/cart/add'}/${product.id}`;
            renderOptions(product.options || []);
            syncQuantity(1);
            submitBtn.disabled = false;
        });
    });

    form?.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!form.action || !currentProduct) return;

        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span>${config.addingText || '...'}...</span><strong>${submitPriceNode.textContent}</strong>`;

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
                return;
            }

            updateCartUI(data.cart_count || (currentCartCount + 1));
            productModal?.hide();
        } catch (error) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
            return;
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
    });

    resetQuickModal();

    const popup = document.getElementById('offerPopupOverlay');
    const closeBtn = document.getElementById('offerPopupCloseBtn');
    if (config.popup && popup) {
        const popupId = `popup_campaign_${config.popup.id}`;
        const showOnce = !!config.popup.showOnce;
        let canShow = true;

        if (showOnce && localStorage.getItem(popupId) === '1') canShow = false;
        if (canShow) setTimeout(() => popup.classList.add('show'), 500);

        closeBtn?.addEventListener('click', () => {
            popup.classList.remove('show');
            if (showOnce) localStorage.setItem(popupId, '1');
        });

        popup.addEventListener('click', e => {
            if (e.target === popup) {
                popup.classList.remove('show');
                if (showOnce) localStorage.setItem(popupId, '1');
            }
        });
    }
});
