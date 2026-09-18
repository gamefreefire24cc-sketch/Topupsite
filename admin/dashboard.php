<?php 
include 'header.php'; 

// ডাটাবেস থেকে রিয়েলটাইম পরিসংখ্যান লোড
$todayRevenue = $pdo->query("SELECT COALESCE(SUM(price), 0) FROM orders WHERE status = 'Completed'")->fetchColumn() ?: 0;
$totalWalletBalance = $pdo->query("SELECT COALESCE(SUM(balance), 0) FROM users")->fetchColumn() ?: 0;
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn() ?: 0;
$pendingDeposits = $pdo->query("SELECT COUNT(*) FROM deposit_requests WHERE status = 'Pending'")->fetchColumn() ?: 0;
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;

// শেষ ৫টি সাম্প্রতিক অর্ডার
$latestOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<!-- ড্যাশবোর্ড হেডার -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-500 animate-pulse"></span>
            <span class="text-xs font-black uppercase text-purple-600 tracking-wider">Live Analytics</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Financial & System Overview</h1>
        <p class="text-xs md:text-sm text-slate-500">সমস্ত ইউজার ব্যালেন্স, ইনকাম ও অর্ডারের সর্বশেষ লাইভ রিপোর্ট।</p>
    </div>
    
    <div class="flex items-center gap-2.5">
        <button onclick="location.reload()" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition border border-slate-200/80 shadow-sm flex items-center gap-1.5">
            <i class="ri-refresh-line text-purple-600 text-sm"></i> Refresh Data
        </button>
        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Realtime SQLite
        </span>
    </div>
</div>

<!-- ৫টি গর্জিয়াস মেট্রিক কার্ড (উভয় ইনকাম ও মোট ওয়ালেট ব্যালেন্স সহ) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-5 mb-8">
    
    <!-- ১. Total Revenue (মোট আয়) -->
    <div class="admin-card p-5 relative overflow-hidden group border-purple-100/80">
        <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-purple-100/60 rounded-full group-hover:scale-125 transition-transform duration-500 -z-0"></div>
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Revenue</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1.5">৳ <?php echo number_format($todayRevenue); ?></h3>
                <span class="inline-block mt-2 text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-100">
                    Completed Sales
                </span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-xl shadow-lg shadow-purple-500/25">
                <i class="ri-money-dollar-circle-line"></i>
            </div>
        </div>
    </div>

    <!-- ২. Total User Wallet Balance (সব ইউজারদের মোট ওয়ালেট ব্যালেন্স) -->
    <div class="admin-card p-5 relative overflow-hidden group border-indigo-100/80 bg-gradient-to-br from-white to-indigo-50/20">
        <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-indigo-100/60 rounded-full group-hover:scale-125 transition-transform duration-500 -z-0"></div>
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-500">User Wallet Funds</p>
                <h3 class="text-2xl font-black text-indigo-900 mt-1.5">৳ <?php echo number_format($totalWalletBalance); ?></h3>
                <span class="inline-block mt-2 text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-200/60">
                    Total In-Wallet
                </span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center text-xl shadow-lg shadow-indigo-500/25">
                <i class="ri-wallet-3-fill"></i>
            </div>
        </div>
    </div>

    <!-- ৩. Pending Orders (অপেক্ষমান অর্ডার) -->
    <a href="orders.php" class="admin-card p-5 relative overflow-hidden group block hover:border-amber-300">
        <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-amber-100/60 rounded-full group-hover:scale-125 transition-transform duration-500 -z-0"></div>
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Pending Orders</p>
                <h3 class="text-2xl font-black text-amber-500 mt-1.5"><?php echo $pendingOrders; ?></h3>
                <span class="inline-block mt-2 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                    To Deliver
                </span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center text-xl shadow-lg shadow-amber-500/25">
                <i class="ri-shopping-bag-3-line"></i>
            </div>
        </div>
    </a>

    <!-- ৪. Pending Deposits (অপেক্ষমান ডিপোজিট) -->
    <a href="deposits.php" class="admin-card p-5 relative overflow-hidden group block hover:border-blue-300">
        <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-blue-100/60 rounded-full group-hover:scale-125 transition-transform duration-500 -z-0"></div>
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Add Money Req</p>
                <h3 class="text-2xl font-black text-blue-600 mt-1.5"><?php echo $pendingDeposits; ?></h3>
                <span class="inline-block mt-2 text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                    Verify TrxID
                </span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-cyan-500 text-white flex items-center justify-center text-xl shadow-lg shadow-blue-500/25">
                <i class="ri-secure-payment-fill"></i>
            </div>
        </div>
    </a>

    <!-- ৫. Total Users (মোট গ্রাহক) -->
    <div class="admin-card p-5 relative overflow-hidden group">
        <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-emerald-100/60 rounded-full group-hover:scale-125 transition-transform duration-500 -z-0"></div>
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Registered Users</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1.5"><?php echo $totalUsers; ?></h3>
                <span class="inline-block mt-2 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                    Active Accounts
                </span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center text-xl shadow-lg shadow-emerald-500/25">
                <i class="ri-user-star-fill"></i>
            </div>
        </div>
    </div>

