(function () {
    initModals();
    initDateRanges();
    initToasts();

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
})();
