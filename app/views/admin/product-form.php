<?php /* app/views/admin/product-form.php */ ?>

<div class="admin-layout">

    <aside class="sidebar">
        <div class="sidebar-logo">
            <span>Riyaa's</span>
            <span>Collection — Admin</span>
        </div>
        <nav>
            <a href="<?= APP_URL ?>/admin">🗂 Produits</a>
            <a href="<?= APP_URL ?>/admin/produit/ajouter" class="active">➕ Ajouter un produit</a>
            <a href="<?= APP_URL ?>/admin/commandes">📦 Commandes</a>
            <a href="<?= APP_URL ?>/" target="_blank">🌐 Voir le site</a>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= APP_URL ?>/admin/deconnexion">Se déconnecter</a>
        </div>
    </aside>

    <main class="admin-main">

        <div class="admin-header">
            <h1><?= $product ? 'Modifier le produit' : 'Ajouter un produit' ?></h1>
            <a href="<?= APP_URL ?>/admin" class="btn btn-ghost">← Retour</a>
        </div>

        <form method="POST"
              action="<?= $product ? APP_URL . '/admin/produit/modifier' : APP_URL . '/admin/produit/ajouter' ?>"
              enctype="multipart/form-data">

            <?php if ($product): ?>
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
            <?php endif; ?>

            <div class="form-card">
                <div class="form-grid">

                    <!-- Nom -->
                    <div class="form-group">
                        <label>Nom du produit *</label>
                        <input type="text" name="name" required
                               value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                               placeholder="Ex: Jupe en soie ivoire">
                    </div>

                    <!-- Catégorie -->
                    <div class="form-group">
                        <label>Catégorie *</label>
                        <select name="category_id" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                  <!-- Prix -->
                    <div class="form-group">
                        <label>Prix (FCFA) *</label>
                        <input type="number" name="price" required min="0" step="500"
                               value="<?= $product['price'] ?? '' ?>"
                               placeholder="Ex: 45000">
                    </div>

                    <!-- Stock -->
                    <div class="form-group">
                        <label>Quantité en stock</label>
                        <input type="number" name="stock_quantity" min="0"
                               value="<?= $product['stock_quantity'] ?? '' ?>"
                               placeholder="Ex: 8">
                    </div>

                    <!-- Seuil alerte stock -->
                    <div class="form-group">
                        <label>Alerte stock bas (en dessous de)</label>
                        <input type="number" name="seuil_alerte_stock" min="0"
                               value="<?= $product['seuil_alerte_stock'] ?? 3 ?>"
                               placeholder="Ex: 3">
                        <p style="font-size:0.72rem; color:#9B9490; margin-top:0.4rem;">
                            Affiche "Stock limité" si la quantité passe sous ce chiffre
                        </p>
                    </div>
                    </div>

                    <!-- Matière -->
                    <div class="form-group">
                        <label>Matière</label>
                        <input type="text" name="material"
                               value="<?= htmlspecialchars($product['material'] ?? 'Soie naturelle 100%') ?>">
                    </div>

                    <!-- Description -->
                    <div class="form-group full">
                        <label>Description</label>
                        <textarea name="description" placeholder="Décrivez le produit, la coupe, les détails..."><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Tailles -->
                    <div class="form-group full">
                        <label>Tailles disponibles</label>
                        <div class="sizes-input" id="sizes-container">
                            <?php
                            $existingSizes = [];
                            if (!empty($variants)) {
                                foreach ($variants as $v) {
                                    if ($v['size']) $existingSizes[] = $v['size'];
                                }
                            }
                            foreach ($existingSizes as $size): ?>
                                <div class="size-tag">
                                    <span><?= htmlspecialchars($size) ?></span>
                                    <input type="hidden" name="sizes[]" value="<?= htmlspecialchars($size) ?>">
                                    <button type="button" onclick="this.parentElement.remove()">×</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="add-size">
                            <input type="text" id="new-size-input" placeholder="Ex: S, M, L, XL">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="addSize()">+ Ajouter</button>
                        </div>
                        <p style="font-size:0.72rem; color:#9B9490; margin-top:0.5rem;">
                            Tapez la taille et cliquez sur Ajouter. Vous pouvez aussi écrire "Sur-mesure".
                        </p>
                    </div>

                  <!-- Mis en avant -->
                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; text-transform:none; letter-spacing:0;">
                            <input type="checkbox" name="is_featured" value="1"
                                   <?= (!empty($product['is_featured'])) ? 'checked' : '' ?>
                                   style="width:auto; accent-color: #B8975A;">
                            <span style="font-size:0.82rem; color:#1A1714;">Afficher en page d'accueil ⭐</span>
                        </label>
                    </div>

                    <!-- Promotion -->
                    <div class="form-group full" style="background:#fef3e2; padding:1.2rem; border-left:3px solid #B8600A;">
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; text-transform:none; letter-spacing:0; margin-bottom:1rem;">
                            <input type="checkbox" name="en_promotion" value="1" id="promo-checkbox"
                                   <?= (!empty($product['en_promotion'])) ? 'checked' : '' ?>
                                   onchange="document.getElementById('promo-price-wrap').style.display = this.checked ? 'block' : 'none'"
                                   style="width:auto; accent-color: #B8600A;">
                            <span style="font-size:0.85rem; font-weight:500; color:#B8600A;">🔥 Activer la promotion</span>
                        </label>
                        <div id="promo-price-wrap" style="display:<?= !empty($product['en_promotion']) ? 'block' : 'none' ?>;">
                            <label>Prix promo (FCFA)</label>
                            <input type="number" name="prix_promotion" min="0" step="500"
                                   value="<?= $product['prix_promotion'] ?? '' ?>"
                                   placeholder="Ex: 30000">
                        </div>
                    </div>

                </div>

                <!-- Upload images -->
                <div style="margin-top:1rem;">
                    <label>Photos du produit</label>

                    <?php if (!empty($images)): ?>
                        <div class="images-preview" style="margin-bottom:1rem;">
                            <?php foreach ($images as $img): ?>
                            <div class="image-preview-item" id="img-<?= $img['id'] ?>">
                                <img src="<?= APP_URL ?>/assets/images/products/<?= htmlspecialchars($img['image_path']) ?>"
                                     alt="Photo produit">
                                <?php if ($img['is_cover']): ?>
                                    <div style="text-align:center; font-size:0.6rem; background:#fef3e2; padding:2px; color:#B8600A;">COUVERTURE</div>
                                <?php else: ?>
                                    <button type="button"
                                            onclick="setCover(<?= $img['id'] ?>, <?= $product['id'] ?>)"
                                            style="width:100%; font-size:0.6rem; padding:2px; background:var(--blush); border:none; cursor:pointer; margin-top:2px;">
                                        Mettre en couverture
                                    </button>
                                <?php endif; ?>
                                <button class="remove-img" type="button"
                                        onclick="deleteImage(<?= $img['id'] ?>)">×</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="upload-zone" onclick="document.getElementById('file-input').click()">
                        <input type="file" id="file-input" name="images[]"
                               multiple accept="image/jpeg,image/png,image/webp"
                               onchange="previewImages(this)">
                        <div style="font-size:2rem;">📷</div>
                        <p><strong>Cliquez pour choisir vos photos</strong></p>
                        <p>JPG, PNG ou WebP — Plusieurs images possibles</p>
                        <p style="color:#B8975A; margin-top:0.3rem;">La première image sera l'image principale</p>
                    </div>

                    <div class="images-preview" id="new-images-preview"></div>
                </div>

                <!-- Actions -->
                <div style="display:flex; gap:1rem; margin-top:2rem; padding-top:1.5rem; border-top:1px solid var(--blush);">
                    <button type="submit" class="btn btn-primary">
                        <?= $product ? 'Enregistrer les modifications' : 'Ajouter le produit' ?>
                    </button>
                    <a href="<?= APP_URL ?>/admin" class="btn btn-ghost">Annuler</a>
                </div>

            </div>
        </form>

    </main>
