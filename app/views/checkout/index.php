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
                <form method="POST" action="<?= APP_URL ?>/commander" id="checkout-form">

                    <!-- Infos rapides — 4 champs max -->
                    <div style="background:var(--white); padding:2rem; border:1px solid var(--blush); margin-bottom:2rem;">
                        <h2 style="font-family:var(--font-display); font-size:1.3rem; font-weight:400;
                                   margin-bottom:0.5rem; padding-bottom:1rem; border-bottom:1px solid var(--blush);">
                            Vos informations
                        </h2>
                        <p style="font-size:0.78rem; color:var(--gold); margin-bottom:1.5rem;">
                            ⚡ Commande rapide — 4 infos suffisent
                        </p>

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
                            <label>Numéro TMoney * (pour le paiement et la livraison)</label>
                            <input type="tel" name="phone" required placeholder="Ex: 90 00 00 00"
                                   style="font-size:1rem;">
                        </div>

                        <div class="form-group">
                            <label>Ville de livraison *</label>
                            <input type="text" name="city" required value="Lomé" placeholder="Ex: Lomé">
                        </div>

                        <!-- Champs cachés simplifiés (valeurs par défaut) -->
                        <input type="hidden" name="email" value="">
                        <input type="hidden" name="address" id="address-hidden" value="">
                        <input type="hidden" name="delivery_notes" value="">

                        <div class="form-group">
                            <label>Quartier / Adresse précise (facultatif)</label>
                            <input type="text" id="address-visible" placeholder="Ex: Adidogomé, rue des manguiers">
                        </div>
                    </div>

                   <!-- Récapitulatif paiement -->
                    <div style="background:var(--obsidian); padding:1.5rem; color:var(--ivory); margin-bottom:2rem;">
                        <p style="font-size:0.68rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--gold); margin-bottom:1rem;">
                            Comment ça marche
                        </p>
                        <p style="font-size:0.88rem; line-height:1.7; color:#9B9490;">
                            En cliquant sur <strong style="color:var(--ivory);">"Valider ma commande sur WhatsApp"</strong>,
                            un message récapitulatif s'ouvrira avec toutes vos infos. Vous enverrez ensuite
                            votre acompte de <strong style="color:var(--gold);">50% via TMoney</strong>
                            pour lancer la confection. Le reste sera payé à la livraison.
                        </p>
                    </div>
                </form>

                <!-- Bouton validation WhatsApp - moyen principal -->
                <a href="#" id="whatsapp-final-btn" target="_blank"
                   style="display:flex; align-items:center; justify-content:center; gap:0.5rem;
                          width:100%; padding:1rem; background:#25D366; color:white; text-decoration:none;
                          font-size:0.78rem; letter-spacing:0.1em; text-transform:uppercase; font-weight:500;">
                     Valider et envoyer sur WhatsApp
                </a>
                <p style="font-size:0.7rem; color:var(--mist); text-align:center; margin-top:0.5rem;">
                    Remplissez le formulaire ci-dessus puis confirmez via WhatsApp
                </p>
            </div>
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
<script>
// ── Synchroniser le champ adresse caché avec le champ visible ──
document.getElementById('address-visible').addEventListener('input', function() {
    document.getElementById('address-hidden').value = this.value;
});

// ── Message WhatsApp de validation finale ───────────────────
document.getElementById('whatsapp-final-btn').addEventListener('click', function(e) {
    e.preventDefault();

    const form = document.getElementById('checkout-form');
    const firstName = form.first_name.value.trim();
    const lastName  = form.last_name.value.trim();
    const phone     = form.phone.value.trim();
    const city      = form.city.value.trim();

    if (!firstName || !lastName || !phone || !city) {
        alert('Merci de remplir votre prénom, nom, téléphone et ville avant de valider sur WhatsApp.');
        return;
    }

    const whatsappNumber = '22890128638';

    let lines = [];
    <?php foreach ($cart as $item): ?>
    lines.push('- <?= addslashes($item['product_name']) ?> (Quantité : <?= $item['quantity'] ?>)');
    <?php endforeach; ?>

    const total   = <?= $total ?>;
    const deposit = <?= $deposit ?>;

    const message =
        "Bonjour Riyaa's Collection, voici ma commande officielle :\n" +
        lines.join('\n') + "\n" +
        "Total : " + total.toLocaleString('fr-FR') + " FCFA\n\n" +
        "Cliente : " + firstName + " " + lastName + "\n" +
        "Téléphone : " + phone + "\n" +
        "Ville : " + city + "\n\n" +
        " Je valide les conditions : Je m'apprête à envoyer mon acompte de 50% (" +
        deposit.toLocaleString('fr-FR') + " FCFA) par TMoney (Mix by Yass) au numéro 70 92 66 76 pour lancer la confection " +
        "de mes vêtements en soie. Le reste sera payé à la livraison sous 5 jours.";

    window.open('https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(message), '_blank');
});
</script>