</div>

<!-- কুইক অপারেশন ও সাম্প্রতিক অ্যাক্টিভিটি -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- LEFT: Recent Orders Stream -->
    <div class="lg:col-span-2 admin-card p-6">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Latest Orders Stream</h3>
                <p class="text-xs text-slate-400">রিয়েলটাইম গ্রাহকদের কেনাকাটার লাইভ তালিকা</p>
            </div>
            <a href="orders.php" class="text-xs font-bold text-purple-600 hover:text-purple-700 flex items-center gap-1">
                View All Orders <i class="ri-arrow-right-s-line text-base"></i>
            </a>
        </div>

        <div class="space-y-3">
            <?php if(empty($latestOrders)): ?>
                <div class="text-center py-10 text-slate-400">
                    <i class="ri-inbox-line text-4xl opacity-50 block mb-1"></i>
                    <p class="text-xs font-medium">এখনও কোনো অর্ডার আসেনি।</p>
                </div>
            <?php endif; ?>

            <?php foreach($latestOrders as $ord): ?>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between hover:bg-white hover:border-purple-200 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                            <?php echo strtoupper(substr($ord['userName'] ?: 'U', 0, 1)); ?>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-slate-800 text-sm truncate"><?php echo htmlspecialchars($ord['product']); ?></h4>
                            <p class="text-xs text-slate-400 truncate mt-0.5"><?php echo htmlspecialchars($ord['package']); ?> • User: <?php echo htmlspecialchars($ord['userName']); ?></p>
                        </div>
                    </div>
                    <div class="text-right shrink-0 ml-3">
                        <span class="text-sm font-black text-slate-900 block">৳<?php echo $ord['price']; ?></span>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full inline-block mt-0.5 <?php echo $ord['status'] === 'Completed' ? 'bg-emerald-100 text-emerald-700' : ($ord['status'] === 'Rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700'); ?>">
                            <?php echo $ord['status']; ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT: Quick Controls Tile -->
    <div class="admin-card p-6 flex flex-col justify-between">
        <div>
            <h3 class="font-extrabold text-slate-900 text-base mb-4 flex items-center gap-2">
                <i class="ri-flashlight-fill text-amber-500"></i> Quick Navigation
            </h3>

            <div class="space-y-3">
                <a href="packages.php" class="flex items-center gap-3 p-3.5 rounded-2xl bg-purple-50/70 hover:bg-purple-100/70 border border-purple-100 text-purple-700 transition">
                    <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center text-lg shadow-sm">
                        <i class="ri-add-circle-line"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs">Add New Package</h4>
                        <p class="text-[11px] text-purple-500">ডায়মন্ড বা কয়েন প্যাকেজ তৈরি</p>
                    </div>
                </a>

                <a href="banners.php" class="flex items-center gap-3 p-3.5 rounded-2xl bg-indigo-50/70 hover:bg-indigo-100/70 border border-indigo-100 text-indigo-700 transition">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg shadow-sm">
                        <i class="ri-image-add-line"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs">Manage Banners</h4>
                        <p class="text-[11px] text-indigo-500">হোমপেজ স্লাইডার কন্ট্রোল</p>
                    </div>
                </a>

                <a href="settings.php" class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200/70 border border-slate-200 text-slate-700 transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center text-lg shadow-sm">
                        <i class="ri-notification-3-line"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs">Notice & Gateways</h4>
                        <p class="text-[11px] text-slate-500">বিকাশ/নগদ নাম্বার ও নোটিশ</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="mt-6 p-4 rounded-2xl bg-slate-900 text-white">
            <p class="text-xs text-slate-400 font-medium">Platform Status</p>
            <div class="flex items-center justify-between mt-1">
                <span class="text-sm font-bold text-emerald-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> 100% Operational
                </span>
                <span class="text-[11px] text-slate-400 font-mono">SQLite DB</span>
            </div>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>