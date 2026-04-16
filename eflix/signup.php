<?php
// signup.php
require_once 'config/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'সব ফিল্ড পূরণ করুন';
    } elseif (strlen($password) < 6) {
        $error = 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে';
    } else {
        $result = register($full_name, $email, $password, $phone);
        if ($result['success']) {
            $success = $result['message'];
            header('refresh:2;url=login.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সাইনআপ - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gradient-to-br from-purple-600 to-indigo-700 min-h-screen">

    <div class="container mx-auto px-4 max-w-md mx-auto min-h-screen flex flex-col justify-center py-8">
        
        <a href="index.php" class="text-white mb-6 inline-block">
            <i class="ri-arrow-left-line text-2xl"></i>
        </a>

        <div class="text-center mb-8">
            <i class="ri-user-add-line text-6xl text-yellow-400"></i>
            <h1 class="text-2xl font-bold text-white mt-3">সাইনআপ</h1>
            <p class="text-white/70 text-sm">Eflix এ জয়েন করুন</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 rounded-lg p-3 mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500/20 border border-green-500 text-green-200 rounded-lg p-3 mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white/10 backdrop-blur rounded-2xl p-6">
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-white text-sm mb-2">পূর্ণ নাম</label>
                    <input type="text" name="full_name" class="w-full bg-white/20 border border-white/30 rounded-lg px-4 py-2 text-white placeholder-white/50" placeholder="জন ডো" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-white text-sm mb-2">ইমেইল</label>
                    <input type="email" name="email" class="w-full bg-white/20 border border-white/30 rounded-lg px-4 py-2 text-white placeholder-white/50" placeholder="example@mail.com" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-white text-sm mb-2">পাসওয়ার্ড</label>
                    <input type="password" name="password" class="w-full bg-white/20 border border-white/30 rounded-lg px-4 py-2 text-white placeholder-white/50" placeholder="********" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-white text-sm mb-2">ফোন নম্বর</label>
                    <input type="tel" name="phone" class="w-full bg-white/20 border border-white/30 rounded-lg px-4 py-2 text-white placeholder-white/50" placeholder="+8801XXXXXXXXX">
                </div>
                
                <button type="submit" class="w-full bg-yellow-500 text-black font-semibold py-3 rounded-lg">
                    সাইনআপ করুন →
                </button>
            </form>
            
            <p class="text-center text-white/60 text-sm mt-4">
                ইতিমধ্যে একাউন্ট আছে? <a href="login.php" class="text-yellow-400">লগইন করুন</a>
            </p>
        </div>
    </div>

</body>
</html>