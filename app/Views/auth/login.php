<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Login') ?> | MeralQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --auth-overlay: rgba(10, 17, 40, 0.68);
            --auth-surface: #ffffff;
            --auth-accent: #ffb703;
            --auth-ink: #122033;
            --auth-muted: #5f6c80;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--auth-ink);
            background:
                linear-gradient(var(--auth-overlay), var(--auth-overlay)),
                url("<?= base_url('assets/login_bg.jpg') ?>") center center / cover no-repeat fixed;
        }

        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem 0.875rem;
        }

        .auth-card {
            width: 100%;
            max-width: 780px;
            overflow: hidden;
            border: 0;
            border-radius: 28px;
            background: transparent;
            box-shadow: 0 24px 60px rgba(5, 10, 25, 0.35);
        }

        .auth-brand {
            min-height: 450px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: linear-gradient(180deg, rgba(7, 12, 30, 0.68), rgba(7, 12, 30, 0.9));
            color: #f8fafc;
        }

        .auth-brand img {
            width: 170px;
            max-width: 100%;
        }

        .auth-brand h1 {
            font-size: clamp(1.6rem, 2.2vw, 2.25rem);
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 0.85rem;
        }

        .auth-brand p,
        .auth-brand li {
            color: rgba(248, 250, 252, 0.84);
            font-size: 0.92rem;
        }

        .auth-panel {
            min-height: 450px;
            padding: 2rem;
            background: var(--auth-surface);
        }

        .auth-panel-content {
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
        }

        .auth-panel-header {
            text-align: center;
            margin-bottom: 0.75rem;
        }

        .auth-panel-logo {
            width: 132px;
            max-width: 100%;
            display: inline-block;
            margin-bottom: 0;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 183, 3, 0.14);
            color: #8a5b00;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: 0.76rem;
        }

        .auth-panel h2 {
            font-size: clamp(1.5rem, 1.9vw, 2rem);
            font-weight: 700;
            margin-top: 0.6rem;
            margin-bottom: 0.6rem;
        }

        .auth-panel .subtitle {
            color: var(--auth-muted);
            margin-bottom: 1.25rem;
            max-width: 460px;
            font-size: 0.92rem;
        }

        .form-control {
            min-height: 50px;
            border-radius: 16px;
            border-color: rgba(18, 32, 51, 0.12);
            padding-inline: 1rem;
        }

        .form-control:focus {
            border-color: rgba(255, 183, 3, 0.9);
            box-shadow: 0 0 0 0.25rem rgba(255, 183, 3, 0.16);
        }

        .btn-auth {
            min-height: 50px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, #ffb703, #fb8500);
            color: #10213a;
            font-weight: 700;
        }

        .btn-auth:hover {
            color: #10213a;
            filter: brightness(1.03);
        }

        .system-points {
            padding-left: 1.1rem;
            margin-bottom: 0;
        }

        .helper-copy {
            font-size: 0.85rem;
            color: var(--auth-muted);
        }

        @media (max-width: 991.98px) {
            .auth-brand,
            .auth-panel {
                min-height: auto;
            }

            .auth-brand,
            .auth-panel {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <section class="card auth-card">
            <div class="row g-0">
                <div class="col-lg-6 d-none d-lg-flex">
                    <div class="auth-brand w-100">
                        <div>
                            <h1>MeralKoo</h1>
                            <p class="mb-4">MeralKoo is a web-based electric billing system.</p>
                            <ul class="system-points">
                                <li>Admin dashboard for account oversight and audit visibility</li>
                                <li>User dashboard prepared for bill computation and billing history</li>
                            </ul>
                        </div>
                        <p class="mb-0">CodeIgniter 4 · Bootstrap · jQuery · AJAX</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="auth-panel h-100 d-flex flex-column justify-content-center">
                        <div class="auth-panel-content">
                            <div class="auth-panel-header">
                                <img class="auth-panel-logo" src="<?= base_url('assets/MeralKoo.svg') ?>" alt="MeralQ logo">
                            </div>
                            <h2>Sign in to continue</h2>
                            <p class="subtitle">Use your username or email and password to enter the correct dashboard for your account role.</p>

                            <div id="loginAlert" class="alert d-none" role="alert"></div>

                            <form id="loginForm" novalidate>
                                <div class="mb-3">
                                    <label for="credential" class="form-label fw-semibold">Username or Email</label>
                                    <input type="text" class="form-control" id="credential" name="credential" placeholder="Enter username or email" autocomplete="username">
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" autocomplete="current-password">
                                </div>
                                <button type="submit" class="btn btn-auth w-100" id="loginButton">Sign In</button>
                            </form>

                            <p class="helper-copy mt-4 mb-0">This first screen is ready for real authentication and role-based redirection to the admin or normal user dashboard.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(function () {
            const $form = $('#loginForm');
            const $button = $('#loginButton');
            const $alert = $('#loginAlert');

            function showAlert(type, message) {
                $alert.removeClass('d-none alert-success alert-danger').addClass('alert-' + type).text(message);
            }

            $form.on('submit', function (event) {
                event.preventDefault();

                $button.prop('disabled', true).text('Signing in...');
                $alert.addClass('d-none').removeClass('alert-success alert-danger').text('');

                $.ajax({
                    url: '<?= site_url('login') ?>',
                    method: 'POST',
                    data: $form.serialize(),
                    dataType: 'json'
                }).done(function (response) {
                    showAlert('success', response.message || 'Login successful. Redirecting...');
                    window.location.href = response.redirect || '<?= site_url('dashboard') ?>';
                }).fail(function (xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Unable to sign in. Please try again.';

                    showAlert('danger', message);
                }).always(function () {
                    $button.prop('disabled', false).text('Sign In');
                });
            });
        });
    </script>
</body>
</html>