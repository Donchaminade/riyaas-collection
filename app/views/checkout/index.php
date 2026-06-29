<?php /* app/views/checkout/index.php */ ?>

<section style="padding:4rem 0 6rem; background:var(--ivory);">
    <div class="container">

        <h1 style="font-family:var(--font-display); font-size:2.5rem; font-weight:300;
                   margin-bottom:3rem; padding-bottom:1rem; border-bottom:1px solid var(--blush);">
            Finaliser ma commande
        </h1>

        <div style="display:grid; grid-template-columns:1fr 380px; gap:3rem; align-items:start;">

            <!-- FORMULAIRE INFOS -->
            <div>
                <form method="POST" action="<?= APP_URL ?>/commander">

                    <!-- Infos personnelles -->
                    <div style="background:var(--white); padding:2rem; border:1px solid var(--blush); margin-bottom:2rem;">
                        <h2 style="font-family:var(--font-display); font-size:1.3rem; font-weight:400;
                                   margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--blush);">
                            Vos informations
                        </h2>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                            <div class="form-group">
                                <label>Prénom *</label>
                                <input type="text" name="first_name" required placeholder="Ex: Ama">
                            </div>
                            <div class="form-group">
                                <label>Nom *</label>
                                <input type="text" name="last_name" required placeholder="Ex: Koffi">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" required placeholder="votre@email.com">
                        </div>

                        <div class="form-group">
                            <label>Numéro TMoney * (pour le paiement)</label>
                            <input type="tel" name="phone" required placeholder="Ex: 90 00 00 00"
                                   style="font-size:1rem;">
                            <p style="font-size:0.72rem; color:var(--gold); margin-top:0.4rem;">
                                ⚠️ Ce numéro sera utilisé pour débiter l'acompte via TMoney
                            </p>
                        </div>
                    </div>

                    <!-- Infos livraison -->
                    <div style="background:var(--white); padding:2rem; border:1px solid var(--blush); margin-bottom:2rem;">
                        <h2 style="font-family:var(--font-display); font-size:1.3rem; font-weight:400;
                                   margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--blush);">
                            Adresse de livraison
                        </h2>

                        <div class="form-group">
                            <label>Quartier / Adresse *</label>
                            <input type="text" name="address" required placeholder="Ex: Adidogomé, rue des manguiers">
                        </div>

                        <div class="form-group">
                            <label>Ville *</label>
                            <input type="text" name="city" required value="Lomé">
                        </div>

                        <div class="form-group">
                            <label>Instructions de livraison (facultatif)</label>
                            <textarea name="delivery_notes" rows="3"
                                      placeholder="Ex: Maison bleue en face de l'école..."></textarea>
                        </div>
                    </div>

                    <!-- Récapitulatif paiement -->
                    <div style="background:var(--obsidian); padding:1.5rem; color:var(--ivory); margin-bottom:2rem;">
                        <p style="font-size:0.68rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--gold); margin-bottom:1rem;">
                            Paiement TMoney
                        </p>
                        <p style="font-size:0.88rem; line-height:1.7; color:#9B9490;">
                            En cliquant sur <strong style="color:var(--ivory);">"Payer l'acompte"</strong>,
                            vous serez redirigée vers la page de paiement TMoney sécurisée pour régler
                            <strong style="color:var(--gold);">50% du montant total</strong>.
                            Le reste sera payé à la livraison.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary"
                            style="width:100%; padding:1.2rem; font-size:0.82rem;">
                        Payer l'acompte via TMoney →
                    </button>

                </form>
            </div>

            <!-- RÉSUMÉ COMMANDE -->
            <div style="background:var(--white); padding:2rem; border:1px solid var(--blush); position:sticky; top:100px;">
                <h3 style="font-family:var(--font-display); font-size:1.3rem; font-weight:400;
                           margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--blush);">
                    Votre commande
                </h3>

                <!-- Articles -->
                <?php foreach ($cart as $item): ?>
                <div style="display:flex; gap:1rem; margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid var(--blush);">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= APP_URL ?>/assets/images/products/<?= htmlspecialchars($item['image']) ?>"
                             style="width:50px; height:65px; object-fit:cover;">
                    <?php else: ?>
                        <div style="width:50px; height:65px; background:var(--blush);"></div>
                    <?php endif; ?>
                    <div style="flex:1;">
                        <p style="font-size:0.85rem; font-weight:400;"><?= htmlspecialchars($item['product_name']) ?></p>
                        <?php if (!empty($item['size'])): ?>
                            <p style="font-size:0.72rem; color:var(--mist);">Taille : <?= htmlspecialchars($item['size']) ?></p>
                        <?php endif; ?>
                        <p style="font-size:0.72rem; color:var(--mist);">Qté : <?= $item['quantity'] ?></p>
                    </div>
                    <p style="font-size:0.85rem; font-weight:500;">
                        <?= number_format($item['unit_price'] * $item['quantity'], 0, ',', ' ') ?> FCFA
                    </p>
                </div>
                <?php endforeach; ?>

                <!-- Totaux -->
                <div style="margin-top:1rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.6rem; font-size:0.85rem;">
                        <span>Total commande</span>
                        <span><?= number_format($total, 0, ',', ' ') ?> FCFA</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; padding:0.8rem;
                                background:var(--ivory); border-left:3px solid var(--gold);
                                margin:1rem 0; font-size:0.88rem;">
                        <span style="color:var(--gold); font-weight:500;">Acompte TMoney (50%)</span>
                        <span style="color:var(--gold); font-weight:500;"><?= number_format($deposit, 0, ',', ' ') ?> FCFA</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; font-size:0.82rem; color:var(--mist);">
                        <span>Reste à la livraison</span>
                        <span><?= number_format($balance, 0, ',', ' ') ?> FCFA</span>
                    </div>
                </div>

                <p style="font-size:0.7rem; color:var(--mist); text-align:center; margin-top:1.5rem;">
                    ⏱ Livraison garantie sous <strong>5 jours</strong><br>
                    📍 Remise en main propre — Lomé
                </p>
            </div>

        </div>
    </div>
</section>