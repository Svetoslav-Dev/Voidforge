const authModalRoots = Array.from(document.querySelectorAll<HTMLElement>('[data-auth-modal-root]'));
const authModalTriggers = Array.from(document.querySelectorAll<HTMLElement>('[data-auth-modal-trigger]'));

if (authModalRoots.length > 0) {
    const modalMap = new Map<string, HTMLElement>();

    authModalRoots.forEach((root) => {
        const modalName = root.dataset.authModalRoot;

        if (modalName) {
            modalMap.set(modalName, root);
        }
    });

    const setBodyScroll = (): void => {
        const hasOpenModal = authModalRoots.some((root) => !root.classList.contains('is-hidden'));
        document.body.style.overflow = hasOpenModal ? 'hidden' : '';
    };

    const closeModal = (modalName?: string): void => {
        if (modalName) {
            const root = modalMap.get(modalName);
            if (root) {
                root.classList.add('is-hidden');
            }
        } else {
            authModalRoots.forEach((root) => {
                root.classList.add('is-hidden');
            });
        }

        setBodyScroll();
    };

    const openModal = (modalName: string): void => {
        authModalRoots.forEach((root) => {
            root.classList.toggle('is-hidden', root.dataset.authModalRoot !== modalName);
        });

        const activeRoot = modalMap.get(modalName);

        requestAnimationFrame(() => {
            const emailField = activeRoot?.querySelector<HTMLInputElement>('input[type="email"]');
            const passwordField = activeRoot?.querySelector<HTMLInputElement>('input[type="password"]');
            const focusTarget = emailField?.value ? (passwordField ?? emailField) : emailField;
            focusTarget?.focus();
        });

        setBodyScroll();
    };

    authModalTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const modalName = trigger.dataset.authModalTrigger;

            if (modalName) {
                openModal(modalName);
            }
        });
    });

    authModalRoots.forEach((root) => {
        root.addEventListener('click', (event) => {
            const target = event.target as HTMLElement;

            if (target.hasAttribute('data-auth-modal-close')) {
                closeModal(root.dataset.authModalRoot);
            }

            const switchTarget = target.dataset.authModalSwitch;
            if (switchTarget) {
                openModal(switchTarget);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    authModalRoots.forEach((root) => {
        if (root.dataset.authModalOpen === 'true') {
            const modalName = root.dataset.authModalRoot;
            if (modalName) {
                openModal(modalName);
            }
        }
    });
}
