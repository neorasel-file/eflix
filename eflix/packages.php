<?php
// packages.php
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();
$user = getCurrentUser();

$db = getDB();

// Get all packages
$packages = $db->query("SELECT * FROM packages WHERE is_active = true ORDER BY \"order\"")->fetchAll();

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $package_id = $_POST['package_id'] ?? '';
    
    if ($package_id) {
        // Get package details
        $stmt = $db->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->execute([$package_id]);
        $package = $stmt->fetch();
        
        if ($package && $user['balance'] >= $package['price']) {
            // Deduct balance
            $newBalance = $user['balance'] - $package['price'];
            $stmt = $db->prepare("UPDATE profiles SET balance = ?, package = ?, daily_limit = ? WHERE id = ?");
            $stmt->execute([$newBalance, $package_id, $package['daily_limit'], $user['id']]);
            
            // Add transaction
            addTransaction($user['id'], 'package', $package['price'], $package['name'] . ' প্যাকেজ কেনা হয়েছে');
            
            $message = $package['name'] . " প্যাকেজ সফলভাবে কেনা হয়েছে!";
            header('refresh:2;url=dashboard.php');
        } else {
            $error = "অপর্যাপ্ত ব্যালেন্স! $ " . number_format($package['price'], 2) . " প্রয়োজন।";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>প্যাকেজ - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 pb-20">

    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 max-w-md mx-auto">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">💎 প্যাকেজ কিনুন</h1>
                    <p class="text-xs text-white/70">আরও বেশি আয়ের জন্য প্যাকেজ কিনুন</p>
                </div>
                <a href="dashboard.php" class="bg-white/20 px-3 py-1 rounded-full text-sm">← ফিরুন</a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-md mx-auto py-4">
        
        <?php if ($message): ?>
            <div class="bg-green-500 text-white rounded-xl p-4 mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-500 text-white rounded-xl p-4 mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl p-4 mb-4 shadow-sm">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">আপনার ব্যালেন্স</span>
                <span class="text-2xl font-bold text-purple-600">$<?php echo number_format($user['balance'], 2); ?></span>
            </div>
            <div class="flex justify-between items-center mt-2">
                <span class="text-gray-600">বর্তমান প্যাকেজ</span>
                <span class="font-semibold text-purple-600"><?php echo ucfirst($user['package']); ?></span>
            </div>
        </div>

        <?php foreach ($packages as $pkg): ?>
        <?php if ($pkg['price'] == 0) continue; ?>
        <div class="bg-white rounded-xl p-4 mb-4 shadow-sm border-l-4 border-<?php echo $pkg['color']; ?>-500">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center space-x-2">
                        <?php if ($pkg['id'] == 'bronze'): ?>
                            <i class="ri-medal-line text-2xl text-amber-600"></i>
                        <?php elseif ($pkg['id'] == 'silver'): ?>
                            <i class="ri-medal-2-line text-2xl text-gray-500"></i>
                        <?php elseif ($pkg['id'] == 'gold'): ?>
                            <i class="ri-crown-line text-2xl text-yellow-500"></i>
                        <?php else: ?>
                            <i class="ri-diamond-line text-2xl text-blue-500"></i>
                        <?php endif; ?>
                        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($pkg['name']); ?></h3>
                    </div>
                    <p class="text-2xl font-bold mt-2">$<?php echo number_format($pkg['price'], 2); ?></p>
                </div>
                <?php if ($pkg['id'] == 'silver'): ?>
                    <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">জনপ্রিয়</span>
                <?php elseif ($pkg['id'] == 'gold'): ?>
                    <span class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">বেস্ট ভ্যালু</span>
                <?php elseif ($pkg['id'] == 'platinum'): ?>
                    <span class="bg-blue-500 text-white px-2 py-1 rounded text-xs">VIP</span>
                <?php endif; ?>
            </div>
            <div class="mt-3 space-y-2">
                <div class="flex items-center space-x-2 text-sm">
                    <i class="ri-checkbox-circle-fill text-green-500"></i>
                    <span><?php echo number_format($pkg['daily_limit']); ?>টি ভিডিও/দিন</span>
                </div>
                <div class="flex items-center space-x-2 text-sm">
                    <i class="ri-checkbox-circle-fill text-green-500"></i>
                    <span><?php echo $pkg['extra_earning']; ?>% এক্সট্রা আয়</span>
                </div>
                <?php if ($pkg['duration_days']): ?>
                <div class="flex items-center space-x-2 text-sm">
                    <i class="ri-checkbox-circle-fill text-green-500"></i>
                    <span><?php echo $pkg['duration_days']; ?> দিন বৈধ</span>
                </div>
                <?php endif; ?>
            </div>
            <form method="POST" action="" class="mt-4">
                <input type="hidden" name="package_id" value="<?php echo $pkg['id']; ?>">
                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-700 text-white py-2 rounded-lg">
                    কিনুন 🛒
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-2 max-w-md mx-auto">
        <div class="flex justify-around">
            <a href="dashboard.php" class="flex flex-col items-center text-gray-400">
                <i class="ri-home-5-line text-xl"></i>
                <span class="text-xs">হোম</span>
            </a>
            <a href="packages.php" class="flex flex-col items-center text-purple-600">
                <i class="ri-shopping-box-line text-xl"></i>
                <span class="text-xs">প্যাকেজ</span>
            </a>
            <a href="tasks.php" class="flex flex-col items-center text-gray-400">
                <i class="ri-task-line text-xl"></i>
                <span class="text-xs">টাস্ক</span>
            </a>
            <a href="history.php" class="flex flex-col items-center text-gray-400">
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