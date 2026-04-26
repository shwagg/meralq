<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'My Action Trail') ?> | MeralQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(255, 183, 3, 0.2), transparent 24%),
                linear-gradient(180deg, #f6f9fd 0%, #e8f0f8 100%);
            color: #152238;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .panel-card,
        .hero-card {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(21, 34, 56, 0.08);
        }

        .hero-card {
            background: linear-gradient(135deg, #10213a 0%, #1f4f8b 100%);
            color: #f8fafc;
        }

        .panel-card {
            background: rgba(255, 255, 255, 0.94);
        }

        .brand-mark {
            height: 48px;
            width: auto;
            display: block;
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

        .table thead th {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #637186;
            border-bottom-color: #dbe4f0;
        }

        .empty-state {
            padding: 2rem 1rem;
            text-align: center;
            color: #637186;
        }

        .page-meta {
            color: #637186;
            font-size: 0.92rem;
        }
    </style>
</head>
<body>
    <?php
        $logs = $pagination['items'] ?? [];
        $page = (int) ($pagination['page'] ?? 1);
        $pageCount = (int) ($pagination['pageCount'] ?? 1);
        $total = (int) ($pagination['total'] ?? 0);
        $baseUrl = site_url('dashboard/user/audit-trail');
    ?>

    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container py-2">
            <a class="navbar-brand" href="<?= site_url('dashboard/user') ?>">
                <img class="brand-mark" src="<?= base_url('assets/MeralKoo.svg') ?>" alt="MeralQ logo">
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-secondary"><?= esc($user['fullname']) ?></span>
                <a class="btn btn-outline-dark" href="<?= site_url('logout') ?>">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <section class="hero-card p-4 p-lg-5 mb-4">
            <span class="pill">My Action Trail</span>
            <h1 class="display-6 fw-bold mt-3">Review your own billing activity on a dedicated page.</h1>
            <p class="mb-0 text-white-50">Your dashboard now stays compact while this page holds the full action history with simple paging.</p>
        </section>

        <section class="card panel-card p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Audit Entries</h2>
                    <p class="page-meta mb-0">Page <?= esc((string) $page) ?> of <?= esc((string) $pageCount) ?>. <?= esc((string) $total) ?> total entries.</p>
                </div>
                <a class="btn btn-outline-dark" href="<?= site_url('dashboard/user') ?>">Back to Dashboard</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Logged At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs === []): ?>
                            <tr>
                                <td colspan="3" class="empty-state">No personal audit logs available.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $entry): ?>
                                <tr>
                                    <td><span class="badge text-bg-secondary"><?= esc($entry['action']) ?></span></td>
                                    <td><?= esc($entry['description']) ?></td>
                                    <td><?= esc($entry['createdAt']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pageCount > 1): ?>
                <nav class="mt-4" aria-label="User audit trail pages">
                    <ul class="pagination mb-0 justify-content-center flex-wrap gap-2">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill" href="<?= $page <= 1 ? '#' : esc($baseUrl . '?page=' . ($page - 1)) ?>">Previous</a>
                        </li>
                        <?php for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++): ?>
                            <li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>">
                                <a class="page-link rounded-pill" href="<?= esc($baseUrl . '?page=' . $pageNumber) ?>"><?= esc((string) $pageNumber) ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $pageCount ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill" href="<?= $page >= $pageCount ? '#' : esc($baseUrl . '?page=' . ($page + 1)) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>