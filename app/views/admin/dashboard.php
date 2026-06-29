<?php /* app/views/admin/dashboard.php */ ?>

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span>Riyaa's</span>
            <span>Collection — Admin</span>
        </div>
        <nav>
            <a href="<?= APP_URL ?>/admin" class="active">🗂 Produits</a>
            <a href="<?= APP_URL ?>/admin/produit/ajouter">➕ Ajouter un produit</a>
            <a href="<?= APP_URL ?>/admin/commandes">📦 Commandes</a>
            <a href="<?= APP_URL ?>/" target="_blank">🌐 Voir le site</a>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= APP_URL ?>/admin/deconnexion">Se déconnecter</a>
        </div>
    </aside>

    <!-- Contenu principal -->
    <main class="admin-main">

        <div class="admin-header">
            <h1>Produits</h1>
            <a href="<?= APP_URL ?>/admin/produit/ajouter" class="btn btn-gold">+ Ajouter un produit</a>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Total produits</div>
                <div class="value"><?= count($products) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Catégories</div>
                <div class="value"><?= count($categories) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Mis en avant</div>
                <div class="value"><?= count(array_filter($products, fn($p) => $p['is_featured'])) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Sur commande</div>
                <div class="value"><?= count(array_filter($products, fn($p) => $p['stock_status'] === 'made_to_order')) ?></div>
            </div>
        </div>

        <!-- Table produits -->
        <div class="admin-table-wrap">
            <div class="admin-table-head">
                <h2>Tous les produits</h2>
            </div>

            <?php if (empty($products)): ?>
                <div style="padding:3rem; text-align:center; color:#9B9490;">
                    <p style="font-size:0.9rem;">Aucun produit pour l'instant.</p>
                    <a href="<?= APP_URL ?>/admin/produit/ajouter" class="btn btn-primary" style="margin-top:1rem;">
                        Ajouter votre premier produit
                    </a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Mis en avant</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <?php if (!empty($p['cover_image'])): ?>
                                    <img src="<?= APP_URL ?>/assets/images/products/<?= htmlspecialchars($p['cover_image']) ?>"
                                         class="product-thumb" alt="<?= htmlspecialchars($p['name']) ?>">
                                <?php else: ?>
                                    <div class="product-thumb-placeholder"></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                                <span style="color:#9B9490; font-size:0.75rem;"><?= htmlspecialchars($p['material']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($p['category_name']) ?></td>
                            <td><strong><?= number_format($p['price'], 0, ',', ' ') ?> FCFA</strong></td>
                            <td>
                                <?php if ($p['stock_status'] === 'made_to_order'): ?>
                                    <span class="badge badge-order">Sur commande</span>
                                <?php else: ?>
                                    <span class="badge badge-active">Disponible</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?= $p['is_featured'] ? '⭐' : '—' ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:0.5rem;">
                                    <a href="<?= APP_URL ?>/admin/produit/modifier?id=<?= $p['id'] ?>"
                                       class="btn btn-ghost btn-sm">Modifier</a>

                                    <form method="POST" action="<?= APP_URL ?>/admin/produit/supprimer"
                                          onsubmit="return confirm('Supprimer ce produit ?')">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>
</div>
