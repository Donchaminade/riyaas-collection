<?php /* app/views/admin/login.php */ ?>

<div class="login-wrap">
    <div class="login-card">
        <h1>Riyaa's</h1>
        <p>Espace administration</p>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/admin/login">
            <div class="form-group" style="text-align:left; margin-bottom:1.5rem;">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" autofocus required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:0.9rem;">
                Accéder à l'admin
            </button>
        </form>

        <div style="margin-top:1.5rem;">
            <a href="<?= APP_URL ?>/" style="font-size:0.75rem; color:#9B9490; text-decoration:none; letter-spacing:0.1em;">
                ← Retour au site
            </a>
        </div>
    </div>
</div>
