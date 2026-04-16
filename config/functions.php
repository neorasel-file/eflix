<?php
// config/functions.php - Helper functions
require_once __DIR__ . '/database.php';

function getSiteSettings() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM settings LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addTransaction($user_id, $type, $amount, $description, $status = 'completed') {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO transactions (user_id, type, amount, status, description) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $type, $amount, $status, $description]);
}

function updateUserBalance($user_id, $amount, $operation = 'add') {
    $db = getDB();
    if ($operation == 'add') {
        $stmt = $db->prepare("UPDATE profiles SET balance = balance + ? WHERE id = ?");
    } else {
        $stmt = $db->prepare("UPDATE profiles SET balance = balance - ? WHERE id = ?");
    }
    return $stmt->execute([$amount, $user_id]);
}

function getUserTodayWatched($user_id) {
    $db = getDB();
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND DATE(created_at) = ? AND type = 'watch'");
    $stmt->execute([$user_id, $today]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

function getUserTodayEarning($user_id) {
    $db = getDB();
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND DATE(created_at) = ? AND type = 'watch'");
    $stmt->execute([$user_id, $today]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function generateRandomString($length = 8) {
    return strtoupper(substr(md5(uniqid()), 0, $length));
}
?>