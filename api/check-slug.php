<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/StoreResolver.php';

// בדיקת method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// קריאת JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['slug']) || empty($input['slug'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Slug is required']);
    exit;
}

$slug = trim($input['slug']);

// בדיקת תקינות slug
if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
    echo json_encode(['available' => false, 'reason' => 'invalid_format']);
    exit;
}

// בדיקת אורך מינימלי
if (strlen($slug) < 3) {
    echo json_encode(['available' => false, 'reason' => 'too_short']);
    exit;
}

try {
    $isAvailable = StoreResolver::isSlugAvailable($slug);
    
    echo json_encode([
        'available' => $isAvailable,
        'slug' => $slug
    ]);
    
} catch (Exception $e) {
    error_log("Error checking slug availability: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?> 