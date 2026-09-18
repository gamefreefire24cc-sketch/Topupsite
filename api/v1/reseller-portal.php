<?php
// সাব-ফোল্ডার থেকে রুট ফোল্ডারে থাকা db.php ফাইলটি এক ধাপ উপরে গিয়ে কানেক্ট করা হচ্ছে
require_once '../../db.php';

$error = '';
$success = '';

// রিসেলার লগআউট
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['reseller_uid']);
    header("Location: reseller-portal.php");
    exit();
}

// রিসেলার লগইন প্রসেস
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_reseller'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $token = trim($_POST['api_token'] ?? '');

    if (!empty($email) && !empty($token)) {
        $stmt = $pdo->prepare("SELECT * FROM resellers WHERE email = ? AND api_token = ? AND status = 'Active'");
        $stmt->execute([$email, $token]);
        $reseller = $stmt->fetch();

        if ($reseller) {
            $_SESSION['reseller_uid'] = $reseller['uid'];
            header("Location: reseller-portal.php");
            exit();
        } else {
            $error = "ভুল ইমেইল অথবা এপিআই টোকেন!";
        }
    } else {
        $error = "সবগুলো ঘর পূরণ করুন!";
    }
}

// লগইন করা থাকলে রিসেলার ডাটা ফেচ করা
$currentReseller = null;
if (isset($_SESSION['reseller_uid'])) {
    $stmt = $pdo->prepare("SELECT * FROM resellers WHERE uid = ?");
    $stmt->execute([$_SESSION['reseller_uid']]);
    $currentReseller = $stmt->fetch();
}

// নতুন API Token জেনারেট করার লজিক
if ($currentReseller && isset($_POST['regenerate_token'])) {
    $newToken = bin2hex(random_bytes(24));
    $pdo->prepare("UPDATE resellers SET api_token = ? WHERE uid = ?")->execute([$newToken, $currentReseller['uid']]);
    header("Location: reseller-portal.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller API Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-xl w-full bg-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-700">
        
        <?php if (!$currentReseller): ?>
            <!-- 🔐 LOGIN FORM -->
            <div class="text-center mb-6">
                <div class="w-14 h-14 bg-purple-600 rounded-2xl mx-auto flex items-center justify-center text-2xl font-bold shadow-lg shadow-purple-600/30 mb-3">
                    <i class="ri-shield-user-line"></i>
                </div>
                <h1 class="text-2xl font-black">Reseller Portal</h1>
                <p class="text-xs text-slate-400 mt-1">আপনার এপিআই টোকেন দিয়ে রিসেলার অ্যাকাউন্টে প্রবেশ করুন</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold p-3 rounded-xl mb-4 text-center">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Reseller Email</label>
                    <input type="email" name="email" required placeholder="reseller@gmail.com" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-sm outline-none focus:border-purple-500 text-white font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">API Token</label>
                    <input type="text" name="api_token" required placeholder="fa4b56c0248e0ee..." class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-sm outline-none focus:border-purple-500 text-white font-mono">
                </div>
                <button type="submit" name="login_reseller" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-purple-600/25">
                    Login to Portal
                </button>
            </form>

        <?php else: ?>
            <!-- 📊 RESELLER DASHBOARD / PROFILE -->
            <div class="flex justify-between items-center border-b border-slate-700 pb-4 mb-6">
                <div>
                    <h2 class="text-xl font-black text-white"><?php echo htmlspecialchars($currentReseller['name']); ?></h2>
                    <p class="text-xs text-slate-400 font-mono"><?php echo htmlspecialchars($currentReseller['email']); ?></p>
                </div>
                <a href="reseller-portal.php?action=logout" class="px-3.5 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-bold rounded-xl border border-rose-500/20 transition flex items-center gap-1.5">
                    <i class="ri-logout-box-r-line"></i> Logout
                </a>
            </div>

            <!-- Wallet Card -->
            <div class="bg-gradient-to-r from-purple-900/60 to-indigo-900/60 border border-purple-500/30 p-5 rounded-2xl mb-6 flex justify-between items-center">
                <div>
                    <p class="text-xs font-bold text-purple-300 uppercase tracking-wider">Available API Balance</p>
                    <h3 class="text-3xl font-black text-white mt-1">৳<?php echo number_format($currentReseller['wallet_balance']); ?></h3>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-extrabold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <?php echo $currentReseller['status']; ?>
                    </span>
                    <p class="text-[10px] text-slate-400 mt-2">ব্যালেন্স বাড়াতে অ্যাডমিনের সাথে যোগাযোগ করুন</p>
                </div>
            </div>

            <!-- API Credentials Box -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Your Secret API Token</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="<?php echo htmlspecialchars($currentReseller['api_token']); ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-xs font-mono text-purple-300 select-all">
                        <button onclick="navigator.clipboard.writeText('<?php echo $currentReseller['api_token']; ?>'); alert('Token Copied!');" class="bg-slate-700 hover:bg-slate-600 px-4 rounded-xl text-xs font-bold">Copy</button>
                    </div>
                </div>

                <div class="bg-slate-900/50 p-4 rounded-2xl border border-slate-700 space-y-2 text-xs text-slate-300">
                    <p class="font-bold text-white mb-1"><i class="ri-code-s-slash-line text-purple-400"></i> API Endpoints for Developers:</p>
                    <p>• <b>Order API:</b> <code class="text-purple-300 font-mono">https://<?php echo $_SERVER['HTTP_HOST']; ?>/api/v1/order.php</code></p>
                    <p>• <b>Products API:</b> <code class="text-purple-300 font-mono">https://<?php echo $_SERVER['HTTP_HOST']; ?>/api/v1/products.php</code></p>
                </div>

                <form method="POST">
                    <button type="submit" name="regenerate_token" onclick="return confirm('সতর্কতা! নতুন টোকেন জেনারেট করলে আগের টোকেন কাজ করা বন্ধ করে দেবে। নিশ্চিত?');" class="w-full bg-slate-700 hover:bg-slate-600 text-slate-200 font-bold py-3 rounded-xl text-xs transition border border-slate-600">
                        Regenerate New API Token
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="mt-6 text-center text-xs text-slate-500 border-t border-slate-700/60 pt-4">
            <a href="../../index.php" class="hover:text-purple-400 transition">← মূল ওয়েবসাইটে ফিরে যান</a>
        </div>

    </div>

</body>
</html>