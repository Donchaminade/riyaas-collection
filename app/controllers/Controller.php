<?php
/**
 * RIYAA'S COLLECTION — Controller de base
 * Tous les controllers héritent de cette classe.
 */

class Controller
{
    /**
     * Charge et affiche une vue avec des données.
     *
     * @param string $view   Chemin relatif depuis /app/views/ (sans .php)
     *                       Ex: 'home/index', 'catalog/show'
     * @param array  $data   Variables à injecter dans la vue
     * @param string $layout Layout à utiliser ('main' par défaut)
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        // Extraire les données pour les rendre disponibles dans la vue
        extract($data);

        // Capturer le contenu de la vue dans un buffer
        ob_start();
        $viewFile = VIEW_PATH . '/' . $view . '.php';
        if (!file_exists($viewFile)) {
            ob_end_clean();
            http_response_code(500);
            die("Vue introuvable : $view");
        }
        require $viewFile;
        $content = ob_get_clean();

        // Injecter dans le layout
        $layoutFile = VIEW_PATH . '/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Redirection simple
     */
    protected function redirect(string $path): void
    {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    /**
     * Retourner du JSON (pour les appels AJAX)
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Vérifier si l'utilisateur est connecté
     */
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Forcer la connexion (redirige si non connecté)
     */
    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/connexion');
        }
    }

    /**
     * Formater un prix en FCFA
     */
    protected function formatPrice(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' ' . CURRENCY;
    }
}
