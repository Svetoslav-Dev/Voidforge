type AddressEditTrigger = HTMLButtonElement & {
    dataset: {
        updateUrl?: string;
        label?: string;
        recipientName?: string;
        phone?: string;
        addressLine1?: string;
        addressLine2?: string;
        city?: string;
        state?: string;
        postalCode?: string;
        country?: string;
        isDefault?: string;
    };
};

type AddressDeleteTrigger = HTMLButtonElement & {
    dataset: {
        deleteUrl?: string;
        addressLabel?: string;
    };
};

const bindAddressEditModal = (): void => {
    const modal = document.querySelector<HTMLElement>('[data-address-edit-modal]');
    const form = document.querySelector<HTMLFormElement>('[data-address-edit-form]');
    const createModal = document.querySelector<HTMLElement>('[data-address-create-modal]');
    const deleteModal = document.querySelector<HTMLElement>('[data-address-delete-modal]');
    const deleteForm = document.querySelector<HTMLFormElement>('[data-address-delete-form]');
    const deleteCopy = document.querySelector<HTMLElement>('[data-address-delete-copy]');

    if (!modal || !form || !createModal || !deleteModal || !deleteForm || !deleteCopy) {
        return;
    }

    const fields = {
        label: form.querySelector<HTMLInputElement>('#edit_label'),
        recipientName: form.querySelector<HTMLInputElement>('#edit_recipient_name'),
        phone: form.querySelector<HTMLInputElement>('#edit_phone'),
        addressLine1: form.querySelector<HTMLInputElement>('#edit_address_line_1'),
        addressLine2: form.querySelector<HTMLInputElement>('#edit_address_line_2'),
        city: form.querySelector<HTMLInputElement>('#edit_city'),
        state: form.querySelector<HTMLInputElement>('#edit_state'),
        postalCode: form.querySelector<HTMLInputElement>('#edit_postal_code'),
        country: form.querySelector<HTMLSelectElement>('#edit_country'),
        isDefault: form.querySelector<HTMLInputElement>('#edit_is_default'),
    };

    const openModal = (trigger: AddressEditTrigger): void => {
        form.action = trigger.dataset.updateUrl ?? '';
        if (fields.label) fields.label.value = trigger.dataset.label ?? '';
        if (fields.recipientName) fields.recipientName.value = trigger.dataset.recipientName ?? '';
        if (fields.phone) fields.phone.value = trigger.dataset.phone ?? '';
        if (fields.addressLine1) fields.addressLine1.value = trigger.dataset.addressLine1 ?? '';
        if (fields.addressLine2) fields.addressLine2.value = trigger.dataset.addressLine2 ?? '';
        if (fields.city) fields.city.value = trigger.dataset.city ?? '';
        if (fields.state) fields.state.value = trigger.dataset.state ?? '';
        if (fields.postalCode) fields.postalCode.value = trigger.dataset.postalCode ?? '';
        if (fields.country) fields.country.value = trigger.dataset.country ?? 'BG';
        if (fields.isDefault) fields.isDefault.checked = trigger.dataset.isDefault === '1';

        modal.hidden = false;
    };

    const closeModal = (): void => {
        modal.hidden = true;
    };

    const openCreateModal = (): void => {
        createModal.hidden = false;
    };

    const closeCreateModal = (): void => {
        createModal.hidden = true;
    };

    const openDeleteModal = (trigger: AddressDeleteTrigger): void => {
        deleteForm.action = trigger.dataset.deleteUrl ?? '';
        deleteCopy.textContent = `Are you sure you want to archive ${trigger.dataset.addressLabel ?? 'this address'}?`;
        deleteModal.hidden = false;
    };

    const closeDeleteModal = (): void => {
        deleteModal.hidden = true;
    };

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        const openTrigger = target.closest<AddressEditTrigger>('[data-address-edit-open]');
        const deleteTrigger = target.closest<AddressDeleteTrigger>('[data-address-delete-open]');

        if (openTrigger) {
            openModal(openTrigger);
            return;
        }

        if (deleteTrigger) {
            openDeleteModal(deleteTrigger);
            return;
        }

        if (target.closest('[data-address-create-open]')) {
            openCreateModal();
            return;
        }

        if (target.closest('[data-address-edit-close]')) {
            closeModal();
            return;
        }

        if (target.closest('[data-address-create-close]')) {
            closeCreateModal();
            return;
        }

        if (target.closest('[data-address-delete-close]')) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }

        if (event.key === 'Escape' && !createModal.hidden) {
            closeCreateModal();
        }

        if (event.key === 'Escape' && !deleteModal.hidden) {
            closeDeleteModal();
        }
    });

    document.addEventListener('submit', async (event) => {
        const target = event.target;

        if (!(target instanceof HTMLFormElement) || !target.matches('[data-address-default-form]')) {
            return;
        }

        event.preventDefault();

        const submitButton = target.querySelector<HTMLButtonElement>('button[type="submit"]');
        const currentCard = target.closest<HTMLElement>('[data-address-card]');
        submitButton?.setAttribute('disabled', 'disabled');

        try {
            const response = await fetch(target.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': target.querySelector<HTMLInputElement>('input[name="_token"]')?.value ?? '',
                    'Accept': 'application/json',
                },
                body: new FormData(target),
            });

            if (!response.ok) {
                target.submit();
                return;
            }

            const payload = await response.json() as { address_id?: number };
            const activeAddressId = payload.address_id ?? Number(currentCard?.dataset.addressId ?? 0);
            const cards = document.querySelectorAll<HTMLElement>('[data-address-card]');

            cards.forEach((card) => {
                const badge = card.querySelector<HTMLElement>('[data-address-default-badge]');
                const form = card.querySelector<HTMLElement>('[data-address-default-form]');
                const isCurrent = Number(card.dataset.addressId) === activeAddressId;

                if (badge) {
                    badge.style.display = isCurrent ? '' : 'none';
                }

                if (form) {
                    form.hidden = isCurrent;
                }
            });
        } finally {
            submitButton?.removeAttribute('disabled');
        }
    });
};

document.addEventListener('DOMContentLoaded', bindAddressEditModal);
