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

document.addEventListener('DOMContentLoaded', bindCatalogNavigation);
