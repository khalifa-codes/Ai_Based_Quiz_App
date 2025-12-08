<?php 
require_once '../auth_check.php';
require_once __DIR__ . '/../../config/database.php';

session_start();
$adminId = $_SESSION['admin_id'] ?? 0;

// Fetch IP management data
$ipList = [];
$stats = [
    'blocked' => 0,
    'whitelisted' => 0,
    'total' => 0
];

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get IP list
    $stmt = $conn->query("
        SELECT id, ip_address, ip_type, description, created_at, updated_at
        FROM ip_management
        ORDER BY created_at DESC
    ");
    $ipList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate stats
    foreach ($ipList as $ip) {
        if ($ip['ip_type'] === 'blacklist') {
            $stats['blocked']++;
        } else {
            $stats['whitelisted']++;
        }
        $stats['total']++;
    }
    
} catch (Exception $e) {
    error_log("IP Management Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Management - Admin Panel</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/logo-removebg-preview.png">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Quizaura Logo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle">Admin</span>
                    </span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="../dashboard.php" class="nav-link">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../profile.php" class="nav-link">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="../organizations/organization_list.php" class="nav-link">
                            <i class="bi bi-building"></i>
                            <span>Organizations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../plans/plan_list.php" class="nav-link">
                            <i class="bi bi-box-seam"></i>
                            <span>Plans</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Security</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="security_dashboard.php" class="nav-link">
                            <i class="bi bi-shield-check"></i>
                            <span>Security Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="ip_management.php" class="nav-link active">
                            <i class="bi bi-router"></i>
                            <span>IP Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="security_settings.php" class="nav-link">
                            <i class="bi bi-gear"></i>
                            <span>Security Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="audit_logs.php" class="nav-link">
                            <i class="bi bi-file-text"></i>
                            <span>Audit Logs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="data_retention.php" class="nav-link">
                            <i class="bi bi-database"></i>
                            <span>Data Retention</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Analytics</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="../reports/system_report.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>System Reports</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="sidebar-user" id="sidebarUserDropdown">
                    <div class="sidebar-user-header" style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; border-radius: 8px; transition: all 0.2s ease;">
                        <div class="sidebar-user-avatar">A</div>
                        <div class="sidebar-user-info" style="flex: 1; min-width: 0;">
                            <p class="sidebar-user-name">Admin User</p>
                            <p class="sidebar-user-role">Administrator</p>
                        </div>
                        <i class="bi bi-chevron-down" id="userDropdownIcon" style="transition: transform 0.2s ease; color: var(--text-muted);"></i>
                    </div>
                    <div class="sidebar-user-menu" id="sidebarUserMenu">
                        <a href="../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                        <a href="#" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
                        </a>
                        <a href="../logout.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <main class="admin-main">
            <button class="floating-hamburger" id="floatingHamburger">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="admin-topbar">
                <div class="topbar-left">
                    <div>
                        <h1 class="topbar-title">IP Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="security_dashboard.php">Security</a></li>
                                <li class="breadcrumb-item active">IP Management</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-actions" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0.75rem !important; flex-wrap: nowrap !important;">
                        <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="admin-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Blocked IPs</h3>
                            <div class="stat-card-icon red">
                                <i class="bi bi-ban"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo $stats['blocked']; ?></div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-info-circle"></i>
                            <span>Blocked IPs</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Whitelisted IPs</h3>
                            <div class="stat-card-icon green">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo $stats['whitelisted']; ?></div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-info-circle"></i>
                            <span>Whitelisted IPs</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total IP Rules</h3>
                            <div class="stat-card-icon blue">
                                <i class="bi bi-list-check"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo $stats['total']; ?></div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-info-circle"></i>
                            <span>Active Rules</span>
                        </div>
                    </div>
                </div>

                <!-- Add IP Section -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Add IP Address</h2>
                            </div>
                            <div class="content-card-body">
                                <form id="addIpForm">
                                    <div class="mb-3">
                                        <label class="admin-form-label">IP Address or Range <span class="required-asterisk">*</span></label>
                                        <input type="text" name="ipAddress" class="admin-form-control" placeholder="192.168.1.1 or 192.168.1.0/24" required>
                                        <small class="form-text text-muted">Enter single IP or CIDR notation for ranges</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Type <span class="required-asterisk">*</span></label>
                                        <select name="ipType" class="admin-form-control" required>
                                            <option value="">Select Type</option>
                                            <option value="blacklist">Blacklist (Block)</option>
                                            <option value="whitelist">Whitelist (Allow)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Reason</label>
                                        <textarea name="reason" class="admin-form-control" rows="3" placeholder="Reason for blocking/whitelisting this IP"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permanent" id="permanentCheck">
                                            <label class="form-check-label" for="permanentCheck">
                                                Permanent (never expires)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3" id="expiryDateGroup" style="display: none;">
                                        <label class="admin-form-label">Expiry Date</label>
                                        <input type="datetime-local" name="expiryDate" class="admin-form-control">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Add IP Rule
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Bulk Import</h2>
                            </div>
                            <div class="content-card-body">
                                <form id="bulkImportForm">
                                    <div class="mb-3">
                                        <label class="admin-form-label">IP Addresses (one per line) <span class="required-asterisk">*</span></label>
                                        <textarea name="ipList" class="admin-form-control" rows="8" placeholder="192.168.1.1&#10;192.168.1.2&#10;10.0.0.0/24" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Type <span class="required-asterisk">*</span></label>
                                        <select name="bulkType" class="admin-form-control" required>
                                            <option value="">Select Type</option>
                                            <option value="blacklist">Blacklist (Block)</option>
                                            <option value="whitelist">Whitelist (Allow)</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload"></i> Import IPs
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- IP Lists -->
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Blocked IPs</h2>
                                <div class="content-card-header-actions">
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="blockedIpSearch" placeholder="Search IPs...">
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" id="clearAllBlockedBtn">
                                        <i class="bi bi-trash"></i> Clear All
                                    </button>
                                </div>
                            </div>
                            <div class="content-card-body">
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>IP Address</th>
                                                <th>Reason</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="blockedIpTableBody">
                                            <?php 
                                            $blockedIPs = array_filter($ipList, function($ip) { return $ip['ip_type'] === 'blacklist'; });
                                            if (empty($blockedIPs)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No blocked IPs</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($blockedIPs as $ip): 
                                                $timeAgo = '';
                                                if ($ip['created_at']) {
                                                    $created = strtotime($ip['created_at']);
                                                    $diff = time() - $created;
                                                    if ($diff < 3600) {
                                                        $timeAgo = round($diff / 60) . ' minutes ago';
                                                    } elseif ($diff < 86400) {
                                                        $timeAgo = round($diff / 3600) . ' hours ago';
                                                    } else {
                                                        $timeAgo = round($diff / 86400) . ' days ago';
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($ip['ip_address']); ?></td>
                                                <td><?php echo htmlspecialchars($ip['description'] ?? 'No reason provided'); ?></td>
                                                <td><?php echo $timeAgo; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary unblockIpBtn" data-id="<?php echo $ip['id']; ?>" data-ip="<?php echo htmlspecialchars($ip['ip_address']); ?>">
                                                        <i class="bi bi-unlock"></i> Unblock
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Whitelisted IPs</h2>
                                <div class="content-card-header-actions">
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="whitelistIpSearch" placeholder="Search IPs...">
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" id="clearAllWhitelistBtn">
                                        <i class="bi bi-trash"></i> Clear All
                                    </button>
                                </div>
                            </div>
                            <div class="content-card-body">
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>IP Address</th>
                                                <th>Reason</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="whitelistIpTableBody">
                                            <?php 
                                            $whitelistedIPs = array_filter($ipList, function($ip) { return $ip['ip_type'] === 'whitelist'; });
                                            if (empty($whitelistedIPs)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No whitelisted IPs</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($whitelistedIPs as $ip): 
                                                $timeAgo = '';
                                                if ($ip['created_at']) {
                                                    $created = strtotime($ip['created_at']);
                                                    $diff = time() - $created;
                                                    if ($diff < 3600) {
                                                        $timeAgo = round($diff / 60) . ' minutes ago';
                                                    } elseif ($diff < 86400) {
                                                        $timeAgo = round($diff / 3600) . ' hours ago';
                                                    } else {
                                                        $timeAgo = round($diff / 86400) . ' days ago';
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($ip['ip_address']); ?></td>
                                                <td><?php echo htmlspecialchars($ip['description'] ?? 'No reason provided'); ?></td>
                                                <td><?php echo $timeAgo; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger removeWhitelistBtn" data-id="<?php echo $ip['id']; ?>" data-ip="<?php echo htmlspecialchars($ip['ip_address']); ?>">
                                                        <i class="bi bi-x-circle"></i> Remove
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Country-Based Access Control -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Country Access Control</h2>
                            </div>
                            <div class="content-card-body">
                                <form id="countryAccessForm">
                                    <div class="mb-3">
                                        <label class="admin-form-label">Action <span class="required-asterisk">*</span></label>
                                        <select name="countryAction" class="admin-form-control" required>
                                            <option value="">Select Action</option>
                                            <option value="block">Block Country</option>
                                            <option value="allow">Allow Country</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Country <span class="required-asterisk">*</span></label>
                                        <select name="country" class="admin-form-control" id="countrySelect" required>
                                            <option value="">Select Country</option>
                                            <option value="US">United States</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="CA">Canada</option>
                                            <option value="AU">Australia</option>
                                            <option value="DE">Germany</option>
                                            <option value="FR">France</option>
                                            <option value="IT">Italy</option>
                                            <option value="ES">Spain</option>
                                            <option value="NL">Netherlands</option>
                                            <option value="BE">Belgium</option>
                                            <option value="CH">Switzerland</option>
                                            <option value="AT">Austria</option>
                                            <option value="SE">Sweden</option>
                                            <option value="NO">Norway</option>
                                            <option value="DK">Denmark</option>
                                            <option value="FI">Finland</option>
                                            <option value="PL">Poland</option>
                                            <option value="CZ">Czech Republic</option>
                                            <option value="IE">Ireland</option>
                                            <option value="PT">Portugal</option>
                                            <option value="GR">Greece</option>
                                            <option value="CN">China</option>
                                            <option value="JP">Japan</option>
                                            <option value="KR">South Korea</option>
                                            <option value="IN">India</option>
                                            <option value="BR">Brazil</option>
                                            <option value="MX">Mexico</option>
                                            <option value="AR">Argentina</option>
                                            <option value="RU">Russia</option>
                                            <option value="TR">Turkey</option>
                                            <option value="SA">Saudi Arabia</option>
                                            <option value="AE">United Arab Emirates</option>
                                            <option value="ZA">South Africa</option>
                                            <option value="EG">Egypt</option>
                                            <option value="NG">Nigeria</option>
                                            <option value="KE">Kenya</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Reason</label>
                                        <textarea name="countryReason" class="admin-form-control" rows="3" placeholder="Reason for blocking/allowing this country"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Add Country Rule
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">VPN/Proxy Detection</h2>
                            </div>
                            <div class="content-card-body">
                                <form id="vpnProxyForm">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enableVpnBlock" id="enableVpnBlock" checked>
                                            <label class="form-check-label" for="enableVpnBlock">
                                                Block VPN Connections
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enableProxyBlock" id="enableProxyBlock" checked>
                                            <label class="form-check-label" for="enableProxyBlock">
                                                Block Proxy Connections
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enableTorBlock" id="enableTorBlock" checked>
                                            <label class="form-check-label" for="enableTorBlock">
                                                Block Tor Network
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Action on Detection</label>
                                        <select name="vpnAction" class="admin-form-control">
                                            <option value="block" selected>Block Immediately</option>
                                            <option value="warn">Warn User</option>
                                            <option value="log">Log Only</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Whitelist IPs (one per line)</label>
                                        <textarea name="vpnWhitelist" class="admin-form-control" rows="4" placeholder="192.168.1.1&#10;10.0.0.50"></textarea>
                                        <small class="form-text text-muted">IPs that should bypass VPN/Proxy detection</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Save Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Country Lists -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Blocked Countries</h2>
                                <div class="content-card-header-actions">
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="blockedCountrySearch" placeholder="Search countries...">
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" id="clearAllBlockedCountriesBtn">
                                        <i class="bi bi-trash"></i> Clear All
                                    </button>
                                </div>
                            </div>
                            <div class="content-card-body">
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Country</th>
                                                <th>Code</th>
                                                <th>Reason</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="blockedCountryTableBody">
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-flag-fill"></i>
                                                        <span>China</span>
                                                    </div>
                                                </td>
                                                <td>CN</td>
                                                <td>High risk region</td>
                                                <td>1 week ago</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary unblockCountryBtn" data-country="CN">
                                                        <i class="bi bi-unlock"></i> Unblock
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-flag-fill"></i>
                                                        <span>Russia</span>
                                                    </div>
                                                </td>
                                                <td>RU</td>
                                                <td>Security concerns</td>
                                                <td>2 weeks ago</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary unblockCountryBtn" data-country="RU">
                                                        <i class="bi bi-unlock"></i> Unblock
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-flag-fill"></i>
                                                        <span>North Korea</span>
                                                    </div>
                                                </td>
                                                <td>KP</td>
                                                <td>Sanctions compliance</td>
                                                <td>1 month ago</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary unblockCountryBtn" data-country="KP">
                                                        <i class="bi bi-unlock"></i> Unblock
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Allowed Countries</h2>
                                <div class="content-card-header-actions">
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="allowedCountrySearch" placeholder="Search countries...">
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" id="clearAllAllowedCountriesBtn">
                                        <i class="bi bi-trash"></i> Clear All
                                    </button>
                                </div>
                            </div>
                            <div class="content-card-body">
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Country</th>
                                                <th>Code</th>
                                                <th>Reason</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="allowedCountryTableBody">
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-flag-fill"></i>
                                                        <span>United States</span>
                                                    </div>
                                                </td>
                                                <td>US</td>
                                                <td>Primary market</td>
                                                <td>1 month ago</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger removeAllowedCountryBtn" data-country="US">
                                                        <i class="bi bi-x-circle"></i> Remove
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-flag-fill"></i>
                                                        <span>United Kingdom</span>
                                                    </div>
                                                </td>
                                                <td>GB</td>
                                                <td>Trusted region</td>
                                                <td>3 weeks ago</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger removeAllowedCountryBtn" data-country="GB">
                                                        <i class="bi bi-x-circle"></i> Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VPN/Proxy Blocked Logs -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">VPN/Proxy Blocked Attempts</h2>
                                <div class="content-card-header-actions">
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="vpnBlockSearch" placeholder="Search IPs...">
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary" id="refreshVpnLogsBtn">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" id="clearVpnLogsBtn">
                                        <i class="bi bi-trash"></i> Clear Logs
                                    </button>
                                </div>
                            </div>
                            <div class="content-card-body">
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>IP Address</th>
                                                <th>Type</th>
                                                <th>Country</th>
                                                <th>Provider</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="vpnBlockTableBody">
                                            <tr>
                                                <td>203.0.113.45</td>
                                                <td><span class="badge badge-warning">VPN</span></td>
                                                <td>US</td>
                                                <td>NordVPN</td>
                                                <td>2 hours ago</td>
                                                <td><span class="badge badge-danger">Blocked</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary whitelistVpnIpBtn" data-ip="203.0.113.45">
                                                        <i class="bi bi-check-circle"></i> Whitelist
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>198.51.100.30</td>
                                                <td><span class="badge badge-info">Proxy</span></td>
                                                <td>GB</td>
                                                <td>Residential Proxy</td>
                                                <td>5 hours ago</td>
                                                <td><span class="badge badge-danger">Blocked</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary whitelistVpnIpBtn" data-ip="198.51.100.30">
                                                        <i class="bi bi-check-circle"></i> Whitelist
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>192.0.2.15</td>
                                                <td><span class="badge badge-danger">Tor</span></td>
                                                <td>Unknown</td>
                                                <td>Tor Network</td>
                                                <td>1 day ago</td>
                                                <td><span class="badge badge-danger">Blocked</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary whitelistVpnIpBtn" data-ip="192.0.2.15">
                                                        <i class="bi bi-check-circle"></i> Whitelist
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>172.16.0.200</td>
                                                <td><span class="badge badge-warning">VPN</span></td>
                                                <td>DE</td>
                                                <td>ExpressVPN</td>
                                                <td>2 days ago</td>
                                                <td><span class="badge badge-danger">Blocked</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary whitelistVpnIpBtn" data-ip="172.16.0.200">
                                                        <i class="bi bi-check-circle"></i> Whitelist
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme Management
        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            }
        }

        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        if (themeToggle) {
            let isToggling = false;
            
            themeToggle.addEventListener('mousedown', function(e) {
                e.preventDefault();
            });
            
            themeToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (isToggling) return;
                isToggling = true;
                
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
                
                setTimeout(() => {
                    isToggling = false;
                }, 300);
            });
        }

        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const floatingHamburger = document.getElementById('floatingHamburger');
        const adminSidebar = document.getElementById('adminSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function closeSidebar() {
            adminSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            if (floatingHamburger) {
                floatingHamburger.style.display = 'flex';
            }
        }

        function openSidebar() {
            adminSidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            if (floatingHamburger) {
                floatingHamburger.style.display = 'none';
            }
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeSidebar();
            });
        }

        if (floatingHamburger) {
            floatingHamburger.addEventListener('click', function(e) {
                e.preventDefault();
                openSidebar();
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                closeSidebar();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && adminSidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // User Dropdown
        const sidebarUserDropdown = document.getElementById('sidebarUserDropdown');
        const sidebarUserMenu = document.getElementById('sidebarUserMenu');

        if (sidebarUserDropdown && sidebarUserMenu) {
            const userHeader = sidebarUserDropdown.querySelector('.sidebar-user-header');
            
            if (userHeader) {
                userHeader.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebarUserDropdown.classList.toggle('active');
                });
            }

            document.addEventListener('click', function(e) {
                if (!sidebarUserDropdown.contains(e.target)) {
                    sidebarUserDropdown.classList.remove('active');
                }
            });
        }

        // Permanent checkbox toggle
        document.getElementById('permanentCheck').addEventListener('change', function() {
            const expiryGroup = document.getElementById('expiryDateGroup');
            if (this.checked) {
                expiryGroup.style.display = 'none';
            } else {
                expiryGroup.style.display = 'block';
            }
        });

        // Add IP Form
        document.getElementById('addIpForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const ipAddress = formData.get('ipAddress');
            const ipType = formData.get('ipType');
            const reason = formData.get('reason');
            
            // Validate IP format
            const ipRegex = /^(\d{1,3}\.){3}\d{1,3}(\/\d{1,2})?$/;
            if (!ipRegex.test(ipAddress)) {
                alert('Please enter a valid IP address or CIDR notation');
                return;
            }
            
            alert(`${ipType === 'blacklist' ? 'Blocked' : 'Whitelisted'} IP address ${ipAddress} successfully!`);
            this.reset();
            document.getElementById('expiryDateGroup').style.display = 'none';
        });

        // Bulk Import Form
        document.getElementById('bulkImportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const ipList = formData.get('ipList');
            const bulkType = formData.get('bulkType');
            
            const ips = ipList.split('\n').filter(ip => ip.trim());
            alert(`Imported ${ips.length} IP addresses as ${bulkType} successfully!`);
            this.reset();
        });

        // Unblock IP
        document.querySelectorAll('.unblockIpBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const ip = this.getAttribute('data-ip');
                if (confirm(`Are you sure you want to unblock IP address ${ip}?`)) {
                    const row = this.closest('tr');
                    row.style.opacity = '0.5';
                    setTimeout(() => {
                        row.remove();
                        alert(`IP address ${ip} has been unblocked!`);
                    }, 500);
                }
            });
        });

        // Remove Whitelist
        document.querySelectorAll('.removeWhitelistBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const ip = this.getAttribute('data-ip');
                if (confirm(`Are you sure you want to remove ${ip} from whitelist?`)) {
                    const row = this.closest('tr');
                    row.style.opacity = '0.5';
                    setTimeout(() => {
                        row.remove();
                        alert(`${ip} has been removed from whitelist!`);
                    }, 500);
                }
            });
        });

        // Clear All Blocked
        document.getElementById('clearAllBlockedBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all blocked IPs? This action cannot be undone.')) {
                document.getElementById('blockedIpTableBody').innerHTML = '';
                alert('All blocked IPs have been cleared!');
            }
        });

        // Clear All Whitelist
        document.getElementById('clearAllWhitelistBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all whitelisted IPs? This action cannot be undone.')) {
                document.getElementById('whitelistIpTableBody').innerHTML = '';
                alert('All whitelisted IPs have been cleared!');
            }
        });

        // Search functionality
        document.getElementById('blockedIpSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#blockedIpTableBody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('whitelistIpSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#whitelistIpTableBody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Country Access Form
        document.getElementById('countryAccessForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const countryAction = formData.get('countryAction');
            const country = formData.get('country');
            const countryName = document.getElementById('countrySelect').selectedOptions[0].text;
            
            if (!country || !countryAction) {
                alert('Please select both action and country');
                return;
            }
            
            alert(`${countryAction === 'block' ? 'Blocked' : 'Allowed'} country ${countryName} (${country}) successfully!`);
            this.reset();
        });

        // VPN/Proxy Form
        document.getElementById('vpnProxyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('VPN/Proxy settings saved successfully!');
            }, 1500);
        });

        // Unblock Country
        document.querySelectorAll('.unblockCountryBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const country = this.getAttribute('data-country');
                if (confirm(`Are you sure you want to unblock country ${country}?`)) {
                    const row = this.closest('tr');
                    row.style.opacity = '0.5';
                    setTimeout(() => {
                        row.remove();
                        alert(`Country ${country} has been unblocked!`);
                    }, 500);
                }
            });
        });

        // Remove Allowed Country
        document.querySelectorAll('.removeAllowedCountryBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const country = this.getAttribute('data-country');
                if (confirm(`Are you sure you want to remove ${country} from allowed countries?`)) {
                    const row = this.closest('tr');
                    row.style.opacity = '0.5';
                    setTimeout(() => {
                        row.remove();
                        alert(`${country} has been removed from allowed countries!`);
                    }, 500);
                }
            });
        });

        // Whitelist VPN IP
        document.querySelectorAll('.whitelistVpnIpBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const ip = this.getAttribute('data-ip');
                if (confirm(`Are you sure you want to whitelist IP address ${ip}? This will bypass VPN/Proxy detection.`)) {
                    const row = this.closest('tr');
                    const actionCell = row.querySelector('td:nth-child(6)');
                    actionCell.innerHTML = '<span class="badge badge-success">Whitelisted</span>';
                    this.innerHTML = '<i class="bi bi-check-circle-fill"></i> Whitelisted';
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-success');
                    this.disabled = true;
                    alert(`IP address ${ip} has been whitelisted!`);
                }
            });
        });

        // Clear All Blocked Countries
        document.getElementById('clearAllBlockedCountriesBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all blocked countries? This action cannot be undone.')) {
                document.getElementById('blockedCountryTableBody').innerHTML = '';
                alert('All blocked countries have been cleared!');
            }
        });

        // Clear All Allowed Countries
        document.getElementById('clearAllAllowedCountriesBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all allowed countries? This action cannot be undone.')) {
                document.getElementById('allowedCountryTableBody').innerHTML = '';
                alert('All allowed countries have been cleared!');
            }
        });

        // Search Blocked Countries
        document.getElementById('blockedCountrySearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#blockedCountryTableBody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Search Allowed Countries
        document.getElementById('allowedCountrySearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#allowedCountryTableBody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Search VPN/Proxy Logs
        document.getElementById('vpnBlockSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#vpnBlockTableBody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Refresh VPN Logs
        document.getElementById('refreshVpnLogsBtn').addEventListener('click', function() {
            const btn = this;
            const icon = btn.querySelector('i');
            icon.classList.add('spin');
            btn.disabled = true;
            
            setTimeout(() => {
                icon.classList.remove('spin');
                btn.disabled = false;
                alert('VPN/Proxy logs refreshed successfully!');
            }, 1000);
        });

        // Clear VPN Logs
        document.getElementById('clearVpnLogsBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all VPN/Proxy blocked logs? This action cannot be undone.')) {
                document.getElementById('vpnBlockTableBody').innerHTML = '';
                alert('All VPN/Proxy logs have been cleared!');
            }
        });

        // Add spin animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .spin {
                animation: spin 1s linear infinite;
            }
        `;
        document.head.appendChild(style);
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

