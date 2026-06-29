<?php /* app/views/catalog/show.php */ ?>

<!-- ══ BREADCRUMB ════════════════════════════════════════════ -->
<div style="background:var(--white); padding:1rem 0; border-bottom:1px solid var(--blush);">
    <div class="container">
        <p style="font-size:0.7rem; color:var(--mist); letter-spacing:0.08em;">
            <a href="<?= APP_URL ?>/" style="color:var(--mist); text-decoration:none;">Accueil</a>
            &nbsp;/&nbsp;
            <a href="<?= APP_URL ?>/catalogue" style="color:var(--mist); text-decoration:none;">Catalogue</a>
            &nbsp;/&nbsp;
            <a href="<?= APP_URL ?>/catalogue?categorie=<?= $product['category_slug'] ?>"
               style="color:var(--mist); text-decoration:none;">
                <?= htmlspecialchars($product['category_name']) ?>
            </a>
            &nbsp;/&nbsp;
            <span style="color:var(--obsidian);"><?= htmlspecialchars($product['name']) ?></span>
        </p>
    </div>
</div>

<!-- ══ FICHE PRODUIT ════════════════════════════════════════ -->
<section style="padding:4rem 0 6rem; background:var(--ivory);">
    <div class="container">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:5rem; align-items:start;">

            <!-- ── GALERIE PHOTOS ─────────────────────────── -->
            <div>
                <!-- Image principale -->
                <div style="overflow:hidden; background:var(--blush); margin-bottom:1rem;">
                    <?php
                    $coverImage = null;
                    foreach ($images as $img) {
                        if ($img['is_cover']) { $coverImage = $img; break; }
                    }
                    if (!$coverImage && !empty($images)) $coverImage = $images[0];
                    ?>
                    <?php if ($coverImage): ?>
                        <img id="main-product-image"
                             src="<?= APP_URL ?>/assets/images/products/<?= htmlspecialchars($coverImage['image_path']) ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             style="width:100%; aspect-ratio:3/4; object-fit:cover; display:block; transition:opacity 0.3s;">
                    <?php else: ?>
                        <div style="width:100%; aspect-ratio:3/4; background:linear-gradient(135deg, var(--blush), var(--gold-light));"></div>
                    <?php endif; ?>
                </div>

                <!-- Miniatures -->
                <?php if (count($images) > 1): ?>
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <?php foreach ($images as $img): ?>
                    <button onclick="changeImage('<?= APP_URL ?>/assets/images/products/<?= htmlspecialchars($img['image_path']) ?>')"
                            style="border:2px solid <?= $img['is_cover'] ? 'var(--gold)' : 'transparent' ?>;
                                   padding:0; cursor:pointer; background:none; transition:border-color 0.2s;"
                            class="thumb-btn">
                        <img src="<?= APP_URL ?>/assets/images/products/<?= htmlspecialchars($img['image_path']) ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             style="width:72px; height:90px; object-fit:cover; display:block;">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ── INFOS PRODUIT ──────────────────────────── -->
            <div style="position:sticky; top:100px;">

                <!-- Catégorie -->
                <p style="font-size:0.68rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--gold); margin-bottom:0.75rem;">
                    <?= htmlspecialchars($product['category_name']) ?>
                </p>

                <!-- Nom -->
                <h1 style="font-family:var(--font-display); font-size:clamp(1.8rem,3vw,2.8rem); font-weight:300; color:var(--obsidian); margin-bottom:1.5rem; line-height:1.2;">
                    <?= htmlspecialchars($product['name']) ?>
                </h1>

                <!-- PRIX -->
                <div style="margin-bottom:1.5rem; padding:1.5rem; background:var(--white); border:1px solid var(--blush);">
                    <div style="display:flex; align-items:baseline; gap:0.5rem; margin-bottom:0.75rem;">
                        <span style="font-family:var(--font-display); font-size:2.2rem; font-weight:600; color:var(--obsidian);">
                            <?= number_format($product['price'], 0, ',', ' ') ?>
                        </span>
                        <span style="font-size:0.85rem; color:var(--mist); letter-spacing:0.05em;">FCFA</span>
                    </div>

                    <!-- Détail acompte -->
                    <div style="border-top:1px solid var(--blush); padding-top:0.75rem;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.4rem;">
                            <span style="font-size:0.78rem; color:var(--mist);">Acompte à payer maintenant (50%)</span>
                            <span style="font-size:0.82rem; font-weight:500; color:var(--gold);">
                                <?= number_format($product['price'] * 0.5, 0, ',', ' ') ?> FCFA
                            </span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="font-size:0.78rem; color:var(--mist);">Reste à payer à la livraison</span>
                            <span style="font-size:0.82rem; color:var(--mist);">
                                <?= number_format($product['price'] * 0.5, 0, ',', ' ') ?> FCFA
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Matière -->
                <p style="font-size:0.78rem; color:var(--mist); margin-bottom:1.5rem; letter-spacing:0.05em;">
                    Matière : <strong style="color:var(--obsidian);"><?= htmlspecialchars($product['material']) ?></strong>
                </p>

                <!-- Description -->
                <?php if (!empty($product['description'])): ?>
                <p style="font-size:0.9rem; color:var(--mist); line-height:1.8; margin-bottom:1.5rem; font-weight:300;">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </p>
                <?php endif; ?>

                <!-- Formulaire ajout au panier -->
                <form id="add-to-cart-form" action="<?= APP_URL ?>/panier/ajouter" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <!-- Sélection taille -->
                    <?php if (!empty($variants)): ?>
                    <div style="margin-bottom:1.5rem;">
                        <p style="font-size:0.68rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--mist); margin-bottom:0.75rem;">
                            Taille
                        </p>
                        <div style="display:flex; flex-wrap:wrap; gap:0.5rem;" id="sizes-selector">
                            <?php foreach ($variants as $v): ?>
                            <label style="cursor:pointer;">
                                <input type="radio" name="variant_id" value="<?= $v['id'] ?>"
                                       style="display:none;" class="size-radio"
                                       <?= $v === reset($variants) ? 'checked' : '' ?>>
                                <span class="size-option"
                                      style="display:block; padding:0.5rem 1rem; border:1px solid var(--blush);
                                             font-size:0.78rem; letter-spacing:0.08em; transition:all 0.2s;
                                             background:var(--white);">
                                    <?= htmlspecialchars($v['size']) ?>
                                    <?= $v['extra_price'] > 0 ? '+' . number_format($v['extra_price'], 0, ',', ' ') . ' FCFA' : '' ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Quantité -->
                    <div style="margin-bottom:1.5rem;">
                        <p style="font-size:0.68rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--mist); margin-bottom:0.75rem;">
                            Quantité
                        </p>
                        <div style="display:flex; align-items:center; border:1px solid var(--blush); width:fit-content; background:var(--white);">
                            <button type="button" onclick="changeQty(-1)"
                                    style="width:40px; height:40px; border:none; background:none; font-size:1.1rem; cursor:pointer; color:var(--obsidian);">−</button>
                            <input type="number" name="quantity" id="qty-input" value="1" min="1" max="10"
                                   style="width:50px; text-align:center; border:none; border-left:1px solid var(--blush);
                                          border-right:1px solid var(--blush); height:40px; font-family:var(--font-body);
                                          font-size:0.9rem; background:none; outline:none;">
                            <button type="button" onclick="changeQty(1)"
                                    style="width:40px; height:40px; border:none; background:none; font-size:1.1rem; cursor:pointer; color:var(--obsidian);">+</button>
                        </div>
                    </div>

                    <!-- Bouton ajouter au panier -->
                    <button type="button" id="add-cart-btn" class="btn btn-primary btn-add-to-cart"
                            style="width:100%; padding:1.1rem; font-size:0.78rem; margin-bottom:0.75rem;">
                        Ajouter au panier
                    </button>

                    <!-- Message confirmation -->
                    <div id="cart-message" style="display:none; text-align:center; padding:0.75rem;
                         background:#e8f5ee; color:#2E7D52; font-size:0.82rem; margin-bottom:0.75rem;">
                        ✓ Article ajouté au panier !
                        <a href="<?= APP_URL ?>/panier" style="color:#2E7D52; font-weight:500; margin-left:0.5rem;">
                            Voir le panier →
                        </a>
                    </div>

                </form>

                <!-- Infos livraison -->
                <div style="border-top:1px solid var(--blush); padding-top:1.5rem; margin-top:1rem;">
                    <div style="display:flex; gap:1rem; margin-bottom:0.75rem; align-items:flex-start;">
                        <span style="font-size:1.1rem;">⏱</span>
                        <div>
                            <p style="font-size:0.78rem; font-weight:500; color:var(--obsidian);">Livraison sous <?= $product['delivery_days'] ?? 5 ?> jours</p>
                            <p style="font-size:0.72rem; color:var(--mist);">Confection artisanale à la commande</p>
                        </div>
                    </div>
                    <div style="display:flex; gap:1rem; margin-bottom:0.75rem; align-items:flex-start;">
                        <span style="font-size:1.1rem;">💳</span>
                        <div>
                            <p style="font-size:0.78rem; font-weight:500; color:var(--obsidian);">Paiement via TMoney</p>
                            <p style="font-size:0.72rem; color:var(--mist);">Acompte sécurisé de 50% à la commande</p>
                        </div>
                    </div>
                    <div style="display:flex; gap:1rem; align-items:flex-start;">
                        <span style="font-size:1.1rem;">📍</span>
                        <div>
                            <p style="font-size:0.78rem; font-weight:500; color:var(--obsidian);">Remise en main propre — Lomé</p>
                            <p style="font-size:0.72rem; color:var(--mist);">Solde payé à la livraison</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
