<?php /* app/views/checkout/failed.php */ ?>

<section style="padding:6rem 0; background:var(--ivory); min-height:70vh;">
    <div class="container" style="max-width:600px; margin:0 auto; text-align:center;">

        <div style="font-size:3rem; margin-bottom:1.5rem;">❌</div>

        <h1 style="font-family:var(--font-display); font-size:2.5rem; font-weight:300;
                   margin-bottom:1rem; color:var(--obsidian);">
            Paiement échoué
        </h1>

        <p style="font-size:0.9rem; color:var(--mist); margin-bottom:2rem; line-height:1.8;">
            Le paiement n'a pas pu être effectué.<br>
            Votre panier est toujours disponible, vous pouvez réessayer.
        </p>

        <div style="display:flex; gap:1rem; justify-content:center;">
            <a href="<?= APP_URL ?>/panier" class="btn btn-primary">Retour au panier</a>
            <a href="<?= APP_URL ?>/catalogue" class="btn btn-ghost">Voir le catalogue</a>
        </div>

    </div>
</section>