<?php
/**
 * Branding Loader
 * Loads organization branding from database and applies it
 * Include this file in all modules (organization, teacher, student)
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get organization ID from session
$organizationId = null;
if (isset($_SESSION['org_id'])) {
    $organizationId = $_SESSION['org_id'];
} elseif (isset($_SESSION['organization_id'])) {
    $organizationId = $_SESSION['organization_id'];
} elseif (isset($_SESSION['teacher_organization_id'])) {
    $organizationId = $_SESSION['teacher_organization_id'];
} elseif (isset($_SESSION['student_organization_id'])) {
    $organizationId = $_SESSION['student_organization_id'];
}

$brandingData = [
    'logo' => null,
    'primaryColor' => '#0d6efd',
    'secondaryColor' => '#0b5ed7',
    'fontFamily' => 'Inter'
];

if ($organizationId) {
    try {
        require_once __DIR__ . '/../config/database.php';
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT logo_url, primary_color, secondary_color, font_family 
            FROM organization_branding 
            WHERE organization_id = ?
        ");
        $stmt->execute([$organizationId]);
        $branding = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($branding) {
            $brandingData = [
                'logo' => $branding['logo_url'],
                'primaryColor' => $branding['primary_color'] ?: '#0d6efd',
                'secondaryColor' => $branding['secondary_color'] ?: '#0b5ed7',
                'fontFamily' => $branding['font_family'] ?: 'Inter'
            ];
        }
    } catch (Exception $e) {
        error_log("Branding Loader Error: " . $e->getMessage());
    }
}

// Output branding as JavaScript for client-side application
?>
<script>
    // Organization Branding Data
    window.orgBranding = <?php echo json_encode($brandingData); ?>;
    
    // Apply branding on page load
    (function() {
        const branding = window.orgBranding;
        
        // Apply colors
        if (branding.primaryColor) {
            document.documentElement.style.setProperty('--primary-color', branding.primaryColor);
        }
        if (branding.secondaryColor) {
            document.documentElement.style.setProperty('--primary-hover', branding.secondaryColor);
        }
        
        // Apply font
        if (branding.fontFamily) {
            document.body.style.fontFamily = branding.fontFamily;
        }
        
        // Apply logo if exists
        const orgLogo = document.getElementById('orgLogo');
        if (orgLogo && branding.logo) {
            orgLogo.src = branding.logo;
        }
        
        // Also save to localStorage for consistency
        localStorage.setItem('orgBranding', JSON.stringify(branding));
    })();
</script>
<?php
// Also store in PHP variable for server-side use
$GLOBALS['orgBranding'] = $brandingData;
?>

