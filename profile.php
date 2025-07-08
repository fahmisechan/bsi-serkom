<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$pageTitle = 'Profile';
$success = '';
$error = '';
$currentUser = getCurrentUser();

if ($_POST) {
    $action = $_POST['action'];
    
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        
        if (empty($full_name) || empty($email)) {
            $error = 'Nama lengkap dan email harus diisi!';
        } else {
            // Cek email unik (kecuali email sendiri)
            $existingUser = fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $currentUser['id']]);
            if ($existingUser) {
                $error = 'Email sudah digunakan user lain!';
            } else {
                try {
                    executeQuery(
                        "UPDATE users SET full_name = ?, email = ? WHERE id = ?",
                        [$full_name, $email, $currentUser['id']]
                    );
                    
                    // Update session
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['email'] = $email;
                    
                    $success = 'Profile berhasil diupdate!';
                } catch (Exception $e) {
                    $error = 'Gagal mengupdate profile!';
                }
            }
        }
    }
    
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Semua field password harus diisi!';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password baru minimal 6 karakter!';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Konfirmasi password tidak cocok!';
        } else {
            // Verifikasi password lama
            $user = fetchOne("SELECT password FROM users WHERE id = ?", [$currentUser['id']]);
            if (!password_verify($current_password, $user['password'])) {
                $error = 'Password lama salah!';
            } else {
                try {
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    executeQuery("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $currentUser['id']]);
                    $success = 'Password berhasil diubah!';
                } catch (Exception $e) {
                    $error = 'Gagal mengubah password!';
                }
            }
        }
    }
}

// Ambil data user terbaru
$userData = fetchOne("SELECT * FROM users WHERE id = ?", [$currentUser['id']]);

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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
                <div class="h-20 w-20 bg-primary rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
                    <?php echo strtoupper(substr($userData['full_name'], 0, 1)); ?>
                </div>
                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($userData['full_name']); ?></h3>
                <p class="text-gray-600">@<?php echo htmlspecialchars($userData['username']); ?></p>
                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full mt-2">
                    <?php echo ucfirst($userData['role']); ?>
                </span>
                
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <div class="text-sm text-gray-600">
                        <p><strong>Bergabung:</strong> <?php echo date('d M Y', strtotime($userData['created_at'])); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="<?php echo $userData['status'] === 'active' ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo ucfirst($userData['status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Forms -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Update Profile -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-user-edit text-primary mr-2"></i>
                        Update Profile
                    </h3>
                </div>
                
                <form method="POST" action="" class="p-6">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                Username
                            </label>
                            <input 
                                type="text" 
                                id="username" 
                                value="<?php echo htmlspecialchars($userData['username']); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500"
                                disabled
                            >
                            <p class="text-xs text-gray-500 mt-1">Username tidak dapat diubah</p>
                        </div>

                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                value="<?php echo htmlspecialchars($userData['full_name']); ?>"
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
                                value="<?php echo htmlspecialchars($userData['email']); ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button 
                            type="submit" 
                            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 font-medium"
                        >
                            <i class="fas fa-save mr-2"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-lock text-primary mr-2"></i>
                        Ubah Password
                    </h3>
                </div>
                
                <form method="POST" action="" class="p-6">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password Lama <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                required
                            >
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password Baru <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="new_password" 
                                name="new_password" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                minlength="6"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Konfirmasi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                minlength="6"
                                required
                            >
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button 
                            type="submit" 
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium"
                        >
                            <i class="fas fa-key mr-2"></i>
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 