// Changer image principale
function changeImage(src) {
    const img = document.getElementById('main-product-image');
    if (img) {
        img.style.opacity = '0';
        setTimeout(() => { img.src = src; img.style.opacity = '1'; }, 150);
    }
    document.querySelectorAll('.thumb-btn').forEach(btn => {
        btn.style.borderColor = btn.querySelector('img').src === src ? 'var(--gold)' : 'transparent';
    });
}

// Changer quantité
function changeQty(delta) {
    const input = document.getElementById('qty-input');
    const val   = Math.max(1, Math.min(10, parseInt(input.value) + delta));
    input.value = val;
}

// Sélection taille — style visuel
document.querySelectorAll('.size-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.size-option').forEach(span => {
            span.style.borderColor    = 'var(--blush)';
            span.style.background     = 'var(--white)';
            span.style.color          = 'var(--obsidian)';
        });
        if (radio.checked) {
            const span = radio.nextElementSibling;
            span.style.borderColor = 'var(--obsidian)';
            span.style.background  = 'var(--obsidian)';
            span.style.color       = 'var(--ivory)';
        }
    });
});

// Initialiser la première taille sélectionnée
const firstRadio = document.querySelector('.size-radio:checked');
if (firstRadio) {
    const span = firstRadio.nextElementSibling;
    span.style.borderColor = 'var(--obsidian)';
    span.style.background  = 'var(--obsidian)';
    span.style.color       = 'var(--ivory)';
}

