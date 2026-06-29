<?php
$pageTitle = "Page introuvable — Riyaa's Collection";
$content = '
<div style="text-align:center; padding: 8rem 2rem;">
    <h1 style="font-family: Cormorant Garamond, serif; font-size: 6rem; opacity:.2;">404</h1>
    <h2 style="font-family: Cormorant Garamond, serif; font-size: 2rem; margin-bottom:1rem;">Page introuvable</h2>
    <p style="margin-bottom:2rem; color:#666;">Cette page n\'existe pas ou a été déplacée.</p>
    <a href="' . APP_URL . '/" style="text-decoration:underline;">Retour à l\'accueil</a>
</div>';
require VIEW_PATH . '/layouts/main.php';
