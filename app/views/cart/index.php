<?php /* app/views/cart/index.php */ ?>

<section class="cart-page">
    <div class="container">

        <h1>Mon panier</h1>

        <?php if (empty($cart)): ?>
            <!-- Panier vide -->
            <div style="text-align:center; padding:5rem 0;">
                <p style="font-family:var(--font-display); font-size:1.5rem; font-weight:300; color:var(--mist); margin-bottom:1rem;">
                    Votre panier est vide
                </p>
                <p style="font-size:0.85rem; color:var(--mist); margin-bottom:2rem;">
                    Découvrez nos modèles en soie et ajoutez vos pièces préférées.
                </p>
                <a href="<?= APP_URL ?>/catalogue" class="btn btn-primary">Voir le catalogue</a>
            </div>

        <?php else: ?>
            <div style="display:grid; grid-template-columns:1fr 380px; gap:3rem; align-items:start;">

                <!-- ── ARTICLES ──────────────────────────── -->
                <div>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Article</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $key => $item): ?>
                            <tr>
                                <!-- Image + nom -->
                                <td>
                                    <div style="display:flex; gap:1rem; align-items:center;">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?= APP_URL ?>/assets/images/products/<?= htmlspecialchars($item['image']) ?>"
                                                 style="width:60px; height:75px; object-fit:cover; background:var(--blush);"
                                                 alt="<?= htmlspecialchars($item['product_name']) ?>">
                                        <?php else: ?>
                                            <div style="width:60px; height:75px; background:var(--blush);"></div>
                                        <?php endif; ?>
                                        <div>
                                            <p style="font-weight:400; font-size:0.9rem; margin-bottom:0.2rem;">
                                                <?= htmlspecialchars($item['product_name']) ?>
                                            </p>
                                            <?php if (!empty($item['size'])): ?>
                                                <p style="font-size:0.75rem; color:var(--mist);">Taille : <?= htmlspecialchars($item['size']) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($item['color'])): ?>
                                                <p style="font-size:0.75rem; color:var(--mist);">Couleur : <?= htmlspecialchars($item['color']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Prix unitaire -->
                                <td style="font-size:0.9rem;">
                                    <?= number_format($item['unit_price'], 0, ',', ' ') ?> FCFA
                                </td>

                                <!-- Quantité -->
                                <td>
                                    <form method="POST" action="<?= APP_URL ?>/panier/modifier"
                                          style="display:flex; align-items:center; gap:0.3rem;">
                                        <input type="hidden" name="cart_key" value="<?= htmlspecialchars($key) ?>">
                                        <div style="display:flex; align-items:center; border:1px solid var(--blush);">
                                            <button type="submit" name="quantity"
                                                    value="<?= max(1, $item['quantity'] - 1) ?>"
                                                    style="width:28px; height:28px; border:none; background:none; cursor:pointer; font-size:0.9rem;">−</button>
                                            <span style="width:30px; text-align:center; font-size:0.85rem;"><?= $item['quantity'] ?></span>
                                            <button type="submit" name="quantity"
                                                    value="<?= $item['quantity'] + 1 ?>"
                                                    style="width:28px; height:28px; border:none; background:none; cursor:pointer; font-size:0.9rem;">+</button>
                                        </div>
                                    </form>
                                </td>

                                <!-- Sous-total -->
                                <td style="font-weight:500; font-size:0.9rem;">
                                    <?= number_format($item['unit_price'] * $item['quantity'], 0, ',', ' ') ?> FCFA
                                </td>

                                <!-- Supprimer -->
                                <td>
                                    <form method="POST" action="<?= APP_URL ?>/panier/supprimer">
                                        <input type="hidden" name="cart_key" value="<?= htmlspecialchars($key) ?>">
                                        <button type="submit"
                                                style="background:none; border:none; cursor:pointer; color:var(--mist); font-size:1.2rem; transition:color 0.2s;"
                                                onmouseover="this.style.color='#C0392B'"
                                                onmouseout="this.style.color='var(--mist)'"
                                                title="Supprimer">×</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <a href="<?= APP_URL ?>/catalogue"
                       style="font-size:0.75rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--mist); text-decoration:none;">
                        ← Continuer mes achats
                    </a>
                </div>

                <!-- ── RÉSUMÉ ─────────────────────────────── -->
                <div class="cart-summary" style="position:sticky; top:100px;">
                    <h3>Résumé de la commande</h3>

                    <div class="summary-line">
                        <span>Sous-total</span>
                        <span><?= number_format($total, 0, ',', ' ') ?> FCFA</span>
                    </div>

                    <div class="summary-line deposit">
                        <span> Acompte à payer maintenant (50%)</span>
                        <span><?= number_format($deposit, 0, ',', ' ') ?> FCFA</span>
                    </div>

                    <div class="summary-line">
                        <span style="color:var(--mist);">Reste à la livraison (50%)</span>
                        <span style="color:var(--mist);"><?= number_format($balance, 0, ',', ' ') ?> FCFA</span>
                    </div>

                    <div class="summary-line total">
                        <span>Total</span>
                        <span><?= number_format($total, 0, ',', ' ') ?> FCFA</span>
                    </div>

                    <!-- Info paiement -->
                    <div style="background:var(--ivory); padding:1rem; margin:1rem 0; font-size:0.78rem; color:var(--mist); line-height:1.6; border-left:3px solid var(--gold);">
                        <strong style="color:var(--obsidian); display:block; margin-bottom:0.3rem;">Comment ça marche ?</strong>
                        Vous payez l'acompte de <strong style="color:var(--gold);"><?= number_format($deposit, 0, ',', ' ') ?> FCFA</strong> 
                        via TMoney maintenant. Le reste (<strong><?= number_format($balance, 0, ',', ' ') ?> FCFA</strong>) 
                        est payé à la livraison en espèces ou TMoney.
                    </div>

                    <!-- Délai -->
                    <p style="font-size:0.75rem; color:var(--mist); text-align:center; margin-bottom:1.5rem;">
                        ⏱ Livraison garantie sous <strong>5 jours</strong>
                    </p>

                    <a href="#" id="whatsapp-cart-btn" target="_blank"
                       style="display:flex; align-items:center; justify-content:center; gap:0.5rem;
                              width:100%; padding:1rem; background:#25D366; color:white; text-decoration:none;
                              font-size:0.78rem; letter-spacing:0.1em; text-transform:uppercase; font-weight:500;">
                         Commander via WhatsApp
                    </a>

                    <p style="text-align:center; font-size:0.7rem; color:var(--mist); margin-top:1rem;">
                        Paiement sécurisé via <strong>TMoney</strong> · Livraison sous <strong>5 jours</strong>
                    </p>

                    <script>
                    document.getElementById('whatsapp-cart-btn').addEventListener('click', function(e) {
                        e.preventDefault();
                        const whatsappNumber = '22890128638';
                        let lines = [];
                        <?php foreach ($cart as $item): ?>
                        lines.push('- <?= addslashes($item['product_name']) ?> (Quantité : <?= $item['quantity'] ?>)');
                        <?php endforeach; ?>

                        const message = "Bonjour Riyaa's Collection, je souhaite commander :\n" + lines.join('\n') +
                            "\nTotal : <?= number_format($total, 0, '', ' ') ?> FCFA";

                        window.open('https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(message), '_blank');
                    });
                    </script>
                </div>

            </div>
        <?php endif; ?>

    </div>
</section>
