<?php
// admin/index.php - Admin Dashboard
require_once '../config/database.php';
session_start();

// Simple admin check
if (!isset($_SESSION['admin_logged_in']) && (!isset($_GET['admin']) || $_GET['admin'] != 'eflix2024')) {
    header('Location: ../login.php');
    exit();
}
$_SESSION['admin_logged_in'] = true;

$db = getDB();

// Get stats
$stmt = $db->query("SELECT COUNT(*) as total FROM profiles");
$totalUsers = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM profiles WHERE DATE(created_at) = CURRENT_DATE");
$newUsersToday = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM deposits WHERE status = 'approved'");
$totalDeposits = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM withdrawals WHERE status = 'pending'");
$pendingWithdrawals = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'watch'");
$totalPaid = $stmt->fetch()['total'];

// Recent activities
$recentUsers = $db->query("SELECT * FROM profiles ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentWithdrawals = $db->query("SELECT w.*, p.full_name FROM withdrawals w JOIN profiles p ON w.user_id = p.id ORDER BY w.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-purple-800 to-indigo-900 text-white fixed h-full">
            <div class="p-4">
                <h1 class="text-xl font-bold">Eflix Admin</h1>
                <p class="text-xs text-white/60">ড্যাশবোর্ড</p>
            </div>
            <nav class="mt-8">
                <a href="index.php?admin=eflix2024" class="block py-2 px-4 bg-white/20">📊 ড্যাশবোর্ড</a>
                <a href="users.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">👥 ইউজার</a>
                <a href="packages.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💎 প্যাকেজ</a>
                <a href="tasks.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">📋 টাস্ক</a>
                <a href="withdrawals.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💸 উইথড্র</a>
                <a href="deposits.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💰 ডিপোজিট</a>
                <a href="settings.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">⚙️ সেটিংস</a>
                <a href="../logout.php" class="block py-2 px-4 mt-8 text-red-300 hover:bg-red-800">🚪 লগআউট</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <div class="p-6">
                <h1 class="text-2xl font-bold mb-6">ড্যাশবোর্ড ওভারভিউ</h1>

                <!-- Stats Cards -->
                <div class="grid grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">মোট ইউজার</p>
                                <p class="text-2xl font-bold"><?php echo $totalUsers; ?></p>
                            </div>
                            <i class="ri-user-line text-3xl text-purple-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">নতুন ইউজার (আজ)</p>
                                <p class="text-2xl font-bold"><?php echo $newUsersToday; ?></p>
                            </div>
                            <i class="ri-user-add-line text-3xl text-green-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">মোট ডিপোজিট</p>
                                <p class="text-2xl font-bold">$<?php echo number_format($totalDeposits, 2); ?></p>
                            </div>
                            <i class="ri-bank-card-line text-3xl text-blue-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">পেন্ডিং উইথড্র</p>
                                <p class="text-2xl font-bold">$<?php echo number_format($pendingWithdrawals, 2); ?></p>
                            </div>
                            <i class="ri-money-dollar-circle-line text-3xl text-red-600"></i>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Recent Users -->
                    <div class="bg-white rounded-xl shadow-sm">
                        <div class="p-4 border-b">
                            <h3 class="font-semibold">নতুন ইউজার</h3>
                        </div>
                        <div class="p-4">
                            <?php foreach ($recentUsers as $user): ?>
                            <div class="flex justify-between items-center py-2 border-b last:border-0">
                                <div>
                                    <p class="font-medium"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                <span class="text-xs text-gray-400"><?php echo date('d M Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Recent Withdrawals -->
                    <div class="bg-white rounded-xl shadow-sm">
                        <div class="p-4 border-b">
                            <h3 class="font-semibold">পেন্ডিং উইথড্র অনুরোধ</h3>
                        </div>
                        <div class="p-4">
                            <?php foreach ($recentWithdrawals as $wd): ?>
                            <div class="flex justify-between items-center py-2 border-b last:border-0">
                                <div>
                                    <p class="font-medium"><?php echo htmlspecialchars($wd['full_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $wd['method']; ?> - <?php echo $wd['account_number']; ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-red-600">$<?php echo number_format($wd['amount'], 2); ?></p>
                                    <span class="text-xs text-yellow-500"><?php echo $wd['status']; ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>