<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

// Pastikan hanya manager yang bisa akses
if (!isManager()) {
    header('Location: user-dashboard.php');
    exit();
}

$pageTitle = 'Dashboard Manager';

// Ambil statistik
$totalUsers = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'user' AND status = 'active'")['count'];
$totalTasks = fetchOne("SELECT COUNT(*) as count FROM tasks")['count'];
$completedTasks = fetchOne("SELECT COUNT(*) as count FROM tasks WHERE status = 'completed'")['count'];
$pendingTasks = fetchOne("SELECT COUNT(*) as count FROM tasks WHERE status = 'pending'")['count'];
$inProgressTasks = fetchOne("SELECT COUNT(*) as count FROM tasks WHERE status = 'in_progress'")['count'];

// Ambil tasks terbaru
$recentTasks = fetchAll("
    SELECT t.*, u.full_name as assigned_name 
    FROM tasks t 
    LEFT JOIN users u ON t.assigned_to = u.id 
    ORDER BY t.created_at DESC 
    LIMIT 5
");

// Ambil tasks berdasarkan prioritas
$urgentTasks = fetchAll("
    SELECT t.*, u.full_name as assigned_name 
    FROM tasks t 
    LEFT JOIN users u ON t.assigned_to = u.id 
    WHERE t.priority = 'urgent' AND t.status != 'completed' 
    ORDER BY t.due_date ASC
");

include 'includes/header.php';
?>

<!-- Dashboard Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Users</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $totalUsers; ?></p>
            </div>
        </div>
    </div>

    <!-- Total Tasks -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-tasks text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Tasks</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $totalTasks; ?></p>
            </div>
        </div>
    </div>

    <!-- Completed Tasks -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Selesai</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $completedTasks; ?></p>
            </div>
        </div>
    </div>

    <!-- Pending Tasks -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $pendingTasks; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Tasks -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Tasks Terbaru</h3>
                <a href="all-tasks.php" class="text-primary hover:text-blue-700 text-sm font-medium">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="p-6">
            <?php if (empty($recentTasks)): ?>
                <p class="text-gray-500 text-center py-4">Belum ada tasks.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentTasks as $task): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h4>
                            <p class="text-sm text-gray-600 mt-1">
                                Ditugaskan ke: <?php echo $task['assigned_name'] ?: 'Belum ditugaskan'; ?>
                            </p>
                            <div class="flex items-center mt-2 space-x-2">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php 
                                    echo $task['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                        ($task['status'] == 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                        ($task['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'));
                                    ?>">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php 
                                    echo $task['priority'] == 'urgent' ? 'bg-red-100 text-red-800' : 
                                        ($task['priority'] == 'high' ? 'bg-orange-100 text-orange-800' : 
                                        ($task['priority'] == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'));
                                    ?>">
                                    <?php echo ucfirst($task['priority']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="text-right text-sm text-gray-500">
                            <?php echo date('d M Y', strtotime($task['created_at'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Urgent Tasks -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Tasks Urgent
            </h3>
        </div>
        <div class="p-6">
            <?php if (empty($urgentTasks)): ?>
                <p class="text-gray-500 text-center py-4">Tidak ada tasks urgent.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($urgentTasks as $task): ?>
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h4>
                        <p class="text-sm text-gray-600 mt-1">
                            Ditugaskan ke: <?php echo $task['assigned_name'] ?: 'Belum ditugaskan'; ?>
                        </p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-red-600">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Due: <?php echo $task['due_date'] ? date('d M Y', strtotime($task['due_date'])) : 'Tidak ada'; ?>
                            </span>
                            <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                                <?php echo ucfirst($task['status']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="create-task.php" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
            <i class="fas fa-plus-circle text-blue-600 text-xl mr-3"></i>
            <div>
                <p class="font-medium text-gray-900">Buat Task Baru</p>
                <p class="text-sm text-gray-600">Tambah task untuk tim</p>
            </div>
        </a>
        
        <a href="manage-users.php" class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
            <i class="fas fa-users text-green-600 text-xl mr-3"></i>
            <div>
                <p class="font-medium text-gray-900">Kelola Users</p>
                <p class="text-sm text-gray-600">Tambah/edit user</p>
            </div>
        </a>
        
    </div>
</div>

<?php include 'includes/footer.php'; ?> 