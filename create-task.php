<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

// Pastikan hanya manager yang bisa akses
if (!isManager()) {
    header('Location: user-dashboard.php');
    exit();
}

$pageTitle = 'Buat Task Baru';
$success = '';
$error = '';

// Ambil daftar users untuk assignment
$users = fetchAll("SELECT id, full_name, username FROM users WHERE role = 'user' AND status = 'active' ORDER BY full_name");

if ($_POST) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $priority = $_POST['priority'];
    $assigned_to = $_POST['assigned_to'] ?: null;
    $due_date = $_POST['due_date'] ?: null;
    $created_by = getCurrentUser()['id'];
    
    // Validasi
    if (empty($title)) {
        $error = 'Judul task harus diisi!';
    } elseif (empty($description)) {
        $error = 'Deskripsi task harus diisi!';
    } else {
        try {
            executeQuery(
                "INSERT INTO tasks (title, description, priority, assigned_to, created_by, due_date) VALUES (?, ?, ?, ?, ?, ?)",
                [$title, $description, $priority, $assigned_to, $created_by, $due_date]
            );
            
            $success = 'Task berhasil dibuat!';
            
            // Reset form
            $_POST = array();
        } catch (Exception $e) {
            $error = 'Gagal membuat task: ' . $e->getMessage();
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

    <!-- Create Task Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-plus-circle text-primary mr-2"></i>
                Buat Task Baru
            </h3>
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
                            value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
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
                        ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
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
                            <option value="low" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo (!isset($_POST['priority']) || $_POST['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                            <option value="urgent" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'urgent') ? 'selected' : ''; ?>>Urgent</option>
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
                            <option value="<?php echo $user['id']; ?>" <?php echo (isset($_POST['assigned_to']) && $_POST['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
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
                            value="<?php echo isset($_POST['due_date']) ? $_POST['due_date'] : ''; ?>"
                            min="<?php echo date('Y-m-d'); ?>"
                        >
                    </div>

                    <!-- Priority Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Informasi Prioritas:</h4>
                        <div class="space-y-1 text-xs text-gray-600">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-gray-400 rounded-full mr-2"></span>
                                <strong>Low:</strong> Tidak mendesak
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-yellow-400 rounded-full mr-2"></span>
                                <strong>Medium:</strong> Prioritas normal
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-orange-400 rounded-full mr-2"></span>
                                <strong>High:</strong> Penting
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-red-400 rounded-full mr-2"></span>
                                <strong>Urgent:</strong> Sangat mendesak
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-100">
                <a href="manager-dashboard.php" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Dashboard
                </a>
                
                <div class="space-x-3">
                    <button 
                        type="reset" 
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium"
                    >
                        Reset
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 font-medium"
                    >
                        <i class="fas fa-plus mr-2"></i>
                        Buat Task
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 