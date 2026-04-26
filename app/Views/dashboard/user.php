<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'User Dashboard') ?> | MeralQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(255, 183, 3, 0.2), transparent 24%),
                linear-gradient(180deg, #f6f9fd 0%, #e8f0f8 100%);
            color: #152238;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-card,
        .panel-card,
        .metric-card,
        .table-card {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(21, 34, 56, 0.08);
        }

        .hero-card {
            background: linear-gradient(135deg, #10213a 0%, #1f4f8b 100%);
            color: #f8fafc;
        }

        .panel-card,
        .metric-card,
        .table-card {
            border-radius: 20px;
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

        .metric-value {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .rate-chip {
            border-radius: 999px;
            padding: 0.4rem 0.75rem;
            background: #edf3fb;
            color: #20436e;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .empty-state {
            padding: 2rem 1rem;
            text-align: center;
            color: #637186;
        }

        .history-meta,
        .audit-meta {
            font-size: 0.88rem;
            color: #637186;
        }

        .breakdown-list {
            margin: 0;
            padding-left: 1.1rem;
        }

        .table thead th {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #637186;
            border-bottom-color: #dbe4f0;
        }

        .table tbody td {
            vertical-align: top;
        }

        .form-hint {
            font-size: 0.85rem;
            color: #6d7a8d;
        }
    </style>
</head>
<body>
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
            <span class="pill">User Dashboard</span>
            <h1 class="display-6 fw-bold mt-3">Compute client bills and review only your own records.</h1>
            <p class="mb-4 text-white-50">Users can calculate electric bills using the required tiered rates, review personal billing history, and inspect their own action trails. Account management remains restricted to admins.</p>
            <div class="d-flex flex-wrap gap-2" id="rateChips"></div>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-md-4">
                <article class="card metric-card h-100 p-4">
                    <p class="text-secondary text-uppercase fw-semibold mb-2">Bills Computed</p>
                    <div class="metric-value" id="billsCount">0</div>
                    <p class="text-secondary mb-0">Bills created by your account only.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card metric-card h-100 p-4">
                    <p class="text-secondary text-uppercase fw-semibold mb-2">Clients Handled</p>
                    <div class="metric-value" id="clientsCount">0</div>
                    <p class="text-secondary mb-0">Clients created under your billing workspace.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card metric-card h-100 p-4">
                    <p class="text-secondary text-uppercase fw-semibold mb-2">Recent Actions</p>
                    <div class="metric-value" id="auditCount">0</div>
                    <p class="text-secondary mb-0">Your latest audit log entries only.</p>
                </article>
            </div>
        </section>

        <section class="row g-4 align-items-start">
            <div class="col-xl-4">
                <article class="card panel-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="section-title mb-1">Bill Computation</h2>
                            <p class="text-secondary mb-0">Enter client details and consumption to generate a bill.</p>
                        </div>
                        <span class="badge text-bg-dark">Tiered Rates</span>
                    </div>

                    <div id="formAlert" class="alert d-none" role="alert"></div>

                    <form id="billForm" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="client_name">Client Name</label>
                            <input class="form-control" id="client_name" name="client_name" type="text" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="client_address">Client Address</label>
                            <textarea class="form-control" id="client_address" name="client_address" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="kw_consumption">Consumption (KW)</label>
                            <input class="form-control" id="kw_consumption" name="kw_consumption" type="number" min="0.01" step="0.01" required>
                            <div class="form-hint mt-1">Rates: first 200 KW at PHP 10, next 300 KW at PHP 13, remaining KW at PHP 15.</div>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-dark btn-lg" type="submit">Compute Electric Bill</button>
                        </div>
                    </form>
                </article>
            </div>

            <div class="col-xl-8">
                <div class="row g-4">
                    <div class="col-12">
                        <article class="card table-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h2 class="section-title mb-1">Billing History</h2>
                                    <p class="text-secondary mb-0">Only bills computed by your account appear here.</p>
                                </div>
                                <button class="btn btn-outline-dark" id="refreshDataButton" type="button">Refresh</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Client</th>
                                            <th>Consumption</th>
                                            <th>Total</th>
                                            <th>Breakdown</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody id="billingHistoryBody"></tbody>
                                </table>
                            </div>
                        </article>
                    </div>

                    <div class="col-12">
                        <article class="card table-card p-4">
                            <div class="mb-3">
                                <h2 class="section-title mb-1">My Action Trail</h2>
                                <p class="text-secondary mb-0">Recent activity generated under your own user account.</p>
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
                                    <tbody id="auditLogsBody"></tbody>
                                </table>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        const initialDashboard = <?= json_encode($dashboard ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

        $(function () {
            let dashboard = initialDashboard;

            renderDashboard(dashboard);

            $('#billForm').on('submit', function (event) {
                event.preventDefault();
                submitBill();
            });

            $('#refreshDataButton').on('click', fetchDashboardData);

            function submitBill() {
                hideFormAlert();

                $.ajax({
                    url: `<?= site_url('dashboard/user/bills') ?>`,
                    method: 'POST',
                    data: $('#billForm').serialize(),
                }).done(function (response) {
                    dashboard = response.data || {};
                    renderDashboard(dashboard);
                    $('#billForm')[0].reset();
                    showFormAlert('success', response.message || 'Electric bill computed successfully.');
                }).fail(function (xhr) {
                    const response = xhr.responseJSON || {};
                    const errors = response.errors || {};
                    const errorText = Object.values(errors).join(' ');

                    showFormAlert('danger', errorText || response.message || 'Unable to compute the bill.');
                });
            }

            function fetchDashboardData() {
                $.getJSON(`<?= site_url('dashboard/user/data') ?>`, function (response) {
                    dashboard = response.data || {};
                    renderDashboard(dashboard);
                });
            }

            function renderDashboard(data) {
                const metrics = data.metrics || {};

                $('#billsCount').text(metrics.billsCount || 0);
                $('#clientsCount').text(metrics.clientsCount || 0);
                $('#auditCount').text(metrics.auditCount || 0);

                renderRates(data.rates || []);
                renderBillingHistory(data.billingHistory || []);
                renderAuditLogs(data.auditLogs || []);
            }

            function renderRates(rates) {
                const container = $('#rateChips');

                if (!rates.length) {
                    container.empty();
                    return;
                }

                container.html(rates.map(function (rate) {
                    return `<span class="rate-chip">${escapeHtml(rate.label)}: PHP ${formatCurrency(rate.rate)}</span>`;
                }).join(''));
            }

            function renderBillingHistory(records) {
                const tbody = $('#billingHistoryBody');

                if (!records.length) {
                    tbody.html('<tr><td colspan="5" class="empty-state">No billing history found for your account.</td></tr>');
                    return;
                }

                tbody.html(records.map(function (record) {
                    const breakdown = (record.breakdown || []).map(function (row) {
                        const rangeTo = Number(row.range_to) === 0 ? 'above' : row.range_to;
                        return `<li>${row.range_from} - ${rangeTo} KW at PHP ${formatCurrency(row.rate_per_kw)} x ${formatCurrency(row.consumption_in_range)} = PHP ${formatCurrency(row.subtotal)}</li>`;
                    }).join('');

                    return `
                        <tr>
                            <td>
                                <div class="fw-semibold">${escapeHtml(record.client_name || 'Unknown Client')}</div>
                                <div class="history-meta">${escapeHtml(record.client_address || '')}</div>
                            </td>
                            <td>${formatCurrency(record.kw_consumption)} KW</td>
                            <td class="fw-semibold">PHP ${formatCurrency(record.total_amount)}</td>
                            <td><ul class="breakdown-list">${breakdown}</ul></td>
                            <td class="history-meta">${escapeHtml(record.createdAt || '')}</td>
                        </tr>
                    `;
                }).join(''));
            }

            function renderAuditLogs(records) {
                const tbody = $('#auditLogsBody');

                if (!records.length) {
                    tbody.html('<tr><td colspan="3" class="empty-state">No personal audit logs available.</td></tr>');
                    return;
                }

                tbody.html(records.map(function (record) {
                    return `
                        <tr>
                            <td><span class="badge text-bg-secondary">${escapeHtml(record.action || '')}</span></td>
                            <td>${escapeHtml(record.description || '')}</td>
                            <td class="audit-meta">${escapeHtml(record.createdAt || '')}</td>
                        </tr>
                    `;
                }).join(''));
            }

            function showFormAlert(type, message) {
                $('#formAlert')
                    .removeClass('d-none alert-success alert-danger')
                    .addClass(`alert-${type}`)
                    .text(message);
            }

            function hideFormAlert() {
                $('#formAlert')
                    .addClass('d-none')
                    .removeClass('alert-success alert-danger')
                    .text('');
            }

            function formatCurrency(value) {
                return Number(value || 0).toFixed(2);
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }
        });
    </script>
</body>
</html>