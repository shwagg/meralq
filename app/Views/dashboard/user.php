<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'User Dashboard') ?> | MeralQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: radial-gradient(circle at top left, #fff2cc 0%, #f7f9fc 45%, #edf3fb 100%);
            color: #152238;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-card,
        .feature-card {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(21, 34, 56, 0.08);
        }

        .hero-card {
            background: linear-gradient(135deg, #ffb703 0%, #fb8500 100%);
            color: #10213a;
        }

        .metric-card {
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.82);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container py-2">
            <a class="navbar-brand fw-bold" href="<?= site_url('dashboard/user') ?>">MeralQ User</a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-secondary"><?= esc($user['fullname']) ?></span>
                <a class="btn btn-outline-dark" href="<?= site_url('logout') ?>">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <section class="hero-card p-4 p-lg-5 mb-4">
            <p class="text-uppercase fw-bold mb-2">Normal User Dashboard</p>
            <h1 class="display-6 fw-bold">Compute bills and review personal activity.</h1>
            <p class="mb-0">This dashboard is reserved for normal users. It is prepared for electric bill computation, personal billing history, and the user's own audit trail.</p>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-md-4">
                <article class="card metric-card h-100 p-4">
                    <h2 class="h4">Bill Computation</h2>
                    <p class="text-secondary mb-0">Tiered-rate billing tools will live here for computing client electric charges.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card metric-card h-100 p-4">
                    <h2 class="h4">Billing History</h2>
                    <p class="text-secondary mb-0">Normal users will be able to review the bills they generated.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card metric-card h-100 p-4">
                    <h2 class="h4">Action Trails</h2>
                    <p class="text-secondary mb-0">Personal activity records can be surfaced here without exposing other users' data.</p>
                </article>
            </div>
        </section>
    </main>
</body>
</html>