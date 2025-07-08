<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

// Pastikan hanya user yang bisa akses
if (!isUser()) {
    header('Location: manager-dashboard.php');
    exit();
}

$pageTitle = 'Tasks Saya';
$currentUserId = getCurrentUser()['id'];

// Filter
$status_filter = $_GET['status'] ?? 'all';
$priority_filter = $_GET['priority'] ?? 'all';

// Build query berdasarkan filter
$whereConditions = ["t.assigned_to = ?"];
$params = [$currentUserId];

if ($status_filter !== 'all') {
    $whereConditions[] = "t.status = ?";
    $params[] = $status_filter;
}

if ($priority_filter !== 'all') {
    $whereConditions[] = "t.priority = ?";
    $params[] = $priority_filter;
}

$whereClause = implode(' AND ', $whereConditions);

// Ambil tasks berdasarkan filter
$tasks = fetchAll("
    SELECT t.*, u.full_name as created_by_name 
    FROM tasks t 
    LEFT JOIN users u ON t.created_by = u.id 
    WHERE {$whereClause}
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

include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <!-- Header dengan Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 md:mb-0">
                    <i class="fas fa-tasks text-primary mr-2"></i>
                    Tasks Saya
                </h3>
                
                <!-- Filters -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <select 
                        id="status-filter" 
                        onchange="applyFilters()" 
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    
                    <select 
                        id="priority-filter" 
                        onchange="applyFilters()" 
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                        <option value="all" <?php echo $priority_filter === 'all' ? 'selected' : ''; ?>>Semua Prioritas</option>
                        <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <?php
                $stats = [
                    'total' => count($tasks),
                    'pending' => count(array_filter($tasks, fn($t) => $t['status'] === 'pending')),
                    'in_progress' => count(array_filter($tasks, fn($t) => $t['status'] === 'in_progress')),
                    'completed' => count(array_filter($tasks, fn($t) => $t['status'] === 'completed'))
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
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <?php if (empty($tasks)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
        <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada tasks</h3>
        <p class="text-gray-600">Belum ada tasks yang sesuai dengan filter yang dipilih.</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($tasks as $task): 
            $daysLeft = $task['due_date'] ? ceil((strtotime($task['due_date']) - time()) / (60 * 60 * 24)) : null;
            $isOverdue = $daysLeft !== null && $daysLeft < 0;
            $isUrgent = $daysLeft !== null && $daysLeft <= 3 && $daysLeft >= 0;
        ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <!-- Task Info -->
                <div class="flex-1 mb-4 lg:mb-0">
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="text-lg font-semibold text-gray-900 pr-4"><?php echo htmlspecialchars($task['title']); ?></h4>
                        
                        <!-- Priority Badge -->
                        <span class="px-2 py-1 text-xs rounded-full whitespace-nowrap
                            <?php 
                            echo $task['priority'] == 'urgent' ? 'bg-red-100 text-red-800' : 
                                ($task['priority'] == 'high' ? 'bg-orange-100 text-orange-800' : 
                                ($task['priority'] == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'));
                            ?>">
                            <?php echo ucfirst($task['priority']); ?>
                        </span>
                    </div>
                    
                    <p class="text-gray-600 mb-3"><?php echo htmlspecialchars($task['description']); ?></p>
                    
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span><i class="fas fa-user mr-1"></i>Dibuat oleh: <?php echo $task['created_by_name']; ?></span>
                        <span><i class="fas fa-calendar-plus mr-1"></i>Dibuat: <?php echo date('d M Y', strtotime($task['created_at'])); ?></span>
                        <?php if ($task['due_date']): ?>
                        <span class="<?php echo $isOverdue ? 'text-red-600' : ($isUrgent ? 'text-orange-600' : ''); ?>">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Deadline: <?php echo date('d M Y', strtotime($task['due_date'])); ?>
                            <?php if ($isOverdue): ?>
                                (Terlambat <?php echo abs($daysLeft); ?> hari)
                            <?php elseif ($isUrgent): ?>
                                (<?php echo $daysLeft == 0 ? 'Hari ini' : $daysLeft . ' hari lagi'; ?>)
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Task Actions -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <!-- Status Selector -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Status:</label>
                        <select 
                            onchange="updateTaskStatus(<?php echo $task['id']; ?>, this.value)" 
                            class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            <?php echo $task['status'] === 'cancelled' ? 'disabled' : ''; ?>
                        >
                            <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    
                    <!-- Status Badge -->
                    <span class="px-3 py-1 text-xs rounded-full
                        <?php 
                        echo $task['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                            ($task['status'] == 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                            ($task['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'));
                        ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($task['completed_at']): ?>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <span class="text-sm text-green-600">
                    <i class="fas fa-check-circle mr-1"></i>
                    Diselesaikan pada: <?php echo date('d M Y H:i', strtotime($task['completed_at'])); ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function applyFilters() {
    const status = document.getElementById('status-filter').value;
    const priority = document.getElementById('priority-filter').value;
    
    const url = new URL(window.location);
    url.searchParams.set('status', status);
    url.searchParams.set('priority', priority);
    
    window.location.href = url.toString();
}
</script>

<?php include 'includes/footer.php'; ?> 