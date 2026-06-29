<?php /* app/views/home/index.php */ ?>

<!-- ══ HERO ════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-text">
            <p class="hero-eyebrow">Confection artisanale · Lomé, Togo</p>
            <h1 class="hero-title">
                La soie,<br>
                <em>réinventée</em><br>
                pour vous.
            </h1>
            <p class="hero-sub">
                Chaque modèle est confectionné à la commande.<br>
                Livraison garantie sous 5 jours.
            </p>
            <div class="hero-cta">
                <a href="<?= APP_URL ?>/catalogue" class="btn btn-primary">Découvrir le catalogue</a>
                <a href="#process" class="btn btn-ghost">Comment ça marche</a>
            </div>
        </div>
        <div class="hero-image">
            <!-- Remplacer par une vraie image produit -->
            <img src="<?= APP_URL ?>/assets/images/hero.jpg"
                 alt="Riyaa's Collection"
                 style="width:100%; aspect-ratio:3/4; object-fit:cover; display:block;">
            </div>
        </div>
    </div>
</section>

<!-- ══ CATÉGORIES ══════════════════════════════════════════════ -->
<section class="section-categories">
    <div class="container">
        <h2 class="section-title">Nos collections</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= APP_URL ?>/catalogue?categorie=<?= $cat['slug'] ?>" class="category-card">
               <div class="category-image-wrap">
    <?php
    $catImages = [
        'jupes-en-soie'    => 'jupes.jpg',
        'chemises-en-soie' => 'chemises.jpg',
        'hauts-en-soie'    => 'hauts.jpg',
    ];
    $imgFile = $catImages[$cat['slug']] ?? null;
    ?>
    <?php if ($imgFile): ?>
        <img src="<?= APP_URL ?>/assets/images/<?= $imgFile ?>"
             style="width:100%; aspect-ratio:2/3; object-fit:cover; display:block; transition:transform 0.6s ease;">
    <?php else: ?>
        <div class="category-image-placeholder"></div>
    <?php endif; ?>
</div>
                <span class="category-name"><?= htmlspecialchars($cat['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ PRODUITS MIS EN AVANT ════════════════════════════════════ -->
<?php if (!empty($featuredProducts)): ?>
<section class="section-featured">
    <div class="container">
        <h2 class="section-title">Pièces phares</h2>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $p): ?>
            <article class="product-card">
                <a href="<?= APP_URL ?>/produit?slug=<?= $p['slug'] ?>" class="product-card-link">
                    <div class="product-image-wrap">
                        <?php if (!empty($p['cover_image'])): ?>
                            <img src="<?= APP_URL ?>/assets/images/products/<?= htmlspecialchars($p['cover_image']) ?>"
                        
                                 alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="product-image-placeholder"></div>
                        <?php endif; ?>
                        <span class="product-badge">
                            <?= $p['stock_status'] === 'made_to_order' ? 'Sur commande' : 'Disponible' ?>
                        </span>
                    </div>
                    <div class="product-info">
                        <p class="product-category"><?= htmlspecialchars($p['category_name']) ?></p>
                        <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                        <p class="product-price">
                            <?= number_format($p['price'], 0, ',', ' ') ?> <?= CURRENCY ?>
                        </p>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="section-cta">
            <a href="<?= APP_URL ?>/catalogue" class="btn btn-outline">Voir tout le catalogue</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══ PROCESSUS DE COMMANDE ════════════════════════════════════ -->
<section class="section-process" id="process">
    <div class="container">
        <h2 class="section-title">Comment commander</h2>
        <div class="process-steps">

            <div class="process-step">
                <div class="step-number">01</div>
                <h3>Choisissez votre modèle</h3>
                <p>Parcourez le catalogue, sélectionnez votre taille et couleur.</p>
            </div>

            <div class="process-step">
                <div class="step-number">02</div>
                <h3>Payez l'acompte de 50%</h3>
                <p>Un acompte de 50% via TMoney déclenche la confection de votre pièce.</p>
            </div>

            <div class="process-step">
                <div class="step-number">03</div>
                <h3>Confection artisanale</h3>
                <p>Votre vêtement est confectionné avec soin dans un délai de 5 jours maximum.</p>
            </div>

            <div class="process-step">
                <div class="step-number">04</div>
                <h3>Livraison & solde</h3>
                <p>À la remise en main propre, vous réglez les 50% restants en espèces ou TMoney.</p>
            </div>

        </div>
    </div>
</section>

<!-- ══ BANNIÈRE MATIÈRE ════════════════════════════════════════ -->
<section class="section-material">
    <div class="container">
        <div class="material-inner">
            <h2>Soie naturelle 100%</h2>
            <p>Chaque pièce Riyaa's Collection est taillée dans une soie naturelle sélectionnée pour sa fluidité, sa brillance et son toucher exceptionnel.</p>
            <a href="<?= APP_URL ?>/catalogue" class="btn btn-primary">Explorer la collection</a>
        </div>
    </div>
</section>
