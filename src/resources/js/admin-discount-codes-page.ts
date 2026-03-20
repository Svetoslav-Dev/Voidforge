const refreshAdminDiscountCodesPage = async (url: string, options?: RequestInit): Promise<void> => {
    const currentPage = document.querySelector<HTMLElement>('[data-admin-discount-codes-page]');

    if (!currentPage) {
        window.location.href = url;
        return;
    }

    currentPage.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html',
                ...(options?.headers ?? {}),
            },
            ...options,
        });

        if (!response.ok) {
            window.location.href = url;
            return;
        }

        const html = await response.text();
        const documentFragment = new DOMParser().parseFromString(html, 'text/html');
        const nextPage = documentFragment.querySelector<HTMLElement>('[data-admin-discount-codes-page]');

        if (!nextPage) {
            window.location.href = url;
            return;
        }

        currentPage.innerHTML = nextPage.innerHTML;

        const nextTitle = documentFragment.querySelector('title')?.textContent;

        if (nextTitle) {
            document.title = nextTitle;
        }
    } finally {
        currentPage.removeAttribute('aria-busy');
    }
};

const bindAdminDiscountCodesPage = (): void => {
    document.addEventListener('submit', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLFormElement)) {
            return;
        }

        if (target.matches('[data-admin-discount-codes-search-form]')) {
            event.preventDefault();

            const formData = new FormData(target);
            const searchParams = new URLSearchParams();

            formData.forEach((value, key) => {
                if (typeof value === 'string' && value !== '') {
                    searchParams.set(key, value);
                }
            });

            const url = searchParams.toString() === ''
                ? target.action
                : `${target.action}?${searchParams.toString()}`;

            void refreshAdminDiscountCodesPage(url);
            window.history.pushState({ adminDiscountCodesUrl: url }, '', url);
            return;
        }

        if (target.matches('[data-admin-discount-codes-toggle-form]')) {
            event.preventDefault();

            void (async () => {
                const response = await fetch(target.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'text/html',
                    },
                    body: new FormData(target),
                });

                if (!response.ok) {
                    target.submit();
                    return;
                }

                await refreshAdminDiscountCodesPage(window.location.href);
            })();
        }
    });

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        const paginationLink = target.closest<HTMLAnchorElement>('[data-admin-discount-codes-page] .pagination a');

        if (!paginationLink) {
            return;
        }

        const url = paginationLink.href;

        if (!url || paginationLink.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        void refreshAdminDiscountCodesPage(url);
        window.history.pushState({ adminDiscountCodesUrl: url }, '', url);
    });

    window.addEventListener('popstate', () => {
        if (!window.location.pathname.startsWith('/admin/discount-codes')) {
            return;
        }

        void refreshAdminDiscountCodesPage(window.location.href);
    });
};

document.addEventListener('DOMContentLoaded', bindAdminDiscountCodesPage);
