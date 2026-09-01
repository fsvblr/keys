(function () {
    const catalogBtn = document.getElementById('catalogBtn');
    const catalogMenu = document.getElementById('catalogMenu');

    if (catalogBtn && catalogMenu) {
        catalogBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            catalogMenu.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (!catalogMenu.contains(e.target) && !catalogBtn.contains(e.target)) {
                catalogMenu.classList.remove('open');
            }
        });
    }

    function setBuyButtonLoading(button, loading) {
        button.disabled = loading;
        button.classList.toggle('is-loading', loading);

        if (loading) {
            button.dataset.originalText = button.textContent;
            button.textContent = 'Processing...';
        } else if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
            delete button.dataset.originalText;
        }
    }

    document.querySelectorAll('.buy-btn').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (btn.disabled) {
                return;
            }

            const sku = btn.dataset.sku;
            setBuyButtonLoading(btn, true);

            try {
                const resp = await fetch('/orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ sku: sku })
                });
                const data = await resp.json();

                if (data.order_number) {
                    window.location.href = '/order/status/' + data.order_number;
                    return;
                }

                alert(data.error || 'Order creation failed');
            } catch (err) {
                alert('Network error');
            } finally {
                setBuyButtonLoading(btn, false);
            }
        });
    });

    function setButtonsGroupLoading(buttons, loading) {
        buttons.forEach(function (btn) {
            if (loading) {
                btn.disabled = true;
                btn.dataset.originalText = btn.textContent;
                btn.textContent = 'Processing...';
            } else {
                btn.disabled = false;
                if (btn.dataset.originalText) {
                    btn.textContent = btn.dataset.originalText;
                    delete btn.dataset.originalText;
                }
            }
        });
    }

    const payButtons = document.querySelectorAll('#paySuccessBtn, #payFailBtn');
    payButtons.forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (btn.disabled) {
                return;
            }

            const status = btn.id === 'payFailBtn' ? 'failed' : 'paid';
            const orderNumber = btn.dataset.order;
            const buttons = Array.from(payButtons);

            setButtonsGroupLoading(buttons, true);

            try {
                await sendPayment(orderNumber, status);
            } finally {
                setButtonsGroupLoading(buttons, false);
            }
        });
    });

    const supplierButtons = document.querySelectorAll('.supplier-btn');
    supplierButtons.forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (btn.disabled) {
                return;
            }

            const orderNumber = btn.dataset.order;
            const sku = btn.dataset.sku;
            const provider = btn.dataset.provider;
            const requestId = `${orderNumber}-${provider}`;
            const resultEl = document.getElementById('supplierResult');
            const buttons = Array.from(supplierButtons);

            if (resultEl) {
                resultEl.textContent = 'Receiving the key...';
            }

            setButtonsGroupLoading(buttons, true);

            try {
                const resp = await fetch('/supplier/issue', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        request_id: requestId,
                        sku: sku,
                        order_id: orderNumber,
                        supplier: provider
                    })
                });

                const data = await resp.json();

                if (resp.ok && data.status === 'ok' && data.code) {
                    window.location.reload();
                    return;
                }

                if (resultEl) {
                    resultEl.textContent = data.reason || data.error || 'Ошибка получения ключа';
                }
            } catch (err) {
                if (resultEl) {
                    resultEl.textContent = 'Network error';
                }
            } finally {
                setButtonsGroupLoading(buttons, false);
            }
        });
    });

    const retryButtons = document.querySelectorAll('.retry-btn');
    retryButtons.forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const orderNumber = btn.dataset.order;
            const resp = await fetch('/admin/retry/' + orderNumber, { method: 'POST' });
            const data = await resp.json();
            alert(data.ok ? 'Retry initiated' : (data.error || 'Retry failed'));
            window.location.reload();
        });
    });

    async function sendPayment(orderNumber, status) {
        const resultEl = document.getElementById('payResult');
        resultEl.textContent = 'Processing...';
        const resp = await fetch('/simulate-payment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_number: orderNumber, status: status })
        });
        const data = await resp.json();
        resultEl.textContent = data.ok ? 'Webhook sent' : (data.error || 'Error');
        setTimeout(() => window.location.reload(), 1200);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const track = document.querySelector('.slider-track');
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.querySelector('.slider-arrow.prev');
        const nextBtn = document.querySelector('.slider-arrow.next');

        let currentIndex = 0;
        const totalSlides = slides.length;

        function updateSlider() {
            track.style.transform = `translateX(-${currentIndex * 100}%)`;
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentIndex);
            });
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateSlider();
        }

        function prevSlide() {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
            updateSlider();
        }

        if (totalSlides) {
            nextBtn.addEventListener('click', nextSlide);
            prevBtn.addEventListener('click', prevSlide);

            let autoPlay = setInterval(nextSlide, 5000);

            function resetTimer() {
                clearInterval(autoPlay);
                autoPlay = setInterval(nextSlide, 5000);
            }

            [nextBtn, prevBtn, ...dots].forEach(element => {
                element.addEventListener('click', resetTimer);
            });
        }

        if (dots) {
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    currentIndex = index;
                    updateSlider();
                });
            });
        }
    });

    const promoBtn = document.getElementById('promo-btn');
    if (promoBtn) {
        const promoInput = promoBtn.parentElement.querySelector('input[name="promocode"]');

        promoBtn.addEventListener('click', (event) => {
            event.preventDefault();
            promoInput.classList.toggle('show');
            if (promoInput.classList.contains('show')) {
                promoInput.focus();
            }
        });
    }

    const currencies = document.querySelectorAll('.currency-switcher .switch-btn');
    if (currencies) {
        currencies.forEach((currency, index) => {
            currency.addEventListener('click', (event) => {
                event.preventDefault();
                const currentActive = event.currentTarget.parentElement.querySelector('.switch-btn.active');
                if (currentActive) {
                    currentActive.classList.remove('active');
                }
                event.currentTarget.classList.add('active');
            });
        });
    }

    const steamPayBtn = document.querySelector('#steam-pay-btn');
    if (steamPayBtn) {
        steamPayBtn.addEventListener('click', async  function (event) {
            event.preventDefault();

            if (this.disabled) {
                return;
            }

            const form = this.closest('form');
            const sku = form.querySelector('input[name="sku"]').value;
            const promocode = form.querySelector('input[name="promocode"]').value;
            const login = form.querySelector('input[name="login"]').value;
            const price = form.querySelector('input[name="price"]').value;
            let cleanedPrice = 0;
            if (price) {
                cleanedPrice = price.replace(/[^\d.,]/g, '');
            }

            setBuyButtonLoading(this, true);

            try {
                const resp = await fetch('/orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ sku: sku, promocode: promocode, login: login, price: cleanedPrice })
                });
                const data = await resp.json();

                if (data.order_number) {
                    window.location.href = '/order/status/' + data.order_number;
                    return;
                }

                alert(data.error || 'Order creation failed');
            } catch (err) {
                alert('Network error');
            } finally {
                setBuyButtonLoading(this, false);
            }
        });
    }

})();