</div>

<script>
// ── Aperçu images avant upload ──────────────────────────────
function previewImages(input) {
    const preview = document.getElementById('new-images-preview');
    preview.innerHTML = '';
    Array.from(input.files).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `<img src="${e.target.result}" style="width:100px;height:130px;object-fit:cover;">
                             ${i === 0 ? '<div style="text-align:center;font-size:0.6rem;background:#fef3e2;padding:2px;color:#B8600A;">COUVERTURE</div>' : ''}`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// ── Ajouter une taille ───────────────────────────────────────
function addSize() {
    const input     = document.getElementById('new-size-input');
    const container = document.getElementById('sizes-container');
    const size      = input.value.trim().toUpperCase();
    if (!size) return;

    const div = document.createElement('div');
    div.className = 'size-tag';
    div.innerHTML = `<span>${size}</span>
                     <input type="hidden" name="sizes[]" value="${size}">
                     <button type="button" onclick="this.parentElement.remove()">×</button>`;
    container.appendChild(div);
    input.value = '';
}

document.getElementById('new-size-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); addSize(); }
});

// ── Supprimer une image existante ───────────────────────────
async function deleteImage(imageId) {
    if (!confirm('Supprimer cette photo ?')) return;
    const res  = await fetch('<?= APP_URL ?>/admin/image/supprimer', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `image_id=${imageId}`
    });
    const data = await res.json();
    if (data.success) document.getElementById('img-' + imageId).remove();
}

// ── Définir image de couverture ──────────────────────────────
async function setCover(imageId, productId) {
    const res  = await fetch('<?= APP_URL ?>/admin/image/couverture', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `image_id=${imageId}&product_id=${productId}`
    });
    const data = await res.json();
    if (data.success) location.reload();
}
</script>
