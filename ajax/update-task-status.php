<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$taskId = $input['task_id'] ?? null;
$status = $input['status'] ?? null;

if (!$taskId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$currentUser = getCurrentUser();

// Cek apakah user punya akses untuk mengupdate task ini
$task = fetchOne("SELECT * FROM tasks WHERE id = ?", [$taskId]);
if (!$task) {
    echo json_encode(['success' => false, 'message' => 'Task not found']);
    exit();
}

// Manager bisa update semua task, user hanya bisa update task yang ditugaskan kepadanya
if (!isManager() && $task['assigned_to'] != $currentUser['id']) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

try {
    $completedAt = ($status === 'completed') ? date('Y-m-d H:i:s') : null;
    
    executeQuery(
        "UPDATE tasks SET status = ?, completed_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
        [$status, $completedAt, $taskId]
    );
    
    echo json_encode(['success' => true, 'message' => 'Task status updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
}
?> 