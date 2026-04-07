import './bootstrap';

const isProduction = import.meta.env.PROD;

if (isProduction) {
    const blockDevtoolsShortcuts = (event) => {
        const key = event.key?.toLowerCase();
        const ctrlOrCmd = event.ctrlKey || event.metaKey;

        if (
            key === 'f12' ||
            (ctrlOrCmd && event.shiftKey && ['i', 'j', 'c'].includes(key)) ||
            (ctrlOrCmd && ['u', 's'].includes(key))
        ) {
            event.preventDefault();
            event.stopPropagation();
        }
    };

    const blockContextMenu = (event) => {
        event.preventDefault();
    };

    window.addEventListener('keydown', blockDevtoolsShortcuts, true);
    window.addEventListener('contextmenu', blockContextMenu, true);

    const showSecurityOverlay = () => {
        if (document.getElementById('security-overlay')) {
            return;
        }

        const overlay = document.createElement('div');
        overlay.id = 'security-overlay';
        overlay.setAttribute(
            'style',
            [
                'position:fixed',
                'inset:0',
                'z-index:2147483647',
                'background:#111827',
                'color:#fff',
                'display:flex',
                'align-items:center',
                'justify-content:center',
                'font-size:20px',
                'font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif',
                'text-align:center',
                'padding:24px',
            ].join(';')
        );
        overlay.textContent = 'تم إيقاف الصفحة لأسباب أمنية. برجاء إغلاق أدوات المطور وإعادة التحميل.';

        document.body?.appendChild(overlay);
    };

    const detectDevtools = () => {
        const widthThreshold = window.outerWidth - window.innerWidth > 160;
        const heightThreshold = window.outerHeight - window.innerHeight > 160;

        if (widthThreshold || heightThreshold) {
            showSecurityOverlay();
        }
    };

    setInterval(detectDevtools, 1000);
}


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
