<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Task Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#64748b',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php if (isLoggedIn()): ?>
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="flex items-center justify-center h-16 bg-primary text-white">
                <i class="fas fa-tasks mr-2"></i>
                <h1 class="text-xl font-bold">Task Manager</h1>
            </div>
            
            <nav class="mt-6">
                <!-- Navigation untuk Manager -->
                <?php if (isManager()): ?>
                <div class="px-4 py-2">
                    <a href="manager-dashboard.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manager-dashboard.php' ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-chart-pie mr-3"></i>
                        Dashboard
                    </a>
                </div>
                
                <div class="px-4 py-2">
                    <a href="all-tasks.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'all-tasks.php' ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-list-check mr-3"></i>
                        Semua Tasks
                    </a>
                </div>
                
                <div class="px-4 py-2">
                    <a href="create-task.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'create-task.php' ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-plus-circle mr-3"></i>
                        Buat Task Baru
                    </a>
                </div>
                
                <div class="px-4 py-2">
                    <a href="manage-users.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage-users.php' ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-users mr-3"></i>
                        Kelola Users
                    </a>
                </div>
                
                <!-- <div class="px-4 py-2">
                    <a href="reports.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-chart-bar mr-3"></i>
                        Laporan
                    </a>
                </div> -->
                
                <!-- Navigation untuk User -->
                <?php else: ?>
                <div class="px-4 py-2">
                    <a href="user-dashboard.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'user-dashboard.php' ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-home mr-3"></i>
                        Dashboard
                    </a>
                </div>
                
                <div class="px-4 py-2">
                    <a href="my-tasks.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'my-tasks.php' ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-tasks mr-3"></i>
                        Tasks Saya
                    </a>
                </div>
                
                <!-- <div class="px-4 py-2">
                    <a href="task-history.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'task-history.php' ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-history mr-3"></i>
                        Riwayat Tasks
                    </a>
                </div> -->
                <?php endif; ?>
                
                <!-- Menu Profile dan Logout untuk semua role -->
                <div class="px-4 py-2">
                    <a href="profile.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-user mr-3"></i>
                        Profile
                    </a>
                </div>
                
                <div class="px-4 py-2">
                    <a href="logout.php" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex justify-between items-center px-6 py-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">
                            <?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?>
                        </h2>
                        <p class="text-sm text-gray-600">
                            <?php echo isManager() ? 'Manager Panel' : 'User Panel'; ?>
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Notification Icon -->
                        <!-- <div class="relative">
                            <button class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-bell text-lg"></i>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                            </button>
                        </div> -->
                        
                        <!-- User Info -->
                        <div class="flex items-center space-x-2">
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-800"><?php echo getCurrentUser()['full_name']; ?></div>
                                <div class="text-xs text-gray-500 capitalize"><?php echo getCurrentUser()['role']; ?></div>
                            </div>
                            <div class="h-8 w-8 bg-primary rounded-full flex items-center justify-center text-white text-sm font-medium">
                                <?php echo strtoupper(substr(getCurrentUser()['full_name'], 0, 1)); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <?php endif; ?> 