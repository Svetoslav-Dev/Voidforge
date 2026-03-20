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

const bindPaymentMethodDefaultForms = (): void => {
    const defaultForms = Array.from(
        document.querySelectorAll<HTMLFormElement>('[data-payment-method-default-form]')
    );

    if (defaultForms.length === 0) {
        return;
    }

    defaultForms.forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: new FormData(form),
            });

            if (!response.ok) {
                window.location.reload();
                return;
            }

            const payload = await response.json() as { payment_method_id?: number };
            const activeId = String(payload.payment_method_id ?? '');

            document.querySelectorAll<HTMLElement>('[data-payment-method-card]').forEach((card) => {
                const isActive = card.dataset.paymentMethodId === activeId;
                const badge = card.querySelector<HTMLElement>('[data-payment-method-default-badge]');
                const defaultForm = card.querySelector<HTMLElement>('[data-payment-method-default-form]');

                if (badge) {
                    badge.style.display = isActive ? '' : 'none';
                }

                if (defaultForm) {
                    defaultForm.hidden = isActive;
                }
            });
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    bindPaymentMethodDeleteModal();
    bindPaymentMethodDefaultForms();
});
