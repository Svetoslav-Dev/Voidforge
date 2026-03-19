const confirmationText = 'I am sure';

const forms = document.querySelectorAll<HTMLFormElement>('[data-account-delete-form]');

forms.forEach((form) => {
    const modal = document.querySelector<HTMLElement>('[data-account-delete-modal]');
    const openButton = form.querySelector<HTMLButtonElement>('[data-account-delete-open]');
    const input = modal?.querySelector<HTMLInputElement>('[data-account-delete-input]');
    const submitButton = modal?.querySelector<HTMLButtonElement>('[data-account-delete-submit]');
    const closeButtons = modal?.querySelectorAll<HTMLElement>('[data-account-delete-close]');

    if (!modal || !openButton || !input || !submitButton || !closeButtons?.length) {
        return;
    }

    const syncState = () => {
        submitButton.disabled = input.value.trim() !== confirmationText;
    };

    const closeModal = () => {
        modal.hidden = true;
        input.value = '';
        syncState();
    };

    openButton.addEventListener('click', () => {
        modal.hidden = false;
        syncState();
        input.focus();
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    input.addEventListener('input', syncState);

    submitButton.addEventListener('click', () => {
        if (submitButton.disabled) {
            return;
        }

        form.submit();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
});
