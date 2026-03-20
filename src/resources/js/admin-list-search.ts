const refreshAdminListPage = async (url: string): Promise<void> => {
    const currentPage = document.querySelector<HTMLElement>('[data-admin-list-search-page]');

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
            },
        });

        if (!response.ok) {
            window.location.href = url;
            return;
        }

        const html = await response.text();
        const documentFragment = new DOMParser().parseFromString(html, 'text/html');
        const nextPage = documentFragment.querySelector<HTMLElement>('[data-admin-list-search-page]');

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

const bindAdminListSearch = (): void => {
    document.addEventListener('submit', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLFormElement) || !target.matches('[data-admin-list-search-form]')) {
            return;
        }

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

        void refreshAdminListPage(url);
        window.history.pushState({ adminListUrl: url }, '', url);
    });

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        const paginationLink = target.closest<HTMLAnchorElement>('[data-admin-list-search-page] .pagination a');

        if (!paginationLink) {
            return;
        }

        const url = paginationLink.href;

        if (!url || paginationLink.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        void refreshAdminListPage(url);
        window.history.pushState({ adminListUrl: url }, '', url);
    });

    window.addEventListener('popstate', () => {
        if (!document.querySelector('[data-admin-list-search-page]')) {
            return;
        }

        void refreshAdminListPage(window.location.href);
    });
};

document.addEventListener('DOMContentLoaded', bindAdminListSearch);
