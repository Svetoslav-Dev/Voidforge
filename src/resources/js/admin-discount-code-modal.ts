type DiscountCodeEditTrigger = HTMLButtonElement & {
    dataset: {
        updateUrl?: string;
        code?: string;
        description?: string;
        type?: string;
        amount?: string;
        usageLimit?: string;
        expiresAt?: string;
        isActive?: string;
    };
};

const bindAdminDiscountCodeModal = (): void => {
    const modal = document.querySelector<HTMLElement>('[data-discount-code-modal]');
    const form = document.querySelector<HTMLFormElement>('[data-discount-code-form]');
    const methodField = document.querySelector<HTMLInputElement>('[data-discount-code-method]');
    const contextField = document.querySelector<HTMLInputElement>('[data-discount-code-context]');
    const updateUrlField = document.querySelector<HTMLInputElement>('[data-discount-code-update-url]');
    const title = document.querySelector<HTMLElement>('[data-discount-code-modal-title]');
    const submitButton = document.querySelector<HTMLElement>('[data-discount-code-submit]');

    if (!modal || !form || !methodField || !contextField || !updateUrlField || !title || !submitButton) {
        return;
    }

    const fields = {
        code: form.querySelector<HTMLInputElement>('#code'),
        description: form.querySelector<HTMLInputElement>('#description'),
        type: form.querySelector<HTMLSelectElement>('#type'),
        amount: form.querySelector<HTMLInputElement>('#amount'),
        usageLimit: form.querySelector<HTMLInputElement>('#usage_limit'),
        expiresAt: form.querySelector<HTMLInputElement>('#expires_at'),
        isActive: form.querySelector<HTMLInputElement>('#is_active'),
    };

    const openCreateModal = (): void => {
        form.action = form.dataset.storeUrl ?? form.action;
        methodField.disabled = true;
        contextField.value = 'create';
        updateUrlField.value = '';
        title.textContent = 'Add discount code';
        submitButton.textContent = 'Add code';

        if (fields.code) fields.code.value = '';
        if (fields.description) fields.description.value = '';
        if (fields.type) fields.type.value = 'percent';
        if (fields.amount) fields.amount.value = '';
        if (fields.usageLimit) fields.usageLimit.value = '';
        if (fields.expiresAt) fields.expiresAt.value = '';
        if (fields.isActive) fields.isActive.checked = true;

        modal.hidden = false;
    };

    const openEditModal = (trigger: DiscountCodeEditTrigger): void => {
        form.action = trigger.dataset.updateUrl ?? '';
        methodField.disabled = false;
        contextField.value = 'edit';
        updateUrlField.value = trigger.dataset.updateUrl ?? '';
        title.textContent = `Edit ${trigger.dataset.code ?? 'discount code'}`;
        submitButton.textContent = 'Save code';

        if (fields.code) fields.code.value = trigger.dataset.code ?? '';
        if (fields.description) fields.description.value = trigger.dataset.description ?? '';
        if (fields.type) fields.type.value = trigger.dataset.type ?? 'percent';
        if (fields.amount) fields.amount.value = trigger.dataset.amount ?? '';
        if (fields.usageLimit) fields.usageLimit.value = trigger.dataset.usageLimit ?? '';
        if (fields.expiresAt) fields.expiresAt.value = trigger.dataset.expiresAt ?? '';
        if (fields.isActive) fields.isActive.checked = trigger.dataset.isActive !== '0';

        modal.hidden = false;
    };

    const closeModal = (): void => {
        modal.hidden = true;
    };

    form.dataset.storeUrl = form.action;

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.closest('[data-discount-code-create-open]')) {
            openCreateModal();
            return;
        }

        const editTrigger = target.closest<DiscountCodeEditTrigger>('[data-discount-code-edit-open]');
        if (editTrigger) {
            openEditModal(editTrigger);
            return;
        }

        if (target.closest('[data-discount-code-close]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
};

document.addEventListener('DOMContentLoaded', bindAdminDiscountCodeModal);
