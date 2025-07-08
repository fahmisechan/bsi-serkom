<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Hanya manager yang bisa menghapus task
if (!isManager()) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$taskId = $input['task_id'] ?? null;

if (!$taskId) {
    echo json_encode(['success' => false, 'message' => 'Task ID required']);
    exit();
}

// Cek apakah task ada
$task = fetchOne("SELECT * FROM tasks WHERE id = ?", [$taskId]);
if (!$task) {
    echo json_encode(['success' => false, 'message' => 'Task not found']);
    exit();
}

try {
    // Begin transaction
    $pdo = getConnection();
    $pdo->beginTransaction();
    
    // 1. Hapus task
    executeQuery("DELETE FROM tasks WHERE id = ?", [$taskId]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Task "' . $task['title'] . '" berhasil dihapus'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction jika ada error
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal menghapus task: ' . $e->getMessage()
    ]);
}
?> 