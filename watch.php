<?php
// watch.php - Watch Ad Page
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();
$user = getCurrentUser();

$db = getDB();

// Check daily limit
$todayWatched = getUserTodayWatched($user['id']);
$error = null;
$reward = 0;
$message = null;

if ($todayWatched >= $user['daily_limit']) {
    $error = "আজকের লিমিট শেষ! আগামীকাল আবার চেষ্টা করুন।";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error) {
    // Random reward between $0.001 and $0.01
    $reward = mt_rand(1, 10) / 1000;
    
    // Add transaction
    addTransaction($user['id'], 'watch', $reward, 'ভিডিও দেখেছেন');
    
    // Update user balance
    updateUserBalance($user['id'], $reward, 'add');
    
    // Refresh user data
    $user = getCurrentUser();
    
    // Check if user has completed task1 (watch 3 videos)
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND DATE(created_at) = CURRENT_DATE AND type = 'watch'");
    $stmt->execute([$user['id']]);
    $count = $stmt->fetch()['count'];
    
    if ($count >= 3) {
        // Check if task already completed
        $stmt = $db->prepare("SELECT id FROM user_tasks WHERE user_id = ? AND task_id = 'task1'");
        $stmt->execute([$user['id']]);
        if (!$stmt->fetch()) {
            // Complete task1
            $stmt = $db->prepare("INSERT INTO user_tasks (user_id, task_id) VALUES (?, 'task1')");
            $stmt->execute([$user['id']]);
            
            // Add task reward
            $stmt = $db->prepare("SELECT reward FROM daily_tasks WHERE id = 'task1'");
            $stmt->execute();
            $taskReward = $stmt->fetch()['reward'];
            
            updateUserBalance($user['id'], $taskReward, 'add');
            addTransaction($user['id'], 'bonus', $taskReward, 'টাস্ক সম্পন্ন: ৩টি ভিডিও দেখা');
            
            $reward += $taskReward;
        }
    }
    
    $message = "অভিনন্দন! $" . number_format($reward, 3) . " আপনার ব্যালেন্সে যোগ হয়েছে।";
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাড দেখুন - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        .ad-container {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="container mx-auto px-4 max-w-md mx-auto min-h-screen flex flex-col justify-center py-8">
        
        <a href="dashboard.php" class="text-gray-600 mb-4 inline-block">
            <i class="ri-arrow-left-line text-2xl"></i>
        </a>

        <?php if ($message): ?>
            <div class="bg-green-500 text-white rounded-2xl p-6 text-center mb-4">
                <i class="ri-checkbox-circle-fill text-5xl mb-3"></i>
                <p class="text-lg font-semibold"><?php echo $message; ?></p>
                <a href="dashboard.php" class="inline-block mt-4 bg-white text-green-600 px-6 py-2 rounded-full font-semibold">
                    ড্যাশবোর্ডে ফিরুন
                </a>
            </div>
        <?php elseif ($error): ?>
            <div class="bg-red-500 text-white rounded-2xl p-6 text-center mb-4">
                <i class="ri-error-warning-fill text-5xl mb-3"></i>
                <p class="text-lg font-semibold"><?php echo $error; ?></p>
                <a href="dashboard.php" class="inline-block mt-4 bg-white text-red-600 px-6 py-2 rounded-full font-semibold">
                    ড্যাশবোর্ডে ফিরুন
                </a>
            </div>
        <?php else: ?>
            <div class="ad-container rounded-2xl p-8 text-center text-white mb-6">
                <i class="ri-advertisement-fill text-6xl mb-4 text-yellow-400"></i>
                <h2 class="text-2xl font-bold mb-2">স্পনসরড কন্টেন্ট</h2>
                <p class="text-white/70 text-sm mb-6">এই অ্যাডটি দেখতে হবে টাকা পাওয়ার জন্য</p>
                
                <div id="countdown" class="text-5xl font-bold mb-6">5</div>
                
                <div class="w-full bg-white/20 rounded-full h-2 mb-6">
                    <div id="progress" class="bg-yellow-400 h-2 rounded-full" style="width: 0%"></div>
                </div>
                
                <form method="POST" action="" id="watchForm" style="display: none;">
                    <button type="submit" class="bg-yellow-500 text-black px-8 py-3 rounded-full font-semibold w-full">
                        ✅ অ্যাড দেখেছেন (রিওয়ার্ড নিন)
                    </button>
                </form>
                
                <p class="text-white/50 text-xs mt-4">অ্যাড সম্পূর্ণ দেখতে হবে।</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$error && !$message): ?>
    <script>
        let timeLeft = 5;
        const countdownEl = document.getElementById('countdown');
        const progressEl = document.getElementById('progress');
        const watchForm = document.getElementById('watchForm');
        
        const interval = setInterval(() => {
            timeLeft--;
            countdownEl.textContent = timeLeft;
            progressEl.style.width = ((5 - timeLeft) / 5) * 100 + '%';
            
            if (timeLeft <= 0) {
                clearInterval(interval);
                countdownEl.textContent = '✅ সম্পন্ন!';
                watchForm.style.display = 'block';
            }
        }, 1000);
    </script>
    <?php endif; ?>
</body>
</html>