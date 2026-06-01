document.addEventListener('DOMContentLoaded', () => {
    // Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuClose = document.getElementById('mobile-menu-close');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.remove('translate-x-full');
            document.body.classList.add('overflow-hidden');
        });
    }

    if (mobileMenuClose && mobileMenu) {
        mobileMenuClose.addEventListener('click', () => {
            mobileMenu.classList.add('translate-x-full');
            document.body.classList.remove('overflow-hidden');
        });
    }

    // Mobile Search Toggle
    const mobileSearchBtn = document.getElementById('mobile-search-btn');
    const mobileSearchContainer = document.getElementById('mobile-search-container');
    const mobileSearchClose = document.getElementById('mobile-search-close');

    if (mobileSearchBtn && mobileSearchContainer) {
        mobileSearchBtn.addEventListener('click', () => {
            mobileSearchContainer.classList.remove('hidden');
            // focus the input
            setTimeout(() => {
                const input = mobileSearchContainer.querySelector('input');
                if (input) input.focus();
            }, 100);
        });
    }

    if (mobileSearchClose && mobileSearchContainer) {
        mobileSearchClose.addEventListener('click', () => {
            mobileSearchContainer.classList.add('hidden');
        });
    }
    }

    // Side Cart Toggle
    const sideCartToggle = document.getElementById('side-cart-toggle');
    const sideCart = document.getElementById('side-cart');
    const sideCartClose = document.getElementById('side-cart-close');
    const sideCartOverlay = document.getElementById('side-cart-overlay');

    function openSideCart(e) {
        if (e) e.preventDefault();
        if (sideCart && sideCartOverlay) {
            sideCart.classList.remove('translate-x-full');
            sideCartOverlay.classList.remove('opacity-0', 'pointer-events-none');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeSideCart() {
        if (sideCart && sideCartOverlay) {
            sideCart.classList.add('translate-x-full');
            sideCartOverlay.classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('overflow-hidden');
        }
    }

    if (sideCartToggle) sideCartToggle.addEventListener('click', openSideCart);
    if (sideCartClose) sideCartClose.addEventListener('click', closeSideCart);
    if (sideCartOverlay) sideCartOverlay.addEventListener('click', closeSideCart);

    // Open side cart automatically when WooCommerce adds item to cart via AJAX
    if (typeof jQuery !== 'undefined') {
        jQuery('body').on('added_to_cart', function() {
            openSideCart();
        });
    }
});
