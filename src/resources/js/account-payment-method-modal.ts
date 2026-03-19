type PaymentMethodDeleteTrigger = HTMLButtonElement & {
    dataset: {
        deleteUrl?: string;
        paymentLabel?: string;
    };
};

const bindPaymentMethodDeleteModal = (): void => {
    const modal = document.querySelector<HTMLElement>('[data-payment-method-delete-modal]');
    const form = document.querySelector<HTMLFormElement>('[data-payment-method-delete-form]');
    const copy = document.querySelector<HTMLElement>('[data-payment-method-delete-copy]');

    if (!modal || !form || !copy) {
        return;
    }

    const openModal = (trigger: PaymentMethodDeleteTrigger): void => {
        form.action = trigger.dataset.deleteUrl ?? '';
        copy.textContent = `Are you sure you want to remove ${trigger.dataset.paymentLabel ?? 'this saved card'}?`;
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

        const deleteTrigger = target.closest<PaymentMethodDeleteTrigger>('[data-payment-method-delete-open]');

        if (deleteTrigger) {
            openModal(deleteTrigger);
            return;
        }

        if (target.closest('[data-payment-method-delete-close]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
};

document.addEventListener('DOMContentLoaded', bindPaymentMethodDeleteModal);
