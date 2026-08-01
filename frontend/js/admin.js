var AdminApp = (function() {
    var adminRoles = ['warehouse_admin', 'system_admin'];
    var deliveryRoles = ['delivery_personnel'];

    function getCurrentUserRole() {
        var user = getCurrentUser();
        var role = user ? (user.Role || user.role) : null;
        return role ? role.toLowerCase() : null;
    }

    function requireAdmin() {
        var role = getCurrentUserRole();
        if (!isLoggedIn() || (role !== 'warehouse_admin' && role !== 'system_admin')) {
            showAlert('Access denied. Admin privileges required.', 'error');
            setTimeout(function() { window.location.href = '../index.html'; }, 1000);
            return false;
        }
        return true;
    }

    function requireDelivery() {
        var role = getCurrentUserRole();
        if (!isLoggedIn() || role !== 'delivery_personnel') {
            showAlert('Access denied. Delivery personnel only.', 'error');
            setTimeout(function() { window.location.href = '../index.html'; }, 1000);
            return false;
        }
        return true;
    }

    function requireSystemAdmin() {
        var role = getCurrentUserRole();
        if (!isLoggedIn() || role !== 'system_admin') {
            showAlert('Access denied. System administrator only.', 'error');
            setTimeout(function() { window.location.href = '../index.html'; }, 1000);
            return false;
        }
        return true;
    }

    function getSidebarHTML(activePage) {
        var role = getCurrentUserRole();
        var user = getCurrentUser();
        var name = (user && user.Name) ? user.Name : 'Admin';
        var initials = name.split(' ').map(function(w) { return w[0]; }).join('').substring(0, 2).toUpperCase();

        var isAdmin = role === 'warehouse_admin' || role === 'system_admin';
        var isDelivery = role === 'delivery_personnel';
        var isSystemAdmin = role === 'system_admin';

        var roleLabel = role === 'system_admin' ? 'System Admin' : role === 'warehouse_admin' ? 'Warehouse Admin' : 'Delivery';

        var html = '<aside class="admin-sidebar" id="adminSidebar">';
        html += '<div class="sidebar-header">';
        html += '<i class="fa-solid fa-warehouse logo-icon"></i>';
        html += '<span class="logo-text">GWC</span>';
        html += '<span class="role-badge">' + roleLabel + '</span>';
        html += '</div>';
        html += '<nav class="sidebar-nav">';

        if (isAdmin) {
            html += '<div class="nav-section">Main</div>';
            html += '<a href="dashboard.html" class="' + (activePage === 'dashboard' ? 'active' : '') + '"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>';

            html += '<div class="nav-section">Management</div>';
            html += '<a href="products.html" class="' + (activePage === 'products' ? 'active' : '') + '"><i class="fa-solid fa-box"></i> Products</a>';
            html += '<a href="orders.html" class="' + (activePage === 'orders' ? 'active' : '') + '"><i class="fa-solid fa-clipboard-list"></i> Orders</a>';
            html += '<a href="customers.html" class="' + (activePage === 'customers' ? 'active' : '') + '"><i class="fa-solid fa-users"></i> Customers</a>';
            html += '<a href="reports.html" class="' + (activePage === 'reports' ? 'active' : '') + '"><i class="fa-solid fa-chart-line"></i> Reports</a>';

            if (isSystemAdmin) {
                html += '<div class="nav-section">System</div>';
                html += '<a href="users.html" class="' + (activePage === 'users' ? 'active' : '') + '"><i class="fa-solid fa-user-gear"></i> User Management</a>';
                html += '<a href="audit.html" class="' + (activePage === 'audit' ? 'active' : '') + '"><i class="fa-solid fa-shield-halved"></i> Audit Logs</a>';
            }
        }

        if (isDelivery) {
            html += '<div class="nav-section">Delivery</div>';
            html += '<a href="dashboard.html" class="' + (activePage === 'dashboard' ? 'active' : '') + '"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>';
            html += '<a href="orders.html" class="' + (activePage === 'orders' ? 'active' : '') + '"><i class="fa-solid fa-truck"></i> My Deliveries</a>';
            html += '<a href="history.html" class="' + (activePage === 'history' ? 'active' : '') + '"><i class="fa-solid fa-clock-rotate-left"></i> History</a>';
        }

        html += '<div class="nav-divider"></div>';
        html += '<div class="nav-section">Account</div>';
        html += '<a href="../profile.html"><i class="fa-solid fa-user"></i> My Profile</a>';
        html += '<a href="#" onclick="logout()"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>';

        html += '</nav></aside>';
        return html;
    }

    function getTopbarHTML(title) {
        var user = getCurrentUser();
        var name = (user && user.Name) ? user.Name : 'Admin';
        var initials = name.split(' ').map(function(w) { return w[0]; }).join('').substring(0, 2).toUpperCase();

        var html = '<div class="admin-topbar">';
        html += '<div class="topbar-left">';
        html += '<button class="sidebar-toggle" onclick="AdminApp.toggleSidebar()"><i class="fa-solid fa-bars"></i></button>';
        html += '<span class="topbar-title">' + title + '</span>';
        html += '</div>';
        html += '<div class="topbar-right">';
        html += '<div class="user-info">';
        html += '<div class="user-avatar">' + initials + '</div>';
        html += '<span>' + escapeHtml(name) + '</span>';
        html += '</div>';
        html += '</div></div>';
        return html;
    }

    function initLayout(title, activePage) {
        var layout = document.getElementById('adminLayout');
        if (!layout) return;

        layout.innerHTML = getSidebarHTML(activePage) +
            '<div class="admin-main">' +
            getTopbarHTML(title) +
            '<div class="admin-content" id="adminContent"></div>' +
            '</div>';

        return document.getElementById('adminContent');
    }

    function toggleSidebar() {
        var sidebar = document.getElementById('adminSidebar');
        if (sidebar) sidebar.classList.toggle('open');
    }

    function showModal(modalId) {
        var overlay = document.getElementById(modalId);
        if (overlay) overlay.classList.add('active');
    }

    function hideModal(modalId) {
        var overlay = document.getElementById(modalId);
        if (overlay) overlay.classList.remove('active');
    }

    function renderTable(config) {
        var container = document.getElementById(config.containerId);
        if (!container) return;

        var data = config.data || [];
        var search = (config.searchTerm || '').toLowerCase();
        var filter = config.filterTerm || '';

        var filtered = data.filter(function(item) {
            var matchSearch = !search || JSON.stringify(item).toLowerCase().indexOf(search) !== -1;
            var matchFilter = !filter || item[config.filterKey] === filter;
            return matchSearch && matchFilter;
        });

        if (filtered.length === 0) {
            container.innerHTML = '<div class="empty-state"><div class="empty-state-icon"><i class="fa-solid fa-inbox"></i></div><p>No data found</p></div>';
            return;
        }

        var html = '<table class="data-table"><thead><tr>';
        config.columns.forEach(function(col) {
            html += '<th>' + col.label + '</th>';
        });
        if (config.actions) html += '<th>Actions</th>';
        html += '</tr></thead><tbody>';

        filtered.forEach(function(item, idx) {
            html += '<tr>';
            config.columns.forEach(function(col) {
                var val = col.render ? col.render(item) : (item[col.key] || '-');
                html += '<td>' + val + '</td>';
            });
            if (config.actions) {
                html += '<td class="actions">' + config.actions(item, idx) + '</td>';
            }
            html += '</tr>';
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function statusBadge(status) {
        var colors = {
            'Processing': 'warning', 'Confirmed': 'info', 'Packed': 'info',
            'Dispatched': 'primary', 'Shipped': 'primary', 'Out for Delivery': 'primary',
            'Delivered': 'success', 'Cancelled': 'danger', 'Failed': 'danger',
            'Active': 'success', 'Inactive': 'danger', 'Pending': 'warning'
        };
        var cls = colors[status] || 'secondary';
        return '<span class="status-badge status-' + cls + '">' + escapeHtml(status) + '</span>';
    }

    return {
        requireAdmin: requireAdmin,
        requireDelivery: requireDelivery,
        requireSystemAdmin: requireSystemAdmin,
        getSidebarHTML: getSidebarHTML,
        getTopbarHTML: getTopbarHTML,
        initLayout: initLayout,
        toggleSidebar: toggleSidebar,
        showModal: showModal,
        hideModal: hideModal,
        renderTable: renderTable,
        statusBadge: statusBadge,
        getCurrentUserRole: getCurrentUserRole
    };
})();
