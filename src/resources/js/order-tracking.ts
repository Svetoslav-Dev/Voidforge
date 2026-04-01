document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector<HTMLFormElement>('[data-order-tracking-form]');

    if (!form) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const result = document.querySelector<HTMLElement>('[data-order-tracking-result]');

        if (!result) {
            form.submit();
            return;
        }

        const submitButton = form.querySelector<HTMLButtonElement>('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });

            if (!response.ok) {
                form.submit();
                return;
            }

            const html = await response.text();
            const fragment = new DOMParser().parseFromString(html, 'text/html');
            const next = fragment.querySelector<HTMLElement>('[data-order-tracking-result]');

            if (!next) {
                form.submit();
                return;
            }

            result.innerHTML = next.innerHTML;
            result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch {
            form.submit();
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    });
});
