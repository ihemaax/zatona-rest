document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.getElementById('mainNavbar');

    if (!navbar) {
        return;
    }

    let lastScrollY = window.scrollY;
    let ticking = false;
    const isMobile = () => window.innerWidth <= 767.98;

    const handleNavbar = () => {
        if (isMobile()) {
            navbar.classList.remove('nav-hidden');
            ticking = false;
            return;
        }

        const currentScrollY = window.scrollY;

        if (currentScrollY <= 10) {
            navbar.classList.remove('nav-hidden');
        } else if (currentScrollY > lastScrollY) {
            navbar.classList.add('nav-hidden');
        } else {
            navbar.classList.remove('nav-hidden');
        }

        lastScrollY = currentScrollY;
        ticking = false;
    };

    window.addEventListener(
        'scroll',
        () => {
            if (!ticking) {
                requestAnimationFrame(handleNavbar);
                ticking = true;
            }
        },
        { passive: true }
    );

    window.addEventListener('resize', () => {
        if (isMobile()) {
            navbar.classList.remove('nav-hidden');
        }
    });
});
