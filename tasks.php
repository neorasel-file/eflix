<?php
// tasks.php - Extra Tasks Page
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();
$user = getCurrentUser();

$db = getDB();

// Get all active tasks
$tasks = $db->query("SELECT * FROM daily_tasks WHERE is_active = true ORDER BY \"order\"")->fetchAll();

// Get completed tasks for this user
$stmt = $db->prepare("SELECT task_id FROM user_tasks WHERE user_id = ?");
$stmt->execute([$user['id']]);
$completedTasks = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Calculate total earned from tasks
$stmt = $db->prepare("
    SELECT COALESCE(SUM(dt.reward), 0) as total 
    FROM user_tasks ut 
    JOIN daily_tasks dt ON ut.task_id = dt.id 
    WHERE ut.user_id = ?
");
$stmt->execute([$user['id']]);
$totalEarned = $stmt->fetch()['total'];

$message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'] ?? '';
    
    // Check if already completed
    if (in_array($task_id, $completedTasks)) {
        $message = 'আপনি ইতিমধ্যে এই টাস্ক সম্পন্ন করেছেন!';
    } else {
        // Get task details
        $stmt = $db->prepare("SELECT * FROM daily_tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        $task = $stmt->fetch();
        
        if ($task) {
            // Mark task as completed
            $stmt = $db->prepare("INSERT INTO user_tasks (user_id, task_id) VALUES (?, ?)");
            $stmt->execute([$user['id'], $task_id]);
            
            // Add reward to balance
            updateUserBalance($user['id'], $task['reward'], 'add');
            
            // Add transaction
            addTransaction($user['id'], 'bonus', $task['reward'], $task['title'] . ' টাস্ক সম্পন্ন');
            
            $message = "অভিনন্দন! $" . number_format($task['reward'], 2) . " আপনার ব্যালেন্সে যোগ হয়েছে।";
            header('refresh:2;url=tasks.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>টাস্ক - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 pb-20">

    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 max-w-md mx-auto">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">🎯 এক্সট্রা টাস্ক</h1>
                    <p class="text-xs text-white/70">টাস্ক করে বাড়তি আয় করুন</p>
                </div>
                <a href="dashboard.php" class="bg-white/20 px-3 py-1 rounded-full text-sm">← ফিরুন</a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-md mx-auto py-4">
        
        <div class="bg-green-100 rounded-xl p-4 mb-4 flex justify-between items-center">
            <span class="text-green-800">মোট টাস্ক থেকে আয়</span>
            <span class="text-2xl font-bold text-green-600">$<?php echo number_format($totalEarned, 2); ?></span>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-500 text-white rounded-xl p-4 mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php foreach ($tasks as $task): ?>
        <?php $isCompleted = in_array($task['id'], $completedTasks); ?>
        <div class="bg-white rounded-xl p-4 mb-3 shadow-sm">
            <div class="flex justify-between items-start">
                <div class="flex items-start space-x-3">
                    <i class="ri-task-line text-2xl text-purple-600"></i>
                    <div>
                        <p class="font-medium"><?php echo htmlspecialchars($task['title']); ?></p>
                        <p class="text-xs text-gray-500">🕒 টার্গেট: <?php echo $task['target']; ?> বার</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-green-500 font-bold">+$<?php echo number_format($task['reward'], 2); ?></p>
                    <?php if ($isCompleted): ?>
                        <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs inline-block mt-1">✅ সম্পন্ন</span>
                    <?php else: ?>
                        <form method="POST" action="" class="mt-1">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <button type="submit" class="bg-purple-600 text-white px-4 py-1 rounded-lg text-xs">স্টার্ট</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
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
            <a href="packages.php" class="flex flex-col items-center text-gray-400">
                <i class="ri-shopping-box-line text-xl"></i>
                <span class="text-xs">প্যাকেজ</span>
            </a>
            <a href="tasks.php" class="flex flex-col items-center text-purple-600">
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