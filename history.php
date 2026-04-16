<?php
// history.php - Transaction History
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();
$user = getCurrentUser();

$db = getDB();

// Get all transactions for this user
$stmt = $db->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user['id']]);
$transactions = $stmt->fetchAll();

// Get total stats
$stmt = $db->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'watch' THEN amount ELSE 0 END), 0) as total_watch,
        COALESCE(SUM(CASE WHEN type = 'bonus' THEN amount ELSE 0 END), 0) as total_bonus,
        COALESCE(SUM(CASE WHEN type = 'referral' THEN amount ELSE 0 END), 0) as total_referral,
        COALESCE(SUM(CASE WHEN type = 'withdraw' THEN amount ELSE 0 END), 0) as total_withdraw
    FROM transactions WHERE user_id = ?
");
$stmt->execute([$user['id']]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>হিস্ট্রি - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 pb-20">

    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 max-w-md mx-auto">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">📜 আয়ের ইতিহাস</h1>
                    <p class="text-xs text-white/70">আপনার সকল লেনদেন</p>
                </div>
                <a href="dashboard.php" class="bg-white/20 px-3 py-1 rounded-full text-sm">← ফিরুন</a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-md mx-auto py-4">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="bg-white rounded-xl p-3 text-center shadow-sm">
                <i class="ri-video-line text-xl text-purple-600"></i>
                <p class="text-lg font-bold text-purple-600">$<?php echo number_format($stats['total_watch'], 2); ?></p>
                <p class="text-xs text-gray-500">ভিডিও থেকে</p>
            </div>
            <div class="bg-white rounded-xl p-3 text-center shadow-sm">
                <i class="ri-gift-line text-xl text-green-600"></i>
                <p class="text-lg font-bold text-green-600">$<?php echo number_format($stats['total_bonus'] + $stats['total_referral'], 2); ?></p>
                <p class="text-xs text-gray-500">বোনাস থেকে</p>
            </div>
        </div>

        <!-- Transactions List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-3 bg-gray-50 border-b">
                <h3 class="font-semibold">সব লেনদেন</h3>
            </div>
            
            <?php if (empty($transactions)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="ri-history-line text-5xl mb-2 block"></i>
                    <p>কোনো লেনদেন নেই</p>
                    <a href="watch.php" class="inline-block mt-2 text-purple-600">অ্যাড দেখে আয় শুরু করুন</a>
                </div>
            <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                <div class="flex justify-between items-center p-4 border-b border-gray-100">
                    <div class="flex items-center space-x-3">
                        <?php if ($tx['type'] == 'watch'): ?>
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="ri-play-circle-line text-purple-600"></i>
                            </div>
                        <?php elseif ($tx['type'] == 'bonus'): ?>
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="ri-gift-line text-green-600"></i>
                            </div>
                        <?php elseif ($tx['type'] == 'deposit'): ?>
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="ri-bank-card-line text-blue-600"></i>
                            </div>
                        <?php elseif ($tx['type'] == 'withdraw'): ?>
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="ri-money-dollar-circle-line text-red-600"></i>
                            </div>
                        <?php else: ?>
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="ri-information-line text-gray-600"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($tx['description']); ?></p>
                            <p class="text-xs text-gray-400"><?php echo date('d M Y, h:i A', strtotime($tx['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <?php if ($tx['type'] == 'withdraw'): ?>
                            <p class="text-red-500 font-semibold">-$<?php echo number_format($tx['amount'], 2); ?></p>
                        <?php else: ?>
                            <p class="text-green-500 font-semibold">+$<?php echo number_format($tx['amount'], 2); ?></p>
                        <?php endif; ?>
                        <?php if ($tx['status'] != 'completed'): ?>
                            <span class="text-xs text-yellow-500"><?php echo $tx['status']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-2 max-w-md mx-auto">
        <div class="flex justify-around">
            <a href="dashboard.php" class="flex flex-col items-center text-gray-400">
                <i class="ri-home-5-line text-xl"></i>
                <span class="text-xs">হোম</span>
            </a>
            <a href="packages.php" class="flex flex-col items-center text-gray-400">
                <i class="ri-shopping-box-line text-xl"></i>
                <span class="text-xs">প্যাকেজ</span>
            </a>
            <a href="tasks.php" class="flex flex-col items-center text-gray-400">
                <i class="ri-task-line text-xl"></i>
                <span class="text-xs">টাস্ক</span>
            </a>
            <a href="history.php" class="flex flex-col items-center text-purple-600">
                <i class="ri-history-line text-xl"></i>
                <span class="text-xs">হিস্ট্রি</span>
            </a>
            <a href="account.php" class="flex flex-col items-center text-gray-400">
                <i class="ri-user-line text-xl"></i>
                <span class="text-xs">একাউন্ট</span>
            </a>
        </div>
    </div>

</body>
</html>