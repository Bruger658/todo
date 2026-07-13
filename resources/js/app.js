document.querySelectorAll('[data-auto-dismiss]').forEach((element) => {
    const timeout = Number.parseInt(element.dataset.autoDismiss ?? '5000', 10);

    window.setTimeout(() => {
        element.classList.add('opacity-0');

        window.setTimeout(() => {
            element.remove();
        }, 300);
    }, timeout);
});

document.querySelectorAll('[data-completion-choice-open]').forEach((button) => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.completionChoiceOpen);

        dialog?.classList.remove('hidden');
        dialog?.classList.add('flex');
    });
});

document.querySelectorAll('[data-task-card-open]').forEach((button) => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.taskCardOpen);
        
        dialog?.classList.remove('hidden');
        dialog?.classList.add('flex');       
    });
});

document.querySelectorAll('[data-task-card]').forEach((dialog) => {
    const closeDialog = () => {
        dialog.classList.add('hidden');
        dialog.classList.remove('flex');
    };

    dialog.querySelectorAll('[data-task-card-close]').forEach((button) => {
        button.addEventListener('click', closeDialog);
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            closeDialog();
        }
    });
});


document.querySelectorAll('[data-completion-choice]').forEach((dialog) => {
    const closeDialog = () => {
        dialog.classList.add('hidden');
        dialog.classList.remove('flex');
    };

    dialog.querySelectorAll('[data-completion-choice-close]').forEach((button) => {
        button.addEventListener('click', closeDialog);
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            closeDialog();
        }
    });
});
