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
    const quantityInput = form.querySelector('input[name="quantity"]');
    const floatingCheckout = document.getElementById('floatingCheckout');
    const floatingCheckoutCount = document.getElementById('floatingCheckoutCount');
    const headerCartButton = document.getElementById('headerCartButton');
    let headerCartCount = document.getElementById('headerCartCount');
    const modalElement = document.getElementById('productQuickAddModal');
    const productModal = bootstrap.Modal.getOrCreateInstance(modalElement);

    const config = window.frontHomeConfig || {};
    let currentCartCount = Number(config.cartCount || 0);
    let activeCategory = 'all';

    function ensureHeaderBadge(count){
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

    function updateCartUI(cartCount, cartTotal){
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
    modalElement?.addEventListener('hidden.bs.modal', () => document.body.classList.remove('mobile-modal-open'));

    document.querySelectorAll('.open-product-modal').forEach(button => {
        button.addEventListener('click', function () {
            let product = {};
            try { product = JSON.parse(this.dataset.product || '{}'); } catch (e) { product = {}; }

            productName.textContent = product.name || '';
            productPrice.textContent = formatMoney(product.price || 0);
            productDescription.textContent = product.description || '';
            productImage.src = product.image || 'https://via.placeholder.com/600x400?text=Food';
            form.action = `/cart/add/${product.id}`;
            quantityInput.value = 1;
            optionsWrap.innerHTML = '';

            if (Array.isArray(product.options) && product.options.length) {
                product.options.forEach(group => {
                    const groupBox = document.createElement('div');
                    groupBox.className = 'mb-3';
                    let itemsHtml = '';

                    if (group.type === 'multiple') {
                        group.items.forEach(item => {
                            itemsHtml += `<div class="form-check"><input class="form-check-input" type="checkbox" name="options[${group.id}][]" value="${item.id}" id="opt_${group.id}_${item.id}"><label class="form-check-label" for="opt_${group.id}_${item.id}">${item.name} ${parseFloat(item.price||0) > 0 ? `( +${parseFloat(item.price).toFixed(2)} ${config.currency || 'EGP'} )` : ''}</label></div>`;
                        });
                    } else {
                        group.items.forEach(item => {
                            itemsHtml += `<div class="form-check"><input class="form-check-input" type="radio" name="options[${group.id}]" value="${item.id}" id="opt_${group.id}_${item.id}" ${group.is_required?'required':''}><label class="form-check-label" for="opt_${group.id}_${item.id}">${item.name} ${parseFloat(item.price||0) > 0 ? `( +${parseFloat(item.price).toFixed(2)} ${config.currency || 'EGP'} )` : ''}</label></div>`;
                        });
                    }

                    groupBox.innerHTML = `<label class="form-label fw-bold d-block mb-2">${group.name}${group.is_required ? '<span class="text-danger">*</span>' : ''}</label><div class="quick-option-box">${itemsHtml}</div>`;
                    optionsWrap.appendChild(groupBox);
                });
            }
        });
    });

    form.addEventListener('submit', async function(e){
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = config.addingText || '...';

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
            if (!response.ok) return;

            productModal.hide();
            form.reset();
            optionsWrap.innerHTML = '';
            quantityInput.value = 1;
            const newCartCount = typeof data.cart_count !== 'undefined' ? data.cart_count : (currentCartCount + parseInt(formData.get('quantity') || 1, 10));
            const newCartTotal = typeof data.cart_total !== 'undefined' ? data.cart_total : currentCartTotal;
            updateCartUI(newCartCount, newCartTotal);
        } catch (error) {
            // noop
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });

    
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
