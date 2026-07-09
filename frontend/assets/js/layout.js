/**
 * RIYAA'S COLLECTION — Header et footer partagés
 * Injectés dans les éléments #site-header et #site-footer de chaque page.
 */

function renderHeader() {
    const el = document.getElementById('site-header');
    if (!el) return;

    el.innerHTML = `
    <header class="site-header">
        <div class="header-inner">
            <a href="index.html" class="logo">
                <span class="logo-main">Riyaa's</span>
                <span class="logo-sub">Collection</span>
            </a>

            <nav class="main-nav" aria-label="Navigation principale">
                <ul>
                    <li><a href="index.html">Accueil</a></li>
                    <li class="has-dropdown" id="nav-catalogue-item">
                        <a href="catalogue.html">Catalogue</a>
                        <ul class="dropdown" id="nav-categories" hidden></ul>
                    </li>
                    <li><a href="catalogue.html">Nouveautés</a></li>
                    <li><a href="demande.html">Sur mesure</a></li>
                    <li><a href="a-propos.html">À propos</a></li>
                    <li><a href="contact.html">Contact</a></li>
                </ul>
            </nav>

            <div class="header-actions">
                <a href="panier.html" class="btn-icon cart-btn" title="Panier">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    <span class="cart-badge" style="display:none;">0</span>
                </a>
                <button class="burger" aria-label="Menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>`;
}

function renderFooter() {
    const el = document.getElementById('site-footer');
    if (!el) return;

    el.innerHTML = `
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="logo-main">Riyaa's</span>
                <span class="logo-sub">Collection</span>
                <p>Vêtements en soie confectionnés<br>à la commande au Togo.</p>
            </div>

            <div class="footer-links">
                <h4>Catalogue</h4>
                <ul id="footer-catalogue-links">
                    <li><a href="catalogue.html">Tout le catalogue</a></li>
                    <li><a href="demande.html">Demande sur mesure</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Informations</h4>
                <ul>
                    <li><a href="a-propos.html">À propos</a></li>
                    <li><a href="index.html#process">Comment commander</a></li>
                    <li><a href="panier.html">Mon panier</a></li>
                    <li><a href="contact.html">Contact</a></li>
                    <li><a href="https://wa.me/${CONFIG.WHATSAPP_NUMBER}" target="_blank" rel="noopener">Contact WhatsApp</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <h4>Contact</h4>
                <p>Lomé, Togo</p>
                <p>Paiement via <strong>TMoney</strong></p>
                <p>Livraison sous <strong>${CONFIG.DELIVERY_DAYS} jours</strong></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; ${new Date().getFullYear()} Riyaa's Collection. Tous droits réservés.</p>
        </div>
    </footer>`;
}

/**
 * Liens de catégories du menu et du footer : chargés depuis l'API
 * (aucune catégorie codée en dur). En cas d'échec, le sous-menu
 * n'apparaît simplement pas — le lien "Catalogue" reste fonctionnel.
 */
async function renderCategoryLinks() {
    try {
        const { data: categories } = await API.categories();
        if (!categories.length) return;

        const links = categories.map(cat =>
            `<li><a href="catalogue.html?categorie=${encodeURIComponent(cat.slug)}">${escapeHtml(cat.name)}</a></li>`
        ).join('');

        const navDropdown = document.getElementById('nav-categories');
        if (navDropdown) {
            navDropdown.innerHTML = links;
            navDropdown.hidden = false;
        }

        const footerList = document.getElementById('footer-catalogue-links');
        if (footerList) footerList.insertAdjacentHTML('afterbegin', links);
    } catch (e) {
        console.error('Erreur chargement catégories (navigation) :', e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    renderHeader();
    renderFooter();
    renderCategoryLinks();
    Cart.updateBadge();

    // Menu burger mobile
    const burger  = document.querySelector('.burger');
    const mainNav = document.querySelector('.main-nav');
    if (burger && mainNav) {
        burger.addEventListener('click', () => {
            const isOpen = burger.getAttribute('aria-expanded') === 'true';
            burger.setAttribute('aria-expanded', String(!isOpen));
            mainNav.classList.toggle('is-open');
        });
    }

    // Effet ombre au scroll
    const header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 60);
        }, { passive: true });
    }
});
