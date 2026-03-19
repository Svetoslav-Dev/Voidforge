const refreshShippingPage = async (form: HTMLFormElement): Promise<void> => {
    const currentSection = document.querySelector<HTMLElement>('[data-shipping-page]');

    if (!currentSection) {
        form.submit();
        return;
    }

    const submitButton = form.querySelector<HTMLButtonElement>('button[type="submit"]');
    submitButton?.setAttribute('disabled', 'disabled');
    currentSection.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': (form.querySelector<HTMLInputElement>('input[name="_token"]')?.value ?? ''),
            },
            body: new FormData(form),
        });

        if (!response.ok) {
            form.submit();
            return;
        }

        const html = await response.text();
        const documentFragment = new DOMParser().parseFromString(html, 'text/html');
        const nextSection = documentFragment.querySelector<HTMLElement>('[data-shipping-page]');

        if (!nextSection) {
            form.submit();
            return;
        }

        currentSection.innerHTML = nextSection.innerHTML;

        const nextTitle = documentFragment.querySelector('title')?.textContent;

        if (nextTitle) {
            document.title = nextTitle;
        }
    } finally {
        currentSection.removeAttribute('aria-busy');
        submitButton?.removeAttribute('disabled');
    }
};

const bindCheckoutAddressSelection = (): void => {
    document.addEventListener('submit', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLFormElement) || !target.matches('[data-checkout-address-form]')) {
            return;
        }

        event.preventDefault();
        void refreshShippingPage(target);
    });
};

document.addEventListener('DOMContentLoaded', bindCheckoutAddressSelection);
