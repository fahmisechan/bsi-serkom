<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

// Pastikan hanya manager yang bisa akses
if (!isManager()) {
    header('Location: user-dashboard.php');
    exit();
}

$pageTitle = 'Semua Tasks';

// Filter
$status_filter = $_GET['status'] ?? 'all';
$priority_filter = $_GET['priority'] ?? 'all';
$user_filter = $_GET['user'] ?? 'all';

// Build query berdasarkan filter
$whereConditions = [];
$params = [];

if ($status_filter !== 'all') {
    $whereConditions[] = "t.status = ?";
    $params[] = $status_filter;
}

if ($priority_filter !== 'all') {
    $whereConditions[] = "t.priority = ?";
    $params[] = $priority_filter;
}

if ($user_filter !== 'all') {
    $whereConditions[] = "t.assigned_to = ?";
    $params[] = $user_filter;
}

$whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);

// Ambil tasks berdasarkan filter
$tasks = fetchAll("
    SELECT t.*, 
           u1.full_name as assigned_name,
           u2.full_name as created_by_name 
    FROM tasks t 
    LEFT JOIN users u1 ON t.assigned_to = u1.id 
    LEFT JOIN users u2 ON t.created_by = u2.id 
    {$whereClause}
    ORDER BY 
        CASE t.priority 
            WHEN 'urgent' THEN 1 
            WHEN 'high' THEN 2 
            WHEN 'medium' THEN 3 
            WHEN 'low' THEN 4 
        END,
        t.due_date ASC,
        t.created_at DESC
", $params);

// Ambil daftar users untuk filter
$users = fetchAll("SELECT id, full_name FROM users WHERE role = 'user' AND status = 'active' ORDER BY full_name");

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Header dengan Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 lg:mb-0">
                    <i class="fas fa-list-check text-primary mr-2"></i>
                    Semua Tasks
                </h3>
                
                <!-- Filters -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <select id="status-filter" onchange="applyFilters()" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    
                    <select id="priority-filter" onchange="applyFilters()" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="all" <?php echo $priority_filter === 'all' ? 'selected' : ''; ?>>Semua Prioritas</option>
                        <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                    
                    <select id="user-filter" onchange="applyFilters()" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="all" <?php echo $user_filter === 'all' ? 'selected' : ''; ?>>Semua User</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['full_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                <?php
                $stats = [
                    'total' => count($tasks),
                    'pending' => count(array_filter($tasks, fn($t) => $t['status'] === 'pending')),
                    'in_progress' => count(array_filter($tasks, fn($t) => $t['status'] === 'in_progress')),
                    'completed' => count(array_filter($tasks, fn($t) => $t['status'] === 'completed')),
                    'cancelled' => count(array_filter($tasks, fn($t) => $t['status'] === 'cancelled'))
                ];
                ?>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900"><?php echo $stats['total']; ?></div>
                    <div class="text-sm text-gray-600">Total</div>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></div>
                    <div class="text-sm text-yellow-600">Pending</div>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600"><?php echo $stats['in_progress']; ?></div>
                    <div class="text-sm text-blue-600">In Progress</div>
                </div>
                <div class="p-3 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600"><?php echo $stats['completed']; ?></div>
                    <div class="text-sm text-green-600">Completed</div>
                </div>
                <div class="p-3 bg-red-50 rounded-lg">
                    <div class="text-2xl font-bold text-red-600"><?php echo $stats['cancelled']; ?></div>
                    <div class="text-sm text-red-600">Cancelled</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action -->
    <div class="mb-6">
        <a href="create-task.php" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 font-medium">
            <i class="fas fa-plus mr-2"></i>
            Buat Task Baru
        </a>
    </div>

    <!-- Tasks Table -->
    <?php if (empty($tasks)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
        <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada tasks</h3>
        <p class="text-gray-600">Belum ada tasks yang sesuai dengan filter yang dipilih.</p>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($tasks as $task): 
                        $daysLeft = $task['due_date'] ? ceil((strtotime($task['due_date']) - time()) / (60 * 60 * 24)) : null;
                        $isOverdue = $daysLeft !== null && $daysLeft < 0;
                        $isUrgent = $daysLeft !== null && $daysLeft <= 3 && $daysLeft >= 0;
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($task['description'], 0, 100)); ?>...</div>
                                <div class="text-xs text-gray-400 mt-1">Dibuat oleh: <?php echo $task['created_by_name']; ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $task['assigned_name'] ?: 'Belum ditugaskan'; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                <?php 
                                echo $task['priority'] == 'urgent' ? 'bg-red-100 text-red-800' : 
                                    ($task['priority'] == 'high' ? 'bg-orange-100 text-orange-800' : 
                                    ($task['priority'] == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'));
                                ?>">
                                <?php echo ucfirst($task['priority']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select onchange="updateTaskStatus(<?php echo $task['id']; ?>, this.value)" class="text-xs border border-gray-300 rounded px-2 py-1">
                                <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $task['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($task['due_date']): ?>
                            <div class="text-sm <?php echo $isOverdue ? 'text-red-600' : ($isUrgent ? 'text-orange-600' : 'text-gray-900'); ?>">
                                <?php echo date('d M Y', strtotime($task['due_date'])); ?>
                                <?php if ($isOverdue): ?>
                                    <br><span class="text-xs">(Terlambat <?php echo abs($daysLeft); ?> hari)</span>
                                <?php elseif ($isUrgent): ?>
                                    <br><span class="text-xs">(<?php echo $daysLeft == 0 ? 'Hari ini' : $daysLeft . ' hari lagi'; ?>)</span>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <span class="text-sm text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="edit-task.php?id=<?php echo $task['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <button onclick="deleteTask(<?php echo $task['id']; ?>)" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function applyFilters() {
    const status = document.getElementById('status-filter').value;
    const priority = document.getElementById('priority-filter').value;
    const user = document.getElementById('user-filter').value;
    
    const url = new URL(window.location);
    url.searchParams.set('status', status);
    url.searchParams.set('priority', priority);
    url.searchParams.set('user', user);
    
    window.location.href = url.toString();
}

function deleteTask(taskId) {
    if (confirmDelete('Apakah Anda yakin ingin menghapus task ini?')) {
        fetch('ajax/delete-task.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ task_id: taskId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Task berhasil dihapus!');
                location.reload();
            } else {
                showNotification('Gagal menghapus task!', 'error');
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?> 