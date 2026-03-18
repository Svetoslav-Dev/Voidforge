type GalleryThumb = HTMLButtonElement & {
    dataset: {
        imageUrl?: string;
        imageAlt?: string;
    };
};

const updateZoomOrigin = (frame: HTMLElement, event: MouseEvent): void => {
    const rect = frame.getBoundingClientRect();
    const x = ((event.clientX - rect.left) / rect.width) * 100;
    const y = ((event.clientY - rect.top) / rect.height) * 100;

    const image = frame.querySelector<HTMLImageElement>('[data-gallery-main]');

    if (image) {
        image.style.transformOrigin = `${x}% ${y}%`;
    }
};

const bindGallery = (gallery: HTMLElement): void => {
    const frame = gallery.querySelector<HTMLElement>('[data-gallery-frame]');
    const mainImage = gallery.querySelector<HTMLImageElement>('[data-gallery-main]');
    const thumbs = Array.from(gallery.querySelectorAll<GalleryThumb>('[data-gallery-thumb]'));

    if (!frame || !mainImage || thumbs.length === 0) {
        return;
    }

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const imageUrl = thumb.dataset.imageUrl;
            const imageAlt = thumb.dataset.imageAlt;

            if (!imageUrl) {
                return;
            }

            mainImage.src = imageUrl;
            mainImage.alt = imageAlt ?? mainImage.alt;
            frame.classList.remove('is-zoomed');
            mainImage.style.transformOrigin = 'center center';

            thumbs.forEach((item) => item.classList.remove('is-active'));
            thumb.classList.add('is-active');
        });
    });

    frame.addEventListener('mouseenter', () => {
        frame.classList.add('is-zoomed');
    });

    frame.addEventListener('mouseleave', () => {
        frame.classList.remove('is-zoomed');
        mainImage.style.transformOrigin = 'center center';
    });

    frame.addEventListener('mousemove', (event) => {
        if (!frame.classList.contains('is-zoomed')) {
            return;
        }

        updateZoomOrigin(frame, event);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll<HTMLElement>('[data-product-gallery]').forEach(bindGallery);
});
