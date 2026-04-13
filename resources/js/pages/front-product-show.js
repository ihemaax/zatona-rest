const config = window.frontProductConfig || {};
    const basePrice = Number(config.basePrice || 0);
    const quantityInput = document.getElementById('quantityInput');
    const finalPriceElement = document.getElementById('finalPrice');
    const optionInputs = document.querySelectorAll('.option-input');
    const currencyText = config.currencyText || 'EGP';
    const maxSelectionText = config.maxSelectionText || 'Max selection reached';

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
