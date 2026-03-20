const syncCatalog = async (url: string): Promise<void> => {
    const currentCatalog = document.querySelector<HTMLElement>('[data-product-catalog]');

    if (!currentCatalog) {
        return;
    }

    currentCatalog.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            window.location.href = url;
            return;
        }

        const html = await response.text();
        const documentFragment = new DOMParser().parseFromString(html, 'text/html');
        const nextCatalog = documentFragment.querySelector<HTMLElement>('[data-product-catalog]');

        if (!nextCatalog) {
            window.location.href = url;
            return;
        }

        currentCatalog.innerHTML = nextCatalog.innerHTML;

        const nextTitle = documentFragment.querySelector('title')?.textContent;

        if (nextTitle) {
            document.title = nextTitle;
        }
    } finally {
        currentCatalog.removeAttribute('aria-busy');
    }
};

const bindCatalogNavigation = (): void => {
    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        const chip = target.closest<HTMLAnchorElement>('[data-product-catalog-chips] a.chip');

        if (!chip) {
            return;
        }

        const url = chip.href;

        if (!url || chip.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        void syncCatalog(url);
        window.history.pushState({ productCatalogUrl: url }, '', url);
    });

    window.addEventListener('popstate', () => {
        if (!window.location.pathname.startsWith('/products')) {
            return;
        }

        void syncCatalog(window.location.href);
    });
};

const bindCatalogAddToCart = (): void => {
    document.addEventListener('submit', async (event) => {
        const target = event.target;

        if (!(target instanceof HTMLFormElement)) {
            return;
        }

        if (!target.matches('[data-ajax-add-to-cart]')) {
            return;
        }

        event.preventDefault();

        const submitButton = target.querySelector<HTMLButtonElement>('button[type="submit"]');
        const originalLabel = submitButton?.textContent ?? 'Add to cart';

        submitButton?.setAttribute('disabled', 'disabled');

        try {
            const response = await fetch(target.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: new FormData(target),
            });

            if (!response.ok) {
                window.location.href = target.action;
                return;
            }

            const payload = await response.json() as { item_count?: number; status?: string };
            const itemCount = Number(payload.item_count ?? 0);
            const navGroup = document.querySelector<HTMLElement>('[data-nav-group]');
            let cartLink = document.querySelector<HTMLAnchorElement>('[data-cart-link]');

            if (itemCount > 0) {
                if (!cartLink && navGroup) {
                    cartLink = document.createElement('a');
                    cartLink.className = 'button secondary';
                    cartLink.href = '/cart';
                    cartLink.dataset.cartLink = 'true';
                    navGroup.appendChild(cartLink);
                }

                if (cartLink) {
                    cartLink.textContent = `Cart (${itemCount})`;
                }
            }

            if (submitButton) {
                submitButton.textContent = 'Added';
                window.setTimeout(() => {
                    submitButton.textContent = originalLabel;
                }, 1200);
            }
        } finally {
            window.setTimeout(() => {
                submitButton?.removeAttribute('disabled');
            }, 200);
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    bindCatalogNavigation();
    bindCatalogAddToCart();
});
