<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Hanya manager yang bisa menghapus user
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

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit();
}

$currentUser = getCurrentUser();

// Cek user yang akan dihapus
$userToDelete = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
if (!$userToDelete) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Tidak bisa menghapus diri sendiri
if ($userId == $currentUser['id']) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete yourself']);
    exit();
}

try {
    // Begin transaction
    $pdo = getConnection();
    $pdo->beginTransaction();
    
    // 1. Update tasks yang dibuat oleh user ini - set created_by ke NULL
    executeQuery("UPDATE tasks SET created_by = NULL WHERE created_by = ?", [$userId]);
    
    // 2. Update tasks yang ditugaskan ke user ini - set assigned_to ke NULL
    executeQuery("UPDATE tasks SET assigned_to = NULL WHERE assigned_to = ?", [$userId]);
    
    // 3. Hapus user
    executeQuery("DELETE FROM users WHERE id = ?", [$userId]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'User berhasil dihapus.'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction jika ada error
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal menghapus user: ' . $e->getMessage()
    ]);
}
?> 