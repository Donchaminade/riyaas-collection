<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? "Admin — Riyaa's Collection") ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --ivory: #F8F5F0; --obsidian: #1A1714; --gold: #B8975A;
            --blush: #EDE7DF; --mist: #6B6460; --white: #fff;
            --font-display: 'Cormorant Garamond', serif;
            --font-body: 'Jost', sans-serif;
        }
        body { font-family: var(--font-body); background: #F0EDE8; color: var(--obsidian); font-size: 14px; }

        /* Sidebar */
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 240px; background: var(--obsidian); color: var(--ivory);
            padding: 2rem 0; position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto;
        }
        .sidebar-logo { padding: 0 1.5rem 2rem; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 1.5rem; }
        .sidebar-logo span:first-child { font-family: var(--font-display); font-size: 1.3rem; color: var(--ivory); display: block; }
        .sidebar-logo span:last-child { font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--gold); }
        .sidebar nav a {
            display: flex; align-items: center; gap: 0.7rem;
            padding: 0.75rem 1.5rem; color: #9B9490; font-size: 0.78rem;
            letter-spacing: 0.08em; text-decoration: none; text-transform: uppercase;
            transition: all 0.2s;
        }
        .sidebar nav a:hover, .sidebar nav a.active { color: var(--ivory); background: rgba(255,255,255,0.05); border-left: 2px solid var(--gold); }
        .sidebar-footer { padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.08); margin-top: auto; position: absolute; bottom: 0; left: 0; right: 0; }
        .sidebar-footer a { color: #9B9490; font-size: 0.72rem; text-decoration: none; letter-spacing: 0.1em; text-transform: uppercase; }

        /* Main */
        .admin-main { margin-left: 240px; flex: 1; padding: 2rem; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .admin-header h1 { font-family: var(--font-display); font-size: 1.8rem; font-weight: 400; }

        /* Cards stats */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: var(--white); padding: 1.5rem; border-radius: 2px; }
        .stat-card .label { font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--mist); margin-bottom: 0.5rem; }
        .stat-card .value { font-family: var(--font-display); font-size: 2rem; font-weight: 400; color: var(--obsidian); }

        /* Table */
        .admin-table-wrap { background: var(--white); border-radius: 2px; overflow: hidden; }
        .admin-table-head { display: flex; justify-content: space-between; align-items: center; padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--blush); }
        .admin-table-head h2 { font-family: var(--font-display); font-size: 1.2rem; font-weight: 400; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--mist); padding: 0.9rem 1.5rem; border-bottom: 1px solid var(--blush); font-weight: 400; }
        td { padding: 1rem 1.5rem; border-bottom: 1px solid var(--blush); font-size: 0.85rem; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--ivory); }
        .product-thumb { width: 48px; height: 60px; object-fit: cover; background: var(--blush); display: block; }
        .product-thumb-placeholder { width: 48px; height: 60px; background: var(--blush); }
        .badge { display: inline-block; font-size: 0.65rem; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.25rem 0.6rem; border-radius: 2px; }
        .badge-active { background: #e8f5ee; color: #2E7D52; }
        .badge-order { background: #fef3e2; color: #B8600A; }

        /* Boutons */
        .btn { display: inline-block; font-family: var(--font-body); font-size: 0.7rem; letter-spacing: 0.12em; text-transform: uppercase; padding: 0.6rem 1.2rem; border: 1px solid transparent; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .btn-primary { background: var(--obsidian); color: var(--ivory); border-color: var(--obsidian); }
        .btn-primary:hover { background: var(--gold); border-color: var(--gold); }
        .btn-gold { background: var(--gold); color: var(--white); border-color: var(--gold); }
        .btn-ghost { background: transparent; color: var(--mist); border-color: var(--blush); }
        .btn-ghost:hover { border-color: var(--obsidian); color: var(--obsidian); }
        .btn-danger { background: transparent; color: #C0392B; border-color: #C0392B; }
        .btn-danger:hover { background: #C0392B; color: var(--white); }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.65rem; }

        /* Formulaire */
        .form-card { background: var(--white); border-radius: 2px; padding: 2rem; max-width: 800px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; font-size: 0.68rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--mist); margin-bottom: 0.5rem; }
        input[type=text], input[type=number], select, textarea {
            width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--blush);
            font-family: var(--font-body); font-size: 0.88rem; color: var(--obsidian);
            outline: none; background: var(--ivory); transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--gold); }
        textarea { resize: vertical; min-height: 100px; }

        /* Upload zone */
        .upload-zone {
            border: 2px dashed var(--blush); padding: 2rem; text-align: center;
            cursor: pointer; transition: border-color 0.2s; background: var(--ivory);
        }
        .upload-zone:hover { border-color: var(--gold); }
        .upload-zone input { display: none; }
        .upload-zone p { font-size: 0.8rem; color: var(--mist); margin-top: 0.5rem; }

        /* Preview images */
        .images-preview { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; }
        .image-preview-item { position: relative; width: 100px; }
        .image-preview-item img { width: 100px; height: 130px; object-fit: cover; display: block; }
        .image-preview-item .remove-img { position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; background: #C0392B; color: white; border: none; border-radius: 50%; cursor: pointer; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; }

        /* Tailles */
        .sizes-input { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .size-tag { display: flex; align-items: center; gap: 0.3rem; background: var(--blush); padding: 0.3rem 0.7rem; font-size: 0.78rem; }
        .size-tag button { background: none; border: none; cursor: pointer; color: var(--mist); font-size: 0.9rem; line-height: 1; }
        .add-size { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
        .add-size input { width: 80px; padding: 0.4rem 0.6rem; }

        /* Login */
        .login-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--obsidian); }
        .login-card { background: var(--ivory); padding: 3rem; width: 360px; text-align: center; }
        .login-card h1 { font-family: var(--font-display); font-size: 1.8rem; font-weight: 300; margin-bottom: 0.5rem; }
        .login-card p { color: var(--mist); font-size: 0.8rem; margin-bottom: 2rem; }
        .error-msg { background: #fdf0ef; color: #C0392B; padding: 0.75rem; font-size: 0.82rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
<?= $content ?>
</body>
</html>
