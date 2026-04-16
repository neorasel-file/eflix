<?php
// admin/packages.php - Package Management for Admin
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php?admin=eflix2024');
    exit();
}

$db = getDB();
$message = null;
$error = null;

// Handle package actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Add new package
    if ($action == 'add') {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $daily_limit = intval($_POST['daily_limit'] ?? 0);
        $extra_earning = intval($_POST['extra_earning'] ?? 0);
        $duration_days = $_POST['duration_days'] ? intval($_POST['duration_days']) : null;
        $color = $_POST['color'] ?? 'purple';
        $order = intval($_POST['order'] ?? 0);
        
        if (empty($id) || empty($name) || $price <= 0) {
            $error = "সব তথ্য পূরণ করুন";
        } else {
            $stmt = $db->prepare("INSERT INTO packages (id, name, price, daily_limit, extra_earning, duration_days, color, \"order\", is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, true)");
            if ($stmt->execute([$id, $name, $price, $daily_limit, $extra_earning, $duration_days, $color, $order])) {
                $message = "নতুন প্যাকেজ যোগ করা হয়েছে!";
            } else {
                $error = "প্যাকেজ যোগ করতে ব্যর্থ হয়েছে";
            }
        }
    }
    
    // Edit package
    if ($action == 'edit') {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $daily_limit = intval($_POST['daily_limit'] ?? 0);
        $extra_earning = intval($_POST['extra_earning'] ?? 0);
        $duration_days = $_POST['duration_days'] ? intval($_POST['duration_days']) : null;
        $color = $_POST['color'] ?? 'purple';
        $order = intval($_POST['order'] ?? 0);
        
        $stmt = $db->prepare("UPDATE packages SET name = ?, price = ?, daily_limit = ?, extra_earning = ?, duration_days = ?, color = ?, \"order\" = ? WHERE id = ?");
        if ($stmt->execute([$name, $price, $daily_limit, $extra_earning, $duration_days, $color, $order, $id])) {
            $message = "প্যাকেজ আপডেট করা হয়েছে!";
        } else {
            $error = "প্যাকেজ আপডেট করতে ব্যর্থ হয়েছে";
        }
    }
    
    // Delete package
    if ($action == 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM packages WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = "প্যাকেজ ডিলিট করা হয়েছে!";
        } else {
            $error = "প্যাকেজ ডিলিট করতে ব্যর্থ হয়েছে";
        }
    }
    
    // Toggle status
    if ($action == 'toggle_status') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("UPDATE packages SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $message = "প্যাকেজ স্ট্যাটাস পরিবর্তন করা হয়েছে!";
    }
}

