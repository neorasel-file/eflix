<?php
// login.php
require_once 'config/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'ইমেইল এবং পাসওয়ার্ড দিন';
    } else {
        if (login($email, $password)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'ভুল ইমেইল বা পাসওয়ার্ড';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগইন - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gradient-to-br from-purple-600 to-indigo-700 min-h-screen">

    <div class="container mx-auto px-4 max-w-md mx-auto min-h-screen flex flex-col justify-center py-8">
        
        <a href="index.php" class="text-white mb-6 inline-block">
            <i class="ri-arrow-left-line text-2xl"></i>
        </a>

        <div class="text-center mb-8">
            <i class="ri-login-circle-line text-6xl text-yellow-400"></i>
            <h1 class="text-2xl font-bold text-white mt-3">লগইন</h1>
            <p class="text-white/70 text-sm">আপনার একাউন্টে ঢুকুন</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 rounded-lg p-3 mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white/10 backdrop-blur rounded-2xl p-6">
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-white text-sm mb-2">ইমেইল</label>
                    <input type="email" name="email" class="w-full bg-white/20 border border-white/30 rounded-lg px-4 py-2 text-white placeholder-white/50" placeholder="example@mail.com" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-white text-sm mb-2">পাসওয়ার্ড</label>
                    <input type="password" name="password" class="w-full bg-white/20 border border-white/30 rounded-lg px-4 py-2 text-white placeholder-white/50" placeholder="********" required>
                </div>
                
                <button type="submit" class="w-full bg-yellow-500 text-black font-semibold py-3 rounded-lg">
                    লগইন করুন →
                </button>
            </form>
            
            <p class="text-center text-white/60 text-sm mt-4">
                একাউন্ট নেই? <a href="signup.php" class="text-yellow-400">সাইনআপ করুন</a>
            </p>
        </div>
    </div>

</body>
</html>