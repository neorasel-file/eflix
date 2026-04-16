<?php
// config/auth.php - Authentication functions
session_start();

require_once __DIR__ . '/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isset($_SESSION['admin_logged_in']) && !isset($_GET['admin'])) {
        header('Location: ../login.php');
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM profiles WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function login($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM profiles WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    return true;
}

function register($full_name, $email, $password, $phone = '') {
    $db = getDB();
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM profiles WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'এই ইমেইলে ইতিমধ্যে একাউন্ট আছে'];
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $referral_code = strtoupper(substr(md5(uniqid()), 0, 8));
    
    $stmt = $db->prepare("INSERT INTO profiles (full_name, email, password, phone, referral_code) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$full_name, $email, $hashed_password, $phone, $referral_code])) {
        return ['success' => true, 'message' => 'সাইনআপ সফল!'];
    }
    
    return ['success' => false, 'message' => 'সাইনআপ ব্যর্থ হয়েছে'];
}
?>