<?php
// admin/tasks.php - Task Management for Admin
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php?admin=eflix2024');
    exit();
}

$db = getDB();
$message = null;
$error = null;

// Handle task actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Add new task
    if ($action == 'add') {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $reward = floatval($_POST['reward'] ?? 0);
        $target = intval($_POST['target'] ?? 1);
        $type = $_POST['type'] ?? 'daily';
        $order = intval($_POST['order'] ?? 0);
        
        if (empty($id) || empty($title) || $reward <= 0) {
            $error = "সব তথ্য পূরণ করুন";
        } else {
            $stmt = $db->prepare("INSERT INTO daily_tasks (id, title, reward, target, type, \"order\", is_active) VALUES (?, ?, ?, ?, ?, ?, true)");
            if ($stmt->execute([$id, $title, $reward, $target, $type, $order])) {
                $message = "নতুন টাস্ক যোগ করা হয়েছে!";
            } else {
                $error = "টাস্ক যোগ করতে ব্যর্থ হয়েছে";
            }
        }
    }
    
    // Edit task
    if ($action == 'edit') {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $reward = floatval($_POST['reward'] ?? 0);
        $target = intval($_POST['target'] ?? 1);
        $type = $_POST['type'] ?? 'daily';
        $order = intval($_POST['order'] ?? 0);
        
        $stmt = $db->prepare("UPDATE daily_tasks SET title = ?, reward = ?, target = ?, type = ?, \"order\" = ? WHERE id = ?");
        if ($stmt->execute([$title, $reward, $target, $type, $order, $id])) {
            $message = "টাস্ক আপডেট করা হয়েছে!";
        } else {
            $error = "টাস্ক আপডেট করতে ব্যর্থ হয়েছে";
        }
    }
    
    // Delete task
    if ($action == 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM daily_tasks WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = "টাস্ক ডিলিট করা হয়েছে!";
        } else {
            $error = "টাস্ক ডিলিট করতে ব্যর্থ হয়েছে";
        }
    }
    
    // Toggle status
    if ($action == 'toggle_status') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("UPDATE daily_tasks SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $message = "টাস্ক স্ট্যাটাস পরিবর্তন করা হয়েছে!";
    }
}

