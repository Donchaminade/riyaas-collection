<?php /* app/views/admin/orders.php */ ?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span>Riyaa's</span>
            <span>Collection — Admin</span>
        </div>
        <nav>
            <a href="<?= APP_URL ?>/admin">🗂 Produits</a>
            <a href="<?= APP_URL ?>/admin/produit/ajouter">➕ Ajouter un produit</a>
            <a href="<?= APP_URL ?>/admin/commandes" class="active">📦 Commandes</a>
            <a href="<?= APP_URL ?>/" target="_blank">🌐 Voir le site</a>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= APP_URL ?>/admin/deconnexion">Se déconnecter</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Commandes</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Total commandes</div>
                <div class="value"><?= count($orders) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Acompte reçu</div>
                <div class="value"><?= count(array_filter($orders, fn($o) => $o['payment_status'] === 'deposit_paid')) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">En production</div>
                <div class="value"><?= count(array_filter($orders, fn($o) => $o['order_status'] === 'in_production')) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Terminées</div>
                <div class="value"><?= count(array_filter($orders, fn($o) => $o['order_status'] === 'completed')) ?></div>
            </div>
        </div>

        <div class="admin-table-wrap">
            <div class="admin-table-head">
                <h2>Toutes les commandes</h2>
            </div>

            <?php if (empty($orders)): ?>
                <div style="padding:3rem; text-align:center; color:#9B9490;">
                    <p>Aucune commande pour l'instant.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Cliente</th>
                            <th>Téléphone</th>
                            <th>Total</th>
                            <th>Acompte</th>
                            <th>Paiement</th>
                            <th>Statut</th>
                            <th>Livraison avant</th>
                            <th>Facture</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($o['order_number']) ?></strong><br>
                                <span style="font-size:0.72rem; color:#9B9490;"><?= date('d/m/Y', strtotime($o['created_at'])) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($o['customer_name']) ?><br>
                                <span style="font-size:0.72rem; color:#9B9490;"><?= htmlspecialchars($o['customer_email']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($o['customer_phone']) ?></td>
                            <td><strong><?= number_format($o['total_amount'], 0, ',', ' ') ?> FCFA</strong></td>
                            <td style="color:#B8975A;"><?= number_format($o['deposit_amount'], 0, ',', ' ') ?> FCFA</td>
                            <td>
                                <?php
                                $pLabels = [
                                    'awaiting_deposit' => '⏳ En attente',
                                    'deposit_paid'     => '✅ Acompte reçu',
                                    'fully_paid'       => '✅ Tout payé',
                                ];
                                $pColors = [
                                    'awaiting_deposit' => '#fef3e2',
                                    'deposit_paid'     => '#e8f5ee',
                                    'fully_paid'       => '#e8f5ee',
                                ];
                                $pLabel = $pLabels[$o['payment_status']] ?? $o['payment_status'];
                                $pColor = $pColors[$o['payment_status']] ?? '#f0f0f0';
                                ?>
                                <span style="background:<?= $pColor ?>; padding:0.3rem 0.6rem; font-size:0.72rem;">
                                    <?= $pLabel ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="<?= APP_URL ?>/admin/commande/statut">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <select name="status" onchange="this.form.submit()"
                                            style="font-size:0.75rem; padding:0.3rem; border:1px solid var(--blush);">
                                        <option value="pending"       <?= $o['order_status']==='pending'       ? 'selected':'' ?>>En attente</option>
                                        <option value="deposit_paid"  <?= $o['order_status']==='deposit_paid'  ? 'selected':'' ?>>Acompte reçu</option>
                                        <option value="in_production" <?= $o['order_status']==='in_production' ? 'selected':'' ?>>En production</option>
                                        <option value="ready"         <?= $o['order_status']==='ready'         ? 'selected':'' ?>>Prête</option>
                                        <option value="delivered"     <?= $o['order_status']==='delivered'     ? 'selected':'' ?>>Livrée</option>
                                        <option value="completed"     <?= $o['order_status']==='completed'     ? 'selected':'' ?>>Terminée</option>
                                        <option value="cancelled"     <?= $o['order_status']==='cancelled'     ? 'selected':'' ?>>Annulée</option>
                                    </select>
                                </form>
                            </td>
                            <td><?= $o['delivery_deadline'] ? date('d/m/Y', strtotime($o['delivery_deadline'])) : '—' ?></td>
                            <td>
                                <a href="<?= APP_URL ?>/facture?ref=<?= htmlspecialchars($o['order_number']) ?>"
                                   class="btn btn-ghost btn-sm" target="_blank">Voir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>
