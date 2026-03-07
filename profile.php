<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/lang.php';

requireLogin();

$user = getUserDetails($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" dir="<?php echo (getLang() == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo trans('profile'); ?> - SmartArchive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-gradient"></div>

    <div class="sidebar-pro" id="sidebar">
        <div class="sidebar-header">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <img src="assets/img/logo.svg" alt="Logo" width="32" height="32" style="border-radius: 8px; flex-shrink: 0;">
                <span class="fw-bold sidebar-text" style="background: linear-gradient(135deg, #6366f1, #a29bfe); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 0.95rem; letter-spacing: -0.2px;">SmartArchive</span>
            </div>
            <button class="btn btn-link text-white-50 p-0 d-md-block d-none" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>
        </div>

        <nav class="sidebar-nav mt-3">
            <a href="dashboard.php" class="nav-item-pro">
                <i class="bi bi-grid-1x2 nav-icon"></i> <span class="sidebar-text"><?php echo trans('dashboard'); ?></span>
            </a>
            <a href="dashboard.php" class="nav-item-pro">
                <i class="bi bi-image nav-icon"></i> <span class="sidebar-text"><?php echo trans('images'); ?></span>
            </a>
            <a href="dashboard.php" class="nav-item-pro">
                <i class="bi bi-receipt nav-icon"></i> <span class="sidebar-text"><?php echo trans('factures'); ?></span>
            </a>
            
            <div class="sidebar-text small text-uppercase text-white-50 px-4 mt-4 mb-2 opacity-50 fw-bold letter-spacing-1"><?php echo trans('personal'); ?></div>
            
            <a href="profile.php" class="nav-item-pro active">
                <i class="bi bi-person nav-icon"></i> <span class="sidebar-text"><?php echo trans('profile'); ?></span>
            </a>
            <a href="#settings" class="nav-item-pro">
                <i class="bi bi-gear nav-icon"></i> <span class="sidebar-text"><?php echo trans('settings'); ?></span>
            </a>
        </nav>

        <div class="sidebar-footer p-3 mt-auto">
            <div class="sidebar-storage-box glass p-3 rounded-4 mb-3 sidebar-text">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-white-50 opacity-75"><?php echo trans('storage'); ?></small>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-2 py-1">PRO</span>
                </div>
                    <div class="progress glass bg-opacity-10 mb-2" style="height: 6px; border-radius: 3px;">
                        <div class="progress-bar bg-primary rounded-pill shadow-glow" style="width: 15%"></div>
                    </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-white-50" style="font-size: 0.65rem;">1.5GB / 10GB</small>
                    <small class="text-white-50 opacity-50" style="font-size: 0.65rem;">15%</small>
                </div>
                <button class="btn btn-primary btn-sm w-100 mt-3 rounded-pill py-1 small fw-bold shadow-glow" style="font-size: 0.65rem;">
                    <i class="bi bi-lightning-charge-fill me-1"></i> <?php echo trans('upgrade_plan'); ?>
                </button>
            </div>
            <a href="api/logout.php" class="nav-item-pro text-danger bg-danger bg-opacity-10 border border-danger border-opacity-10 py-2">
                <i class="bi bi-box-arrow-right nav-icon"></i> <span class="sidebar-text"><?php echo trans('logout'); ?></span>
            </a>
        </div>
    </div>

    <main class="main-content">
        <header class="mb-5 fade-in">
            <h3 class="fw-bold mb-1"><?php echo trans('profile'); ?></h3>
            <p class="text-white-50 small">Manage your account and preferences</p>
        </header>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass p-4 text-center fade-in">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=6366f1&color=fff&size=128" class="rounded-circle mb-4 border border-5 border-white border-opacity-10 shadow-lg">
                    <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p class="text-white-50 small mb-4"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-primary px-3 py-2 rounded-pill shadow-glow">PRO Member</span>
                        <span class="badge bg-white-10 px-3 py-2 rounded-pill"><?php echo strtoupper($user['subscription_plan'] ?? 'FREE'); ?></span>
                    </div>

                    <div class="border-top border-white border-opacity-5 pt-4 text-start">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-white-50">Member Since</small>
                            <small class="fw-bold"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-white-50">Storage Limit</small>
                            <small class="fw-bold">10 GB</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass p-4 mb-4 fade-in" style="animation-delay: 0.1s;">
                    <h6 class="fw-bold mb-4"><i class="bi bi-person-lines-fill me-2 text-primary"></i> Personal Details</h6>
                    <form id="profileForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-white-50">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-white-50">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-white-50">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+212 6xx xxx xxx">
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2 small rounded-pill">Save Profile Changes</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="glass p-4 mb-4 fade-in" id="settings" style="animation-delay: 0.2s;">
                    <h6 class="fw-bold mb-4"><i class="bi bi-shield-lock-fill me-2 text-primary"></i> Security</h6>
                    <form id="passwordForm">
                        <div class="row g-3">
                            <div class="col-md-12 mb-2">
                                <label class="form-label small text-white-50">Current Password <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control" placeholder="Required to change password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-white-50">New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep same">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-white-50">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                            </div>
                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-primary px-4 py-2 small rounded-pill">Update Password</button>
                            </div>
                        </div>
                    </form>

                    <hr class="border-white border-opacity-10 my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-shield-check me-2 text-success"></i> Two-Factor Authentication (2FA)</h6>
                        <span class="badge bg-secondary bg-opacity-25 text-white-50">Not Configured</span>
                    </div>
                    <p class="small text-white-50 mb-4">Add an extra layer of security to your account. Once configured, you'll be required to enter both your password and an authentication code from your mobile phone in order to sign in.</p>
                    <button class="btn btn-outline-light px-4 py-2 small rounded-pill" onclick="open2FAModal()">
                        Setup 2FA
                    </button>
                </div>

                <div class="glass p-4 fade-in" style="animation-delay: 0.3s;">
                    <h6 class="fw-bold mb-4"><i class="bi bi-palette-fill me-2 text-primary"></i> Preferences</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label small text-white-50">Language</label>
                            <div class="btn-group w-100 glass p-1">
                                <a href="?lang=fr" class="btn btn-sm rounded-3 w-100 <?php echo getLang() == 'fr' ? 'btn-primary shadow-sm' : 'text-white-50'; ?>">Français</a>
                                <a href="?lang=ar" class="btn btn-sm rounded-3 w-100 <?php echo getLang() == 'ar' ? 'btn-primary shadow-sm' : 'text-white-50'; ?>">العربية</a>
                                <a href="?lang=en" class="btn btn-sm rounded-3 w-100 <?php echo getLang() == 'en' ? 'btn-primary shadow-sm' : 'text-white-50'; ?>">English</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-white-50">Appearance</label>
                            <button class="btn btn-secondary w-100 rounded-3 small" id="themeToggle">
                                <i class="bi bi-moon-stars me-2"></i> Switch Theme
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast glass border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body text-white">
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                <span id="toastMessage"></span>
            </div>
        </div>
    </div>

    <!-- 2FA Modal -->
    <div class="modal fade" id="twoFactorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i>2FA Setup</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-white-50 mb-4">Enhance your account security by enabling Two-Factor Authentication.</p>
                    
                    <div id="twoFactorStep1">
                        <div class="glass p-3 rounded-4 mb-4 d-inline-block">
                            <i class="bi bi-qr-code fs-1 text-white"></i>
                            <p class="mt-2 small fw-bold mb-0">Scan this QR Code</p>
                        </div>
                        <p class="small text-white-50 mb-4">Use Google Authenticator or Authy to scan the code above.</p>
                        <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold" onclick="show2FAVerification()">
                            I've scanned it <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>

                    <div id="twoFactorStep2" class="d-none">
                        <label class="form-label text-white-50 small fw-bold mb-3">Enter Verification Code</label>
                        <div class="d-flex justify-content-center gap-2 mb-4">
                            <input type="text" maxlength="6" class="form-control glass border-0 text-center fw-bold fs-4 rounded-3 text-white" style="width: 150px; letter-spacing: 5px; background: rgba(255,255,255,0.1);" placeholder="000000" id="twoFactorCode">
                        </div>
                        <button class="btn btn-success w-100 rounded-pill py-2 fw-bold" id="verify2FABtn">
                            Verify & Enable
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const toast = new bootstrap.Toast(document.getElementById('liveToast'));
            
            // Theme Logic
            const themeToggle = document.getElementById('themeToggle');
            const updateTheme = (theme) => {
                const icon = themeToggle.querySelector('i');
                if (theme === 'light') {
                    document.documentElement.setAttribute('data-theme', 'light');
                    icon.className = 'bi bi-sun me-2';
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    icon.className = 'bi bi-moon-stars me-2';
                }
            };
            const savedTheme = localStorage.getItem('theme') || 'dark';
            updateTheme(savedTheme);
            themeToggle.addEventListener('click', () => {
                const current = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
                const next = current === 'light' ? 'dark' : 'light';
                localStorage.setItem('theme', next);
                updateTheme(next);
            });

            // Sidebar logic
            if (localStorage.getItem('sidebarCollapsed') === 'true') sidebar.classList.add('collapsed');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
            }

            const showToast = (msg) => {
                document.getElementById('toastMessage').innerText = msg;
                toast.show();
            };

            // Password strength logic
            const passInput = document.querySelector('input[name="new_password"]');
            passInput.addEventListener('input', () => {
                const val = passInput.value;
                let score = 0;
                if (val.length > 8) score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;
                
                // You could add a UI meter here if you have one
                if (val.length > 0) {
                    passInput.style.borderBottom = score < 2 ? '2px solid #ff4757' : (score < 4 ? '2px solid #ffa502' : '2px solid #2ed573');
                } else {
                    passInput.style.borderBottom = 'none';
                }
            });

            // Profile Update
            document.getElementById('profileForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                try {
                    const resp = await fetch('api/update_profile.php', { method: 'POST', body: formData });
                    const res = await resp.json();
                    if (res.success) showToast('Profile updated successfully!');
                    else alert(res.message);
                } catch (err) { alert('Update failed'); }
            });

            // Password Update
            document.getElementById('passwordForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                
                if (formData.get('new_password') && !formData.get('current_password')) {
                    alert('Please enter your current password to set a new one.');
                    return;
                }
                
                if (formData.get('new_password') !== formData.get('confirm_password')) {
                    alert('Passwords do not match');
                    return;
                }
                
                if (!formData.get('new_password') && !formData.get('current_password')) {
                    alert('Please enter a new password to update');
                    return;
                }
                
                try {
                    const resp = await fetch('api/update_profile.php', { method: 'POST', body: formData });
                    const res = await resp.json();
                    if (res.success) {
                        showToast('Password updated successfully!');
                        e.target.reset();
                    } else {
                        alert(res.message);
                    }
                } catch (err) { alert('Update failed'); }
            });
        });

        // 2FA Functions
        const twoFactorModal = new bootstrap.Modal(document.getElementById('twoFactorModal'));
        
        function open2FAModal() {
            document.getElementById('twoFactorStep1').classList.remove('d-none');
            document.getElementById('twoFactorStep2').classList.add('d-none');
            document.getElementById('twoFactorCode').value = '';
            twoFactorModal.show();
        }

        function show2FAVerification() {
            document.getElementById('twoFactorStep1').classList.add('d-none');
            document.getElementById('twoFactorStep2').classList.remove('d-none');
        }

        document.getElementById('verify2FABtn').addEventListener('click', async () => {
            const code = document.getElementById('twoFactorCode').value;
            if (code.length !== 6) {
                alert('Please enter a valid 6-digit code');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'verify');
                formData.append('code', code);
                
                const resp = await fetch('api/manage_2fa.php', { method: 'POST', body: formData });
                const res = await resp.json();
                
                if (res.success) {
                    twoFactorModal.hide();
                    location.reload(); // Reload to show updated status
                } else {
                    alert(res.message);
                }
            } catch (err) { alert('Verification failed'); }
        });
    </script>
</body>
</html>
