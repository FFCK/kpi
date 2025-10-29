<?php
/**
 * Test Bootstrap 5.3.8 - Phase 1
 * Vérification du chargement CSS/JS depuis vendor/
 */
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Bootstrap 5.3.8</title>

    <!-- Bootstrap 5.3.8 CSS depuis vendor -->
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css?v=5.3.8" rel="stylesheet">

    <style>
        .test-box { margin: 20px 0; padding: 15px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-primary">✅ Test Bootstrap 5.3.8 - Phase 1</h1>

        <div class="alert alert-success test-box" role="alert">
            <h4 class="alert-heading">Installation réussie!</h4>
            <p>Bootstrap 5.3.8 est chargé depuis <code>vendor/twbs/bootstrap/dist/</code></p>
            <hr>
            <p class="mb-0">Composer: <strong>✅ OK</strong></p>
        </div>

        <div class="card test-box">
            <div class="card-header">
                <h5>Tests des composants Bootstrap 5.3.8</h5>
            </div>
            <div class="card-body">

                <!-- Test Grid System -->
                <h6>Grid System (Flexbox)</h6>
                <div class="row mb-3">
                    <div class="col-md-4 bg-primary text-white p-2">Col 1</div>
                    <div class="col-md-4 bg-secondary text-white p-2">Col 2</div>
                    <div class="col-md-4 bg-success text-white p-2">Col 3</div>
                </div>

                <!-- Test Buttons -->
                <h6>Buttons</h6>
                <div class="mb-3">
                    <button type="button" class="btn btn-primary">Primary</button>
                    <button type="button" class="btn btn-secondary">Secondary</button>
                    <button type="button" class="btn btn-success">Success</button>
                    <button type="button" class="btn btn-danger">Danger</button>
                </div>

                <!-- Test Modal -->
                <h6>Modal (JS Component)</h6>
                <button type="button" class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#testModal">
                    Ouvrir Modal
                </button>

                <!-- Test Dropdown -->
                <h6>Dropdown (JS Component)</h6>
                <div class="dropdown mb-3">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Dropdown
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Action 1</a></li>
                        <li><a class="dropdown-item" href="#">Action 2</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Action 3</a></li>
                    </ul>
                </div>

                <!-- Test Utilities -->
                <h6>Utilities CSS (Bootstrap 5)</h6>
                <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
                    <span class="badge bg-primary">Flexbox</span>
                    <span class="badge bg-success">Utilities</span>
                    <span class="badge bg-danger">Bootstrap 5</span>
                </div>

                <!-- Test Dark Mode (Bootstrap 5.3) -->
                <h6>Dark Mode (Nouveau Bootstrap 5.3)</h6>
                <div class="mb-3">
                    <button class="btn btn-outline-secondary" onclick="toggleTheme()">
                        🌙 Toggle Dark Mode
                    </button>
                    <span id="themeStatus" class="ms-3"></span>
                </div>

            </div>
        </div>

        <div class="alert alert-info test-box">
            <strong>Phase 1 complète!</strong> Bootstrap 5.3.8 fonctionne correctement.
            <br>Prochaine étape: <strong>Phase 2</strong> - Migration des fichiers Bootstrap 5.x
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="testModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Modal Bootstrap 5.3.8</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>✅ Le composant Modal fonctionne!</p>
                    <p>Bootstrap 5 JS est correctement chargé depuis vendor/</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3.8 JS Bundle (inclut Popper.js) depuis vendor -->
    <script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js?v=5.3.8"></script>

    <script>
        // Test Dark Mode (Bootstrap 5.3 feature)
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            updateThemeStatus(newTheme);
        }

        function updateThemeStatus(theme) {
            document.getElementById('themeStatus').textContent =
                `Theme actuel: ${theme} ${theme === 'dark' ? '🌙' : '☀️'}`;
        }

        // Init
        updateThemeStatus('light');

        // Log version Bootstrap
        console.log('✅ Bootstrap version:', bootstrap.Tooltip.VERSION);
    </script>
</body>
</html>
