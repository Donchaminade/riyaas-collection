/**
 * RIYAA'S COLLECTION — Client API
 * Petites fonctions fetch vers le backend PHP.
 */

async function apiGet(path) {
    const res  = await fetch(`${CONFIG.API_BASE}/${path}`);
    const json = await res.json();
    if (!res.ok || json.success === false) {
        throw new Error(json.error || `Erreur API (${res.status})`);
    }
    return json;
}

async function apiPost(path, body) {
    const res = await fetch(`${CONFIG.API_BASE}/${path}`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(body),
    });
    const json = await res.json();
    if (!res.ok || json.success === false) {
        throw new Error(json.error || `Erreur API (${res.status})`);
    }
    return json;
}

// ── API publique ─────────────────────────────────────────────
const API = {
    listProducts:  (categorySlug) => apiGet(`products.php?action=list${categorySlug ? '&categorie=' + encodeURIComponent(categorySlug) : ''}`),
    showProduct:   (slug)         => apiGet(`products.php?action=show&slug=${encodeURIComponent(slug)}`),
    categories:    ()             => apiGet('products.php?action=categories'),
    featured:      ()             => apiGet('products.php?action=featured'),
    cartAdd:       (item)         => apiPost('cart.php?action=add', item),
    cartValidate:  (cart)         => apiPost('cart.php?action=validate', { cart }),
    createOrder:   (order)        => apiPost('orders.php', order),
};

// ── Formatage ────────────────────────────────────────────────
function formatPrice(amount) {
    return Number(amount).toLocaleString('fr-FR').replace(/\u202F|\u00A0/g, ' ');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

// ── Carte produit (catalogue, accueil) ───────────────────────
function productCardHtml(p) {
    const isPromo    = Boolean(Number(p.en_promotion)) && p.prix_promotion;
    const finalPrice = Number(p.final_price ?? (isPromo ? p.prix_promotion : p.price));
    const lowStock   = p.stock_quantity && Number(p.stock_quantity) <= Number(p.seuil_alerte_stock || 3);

    return `
    <article class="product-card">
        <a href="produit.html?slug=${encodeURIComponent(p.slug)}" class="product-card-link">
            <div class="product-image-wrap">
                ${p.cover_image_url
                    ? `<img src="${p.cover_image_url}" alt="${escapeHtml(p.name)}" loading="lazy">`
                    : '<div class="product-image-placeholder"></div>'}
                ${isPromo ? '<span class="product-badge" style="background:#C0392B; color:white; left:auto; right:1rem;">PROMO</span>' : ''}
                <span class="product-badge">${p.stock_status === 'made_to_order' ? 'Sur commande' : 'Disponible'}</span>
            </div>
            <div class="product-info">
                <p class="product-category">${escapeHtml(p.category_name || '')}</p>
                <h3 class="product-name">${escapeHtml(p.name)}</h3>
                <div style="display:flex; align-items:baseline; gap:0.6rem; margin-top:0.5rem; flex-wrap:wrap;">
                    <span style="font-family:var(--font-display); font-size:1.4rem; font-weight:600; color:${isPromo ? '#C0392B' : 'var(--obsidian)'};">
                        ${formatPrice(finalPrice)}
                    </span>
                    <span style="font-size:0.75rem; color:var(--mist); letter-spacing:0.05em;">${CONFIG.CURRENCY}</span>
                    ${isPromo ? `<span style="font-size:0.85rem; color:var(--mist); text-decoration:line-through;">${formatPrice(p.price)} ${CONFIG.CURRENCY}</span>` : ''}
                </div>
                <p style="font-size:0.72rem; color:var(--gold); margin-top:0.3rem;">
                    Acompte : ${formatPrice(finalPrice * CONFIG.DEPOSIT_PERCENT / 100)} ${CONFIG.CURRENCY}
                </p>
                ${lowStock ? `<p style="font-size:0.72rem; color:#C0392B; font-weight:500; margin-top:0.3rem;">Plus que ${p.stock_quantity} exemplaire${p.stock_quantity > 1 ? 's' : ''} !</p>` : ''}
                <p style="font-size:0.7rem; color:var(--mist); margin-top:0.2rem; letter-spacing:0.05em;">
                    Livraison sous ${p.delivery_days || CONFIG.DELIVERY_DAYS} jours
                </p>
            </div>
        </a>
    </article>`;
}
