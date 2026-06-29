<?php /* app/views/catalog/index.php */ ?>

<!-- ══ EN-TÊTE CATALOGUE ════════════════════════════════════ -->
<section style="padding: 3rem 0 2rem; background: var(--white); border-bottom: 1px solid var(--blush);">
    <div class="container">
        <p style="font-size:0.7rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--gold); margin-bottom:0.5rem;">
            <?= $activeCategory ? htmlspecialchars($activeCategory['name']) : 'Toute la collection' ?>
        </p>
        <h1 style="font-family:var(--font-display); font-size:clamp(2rem,4vw,3rem); font-weight:300;">
            <?= $activeCategory ? htmlspecialchars($activeCategory['name']) : 'Notre catalogue' ?>
        </h1>
    </div>
</section>

<!-- ══ FILTRES PAR CATÉGORIE ════════════════════════════════ -->
<section style="padding: 1.5rem 0; background: var(--white); border-bottom: 1px solid var(--blush); position:sticky; top:72px; z-index:10;">
    <div class="container">
        <div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
            <a href="<?= APP_URL ?>/catalogue"
               style="font-size:0.7rem; letter-spacing:0.15em; text-transform:uppercase;
                      padding:0.4rem 1rem; border:1px solid;
                      <?= !$activeCategory ? 'border-color:var(--obsidian); background:var(--obsidian); color:var(--ivory);' : 'border-color:var(--blush); color:var(--mist);' ?>
                      text-decoration:none; transition:all 0.2s;">
                Tout voir
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= APP_URL ?>/catalogue?categorie=<?= $cat['slug'] ?>"
                   style="font-size:0.7rem; letter-spacing:0.15em; text-transform:uppercase;
                          padding:0.4rem 1rem; border:1px solid; text-decoration:none; transition:all 0.2s;
                          <?= ($activeCategory && $activeCategory['id'] == $cat['id'])
                              ? 'border-color:var(--gold); background:var(--gold); color:var(--white);'
                              : 'border-color:var(--blush); color:var(--mist);' ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>

            <span style="margin-left:auto; font-size:0.78rem; color:var(--mist);">
                <?= count($products) ?> modèle<?= count($products) > 1 ? 's' : '' ?>
            </span>
        </div>
    </div>
</section>

<!-- ══ GRILLE PRODUITS ══════════════════════════════════════ -->
<section style="padding: 4rem 0 6rem; background: var(--ivory);">
    <div class="container">

        <?php if (empty($products)): ?>
            <div style="text-align:center; padding:6rem 0;">
                <p style="font-family:var(--font-display); font-size:1.5rem; font-weight:300; color:var(--mist);">
                    Aucun modèle disponible pour l'instant.
                </p>
                <p style="font-size:0.85rem; color:var(--mist); margin-top:0.5rem;">
                    Revenez bientôt, de nouveaux modèles arrivent !
                </p>
            </div>

        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $p): ?>
                <article class="product-card">
                    <a href="<?= APP_URL ?>/produit?slug=<?= $p['slug'] ?>" class="product-card-link">

                        <!-- Image -->
                        <div class="product-image-wrap">
                            <?php if (!empty($p['cover_image'])): ?>
                                <img src="<?= APP_URL ?>/assets/images/products/<?= htmlspecialchars($p['cover_image']) ?>"
                                     alt="<?= htmlspecialchars($p['name']) ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="product-image-placeholder"></div>
                            <?php endif; ?>

                            <span class="product-badge">
                                <?= $p['stock_status'] === 'made_to_order' ? 'Sur commande' : 'Disponible' ?>
                            </span>
                        </div>

                        <!-- Infos + PRIX -->
                        <div class="product-info">
                            <p class="product-category"><?= htmlspecialchars($p['category_name']) ?></p>
                            <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>

                            <!-- PRIX bien visible -->
                            <div style="display:flex; align-items:baseline; gap:0.5rem; margin-top:0.5rem;">
                                <span style="font-family:var(--font-display); font-size:1.4rem; font-weight:600; color:var(--obsidian);">
                                    <?= number_format($p['price'], 0, ',', ' ') ?>
                                </span>
                                <span style="font-size:0.75rem; color:var(--mist); letter-spacing:0.05em;">FCFA</span>
                            </div>

                            <!-- Acompte -->
                            <p style="font-size:0.72rem; color:var(--gold); margin-top:0.3rem;">
                                Acompte : <?= number_format($p['price'] * 0.5, 0, ',', ' ') ?> FCFA
                            </p>

                            <!-- Délai -->
                            <p style="font-size:0.7rem; color:var(--mist); margin-top:0.2rem; letter-spacing:0.05em;">
                                ⏱ Livraison sous <?= $p['delivery_days'] ?? 5 ?> jours
                            </p>
                        </div>

                    </a>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
