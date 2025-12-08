<?php
/**
 * IP Management API
 * Handles IP whitelist/blacklist operations
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if ($method === 'GET') {
        // Get IP list
        $type = $_GET['type'] ?? 'all';
        $where = '';
        $params = [];
        
        if ($type !== 'all') {
            $where = 'WHERE ip_type = ?';
            $params[] = $type;
        }
        
        $stmt = $conn->prepare("
            SELECT id, ip_address, ip_type, description, created_at, updated_at
            FROM ip_management
            $where
            ORDER BY created_at DESC
        ");
        $stmt->execute($params);
        $ips = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $ips]);
        
    } elseif ($method === 'POST') {
        // Add IP
        $input = json_decode(file_get_contents('php://input'), true);
        
        $ipAddress = trim($input['ip_address'] ?? '');
        $ipType = $input['ip_type'] ?? 'blacklist';
        $description = trim($input['description'] ?? '');
        
        if (empty($ipAddress)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'IP address is required']);
            exit;
        }
        
        // Validate IP format
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP) && !preg_match('/^(\d{1,3}\.){3}\d{1,3}\/\d{1,2}$/', $ipAddress)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid IP address format']);
            exit;
        }
        
        // Check if IP already exists
        $stmt = $conn->prepare("SELECT id FROM ip_management WHERE ip_address = ?");
        $stmt->execute([$ipAddress]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'IP address already exists']);
            exit;
        }
        
        // Insert IP
        $stmt = $conn->prepare("
            INSERT INTO ip_management (ip_address, ip_type, description, created_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$ipAddress, $ipType, $description, $_SESSION['admin_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'IP address added successfully',
            'data' => ['id' => $conn->lastInsertId()]
        ]);
        
    } elseif ($method === 'DELETE') {
        // Delete IP
        $input = json_decode(file_get_contents('php://input'), true);
        $ipId = $input['id'] ?? 0;
        
        if ($ipId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid IP ID']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM ip_management WHERE id = ?");
        $stmt->execute([$ipId]);
        
        echo json_encode(['success' => true, 'message' => 'IP address removed successfully']);
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (PDOException $e) {
    error_log("IP Management API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("IP Management API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>

