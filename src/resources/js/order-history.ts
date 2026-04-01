const syncOrderHistory = async (url: string): Promise<void> => {
    const current = document.querySelector<HTMLElement>('[data-order-history]');

    if (!current) {
        return;
    }

    current.setAttribute('aria-busy', 'true');

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
        const fragment = new DOMParser().parseFromString(html, 'text/html');
        const next = fragment.querySelector<HTMLElement>('[data-order-history]');

        if (!next) {
            window.location.href = url;
            return;
        }

        current.innerHTML = next.innerHTML;

        const nextTitle = fragment.querySelector('title')?.textContent;

        if (nextTitle) {
            document.title = nextTitle;
        }
    } finally {
        current.removeAttribute('aria-busy');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        const chip = target.closest<HTMLAnchorElement>('[data-order-history-chips] a.chip');
        const paginationLink = target.closest<HTMLAnchorElement>('[data-order-history] .pagination-wrap a');
        const link = chip ?? paginationLink;

        if (!link) {
            return;
        }

        const url = link.href;

        if (!url || link.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        void syncOrderHistory(url);
        window.history.pushState({ orderHistoryUrl: url }, '', url);
    });

    window.addEventListener('popstate', () => {
        if (!window.location.pathname.startsWith('/orders')) {
            return;
        }

        void syncOrderHistory(window.location.href);
    });
});