// Get all packages
$packages = $db->query("SELECT * FROM packages ORDER BY \"order\"")->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>প্যাকেজ ম্যানেজ - Eflix Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
    <script>
        function showAddModal() {
            document.getElementById('modalTitle').innerText = 'নতুন প্যাকেজ যোগ করুন';
            document.getElementById('action').value = 'add';
            document.getElementById('packageId').value = '';
            document.getElementById('packageName').value = '';
            document.getElementById('packagePrice').value = '';
            document.getElementById('packageDailyLimit').value = '';
            document.getElementById('packageExtraEarning').value = '';
            document.getElementById('packageDuration').value = '';
            document.getElementById('packageColor').value = 'purple';
            document.getElementById('packageOrder').value = '';
            document.getElementById('packageModal').classList.remove('hidden');
            document.getElementById('packageModal').classList.add('flex');
        }
        
        function showEditModal(id, name, price, dailyLimit, extraEarning, duration, color, order) {
            document.getElementById('modalTitle').innerText = 'প্যাকেজ এডিট করুন';
            document.getElementById('action').value = 'edit';
            document.getElementById('packageId').value = id;
            document.getElementById('packageName').value = name;
            document.getElementById('packagePrice').value = price;
            document.getElementById('packageDailyLimit').value = dailyLimit;
            document.getElementById('packageExtraEarning').value = extraEarning;
            document.getElementById('packageDuration').value = duration || '';
            document.getElementById('packageColor').value = color;
            document.getElementById('packageOrder').value = order;
            document.getElementById('packageModal').classList.remove('hidden');
            document.getElementById('packageModal').classList.add('flex');
        }
        
        function closeModal() {
            document.getElementById('packageModal').classList.add('hidden');
            document.getElementById('packageModal').classList.remove('flex');
        }
        
        function deletePackage(id) {
            if (confirm('আপনি কি এই প্যাকেজ ডিলিট করতে চান?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function toggleStatus(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="toggle_status"><input type="hidden" name="id" value="${id}">`;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</head>
<body class="bg-gray-100">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-purple-800 to-indigo-900 text-white fixed h-full">
            <div class="p-4">
                <h1 class="text-xl font-bold">Eflix Admin</h1>
                <p class="text-xs text-white/60">প্যাকেজ ম্যানেজমেন্ট</p>
            </div>
            <nav class="mt-8">
                <a href="index.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">📊 ড্যাশবোর্ড</a>
                <a href="users.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">👥 ইউজার</a>
                <a href="packages.php?admin=eflix2024" class="block py-2 px-4 bg-white/20">💎 প্যাকেজ</a>
                <a href="tasks.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">📋 টাস্ক</a>
                <a href="withdrawals.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💸 উইথড্র</a>
                <a href="deposits.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💰 ডিপোজিট</a>
                <a href="settings.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">⚙️ সেটিংস</a>
                <a href="../logout.php" class="block py-2 px-4 mt-8 text-red-300 hover:bg-red-800">🚪 লগআউট</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-64 p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">💎 প্যাকেজ ম্যানেজমেন্ট</h1>
                <button onclick="showAddModal()" class="bg-green-500 text-white px-4 py-2 rounded-lg">
                    + নতুন প্যাকেজ যোগ করুন
                </button>
            </div>

            <?php if ($message): ?>
                <div class="bg-green-500 text-white rounded-xl p-3 mb-4"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-500 text-white rounded-xl p-3 mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left p-3">আইডি</th>
                                <th class="text-left p-3">নাম</th>
                                <th class="text-left p-3">মূল্য</th>
                                <th class="text-left p-3">ডেইলি লিমিট</th>
                                <th class="text-left p-3">এক্সট্রা আয়</th>
                                <th class="text-left p-3">মেয়াদ</th>
                                <th class="text-left p-3">অর্ডার</th>
                                <th class="text-left p-3">স্ট্যাটাস</th>
                                <th class="text-left p-3">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($packages as $pkg): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-mono text-sm"><?php echo htmlspecialchars($pkg['id']); ?></td>
                                <td class="p-3 font-semibold"><?php echo htmlspecialchars($pkg['name']); ?></td>
                                <td class="p-3 text-purple-600 font-bold">$<?php echo number_format($pkg['price'], 2); ?></td>
                                <td class="p-3"><?php echo number_format($pkg['daily_limit']); ?> </span></td>
                                <td class="p-3 text-green-600"><?php echo $pkg['extra_earning']; ?>%</span></td>
                                <td class="p-3"><?php echo $pkg['duration_days'] ? $pkg['duration_days'] . ' দিন' : 'লাইফটাইম'; ?></td>
                                <td class="p-3"><?php echo $pkg['order']; ?></span></td>
                                <td class="p-3">
                                    <?php if ($pkg['is_active']): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">সক্রিয়</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">নিষ্ক্রিয়</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <button onclick="showEditModal('<?php echo $pkg['id']; ?>', '<?php echo htmlspecialchars($pkg['name']); ?>', <?php echo $pkg['price']; ?>, <?php echo $pkg['daily_limit']; ?>, <?php echo $pkg['extra_earning']; ?>, '<?php echo $pkg['duration_days']; ?>', '<?php echo $pkg['color']; ?>', <?php echo $pkg['order']; ?>)" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">✏️ এডিট</button>
                                    <button onclick="toggleStatus('<?php echo $pkg['id']; ?>')" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">🔄 স্ট্যাটাস</button>
                                    <button onclick="deletePackage('<?php echo $pkg['id']; ?>')" class="bg-red-500 text-white px-2 py-1 rounded text-xs">🗑️ ডিলিট</button>
                                 </td>
                             </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Modal -->
    <div id="packageModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-96">
            <h3 id="modalTitle" class="font-bold text-lg mb-4">প্যাকেজ</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" id="action">
                <input type="hidden" name="id" id="packageId">
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">প্যাকেজ নাম</label>
                    <input type="text" name="name" id="packageName" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">মূল্য ($)</label>
                    <input type="number" name="price" id="packagePrice" class="w-full border rounded-lg px-3 py-2" step="0.01" required>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">ডেইলি ভিডিও লিমিট</label>
                    <input type="number" name="daily_limit" id="packageDailyLimit" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">এক্সট্রা আয় (%)</label>
                    <input type="number" name="extra_earning" id="packageExtraEarning" class="w-full border rounded-lg px-3 py-2" value="0">
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">মেয়াদ (দিন) - খালি রাখলে লাইফটাইম</label>
                    <input type="number" name="duration_days" id="packageDuration" class="w-full border rounded-lg px-3 py-2">
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">রঙ</label>
                    <select name="color" id="packageColor" class="w-full border rounded-lg px-3 py-2">
                        <option value="purple">পার্পল</option>
                        <option value="blue">ব্লু</option>
                        <option value="green">গ্রিন</option>
                        <option value="yellow">ইয়েলো</option>
                        <option value="red">রেড</option>
                        <option value="pink">পিঙ্ক</option>
                        <option value="amber">অ্যাম্বার</option>
                        <option value="gray">গ্রে</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">অর্ডার (ছোট সংখ্যা উপরে দেখাবে)</label>
                    <input type="number" name="order" id="packageOrder" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-lg">বাতিল</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg">সংরক্ষণ</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>