<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? "Riyaa's Collection") ?></title>
    <meta name="description" content="Riyaa's Collection — Vêtements en soie haut de gamme confectionnés à la commande au Togo.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- CSS principal -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>

<!-- ═══════════════════════════════════════════ HEADER -->
<header class="site-header">
    <div class="header-inner">

        <!-- Logo -->
        <a href="<?= APP_URL ?>/" class="logo">
            <span class="logo-main">Riyaa's</span>
            <span class="logo-sub">Collection</span>
        </a>

        <!-- Navigation principale -->
        <nav class="main-nav" aria-label="Navigation principale">
            <ul>
                <li><a href="<?= APP_URL ?>/">Accueil</a></li>
                <li class="has-dropdown">
                    <a href="<?= APP_URL ?>/catalogue">Catalogue</a>
                    <ul class="dropdown">
                        <li><a href="<?= APP_URL ?>/catalogue?categorie=jupes-en-soie">Jupes en soie</a></li>
                        <li><a href="<?= APP_URL ?>/catalogue?categorie=chemises-en-soie">Chemises en soie</a></li>
                        <li><a href="<?= APP_URL ?>/catalogue?categorie=hauts-en-soie">Hauts en soie</a></li>
                    </ul>
                </li>
                <li><a href="<?= APP_URL ?>/catalogue">Nouveautés</a></li>
            </ul>
        </nav>

        <!-- Actions header -->
        <div class="header-actions">
            <!-- Compte -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= APP_URL ?>/mon-compte" class="btn-icon" title="Mon compte">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="7" r="4"/><path d="M4 21v-1a8 8 0 0116 0v1"/></svg>
                </a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/connexion" class="btn-icon" title="Se connecter">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="7" r="4"/><path d="M4 21v-1a8 8 0 0116 0v1"/></svg>
                </a>
            <?php endif; ?>

            <!-- Panier -->
            <a href="<?= APP_URL ?>/panier" class="btn-icon cart-btn" title="Panier">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <?php
                $cartCount = 0;
                if (!empty($_SESSION['cart'])) {
                    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
                }
                ?>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>

            <!-- Menu burger mobile -->
            <button class="burger" aria-label="Menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>

    </div>
</header>

<!-- ═══════════════════════════════════════════ CONTENU PRINCIPAL -->
<main id="main-content">
    <?= $content ?>
</main>

<!-- ═══════════════════════════════════════════ FOOTER -->
<footer class="site-footer">
    <div class="footer-inner">

        <div class="footer-brand">
            <span class="logo-main">Riyaa's</span>
            <span class="logo-sub">Collection</span>
            <p>Vêtements en soie confectionnés<br>à la commande au Togo.</p>
        </div>

        <div class="footer-links">
            <h4>Catalogue</h4>
            <ul>
                <li><a href="<?= APP_URL ?>/catalogue?categorie=jupes-en-soie">Jupes en soie</a></li>
                <li><a href="<?= APP_URL ?>/catalogue?categorie=chemises-en-soie">Chemises en soie</a></li>
                <li><a href="<?= APP_URL ?>/catalogue?categorie=hauts-en-soie">Hauts en soie</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h4>Informations</h4>
            <ul>
                <li><a href="#">Comment commander</a></li>
                <li><a href="#">Délais & livraison</a></li>
                <li><a href="#">Paiement TMoney</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>

        <div class="footer-contact">
            <h4>Contact</h4>
            <p>Lomé, Togo</p>
            <p>Paiement via <strong>TMoney</strong></p>
            <p>Livraison sous <strong>5 jours</strong></p>
        </div>

    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Riyaa's Collection. Tous droits réservés.</p>
    </div>
</footer>

<!-- JS principal -->
<script src="<?= APP_URL ?>/assets/js/main.js"></script>

</body>
</html>
