document.querySelectorAll('[data-auto-dismiss]').forEach(function (element) {
    var timeout = Number.parseInt(element.dataset.autoDismiss || '5000', 10);

    window.setTimeout(function () {
        element.classList.add('opacity-0');

        window.setTimeout(function () {
            element.remove();
        }, 300);
    }, timeout);
});

function openDialog(dialog) {
    if (! dialog) {
        return;
    }

    dialog.classList.remove('hidden');
    dialog.classList.add('flex');
}

function closeDialog(dialog) {
    dialog.classList.add('hidden');
    dialog.classList.remove('flex');
}

document.querySelectorAll('[data-completion-choice-open]').forEach(function (button) {
    button.addEventListener('click', function () {
        openDialog(document.getElementById(button.dataset.completionChoiceOpen));
    });
});    

document.querySelectorAll('[data-task-card-open]').forEach(function (button) {
    button.addEventListener('click', function () {
        openDialog(document.getElementById(button.dataset.taskCardOpen));
    });
});

document.querySelectorAll('[data-task-card]').forEach(function (dialog) {
    var closeCurrentDialog = function () {
        closeDialog(dialog);
    };

dialog.querySelectorAll('[data-task-card-close]').forEach(function (button) {
        button.addEventListener('click', closeCurrentDialog);
    });
    
    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) {
            closeCurrentDialog();
        }
    });
});
   

document.querySelectorAll('[data-completion-choice]').forEach(function (dialog) {
    var closeCurrentDialog = function () {
    closeDialog(dialog);
    };

    dialog.querySelectorAll('[data-completion-choice-close]').forEach(function (button) {
        button.addEventListener('click', closeCurrentDialog);
    });

    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) {
            closeCurrentDialog();
        }
    });
});
