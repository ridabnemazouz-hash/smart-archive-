<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" dir="<?php echo isRTL() ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo trans('app_name'); ?> - <?php echo trans('login'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <style>
        .auth-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            padding: 50px;
            width: 100%;
            max-width: 450px;
            color: #fff;
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 12px;
            padding: 14px;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border-color: #667eea;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .toggle-link {
            color: #a29bfe;
            text-decoration: none;
            font-weight: 700;
        }
        .z-index-10 {
            z-index: 10 !important;
        }
        .hover-text-white:hover {
            color: #fff !important;
        }
        .hover-bg-white:hover {
            background-color: #fff !important;
            border-color: #fff !important;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>
    <div class="particles"></div>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <!-- Language Switcher -->
    <div class="position-fixed top-0 end-0 p-4" style="z-index: 1000;">
        <div class="btn-group glass rounded-pill p-1">
            <a href="?lang=fr" class="btn btn-sm rounded-pill <?php echo getLang() == 'fr' ? 'btn-primary' : 'text-white-50'; ?>">FR</a>
            <a href="?lang=ar" class="btn btn-sm rounded-pill <?php echo getLang() == 'ar' ? 'btn-primary' : 'text-white-50'; ?>">AR</a>
            <a href="?lang=en" class="btn btn-sm rounded-pill <?php echo getLang() == 'en' ? 'btn-primary' : 'text-white-50'; ?>">EN</a>
        </div>
    </div>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="glass p-5 rounded-2xl shadow-lg w-100 fade-in" style="max-width: 450px;">
            <div class="text-center mb-5">
                <div class="glass d-inline-block p-3 rounded-circle mb-3 shadow-glow">
                    <i class="bi bi-archive-fill text-primary fs-1"></i>
                </div>
                <h2 class="fw-bold mb-1"><?php echo trans('app_name'); ?></h2>
                <p class="text-white-50"><?php echo trans('welcome_back'); ?></p>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="auth-form">
                <div class="mb-4">
                    <label class="form-label text-white-50 small ms-2"><?php echo trans('email'); ?></label>
                    <div class="input-group glass rounded-pill overflow-hidden">
                        <span class="input-group-text bg-transparent border-0 text-white-50"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control bg-transparent border-0 text-white py-2" placeholder="name@example.com" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label text-white-50 small ms-2"><?php echo trans('password'); ?></label>
                    <div class="input-group glass rounded-pill overflow-hidden position-relative">
                        <span class="input-group-text bg-transparent border-0 text-white-50"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="loginPassword" class="form-control bg-transparent border-0 text-white py-2 pe-5" placeholder="••••••••" required>
                        <span class="position-absolute top-50 end-0 translate-middle-y me-3 text-white-50 cursor-pointer hover-text-white z-index-10" onclick="togglePasswordVisibility('loginPassword', this)">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4 ms-2 me-2">
                    <div class="form-check mb-0">
                        <input class="form-check-input bg-transparent border-secondary cursor-pointer" type="checkbox" name="remember" id="rememberMe">
                        <label class="form-check-label text-white-50 small mt-1 cursor-pointer" for="rememberMe">
                            Remember Me
                        </label>
                    </div>
                    <a href="javascript:void(0)" onclick="showForgotPassword()" class="text-primary small fw-bold text-decoration-none hover-lift-sm transition-all">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-glow mb-4 transition-hover">
                    <?php echo trans('login'); ?> <i class="bi bi-arrow-right ms-2 mt-1"></i>
                </button>

                <div class="d-flex align-items-center mb-4">
                    <hr class="flex-grow-1 border-white border-opacity-25">
                    <span class="px-3 text-white-50 small fw-bold">OR</span>
                    <hr class="flex-grow-1 border-white border-opacity-25">
                </div>
                
                <div class="d-flex gap-3 mb-4">
                    <button type="button" class="btn btn-outline-light w-50 rounded-pill py-2 text-white-50 border-white border-opacity-25 d-flex align-items-center justify-content-center hover-bg-white hover-text-dark transition-all">
                        <i class="bi bi-google me-2 text-danger"></i> Google
                    </button>
                    <button type="button" class="btn btn-outline-light w-50 rounded-pill py-2 text-white-50 border-white border-opacity-25 d-flex align-items-center justify-content-center hover-bg-white hover-text-dark transition-all">
                        <i class="bi bi-github me-2"></i> GitHub
                    </button>
                </div>
                <p class="text-center text-white-50 small mb-0">
                    <?php echo trans('no_account'); ?> <a href="javascript:void(0)" onclick="toggleAuth()" id="showRegister" class="text-primary fw-bold text-decoration-none ms-1"><?php echo trans('register'); ?></a>
                </p>
            </form>

            <!-- Register Form (Hidden by default) -->
            <form id="registerForm" class="auth-form d-none">
                <div class="mb-4">
                    <label class="form-label text-white-50 small ms-2"><?php echo trans('name'); ?></label>
                    <div class="input-group glass rounded-pill overflow-hidden">
                        <span class="input-group-text bg-transparent border-0 text-white-50"><i class="bi bi-person"></i></span>
                        <input type="text" name="name" class="form-control bg-transparent border-0 text-white py-2" placeholder="John Doe" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label text-white-50 small ms-2"><?php echo trans('email'); ?></label>
                    <div class="input-group glass rounded-pill overflow-hidden">
                        <span class="input-group-text bg-transparent border-0 text-white-50"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control bg-transparent border-0 text-white py-2" placeholder="name@example.com" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label text-white-50 small ms-2"><?php echo trans('password'); ?></label>
                    <div class="input-group glass rounded-pill overflow-hidden position-relative mb-1">
                        <span class="input-group-text bg-transparent border-0 text-white-50"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="registerPassword" class="form-control bg-transparent border-0 text-white py-2 pe-5" placeholder="••••••••" required onkeyup="checkPasswordStrength(this.value)">
                        <span class="position-absolute top-50 end-0 translate-middle-y me-3 text-white-50 cursor-pointer hover-text-white z-index-10" onclick="togglePasswordVisibility('registerPassword', this)">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                    <div class="progress bg-transparent px-2" style="height: 4px; border-radius: 2px;">
                        <div id="passwordStrengthBar" class="progress-bar transition-all" role="progressbar" style="width: 0%; border-radius: 2px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small id="passwordStrengthText" class="text-white-50 ms-2 d-none" style="font-size: 0.7rem;"></small>
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-glow mb-4 transition-hover mt-2">
                    <?php echo trans('register'); ?> <i class="bi bi-person-plus ms-2 mt-1"></i>
                </button>
                
                <div class="d-flex align-items-center mb-4">
                    <hr class="flex-grow-1 border-white border-opacity-25">
                    <span class="px-3 text-white-50 small fw-bold">OR</span>
                    <hr class="flex-grow-1 border-white border-opacity-25">
                </div>
                
                <div class="d-flex gap-3 mb-4">
                    <button type="button" class="btn btn-outline-light w-50 rounded-pill py-2 text-white-50 border-white border-opacity-25 d-flex align-items-center justify-content-center hover-bg-white hover-text-dark transition-all">
                        <i class="bi bi-google me-2 text-danger"></i> Google
                    </button>
                    <button type="button" class="btn btn-outline-light w-50 rounded-pill py-2 text-white-50 border-white border-opacity-25 d-flex align-items-center justify-content-center hover-bg-white hover-text-dark transition-all">
                        <i class="bi bi-github me-2"></i> GitHub
                    </button>
                </div>
                <p class="text-center text-white-50 small mb-0">
                    <?php echo trans('has_account'); ?> <a href="javascript:void(0)" onclick="toggleAuth()" id="showLogin" class="text-primary fw-bold text-decoration-none ms-1"><?php echo trans('login'); ?></a>
                </p>
            </form>

        <div id="authAlert" class="alert alert-danger mt-3" style="display: none;"></div>
    </div>

    <script src="assets/js/auth.js"></script>
    <script>
        function toggleAuth() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const welcomeMsg = document.querySelector('p.text-white-50');
            
            if (loginForm.classList.contains('d-none')) {
                loginForm.classList.remove('d-none');
                registerForm.classList.add('d-none');
                if(welcomeMsg) welcomeMsg.textContent = "<?php echo trans('welcome_back'); ?>";
            } else {
                loginForm.classList.add('d-none');
                registerForm.classList.remove('d-none');
                if(welcomeMsg) welcomeMsg.textContent = "<?php echo trans('create_account'); ?>";
            }
            // Clear any old alerts
            const authAlert = document.getElementById('authAlert');
            if(authAlert) authAlert.style.display = 'none';
        }
    </script>
</body>
</html>
