<?php
session_start();
require_once 'config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function login($email, $password) {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        // Update last login
        $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $update->execute([$user['user_id']]);
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: /login.php');
    exit;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /pages/dynamic/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied');
    }
}
?>
