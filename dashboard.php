<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" dir="<?php echo isRTL() ? 'rtl' : 'ltr'; ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo trans('app_name'); ?> - <?php echo trans('dashboard'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
</head>
<body>
    <!-- Page Load Splash -->
    <div id="pageSplash">
        <img src="assets/img/logo.svg" class="splash-logo" width="72" height="72" alt="SmartArchive">
        <div class="splash-name">SmartArchive</div>
        <div class="splash-bar"><div class="splash-bar-fill"></div></div>
    </div>

    <div class="bg-gradient"></div>
    <div class="particles"></div>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

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
            <a href="dashboard.php" class="nav-item-pro active">
                <i class="bi bi-grid-1x2 nav-icon"></i> <span class="sidebar-text"><?php echo trans('dashboard'); ?></span>
            </a>
            <a href="#" class="nav-item-pro" onclick="loadDocumentsWithFilter('category', 'Image', this)">
                <i class="bi bi-image nav-icon"></i> <span class="sidebar-text"><?php echo trans('images'); ?></span>
            </a>
            <a href="#" class="nav-item-pro" onclick="loadDocumentsWithFilter('category', 'Facture', this)">
                <i class="bi bi-receipt nav-icon"></i> <span class="sidebar-text"><?php echo trans('factures'); ?></span>
            </a>
            <a href="javascript:void(0)" onclick="loadCreditManager(this)" class="nav-item-pro">
                <i class="bi bi-wallet2 nav-icon"></i> <span class="sidebar-text"><?php echo trans('credit_manager'); ?></span>
            </a>
            <a href="#" class="nav-item-pro" onclick="loadFavorites(this)">
                <i class="bi bi-star nav-icon"></i> <span class="sidebar-text">Favorites</span>
            </a>
            <div class="sidebar-text small text-uppercase text-white-50 px-4 mt-4 mb-2 opacity-50 fw-bold letter-spacing-1">Folders</div>
            <div id="sidebarFolders" class="mb-2">
                <!-- Folders will be loaded here -->
            </div>
            <a href="javascript:void(0)" onclick="openCreateFolderModal()" class="nav-item-pro small opacity-75">
                <i class="bi bi-plus-circle nav-icon"></i> <span class="sidebar-text">New Folder</span>
            </a>
            
            <div class="sidebar-text small text-uppercase text-white-50 px-4 mt-4 mb-2 opacity-50 fw-bold letter-spacing-1"><?php echo trans('trash_bin'); ?></div>
            <a href="#" class="nav-item-pro" onclick="loadDocumentsWithFilter('trash', '1', this)">
                <i class="bi bi-trash3 nav-icon"></i> <span class="sidebar-text"><?php echo trans('trash_bin'); ?></span>
            </a>
            
            <div class="sidebar-text small text-uppercase text-white-50 px-4 mt-4 mb-2 opacity-50 fw-bold letter-spacing-1"><?php echo trans('tools'); ?></div>
            
            <a href="javascript:void(0)" onclick="openScannerModal()" class="nav-item-pro">
                <i class="bi bi-camera nav-icon"></i> <span class="sidebar-text"><?php echo trans('scanner'); ?></span>
            </a>
            
            <div class="sidebar-text small text-uppercase text-white-50 px-4 mt-4 mb-2 opacity-50 fw-bold letter-spacing-1"><?php echo trans('personal'); ?></div>
            
            <a href="profile.php" class="nav-item-pro">
                <i class="bi bi-person nav-icon"></i> <span class="sidebar-text"><?php echo trans('profile'); ?></span>
            </a>
            <a href="profile.php#settings" class="nav-item-pro">
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
                        <div id="storageUsageBar" class="progress-bar bg-primary rounded-pill shadow-glow" style="width: 0%"></div>
                    </div>
                    <p class="text-white-50 tiny mb-0" id="storageTrendText">Analyzing storage trend...</p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-white-50" id="storageUsageLabel" style="font-size: 0.65rem;">0B / 10GB</small>
                    <small class="text-white-50 opacity-50" id="storageUsagePercent" style="font-size: 0.65rem;">0%</small>
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
        <!-- Header -->
        <header class="main-header d-flex justify-content-between align-items-center mb-5 fade-in">
            <div>
                <h3 class="fw-bold mb-1" id="dynamicGreeting"><?php echo trans('overview'); ?></h3>
                <div class="d-flex align-items-center gap-2">
                    <p class="text-white-50 small mb-0"><?php echo trans('welcome_back'); ?> <?php echo e($_SESSION['user_name']); ?></p>
                    <span class="text-white-50 opacity-25">•</span>
                    <p class="text-white-50 small mb-0" id="realTimeClock"></p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 gap-md-3 w-100 w-md-auto justify-content-between justify-content-md-start">
                <button class="btn btn-link text-white-50 p-0 d-md-none" id="mobileSidebarToggle">
                    <i class="bi bi-list fs-3"></i>
                </button>
                
                <!-- Search Bar -->
                    <div class="search-pro glass p-2 rounded-pill d-flex align-items-center flex-grow-1 shadow-glow" style="max-width: 500px;">
                        <i class="bi bi-search ms-3 opacity-50"></i>
                        <input type="text" id="searchInput" class="form-control bg-transparent border-0 text-white shadow-none ps-2" placeholder="<?php echo trans('search_docs'); ?>...">
                        <div class="form-check form-switch mb-0 ms-2 me-3" title="AI Smart Search">
                            <input class="form-check-input" type="checkbox" id="aiSearchToggle">
                            <label class="form-check-label small text-white-50 ms-1" for="aiSearchToggle"><i class="bi bi-magic"></i> AI</label>
                        </div>
                    </div>

                <div class="header-actions d-flex align-items-center gap-2 gap-md-3">
                    <button class="glass p-2 border-0 text-white-50 rounded-circle" onclick="open2FAModal()" title="Security/2FA">
                        <i class="bi bi-shield-lock-fill"></i>
                    </button>
                    <button class="glass p-2 border-0 text-white-50 rounded-circle" id="themeToggle">
                        <i class="bi bi-moon-stars"></i>
                    </button>

                    <div class="btn-group glass p-1 d-none d-sm-flex">
                        <a href="?lang=fr" class="btn btn-sm rounded-pill <?php echo getLang() == 'fr' ? 'btn-primary shadow-sm' : 'text-white-50'; ?>">FR</a>
                        <a href="?lang=ar" class="btn btn-sm rounded-pill <?php echo getLang() == 'ar' ? 'btn-primary shadow-sm' : 'text-white-50'; ?>">AR</a>
                        <a href="?lang=en" class="btn btn-sm rounded-pill <?php echo getLang() == 'en' ? 'btn-primary shadow-sm' : 'text-white-50'; ?>">EN</a>
                    </div>

                    <div class="dropdown">
                        <button class="glass p-2 border-0 text-white-50 rounded-circle position-relative" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle scale-in"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end glass border-0 shadow-lg p-3 mt-3" style="width: 280px;">
                            <h6 class="fw-bold mb-3">Notifications</h6>
                            <div id="notificationList">
                                <div class="text-center py-4 text-white-50">
                                    <i class="bi bi-check2-all fs-2 mb-2"></i>
                                    <p class="small mb-0">No new notifications</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="glass border-0 text-white p-1 pe-2 pe-md-3 rounded-pill d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=6366f1&color=fff" class="rounded-pill" width="32">
                            <i class="bi bi-chevron-down small text-white-50 d-none d-md-block"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end glass border-0 mt-3 shadow-lg overflow-hidden">
                            <li><a class="dropdown-item py-2" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item py-2" href="profile.php#settings"><i class="bi bi-gear me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider opacity-10"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="api/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <div class="row g-4">
            <!-- Left Area -->
            <div class="col-lg-8">
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="glass p-4 fade-in">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <p class="text-white-50 small mb-1"><?php echo trans('total_docs'); ?></p>
                                    <h2 class="stat-value mb-0" id="totalDocs">0</h2>
                                </div>
                                <div class="glass p-2 rounded-3 text-primary bg-primary bg-opacity-10 border-primary border-opacity-20">
                                    <i class="bi bi-files fs-4"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 small">
                                <span class="text-success fw-bold"><i class="bi bi-arrow-up-short"></i> +4 today</span>
                                <span class="text-white-50 text-opacity-50">vs last week</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="glass p-4 fade-in" style="animation-delay: 0.1s;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <p class="text-white-50 small mb-1"><?php echo trans('important_files'); ?></p>
                                    <h2 class="stat-value mb-0" id="importantDocs">0</h2>
                                </div>
                                <div class="glass p-2 rounded-3 text-warning bg-warning bg-opacity-10 border-warning border-opacity-20">
                                    <i class="bi bi-star-fill fs-4"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 small">
                                <span class="text-white-50 cursor-pointer hover-link">View high priority files</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass p-0 mb-5 overflow-hidden fade-in" style="animation-delay: 0.2s;">
                    <div class="p-4 border-bottom border-white border-opacity-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="fw-bold mb-0"><?php echo trans('my_documents'); ?></h5>
                            <?php if (isAdmin()): ?>
                            <button id="bulkDeleteBtn" class="btn btn-sm btn-outline-danger d-none rounded-pill px-3" onclick="handleBulkDelete()">
                                <i class="bi bi-trash me-1"></i> Delete Selected
                            </button>
                            <?php endif; ?>
                            <div class="btn-group glass p-1" style="border-radius: 10px;">
                                <button class="view-toggle-btn active" id="listViewBtn"><i class="bi bi-list-ul"></i></button>
                                <button class="view-toggle-btn" id="gridViewBtn"><i class="bi bi-grid-3x3-gap"></i></button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-2 ms-auto">
                            <select id="categoryFilter" class="form-select glass border-0 text-white-50 w-auto rounded-pill px-3 py-2 small shadow-sm">
                                <option value=""><?php echo trans('all_categories'); ?></option>
                                <option value="Facture"><?php echo trans('invoices'); ?></option>
                                <option value="Image"><?php echo trans('images'); ?></option>
                                <option value="Administratif">Administratif</option>
                                <option value="Personnel">Personnel</option>
                            </select>

                            <input type="date" id="dateFilter" class="form-control glass border-0 text-white-50 w-auto rounded-pill px-3 py-2 small shadow-sm" placeholder="Filter by date">

                            <select id="sortFilter" class="form-select glass border-0 text-white-50 w-auto rounded-pill px-3 py-2 small shadow-sm">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="important">Important First</option>
                            </select>

                            <button class="btn btn-primary px-3 py-2 small rounded-pill shadow-glow ms-lg-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="bi bi-plus-lg me-1"></i> <?php echo trans('upload_new'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Dropzone -->
                        <div id="dropZone" class="glass border-dashed p-5 text-center mb-4 cursor-pointer hover-lift">
                            <i class="bi bi-cloud-arrow-up fs-1 text-primary mb-3 d-block"></i>
                            <h6 class="fw-bold"><?php echo trans('drag_drop'); ?></h6>
                            <p class="text-white-50 small mb-0"><?php echo trans('or_click'); ?></p>
                            <input type="file" id="fileInputHidden" class="d-none">
                        </div>

                        <!-- Data Views -->
                        <div id="listView">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;"><input type="checkbox" id="selectAll" class="form-check-input glass"></th>
                                            <th><?php echo trans('doc_name'); ?></th>
                                            <th><?php echo trans('category'); ?></th>
                                            <th><?php echo trans('added_on'); ?></th>
                                            <th class="text-end"><?php echo trans('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="documentTableBody">
                                        <!-- Table Rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="gridView" class="file-grid d-none">
                            <!-- File Cards -->
                        </div>
                    </div>
                </div>

                <!-- Credit Manager View (Hidden by default) -->
                <div id="creditView" class="d-none">
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <div class="glass p-4 border-start border-4 border-primary shadow-sm hover-lift-sm">
                                <p class="text-white-50 small mb-1">Total Clients</p>
                                <h2 class="stat-value mb-0" id="totalClients">0</h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="glass p-4 border-start border-4 border-danger shadow-sm hover-lift-sm">
                                <p class="text-white-50 small mb-1">Salaf (Credit Out)</p>
                                <h2 class="stat-value mb-0" id="totalCreditOut">0.00 MAD</h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="glass p-4 border-start border-4 border-success shadow-sm hover-lift-sm">
                                <p class="text-white-50 small mb-1">Dakhla (Received)</p>
                                <h2 class="stat-value mb-0" id="totalReceived">0.00 MAD</h2>
                            </div>
                        </div>
                    </div>

                    <div class="glass p-0 overflow-hidden fade-in shadow-lg">
                        <div class="p-4 border-bottom border-white border-opacity-5 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                    <i class="bi bi-person-lines-fill text-primary"></i>
                                </div>
                                <h5 class="fw-bold mb-0">Client Ledger</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-white border-white border-opacity-10 rounded-pill px-3 shadow-sm" onclick="exportClientsCSV()">
                                    <i class="bi bi-download me-2"></i> Export CSV
                                </button>
                                <button class="btn btn-primary rounded-pill px-4 shadow-glow" data-bs-toggle="modal" data-bs-target="#addClientModal">
                                    <i class="bi bi-person-plus me-2"></i> Add Client
                                </button>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Client Name</th>
                                            <th>Phone</th>
                                            <th>Total Debt</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="clientTableBody">
                                        <!-- Clients will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Area (Pro Analytics & Activity) -->
            <div class="col-lg-4">
                <!-- Advanced Analytics -->
                <div class="glass p-4 mb-4 fade-in" style="animation-delay: 0.3s;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0"><?php echo trans('upcoming_deadlines'); ?></h6>
                        <span class="badge bg-danger bg-opacity-10 text-danger small border border-danger border-opacity-10">Urgent</span>
                    </div>
                    
                    <!-- Modern Calendar + Deadlines Widget -->
                    <div class="calendar-widget mb-4">
                        <div class="calendar-month-nav">
                            <h6 class="calendar-month-title mb-0" id="calendarMonth"><?php echo date('F Y'); ?></h6>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-link text-white-50 p-0" id="prevMonth"><i class="bi bi-chevron-left"></i></button>
                                <button class="btn btn-sm btn-link text-white-50 p-0" id="nextMonth"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="calendar-grid">
                            <div class="calendar-day-head">S</div>
                            <div class="calendar-day-head">M</div>
                            <div class="calendar-day-head">T</div>
                            <div class="calendar-day-head">W</div>
                            <div class="calendar-day-head">T</div>
                            <div class="calendar-day-head">F</div>
                            <div class="calendar-day-head">S</div>
                        </div>
                        <div id="calendarDays" class="calendar-grid mt-2">
                            <!-- Days will be injected by JS -->
                        </div>
                    </div>

                    <!-- Deadlines List -->
                    <div id="deadlinesList" class="mb-4">
                        <!-- Skeleton loader placeholder -->
                        <div class="skeleton-rect w-100 mb-2" style="height: 50px;"></div>
                        <div class="skeleton-rect w-100 mb-2" style="height: 50px;"></div>
                    </div>

                    <hr class="border-white border-opacity-5 mb-4">

                    <!-- AI Smart Recommendations -->
                    <div class="glass p-3 rounded-4 shadow-pro mb-4 fade-in ai-recommendation-pulse" style="background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.1);">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-lightbulb-fill text-warning"></i>
                            <h6 class="fw-bold mb-0" style="font-size: 0.85rem;">AI Smart Recommendations</h6>
                        </div>
                        <div id="aiRecommendations">
                            <div class="d-flex align-items-start gap-3 p-2 rounded-3">
                                <i class="bi bi-robot text-primary small mt-1"></i>
                                <p class="small mb-0 text-white-50" id="aiRecText" style="font-size: 0.75rem;">Analyzing your patterns...</p>
                            </div>
                        </div>
                    </div>

                    <!-- AI Insights -->
                    <div class="ai-insights-box glass p-3 rounded-4 border-0">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                <i class="bi bi-magic text-primary"></i>
                            </div>
                            <h6 class="fw-bold mb-0"><?php echo trans('ai_insights'); ?></h6>
                        </div>
                        <div id="aiInsightsContent">
                            <div class="mb-3">
                                <p class="small text-white-50 mb-1"><?php echo trans('most_uploaded'); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold" id="topCategory">...</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary" id="topCategoryCount">0</span>
                                </div>
                            </div>
                            <div>
                                <p class="small text-white-50 mb-1"><?php echo trans('busy_day'); ?></p>
                                <span class="fw-bold" id="busiestDay">...</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Activity Analytics & Heatmap -->
                <div class="glass p-4 mb-4 fade-in" style="animation-delay: 0.35s;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><?php echo trans('upload_activity'); ?></h6>
                        <span class="text-white-50 tiny">Last 30 Days</span>
                    </div>
                    <div style="height: 160px;" class="mb-4">
                        <canvas id="uploadTrendChart"></canvas>
                    </div>
                    <div class="border-top border-white border-opacity-5 pt-3">
                        <h6 class="fw-bold mb-2 small text-uppercase opacity-50 letter-spacing-1">Usage Heatmap</h6>
                        <div id="activityHeatmap" class="d-flex flex-wrap gap-1">
                            <!-- Heatmap cells will be injected -->
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="glass p-4 fade-in" style="animation-delay: 0.4s;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">Activity Timeline</h6>
                        <button class="btn btn-link btn-sm text-primary p-0 text-decoration-none small">View All</button>
                    </div>
                    <div id="activityTimeline">
                        <div class="text-center py-4 text-white-50">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><?php echo trans('upload_new'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadForm">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold mb-2">
                                <i class="bi bi-fonts me-1"></i> <?php echo trans('title'); ?>
                            </label>
                            <input type="text" name="title" class="form-control glass border-0 py-2 px-3 rounded-3" placeholder="Enter document title" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold mb-2">
                                <i class="bi bi-tag me-1"></i> <?php echo trans('category'); ?>
                            </label>
                            <select name="category" class="form-select glass border-0 py-2 px-3 rounded-3">
                                <option value="Facture"><?php echo trans('invoices'); ?></option>
                                <option value="Image"><?php echo trans('images'); ?></option>
                                <option value="Administratif">Administratif</option>
                                <option value="Personnel">Personnel</option>
                                <option value="Banque">Banque</option>
                                <option value="Clients">Clients</option>
                                <option value="Études">Études</option>
                                <option value="Projets">Projets</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold mb-2">
                                <i class="bi bi-justify-left me-1"></i> <?php echo trans('description'); ?>
                            </label>
                            <textarea name="description" class="form-control glass border-0 py-2 px-3 rounded-3" rows="3" placeholder="Optional description..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold mb-2">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i> <?php echo trans('file'); ?>
                            </label>
                            <input type="file" name="documents[]" class="form-control glass border-0 py-2 px-3 rounded-3" multiple required>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small fw-bold mb-2">
                                    <i class="bi bi-calendar-event me-1"></i> <?php echo trans('expiry_date'); ?>
                                </label>
                                <input type="date" name="expiry_date" class="form-control glass border-0 py-2 px-3 rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small fw-bold mb-2">
                                    <i class="bi bi-tags me-1"></i> <?php echo trans('tags'); ?>
                                </label>
                                <input type="text" name="tags" class="form-control glass border-0 py-2 px-3 rounded-3" placeholder="e.g. 2026, Urgent">
                            </div>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input glass border-0" type="checkbox" name="important" id="importantCheck">
                            <label class="form-check-label text-white-50 small fw-bold" for="importantCheck">
                                <i class="bi bi-star-fill text-warning me-1"></i> Mark as Important
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-glow">
                            <i class="bi bi-cloud-upload me-2"></i> <?php echo trans('upload'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content glass border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 d-flex justify-content-between align-items-center p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="preview-icon-box glass p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i id="previewFileIcon" class="bi bi-file-earmark-text fs-4 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-main" id="previewTitle">Document Preview</h5>
                            <small class="text-muted" id="previewMeta"></small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a id="previewDownloadBtn" href="#" class="btn btn-primary rounded-pill btn-sm px-3 shadow-glow me-2">
                            <i class="bi bi-download me-1"></i> Download
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-0 bg-black bg-opacity-10" id="previewBody" style="height: 75vh; min-height: 500px;">
                    <div class="row g-0 h-100">
                        <!-- Left: Content -->
                        <div class="col-lg-8 border-end border-white border-opacity-5 h-100 overflow-hidden">
                            <div id="previewContent" class="w-100 h-100 d-flex align-items-center justify-content-center">
                                <!-- Content injected by JS -->
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <!-- Right: AI Sidebar -->
                        <div class="col-lg-4 glass h-100 overflow-y-auto p-4 ai-sidebar" style="background: rgba(0,0,0,0.2);">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="bi bi-magic text-primary fs-5"></i>
                                <h6 class="fw-bold mb-0">AI Document Analyzer</h6>
                            </div>
                            
                            <div id="aiAnalyzerLoading" class="text-center py-5 d-none">
                                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                                <p class="small text-white-50">Analyzing content...</p>
                            </div>

                            <div id="aiAnalyzerContent">
                                <div class="mb-4">
                                    <label class="small text-white-50 d-block mb-1">Document Type</label>
                                    <span class="badge bg-primary bg-opacity-10 text-primary" id="aiDocType">...</span>
                                </div>
                                <div class="mb-4">
                                    <label class="small text-white-50 d-block mb-1">Extracted Date</label>
                                    <p class="fw-bold mb-0" id="aiDocDate">...</p>
                                </div>
                                <div class="mb-4">
                                    <label class="small text-white-50 d-block mb-1">Amount / Value</label>
                                    <p class="fw-bold mb-0 text-success" id="aiDocAmount">...</p>
                                </div>
                                <div class="mb-4">
                                    <label class="small text-white-50 d-block mb-1">Smart Summary</label>
                                    <p class="small text-white-75 mb-0" id="aiDocSummary">...</p>
                                </div>
                                <div class="mb-4">
                                    <label class="small text-white-50 d-block mb-1">Keywords</label>
                                    <div class="d-flex flex-wrap gap-1" id="aiDocKeywords">
                                        <!-- Keywords injected by JS -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast glass border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-transparent border-0 text-white">
                <i class="bi bi-bell-fill me-2 text-primary"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content glass border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><?php echo trans('edit_doc'); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm">
                    <input type="hidden" name="id" id="editDocId">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold mb-2">
                                <i class="bi bi-fonts me-1"></i> <?php echo trans('title'); ?>
                            </label>
                            <input type="text" name="title" id="editTitle" class="form-control glass border-0 text-white py-2 px-3 rounded-3" required>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small fw-bold mb-2">
                                    <i class="bi bi-tag me-1"></i> <?php echo trans('category'); ?>
                                </label>
                                <select name="category" id="editCategory" class="form-select glass border-0 text-white py-2 px-3 rounded-3">
                                    <option value="Facture"><?php echo trans('facture'); ?></option>
                                    <option value="Image"><?php echo trans('image'); ?></option>
                                    <option value="Administratif"><?php echo trans('admin'); ?></option>
                                    <option value="Personnel"><?php echo trans('personnel'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small fw-bold mb-2">
                                    <i class="bi bi-calendar-event me-1"></i> <?php echo trans('expiry_date'); ?>
                                </label>
                                <input type="date" name="expiry_date" id="editExpiryDate" class="form-control glass border-0 text-white py-2 px-3 rounded-3">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold mb-2">
                                <i class="bi bi-tags me-1"></i> <?php echo trans('tags'); ?>
                            </label>
                            <input type="text" name="tags" id="editTags" class="form-control glass border-0 text-white py-2 px-3 rounded-3" placeholder="comma separated...">
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold mb-2">
                                <i class="bi bi-justify-left me-1"></i> <?php echo trans('description'); ?>
                            </label>
                            <textarea name="description" id="editDescription" class="form-control glass border-0 text-white py-2 px-3 rounded-3" rows="3"></textarea>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input glass border-0" type="checkbox" name="important" id="editImportantCheck">
                            <label class="form-check-label text-white-50 small fw-bold" for="editImportantCheck">
                                <i class="bi bi-star-fill text-warning me-1"></i> Mark as Important
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-glow">
                            <i class="bi bi-check2-circle me-2"></i> <?php echo trans('save_changes'); ?>
                        </button>
                    </div>
                </form>
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
                        <button class="btn btn-primary w-100 rounded-pill py-2" onclick="show2FAVerification()">
                            I've scanned it <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>

                    <div id="twoFactorStep2" class="d-none">
                        <label class="form-label text-white-50 small fw-bold mb-3">Enter Verification Code</label>
                        <div class="d-flex justify-content-center gap-2 mb-4">
                            <input type="text" maxlength="6" class="form-control glass border-0 text-center fw-bold fs-4 rounded-3" style="width: 150px; letter-spacing: 5px;" placeholder="000000" id="twoFactorCode">
                        </div>
                        <button class="btn btn-success w-100 rounded-pill py-2" id="verify2FABtn">
                            Verify & Enable
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
                <form id="addClientForm">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold mb-2">Name</label>
                            <input type="text" name="name" class="form-control glass border-0" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold mb-2">Phone</label>
                            <input type="text" name="phone" class="form-control glass border-0">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-glow">Create Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Credit/Payment Modal -->
    <div class="modal fade" id="addCreditModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Record Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addCreditForm">
                    <input type="hidden" name="client_id" id="creditClientId">
                    <div class="modal-body p-4">
                        <h6 class="text-primary mb-4" id="creditClientName">Client Name</h6>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold mb-2">Type</label>
                            <select name="type" id="creditType" class="form-select glass border-0" required>
                                <option value="gave">Salaf (Money Out)</option>
                                <option value="received">Dakhla (Money In)</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold mb-2">Amount (MAD)</label>
                            <input type="number" step="0.01" name="amount" id="creditAmount" class="form-control glass border-0" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold mb-2">Description</label>
                            <textarea name="description" class="form-control glass border-0" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-glow">Save Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Scanner Modal -->
    <div class="modal fade" id="scannerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-white"><i class="bi bi-camera me-2"></i> <?php echo trans('scanner'); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="scannerDropZone" class="glass border-dashed p-4 text-center mb-4 cursor-pointer hover-lift">
                        <i class="bi bi-camera fs-2 text-primary mb-2 d-block"></i>
                        <p class="fw-bold mb-1"><?php echo trans('scanner_drop_title'); ?></p>
                        <p class="text-white-50 small mb-0"><?php echo trans('scanner_drop_subtitle'); ?></p>
                        <input type="file" id="scannerInput" class="d-none" accept="image/*" multiple capture="environment">
                    </div>

                    <div id="scannerPreview" class="row g-3 mb-4 explorer-grid" style="max-height: 300px; overflow-y: auto;">
                        <!-- Image previews go here -->
                    </div>

                    <div id="scannerActions" class="d-none">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold"><?php echo trans('pdf_title_label'); ?></label>
                            <input type="text" id="scannerPdfTitle" class="form-control glass border-0" placeholder="Scan_<?php echo date('Ymd_His'); ?>">
                        </div>
                        <button class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-glow" id="generatePdfBtn">
                            <i class="bi bi-file-earmark-pdf me-2"></i> <?php echo trans('gen_upload_pdf'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Ledger Modal -->
    <div class="modal fade" id="clientLedgerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                            <i class="bi bi-person-lines-fill text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold" id="lexerClientName">Client Ledger</h5>
                            <p class="text-white-50 small mb-0">Detailed transaction history</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="text-white-50 small text-uppercase fw-bold letter-spacing-1">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody id="ledgerTableBody">
                                <!-- Transactions will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart AI Assistant (SmartBot) -->
    <div class="ai-assistant-wrapper">
        <div id="aiChatWindow" class="ai-chat-window glass rounded-4 shadow-lg d-none fade-in">
            <div class="ai-chat-header d-flex justify-content-between align-items-center p-3 border-bottom border-white border-opacity-10">
                <div class="d-flex align-items-center gap-2">
                    <div class="ai-avatar-small bg-primary rounded-circle shadow-glow pulse">
                        <i class="bi bi-robot text-white"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-white">SmartBot</h6>
                </div>
                <button type="button" class="btn-close btn-close-white btn-sm" onclick="toggleAiChat()"></button>
            </div>
            <div id="aiChatMessages" class="ai-chat-messages p-3">
                <div class="ai-message bot">
                    <div class="message-content glass">
                        Mar7ba! 👋 Ana SmartBot. T9dar t'sewelni 3la ay 7aja f les documents dyalk.
                    </div>
                </div>
            </div>
            <div class="ai-chat-footer p-3 border-top border-white border-opacity-10">
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="aiChatInput" class="form-control glass border-0 py-2" placeholder="Sewelni..." onkeypress="handleAiKeyPress(event)">
                    <button class="btn btn-primary rounded-circle p-2 shadow-glow flex-shrink-0" style="width: 40px; height: 40px;" onclick="sendAiMessage()">
                        <i class="bi bi-send-fill" style="margin-left: 2px;"></i>
                    </button>
                </div>
            </div>
        </div>
        <button id="aiChatToggle" class="ai-chat-toggle btn btn-primary rounded-circle shadow-glow pulse" onclick="toggleAiChat()">
            <i class="bi bi-robot fs-4"></i>
        </button>
    </div>

    <!-- Floating Action Button -->
    <div class="fab pulse" onclick="document.getElementById('fileInputHidden').click()">
        <i class="bi bi-plus-lg"></i>
    </div>

    <!-- Keyboard Shortcuts Modal -->
    <div class="modal fade" id="shortcutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass border-0 shadow-lg p-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-keyboard me-2 text-primary"></i> Keyboard Shortcuts</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm text-white-50 align-middle">
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-white bg-opacity-10 py-1 px-2 border border-white border-opacity-10 fw-normal">/</span></td>
                                    <td>Focus Search bar</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-white bg-opacity-10 py-1 px-2 border border-white border-opacity-10 fw-normal">n</span></td>
                                    <td>Quick Upload</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-white bg-opacity-10 py-1 px-2 border border-white border-opacity-10 fw-normal">l</span></td>
                                    <td>Open Credit Ledger</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-white bg-opacity-10 py-1 px-2 border border-white border-opacity-10 fw-normal">k</span></td>
                                    <td>Show this help</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-white bg-opacity-10 py-1 px-2 border border-white border-opacity-10 fw-normal">Esc</span></td>
                                    <td>Close all Modals</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-filter-right me-2 text-primary"></i> Advanced Filters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="small text-white-50 mb-2">From Date</label>
                            <input type="date" id="filterDateStart" class="form-control glass border-0">
                        </div>
                        <div class="col-6">
                            <label class="small text-white-50 mb-2">To Date</label>
                            <input type="date" id="filterDateEnd" class="form-control glass border-0">
                        </div>
                        <div class="col-12">
                            <label class="small text-white-50 mb-2">Min File Size (KB)</label>
                            <input type="number" id="filterMinSize" class="form-control glass border-0" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-link text-white-50 text-decoration-none" onclick="resetAdvancedFilters()">Reset</button>
                    <button class="btn btn-primary rounded-pill px-4" onclick="applyAdvancedFilters()" data-bs-dismiss="modal">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Summary Modal -->
    <div class="modal fade" id="aiSummaryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-robot me-2 text-primary"></i> AI Document Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="aiSummaryContent" class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); min-height: 200px; white-space: pre-line; font-size: 0.9rem; line-height: 1.7;">
                        <div class="text-center py-5 text-white-50">
                            <div class="spinner-border spinner-border-sm me-2"></div> Generating AI Summary...
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-sm btn-outline-light rounded-pill" onclick="copyAISummary()"><i class="bi bi-clipboard me-1"></i> Copy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Link Modal -->
    <div class="modal fade" id="shareLinkModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-share me-2 text-success"></i> Share Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="shareDocId">
                    <div class="mb-3">
                        <label class="small text-white-50 mb-2">Link Expiry</label>
                        <select id="shareExpiry" class="form-select glass border-0">
                            <option value="24">24 hours</option>
                            <option value="72" selected>3 days</option>
                            <option value="168">1 week</option>
                            <option value="720">30 days</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="small text-white-50 mb-2">Password Protection (optional)</label>
                        <input type="password" id="sharePassword" class="form-control glass border-0" placeholder="Leave empty for no password">
                    </div>
                    <div id="shareResult" class="d-none mt-3 p-3 rounded-3" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2);">
                        <small class="text-success d-block mb-2"><i class="bi bi-check-circle me-1"></i> Link created!</small>
                        <div class="input-group">
                            <input type="text" id="shareUrlOutput" class="form-control glass border-0" readonly style="font-size: 0.8rem;">
                            <button class="btn btn-success btn-sm" onclick="copyShareUrl()"><i class="bi bi-clipboard"></i></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-success rounded-pill px-4" onclick="generateShareLink()"><i class="bi bi-link-45deg me-1"></i> Generate Link</button>
                </div>
            </div>
        </div>
    </div>

    <!-- OCR Result Modal -->
    <div class="modal fade" id="ocrModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2 text-info"></i> OCR Text Extraction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="ocrContent" class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); min-height: 200px; white-space: pre-line; font-size: 0.85rem;">
                        <div class="text-center py-5 text-white-50">
                            <div class="spinner-border spinner-border-sm me-2"></div> Extracting text...
                        </div>
                    </div>
                    <div id="ocrMeta" class="mt-3 text-white-50 small"></div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-sm btn-outline-light rounded-pill" onclick="copyOCRText()"><i class="bi bi-clipboard me-1"></i> Copy Text</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus me-2 text-primary"></i> New Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-white-50 mb-2">Folder Name</label>
                        <input type="text" id="folderNameInput" class="form-control glass border-0" placeholder="Enter folder name...">
                    </div>
                    <div class="mb-3">
                        <label class="small text-white-50 mb-2">Color</label>
                        <div class="d-flex gap-2" id="folderColorPicker">
                            <div class="color-swatch active" style="background: #6366f1; width: 24px; height: 24px; border-radius: 50%; cursor: pointer;" data-color="#6366f1"></div>
                            <div class="color-swatch" style="background: #ec4899; width: 24px; height: 24px; border-radius: 50%; cursor: pointer;" data-color="#ec4899"></div>
                            <div class="color-swatch" style="background: #10b981; width: 24px; height: 24px; border-radius: 50%; cursor: pointer;" data-color="#10b981"></div>
                            <div class="color-swatch" style="background: #f59e0b; width: 24px; height: 24px; border-radius: 50%; cursor: pointer;" data-color="#f59e0b"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-primary rounded-pill px-4" onclick="createFolder()">Create Folder</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Move to Folder Modal -->
    <div class="modal fade" id="moveFolderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-folder-symlink me-2 text-warning"></i> Move to Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <input type="hidden" id="moveDocId">
                    <div class="list-group list-group-flush glass-list" id="folderListMove" style="max-height: 300px; overflow-y: auto;">
                        <!-- Folders will load here -->
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-outline-light rounded-pill px-4" onclick="moveToFolder(null)">Move to Root</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script>
        // Export translations to JS
        window.translations = {
            toast_pdf_success: "<?php echo trans('toast_pdf_success'); ?>",
            toast_client_success: "<?php echo trans('toast_client_success'); ?>",
            toast_trans_success: "<?php echo trans('toast_trans_success'); ?>",
            err_pdf_fail: "<?php echo trans('err_pdf_fail'); ?>",
            err_ledger_load: "<?php echo trans('err_ledger_load'); ?>"
        };

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('service-worker.js')
                    .then(reg => console.log('Service Worker registered'))
                    .catch(err => console.log('Service Worker registration failed', err));
            });
        }
    </script>
</body>
</html>
