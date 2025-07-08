<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

// Pastikan hanya user yang bisa akses
if (!isUser()) {
    header('Location: manager-dashboard.php');
    exit();
}

$pageTitle = 'Dashboard User';
$currentUserId = getCurrentUser()['id'];

// Ambil statistik tasks user
$myTotalTasks = fetchOne("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ?", [$currentUserId])['count'];
$myCompletedTasks = fetchOne("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ? AND status = 'completed'", [$currentUserId])['count'];
$myPendingTasks = fetchOne("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ? AND status = 'pending'", [$currentUserId])['count'];
$myInProgressTasks = fetchOne("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ? AND status = 'in_progress'", [$currentUserId])['count'];

// Ambil tasks yang sedang dalam progress
$currentTasks = fetchAll("
    SELECT t.*, u.full_name as created_by_name 
    FROM tasks t 
    LEFT JOIN users u ON t.created_by = u.id 
    WHERE t.assigned_to = ? AND t.status IN ('pending', 'in_progress') 
    ORDER BY t.priority DESC, t.due_date ASC
", [$currentUserId]);

// Ambil tasks yang sudah selesai (5 terbaru)
$completedTasks = fetchAll("
    SELECT t.*, u.full_name as created_by_name 
    FROM tasks t 
    LEFT JOIN users u ON t.created_by = u.id 
    WHERE t.assigned_to = ? AND t.status = 'completed' 
    ORDER BY t.completed_at DESC 
    LIMIT 5
", [$currentUserId]);

// Ambil tasks dengan deadline terdekat
$upcomingDeadlines = fetchAll("
    SELECT t.*, u.full_name as created_by_name 
    FROM tasks t 
    LEFT JOIN users u ON t.created_by = u.id 
    WHERE t.assigned_to = ? AND t.status != 'completed' AND t.due_date IS NOT NULL 
    ORDER BY t.due_date ASC 
    LIMIT 5
", [$currentUserId]);

include 'includes/header.php';
?>

<!-- Dashboard Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Tasks -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-tasks text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Tasks</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $myTotalTasks; ?></p>
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
                <p class="text-2xl font-bold text-gray-900"><?php echo $myCompletedTasks; ?></p>
            </div>
        </div>
    </div>

    <!-- In Progress Tasks -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-spinner text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Sedang Dikerjakan</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $myInProgressTasks; ?></p>
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
                <p class="text-2xl font-bold text-gray-900"><?php echo $myPendingTasks; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Current Tasks -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Tasks Aktif</h3>
                <a href="my-tasks.php" class="text-primary hover:text-blue-700 text-sm font-medium">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="p-6">
            <?php if (empty($currentTasks)): ?>
                <p class="text-gray-500 text-center py-4">Tidak ada tasks aktif.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($currentTasks as $task): ?>
                    <div class="p-4 bg-gray-50 rounded-lg border-l-4 
                        <?php 
                        echo $task['priority'] == 'urgent' ? 'border-red-500' : 
                            ($task['priority'] == 'high' ? 'border-orange-500' : 
                            ($task['priority'] == 'medium' ? 'border-yellow-500' : 'border-gray-500'));
                        ?>">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h4>
                                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars(substr($task['description'], 0, 100)); ?>...</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    Dibuat oleh: <?php echo $task['created_by_name']; ?>
                                </p>
                            </div>
                            <div class="ml-4">
                                <select onchange="updateTaskStatus(<?php echo $task['id']; ?>, this.value)" 
                                    class="text-xs border border-gray-300 rounded px-2 py-1">
                                    <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php 
                                    echo $task['priority'] == 'urgent' ? 'bg-red-100 text-red-800' : 
                                        ($task['priority'] == 'high' ? 'bg-orange-100 text-orange-800' : 
                                        ($task['priority'] == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'));
                                    ?>">
                                    <?php echo ucfirst($task['priority']); ?>
                                </span>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php 
                                    echo $task['status'] == 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800';
                                    ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                </span>
                            </div>
                            <?php if ($task['due_date']): ?>
                            <span class="text-xs text-gray-500">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <?php echo date('d M Y', strtotime($task['due_date'])); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-clock text-orange-500 mr-2"></i>
                Deadline Terdekat
            </h3>
        </div>
        <div class="p-6">
            <?php if (empty($upcomingDeadlines)): ?>
                <p class="text-gray-500 text-center py-4">Tidak ada deadline terdekat.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($upcomingDeadlines as $task): 
                        $daysLeft = ceil((strtotime($task['due_date']) - time()) / (60 * 60 * 24));
                        $isOverdue = $daysLeft < 0;
                        $isUrgent = $daysLeft <= 3 && $daysLeft >= 0;
                    ?>
                    <div class="p-4 rounded-lg <?php echo $isOverdue ? 'bg-red-50 border border-red-200' : ($isUrgent ? 'bg-orange-50 border border-orange-200' : 'bg-gray-50'); ?>">
                        <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h4>
                        <p class="text-sm text-gray-600 mt-1">
                            Dibuat oleh: <?php echo $task['created_by_name']; ?>
                        </p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs <?php echo $isOverdue ? 'text-red-600' : ($isUrgent ? 'text-orange-600' : 'text-gray-600'); ?>">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <?php 
                                if ($isOverdue) {
                                    echo 'Terlambat ' . abs($daysLeft) . ' hari';
                                } elseif ($daysLeft == 0) {
                                    echo 'Hari ini';
                                } elseif ($daysLeft == 1) {
                                    echo 'Besok';
                                } else {
                                    echo $daysLeft . ' hari lagi';
                                }
                                ?>
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
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Completed Tasks -->
<?php if (!empty($completedTasks)): ?>
<div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-2"></i>
            Tasks Selesai Terbaru
        </h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($completedTasks as $task): ?>
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h4>
                <p class="text-sm text-gray-600 mt-1">
                    Dibuat oleh: <?php echo $task['created_by_name']; ?>
                </p>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-green-600">
                        <i class="fas fa-check mr-1"></i>
                        Selesai: <?php echo date('d M Y', strtotime($task['completed_at'])); ?>
                    </span>
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                        Completed
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?> 