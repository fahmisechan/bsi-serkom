<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

// Pastikan hanya manager yang bisa akses
if (!isManager()) {
    header('Location: user-dashboard.php');
    exit();
}

$taskId = $_GET['id'] ?? null;
if (!$taskId) {
    header('Location: all-tasks.php');
    exit();
}

$pageTitle = 'Edit Task';
$success = '';
$error = '';

// Ambil data task
$task = fetchOne("SELECT * FROM tasks WHERE id = ?", [$taskId]);
if (!$task) {
    header('Location: all-tasks.php');
    exit();
}

// Ambil daftar users untuk assignment
$users = fetchAll("SELECT id, full_name, username FROM users WHERE role = 'user' AND status = 'active' ORDER BY full_name");

if ($_POST) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $assigned_to = $_POST['assigned_to'] ?: null;
    $due_date = $_POST['due_date'] ?: null;
    
    // Validasi
    if (empty($title)) {
        $error = 'Judul task harus diisi!';
    } elseif (empty($description)) {
        $error = 'Deskripsi task harus diisi!';
    } else {
        try {
            $completedAt = ($status === 'completed' && $task['status'] !== 'completed') ? date('Y-m-d H:i:s') : ($status !== 'completed' ? null : $task['completed_at']);
            
            executeQuery(
                "UPDATE tasks SET title = ?, description = ?, priority = ?, status = ?, assigned_to = ?, due_date = ?, completed_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$title, $description, $priority, $status, $assigned_to, $due_date, $completedAt, $taskId]
            );
            
            $success = 'Task berhasil diupdate!';
            
            // Refresh data task
            $task = fetchOne("SELECT * FROM tasks WHERE id = ?", [$taskId]);
        } catch (Exception $e) {
            $error = 'Gagal mengupdate task: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Success/Error Messages -->
    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 alert-auto-hide">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo $success; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?php echo $error; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Edit Task Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-edit text-primary mr-2"></i>
                    Edit Task
                </h3>
                <a href="all-tasks.php" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Kembali ke Daftar Tasks
                </a>
            </div>
        </div>
        
        <form method="POST" action="" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Judul Task <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Masukkan judul task"
                            value="<?php echo htmlspecialchars($task['title']); ?>"
                            required
                        >
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="6"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Masukkan deskripsi detail task"
                            required
                        ><?php echo htmlspecialchars($task['description']); ?></textarea>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Priority -->
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Prioritas
                        </label>
                        <select 
                            id="priority" 
                            name="priority" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option value="low" <?php echo $task['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo $task['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo $task['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="urgent" <?php echo $task['priority'] == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $task['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <!-- Assigned To -->
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">
                            Ditugaskan Kepada
                        </label>
                        <select 
                            id="assigned_to" 
                            name="assigned_to" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option value="">Pilih User</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo $task['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['full_name']) . ' (' . $user['username'] . ')'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Deadline
                        </label>
                        <input 
                            type="date" 
                            id="due_date" 
                            name="due_date" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            value="<?php echo $task['due_date']; ?>"
                        >
                    </div>
                </div>
            </div>

            <!-- Task Info -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Informasi Task:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <strong>ID:</strong> #<?php echo $task['id']; ?>
                    </div>
                    <div>
                        <strong>Dibuat:</strong> <?php echo date('d M Y H:i', strtotime($task['created_at'])); ?>
                    </div>
                    <?php if ($task['completed_at']): ?>
                    <div class="md:col-span-2">
                        <strong>Diselesaikan:</strong> 
                        <span class="text-green-600"><?php echo date('d M Y H:i', strtotime($task['completed_at'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-100">
                <a href="all-tasks.php" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                
                <div class="space-x-3">
                    <button 
                        type="button" 
                        onclick="if(confirmDelete('Apakah Anda yakin ingin menghapus task ini?')) { deleteTask(<?php echo $task['id']; ?>); }"
                        class="px-6 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 font-medium"
                    >
                        <i class="fas fa-trash mr-2"></i>
                        Hapus Task
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 font-medium"
                    >
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function deleteTask(taskId) {
    fetch('ajax/delete-task.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Task berhasil dihapus!');
            window.location.href = 'all-tasks.php';
        } else {
            showNotification('Gagal menghapus task!', 'error');
        }
    })
    .catch(error => {
        showNotification('Terjadi kesalahan!', 'error');
    });
}
</script>

<?php include 'includes/footer.php'; ?> 