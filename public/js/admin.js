(function () {
    initModals();
    initDateRanges();
    initAutoSubmit();
    initToasts();

    var shell = document.querySelector('[data-admin-shell]');

    if (shell) {
        initAdminShell(shell);
        initPhotoUploaders();
        initTreeToggles();
    }

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

    function initAutoSubmit() {
        document.querySelectorAll('[data-auto-submit]').forEach(function (form) {
            form.querySelectorAll('[data-auto-submit-field]').forEach(function (field) {
                field.addEventListener('change', function () {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                });
            });
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

    function initAdminShell(shell) {
        var toggle = shell.querySelector('[data-sidebar-toggle]');
        var backdrop = shell.querySelector('[data-sidebar-backdrop]');
        var desktop = window.matchMedia('(min-width: 768px)');

        function syncExpanded() {
            if (!toggle) {
                return;
            }

            var expanded = desktop.matches
                ? !shell.classList.contains('is-collapsed')
                : shell.classList.contains('is-open');

            toggle.setAttribute('aria-expanded', String(expanded));
        }

        if (desktop.matches && localStorage.getItem('lendora.sidebar') === 'collapsed') {
            shell.classList.add('is-collapsed');
        }

        syncExpanded();

        if (toggle) {
            toggle.addEventListener('click', function () {
                if (desktop.matches) {
                    shell.classList.toggle('is-collapsed');
                    localStorage.setItem(
                        'lendora.sidebar',
                        shell.classList.contains('is-collapsed') ? 'collapsed' : 'open'
                    );
                } else {
                    shell.classList.toggle('is-open');
                }

                syncExpanded();
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', function () {
                shell.classList.remove('is-open');
                syncExpanded();
            });
        }

        desktop.addEventListener('change', function () {
            shell.classList.remove('is-open');
            syncExpanded();
        });

        shell.querySelectorAll('[data-menu-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var group = button.closest('[data-menu-group]');
                var folded = group.classList.toggle('is-folded');

                button.setAttribute('aria-expanded', String(!folded));
            });
        });
    }

    function initPhotoUploaders() {
        document.querySelectorAll('[data-photo-uploader]').forEach(function (uploader) {
            var input = uploader.querySelector('[data-photo-input]');
            var preview = uploader.querySelector('[data-photo-preview]');

            if (!input || !preview) {
                return;
            }

            var selected = [];
            var primaryIndex = 0;

            function render() {
                preview.innerHTML = '';

                selected.forEach(function (file, index) {
                    var figure = document.createElement('figure');
                    figure.className = 'ldr-photo';

                    var image = document.createElement('img');
                    image.src = URL.createObjectURL(file);
                    image.alt = file.name;
                    image.width = 160;
                    image.height = 120;
                    figure.appendChild(image);

                    var label = document.createElement('label');
                    label.className = 'ldr-photo__choice';

                    var radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = 'primary_new';
                    radio.value = String(index);
                    radio.checked = index === primaryIndex;
                    radio.addEventListener('change', function () {
                        primaryIndex = index;
                    });

                    label.appendChild(radio);
                    label.appendChild(document.createTextNode(' ' + (uploader.dataset.labelPrimary || 'Utama')));
                    figure.appendChild(label);

                    var remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'ldr-photo__remove';
                    remove.textContent = uploader.dataset.labelRemove || 'Hapus';
                    remove.addEventListener('click', function () {
                        selected.splice(index, 1);
                        primaryIndex = Math.min(primaryIndex, Math.max(0, selected.length - 1));
                        render();
                    });
                    figure.appendChild(remove);

                    preview.appendChild(figure);
                });
            }

            input.addEventListener('change', function () {
                Array.prototype.slice.call(input.files).forEach(function (file) {
                    selected.push(file);
                });

                input.value = '';
                render();
            });

            uploader.closest('form').addEventListener('submit', function () {
                if (selected.length === 0 || typeof DataTransfer === 'undefined') {
                    return;
                }

                var primary = selected[primaryIndex] || selected[0];
                var ordered = [primary].concat(selected.filter(function (file) {
                    return file !== primary;
                }));

                var transfer = new DataTransfer();
                ordered.forEach(function (file) {
                    transfer.items.add(file);
                });

                input.files = transfer.files;
            });
        });
    }

    function initTreeToggles() {
        document.querySelectorAll('[data-tree-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var item = button.closest('.ldr-tree__item');
                var folded = item.classList.toggle('is-collapsed');

                button.setAttribute('aria-expanded', String(!folded));
            });
        });
    }
})();
