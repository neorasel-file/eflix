<?php
// admin/settings.php - Site Settings Management
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php?admin=eflix2024');
    exit();
}

$db = getDB();
$message = null;
$error = null;

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_general') {
        $site_name = $_POST['site_name'] ?? 'Eflix';
        $site_url = $_POST['site_url'] ?? '';
        $site_description = $_POST['site_description'] ?? '';
        $admin_email = $_POST['admin_email'] ?? '';
        $currency = $_POST['currency'] ?? 'USD';
        $primary_color = $_POST['primary_color'] ?? '#6366f1';
        $secondary_color = $_POST['secondary_color'] ?? '#8b5cf6';
        
        $stmt = $db->prepare("UPDATE settings SET 
            site_name = ?, site_url = ?, site_description = ?, 
            admin_email = ?, currency = ?, primary_color = ?, secondary_color = ? 
            WHERE id = 1");
        if ($stmt->execute([$site_name, $site_url, $site_description, $admin_email, $currency, $primary_color, $secondary_color])) {
            $message = "জেনারেল সেটিংস আপডেট করা হয়েছে!";
        } else {
            $error = "আপডেট ব্যর্থ হয়েছে";
        }
    }
    
    if ($action == 'update_withdraw') {
        $min_withdraw = floatval($_POST['min_withdraw'] ?? 10);
        $max_withdraw = floatval($_POST['max_withdraw'] ?? 500);
        $withdraw_fee = floatval($_POST['withdraw_fee'] ?? 0);
        
        $stmt = $db->prepare("UPDATE settings SET 
            min_withdraw = ?, max_withdraw = ?, withdraw_fee = ? 
            WHERE id = 1");
        if ($stmt->execute([$min_withdraw, $max_withdraw, $withdraw_fee])) {
            $message = "উইথড্র সেটিংস আপডেট করা হয়েছে!";
        } else {
            $error = "আপডেট ব্যর্থ হয়েছে";
        }
    }
    
    if ($action == 'update_bonus') {
        $referral_bonus = floatval($_POST['referral_bonus'] ?? 2);
        $first_deposit_bonus = floatval($_POST['first_deposit_bonus'] ?? 50);
        $daily_bonus = floatval($_POST['daily_bonus'] ?? 0.20);
        
        $stmt = $db->prepare("UPDATE settings SET 
            referral_bonus = ?, first_deposit_bonus = ?, daily_bonus = ? 
            WHERE id = 1");
        if ($stmt->execute([$referral_bonus, $first_deposit_bonus, $daily_bonus])) {
            $message = "বোনাস সেটিংস আপডেট করা হয়েছে!";
        } else {
            $error = "আপডেট ব্যর্থ হয়েছে";
        }
    }
    
    if ($action == 'update_ad') {
        $ad_reward_min = floatval($_POST['ad_reward_min'] ?? 0.001);
        $ad_reward_max = floatval($_POST['ad_reward_max'] ?? 0.01);
        
        $stmt = $db->prepare("UPDATE settings SET 
            ad_reward_min = ?, ad_reward_max = ? 
            WHERE id = 1");
        if ($stmt->execute([$ad_reward_min, $ad_reward_max])) {
            $message = "অ্যাড সেটিংস আপডেট করা হয়েছে!";
        } else {
            $error = "আপডেট ব্যর্থ হয়েছে";
        }
    }
}

// Get current settings
$settings = $db->query("SELECT * FROM settings WHERE id = 1")->fetch();
if (!$settings) {
    // Insert default settings if not exists
    $db->query("INSERT INTO settings (id) VALUES (1)");
    $settings = $db->query("SELECT * FROM settings WHERE id = 1")->fetch();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সেটিংস - Eflix Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
    <script>
        function showSection(section) {
            document.querySelectorAll('.settings-section').forEach(el => {
                el.classList.add('hidden');
            });
            document.getElementById(section).classList.remove('hidden');
            
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('bg-purple-600', 'text-white');
                el.classList.add('bg-gray-200', 'text-gray-700');
            });
            document.getElementById('tab-' + section).classList.remove('bg-gray-200', 'text-gray-700');
            document.getElementById('tab-' + section).classList.add('bg-purple-600', 'text-white');
        }
    </script>
</head>
<body class="bg-gray-100">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-purple-800 to-indigo-900 text-white fixed h-full">
            <div class="p-4">
                <h1 class="text-xl font-bold">Eflix Admin</h1>
                <p class="text-xs text-white/60">সেটিংস</p>
            </div>
            <nav class="mt-8">
                <a href="index.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">📊 ড্যাশবোর্ড</a>
                <a href="users.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">👥 ইউজার</a>
                <a href="packages.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💎 প্যাকেজ</a>
                <a href="tasks.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">📋 টাস্ক</a>
                <a href="withdrawals.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💸 উইথড্র</a>
                <a href="deposits.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💰 ডিপোজিট</a>
                <a href="settings.php?admin=eflix2024" class="block py-2 px-4 bg-white/20">⚙️ সেটিংস</a>
                <a href="../logout.php" class="block py-2 px-4 mt-8 text-red-300 hover:bg-red-800">🚪 লগআউট</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-64 p-6">
            <h1 class="text-2xl font-bold mb-6">⚙️ সাইট সেটিংস</h1>

            <?php if ($message): ?>
                <div class="bg-green-500 text-white rounded-xl p-3 mb-4"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-500 text-white rounded-xl p-3 mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="flex space-x-2 mb-6">
                <button id="tab-general" onclick="showSection('general')" class="tab-btn px-4 py-2 rounded-lg bg-purple-600 text-white">📝 জেনারেল</button>
                <button id="tab-withdraw" onclick="showSection('withdraw')" class="tab-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700">💸 উইথড্র</button>
                <button id="tab-bonus" onclick="showSection('bonus')" class="tab-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700">🎁 বোনাস</button>
                <button id="tab-ad" onclick="showSection('ad')" class="tab-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700">📢 অ্যাড</button>
            </div>

            <!-- General Settings -->
            <div id="general" class="settings-section bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">📝 জেনারেল সেটিংস</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_general">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">সাইটের নাম</label>
                            <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Eflix'); ?>" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">সাইটের URL</label>
                            <input type="text" name="site_url" value="<?php echo htmlspecialchars($settings['site_url'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        
                        <div class="mb-4 col-span-2">
                            <label class="block text-sm font-medium mb-1">সাইটের বিবরণ</label>
                            <textarea name="site_description" rows="3" class="w-full border rounded-lg px-3 py-2"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">অ্যাডমিন ইমেইল</label>
                            <input type="email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">কারেন্সি</label>
                            <select name="currency" class="w-full border rounded-lg px-3 py-2">
                                <option value="USD" <?php echo ($settings['currency'] ?? 'USD') == 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                <option value="BDT" <?php echo ($settings['currency'] ?? 'USD') == 'BDT' ? 'selected' : ''; ?>>BDT (৳)</option>
                                <option value="EUR" <?php echo ($settings['currency'] ?? 'USD') == 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                <option value="GBP" <?php echo ($settings['currency'] ?? 'USD') == 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">প্রাইমারি কালার</label>
                            <input type="color" name="primary_color" value="<?php echo htmlspecialchars($settings['primary_color'] ?? '#6366f1'); ?>" class="w-full border rounded-lg px-3 py-2 h-10">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">সেকেন্ডারি কালার</label>
                            <input type="color" name="secondary_color" value="<?php echo htmlspecialchars($settings['secondary_color'] ?? '#8b5cf6'); ?>" class="w-full border rounded-lg px-3 py-2 h-10">
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg">সংরক্ষণ করুন</button>
                </form>
            </div>

            <!-- Withdraw Settings -->
            <div id="withdraw" class="settings-section bg-white rounded-xl shadow-sm p-6 hidden">
                <h2 class="text-lg font-semibold mb-4">💸 উইথড্র সেটিংস</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_withdraw">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">ন্যূনতম উইথড্র ($)</label>
                            <input type="number" name="min_withdraw" value="<?php echo $settings['min_withdraw'] ?? 10; ?>" class="w-full border rounded-lg px-3 py-2" step="0.01">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">সর্বোচ্চ উইথড্র ($)</label>
                            <input type="number" name="max_withdraw" value="<?php echo $settings['max_withdraw'] ?? 500; ?>" class="w-full border rounded-lg px-3 py-2" step="0.01">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">উইথড্র ফি (%)</label>
                            <input type="number" name="withdraw_fee" value="<?php echo $settings['withdraw_fee'] ?? 0; ?>" class="w-full border rounded-lg px-3 py-2" step="0.01">
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg">সংরক্ষণ করুন</button>
                </form>
            </div>

            <!-- Bonus Settings -->
            <div id="bonus" class="settings-section bg-white rounded-xl shadow-sm p-6 hidden">
                <h2 class="text-lg font-semibold mb-4">🎁 বোনাস সেটিংস</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_bonus">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">রেফারেল বোনাস ($)</label>
                            <input type="number" name="referral_bonus" value="<?php echo $settings['referral_bonus'] ?? 2; ?>" class="w-full border rounded-lg px-3 py-2" step="0.01">
                            <p class="text-xs text-gray-500 mt-1">প্রতি রেফারে কত টাকা বোনাস দেবেন</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">প্রথম ডিপোজিট বোনাস (%)</label>
                            <input type="number" name="first_deposit_bonus" value="<?php echo $settings['first_deposit_bonus'] ?? 50; ?>" class="w-full border rounded-lg px-3 py-2" step="0.01">
                            <p class="text-xs text-gray-500 mt-1">প্রথম জমার উপর কত শতাংশ বোনাস দেবেন</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">ডেইলি বোনাস ($)</label>
                            <input type="number" name="daily_bonus" value="<?php echo $settings['daily_bonus'] ?? 0.20; ?>" class="w-full border rounded-lg px-3 py-2" step="0.01">
                            <p class="text-xs text-gray-500 mt-1">প্রতিদিন চেক-ইন করলে কত টাকা বোনাস দেবেন</p>
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg">সংরক্ষণ করুন</button>
                </form>
            </div>

            <!-- Ad Settings -->
            <div id="ad" class="settings-section bg-white rounded-xl shadow-sm p-6 hidden">
                <h2 class="text-lg font-semibold mb-4">📢 অ্যাড সেটিংস</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_ad">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">ন্যূনতম অ্যাড রিওয়ার্ড ($)</label>
                            <input type="number" name="ad_reward_min" value="<?php echo $settings['ad_reward_min'] ?? 0.001; ?>" class="w-full border rounded-lg px-3 py-2" step="0.001">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">সর্বোচ্চ অ্যাড রিওয়ার্ড ($)</label>
                            <input type="number" name="ad_reward_max" value="<?php echo $settings['ad_reward_max'] ?? 0.01; ?>" class="w-full border rounded-lg px-3 py-2" step="0.001">
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg">সংরক্ষণ করুন</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize first section
        document.getElementById('general').classList.remove('hidden');
    </script>
</body>
</html>