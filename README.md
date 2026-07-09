# Riyaa's Collection

Boutique en ligne de vêtements en soie confectionnés à la commande (Lomé, Togo).

## Architecture

```
riyaas-collection/
├── backend/     ← API PHP pur (à déployer sur HOSTINGER)
│   ├── api/           auth.php · products.php · cart.php · orders.php · requests.php · admin.php
│   ├── config/        config.php (+ config.local.php non versionné) · database.php
│   ├── middleware/    security.php (CORS, helpers, auth token)
│   ├── lib/           cart.php (validation prix serveur)
│   ├── migrations/    scripts SQL incrémentaux pour bases existantes
│   ├── uploads/       products/ (images produits) · requests/ (photos des demandes sur mesure)
│   └── db.sql         schéma MySQL complet
│
├── frontend/    ← Site statique HTML/CSS/JS pur (à déployer sur VERCEL)
│   ├── index.html · catalogue.html · produit.html · panier.html
│   ├── commander.html · confirmation.html · 404.html
│   ├── a-propos.html · contact.html · demande.html (demande sur mesure)
│   ├── admin/         login.html · index.html · produit-form.html · commandes.html · demandes.html
│   └── assets/        css/ · js/ (config.js, api.js, cart.js, layout.js) · images/
│
└── app/, config/, public/, vendor/   ← ANCIEN monolithe PHP (obsolète, conservé pour référence)
```

## Développement local (XAMPP)

1. Démarrer Apache + MySQL dans XAMPP
2. Importer `backend/db.sql` via phpMyAdmin (crée la base `riyaas_collection`)
3. Boutique : `http://localhost/riyaas-collection/frontend/index.html`
4. Admin : le dossier `admin/` utilise des chemins absolus (`/admin/...`, `/assets/...`) pour
   fonctionner correctement une fois déployé à la racine du domaine sur Vercel — il ne
   s'affiche donc pas correctement sous le sous-chemin XAMPP (`/riyaas-collection/frontend/`).
   Pour tester l'admin en local dans les mêmes conditions qu'en production, lancer un serveur
   PHP à la racine du dossier `frontend/` :

   ```
   php -S localhost:8099 -t frontend
   ```

   puis ouvrir `http://localhost:8099/admin/login` (mot de passe dev : `riyaas2024`).
   Les pages boutique (chemins relatifs) restent utilisables via l'URL XAMPP classique.

L'URL de l'API en local est détectée automatiquement (`frontend/assets/js/config.js`) — elle
fonctionne dans les deux cas de figure ci-dessus (XAMPP ou serveur PHP autonome).

## Déploiement

### 1. Backend → Hostinger

1. **Base de données** : créer une base MySQL dans hPanel, importer `backend/db.sql` via phpMyAdmin.
2. **Fichiers** : uploader le contenu du dossier `backend/` sur le sous-domaine `riyaas.grosbit.com` (les endpoints seront alors du type `https://riyaas.grosbit.com/api/products.php`).
3. **Configuration** : créer `config/config.local.php` sur le serveur (jamais dans git) :

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'uXXXXXX_riyaas');
define('DB_USER', 'uXXXXXX_user');
define('DB_PASS', 'mot-de-passe-mysql');
define('ADMIN_PASSWORD', 'un-mot-de-passe-fort');
define('AUTH_SECRET', 'une-longue-chaine-aleatoire-64-caracteres');
define('ASSETS_BASE_URL', 'https://riyaas.grosbit.com/uploads/products');
define('ALLOWED_ORIGINS', ['https://riyaas-collection.vercel.app', 'https://riyaascollection.vercel.app']);
```

4. Vérifier que les dossiers `uploads/products/` et `uploads/requests/` sont accessibles en écriture (chmod 755).
5. Pour une base existante, appliquer les scripts de `backend/migrations/` (ex. `2026-07-add-custom-requests.sql`).

### 2. Frontend → Vercel

1. L'URL de production `API_BASE` est déjà configurée dans `frontend/assets/js/config.js` : `https://riyaas.grosbit.com/api`.
2. Sur Vercel : **New Project** → importer le repo → **Root Directory : `frontend`** → Deploy (aucun build, site statique).
3. Ajouter le domaine Vercel final dans `ALLOWED_ORIGINS` du `config.local.php` sur Hostinger.

## Endpoints API

### Public
| Méthode | Endpoint | Description |
|---|---|---|
| GET | `/api/products.php?action=list[&categorie=slug]` | Liste des produits |
| GET | `/api/products.php?action=show&slug=...` | Détail produit (images + tailles) |
| GET | `/api/products.php?action=categories` | Catégories |
| GET | `/api/products.php?action=featured` | Pièces phares (max 6) |
| POST | `/api/cart.php?action=add` | Prix serveur d'un article |
| POST | `/api/cart.php?action=validate` | Revalidation du panier complet |
| POST | `/api/orders.php` | Création de commande (prix recalculés serveur) |
| POST | `/api/requests.php?action=create` | Demande sur mesure (FormData : name, phone, description, budget?, image?) |
| POST | `/api/auth.php?action=admin-login` | Connexion admin → token |

### Admin (header `Authorization: Bearer {token}`)
| Méthode | Endpoint | Description |
|---|---|---|
| GET | `/api/admin.php?action=stats` | Statistiques dashboard |
| GET | `/api/admin.php?action=products` | Tous les produits (actifs + inactifs) |
| GET | `/api/admin.php?action=product&id=...` | Détail produit admin |
| POST | `/api/admin.php?action=create-product` | Créer (FormData, `images[]`) |
| POST | `/api/admin.php?action=update-product` | Modifier |
| POST | `/api/admin.php?action=delete-product` | Supprimer définitivement (produit + images + variantes) |
| POST | `/api/admin.php?action=toggle-product` | Activer / désactiver la visibilité boutique |
| POST | `/api/admin.php?action=delete-image` | Supprimer une image |
| POST | `/api/admin.php?action=set-cover` | Définir l'image de couverture |
| GET | `/api/admin.php?action=orders` | Commandes + articles |
| POST | `/api/admin.php?action=update-order-status` | Changer le statut d'une commande |
| GET | `/api/requests.php?action=list` | Demandes sur mesure (avec URL image) |
| POST | `/api/requests.php?action=update-status` | Statut + notes d'une demande (`{id, status, admin_notes?}`) |

## Flux de commande

1. La cliente ajoute des articles au panier (localStorage, prix fournis par le serveur)
2. Au checkout, le panier est **revalidé côté serveur** (`cart.php?action=validate`)
3. La commande est enregistrée en base (`orders.php`, prix recalculés serveur)
4. Un récapitulatif **WhatsApp** s'ouvre pour la validation et l'envoi de l'acompte **TMoney 50%**
5. Le solde est payé à la livraison (sous 5 jours)

## Sécurité

- Prix toujours recalculés côté serveur (le client n'envoie que `product_id`/`variant_id`/`quantity`)
- Auth admin : token HMAC-SHA256 stateless (8 h), compatible cross-origin Vercel → Hostinger
- Upload d'images : type MIME vérifié via `finfo`, exécution PHP bloquée dans `uploads/`
- Secrets (DB, mot de passe admin, secret HMAC) dans `config.local.php` non versionné
