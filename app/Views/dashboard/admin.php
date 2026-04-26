<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Admin Dashboard') ?> | MeralQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(180deg, #f5f7fb 0%, #e8eef9 100%);
            color: #122033;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-card,
        .feature-card {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(18, 32, 51, 0.08);
        }

        .hero-card {
            background: linear-gradient(135deg, #10213a 0%, #1f4f8b 100%);
            color: #f8fafc;
        }

        .pill {
            display: inline-block;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container py-2">
            <a class="navbar-brand fw-bold" href="<?= site_url('dashboard/admin') ?>">MeralQ Admin</a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-secondary"><?= esc($user['fullname']) ?></span>
                <a class="btn btn-outline-dark" href="<?= site_url('logout') ?>">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <section class="hero-card p-4 p-lg-5 mb-4">
            <span class="pill">Admin Dashboard</span>
            <h1 class="display-6 fw-bold mt-3">Manage users and monitor system activity.</h1>
            <p class="mb-0 text-white-50">This dashboard is reserved for admin accounts. It is prepared for user management, registered user visibility, and audit trail monitoring while keeping billing computation out of the admin flow.</p>
        </section>

        <section class="row g-4">
            <div class="col-md-4">
                <article class="card feature-card h-100 p-4">
                    <h2 class="h4">Account Management</h2>
                    <p class="text-secondary mb-0">Create, edit, and remove user accounts from a controlled admin workspace.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card feature-card h-100 p-4">
                    <h2 class="h4">Registered Users</h2>
                    <p class="text-secondary mb-0">View all registered accounts and separate admins from normal bill processors.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card feature-card h-100 p-4">
                    <h2 class="h4">Audit Trails</h2>
                    <p class="text-secondary mb-0">Track who performed actions in the system and when each activity occurred.</p>
                </article>
            </div>
        </section>
    </main>
</body>
</html>