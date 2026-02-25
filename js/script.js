document.addEventListener("DOMContentLoaded", () => {

    /* --- CUSTOM CURSOR --- */
    const cursorDot = document.querySelector("[data-cursor-dot]");
    const cursorOutline = document.querySelector("[data-cursor-outline]");

    // Only activate custom cursor on non-touch devices
    if (matchMedia("(pointer:fine)").matches) {
        window.addEventListener("mousemove", function (e) {
            const posX = e.clientX;
            const posY = e.clientY;

            // Dot follows instantly
            cursorDot.style.left = `${posX}px`;
            cursorDot.style.top = `${posY}px`;

            // Outline follows with slight delay (handled by CSS transition, we just set position)
            // Using animate for smoother trailing effect if desired, or just setting props:
            cursorOutline.animate({
                left: `${posX}px`,
                top: `${posY}px`
            }, { duration: 500, fill: "forwards" });
        });

        // Add hover effects for links and buttons
        const interactiveElements = document.querySelectorAll("a, button, .productCard, .categoryItem, .mainLogo");
        interactiveElements.forEach(el => {
            el.addEventListener("mouseenter", () => {
                cursorOutline.style.transform = "translate(-50%, -50%) scale(1.5)";
                cursorOutline.style.backgroundColor = "rgba(220, 38, 38, 0.1)"; // Slight red tint
                cursorOutline.style.borderColor = "transparent";
            });
            el.addEventListener("mouseleave", () => {
                cursorOutline.style.transform = "translate(-50%, -50%) scale(1)";
                cursorOutline.style.backgroundColor = "transparent";
                cursorOutline.style.borderColor = "rgba(28, 25, 23, 0.5)";
            });
        });
    } else {
        // Hide custom cursor elements on touch devices
        if (cursorDot) cursorDot.style.display = 'none';
        if (cursorOutline) cursorOutline.style.display = 'none';
    }


    /* --- SCROLL ANIMATIONS (Reveal) --- */
    const revealElements = document.querySelectorAll('.reveal');

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                // Optional: stop observing once revealed
                // revealObserver.unobserve(entry.target); 
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
    });

    revealElements.forEach(el => revealObserver.observe(el));


    /* --- PARALLAX EFFECT (Hero) --- */
    const heroBg = document.querySelector('.heroBgContainer');
    if (heroBg) {
        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            // Move background slower than scroll
            heroBg.style.transform = `translateY(${scrollY * 0.5}px)`;
        });
    }


    /* --- EXISTING NAVIGATION LOGIC --- */
    const btnBoutiqueNav = document.getElementById("btnBoutiqueNav");
    const megaMenuShop = document.getElementById("megaMenuShop");
    const navBarPrincipal = document.getElementById("navBarPrincipal");
    const navBar = document.getElementById("navBar");

    // Toggle Mega Menu
    if (btnBoutiqueNav && megaMenuShop) {
        btnBoutiqueNav.addEventListener("click", (e) => {
            e.preventDefault();
            const isExpanded = btnBoutiqueNav.getAttribute("aria-expanded") === "true";

            if (isExpanded) {
                btnBoutiqueNav.setAttribute("aria-expanded", "false");
                megaMenuShop.classList.remove("showMegaMenuShop");
                navBarPrincipal.classList.remove("showMegaMenuShop");

                // Reset navbar style if not scrolled
                if (window.scrollY < 50) {
                    navBar.classList.remove("modeWhite");
                    navBar.classList.remove("modeLogoVisible");
                }
            } else {
                btnBoutiqueNav.setAttribute("aria-expanded", "true");
                megaMenuShop.classList.add("showMegaMenuShop");
                navBarPrincipal.classList.add("showMegaMenuShop");

                // Force white mode when menu is open
                navBar.classList.add("modeWhite");
                navBar.classList.add("modeLogoVisible");
            }
        });
    }

    // Navbar Scroll Effect
    window.addEventListener("scroll", () => {
        if (navBar) {
            if (window.scrollY > 50) {
                navBar.classList.add("modeWhite");
                navBar.classList.add("modeLogoVisible");
            } else {
                navBar.classList.remove("modeWhite");
                navBar.classList.remove("modeLogoVisible");
            }
        }
    });
});

/* --- FAVORITES API INTEGRATION --- */
function toggleFavorite(event, articleId) {
    event.preventDefault();
    event.stopPropagation();

    const btn = event.currentTarget || event.target.closest('button');
    if (!btn) return;
    const icon = btn.querySelector('svg');

    // Get the base url path, assumes /Residue_/
    const baseUrl = '/Residue_/';

    fetch(baseUrl + 'api/toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ article_id: articleId })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.action === 'added') {
                    icon.classList.add('is-favorite');
                } else if (data.action === 'removed') {
                    icon.classList.remove('is-favorite');

                    // If we are currently on the favorites page, we should instantly visually remove the card
                    if (window.location.pathname.includes('favorites.php')) {
                        const card = btn.closest('.catalogCard');
                        if (card) {
                            card.style.display = 'none';
                        }
                    }
                }
            } else {
                if (data.message === 'Non connecté') {
                    window.location.href = baseUrl + 'auth/login.php';
                } else {
                    console.error("Error toggling favorite:", data.message);
                }
            }
        })
        .catch(error => {
            console.error("Fetch error:", error);
        });
}