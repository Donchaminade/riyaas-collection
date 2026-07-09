/**
 * RIYAA'S COLLECTION — Back-office : auth et client API admin
 * Le token HMAC est stocké en localStorage et envoyé en Authorization: Bearer.
 */
const TOKEN_KEY = 'riyaas_admin_token';

const AdminAuth = {
    getToken: ()      => localStorage.getItem(TOKEN_KEY),
    setToken: (token) => localStorage.setItem(TOKEN_KEY, token),
    clear:    ()      => localStorage.removeItem(TOKEN_KEY),

    async login(password) {
        const res  = await fetch(`${CONFIG.API_BASE}/auth.php?action=admin-login`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ password }),
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.error || 'Connexion refusée');
        AdminAuth.setToken(json.token);
        return json;
    },

    async verify() {
        const token = AdminAuth.getToken();
        if (!token) return false;
        try {
            const res = await fetch(`${CONFIG.API_BASE}/auth.php?action=verify`, {
                method:  'POST',
                headers: { 'Authorization': `Bearer ${token}` },
            });
            return res.ok;
        } catch {
            return false;
        }
    },

    logout() {
        AdminAuth.clear();
        location.href = 'login.html';
    },

    // À appeler en haut de chaque page admin protégée
    async requireAuth() {
        if (!(await AdminAuth.verify())) {
            AdminAuth.clear();
            location.href = 'login.html';
        }
    },
};

// ── Appels API admin (token automatique) ─────────────────────
// endpoint : fichier API cible (admin.php par défaut, ex: requests.php)
async function adminApi(action, { method = 'GET', body = null, formData = null, endpoint = 'admin.php' } = {}) {
    const headers = { 'Authorization': `Bearer ${AdminAuth.getToken()}` };
    const options = { method, headers };

    if (formData) {
        options.method = 'POST';
        options.body   = formData;
    } else if (body) {
        options.method          = 'POST';
        headers['Content-Type'] = 'application/json';
        options.body            = JSON.stringify(body);
    }

    const res  = await fetch(`${CONFIG.API_BASE}/${endpoint}?action=${action}`, options);
    const json = await res.json();

    if (res.status === 401) {
        AdminAuth.clear();
        location.href = 'login.html';
        throw new Error('Session expirée');
    }
    if (!res.ok || json.success === false) {
        throw new Error(json.error || `Erreur API (${res.status})`);
    }
    return json;
}

const AdminAPI = {
    stats:             ()           => adminApi('stats'),
    dashboardStats:    ()           => adminApi('dashboard-stats'),
    products:          ()           => adminApi('products'),
    product:           (id)         => adminApi(`product&id=${id}`),
    createProduct:     (formData)   => adminApi('create-product', { formData }),
    updateProduct:     (formData)   => adminApi('update-product', { formData }),
    deleteProduct:     (id)         => adminApi('delete-product', { body: { id } }),
    toggleProduct:     (id, isActive) => adminApi('toggle-product', { body: { id, is_active: isActive ? 1 : 0 } }),
    deleteImage:       (imageId)    => adminApi('delete-image', { body: { image_id: imageId } }),
    setCover:          (imageId, productId) => adminApi('set-cover', { body: { image_id: imageId, product_id: productId } }),
    setImageColor:     (imageId, color) => adminApi('set-image-color', { body: { image_id: imageId, color } }),
    orders:            ()           => adminApi('orders'),
    updateOrderStatus: (orderId, status) => adminApi('update-order-status', { body: { order_id: orderId, status } }),
    updatePaymentStatus: (orderId, status) => adminApi('update-payment-status', { body: { order_id: orderId, status } }),
    deleteOrder:       (id)         => adminApi('delete-order', { body: { id } }),

    // Demandes sur mesure (endpoint requests.php)
    requests:            ()                  => adminApi('list', { endpoint: 'requests.php' }),
    updateRequestStatus: (id, status, adminNotes) => adminApi('update-status', {
        endpoint: 'requests.php',
        body: adminNotes === undefined ? { id, status } : { id, status, admin_notes: adminNotes },
    }),
};

// ── Libellés statuts commandes ───────────────────────────────
const ORDER_STATUSES = {
    pending:       { label: 'En attente',       color: '#B8975A' },
    deposit_paid:  { label: 'Acompte payé',     color: '#2E86C1' },
    in_production: { label: 'En confection',    color: '#8E44AD' },
    ready:         { label: 'Prête',            color: '#16A085' },
    delivered:     { label: 'Livrée',           color: '#27AE60' },
    completed:     { label: 'Terminée',         color: '#2E7D52' },
    cancelled:     { label: 'Annulée',          color: '#C0392B' },
};

// ── Libellés statuts de paiement ─────────────────────────────
const PAYMENT_STATUSES = {
    awaiting_deposit: { label: 'Acompte attendu',      color: '#B8975A' },
    deposit_paid:     { label: 'Acompte reçu',         color: '#2E86C1' },
    balance_paid:     { label: 'Solde reçu',           color: '#16A085' },
    fully_paid:       { label: 'Payée intégralement',  color: '#2E7D52' },
    refunded:         { label: 'Remboursée',           color: '#C0392B' },
};

// ── Libellés statuts demandes sur mesure ─────────────────────
const REQUEST_STATUSES = {
    new:       { label: 'Nouvelle',     color: '#B8975A' },
    in_review: { label: 'En étude',     color: '#2E86C1' },
    quoted:    { label: 'Devis envoyé', color: '#8E44AD' },
    accepted:  { label: 'Acceptée',     color: '#27AE60' },
    rejected:  { label: 'Refusée',      color: '#C0392B' },
    completed: { label: 'Terminée',     color: '#2E7D52' },
};
