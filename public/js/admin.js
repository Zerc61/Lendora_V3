(function () {
    initModals();
    initDateRanges();
    initAutoSubmit();
    initToasts();
    initOperatorTabs();
    initClocks();
    initKioskShortcuts();
    initSignaturePads();
    initQueuePolling();
    initDynamicRows();
    initGanttPanel();

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

    function initOperatorTabs() {
        document.querySelectorAll('[data-op-tabs]').forEach(function (tabs) {
            var target = document.querySelector(tabs.getAttribute('data-op-target') || '');

            if (!target) {
                return;
            }

            tabs.addEventListener('click', function (event) {
                var button = event.target.closest('[data-op-tab]');

                if (!button) {
                    return;
                }

                var key = button.getAttribute('data-op-tab');

                tabs.querySelectorAll('[data-op-tab]').forEach(function (tab) {
                    var active = tab === button;
                    tab.classList.toggle('is-active', active);
                    tab.setAttribute('aria-selected', String(active));
                });

                target.querySelectorAll('[data-op-row]').forEach(function (row) {
                    row.hidden = key !== 'all' && row.getAttribute('data-op-row') !== key;
                });
            });
        });
    }

    function initClocks() {
        var clocks = document.querySelectorAll('[data-clock]');

        if (!clocks.length) {
            return;
        }

        function pad(value) {
            return value < 10 ? '0' + value : String(value);
        }

        function tick() {
            var now = new Date();
            var text = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ' WIB';

            clocks.forEach(function (clock) {
                var base = clock.getAttribute('data-clock-date') || clock.textContent.trim();
                clock.setAttribute('data-clock-date', base);
                clock.textContent = base + ' · ' + text;
            });
        }

        tick();
        setInterval(tick, 30000);
    }

    function initKioskShortcuts() {
        var scan = document.querySelector('[data-kiosk-scan]');

        if (!scan) {
            return;
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'F1') {
                event.preventDefault();
                scan.focus();
                scan.select();
            }

            if (event.key === 'Escape') {
                scan.value = '';
                scan.focus();
            }
        });
    }

    function initSignaturePads() {
        document.querySelectorAll('[data-signature]').forEach(function (canvas) {
            var key = canvas.getAttribute('data-signature');
            var input = document.querySelector('[data-signature-input="' + key + '"]');
            var clear = document.querySelector('[data-signature-clear="' + key + '"]');
            var context = canvas.getContext('2d');
            var drawing = false;

            if (!input || !context) {
                return;
            }

            context.lineWidth = 2;
            context.lineCap = 'round';
            context.lineJoin = 'round';
            context.strokeStyle = '#262626';

            function point(event) {
                var rect = canvas.getBoundingClientRect();

                return {
                    x: (event.clientX - rect.left) * (canvas.width / rect.width),
                    y: (event.clientY - rect.top) * (canvas.height / rect.height)
                };
            }

            canvas.addEventListener('pointerdown', function (event) {
                event.preventDefault();
                drawing = true;
                canvas.setPointerCapture(event.pointerId);

                var spot = point(event);
                context.beginPath();
                context.moveTo(spot.x, spot.y);
            });

            canvas.addEventListener('pointermove', function (event) {
                if (!drawing) {
                    return;
                }

                var spot = point(event);
                context.lineTo(spot.x, spot.y);
                context.stroke();
            });

            function finish() {
                if (!drawing) {
                    return;
                }

                drawing = false;
                input.value = canvas.toDataURL('image/png');
            }

            canvas.addEventListener('pointerup', finish);
            canvas.addEventListener('pointerleave', finish);
            canvas.addEventListener('pointercancel', finish);

            if (clear) {
                clear.addEventListener('click', function () {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    input.value = '';
                });
            }
        });
    }

    function initQueuePolling() {
        document.querySelectorAll('[data-queue-poll]').forEach(function (root) {
            var seconds = parseInt(root.getAttribute('data-queue-poll'), 10);
            var toggle = document.querySelector('[data-queue-poll-toggle]');

            if (!seconds || seconds < 10) {
                return;
            }

            setInterval(function () {
                if (document.hidden || (toggle && !toggle.checked)) {
                    return;
                }

                window.location.reload();
            }, seconds * 1000);
        });
    }

    function initGanttPanel() {
        var panel = document.querySelector('[data-gantt-panel]');

        if (!panel) {
            return;
        }

        var fields = {
            code: panel.querySelector('[data-gantt-field="code"]'),
            asset: panel.querySelector('[data-gantt-field="asset"]'),
            period: panel.querySelector('[data-gantt-field="period"]'),
            status: panel.querySelector('[data-gantt-field="status"]')
        };
        var link = panel.querySelector('[data-gantt-link]');

        document.querySelectorAll('[data-gantt-bar]').forEach(function (bar) {
            bar.addEventListener('click', function (event) {
                if (fields.code) { fields.code.textContent = bar.dataset.code || ''; }
                if (fields.asset) { fields.asset.textContent = bar.dataset.asset + ' · ' + bar.dataset.name; }
                if (fields.period) { fields.period.textContent = bar.dataset.start + ' → ' + bar.dataset.end; }
                if (fields.status) { fields.status.textContent = bar.dataset.status || ''; }

                if (link) {
                    if (bar.dataset.url) {
                        link.href = bar.dataset.url;
                        link.hidden = false;
                    } else {
                        link.hidden = true;
                    }
                }

                if (bar.tagName === 'A') {
                    event.preventDefault();
                    panel.dataset.selectedUrl = bar.href;
                }
            });
        });

        if (link) {
            link.addEventListener('click', function (event) {
                if (!link.getAttribute('href') || link.getAttribute('href') === '#') {
                    event.preventDefault();
                }
            });
        }
    }

    function initDynamicRows() {
        document.querySelectorAll('[data-dynamic-rows]').forEach(function (root) {
            var list = root.querySelector('[data-row-list]');
            var template = root.querySelector('template[data-row-template]');
            var add = root.querySelector('[data-add-row]');
            var form = root.closest('form');

            if (!list || !template || !add || !form) {
                return;
            }

            var counter = list.querySelectorAll('[data-row]').length;

            add.addEventListener('click', function () {
                var markup = template.innerHTML.replace(/__INDEX__/g, String(counter++));
                var wrapper = document.createElement('div');
                wrapper.innerHTML = markup.trim();
                list.appendChild(wrapper.firstElementChild);
            });

            list.addEventListener('click', function (event) {
                var remove = event.target.closest('[data-remove-row]');

                if (!remove || list.querySelectorAll('[data-row]').length <= 1) {
                    return;
                }

                remove.closest('[data-row]').remove();
            });

            form.addEventListener('submit', function () {
                list.querySelectorAll('[data-row]').forEach(function (row) {
                    var description = row.querySelector('input[name$="[description]"]');

                    if (description && !description.value.trim()) {
                        row.remove();
                    }
                });
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
