/**
 * RIYAA'S COLLECTION — JavaScript principal
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Menu burger mobile ────────────────────────────────────
    const burger  = document.querySelector('.burger');
    const mainNav = document.querySelector('.main-nav');

    if (burger && mainNav) {
        burger.addEventListener('click', () => {
            const isOpen = burger.getAttribute('aria-expanded') === 'true';
            burger.setAttribute('aria-expanded', String(!isOpen));
            mainNav.classList.toggle('is-open');
            burger.classList.toggle('is-active');
        });
    }

    // ── Ajout au panier (AJAX) ────────────────────────────────
    document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();

            const form = btn.closest('form') || btn.closest('[data-product-form]');
            if (!form) return;

            const formData = new FormData(form);
            btn.disabled   = true;
            btn.textContent = 'Ajout en cours…';

            try {
                const res  = await fetch(form.action || '/panier/ajouter', {
                    method: 'POST',
                    body:   formData,
                });
                const data = await res.json();

                if (data.success) {
                    // Mettre à jour le badge panier
                    const badge = document.querySelector('.cart-badge');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        badge.style.display = 'flex';
                    } else if (data.cart_count > 0) {
                        const cartBtn = document.querySelector('.cart-btn');
                        if (cartBtn) {
                            const newBadge = document.createElement('span');
                            newBadge.className   = 'cart-badge';
                            newBadge.textContent = data.cart_count;
                            cartBtn.appendChild(newBadge);
                        }
                    }

                    btn.textContent = '✓ Ajouté';
                    setTimeout(() => {
                        btn.textContent = 'Ajouter au panier';
                        btn.disabled    = false;
                    }, 2000);

                } else {
                    btn.textContent = 'Erreur';
                    setTimeout(() => {
                        btn.textContent = 'Ajouter au panier';
                        btn.disabled    = false;
                    }, 2000);
                }
            } catch (err) {
                console.error('Erreur panier :', err);
                btn.textContent = 'Erreur réseau';
                btn.disabled = false;
            }
        });
    });

    // ── Header scroll effect ──────────────────────────────────
    const header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 60);
        }, { passive: true });
    }

    // ── Galerie produit (fiche produit) ───────────────────────
    const thumbs = document.querySelectorAll('.gallery-thumb');
    const mainImg = document.querySelector('.gallery-main img');

    if (thumbs.length && mainImg) {
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                mainImg.src = thumb.dataset.full;
                mainImg.alt = thumb.alt;
                thumbs.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });
    }

});
