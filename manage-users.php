<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

// Pastikan hanya manager yang bisa akses
if (!isManager()) {
    header('Location: user-dashboard.php');
    exit();
}

$pageTitle = 'Kelola Users';
$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action === 'create') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        
        // Validasi
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            $error = 'Semua field harus diisi!';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter!';
        } else {
            // Cek username dan email unik
            $existingUser = fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
            if ($existingUser) {
                $error = 'Username atau email sudah digunakan!';
            } else {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    executeQuery(
                        "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)",
                        [$username, $email, $hashedPassword, $full_name, $role]
                    );
                    $success = 'User berhasil dibuat!';
                } catch (Exception $e) {
                    $error = 'Gagal membuat user: ' . $e->getMessage();
                }
            }
        }
    }
    
    if ($action === 'update_status') {
        $userId = $_POST['user_id'];
        $status = $_POST['status'];
        
        try {
            executeQuery("UPDATE users SET status = ? WHERE id = ?", [$status, $userId]);
            $success = 'Status user berhasil diupdate!';
        } catch (Exception $e) {
            $error = 'Gagal mengupdate status user!';
        }
    }
}

// Ambil semua users
$users = fetchAll("SELECT * FROM users ORDER BY created_at DESC");

include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto">
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

    <!-- Create User Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-user-plus text-primary mr-2"></i>
                Tambah User Baru
            </h3>
        </div>
        
        <form method="POST" action="" class="p-6">
            <input type="hidden" name="action" value="create">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Masukkan username"
                            required
                        >
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Masukkan email"
                            required
                        >
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Masukkan nama lengkap"
                            required
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Minimal 6 karakter"
                            required
                            minlength="6"
                        >
                    </div>
                </div>

                <!-- Role Selection -->
                <div class="md:col-span-2">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Role
                    </label>
                    <select 
                        id="role" 
                        name="role" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="user">User</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 font-medium"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Tambah User
                </button>
            </div>
        </form>
    </div>

    <!-- Users List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-users text-primary mr-2"></i>
                Daftar Users
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): 
                        $userTasks = fetchOne("SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                            FROM tasks WHERE assigned_to = ?", [$user['id']]);
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-primary rounded-full flex items-center justify-center text-white font-medium mr-4">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['username']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                <?php echo $user['role'] === 'manager' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select 
                                    name="status" 
                                    onchange="this.form.submit()"
                                    class="text-xs border border-gray-300 rounded px-2 py-1 <?php echo $user['status'] === 'active' ? 'text-green-600' : 'text-red-600'; ?>"
                                >
                                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                Total: <?php echo $userTasks['total']; ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                Selesai: <?php echo $userTasks['completed']; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <?php if ($user['id'] != getCurrentUser()['id']): ?>
                            <button 
                                onclick="resetPassword(<?php echo $user['id']; ?>)" 
                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                            >
                                Reset Password
                            </button>
                            <button 
                                onclick="deleteUser(<?php echo $user['id']; ?>)" 
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                            <?php else: ?>
                            <span class="text-gray-400">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function resetPassword(userId) {
    const newPassword = prompt('Masukkan password baru (minimal 6 karakter):');
    if (newPassword && newPassword.length >= 6) {
        fetch('ajax/reset-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, new_password: newPassword })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Password berhasil direset!');
            } else {
                showNotification('Gagal reset password!', 'error');
            }
        });
    } else if (newPassword !== null) {
        alert('Password minimal 6 karakter!');
    }
}

function deleteUser(userId) {
    if (confirmDelete('Apakah Anda yakin ingin menghapus user ini? Semua data terkait akan ikut terhapus.')) {
        fetch('ajax/delete-user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('User berhasil dihapus!');
                location.reload();
            } else {
                showNotification('Gagal menghapus user!', 'error');
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?> 