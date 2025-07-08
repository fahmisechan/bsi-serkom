<?php
// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk memeriksa apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk mendapatkan data user yang sedang login
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role'],
            'full_name' => $_SESSION['full_name']
        ];
    }
    return null;
}

// Fungsi untuk login user
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
}

// Fungsi untuk logout user
function logoutUser() {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Fungsi untuk redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Fungsi untuk memeriksa role user
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

// Fungsi untuk memeriksa apakah user adalah manager
function isManager() {
    return hasRole('manager');
}

// Fungsi untuk memeriksa apakah user adalah user biasa
function isUser() {
    return hasRole('user');
}

// Fungsi untuk redirect berdasarkan role
function redirectBasedOnRole() {
    if (isManager()) {
        header('Location: manager-dashboard.php');
    } else {
        header('Location: user-dashboard.php');
    }
    exit();
}
?> 