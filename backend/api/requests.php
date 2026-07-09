<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/security.php';

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {

    // ══════════════════════ PUBLIC ══════════════════════

    // POST /api/requests.php?action=create — Demande sur mesure
    // FormData : name, phone, description, budget? (nombre), image? (fichier JPG/PNG/WebP, 5 Mo max)
    case 'create': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $name        = sanitize($_POST['name'] ?? '');
        $phone       = sanitize($_POST['phone'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $budgetRaw   = trim($_POST['budget'] ?? '');

        if (!$name || !$phone || !$description) {
            respondError('Nom, téléphone et description sont obligatoires', 400);
        }
        if (mb_strlen($name) > 200)  respondError('Nom trop long (200 caractères max)', 400);
        if (mb_strlen($phone) > 20)  respondError('Numéro de téléphone invalide', 400);
        if (mb_strlen($description) > 5000) {
            respondError('Description trop longue (5000 caractères max)', 400);
        }

        $budget = null;
        if ($budgetRaw !== '') {
            if (!is_numeric($budgetRaw) || (float)$budgetRaw < 0) {
                respondError('Budget invalide', 400);
            }
            $budget = (float)$budgetRaw;
        }

        // Upload facultatif de la photo de référence
        $imagePath = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagePath = handleRequestImageUpload($_FILES['image']);
        }

        $requestNumber = 'DM-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $db->prepare("
            INSERT INTO custom_requests (request_number, customer_name, customer_phone, description, budget, image_path)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([$requestNumber, $name, $phone, $description, $budget, $imagePath]);

        respond([
            'success'        => true,
            'request_number' => $requestNumber,
        ], 201);
        break;
    }

    // ══════════════════════ ADMIN ══════════════════════

    // GET /api/requests.php?action=list — toutes les demandes (token admin requis)
    case 'list': {
        if ($method !== 'GET') respondError('Méthode non autorisée', 405);
        requireAdmin();

        $requests = $db->query("SELECT * FROM custom_requests ORDER BY created_at DESC")->fetchAll();
        foreach ($requests as &$r) {
            $r['image_url'] = requestImageUrl($r['image_path']);
        }

        respond(['success' => true, 'data' => $requests]);
        break;
    }

    // POST /api/requests.php?action=update-status — Body : { "id": 1, "status": "quoted", "admin_notes"?: "…" }
    case 'update-status': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);
        requireAdmin();

        $body   = getBody();
        $id     = (int)($body['id'] ?? 0);
        $status = $body['status'] ?? '';

        $allowed = ['new', 'in_review', 'quoted', 'accepted', 'rejected', 'completed'];
        if (!$id || !in_array($status, $allowed, true)) {
            respondError('Paramètres invalides', 400);
        }

        if (array_key_exists('admin_notes', $body)) {
            $notes = sanitize((string)$body['admin_notes']);
            $db->prepare("UPDATE custom_requests SET status = ?, admin_notes = ? WHERE id = ?")
               ->execute([$status, $notes !== '' ? $notes : null, $id]);
        } else {
            $db->prepare("UPDATE custom_requests SET status = ? WHERE id = ?")
               ->execute([$status, $id]);
        }

        respond(['success' => true]);
        break;
    }

    default:
        respondError('Action invalide', 400);
}

// ══════════════════════ HELPERS ══════════════════════

function requestUploadDir(): string {
    $dir = __DIR__ . '/../uploads/requests/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    // .htaccess anti-exécution PHP, identique à uploads/products (créé si absent)
    $htaccess = $dir . '.htaccess';
    if (!file_exists($htaccess)) {
        copy(__DIR__ . '/../uploads/products/.htaccess', $htaccess);
    }
    return $dir;
}

// Validation du fichier (taille, type MIME réel via finfo, extension) puis
// déplacement dans uploads/requests — même logique que handleImageUpload (admin.php)
function handleRequestImageUpload(array $file): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        respondError("Erreur lors de l'envoi de l'image, veuillez réessayer", 400);
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        respondError('Image trop lourde (5 Mo maximum)', 400);
    }

    $finfo        = new finfo(FILEINFO_MIME_TYPE);
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($finfo->file($file['tmp_name']), $allowedTypes, true)) {
        respondError('Format d\'image non accepté (JPG, PNG ou WebP uniquement)', 400);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        respondError('Extension d\'image non acceptée (JPG, PNG ou WebP uniquement)', 400);
    }

    $filename = 'request_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], requestUploadDir() . $filename)) {
        respondError('Impossible d\'enregistrer l\'image, veuillez réessayer', 500);
    }
    return $filename;
}

// URL publique d'une image de demande (même logique que productImageUrl,
// mais pointée sur uploads/requests)
function requestImageUrl(?string $filename): ?string {
    if (!$filename) return null;

    $base = ASSETS_BASE_URL;
    if ($base !== '') {
        // ASSETS_BASE_URL pointe sur .../uploads/products → on bascule sur /requests
        $base = preg_replace('#/products/?$#', '/requests', rtrim($base, '/'));
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $root   = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $root   = ($root === '/' || $root === '\\') ? '' : $root;
        $base   = "$scheme://$host$root/uploads/requests";
    }

    return rtrim($base, '/') . '/' . ltrim($filename, '/');
}
