const groups = document.querySelectorAll<HTMLElement>('[data-checkout-complete-cards]');

groups.forEach((group) => {
    const shipmentCard = group.querySelector<HTMLElement>('[data-checkout-shipment-card]');
    const summaryCard = group.querySelector<HTMLElement>('[data-checkout-summary-card]');

    if (!shipmentCard || !summaryCard) {
        return;
    }

    const syncHeight = () => {
        if (window.innerWidth <= 900) {
            summaryCard.style.minHeight = '';
            summaryCard.style.height = '';
            summaryCard.style.maxHeight = '';
            return;
        }

        summaryCard.style.minHeight = '';
        summaryCard.style.height = '';
        summaryCard.style.maxHeight = '';

        const shipmentHeight = shipmentCard.getBoundingClientRect().height;

        summaryCard.style.minHeight = `${shipmentHeight}px`;
    };

    syncHeight();

    window.addEventListener('resize', syncHeight);

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(syncHeight);
        observer.observe(shipmentCard);
    }
});
