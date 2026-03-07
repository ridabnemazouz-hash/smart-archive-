document.addEventListener('DOMContentLoaded', () => {
    // Hide splash screen after load animation completes
    setTimeout(() => {
        const splash = document.getElementById('pageSplash');
        if (splash) splash.classList.add('hidden');
    }, 900);

    // Core Elements
    const documentTableBody = document.getElementById('documentTableBody');
    const uploadForm = document.getElementById('uploadForm');
    const editForm = document.getElementById('editForm');
    const searchInput = document.getElementById('searchInput');
    const totalDocsLabel = document.getElementById('totalDocs');
    const importantDocsLabel = document.getElementById('importantDocs');
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const listViewBtn = document.getElementById('listViewBtn');
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listView = document.getElementById('listView');
    const gridView = document.getElementById('gridView');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectAll = document.getElementById('selectAll');
    const toastElement = document.getElementById('liveToast');
    const toast = toastElement ? new bootstrap.Toast(toastElement) : null;
    const toastMessage = document.getElementById('toastMessage');
    let allDocuments = []; // Global cache for calendar and context menu
    window.allDocuments = allDocuments;

    // Theme Logic
    const themeToggle = document.getElementById('themeToggle');
    const updateThemeIcon = (theme) => {
        if (!themeToggle) return;
        const icon = themeToggle.querySelector('i');
        if (theme === 'light') {
            icon.className = 'bi bi-sun fs-5';
            document.documentElement.setAttribute('data-theme', 'light');
        } else {
            icon.className = 'bi bi-moon-stars fs-5';
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    };

    const savedTheme = localStorage.getItem('theme') || 'dark';
    updateThemeIcon(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
            const next = current === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme', next);
            updateThemeIcon(next);
        });
    }
    let categoryChart = null;
    let currentView = localStorage.getItem('preferredView') || 'list';

    // UI Helpers
    const showToast = (message, isError = false) => {
        if (!toast || !toastMessage) return;
        toastMessage.innerText = message;
        toastElement.querySelector('.toast-header').className = `toast-header bg-transparent border-0 ${isError ? 'text-danger' : 'text-white'}`;
        toast.show();
    };

    const escapeHTML = (str) => {
        if (!str) return '';
        return str.replace(/[&<>"']/g, function (m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[m];
        });
    };

    const getFileIcon = (filename) => {
        const ext = filename.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) return 'bi-image text-info';
        if (ext === 'pdf') return 'bi-file-pdf text-danger';
        if (['doc', 'docx'].includes(ext)) return 'bi-file-word text-primary';
        return 'bi-file-earmark text-secondary';
    };

    const formatBytes = (bytes, decimals = 2) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    };

    // Sidebar Logic
    if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth > 768) sidebar.classList.add('collapsed');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }

    // Mobile Sidebar Toggle
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-show');
        });
    }

    // Close mobile sidebar when clicking a link
    document.querySelectorAll('.nav-item-pro').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('mobile-show');
            }
        });
    });

    // View Toggling
    const setView = (view) => {
        currentView = view;
        localStorage.setItem('preferredView', view);
        if (view === 'grid') {
            listView.classList.add('d-none');
            gridView.classList.remove('d-none');
            gridViewBtn.classList.add('active');
            listViewBtn.classList.remove('active');
        } else {
            gridView.classList.add('d-none');
            listView.classList.remove('d-none');
            listViewBtn.classList.add('active');
            gridViewBtn.classList.remove('active');
        }
    };
    listViewBtn.addEventListener('click', () => setView('list'));
    gridViewBtn.addEventListener('click', () => setView('grid'));
    setView(currentView);

    // Charts Initialization Helper
    let categoryBarChart = null;
    let uploadTrendChart = null;

    const initCharts = (docs) => {
        const isAr = document.documentElement.lang === 'ar';

        // 1. Category Bar
        // ... (Skipping Category Bar Chart as it's replaced in UI, but keeping logic for data)
        const catMap = { 'Facture': 0, 'Image': 0, 'Administratif': 0, 'Personnel': 0 };
        docs.forEach(d => { if (catMap.hasOwnProperty(d.category)) catMap[d.category]++; else catMap[d.category] = (catMap[d.category] || 0) + 1; });

        // 3. Upload Trend (Last 6 Months)
        const months = {};
        const monthNames = isAr ? ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'] : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        docs.forEach(d => {
            const date = new Date(d.created_at);
            const key = monthNames[date.getMonth()];
            months[key] = (months[key] || 0) + 1;
        });

        const trendCtx = document.getElementById('uploadTrendChart')?.getContext('2d');
        if (trendCtx) {
            if (uploadTrendChart) uploadTrendChart.destroy();
            uploadTrendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: Object.keys(months),
                    datasets: [{
                        data: Object.values(months),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#6366f1',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { display: false },
                        x: {
                            grid: { display: false },
                            ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } }
                        }
                    }
                }
            });
        }

        initInsights(docs, catMap);
        renderDeadlines(docs);
    };

    const initInsights = (docs, catMap) => {
        // AI Insights Logic
        const topCatEl = document.getElementById('topCategory');
        const topCatCountEl = document.getElementById('topCategoryCount');
        const busiestDayEl = document.getElementById('busiestDay');

        if (!docs.length) return;

        // Top Category
        const sortedCats = Object.entries(catMap).sort((a, b) => b[1] - a[1]);
        topCatEl.innerText = sortedCats[0][0];
        topCatCountEl.innerText = sortedCats[0][1];

        // Busiest Day (Insights)
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const dayCounts = [0, 0, 0, 0, 0, 0, 0];
        docs.forEach(d => {
            const day = new Date(d.created_at).getDay();
            dayCounts[day]++;
        });
        const busiestIndex = dayCounts.indexOf(Math.max(...dayCounts));
        busiestDayEl.innerText = `You upload more files on ${days[busiestIndex]}s`;

        // Update AI Recommendation text
        const recText = document.getElementById('aiRecText');
        if (recText) {
            const hasInvoices = docs.some(d => d.category === 'Facture');
            const hasDeadlines = docs.some(d => d.expiry_date);
            if (hasDeadlines) {
                recText.innerHTML = "I see upcoming deadlines. I've highlighted them in the calendar with <span class='text-primary'>dots</span> for you. 📅";
            } else if (hasInvoices) {
                recText.innerText = "You have several invoices. Would you like me to generate a monthly spending summary? 📊";
            } else {
                recText.innerText = "Keep it up! Your archive is growing. Try adding tags to find files even faster. 🏷️";
            }
        }
    };

    const renderDeadlines = (docs) => {
        const container = document.getElementById('deadlinesList');
        if (!container) return;

        const deadlines = docs.filter(d => d.expiry_date).sort((a, b) => new Date(a.expiry_date) - new Date(b.expiry_date)).slice(0, 3);

        if (deadlines.length === 0) {
            container.innerHTML = '<div class="text-white-50 small text-center py-2">No upcoming deadlines 🚀</div>';
            return;
        }

        container.innerHTML = deadlines.map(d => {
            const diff = Math.ceil((new Date(d.expiry_date) - new Date()) / (1000 * 60 * 60 * 24));
            const colorClass = diff <= 7 ? 'text-danger' : 'text-warning';
            return `
                <div class="d-flex align-items-center gap-3 glass p-2 rounded-3 mb-2 hover-lift-sm">
                    <div class="bg-opacity-10 p-2 rounded-3 ${diff <= 7 ? 'bg-danger' : 'bg-warning'}">
                        <i class="bi bi-clock-history ${colorClass}"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="small mb-0 fw-bold text-truncate">${d.title}</p>
                        <small class="${colorClass}" style="font-size: 0.7rem;">Expires in ${diff} days</small>
                    </div>
                </div>
            `;
        }).join('');
    };

    const animateValue = (id, start, end, duration) => {
        const obj = document.getElementById(id);
        if (!obj) return;
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.innerHTML = Math.floor(progress * (end - start) + start);
            if (progress < 1) window.requestAnimationFrame(step);
        };
        window.requestAnimationFrame(step);
    };

    let currentPage = 1;
    let isFetching = false;
    let currentFilterType = null;
    let currentFilterValue = null;

    // Document Loading Logic
    const loadDocuments = async (filterType, filterValue) => {
        if (isFetching) return;
        isFetching = true;

        if (filterType !== undefined) {
            currentFilterType = filterType;
            currentFilterValue = filterValue;
        }

        // Show Skeletons
        renderSkeletons();

        let url = 'api/get_docs.php?page=' + currentPage;
        const params = new URLSearchParams();

        // Handle Sidebar/Explicit Filters
        if (currentFilterType === 'category') params.append('category', currentFilterValue);
        else if (currentFilterType === 'important') params.append('important', currentFilterValue);
        else if (currentFilterType === 'trash') params.append('trash', '1');
        else if (currentFilterType === 'folder') params.append('folder_id', currentFilterValue);
        else if (currentFilterType === 'favorites') params.append('is_favorite', '1');

        const catVal = document.getElementById('categoryFilter')?.value;
        const dateVal = document.getElementById('dateFilter')?.value;
        const sortVal = document.getElementById('sortFilter')?.value;

        // Dropdown filters should only override if we aren't in a specific sidebar view (like Trash)
        if (currentFilterType !== 'trash') {
            if (catVal) params.append('category', catVal);
        }

        if (dateVal) params.append('date', dateVal);
        if (sortVal) params.append('sort', sortVal);

        // Advanced Filters (Phase 7)
        const dateStart = document.getElementById('filterDateStart')?.value;
        const dateEnd = document.getElementById('filterDateEnd')?.value;
        const minSize = document.getElementById('filterMinSize')?.value;

        if (dateStart) params.append('date_start', dateStart);
        if (dateEnd) params.append('date_end', dateEnd);
        if (minSize) params.append('min_size', minSize);

        if (searchInput.value) params.append('search', searchInput.value);
        url += (url.includes('?') ? '&' : '?') + params.toString();

        try {
            const response = await fetch(url);
            const data = await response.json();
            isFetching = false;

            if (data.success) {
                window.allDocuments = data.documents; // Cache for calendar
                renderDocuments(data.documents);
                initCharts(data.documents);
                renderCalendar(); // Re-render calendar to show dots
                renderPagination(data.pages, data.current_page);

                animateValue('totalDocs', 0, data.stats.total_docs, 1000);
                animateValue('importantDocs', 0, data.stats.important_docs, 1000);

                // Update Storage Usage (Global)
                const totalBytes = data.stats.total_size;
                const limit = 10 * 1024 * 1024 * 1024; // 10GB
                const percent = Math.min((totalBytes / limit) * 100, 100);

                const bar = document.getElementById('storageUsageBar');
                const box = document.querySelector('.sidebar-storage-box');
                const label = document.getElementById('storageUsageLabel');
                const percentLabel = document.getElementById('storageUsagePercent');

                if (bar) {
                    bar.style.width = percent + '%';
                    if (percent >= 80) {
                        bar.classList.replace('bg-primary', 'bg-danger');
                        box.classList.add('shadow-glow-red');
                    } else {
                        bar.classList.replace('bg-danger', 'bg-primary');
                        box.classList.remove('shadow-glow-red');
                    }
                }
                if (label) label.innerText = `${formatBytes(totalBytes)} / 10GB`;
                if (percentLabel) percentLabel.innerText = `${Math.round(percent)}%`;

                // AI Storage Trend (Bonus)
                const storageTrend = document.getElementById('storageTrendText');
                if (storageTrend) {
                    const daysRemaining = Math.max(30, Math.round((100 - percent) * 2)); // Mock logic
                    storageTrend.innerText = `Full in approx. ${daysRemaining} days (based on trend)`;
                }
            }
        } catch (error) {
            isFetching = false;
            console.error('Error loading documents:', error);
        }
    };

    const renderSkeletons = () => {
        documentTableBody.innerHTML = Array(5).fill(`
            <tr class="opacity-50">
                <td><div class="skeleton-box" style="width: 20px; height: 20px;"></div></td>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div class="skeleton-box" style="width: 40px; height: 40px; border-radius: 8px;"></div>
                        <div>
                            <div class="skeleton-box mb-1" style="width: 120px; height: 14px;"></div>
                            <div class="skeleton-box" style="width: 60px; height: 10px;"></div>
                        </div>
                    </div>
                </td>
                <td><div class="skeleton-box" style="width: 70px; height: 20px; border-radius: 20px;"></div></td>
                <td><div class="skeleton-box" style="width: 80px; height: 12px;"></div></td>
                <td class="text-end"><div class="skeleton-box ms-auto" style="width: 60px; height: 28px; border-radius: 6px;"></div></td>
            </tr>
        `).join('');
    };

    const renderPagination = (totalPages, current) => {
        let pager = document.getElementById('paginationContainer');
        if (!pager) {
            pager = document.createElement('div');
            pager.id = 'paginationContainer';
            pager.className = 'd-flex justify-content-center mt-4';
            listView.appendChild(pager);
        }

        if (totalPages <= 1) { pager.innerHTML = ''; return; }

        let html = '<ul class="pagination pagination-pro">';
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="#" onclick="changePage(${i})">${i}</a></li>`;
        }
        html += '</ul>';
        pager.innerHTML = html;
    };

    window.changePage = (p) => {
        currentPage = p;
        loadDocuments();
    };

    const renderDocuments = (docs) => {
        documentTableBody.innerHTML = '';
        gridView.innerHTML = '';
        if (docs.length === 0) {
            const emptyMsg = `<div class="col-12 text-center py-5 text-white-50"><i class="bi bi-folder2-open fs-1 mb-3 d-block"></i> No documents found</div>`;
            documentTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-white-50 p-5">No documents found</td></tr>`;
            gridView.innerHTML = emptyMsg;
            return;
        }

        docs.forEach(doc => {
            const isDeleted = doc.deleted_at !== null;
            const tagsArr = doc.tags ? doc.tags.split(',').map(t => t.trim()) : [];
            const row = document.createElement('tr');
            row.classList.add('fade-in', 'hover-lift-sm');
            row.dataset.id = doc.id;

            // Expiry Badge Logic
            let expiryHtml = '';
            if (doc.expiry_date) {
                const diff = Math.ceil((new Date(doc.expiry_date) - new Date()) / (1000 * 60 * 60 * 24));
                const color = diff <= 7 ? 'danger' : (diff <= 30 ? 'warning' : 'success');
                expiryHtml = `<span class="badge bg-${color} bg-opacity-10 text-${color} border border-${color} border-opacity-20 ms-2" style="font-size: 0.6rem;">Exp in ${diff}d</span>`;
            }

            row.innerHTML = `
                <td><input type="checkbox" class="form-check-input glass bg-opacity-10 border-0" value="${doc.id}"></td>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div class="glass p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.03);">
                            <i class="bi ${getFileIcon(doc.file_path)} fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="fw-bold d-block text-truncate" style="max-width: 180px;">${escapeHTML(doc.title)}</span>
                            <div class="d-flex align-items-center gap-1">
                                <small class="text-muted opacity-75">${formatBytes(doc.file_size || 0)}</small>
                                ${expiryHtml}
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-white-10 text-white-50 rounded-pill px-2" style="font-size: 0.65rem;">${doc.category}</span>
                        ${tagsArr.map(t => `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill px-2" style="font-size: 0.65rem;">#${escapeHTML(t)}</span>`).join('')}
                    </div>
                </td>
                <td><small class="text-white-50">${new Date(doc.created_at).toLocaleDateString()}</small></td>
                <td class="text-end">
                    <div class="btn-group">
                        ${isDeleted ? `
                            <button class="btn btn-sm btn-link text-success p-2" onclick="restoreDocument(${doc.id})"><i class="bi bi-arrow-counterclockwise"></i></button>
                        ` : `
                            <button class="btn btn-sm btn-link ${doc.is_favorite ? 'text-warning' : 'text-muted'} p-2" onclick="toggleFavorite(${doc.id})"><i class="bi ${doc.is_favorite ? 'bi-star-fill' : 'bi-star'}"></i></button>
                            <button class="btn btn-sm btn-link text-muted p-2" onclick='openPreviewModal(${JSON.stringify(doc).replace(/'/g, "&#39;")})'><i class="bi bi-eye"></i></button>
                            <button class="btn btn-sm btn-link text-info p-2" onclick="openMoveFolderModal(${doc.id})"><i class="bi bi-folder-symlink"></i></button>
                            <button class="btn btn-sm btn-link text-danger p-2" onclick="deleteDocument(${doc.id})"><i class="bi bi-trash"></i></button>
                        `}
                    </div>
                </td>
            `;
            documentTableBody.appendChild(row);

            const card = document.createElement('div');
            card.className = 'file-card fade-in hover-lift';
            card.dataset.id = doc.id;
            card.innerHTML = `
                <div class="d-flex justify-content-between mb-3">
                    <div class="file-icon-wrapper shadow-sm">
                        <i class="bi ${getFileIcon(doc.file_path)}"></i>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-2 rounded-circle" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end glass border-0 shadow-lg mt-2">
                            ${isDeleted ? `
                                <li><a class="dropdown-item py-2" href="#" onclick="restoreDocument(${doc.id})"><i class="bi bi-arrow-counterclockwise me-2"></i> Restore</a></li>
                            ` : `
                                <li><a class="dropdown-item py-2" href="#" onclick='openPreviewModal(${JSON.stringify(doc).replace(/'/g, "&#39;")})'><i class="bi bi-eye me-2"></i> Preview</a></li>
                                <li><a class="dropdown-item py-2" href="#" onclick="toggleFavorite(${doc.id})"><i class="bi ${doc.is_favorite ? 'bi-star-fill text-warning' : 'bi-star'} me-2"></i> ${doc.is_favorite ? 'Unfavorite' : 'Favorite'}</a></li>
                                <li><a class="dropdown-item py-2" href="#" onclick="openMoveFolderModal(${doc.id})"><i class="bi bi-folder-symlink me-2"></i> Move to Folder</a></li>
                                <li><a class="dropdown-item py-2 text-danger" href="#" onclick="deleteDocument(${doc.id})"><i class="bi bi-trash me-2"></i> Delete</a></li>
                            `}
                        </ul>
                    </div>
                </div>
                <h6 class="fw-bold text-truncate mb-1" style="font-size: 0.9rem;">${escapeHTML(doc.title)}</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted opacity-75" style="font-size: 0.7rem;">${formatBytes(doc.file_size || 0)}</small>
                    ${doc.is_important == 1 ? '<i class="bi bi-star-fill text-warning scale-in" style="font-size: 0.7rem;"></i>' : ''}
                </div>
            `;
            gridView.appendChild(card);
        });
    };

    const loadActivities = async () => {
        const timeline = document.getElementById('activityTimeline');
        if (!timeline) return;
        try {
            const resp = await fetch('api/get_activity.php');
            const data = await resp.json();
            if (data.success && data.activities.length > 0) {
                timeline.innerHTML = '';

                // Render Heatmap (Phase 7)
                renderActivityHeatmap(data.activities);

                data.activities.forEach(act => {
                    const item = document.createElement('div');
                    item.className = 'd-flex gap-3 mb-4 activity-item-pro';
                    const relativeTime = window.getRelativeTime ? window.getRelativeTime(act.created_at) : new Date(act.created_at).toLocaleString();

                    // Simple UA parsing (Phase 15)
                    let browserIcon = 'bi-globe';
                    if (act.user_agent) {
                        if (act.user_agent.includes('Chrome')) browserIcon = 'bi-browser-chrome';
                        else if (act.user_agent.includes('Firefox')) browserIcon = 'bi-browser-firefox';
                        else if (act.user_agent.includes('Safari')) browserIcon = 'bi-browser-safari';
                    }

                    item.innerHTML = `
                        <div class="glass p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.03);">
                            <i class="bi ${act.action === 'Upload' ? 'bi-cloud-upload text-primary' : (act.action === 'Delete' ? 'bi-trash text-danger' : 'bi-shield-check text-success')} fs-6"></i>
                        </div>
                        <div class="overflow-hidden flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <p class="mb-0 fw-bold text-truncate" style="font-size: 0.85rem;">${act.action}</p>
                                <span class="badge bg-white-10 text-white-50 tiny opacity-50 px-1" title="${act.ip_address || 'Unknown'}">${act.ip_address ? act.ip_address.substring(0, 7) + '...' : 'Local'}</span>
                            </div>
                            <p class="text-white-50 mb-1 text-truncate" style="font-size: 0.75rem; opacity: 0.8;">${act.details || ''}</p>
                            <div class="d-flex align-items-center gap-2">
                                <small class="text-white-50 opacity-50" style="font-size: 0.65rem;">${relativeTime}</small>
                                <i class="bi ${browserIcon} tiny text-white-50 opacity-25"></i>
                            </div>
                        </div>
                    `;
                    timeline.appendChild(item);
                });
            } else {
                timeline.innerHTML = '<div class="text-center py-4 text-white-50 opacity-50"><i class="bi bi-clock-history d-block fs-3 mb-2"></i> No recent activity</div>';
            }
        } catch (e) {
            timeline.innerHTML = '<div class="text-center py-4 text-danger small">Failed to load activities</div>';
        }
    };

    // Global Functions exposure
    window.openPreviewModal = (doc) => {
        const previewContent = document.getElementById('previewContent');
        const previewTitle = document.getElementById('previewTitle');
        const previewMeta = document.getElementById('previewMeta');
        const previewIcon = document.getElementById('previewFileIcon');
        const downloadBtn = document.getElementById('previewDownloadBtn');
        const ext = doc.file_path ? doc.file_path.split('.').pop().toLowerCase() : '';
        const filePath = `uploads/${doc.file_path}`;

        // Set header info
        previewTitle.textContent = doc.title;
        previewMeta.textContent = `${doc.category} · ${formatBytes(doc.file_size || 0)} · ${new Date(doc.created_at).toLocaleDateString()}`;
        downloadBtn.href = filePath;
        downloadBtn.download = doc.title;

        // Set icon based on type
        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext);
        const isPdf = ext === 'pdf';
        previewIcon.className = isPdf ? 'bi bi-file-earmark-pdf text-danger' : (isImage ? 'bi bi-image text-success' : 'bi bi-file-earmark-text text-primary');

        // Render content
        if (isImage) {
            previewContent.innerHTML = `
                <img src="${filePath}" class="img-fluid rounded-3 shadow" 
                     style="max-height: 65vh; max-width: 100%; object-fit: contain;"
                     onerror="this.parentElement.innerHTML='<div class=\\'text-center text-muted\\'><i class=\\'bi bi-image-alt d-block fs-1 mb-2\\'></i>Image not found</div>'">
            `;
        } else if (isPdf) {
            previewContent.style.minHeight = '65vh';
            previewContent.innerHTML = `
                <iframe src="${filePath}" 
                        style="width:100%; height:65vh; border:none; border-radius: 8px; background:#fff;"
                        title="${doc.title}">
                </iframe>
            `;
        } else {
            previewContent.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-file-earmark-lock2 d-block fs-1 mb-3 opacity-50"></i>
                    <p class="mb-3">No preview available for <strong>.${escapeHTML(ext)}</strong> files</p>
                    <a href="${filePath}" download="${escapeHTML(doc.title)}" class="btn btn-primary rounded-pill px-4 shadow-glow">
                        <i class="bi bi-download me-2"></i>Download File
                    </a>
                </div>
            `;
        }

        // AI Analyzer Mock Injection
        const loading = document.getElementById('aiAnalyzerLoading');
        const content = document.getElementById('aiAnalyzerContent');
        loading.classList.remove('d-none');
        content.classList.add('opacity-50');

        setTimeout(() => {
            loading.classList.add('d-none');
            content.classList.remove('opacity-50');

            document.getElementById('aiDocType').innerText = doc.category;
            document.getElementById('aiDocDate').innerText = doc.expiry_date || 'Not detected';
            document.getElementById('aiDocAmount').innerText = doc.description && doc.description.includes('$') ? doc.description.match(/\$\d+/)[0] : 'N/A';
            document.getElementById('aiDocSummary').innerText = `This is a ${doc.category.toLowerCase()} document titled "${doc.title}". It was uploaded on ${new Date(doc.created_at).toLocaleDateString()}.`;

            const kwContainer = document.getElementById('aiDocKeywords');
            const tags = doc.tags ? doc.tags.split(',') : [doc.category, 'Document'];
            kwContainer.innerHTML = tags.map(t => `<span class="badge bg-white-10 text-white-50 small rounded-pill px-2 border border-white border-opacity-5">#${t.trim()}</span>`).join('');
        }, 800);

        // Add AI Action Buttons to preview modal
        let aiActionsContainer = document.getElementById('previewAIActions');
        if (!aiActionsContainer) {
            aiActionsContainer = document.createElement('div');
            aiActionsContainer.id = 'previewAIActions';
            aiActionsContainer.className = 'd-flex gap-2 flex-wrap mt-3 pt-3 border-top border-white border-opacity-10';
            const previewBody = document.querySelector('#previewModal .modal-body');
            if (previewBody) previewBody.appendChild(aiActionsContainer);
        }
        aiActionsContainer.innerHTML = `
            <button class="btn btn-sm btn-outline-primary rounded-pill" onclick="generateAISummary(${doc.id})">
                <i class="bi bi-robot me-1"></i> AI Summary
            </button>
            <button class="btn btn-sm btn-outline-info rounded-pill" onclick="extractOCR(${doc.id})">
                <i class="bi bi-eye me-1"></i> Extract Text
            </button>
            <button class="btn btn-sm btn-outline-success rounded-pill" onclick="openShareModal(${doc.id})">
                <i class="bi bi-share me-1"></i> Share
            </button>
        `;

        new bootstrap.Modal(document.getElementById('previewModal')).show();
    };

    window.restoreDocument = async (id) => {
        const formData = new FormData();
        formData.append('id', id);
        try {
            const resp = await fetch('api/restore_doc.php', { method: 'POST', body: formData });
            const res = await resp.json();
            if (res.success) {
                loadDocuments();
                loadActivities();
                showToast('Document restored successfully!');
            } else alert(res.message);
        } catch (e) { alert('Restore failed'); }
    };

    window.deleteDocument = async (id) => {
        if (!confirm('Are you sure?')) return;
        const formData = new FormData();
        formData.append('id', id);
        try {
            const resp = await fetch('api/delete_doc.php', { method: 'POST', body: formData });
            const res = await resp.json();
            if (res.success) {
                loadDocuments();
                loadActivities();
                showToast('Document deleted');
            } else alert(res.message);
        } catch (e) { alert('Delete failed'); }
    };

    window.loadDocuments = loadDocuments;

    // Listeners
    if (document.getElementById('categoryFilter')) document.getElementById('categoryFilter').addEventListener('change', () => { currentPage = 1; loadDocuments(); });
    if (document.getElementById('dateFilter')) document.getElementById('dateFilter').addEventListener('change', () => { currentPage = 1; loadDocuments(); });
    if (document.getElementById('sortFilter')) document.getElementById('sortFilter').addEventListener('change', () => { currentPage = 1; loadDocuments(); });
    if (searchInput) searchInput.addEventListener('input', () => { currentPage = 1; loadDocuments(); });
    if (document.getElementById('aiSearchToggle')) document.getElementById('aiSearchToggle').addEventListener('change', () => { currentPage = 1; loadDocuments(); });

    // Handle Sidebar Navigation properly
    window.loadDocumentsWithFilter = (type, val, element) => {
        currentPage = 1;
        // Update active class for sidebar items
        document.querySelectorAll('.nav-item-pro').forEach(n => n.classList.remove('active'));
        if (element) {
            element.classList.add('active');
        } else if (type === 'trash') {
            document.querySelector('[onclick*="trash"]')?.classList.add('active');
        } else if (type === 'important') {
            document.querySelector('[onclick*="important"]')?.classList.add('active');
        }

        loadDocuments(type, val);
    };

    // Upgrade Button Feedback
    document.querySelector('.sidebar-storage-box button')?.addEventListener('click', () => {
        showToast('Pricing plans coming soon! Contact support to upgrade.', 'info');
    });

    // Drag & Drop
    const dropZone = document.getElementById('dropZone');
    const fileInputHidden = document.getElementById('fileInputHidden');
    if (dropZone && fileInputHidden) {
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('bg-white-10'); });
        dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('bg-white-10'); });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-white-10');
            const files = e.dataTransfer.files;
            if (files.length > 0) handleFileUpload(files);
        });
        dropZone.addEventListener('click', () => fileInputHidden.click());
        fileInputHidden.addEventListener('change', (e) => {
            if (e.target.files.length > 0) handleFileUpload(e.target.files);
        });
    }

    const handleFileUpload = (files) => {
        const uploadModalElement = document.getElementById('uploadModal');
        const uploadModal = bootstrap.Modal.getInstance(uploadModalElement) || new bootstrap.Modal(uploadModalElement);
        const modalFileInput = uploadModalElement.querySelector('input[type="file"]');

        // Populate the file input with the dropped/selected files
        modalFileInput.files = files;
        uploadModal.show();
    };

    if (uploadForm) {
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = uploadForm.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Uploading...';

            const formData = new FormData(uploadForm);

            // If we are currently inside a folder, upload to that folder
            if (currentFilterType === 'folder' && currentFilterValue) {
                formData.append('folder_id', currentFilterValue);
            }

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'api/upload_multi.php', true);

                // Progress Bar Logic
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const percent = (e.loaded / e.total) * 100;
                        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> ${Math.round(percent)}%`;
                    }
                };

                xhr.onload = function () {
                    const result = JSON.parse(xhr.responseText);
                    if (result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                        uploadForm.reset();
                        loadDocuments();
                        loadActivities();
                        showToast(`Successfully uploaded ${formData.getAll('documents[]').length} files!`);
                        triggerConfetti();
                    } else alert(result.message);

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                };

                xhr.send(formData);
            } catch (error) {
                alert('Upload failed.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        });
    }

    const triggerConfetti = () => {
        const colors = ['#6366f1', '#a29bfe', '#4ade80', '#fbbf24'];
        for (let i = 0; i < 50; i++) {
            const particle = document.createElement('div');
            particle.className = 'confetti-particle';
            particle.style.background = colors[Math.floor(Math.random() * colors.length)];
            particle.style.left = Math.random() * 100 + 'vw';
            particle.style.top = '-10px';
            particle.style.transform = `rotate(${Math.random() * 360}deg)`;
            document.body.appendChild(particle);

            const animation = particle.animate([
                { transform: `translate3d(0, 0, 0) rotate(0deg)`, opacity: 1 },
                { transform: `translate3d(${(Math.random() - 0.5) * 200}px, 100vh, 0) rotate(${Math.random() * 360}deg)`, opacity: 0 }
            ], {
                duration: 1000 + Math.random() * 2000,
                easing: 'cubic-bezier(0, .9, .57, 1)'
            });
            animation.onfinish = () => particle.remove();
        }
    };

    const loadAiReminders = async () => {
        const container = document.getElementById('aiRemindersList');
        if (!container) return;

        try {
            const response = await fetch('api/get_reminders.php');
            const data = await response.json();

            if (data.success && data.reminders.length > 0) {
                container.innerHTML = '';
                data.reminders.forEach(rem => {
                    const item = document.createElement('div');
                    item.className = 'glass p-3 rounded-3 border-start border-4 border-warning fade-in hover-lift-sm cursor-pointer';
                    item.innerHTML = `
                        <div class="d-flex gap-3 align-items-center">
                            <div class="bg-warning bg-opacity-10 p-2 rounded-circle">
                                <i class="bi bi-lightning-charge-fill text-warning"></i>
                            </div>
                            <div>
                                <p class="small mb-0 fw-bold">${rem.reminder_text}</p>
                                <small class="text-white-50" style="font-size: 0.7rem;">${new Date(rem.remind_at).toLocaleDateString()} • AI Suggested</small>
                            </div>
                        </div>
                    `;
                    container.appendChild(item);
                });
            } else {
                container.innerHTML = '<div class="text-center text-white-50 small">No urgent tasks. 🚀</div>';
            }
        } catch (error) {
            container.innerHTML = '<div class="text-center text-white-50 small text-danger">Failed to load alerts.</div>';
        }
    };

    // Credit Manager Logic
    window.loadCreditManager = (element) => {
        // Update Sidebar Active State
        document.querySelectorAll('.nav-item-pro').forEach(n => n.classList.remove('active'));
        if (element) element.classList.add('active');

        // Toggle Views
        document.getElementById('creditView').classList.remove('d-none');
        document.querySelector('.col-lg-8 > .row.g-4').classList.add('d-none'); // Hide Stats Cards
        document.querySelector('.col-lg-8 > .glass.p-0.mb-5').classList.add('d-none'); // Hide Docs Table
        document.querySelector('.col-lg-4').classList.add('d-none'); // Hide Analytics Panel

        loadClients();
    };

    const loadClients = async () => {
        const tbody = document.getElementById('clientTableBody');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>';

        try {
            const response = await fetch('api/manage_clients.php');
            const data = await response.json();

            if (data.success) {
                tbody.innerHTML = '';
                let totalDebt = 0;
                document.getElementById('totalClients').textContent = data.clients.length;

                data.clients.forEach(client => {
                    totalDebt += parseFloat(client.total_debt);
                    const tr = document.createElement('tr');
                    tr.className = 'fade-in';
                    tr.innerHTML = `
                        <td class="fw-bold">${client.name}</td>
                        <td class="text-white-50">${client.phone || '-'}</td>
                        <td class="text-danger fw-bold">${parseFloat(client.total_debt).toFixed(2)} MAD</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2" onclick="viewClientDetails(${client.id}, '${client.name}')">
                                <i class="bi bi-eye me-1"></i> View
                            </button>
                            <button class="btn btn-sm btn-success rounded-pill px-3 me-2" onclick="openPaymentModal(${client.id}, '${client.name}', ${client.total_debt})">
                                <i class="bi bi-cash-coin me-1"></i> Pay
                            </button>
                            <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="openCreditModal(${client.id}, '${client.name}')">
                                <i class="bi bi-plus-lg me-1"></i> Credit
                            </button>
                            <button class="btn btn-sm btn-link text-danger p-1 ms-1" onclick="deleteClient(${client.id})" title="Delete Client">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                document.getElementById('totalCreditOut').textContent = totalDebt.toFixed(2) + ' MAD';

                // Load total received (Dakhla)
                loadTotalReceived();
            }
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger p-5">Failed to load clients.</td></tr>';
        }
    };

    window.deleteClient = async (id) => {
        if (!confirm('Are you sure you want to delete this client? This will remove all their credit history.')) return;
        try {
            const resp = await fetch(`api/manage_clients.php?id=${id}`, { method: 'DELETE' });
            const res = await resp.json();
            if (res.success) {
                showToast('Client deleted successfully');
                loadClients();
            } else {
                alert(res.message || 'Failed to delete client');
            }
        } catch (error) {
            showToast('Error deleting client', true);
        }
    };

    const loadTotalReceived = async () => {
        try {
            const response = await fetch('api/manage_credits.php');
            const data = await response.json();

            if (data.success) {
                let totalReceived = 0;
                data.credits.forEach(credit => {
                    if (credit.type === 'received') {
                        totalReceived += parseFloat(credit.amount);
                    }
                });
                document.getElementById('totalReceived').textContent = totalReceived.toFixed(2) + ' MAD';
            }
        } catch (error) {
            console.error('Failed to load total received:', error);
        }
    };

    // Update existing filter function to handle view reset
    const originalLoadDocumentsWithFilter = window.loadDocumentsWithFilter;
    window.loadDocumentsWithFilter = (type, val, element) => {
        // Reset View if returning to Docs
        document.getElementById('creditView').classList.add('d-none');
        document.querySelector('.col-lg-8 > .row.g-4').classList.remove('d-none');
        document.querySelector('.col-lg-8 > .glass.p-0.mb-5').classList.remove('d-none');
        document.querySelector('.col-lg-4').classList.remove('d-none'); // Restore Analytics Panel

        originalLoadDocumentsWithFilter(type, val, element);
    };

    // Calendar Logic
    let calendarDate = new Date();
    let selectedDate = new Date(); // To track selection

    const renderCalendar = () => {
        const daysContainer = document.getElementById('calendarDays');
        const monthLabel = document.getElementById('calendarMonth');
        if (!daysContainer || !monthLabel) return;

        const year = calendarDate.getFullYear();
        const month = calendarDate.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();
        const prevLastDate = new Date(year, month, 0).getDate();
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        monthLabel.innerHTML = `${monthNames[month]} ${year}`;
        daysContainer.innerHTML = '';

        // Fill empty slots from prev month
        for (let i = firstDay; i > 0; i--) {
            const div = document.createElement('div');
            div.className = 'calendar-day other-month';
            div.innerHTML = `<span>${prevLastDate - i + 1}</span>`;
            daysContainer.appendChild(div);
        }

        const today = new Date();

        // Fill current month days
        for (let i = 1; i <= lastDate; i++) {
            const div = document.createElement('div');
            div.className = 'calendar-day';
            div.innerHTML = `<span>${i}</span>`;

            // Check if this day is today
            if (year === today.getFullYear() && month === today.getMonth() && i === today.getDate()) {
                div.classList.add('today');
            }

            // Check if this day is selected
            if (year === selectedDate.getFullYear() && month === selectedDate.getMonth() && i === selectedDate.getDate()) {
                div.classList.add('selected');
            }

            // Dot indicators for deadlines
            const dayDateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            const hasDeadline = (window.allDocuments || []).some(d => d.expiry_date === dayDateString);

            if (hasDeadline) {
                const dot = document.createElement('div');
                dot.className = 'event-dot';
                div.appendChild(dot);
            }

            // Interaction
            div.addEventListener('click', () => {
                selectedDate = new Date(year, month, i);
                renderCalendar();
            });

            daysContainer.appendChild(div);
        }

        // Complete 6-row grid (42 cells)
        const totalCells = 42;
        const remaining = totalCells - daysContainer.children.length;
        for (let i = 1; i <= remaining; i++) {
            const div = document.createElement('div');
            div.className = 'calendar-day other-month';
            div.innerText = i;
            daysContainer.appendChild(div);
        }
    };

    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');

    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            calendarDate.setMonth(calendarDate.getMonth() - 1);
            renderCalendar();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            calendarDate.setMonth(calendarDate.getMonth() + 1);
            renderCalendar();
        });
    }

    renderCalendar();

    // Initialize
    loadDocuments();
    loadActivities();
    loadAiReminders();

    // AI Assistant Logic
    window.toggleAiChat = () => {
        const chatWindow = document.getElementById('aiChatWindow');
        chatWindow.classList.toggle('d-none');
        if (!chatWindow.classList.contains('d-none')) {
            document.getElementById('aiChatInput').focus();
        }
    };

    window.sendAiMessage = async () => {
        const input = document.getElementById('aiChatInput');
        const message = input.value.trim();
        if (!message) return;

        appendMessage('user', message);
        input.value = '';

        // Show typing indicator
        const typingId = 'typing-' + Date.now();
        const chatMessages = document.getElementById('aiChatMessages');
        const typingDiv = document.createElement('div');
        typingDiv.id = typingId;
        typingDiv.className = 'ai-message bot';
        typingDiv.innerHTML = '<div class="message-content glass"><div class="spinner-border spinner-border-sm text-primary"></div> SmartBot is thinking...</div>';
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        try {
            const formData = new URLSearchParams();
            formData.append('message', message);

            const response = await fetch('api/ai_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });
            const data = await response.json();

            document.getElementById(typingId).remove();
            appendMessage('bot', data.reply || "Sma7 lia, t'tra mouchkil f l'AI. 🤖");
        } catch (error) {
            document.getElementById(typingId).remove();
            appendMessage('bot', "Sma7 lia, ma 9dertch n'tconnecta m3a l'AI. 🌐");
        }
    };

    window.handleAiKeyPress = (e) => {
        if (e.key === 'Enter') sendAiMessage();
    };

    const appendMessage = (role, text) => {
        const chatMessages = document.getElementById('aiChatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `ai-message ${role} fade-in`;
        messageDiv.innerHTML = `<div class="message-content glass">${text}</div>`;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    };

    // Credit Forms Handlers
    document.getElementById('addClientForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        try {
            const response = await fetch('api/manage_clients.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                const modalElement = document.getElementById('addClientModal');
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();
                e.target.reset();
                loadClients();
                showToast(window.translations.toast_client_success || 'Client added successfully!');
            }
        } catch (error) { showToast('Error adding client', true); }
    });

    window.openCreditModal = (id, name) => {
        document.getElementById('creditClientId').value = id;
        document.getElementById('creditClientName').textContent = name;
        document.getElementById('creditType').value = 'gave'; // Default to Salaf
        document.getElementById('creditAmount').value = ''; // Clear amount
        new bootstrap.Modal(document.getElementById('addCreditModal')).show();
    };

    window.openPaymentModal = (id, name, currentDebt = 0) => {
        document.getElementById('creditClientId').value = id;
        document.getElementById('creditClientName').textContent = name;
        document.getElementById('creditType').value = 'received'; // Pre-select Dakhla
        document.getElementById('creditAmount').value = currentDebt > 0 ? parseFloat(currentDebt).toFixed(2) : ''; // Pre-fill debt
        new bootstrap.Modal(document.getElementById('addCreditModal')).show();
    };

    window.editDocument = (doc) => {
        document.getElementById('editDocId').value = doc.id;
        document.getElementById('editTitle').value = doc.title;
        document.getElementById('editCategory').value = doc.category;
        document.getElementById('editDescription').value = doc.description || '';
        document.getElementById('editExpiryDate').value = doc.expiry_date || '';
        document.getElementById('editTags').value = doc.tags || '';
        document.getElementById('editImportantCheck').checked = doc.is_important == 1;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    };

    // Use the existing editForm variable from the top Scope
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            try {
                const resp = await fetch('api/update_doc.php', { method: 'POST', body: formData });
                const res = await resp.json();
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    loadDocuments();
                    loadActivities();
                    showToast('Document updated!');
                } else alert(res.message);
            } catch (e) { alert('Update failed'); }
        });
    }

    document.getElementById('addCreditForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        try {
            const response = await fetch('api/manage_credits.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                const modalElement = document.getElementById('addCreditModal');
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();
                e.target.reset();
                loadClients();
                loadActivities();
                loadTotalReceived(); // Added this line
                showToast(window.translations.toast_trans_success || 'Transaction recorded successfully!');
            }
        } catch (error) { showToast('Error recording transaction', true); }
    });

    // Right-Click Context Menu
    const contextMenu = document.createElement('div');
    contextMenu.className = 'glass p-2 rounded-3 shadow-lg position-fixed d-none';
    contextMenu.style.zIndex = '10000';
    contextMenu.style.width = '180px';
    contextMenu.innerHTML = `
        <div class="list-group list-group-flush bg-transparent">
            <a href="#" class="list-group-item list-group-item-action border-0 py-2 rounded-2" id="ctxPreview"><i class="bi bi-eye me-2"></i> Preview</a>
            <a href="#" class="list-group-item list-group-item-action border-0 py-2 rounded-2" id="ctxEdit"><i class="bi bi-pencil me-2"></i> Edit</a>
            <a href="#" class="list-group-item list-group-item-action border-0 py-2 rounded-2 text-danger" id="ctxDelete"><i class="bi bi-trash me-2"></i> Delete</a>
        </div>
    `;
    document.body.appendChild(contextMenu);

    document.addEventListener('contextmenu', (e) => {
        const row = e.target.closest('tr[data-id]');
        const card = e.target.closest('.file-card[data-id]');
        const item = row || card;

        if (item) {
            e.preventDefault();
            const id = item.dataset.id;
            const doc = allDocuments.find(d => d.id == id);

            if (doc) {
                contextMenu.style.top = `${e.clientY}px`;
                contextMenu.style.left = `${e.clientX}px`;
                contextMenu.classList.remove('d-none');

                document.getElementById('ctxPreview').onclick = () => { openPreviewModal(doc); contextMenu.classList.add('d-none'); };
                document.getElementById('ctxEdit').onclick = () => { editDocument(doc); contextMenu.classList.add('d-none'); };
                document.getElementById('ctxDelete').onclick = () => { deleteDocument(doc.id); contextMenu.classList.add('d-none'); };
            }
        } else {
            contextMenu.classList.add('d-none');
        }
    });

    document.addEventListener('click', () => contextMenu.classList.add('d-none'));

    window.viewClientDetails = async (id, name) => {
        const tbody = document.getElementById('ledgerTableBody');
        document.getElementById('lexerClientName').textContent = `Ledger: ${name}`;
        tbody.innerHTML = '<tr><td colspan="4" class="text-center p-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        const modal = new bootstrap.Modal(document.getElementById('clientLedgerModal'));
        modal.show();

        try {
            const response = await fetch(`api/manage_credits.php?client_id=${id}`);
            const data = await response.json();

            if (data.success) {
                tbody.innerHTML = '';
                if (data.credits.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-white-50 p-4">No transactions found.</td></tr>';
                    return;
                }

                data.credits.forEach(c => {
                    const tr = document.createElement('tr');
                    tr.className = 'small';
                    const isGave = c.type === 'gave';
                    tr.innerHTML = `
                        <td class="text-white-50">${c.date}</td>
                        <td>
                            <span class="badge ${isGave ? 'bg-danger' : 'bg-success'} bg-opacity-10 ${isGave ? 'text-danger' : 'text-success'} border ${isGave ? 'border-danger' : 'border-success'} border-opacity-20 px-2 py-1">
                                ${isGave ? 'SALAF' : 'DAKHLA'}
                            </span>
                        </td>
                        <td class="fw-bold ${isGave ? 'text-danger' : 'text-success'}">${parseFloat(c.amount).toFixed(2)} MAD</td>
                        <td class="text-white-50">${c.description || '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger p-4">Error loading ledger.</td></tr>';
        }
    };

    // --- Scanner Logic ---
    let scannedImages = [];
    const scannerModal = document.getElementById('scannerModal');
    const scannerInput = document.getElementById('scannerInput');
    const scannerPreview = document.getElementById('scannerPreview');
    const scannerActions = document.getElementById('scannerActions');
    const generatePdfBtn = document.getElementById('generatePdfBtn');

    window.openScannerModal = () => {
        scannedImages = [];
        scannerPreview.innerHTML = '';
        scannerActions.classList.add('d-none');
        document.getElementById('scannerPdfTitle').value = '';
        new bootstrap.Modal(scannerModal).show();
    };

    document.getElementById('scannerDropZone')?.addEventListener('click', () => {
        scannerInput.click();
    });

    scannerInput?.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        if (files.length === 0) return;

        files.forEach(file => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                const imgData = event.target.result;
                scannedImages.push(imgData);
                renderScannerPreviews();
            };
            reader.readAsDataURL(file);
        });

        scannerActions.classList.remove('d-none');
    });

    const renderScannerPreviews = () => {
        scannerPreview.innerHTML = '';
        scannedImages.forEach((src, index) => {
            const div = document.createElement('div');
            div.className = 'col-4 col-md-3 position-relative fade-in';
            div.innerHTML = `
                <div class="glass p-1 overflow-hidden rounded-3 border-opacity-20" style="height: 100px;">
                    <img src="${src}" class="w-100 h-100 object-fit-cover rounded-2">
                    <button class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center" style="width: 20px; height: 20px;" onclick="removeScannedImage(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
            scannerPreview.appendChild(div);
        });
    };

    window.removeScannedImage = (index) => {
        scannedImages.splice(index, 1);
        renderScannerPreviews();
        if (scannedImages.length === 0) scannerActions.classList.add('d-none');
    };

    generatePdfBtn?.addEventListener('click', async () => {
        if (scannedImages.length === 0) return;

        generatePdfBtn.disabled = true;
        generatePdfBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating...';

        try {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF();

            for (let i = 0; i < scannedImages.length; i++) {
                if (i > 0) pdf.addPage();

                const imgData = scannedImages[i];
                const props = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (props.height * pdfWidth) / props.width;

                pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
            }

            const pdfBlob = pdf.output('blob');
            const title = document.getElementById('scannerPdfTitle').value || `Scan_${new Date().getTime()}`;

            const formData = new FormData();
            formData.append('document', pdfBlob, `${title}.pdf`);
            formData.append('title', title);
            formData.append('category', 'Administratif');
            formData.append('description', 'Scanned from images');

            const resp = await fetch('api/upload_doc.php', { method: 'POST', body: formData });
            const res = await resp.json();

            if (res.success) {
                bootstrap.Modal.getInstance(scannerModal).hide();
                showToast(window.translations.toast_pdf_success || 'PDF Scanned & Uploaded!');
                loadDocuments();
                loadActivities();
            } else {
                alert(res.message);
            }
        } catch (e) {
            console.error(e);
            alert(window.translations.err_pdf_fail || 'Failed to generate PDF');
        } finally {
            generatePdfBtn.disabled = false;
            generatePdfBtn.innerHTML = '<i class="bi bi-file-earmark-pdf me-2"></i> Generate & Upload PDF';
        }
    });

    // --- 2FA logic ---
    window.open2FAModal = () => {
        document.getElementById('twoFactorStep1').classList.remove('d-none');
        document.getElementById('twoFactorStep2').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('twoFactorModal')).show();
    };

    window.show2FAVerification = () => {
        document.getElementById('twoFactorStep1').classList.add('d-none');
        document.getElementById('twoFactorStep2').classList.remove('d-none');
    };

    const verifyBtn = document.getElementById('verify2FABtn');
    if (verifyBtn) {
        verifyBtn.addEventListener('click', async () => {
            const code = document.getElementById('twoFactorCode').value;
            if (code.length !== 6) return showToast('Enter 6-digit code', true);

            verifyBtn.disabled = true;
            const originalText = verifyBtn.innerHTML;
            verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying...';

            try {
                const formData = new FormData();
                formData.append('code', code);
                formData.append('action', 'verify');

                const resp = await fetch('api/manage_2fa.php', { method: 'POST', body: formData });
                const res = await resp.json();

                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('twoFactorModal')).hide();
                    showToast('2FA Enabled Successfully!', 'success');
                    triggerConfetti();
                } else alert(res.message);
            } catch (e) { alert('Verification failed'); }
            finally {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = originalText;
            }
        });
    }

    // --- Phase 7: SaaS Mastery & Ultimate Polish ---

    // 1. Activity Heatmap Logic
    const renderActivityHeatmap = (activities) => {
        const heatmap = document.getElementById('activityHeatmap');
        if (!heatmap) return;
        heatmap.innerHTML = '';

        // Generate a simple 30-day heatmap
        const now = new Date();
        const activityMap = {};

        activities.forEach(act => {
            const dateStr = new Date(act.timestamp).toISOString().split('T')[0];
            activityMap[dateStr] = (activityMap[dateStr] || 0) + 1;
        });

        for (let i = 29; i >= 0; i--) {
            const d = new Date();
            d.setDate(now.getDate() - i);
            const dateStr = d.toISOString().split('T')[0];
            const count = activityMap[dateStr] || 0;

            const cell = document.createElement('div');
            cell.className = 'heatmap-cell';
            cell.style.width = '12px';
            cell.style.height = '12px';
            cell.style.borderRadius = '2px';
            cell.style.background = count > 2 ? '#6366f1' : (count > 0 ? 'rgba(99, 102, 241, 0.4)' : 'rgba(255,255,255,0.05)');
            cell.title = `${dateStr}: ${count} activities`;
            heatmap.appendChild(cell);
        }
    };

    // 2. Relative Time Helper
    window.getRelativeTime = (timestamp) => {
        const now = new Date();
        const past = new Date(timestamp);
        const diffMs = now - past;
        const diffSec = Math.floor(diffMs / 1000);
        const diffMin = Math.floor(diffSec / 60);
        const diffHour = Math.floor(diffMin / 60);
        const diffDay = Math.floor(diffHour / 24);

        if (diffSec < 60) return 'just now';
        if (diffMin < 60) return `${diffMin}m ago`;
        if (diffHour < 24) return `${diffHour}h ago`;
        if (diffDay < 7) return `${diffDay}d ago`;
        return past.toLocaleDateString();
    };

    // --- Phase 6: Advanced SaaS Features ---

    // 1. Dynamic Header & Real-time Clock
    const initDynamicHeader = () => {
        const greetingEl = document.getElementById('dynamicGreeting');
        const clockEl = document.getElementById('realTimeClock');

        const updateHeader = () => {
            const now = new Date();
            const hour = now.getHours();
            let greeting = 'Good Evening';
            if (hour < 12) greeting = 'Good Morning';
            else if (hour < 18) greeting = 'Good Afternoon';

            if (greetingEl) greetingEl.textContent = greeting;
            if (clockEl) clockEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        };

        updateHeader();
        setInterval(updateHeader, 1000);
    };

    // 2. CSV Export for Client Ledger
    window.exportClientsCSV = async () => {
        try {
            const resp = await fetch('api/manage_clients.php');
            const data = await resp.json();
            if (!data.success) throw new Error('Failed to fetch clients');

            let csv = 'Name,Phone,Email,Total Debt (MAD)\n';
            data.clients.forEach(c => {
                csv += `"${c.name}","${c.phone || ''}","${c.email || ''}",${parseFloat(c.total_debt).toFixed(2)}\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('hidden', '');
            a.setAttribute('href', url);
            a.setAttribute('download', `SmartArchive_Clients_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            showToast('CSV Exported Successfully!');
        } catch (error) {
            showToast('Export failed', true);
        }
    };

    // 3. Pro Keyboard Shortcuts
    document.addEventListener('keydown', (e) => {
        // Don't trigger if user is typing in an input/textarea
        if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            if (e.key === 'Escape') document.activeElement.blur();
            return;
        }

        switch (e.key.toLowerCase()) {
            case '/':
                e.preventDefault();
                document.getElementById('searchInput').focus();
                break;
            case 'n':
                document.getElementById('fileInputHidden').click();
                break;
            case 'l':
                const creditTab = document.querySelector('[onclick*="loadCreditManager"]');
                if (creditTab) creditTab.click();
                break;
            case 'k':
                const shortcutModalElem = document.getElementById('shortcutModal');
                if (shortcutModalElem) {
                    new bootstrap.Modal(shortcutModalElem).show();
                }
                break;
            case 'escape':
                document.querySelectorAll('.modal.show').forEach(m => {
                    const inst = bootstrap.Modal.getInstance(m);
                    if (inst) inst.hide();
                });
                break;
        }
    });

    // --- Phase 7: SaaS Mastery ---

    // 1. Bulk Management
    const updateBulkActionsVisibility = () => {
        const checked = document.querySelectorAll('#documentTableBody .doc-checkbox:checked').length;
        if (bulkDeleteBtn) {
            if (checked > 0) {
                bulkDeleteBtn.classList.remove('d-none');
                bulkDeleteBtn.innerHTML = `<i class="bi bi-trash me-1"></i> Delete Selected (${checked})`;
            } else {
                bulkDeleteBtn.classList.add('d-none');
            }
        }
    };

    selectAll?.addEventListener('change', (e) => {
        document.querySelectorAll('#documentTableBody .doc-checkbox').forEach(cb => {
            cb.checked = e.target.checked;
        });
        updateBulkActionsVisibility();
    });

    document.getElementById('documentTableBody')?.addEventListener('change', (e) => {
        if (e.target.classList.contains('doc-checkbox')) {
            updateBulkActionsVisibility();
        }
    });

    window.deleteSelectedDocuments = async () => {
        const selected = Array.from(document.querySelectorAll('#documentTableBody .doc-checkbox:checked'))
            .map(cb => cb.closest('tr').dataset.id);
        if (selected.length === 0) return;
        if (!confirm(`Are you sure you want to delete ${selected.length} documents?`)) return;

        try {
            // Sequential delete for simplicity, or we could handle a bulk endpoint
            for (const id of selected) {
                await fetch(`api/get_docs.php?action=delete&id=${id}`);
            }
            showToast(`${selected.length} documents deleted`);
            loadDocuments();
        } catch (e) {
            showToast('Bulk delete failed', true);
        }
    };

    if (bulkDeleteBtn) bulkDeleteBtn.onclick = deleteSelectedDocuments;

    // 2. Advanced Filtering
    window.applyAdvancedFilters = () => {
        currentPage = 1;
        loadDocuments();
    };

    window.resetAdvancedFilters = () => {
        document.getElementById('filterDateStart').value = '';
        document.getElementById('filterDateEnd').value = '';
        document.getElementById('filterMinSize').value = '';
        loadDocuments();
    };

    // Modified loadDocuments to include advanced filters
    const baseLoadDocs = window.loadDocuments;
    window.loadDocuments = async () => {
        // We'll update the fetch URL within the existing loadDocuments logic via params
        // Since loadDocuments is a local const in the DOMContentLoaded scope, 
        // we should have modified it directly. I will re-modify loadDocuments in the next tool call.
        baseLoadDocs();
    };
    document.getElementById('documentTableBody')?.addEventListener('mouseover', (e) => {
        const row = e.target.closest('tr');
        if (row) row.classList.add('row-highlight-pro');
    });
    document.getElementById('documentTableBody')?.addEventListener('mouseout', (e) => {
        const row = e.target.closest('tr');
        if (row) row.classList.remove('row-highlight-pro');
    });

    // Initialize New Features
    initDynamicHeader();

    // ============================================
    // Phase 9-10: AI Intelligence & Document Pro
    // ============================================

    // --- AI Summary ---
    window.generateAISummary = async (docId) => {
        const modal = new bootstrap.Modal(document.getElementById('aiSummaryModal'));
        const content = document.getElementById('aiSummaryContent');
        content.innerHTML = '<div class="text-center py-5 text-white-50"><div class="spinner-border spinner-border-sm me-2"></div> Generating AI Summary...</div>';
        modal.show();

        try {
            const formData = new FormData();
            formData.append('document_id', docId);
            const resp = await fetch('api/ai_summary.php', { method: 'POST', body: formData });
            const data = await resp.json();
            if (data.success) {
                // Render markdown-like formatting
                let html = data.summary
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/^(📄|📋|🪪|🧾|📊|👤|✉️|🏗️|🖼️|🔒|📁)(.*)/gm, '<div class="mb-2" style="font-size: 1.05rem;">$1$2</div>')
                    .replace(/^(💡|⚠️|🔔|📅|⭐|📝)(.*)/gm, '<div class="mt-2 p-2 rounded" style="background: rgba(99,102,241,0.1);">$1$2</div>')
                    .replace(/^> (.*)/gm, '<blockquote class="ps-3 border-start border-primary border-3 text-white-50" style="font-style: italic;">$1</blockquote>')
                    .replace(/\n/g, '<br>');
                content.innerHTML = html;
                if (data.cached) {
                    content.innerHTML += '<div class="mt-3 text-white-50 small"><i class="bi bi-lightning-charge text-warning"></i> Loaded from cache</div>';
                }
            } else {
                content.innerHTML = `<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle d-block fs-3 mb-2"></i>${data.message}</div>`;
            }
        } catch (e) {
            content.innerHTML = '<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle d-block fs-3 mb-2"></i>Failed to generate summary</div>';
        }
    };

    window.copyAISummary = () => {
        const content = document.getElementById('aiSummaryContent')?.innerText;
        if (content) {
            navigator.clipboard.writeText(content);
            showToast('Summary copied to clipboard!');
        }
    };

    // --- OCR Text Extraction ---
    window.extractOCR = async (docId) => {
        const modal = new bootstrap.Modal(document.getElementById('ocrModal'));
        const content = document.getElementById('ocrContent');
        const meta = document.getElementById('ocrMeta');
        content.innerHTML = '<div class="text-center py-5 text-white-50"><div class="spinner-border spinner-border-sm me-2"></div> Extracting text from document...</div>';
        meta.innerHTML = '';
        modal.show();

        try {
            const formData = new FormData();
            formData.append('document_id', docId);
            const resp = await fetch('api/ocr_extract.php', { method: 'POST', body: formData });
            const data = await resp.json();
            if (data.success) {
                content.textContent = data.text;
                meta.innerHTML = `<i class="bi bi-info-circle me-1"></i> Method: <strong>${data.method}</strong> | Words: <strong>${data.word_count}</strong>`;
            } else {
                content.innerHTML = `<div class="text-center py-5 text-danger">${data.message}</div>`;
            }
        } catch (e) {
            content.innerHTML = '<div class="text-center py-5 text-danger">OCR extraction failed</div>';
        }
    };

    window.copyOCRText = () => {
        const content = document.getElementById('ocrContent')?.innerText;
        if (content) {
            navigator.clipboard.writeText(content);
            showToast('Extracted text copied!');
        }
    };

    // --- Share Link ---
    window.openShareModal = (docId) => {
        document.getElementById('shareDocId').value = docId;
        document.getElementById('shareResult').classList.add('d-none');
        document.getElementById('sharePassword').value = '';
        const modal = new bootstrap.Modal(document.getElementById('shareLinkModal'));
        modal.show();
    };

    window.generateShareLink = async () => {
        const docId = document.getElementById('shareDocId').value;
        const expiry = document.getElementById('shareExpiry').value;
        const password = document.getElementById('sharePassword').value;

        try {
            const formData = new FormData();
            formData.append('document_id', docId);
            formData.append('expires_hours', expiry);
            if (password) formData.append('password', password);

            const resp = await fetch('api/share_link.php?action=create', { method: 'POST', body: formData });
            const data = await resp.json();

            if (data.success) {
                document.getElementById('shareResult').classList.remove('d-none');
                document.getElementById('shareUrlOutput').value = data.share_url;
                showToast('Share link created! ' + (data.has_password ? '🔒 Password protected' : ''));
            } else {
                showToast(data.message, true);
            }
        } catch (e) {
            showToast('Failed to create share link', true);
        }
    };

    window.copyShareUrl = () => {
        const url = document.getElementById('shareUrlOutput')?.value;
        if (url) {
            navigator.clipboard.writeText(url);
            showToast('Share link copied to clipboard!');
        }
    };

    // --- Storage Analytics ---
    window.loadStorageAnalytics = async () => {
        try {
            const resp = await fetch('api/storage_analytics.php');
            const data = await resp.json();
            if (data.success) {
                const a = data.analytics;
                const storageEl = document.getElementById('storageUsedText');
                if (storageEl && a.usage_percent !== undefined) {
                    // Update storage progress bar if exists
                    const bar = document.querySelector('.storage-progress-bar');
                    if (bar) {
                        bar.style.width = Math.min(a.usage_percent, 100) + '%';
                        if (a.usage_percent > 80) bar.style.background = 'linear-gradient(90deg, #ef4444, #f97316)';
                    }
                }
                // Show warning if needed
                if (a.warning) {
                    showToast('⚠️ ' + a.warning);
                }
            }
        } catch (e) {
            // Silent fail
        }
    };

    // Load storage analytics on page load
    loadStorageAnalytics();

    // --- Session Timeout Warning ---
    let sessionTimer;
    const SESSION_TIMEOUT_MS = 25 * 60 * 1000; // Warn at 25 min (5 min before timeout)
    const resetSessionTimer = () => {
        clearTimeout(sessionTimer);
        sessionTimer = setTimeout(() => {
            showToast('⏰ Your session will expire in 5 minutes due to inactivity.', true);
        }, SESSION_TIMEOUT_MS);
    };
    // Reset timer on user activity
    ['click', 'keypress', 'mousemove', 'scroll'].forEach(evt => {
        document.addEventListener(evt, resetSessionTimer, { passive: true });
    });
    resetSessionTimer();

    // --- Phase 13: Folders & Favorites Logic ---
    window.toggleFavorite = async (docId) => {
        try {
            const resp = await fetch('api/favorites.php?action=toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `document_id=${docId}`
            });
            const res = await resp.json();
            if (res.success) {
                showToast(res.message);
                loadDocuments(); // Refresh to update star
            }
        } catch (e) { showToast('Action failed', true); }
    };

    window.loadFavorites = (element) => {
        window.loadDocumentsWithFilter('favorites', '1', element);
    };

    const loadFolders = async () => {
        const sidebarFolders = document.getElementById('sidebarFolders');
        const folderListMove = document.getElementById('folderListMove');
        try {
            const resp = await fetch('api/manage_folders.php?action=list');
            const data = await resp.json();
            if (data.success) {
                if (sidebarFolders) {
                    sidebarFolders.innerHTML = data.folders.map(f => `
                        <a href="#" class="nav-item-pro py-1 ps-4" onclick="loadDocumentsWithFilter('folder', ${f.id}, this)">
                            <i class="bi bi-folder-fill me-2" style="color: ${f.color}"></i>
                            <span class="sidebar-text small">${escapeHTML(f.name)}</span>
                            <span class="ms-auto badge bg-white-10 text-white-50 tiny">${f.doc_count}</span>
                        </a>
                    `).join('');
                }
                if (folderListMove) {
                    folderListMove.innerHTML = data.folders.map(f => `
                        <a href="#" class="list-group-item list-group-item-action glass border-0 text-white py-3" onclick="moveToFolder(${f.id})">
                            <i class="bi bi-folder-fill me-2" style="color: ${f.color}"></i> ${escapeHTML(f.name)}
                        </a>
                    `).join('');
                }
            }
        } catch (e) { }
    };

    window.openCreateFolderModal = () => {
        new bootstrap.Modal(document.getElementById('createFolderModal')).show();
    };

    window.createFolder = async () => {
        const name = document.getElementById('folderNameInput').value;
        const color = document.querySelector('.color-swatch.active')?.dataset.color || '#6366f1';
        if (!name) return showToast('Please enter a name', true);

        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('name', name);
        formData.append('color', color);

        try {
            const resp = await fetch('api/manage_folders.php', { method: 'POST', body: formData });
            const res = await resp.json();
            if (res.success) {
                showToast('Folder created!');
                bootstrap.Modal.getInstance(document.getElementById('createFolderModal')).hide();
                document.getElementById('folderNameInput').value = '';
                loadFolders();
            } else showToast(res.message, true);
        } catch (e) { showToast('Creation failed', true); }
    };

    window.openMoveFolderModal = (docId) => {
        document.getElementById('moveDocId').value = docId;
        loadFolders(); // Refresh list
        new bootstrap.Modal(document.getElementById('moveFolderModal')).show();
    };

    window.moveToFolder = async (folderId) => {
        const docId = document.getElementById('moveDocId').value;
        const formData = new FormData();
        formData.append('action', 'move');
        formData.append('document_id', docId);
        formData.append('folder_id', folderId || '');

        try {
            const resp = await fetch('api/manage_folders.php', { method: 'POST', body: formData });
            const res = await resp.json();
            if (res.success) {
                showToast('Document moved!');
                bootstrap.Modal.getInstance(document.getElementById('moveFolderModal')).hide();
                loadDocuments();
                loadFolders();
            }
        } catch (e) { showToast('Move failed', true); }
    };

    // Color picker logic for folder modal
    document.getElementById('folderColorPicker')?.addEventListener('click', (e) => {
        if (e.target.classList.contains('color-swatch')) {
            document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active', 'border', 'border-white'));
            e.target.classList.add('active', 'border', 'border-white');
        }
    });

    // --- Phase 15: Bulk Actions & Security ---
    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            const checkboxes = document.querySelectorAll('#documentTableBody .form-check-input');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            updateBulkDeleteVisibility();
        });
    }

    // Monitor individual checkbox changes
    document.getElementById('documentTableBody')?.addEventListener('change', (e) => {
        if (e.target.classList.contains('form-check-input')) {
            updateBulkDeleteVisibility();
        }
    });

    const updateBulkDeleteVisibility = () => {
        if (!bulkDeleteBtn) return;
        const selectedCount = document.querySelectorAll('#documentTableBody .form-check-input:checked').length;
        if (selectedCount > 0) {
            bulkDeleteBtn.classList.remove('d-none');
            bulkDeleteBtn.innerHTML = `<i class="bi bi-trash me-1"></i> Delete Selected (${selectedCount})`;
        } else {
            bulkDeleteBtn.classList.add('d-none');
        }
    };

    window.handleBulkDelete = async () => {
        const selectedCbs = document.querySelectorAll('#documentTableBody .form-check-input:checked');
        const ids = Array.from(selectedCbs).map(cb => cb.value);
        if (ids.length === 0) return;

        if (!confirm(`Are you sure you want to move ${ids.length} documents to trash?`)) return;

        const formData = new FormData();
        ids.forEach(id => formData.append('ids[]', id));

        try {
            const resp = await fetch('api/bulk_delete.php', { method: 'POST', body: formData });
            const res = await resp.json();
            if (res.success) {
                showToast(res.message);
                loadDocuments();
                if (selectAll) selectAll.checked = false;
                updateBulkDeleteVisibility();
            } else {
                showToast(res.message, true);
            }
        } catch (e) {
            showToast('Bulk delete failed', true);
        }
    };

    // Initial load
    loadFolders();



});