// Get all tasks
$tasks = $db->query("SELECT * FROM daily_tasks ORDER BY \"order\"")->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>টাস্ক ম্যানেজ - Eflix Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
    <script>
        function showAddModal() {
            document.getElementById('modalTitle').innerText = 'নতুন টাস্ক যোগ করুন';
            document.getElementById('action').value = 'add';
            document.getElementById('taskId').value = '';
            document.getElementById('taskTitle').value = '';
            document.getElementById('taskReward').value = '';
            document.getElementById('taskTarget').value = '1';
            document.getElementById('taskType').value = 'daily';
            document.getElementById('taskOrder').value = '';
            document.getElementById('taskModal').classList.remove('hidden');
            document.getElementById('taskModal').classList.add('flex');
        }
        
        function showEditModal(id, title, reward, target, type, order) {
            document.getElementById('modalTitle').innerText = 'টাস্ক এডিট করুন';
            document.getElementById('action').value = 'edit';
            document.getElementById('taskId').value = id;
            document.getElementById('taskTitle').value = title;
            document.getElementById('taskReward').value = reward;
            document.getElementById('taskTarget').value = target;
            document.getElementById('taskType').value = type;
            document.getElementById('taskOrder').value = order;
            document.getElementById('taskModal').classList.remove('hidden');
            document.getElementById('taskModal').classList.add('flex');
        }
        
        function closeModal() {
            document.getElementById('taskModal').classList.add('hidden');
            document.getElementById('taskModal').classList.remove('flex');
        }
        
        function deleteTask(id) {
            if (confirm('আপনি কি এই টাস্ক ডিলিট করতে চান?')) {
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
                <p class="text-xs text-white/60">টাস্ক ম্যানেজমেন্ট</p>
            </div>
            <nav class="mt-8">
                <a href="index.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">📊 ড্যাশবোর্ড</a>
                <a href="users.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">👥 ইউজার</a>
                <a href="packages.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💎 প্যাকেজ</a>
                <a href="tasks.php?admin=eflix2024" class="block py-2 px-4 bg-white/20">📋 টাস্ক</a>
                <a href="withdrawals.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💸 উইথড্র</a>
                <a href="deposits.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💰 ডিপোজিট</a>
                <a href="settings.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">⚙️ সেটিংস</a>
                <a href="../logout.php" class="block py-2 px-4 mt-8 text-red-300 hover:bg-red-800">🚪 লগআউট</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-64 p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">📋 টাস্ক ম্যানেজমেন্ট</h1>
                <button onclick="showAddModal()" class="bg-green-500 text-white px-4 py-2 rounded-lg">
                    + নতুন টাস্ক যোগ করুন
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
                                <th class="text-left p-3">টাস্কের নাম</th>
                                <th class="text-left p-3">রিওয়ার্ড</th>
                                <th class="text-left p-3">টার্গেট</th>
                                <th class="text-left p-3">টাইপ</th>
                                <th class="text-left p-3">অর্ডার</th>
                                <th class="text-left p-3">স্ট্যাটাস</th>
                                <th class="text-left p-3">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-mono text-sm"><?php echo htmlspecialchars($task['id']); ?></td>
                                <td class="p-3 font-semibold"><?php echo htmlspecialchars($task['title']); ?></td>
                                <td class="p-3 text-green-600 font-bold">$<?php echo number_format($task['reward'], 2); ?></td>
                                <td class="p-3"><?php echo $task['target']; ?> বার</span></td>
                                <td class="p-3">
                                    <?php if ($task['type'] == 'daily'): ?>
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">ডেইলি</span>
                                    <?php else: ?>
                                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">ওয়ান-টাইম</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3"><?php echo $task['order']; ?></span></td>
                                <td class="p-3">
                                    <?php if ($task['is_active']): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">সক্রিয়</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">নিষ্ক্রিয়</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <button onclick="showEditModal('<?php echo $task['id']; ?>', '<?php echo htmlspecialchars($task['title']); ?>', <?php echo $task['reward']; ?>, <?php echo $task['target']; ?>, '<?php echo $task['type']; ?>', <?php echo $task['order']; ?>)" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">✏️ এডিট</button>
                                    <button onclick="toggleStatus('<?php echo $task['id']; ?>')" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">🔄 স্ট্যাটাস</button>
                                    <button onclick="deleteTask('<?php echo $task['id']; ?>')" class="bg-red-500 text-white px-2 py-1 rounded text-xs">🗑️ ডিলিট</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if (empty($tasks)): ?>
                <div class="bg-white rounded-xl p-8 text-center text-gray-500 mt-4">
                    <i class="ri-task-line text-5xl mb-2 block"></i>
                    <p>কোনো টাস্ক নেই</p>
                    <button onclick="showAddModal()" class="mt-2 text-purple-600">নতুন টাস্ক যোগ করুন</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Task Modal -->
    <div id="taskModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-96">
            <h3 id="modalTitle" class="font-bold text-lg mb-4">টাস্ক</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" id="action">
                <input type="hidden" name="id" id="taskId">
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">টাস্ক আইডি (ইউনিক)</label>
                    <input type="text" name="id" id="taskIdInput" class="w-full border rounded-lg px-3 py-2" required>
                    <p class="text-xs text-gray-500 mt-1">যেমন: task1, task2, daily_watch</p>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">টাস্কের নাম</label>
                    <input type="text" name="title" id="taskTitle" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">রিওয়ার্ড ($)</label>
                    <input type="number" name="reward" id="taskReward" class="w-full border rounded-lg px-3 py-2" step="0.01" required>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">টার্গেট (কতবার করতে হবে)</label>
                    <input type="number" name="target" id="taskTarget" class="w-full border rounded-lg px-3 py-2" value="1" required>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">টাস্ক টাইপ</label>
                    <select name="type" id="taskType" class="w-full border rounded-lg px-3 py-2">
                        <option value="daily">ডেইলি (প্রতিদিন করতে পারবে)</option>
                        <option value="one-time">ওয়ান-টাইম (একবারই করতে পারবে)</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">অর্ডার (ছোট সংখ্যা উপরে দেখাবে)</label>
                    <input type="number" name="order" id="taskOrder" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-lg">বাতিল</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg">সংরক্ষণ</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fix for edit modal - id field should be disabled for edit
        function showEditModal(id, title, reward, target, type, order) {
            document.getElementById('modalTitle').innerText = 'টাস্ক এডিট করুন';
            document.getElementById('action').value = 'edit';
            document.getElementById('taskId').value = id;
            document.getElementById('taskIdInput').value = id;
            document.getElementById('taskIdInput').disabled = true;
            document.getElementById('taskTitle').value = title;
            document.getElementById('taskReward').value = reward;
            document.getElementById('taskTarget').value = target;
            document.getElementById('taskType').value = type;
            document.getElementById('taskOrder').value = order;
            document.getElementById('taskModal').classList.remove('hidden');
            document.getElementById('taskModal').classList.add('flex');
        }
        
        function showAddModal() {
            document.getElementById('modalTitle').innerText = 'নতুন টাস্ক যোগ করুন';
            document.getElementById('action').value = 'add';
            document.getElementById('taskId').value = '';
            document.getElementById('taskIdInput').value = '';
            document.getElementById('taskIdInput').disabled = false;
            document.getElementById('taskTitle').value = '';
            document.getElementById('taskReward').value = '';
            document.getElementById('taskTarget').value = '1';
            document.getElementById('taskType').value = 'daily';
            document.getElementById('taskOrder').value = '';
            document.getElementById('taskModal').classList.remove('hidden');
            document.getElementById('taskModal').classList.add('flex');
        }
    </script>
</body>
</html>