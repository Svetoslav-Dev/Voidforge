const bindAdminUserModal = (): void => {
    const modal = document.querySelector<HTMLElement>('[data-admin-user-modal]');

    if (!modal) {
        return;
    }

    const openModal = (): void => {
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

        if (target.closest('[data-admin-user-create-open]')) {
            openModal();
            return;
        }

        if (target.closest('[data-admin-user-close]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
};

document.addEventListener('DOMContentLoaded', bindAdminUserModal);
