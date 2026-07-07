document.querySelectorAll('[data-auto-dismiss]').forEach((element) => {
    const timeout = Number.parseInt(element.dataset.autoDismiss ?? '5000', 10);

    window.setTimeout(() => {
        element.classList.add('opacity-0');

        window.setTimeout(() => {
            element.remove();
        }, 300);
    }, timeout);
});