// Ajout au panier AJAX
document.getElementById('add-cart-btn').addEventListener('click', async () => {
    const form    = document.getElementById('add-to-cart-form');
    const btn     = document.getElementById('add-cart-btn');
    const message = document.getElementById('cart-message');
    const data    = new FormData(form);

    btn.disabled    = true;
    btn.textContent = 'Ajout en cours…';

    try {
        const res  = await fetch('<?= APP_URL ?>/panier/ajouter', { method:'POST', body: data });
        const json = await res.json();

        if (json.success) {
            btn.textContent = '✓ Ajouté !';
            message.style.display = 'block';

            // Mettre à jour le badge panier dans le header
            const badge = document.querySelector('.cart-badge');
            if (badge) {
                badge.textContent = json.cart_count;
            } else {
                const cartBtn = document.querySelector('.cart-btn');
                if (cartBtn) {
                    const newBadge = document.createElement('span');
                    newBadge.className   = 'cart-badge';
                    newBadge.textContent = json.cart_count;
                    cartBtn.appendChild(newBadge);
                }
            }

            setTimeout(() => {
                btn.textContent = 'Ajouter au panier';
                btn.disabled    = false;
            }, 3000);
        }
    } catch(e) {
        btn.textContent = 'Erreur — Réessayer';
        btn.disabled    = false;
    }
});
</script>
