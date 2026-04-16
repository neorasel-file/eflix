<?php
// dashboard.php
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();
$user = getCurrentUser();

$db = getDB();

// Get today's stats
$todayWatched = getUserTodayWatched($user['id']);
$todayEarning = getUserTodayEarning($user['id']);

// Get packages
$packages = $db->query("SELECT * FROM packages WHERE is_active = true ORDER BY \"order\"")->fetchAll();

// Get daily tasks
$tasks = $db->query("SELECT * FROM daily_tasks WHERE is_active = true ORDER BY \"order\"")->fetchAll();

// Get completed tasks for this user
$stmt = $db->prepare("SELECT task_id FROM user_tasks WHERE user_id = ?");
$stmt->execute([$user['id']]);
$completedTasks = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ড্যাশবোর্ড - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 pb-20">

    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 max-w-md mx-auto">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">Eflix</h1>
                    <p class="text-xs text-white/70">ভিডিও দেখে আয় করুন</p>
                </div>
                <div class="flex space-x-2">
                    <a href="logout.php" class="bg-white/20 px-3 py-1 rounded-full text-sm">লগআউট</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-md mx-auto py-4">
        
        <!-- Balance Card -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl p-5 mb-6 text-white">
            <p class="text-white/80 text-sm">মোট ব্যালেন্স</p>
            <h2 class="text-3xl font-bold">$<?php echo number_format($user['balance'], 2); ?></h2>
            <p class="text-white/70 text-xs mt-1">আজকের আয়: $<?php echo number_format($todayEarning, 2); ?></p>
            <div class="flex space-x-3 mt-4">
                <a href="withdraw.php" class="bg-white/20 backdrop-blur px-4 py-2 rounded-full text-sm">
                    <i class="ri-money-dollar-circle-line"></i> উইথড্র
                </a>
                <a href="add-money.php" class="bg-white/20 backdrop-blur px-4 py-2 rounded-full text-sm">
                    <i class="ri-add-line"></i> অ্যাড মানি
                </a>
            </div>
        </div>

        <!-- Today's Limit -->
        <div class="bg-white rounded-xl p-4 mb-6 shadow-sm">
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-semibold">📊 আজকের লিমিট</h3>
                <span class="text-sm text-purple-600"><?php echo $todayWatched; ?>/<?php echo $user['daily_limit']; ?> ভিডিও</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo min(($todayWatched / $user['daily_limit']) * 100, 100); ?>%"></div>
            </div>
        </div>

        <!-- Watch Ad Button -->
        <a href="watch.php" class="block">
            <div class="bg-white rounded-xl p-6 mb-6 shadow-sm text-center">
                <i class="ri-play-circle-line text-6xl text-purple-600"></i>
                <h3 class="font-bold text-lg mt-2">অ্যাড দেখে আয় করুন</h3>
                <p class="text-gray-500 text-sm mb-4">প্রতি অ্যাড দেখে $0.001 - $0.01 আয় করুন</p>
                <div class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white px-6 py-3 rounded-full font-semibold inline-block">
                    🎬 অ্যাড দেখুন
                </div>
            </div>
        </a>

        <!-- Daily Tasks -->
        <div class="bg-white rounded-xl p-4 mb-6 shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold">📋 ডেইলি টাস্ক</h3>
                <span class="text-xs text-purple-600">টাস্ক করে আয় করুন</span>
            </div>
            <?php foreach ($tasks as $task): ?>
            <?php $isCompleted = in_array($task['id'], $completedTasks); ?>
            <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-0">
                <div class="flex items-center space-x-3">
                    <?php if ($isCompleted): ?>
                        <i class="ri-checkbox-circle-fill text-green-500"></i>
                    <?php else: ?>
                        <i class="ri-checkbox-circle-line text-gray-400"></i>
                    <?php endif; ?>
                    <span class="text-sm"><?php echo htmlspecialchars($task['title']); ?></span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-green-500 text-sm">+$<?php echo number_format($task['reward'], 2); ?></span>
                    <?php if (!$isCompleted): ?>
                    <button onclick="completeTask('<?php echo $task['id']; ?>', <?php echo $task['reward']; ?>)" class="bg-purple-600 text-white px-3 py-1 rounded-lg text-xs">স্টার্ট</button>
                    <?php else: ?>
                        <span class="text-green-500 text-xs">সম্পন্ন</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Packages -->
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold">💎 প্যাকেজসমূহ</h3>
                <a href="packages.php" class="text-purple-600 text-sm">সব দেখুন</a>
            </div>
            <div class="flex space-x-3 overflow-x-auto pb-2">
                <?php foreach ($packages as $pkg): ?>
                <div class="flex-shrink-0 w-36 bg-gradient-to-r from-<?php echo $pkg['color']; ?>-500 to-<?php echo $pkg['color']; ?>-600 rounded-xl p-3 text-white">
                    <p class="font-bold text-sm"><?php echo htmlspecialchars($pkg['name']); ?></p>
                    <p class="text-lg font-bold">$<?php echo $pkg['price']; ?></p>
                    <p class="text-xs"><?php echo $pkg['daily_limit']; ?> ভিডিও/দিন</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-2 max-w-md mx-auto">
        <div class="flex justify-around">
            <a href="dashboard.php" class="flex flex-col items-center text-purple-600">
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

    <script>
    function completeTask(taskId, reward) {
        fetch('api/complete-task.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ task_id: taskId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`অভিনন্দন! $${reward} আপনার ব্যালেন্সে যোগ হয়েছে।`);
                location.reload();
            } else {
                alert(data.error || 'টাস্ক সম্পন্ন করতে ব্যর্থ হয়েছে।');
            }
        })
        .catch(error => alert('এরর হয়েছে: ' + error));
    }
    </script>
</body>
</html>