<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Admin Dashboard') ?> | MeralQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(255, 183, 3, 0.22), transparent 24%),
                linear-gradient(180deg, #f4f7fb 0%, #e6edf7 100%);
            color: #122033;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-card,
        .panel-card,
        .metric-card {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(18, 32, 51, 0.08);
        }

        .hero-card {
            background: linear-gradient(135deg, #10213a 0%, #1f4f8b 100%);
            color: #f8fafc;
        }

        .panel-card,
        .metric-card {
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

        .table thead th {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #637186;
            border-bottom-color: #dbe4f0;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .badge-role {
            min-width: 72px;
        }

        .empty-state {
            padding: 2rem 1rem;
            text-align: center;
            color: #637186;
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
            <a class="navbar-brand" href="<?= site_url('dashboard/admin') ?>">
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
            <span class="pill">Admin Dashboard</span>
            <h1 class="display-6 fw-bold mt-3">Manage accounts and monitor platform activity.</h1>
            <p class="mb-0 text-white-50">Admins can create, review, update, and delete user accounts, inspect all registered users, and review audit activity. Bill computation stays outside this workspace.</p>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-md-4">
                <article class="card metric-card h-100 p-4">
                    <p class="text-secondary text-uppercase fw-semibold mb-2">Registered Users</p>
                    <div class="metric-value" id="usersCount"><?= count($users ?? []) ?></div>
                    <p class="text-secondary mb-0">All admin and normal user accounts currently registered in the system.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card metric-card h-100 p-4">
                    <p class="text-secondary text-uppercase fw-semibold mb-2">Admin Accounts</p>
                    <div class="metric-value" id="adminsCount"><?= count(array_filter($users ?? [], static fn(array $account): bool => $account['role'] === 'admin')) ?></div>
                    <p class="text-secondary mb-0">Users with full account management and audit trail visibility.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card metric-card h-100 p-4">
                    <p class="text-secondary text-uppercase fw-semibold mb-2">Audit Entries</p>
                    <div class="metric-value" id="auditCount"><?= count($auditLogs ?? []) ?></div>
                    <p class="text-secondary mb-0">Recent activity logs showing who did what and when it happened.</p>
                </article>
            </div>
        </section>

        <section class="row g-4 align-items-start">
            <div class="col-xl-4">
                <article class="card panel-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="section-title mb-1">User Account Form</h2>
                            <p class="text-secondary mb-0">Create a new account or edit an existing one.</p>
                        </div>
                        <span class="badge text-bg-dark" id="formModeBadge">Create</span>
                    </div>

                    <div id="formAlert" class="alert d-none" role="alert"></div>

                    <form id="userForm" novalidate>
                        <input type="hidden" id="userId" name="userId">

                        <div class="mb-3">
                            <label class="form-label" for="fullname">Full Name</label>
                            <input class="form-control" id="fullname" name="fullname" type="text" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input class="form-control" id="username" name="username" type="text" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" id="email" name="email" type="email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" id="password" name="password" type="password">
                            <div class="form-hint mt-1">Required for new users. Leave blank when editing to keep the current password.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="role">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1" type="submit" id="saveButton">Save User</button>
                            <button class="btn btn-outline-secondary" type="button" id="resetButton">Reset</button>
                        </div>
                    </form>
                </article>
            </div>

            <div class="col-xl-8">
                <article class="card panel-card p-4 mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h2 class="section-title mb-1">Registered Users</h2>
                            <p class="text-secondary mb-0">Admins can manage accounts here. Billing actions are intentionally excluded.</p>
                        </div>
                        <button class="btn btn-outline-dark" type="button" id="refreshUsersButton">Refresh</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody"></tbody>
                        </table>
                    </div>
                </article>

                <article class="card panel-card p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h2 class="section-title mb-1">Latest Audit Trail</h2>
                            <p class="text-secondary mb-0">Showing the three most recent actions. Open the full audit trail for the complete paged history.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-dark" type="button" id="refreshLogsButton">Refresh</button>
                            <a class="btn btn-dark" href="<?= site_url('dashboard/admin/audit-trail') ?>">View Full Trail</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>When</th>
                                </tr>
                            </thead>
                            <tbody id="auditTableBody"></tbody>
                        </table>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <script>
        const initialUsers = <?= json_encode($users ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const initialAuditLogs = <?= json_encode($auditLogs ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const currentUserId = <?= (int) ($user['id'] ?? 0) ?>;

        $(function () {
            let users = initialUsers;
            let auditLogs = initialAuditLogs;

            renderUsers(users);
            renderAuditLogs(auditLogs);
            updateMetrics(users, auditLogs);

            $('#userForm').on('submit', function (event) {
                event.preventDefault();
                submitUserForm();
            });

            $('#resetButton').on('click', resetForm);
            $('#refreshUsersButton').on('click', fetchUsers);
            $('#refreshLogsButton').on('click', fetchAuditLogs);

            $('#usersTableBody').on('click', '[data-action="edit"]', function () {
                const userRecord = users.find((entry) => Number(entry.id) === Number($(this).data('id')));

                if (!userRecord) {
                    return;
                }

                $('#userId').val(userRecord.id);
                $('#fullname').val(userRecord.fullname);
                $('#username').val(userRecord.username);
                $('#email').val(userRecord.email);
                $('#password').val('');
                $('#role').val(userRecord.role);
                $('#formModeBadge').text('Edit');
                $('#saveButton').text('Update User');
                clearFormAlert();
            });

            $('#usersTableBody').on('click', '[data-action="delete"]', function () {
                const userId = Number($(this).data('id'));
                const userRecord = users.find((entry) => Number(entry.id) === userId);

                if (!userRecord) {
                    return;
                }

                if (!window.confirm(`Delete ${userRecord.fullname}? This cannot be undone.`)) {
                    return;
                }

                $.ajax({
                    url: `<?= site_url('dashboard/admin/users') ?>/${userId}`,
                    method: 'DELETE',
                    dataType: 'json'
                }).done(handleDashboardResponse)
                  .fail(handleRequestError);
            });

            function submitUserForm() {
                const userId = $('#userId').val();
                const formData = {
                    fullname: $('#fullname').val().trim(),
                    username: $('#username').val().trim(),
                    email: $('#email').val().trim(),
                    password: $('#password').val(),
                    role: $('#role').val()
                };

                const endpoint = userId
                    ? `<?= site_url('dashboard/admin/users') ?>/${userId}`
                    : `<?= site_url('dashboard/admin/users') ?>`;

                $.ajax({
                    url: endpoint,
                    method: 'POST',
                    data: formData,
                    dataType: 'json'
                }).done(handleDashboardResponse)
                  .fail(handleRequestError);
            }

            function fetchUsers() {
                $.getJSON(`<?= site_url('dashboard/admin/users') ?>`, function (response) {
                    users = response.data || [];
                    renderUsers(users);
                    updateMetrics(users, auditLogs);
                });
            }

            function fetchAuditLogs() {
                $.getJSON(`<?= site_url('dashboard/admin/audit-logs') ?>`, function (response) {
                    auditLogs = response.data || [];
                    renderAuditLogs(auditLogs);
                    updateMetrics(users, auditLogs);
                });
            }

            function handleDashboardResponse(response) {
                users = response.data.users || [];
                auditLogs = response.data.auditLogs || [];
                renderUsers(users);
                renderAuditLogs(auditLogs);
                updateMetrics(users, auditLogs);
                showFormAlert('success', response.message || 'Request completed successfully.');
                resetForm();
            }

            function handleRequestError(xhr) {
                const response = xhr.responseJSON || {};
                const errors = response.errors ? Object.values(response.errors).join(' ') : '';
                showFormAlert('danger', response.message ? `${response.message} ${errors}`.trim() : 'The request could not be completed.');
            }

            function renderUsers(data) {
                const tbody = $('#usersTableBody');

                if (!data.length) {
                    tbody.html('<tr><td colspan="6" class="empty-state">No user accounts found.</td></tr>');
                    return;
                }

                tbody.html(data.map(function (entry) {
                    const roleClass = entry.role === 'admin' ? 'text-bg-dark' : 'text-bg-warning';
                    const deleteDisabled = Number(entry.id) === currentUserId ? 'disabled' : '';

                    return `
                        <tr>
                            <td>
                                <div class="fw-semibold">${escapeHtml(entry.fullname)}</div>
                            </td>
                            <td>${escapeHtml(entry.username)}</td>
                            <td>${escapeHtml(entry.email)}</td>
                            <td><span class="badge ${roleClass} badge-role">${escapeHtml(entry.role)}</span></td>
                            <td>${formatDate(entry.createdAt)}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" type="button" data-action="edit" data-id="${entry.id}">Edit</button>
                                    <button class="btn btn-outline-danger" type="button" data-action="delete" data-id="${entry.id}" ${deleteDisabled}>Delete</button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join(''));
            }

            function renderAuditLogs(data) {
                const tbody = $('#auditTableBody');
                const previewLogs = data.slice(0, 3);

                if (!previewLogs.length) {
                    tbody.html('<tr><td colspan="4" class="empty-state">No audit entries available.</td></tr>');
                    return;
                }

                tbody.html(previewLogs.map(function (entry) {
                    const displayName = entry.fullname ? `${entry.fullname} (${entry.username || 'n/a'})` : 'Unknown user';

                    return `
                        <tr>
                            <td>
                                <div class="fw-semibold">${escapeHtml(displayName)}</div>
                                <div class="text-secondary small text-uppercase">${escapeHtml(entry.role || 'unknown')}</div>
                            </td>
                            <td><span class="badge text-bg-light border">${escapeHtml(entry.action)}</span></td>
                            <td>${escapeHtml(entry.description)}</td>
                            <td>${formatDate(entry.createdAt)}</td>
                        </tr>
                    `;
                }).join(''));
            }

            function updateMetrics(userRecords, logRecords) {
                $('#usersCount').text(userRecords.length);
                $('#adminsCount').text(userRecords.filter(function (entry) {
                    return entry.role === 'admin';
                }).length);
                $('#auditCount').text(logRecords.length);
            }

            function resetForm() {
                $('#userForm')[0].reset();
                $('#userId').val('');
                $('#role').val('admin');
                $('#formModeBadge').text('Create');
                $('#saveButton').text('Save User');
                clearFormAlert();
            }

            function showFormAlert(type, message) {
                $('#formAlert')
                    .removeClass('d-none alert-success alert-danger')
                    .addClass(`alert-${type}`)
                    .text(message);
            }

            function clearFormAlert() {
                $('#formAlert')
                    .addClass('d-none')
                    .removeClass('alert-success alert-danger')
                    .text('');
            }

            function formatDate(value) {
                if (!value) {
                    return 'N/A';
                }

                const date = new Date(value.replace(' ', 'T'));

                if (Number.isNaN(date.getTime())) {
                    return value;
                }

                return new Intl.DateTimeFormat('en-PH', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit'
                }).format(date);
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }
        });
    </script>
</body>
</html>