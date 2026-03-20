type AdminCategoryEditTrigger = HTMLButtonElement & {
    dataset: {
        updateUrl?: string;
        name?: string;
        slug?: string;
        description?: string;
    };
};

const bindAdminCategoryModal = (): void => {
    const modal = document.querySelector<HTMLElement>('[data-admin-category-modal]');
    const form = document.querySelector<HTMLFormElement>('[data-admin-category-form]');
    const methodField = document.querySelector<HTMLInputElement>('[data-admin-category-method]');
    const contextField = document.querySelector<HTMLInputElement>('[data-admin-category-context]');
    const updateUrlField = document.querySelector<HTMLInputElement>('[data-admin-category-update-url]');
    const title = document.querySelector<HTMLElement>('[data-admin-category-modal-title]');
    const submitButton = document.querySelector<HTMLElement>('[data-admin-category-submit]');

    if (!modal || !form || !methodField || !contextField || !updateUrlField || !title || !submitButton) {
        return;
    }

    const fields = {
        name: form.querySelector<HTMLInputElement>('#name'),
        slug: form.querySelector<HTMLInputElement>('#slug'),
        description: form.querySelector<HTMLTextAreaElement>('#description'),
    };

    const openCreateModal = (): void => {
        form.action = form.dataset.storeUrl ?? form.action;
        methodField.disabled = true;
        contextField.value = 'create';
        updateUrlField.value = '';
        title.textContent = 'Add catalog';
        submitButton.textContent = 'Create catalog';

        if (fields.name) fields.name.value = '';
        if (fields.slug) fields.slug.value = '';
        if (fields.description) fields.description.value = '';

        modal.hidden = false;
    };

    const openEditModal = (trigger: AdminCategoryEditTrigger): void => {
        form.action = trigger.dataset.updateUrl ?? '';
        methodField.disabled = false;
        contextField.value = 'edit';
        updateUrlField.value = trigger.dataset.updateUrl ?? '';
        title.textContent = `Edit ${trigger.dataset.name ?? 'catalog'}`;
        submitButton.textContent = 'Save catalog';

        if (fields.name) fields.name.value = trigger.dataset.name ?? '';
        if (fields.slug) fields.slug.value = trigger.dataset.slug ?? '';
        if (fields.description) fields.description.value = trigger.dataset.description ?? '';

        modal.hidden = false;
    };

    const closeModal = (): void => {
        modal.hidden = true;
    };

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.closest('[data-admin-category-create-open]')) {
            openCreateModal();
            return;
        }

        const editTrigger = target.closest<AdminCategoryEditTrigger>('[data-admin-category-edit-open]');

        if (editTrigger) {
            openEditModal(editTrigger);
            return;
        }

        if (target.closest('[data-admin-category-close]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
};

document.addEventListener('DOMContentLoaded', bindAdminCategoryModal);
