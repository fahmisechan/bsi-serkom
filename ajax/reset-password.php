<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Hanya manager yang bisa reset password user lain
if (!isManager()) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['user_id'] ?? null;
$newPassword = $input['new_password'] ?? null;

if (!$userId || !$newPassword) {
    echo json_encode(['success' => false, 'message' => 'User ID and new password required']);
    exit();
}

// Validasi password
if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
    exit();
}

$currentUser = getCurrentUser();

// Cek user yang akan direset passwordnya
$userToReset = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
if (!$userToReset) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

try {
    // Hash password baru
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    executeQuery("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $userId]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Password user ' . $userToReset['username'] . ' berhasil direset'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal reset password: ' . $e->getMessage()
    ]);
}
?> 