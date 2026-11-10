(function () {
    initModals();
    initDateRanges();
    initToasts();
    initFavoriteToggle();
    initCopyToken();
    initDrawerFilter();
    initPenaltyCalculator();
    initQrRender();
    initPaymentToggle();
    initPaymentStatusReload();
    initFilePreview();

    function initModals() {
        var activeModal = null;
        var lastFocused = null;

        function open(modal) {
            activeModal = modal;
            lastFocused = document.activeElement;
            modal.hidden = false;
            document.body.classList.add('ldr-modal-open');

            var focusable = modal.querySelector('textarea, input, select, button, [href]');
            if (focusable) {
                focusable.focus();
            }
        }

        function close() {
            if (!activeModal) {
                return;
            }

            activeModal.hidden = true;
            activeModal = null;
            document.body.classList.remove('ldr-modal-open');

            if (lastFocused) {
                lastFocused.focus();
            }
        }

        document.querySelectorAll('[data-modal-open], [data-confirm-open]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                var id = trigger.getAttribute('data-modal-open') || trigger.getAttribute('data-confirm-open');
                var modal = id ? document.getElementById(id) : null;

                if (modal) {
                    open(modal);
                }
            });
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-modal-close], [data-confirm-close]')) {
                close();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                close();
            }
        });
    }

    function initDateRanges() {
        document.querySelectorAll('[data-date-range]').forEach(function (form) {
            var start = form.querySelector('[data-date-start]');
            var end = form.querySelector('[data-date-end]');

            if (!start || !end) {
                return;
            }

            function sync() {
                if (start.value) {
                    end.min = start.value;
                } else {
                    end.removeAttribute('min');
                }

                if (start.value && end.value && end.value < start.value) {
                    end.value = start.value;
                }
            }

            start.addEventListener('change', sync);
            sync();

            var display = form.querySelector('[data-duration-display]');
            var maxDays = parseInt(form.getAttribute('data-max-days') || '0', 10);

            if (!display) {
                return;
            }

            function showDuration() {
                if (!start.value || !end.value || end.value < start.value) {
                    display.textContent = '—';
                    return;
                }

                var days = Math.round((new Date(end.value) - new Date(start.value)) / 86400000);

                display.textContent = days + (maxDays ? ' / ' + maxDays : '') + ' hari';
            }

            start.addEventListener('change', showDuration);
            end.addEventListener('change', showDuration);
            showDuration();
        });
    }

    function initToasts() {
        document.querySelectorAll('[data-toast]').forEach(function (stack) {
            function hide() {
                stack.hidden = true;
            }

            stack.querySelectorAll('[data-toast-close]').forEach(function (button) {
                button.addEventListener('click', hide);
            });

            window.setTimeout(hide, 4000);
        });
    }

    function initFavoriteToggle() {
        document.querySelectorAll('[data-asset][data-favorite-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var assetId = btn.getAttribute('data-asset');
                var isActive = btn.classList.contains('is-active');

                fetch('/borrower/catalog/' + assetId + '/favorite', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                }).then(function (res) {
                    return res.ok ? res.json() : null;
                }).then(function (data) {
                    if (!data) {
                        return;
                    }
                    if (data.favorited) {
                        btn.classList.add('is-active');
                        btn.setAttribute('aria-label', 'Hapus dari favorit');
                    } else {
                        btn.classList.remove('is-active');
                        btn.setAttribute('aria-label', 'Tambah ke favorit');
                    }
                });
            });
        });
    }

    function initCopyToken() {
        document.querySelectorAll('[data-copy-token]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var token = btn.getAttribute('data-copy-token');
                navigator.clipboard.writeText(token).then(function () {
                    showToast('Nomor disalin');
                });
            });
        });
    }

    function initDrawerFilter() {
        var drawer = document.querySelector('[data-filter-drawer]');
        var openBtn = document.querySelector('[data-filter-open]');
        var closeBtn = document.querySelector('[data-filter-close]');

        if (!drawer || !openBtn || !closeBtn) {
            return;
        }

        openBtn.addEventListener('click', function () {
            drawer.classList.add('is-open');
        });

        closeBtn.addEventListener('click', function () {
            drawer.classList.remove('is-open');
        });

        document.addEventListener('click', function (event) {
            if (!drawer.contains(event.target) && !openBtn.contains(event.target)) {
                drawer.classList.remove('is-open');
            }
        });
    }

    function initPenaltyCalculator() {
        var hoursInput = document.querySelector('[data-calculator-hours]');
        var unitsInput = document.querySelector('[data-calculator-units]');
        var resultEl = document.querySelector('[data-calculator-result]');

        if (!hoursInput || !unitsInput || !resultEl) {
            return;
        }

        var rate = parseInt(hoursInput.getAttribute('data-rate') || '10000', 10);
        var subsidy = parseInt(hoursInput.getAttribute('data-subsidy') || '10', 10);

        function calc() {
            var hours = parseFloat(hoursInput.value) || 0;
            var units = parseInt(unitsInput.value) || 0;
            var subtotal = hours * units * rate;
            var subsidyAmt = Math.round(subtotal * subsidy / 100);
            var total = subtotal - subsidyAmt;

            resultEl.textContent = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
            }).format(total);
        }

        hoursInput.addEventListener('input', calc);
        unitsInput.addEventListener('input', calc);
        calc();
    }

    function initQrRender() {
        var qrSvg = document.getElementById('qr-svg');
        var qrData = document.querySelector('[data-qr-token]');

        if (!qrSvg || !qrData) {
            return;
        }

        var token = qrData.getAttribute('data-qr-token');
        var size = parseInt(qrSvg.getAttribute('data-size') || '200', 10);

        drawQR(qrSvg, token, size);
    }

    function drawQR(svg, text, size) {
        var data = encodeQR(text);
        var dim = Math.sqrt(data.length);
        var cell = size / dim;
        var html = '';

        for (var y = 0; y < dim; y++) {
            for (var x = 0; x < dim; x++) {
                if (data[y * dim + x] === '1') {
                    html += '<rect x="' + (x * cell) + '" y="' + (y * cell) + '" width="' + cell + '" height="' + cell + '" fill="#000" />';
                }
            }
        }

        svg.setAttribute('width', size);
        svg.setAttribute('height', size);
        svg.innerHTML = html;
    }

    function encodeQR(text) {
        var size = 21;
        var grid = [];

        for (var i = 0; i < size * size; i++) {
            grid.push('0');
        }

        var idx = 0;
        for (var i = 0; i < text.length; i++) {
            var ch = text.charCodeAt(i);
            for (var b = 7; b >= 0; b--) {
                grid[idx] = (ch >> b) & 1 ? '1' : '0';
                idx++;
            }
        }

        return grid;
    }

    function initPaymentToggle() {
        document.querySelectorAll('[data-payment-method]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var method = btn.getAttribute('data-payment-method');
                var panels = document.querySelectorAll('[data-payment-panel]');

                panels.forEach(function (panel) {
                    panel.hidden = panel.getAttribute('data-payment-panel') !== method;
                });

                document.querySelectorAll('[data-payment-method]').forEach(function (b) {
                    b.classList.toggle('is-active', b === btn);
                });
            });
        });
    }

    function initPaymentStatusReload() {
        var btn = document.querySelector('[data-reload-payment]');
        if (!btn) {
            return;
        }

        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-reload-payment');
            fetch(url, {
                headers: { 'Accept': 'application/json' },
            }).then(function (res) {
                return res.ok ? res.json() : null;
            }).then(function (data) {
                if (data && data.paid) {
                    location.reload();
                }
            });
        });
    }

    function initFilePreview() {
        document.querySelectorAll('[data-file-input]').forEach(function (input) {
            var preview = document.getElementById(input.getAttribute('data-file-input'));
            if (!preview) {
                return;
            }

            input.addEventListener('change', function () {
                var file = input.files[0];
                if (!file) {
                    preview.textContent = '';
                    return;
                }
                preview.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
            });
        });
    }

    function showToast(message) {
        var toast = document.createElement('div');
        toast.className = 'ldr-toast ldr-toast--success';
        toast.setAttribute('role', 'status');
        toast.innerHTML = '<x-icon name="check-circle" size="18"></x-icon><p>' + message + '</p><button type="button" class="ldr-toast__close" data-toast-close aria-label="Tutup"><x-icon name="x" size="16"></x-icon></button>';

        var stack = document.querySelector('[data-toast]');
        if (stack) {
            stack.appendChild(toast);
            stack.hidden = false;
            window.setTimeout(function () { toast.hidden = true; }, 3000);
        }
    }
})();
