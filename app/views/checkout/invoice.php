<?php /* app/views/checkout/invoice.php */ ?>

<section style="padding:4rem 0 6rem; background:var(--ivory);">
    <div class="container" style="max-width:750px; margin:0 auto;">

        <!-- Bouton imprimer -->
        <div style="text-align:right; margin-bottom:2rem;">
            <button onclick="window.print()" class="btn btn-outline">🖨️ Imprimer / Sauvegarder PDF</button>
        </div>

        <!-- Facture -->
        <div style="background:var(--white); padding:3rem; border:1px solid var(--blush);">

            <!-- En-tête -->
            <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:3rem;">
                <div>
                    <p style="font-family:var(--font-display); font-size:1.8rem; font-weight:600; color:var(--obsidian);">Riyaa's</p>
                    <p style="font-size:0.6rem; letter-spacing:0.3em; text-transform:uppercase; color:var(--gold);">Collection</p>
                    <p style="font-size:0.78rem; color:var(--mist); margin-top:0.5rem;">Lomé, Togo</p>
                    <p style="font-size:0.78rem; color:var(--mist);">Paiement via TMoney</p>
                </div>
                <div style="text-align:right;">
                    <p style="font-size:0.68rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--mist); margin-bottom:0.3rem;">Facture</p>
                    <p style="font-family:var(--font-display); font-size:1.3rem; font-weight:600;">
                        <?= htmlspecialchars($order['order_number']) ?>
                    </p>
                    <p style="font-size:0.78rem; color:var(--mist); margin-top:0.3rem;">
                        Date : <?= date('d/m/Y', strtotime($order['created_at'])) ?>
                    </p>
                </div>
            </div>

            <!-- Infos cliente -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; margin-bottom:2rem;
                        padding:1.5rem; background:var(--ivory);">
                <div>
                    <p style="font-size:0.65rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--mist); margin-bottom:0.5rem;">Cliente</p>
                    <p style="font-size:0.88rem; font-weight:500;"><?= htmlspecialchars($order['customer_name']) ?></p>
                    <p style="font-size:0.82rem; color:var(--mist);"><?= htmlspecialchars($order['customer_email']) ?></p>
                    <p style="font-size:0.82rem; color:var(--mist);"><?= htmlspecialchars($order['customer_phone']) ?></p>
                </div>
                <div>
                    <p style="font-size:0.65rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--mist); margin-bottom:0.5rem;">Livraison</p>
                    <p style="font-size:0.88rem;"><?= htmlspecialchars($order['delivery_address']) ?></p>
                    <p style="font-size:0.82rem; color:var(--mist);"><?= htmlspecialchars($order['delivery_city']) ?></p>
                    <p style="font-size:0.82rem; color:var(--mist);">Avant le <?= date('d/m/Y', strtotime($order['delivery_deadline'])) ?></p>
                </div>
            </div>

            <!-- Articles -->
            <table style="width:100%; border-collapse:collapse; margin-bottom:2rem;">
                <thead>
                    <tr style="border-bottom:2px solid var(--obsidian);">
                        <th style="text-align:left; font-size:0.65rem; letter-spacing:0.15em; text-transform:uppercase; padding:0.75rem 0; color:var(--mist); font-weight:400;">Article</th>
                        <th style="text-align:center; font-size:0.65rem; letter-spacing:0.15em; text-transform:uppercase; padding:0.75rem 0; color:var(--mist); font-weight:400;">Qté</th>
                        <th style="text-align:right; font-size:0.65rem; letter-spacing:0.15em; text-transform:uppercase; padding:0.75rem 0; color:var(--mist); font-weight:400;">Prix unitaire</th>
                        <th style="text-align:right; font-size:0.65rem; letter-spacing:0.15em; text-transform:uppercase; padding:0.75rem 0; color:var(--mist); font-weight:400;">Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr style="border-bottom:1px solid var(--blush);">
                        <td style="padding:1rem 0; font-size:0.88rem;">
                            <?= htmlspecialchars($item['product_name']) ?>
                            <?php if (!empty($item['size'])): ?>
                                <span style="color:var(--mist); font-size:0.75rem;"> — Taille <?= htmlspecialchars($item['size']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center; font-size:0.88rem; padding:1rem 0;"><?= $item['quantity'] ?></td>
                        <td style="text-align:right; font-size:0.88rem; padding:1rem 0;"><?= number_format($item['unit_price'], 0, ',', ' ') ?> FCFA</td>
                        <td style="text-align:right; font-size:0.88rem; font-weight:500; padding:1rem 0;"><?= number_format($item['subtotal'], 0, ',', ' ') ?> FCFA</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Totaux -->
            <div style="max-width:300px; margin-left:auto;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.6rem; font-size:0.88rem;">
                    <span style="color:var(--mist);">Montant total</span>
                    <span><?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA</span>
                </div>

                <div style="display:flex; justify-content:space-between; padding:0.8rem;
                            background:#e8f5ee; color:#2E7D52; font-size:0.88rem;
                            font-weight:500; margin:0.75rem 0;">
                    <span>✓ Acompte payé via TMoney</span>
                    <span><?= number_format($order['deposit_amount'], 0, ',', ' ') ?> FCFA</span>
                </div>

                <div style="display:flex; justify-content:space-between; padding:0.8rem;
                            background:var(--ivory); border-left:3px solid var(--gold); font-size:0.88rem;">
                    <span style="color:var(--gold); font-weight:500;">Reste à payer à la livraison</span>
                    <span style="color:var(--gold); font-weight:500;"><?= number_format($order['balance_amount'], 0, ',', ' ') ?> FCFA</span>
                </div>
            </div>

            <!-- Note bas de facture -->
            <div style="margin-top:3rem; padding-top:2rem; border-top:1px solid var(--blush); text-align:center;">
                <p style="font-size:0.75rem; color:var(--mist); line-height:1.8;">
                    Merci pour votre confiance. Votre pièce est confectionnée avec soin.<br>
                    Le solde de <strong><?= number_format($order['balance_amount'], 0, ',', ' ') ?> FCFA</strong>
                    sera réglé à la remise en main propre.<br>
                    <strong style="color:var(--obsidian);">Riyaa's Collection — Lomé, Togo</strong>
                </p>
            </div>

        </div>
    </div>
</section>

<style>
@media print {
    .site-header, .site-footer, button { display: none !important; }
    body { background: white; }
}
</style>