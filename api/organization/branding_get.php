<?php
/**
 * Organization Branding Get API
 * Retrieves organization branding data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../../config/database.php';

// Get organization ID from session or parameter
session_start();
$organizationId = $_SESSION['organization_id'] ?? $_GET['organization_id'] ?? null;

if (!$organizationId) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Organization ID required'
    ]);
    exit;
}

try {
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get branding data
    $stmt = $conn->prepare("
        SELECT logo_url, primary_color, secondary_color, font_family 
        FROM organization_branding 
        WHERE organization_id = ?
    ");
    $stmt->execute([$organizationId]);
    $branding = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($branding) {
        echo json_encode([
            'success' => true,
            'data' => [
                'logo' => $branding['logo_url'],
                'primaryColor' => $branding['primary_color'],
                'secondaryColor' => $branding['secondary_color'],
                'fontFamily' => $branding['font_family']
            ]
        ]);
    } else {
        // Return default branding
        echo json_encode([
            'success' => true,
            'data' => [
                'logo' => null,
                'primaryColor' => '#0d6efd',
                'secondaryColor' => '#0b5ed7',
                'fontFamily' => 'Inter'
            ]
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Branding Get Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred.'
    ]);
}
?>

