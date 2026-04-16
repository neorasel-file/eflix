<?php
// includes/functions.php - Common Functions

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM profiles WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Get user balance
 */
function getUserBalance($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT balance FROM profiles WHERE id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result ? $result['balance'] : 0;
}

/**
 * Update user balance
 */
function updateBalance($user_id, $amount, $operation = 'add') {
    $db = getDB();
    if ($operation == 'add') {
        $sql = "UPDATE profiles SET balance = balance + ? WHERE id = ?";
    } else {
        $sql = "UPDATE profiles SET balance = balance - ? WHERE id = ?";
    }
    $stmt = $db->prepare($sql);
    return $stmt->execute([$amount, $user_id]);
}

/**
 * Add transaction
 */
function addTransaction($user_id, $type, $amount, $description, $status = 'completed') {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO transactions (user_id, type, amount, status, description) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $type, $amount, $status, $description]);
}

/**
 * Get today's watch count
 */
function getTodayWatchCount($user_id) {
    $db = getDB();
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND DATE(created_at) = ? AND type = 'watch'");
    $stmt->execute([$user_id, $today]);
    return $stmt->fetch()['count'];
}

/**
 * Get today's earning
 */
function getTodayEarning($user_id) {
    $db = getDB();
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND DATE(created_at) = ? AND type = 'watch'");
    $stmt->execute([$user_id, $today]);
    return $stmt->fetch()['total'];
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = '$') {
    return $currency . number_format($amount, 2);
}

/**
 * Generate random string
 */
function generateRandomString($length = 8) {
    return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

/**
 * Send email
 */
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@eflix.com" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Get site settings
 */
function getSiteSettings() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
    return $stmt->fetch();
}
?>