<?php
/**
 * Organization Branding Update API
 * Handles organization branding updates and applies to all modules
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Include database connection
require_once '../../config/database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Get organization ID from session (assuming organization is logged in)
session_start();
$organizationId = $_SESSION['org_id'] ?? $_SESSION['organization_id'] ?? null;

if (!$organizationId) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login.'
    ]);
    exit;
}

// Validate input
$primaryColor = isset($input['primaryColor']) ? trim($input['primaryColor']) : '#0d6efd';
$secondaryColor = isset($input['secondaryColor']) ? trim($input['secondaryColor']) : '#0b5ed7';
$fontFamily = isset($input['fontFamily']) ? trim($input['fontFamily']) : 'Inter';
$logoUrl = isset($input['logo']) ? trim($input['logo']) : null;

// Validate color format
if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $primaryColor)) {
    $primaryColor = '#0d6efd';
}
if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $secondaryColor)) {
    $secondaryColor = '#0b5ed7';
}

try {
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if branding exists
    $stmt = $conn->prepare("SELECT id FROM organization_branding WHERE organization_id = ?");
    $stmt->execute([$organizationId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing branding
        $stmt = $conn->prepare("
            UPDATE organization_branding 
            SET logo_url = ?, 
                primary_color = ?, 
                secondary_color = ?, 
                font_family = ?,
                updated_at = NOW()
            WHERE organization_id = ?
        ");
        
        $result = $stmt->execute([
            $logoUrl,
            $primaryColor,
            $secondaryColor,
            $fontFamily,
            $organizationId
        ]);
    } else {
        // Insert new branding
        $stmt = $conn->prepare("
            INSERT INTO organization_branding 
            (organization_id, logo_url, primary_color, secondary_color, font_family, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $result = $stmt->execute([
            $organizationId,
            $logoUrl,
            $primaryColor,
            $secondaryColor,
            $fontFamily
        ]);
    }
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Branding updated successfully! Changes will be applied across all modules.',
            'data' => [
                'primaryColor' => $primaryColor,
                'secondaryColor' => $secondaryColor,
                'fontFamily' => $fontFamily,
                'logoUrl' => $logoUrl
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update branding. Please try again.'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Branding Update Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log("Branding Update Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during update. Please try again.'
    ]);
}
?>

