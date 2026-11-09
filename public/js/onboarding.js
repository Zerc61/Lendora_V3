(function () {
    'use strict';

    var root = document.querySelector('[data-onboarding]');
    if (!root) {
        return;
    }

    var slides = Array.prototype.slice.call(root.querySelectorAll('[data-slide]'));
    var panels = Array.prototype.slice.call(root.querySelectorAll('[data-panel]'));
    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-go-to]'));
    var next = root.querySelector('[data-onboarding-next]');
    var nextLabel = root.querySelector('[data-next-label]');
    var index = 0;

    if (!slides.length || !panels.length || !next || !nextLabel) {
        return;
    }

    function render(focusPanel) {
        var last = index === slides.length - 1;

        slides.forEach(function (slide, i) {
            slide.hidden = i !== index;
            slide.classList.toggle('is-active', i === index);
        });

        panels.forEach(function (panel, i) {
            panel.hidden = i !== index;
            panel.classList.toggle('is-active', i === index);
        });

        dots.forEach(function (dot, i) {
            dot.classList.toggle('is-active', i === index);

            if (i === index) {
                dot.setAttribute('aria-current', 'step');
            } else {
                dot.removeAttribute('aria-current');
            }
        });

        nextLabel.textContent = last ? next.dataset.labelFinish : next.dataset.labelNext;

        if (focusPanel) {
            var heading = panels[index].querySelector('h1');
            if (heading) {
                heading.focus({ preventScroll: true });
            }
        }
    }

    function goTo(nextIndex, focusPanel) {
        index = Math.max(0, Math.min(slides.length - 1, nextIndex));
        render(focusPanel);
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(Number(dot.dataset.goTo), true);
        });
    });

    next.addEventListener('click', function () {
        if (index < slides.length - 1) {
            goTo(index + 1, true);
            return;
        }

        window.location.href = root.dataset.loginUrl;
    });

    document.addEventListener('keydown', function (event) {
        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.altKey) {
            return;
        }

        if (event.key === 'ArrowRight') {
            goTo(index + 1, true);
        } else if (event.key === 'ArrowLeft') {
            goTo(index - 1, true);
        }
    });

    render(false);
})();
