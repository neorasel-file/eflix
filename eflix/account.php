<?php
// account.php - User Account Settings
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();
$user = getCurrentUser();

$db = getDB();

$message = null;
$error = null;

// Get user stats
$stmt = $db->prepare("SELECT COUNT(*) as total FROM transactions WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalTransactions = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM user_tasks WHERE user_id = ?");
$stmt->execute([$user['id']]);
$completedTasks = $stmt->fetch()['total'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    $stmt = $db->prepare("UPDATE profiles SET full_name = ?, phone = ? WHERE id = ?");
    if ($stmt->execute([$full_name, $phone, $user['id']])) {
        $message = "প্রোফাইল আপডেট করা হয়েছে!";
        $user = getCurrentUser();
    } else {
        $error = "প্রোফাইল আপডেট ব্যর্থ হয়েছে।";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password)) {
        $error = "সব ফিল্ড পূরণ করুন";
    } elseif ($new_password !== $confirm_password) {
        $error = "নতুন পাসওয়ার্ড মিলছে না";
    } elseif (strlen($new_password) < 6) {
        $error = "পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "বর্তমান পাসওয়ার্ড ভুল";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE profiles SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $user['id']])) {
            $message = "পাসওয়ার্ড পরিবর্তন করা হয়েছে!";
        } else {
            $error = "পাসওয়ার্ড পরিবর্তন ব্যর্থ হয়েছে।";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>একাউন্ট - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
    <script>
        function toggleSection(section) {
            document.getElementById('profileSection').classList.add('hidden');
            document.getElementById('passwordSection').classList.add('hidden');
            document.getElementById(section).classList.remove('hidden');
        }
        
        function copyReferralLink() {
            const link = document.getElementById('referralLink');
            link.select();
            document.execCommand('copy');
            alert('রেফারেল লিংক কপি হয়েছে!');
        }
        
        function copyReferralCode() {
            const input = document.querySelector('#profileSection input[readonly]');
            input.select();
            document.execCommand('copy');
            alert('রেফারেল কোড কপি হয়েছে!');
        }
    </script>
</head>
<body class="bg-gray-100 pb-20">

    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 max-w-md mx-auto">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">👤 আমার একাউন্ট</h1>
                    <p class="text-xs text-white/70">প্রোফাইল ও সেটিংস</p>
                </div>
                <a href="dashboard.php" class="bg-white/20 px-3 py-1 rounded-full text-sm">← ফিরুন</a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-md mx-auto py-4">
        
        <?php if ($message): ?>
            <div class="bg-green-500 text-white rounded-xl p-3 mb-4"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-500 text-white rounded-xl p-3 mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl p-6 mb-6 text-white text-center">
            <div class="w-20 h-20 bg-white/30 rounded-full mx-auto flex items-center justify-center mb-3">
                <i class="ri-user-line text-4xl text-white"></i>
            </div>
            <h2 class="text-xl font-bold"><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p class="text-white/80 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
            <p class="text-white/70 text-xs mt-1"><?php echo htmlspecialchars($user['phone'] ?? 'ফোন নম্বর যোগ করুন'); ?></p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-white rounded-xl p-3 text-center shadow-sm">
                <p class="text-lg font-bold text-purple-600">$<?php echo number_format($user['balance'], 2); ?></p>
                <p class="text-xs text-gray-500">মোট আয়</p>
            </div>
            <div class="bg-white rounded-xl p-3 text-center shadow-sm">
                <p class="text-lg font-bold text-purple-600"><?php echo $totalTransactions; ?></p>
                <p class="text-xs text-gray-500">লেনদেন</p>
            </div>
            <div class="bg-white rounded-xl p-3 text-center shadow-sm">
                <p class="text-lg font-bold text-purple-600"><?php echo $completedTasks; ?></p>
                <p class="text-xs text-gray-500">টাস্ক সম্পন্ন</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-2 mb-4">
            <button onclick="toggleSection('profileSection')" class="flex-1 bg-purple-600 text-white py-2 rounded-lg text-sm">প্রোফাইল</button>
            <button onclick="toggleSection('passwordSection')" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg text-sm">পাসওয়ার্ড</button>
        </div>

        <!-- Profile Section -->
        <div id="profileSection" class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="font-semibold mb-4">📝 প্রোফাইল তথ্য</h3>
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">পূর্ণ নাম</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">ইমেইল</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly disabled>
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">ফোন নম্বর</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2" placeholder="+8801XXXXXXXXX">
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">রেফারেল কোড</label>
                    <div class="flex">
                        <input type="text" value="<?php echo htmlspecialchars($user['referral_code']); ?>" class="flex-1 border rounded-l-lg px-3 py-2 bg-gray-100" readonly>
                        <button type="button" onclick="copyReferralCode()" class="bg-purple-600 text-white px-4 rounded-r-lg">কপি</button>
                    </div>
                </div>
                <button type="submit" name="update_profile" class="w-full bg-purple-600 text-white py-2 rounded-lg mt-2">সংরক্ষণ করুন</button>
            </form>
        </div>

        <!-- Password Section -->
        <div id="passwordSection" class="bg-white rounded-xl shadow-sm p-4 hidden">
            <h3 class="font-semibold mb-4">🔐 পাসওয়ার্ড পরিবর্তন</h3>
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">বর্তমান পাসওয়ার্ড</label>
                    <input type="password" name="current_password" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">নতুন পাসওয়ার্ড</label>
                    <input type="password" name="new_password" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">নতুন পাসওয়ার্ড আবার দিন</label>
                    <input type="password" name="confirm_password" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                <button type="submit" name="change_password" class="w-full bg-purple-600 text-white py-2 rounded-lg">পাসওয়ার্ড পরিবর্তন করুন</button>
            </form>
        </div>

        <!-- Referral Link -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-4 mt-6">
            <h3 class="font-semibold mb-2">👥 রেফারেল লিংক</h3>
            <div class="flex">
                <input type="text" id="referralLink" class="flex-1 border rounded-l-lg px-3 py-2 text-sm bg-white" readonly value="<?php echo "https://eflix.com/?ref=" . $user['referral_code']; ?>">
                <button onclick="copyReferralLink()" class="bg-purple-600 text-white px-4 rounded-r-lg">কপি</button>
            </div>
            <p class="text-xs text-gray-500 mt-2">প্রতি রেফারে $2 বোনাস!</p>
        </div>

        <!-- Logout Button -->
        <a href="logout.php" class="block mt-4">
            <div class="bg-red-500 text-white rounded-xl p-3 text-center">
                <i class="ri-logout-box-line"></i> লগআউট
            </div>
        </a>
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
            <a href="history.php" class="flex flex-col items-center text-gray-400">
                <i class="ri-history-line text-xl"></i>
                <span class="text-xs">হিস্ট্রি</span>
            </a>
            <a href="account.php" class="flex flex-col items-center text-purple-600">
                <i class="ri-user-line text-xl"></i>
                <span class="text-xs">একাউন্ট</span>
            </a>
        </div>
    </div>

    <script>
        // Initialize with profile section visible
        document.getElementById('profileSection').classList.remove('hidden');
    </script>
</body>
</html>