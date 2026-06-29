<?php /* app/views/checkout/confirmation.php */ ?>

<section style="padding:6rem 0; background:var(--ivory); min-height:70vh;">
    <div class="container" style="max-width:700px; margin:0 auto; text-align:center;">

        <!-- Icône succès -->
        <div style="width:80px; height:80px; background:var(--obsidian); border-radius:50%;
                    display:flex; align-items:center; justify-content:center;
                    margin:0 auto 2rem; font-size:2rem;">
            ✓
        </div>

        <h1 style="font-family:var(--font-display); font-size:2.5rem; font-weight:300;
                   color:var(--obsidian); margin-bottom:1rem;">
            Commande confirmée !
        </h1>

        <p style="font-size:0.9rem; color:var(--mist); margin-bottom:3rem; line-height:1.8;">
            Merci pour votre commande. Votre acompte a bien été reçu.<br>
            La confection de votre pièce commence maintenant.
        </p>

        <!-- Récapitulatif -->
        <div style="background:var(--white); padding:2rem; border:1px solid var(--blush);
                    text-align:left; margin-bottom:2rem;">

            <h2 style="font-family:var(--font-display); font-size:1.3rem; font-weight:400;
                       margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--blush);">
                Récapitulatif
            </h2>

            <div style="display:flex; justify-content:space-between; margin-bottom:0.8rem; font-size:0.88rem;">
                <span style="color:var(--mist);">Numéro de commande</span>
                <strong><?= htmlspecialchars($order['order_number']) ?></strong>
            </div>

            <div style="display:flex; justify-content:space-between; margin-bottom:0.8rem; font-size:0.88rem;">
                <span style="color:var(--mist);">Nom</span>
                <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
            </div>

            <div style="display:flex; justify-content:space-between; margin-bottom:0.8rem; font-size:0.88rem;">
                <span style="color:var(--mist);">Téléphone</span>
                <strong><?= htmlspecialchars($order['customer_phone']) ?></strong>
            </div>

            <div style="display:flex; justify-content:space-between; margin-bottom:0.8rem; font-size:0.88rem;">
                <span style="color:var(--mist);">Adresse</span>
                <strong><?= htmlspecialchars($order['delivery_address']) ?>, <?= htmlspecialchars($order['delivery_city']) ?></strong>
            </div>

            <div style="border-top:1px solid var(--blush); padding-top:1rem; margin-top:1rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.6rem; font-size:0.88rem;">
                    <span style="color:var(--mist);">Montant total</span>
                    <span><?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.6rem;
                            font-size:0.9rem; color:var(--gold); font-weight:500;">
                    <span>✓ Acompte payé via TMoney</span>
                    <span><?= number_format($order['deposit_amount'], 0, ',', ' ') ?> FCFA</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.88rem;">
                    <span style="color:var(--mist);">Reste à payer à la livraison</span>
                    <span><?= number_format($order['balance_amount'], 0, ',', ' ') ?> FCFA</span>
                </div>
            </div>
        </div>

        <!-- Délai -->
        <div style="background:var(--obsidian); padding:1.5rem; color:var(--ivory); margin-bottom:2rem;">
            <p style="font-size:0.68rem; letter-spacing:0.2em; text-transform:uppercase;
                      color:var(--gold); margin-bottom:0.5rem;">
                Livraison prévue
            </p>
            <p style="font-family:var(--font-display); font-size:1.5rem; font-weight:300;">
                Avant le <?= date('d/m/Y', strtotime($order['delivery_deadline'])) ?>
            </p>
            <p style="font-size:0.8rem; color:#9B9490; margin-top:0.5rem;">
                Livraison en main propre à <?= htmlspecialchars($order['delivery_city']) ?>
            </p>
        </div>

        <!-- Boutons -->
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="<?= APP_URL ?>/facture?ref=<?= htmlspecialchars($order['order_number']) ?>"
               class="btn btn-outline">
                Voir ma facture
            </a>
            <a href="<?= APP_URL ?>/catalogue" class="btn btn-primary">
                Continuer mes achats
            </a>
        </div>

    </div>
</section>