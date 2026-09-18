<?php 
include 'header.php'; 

// ডাটাবেস থেকে জয়েন (JOIN) করে অর্ডার, ইউজারের তথ্য এবং প্রোডাক্টের ছবি একসাথ করে আনা হচ্ছে
$sql = "
    SELECT 
        o.*, 
        p.image as product_image, 
        u.email as user_email, 
        u.phone as user_phone 
    FROM orders o 
    LEFT JOIN products p ON o.product = p.name 
    LEFT JOIN users u ON o.userId = u.uid
    ORDER BY o.id DESC
";
$orders = $pdo->query($sql)->fetchAll();

// স্ট্যাটাস ভিত্তিক গণনা
$totalCount = count($orders);
$pendingCount = 0;
$completedCount = 0;
$rejectedCount = 0;

foreach ($orders as $o) {
    if ($o['status'] === 'Pending') $pendingCount++;
    elseif ($o['status'] === 'Completed') $completedCount++;
    elseif ($o['status'] === 'Rejected') $rejectedCount++;
}
?>

<!-- স্টাইলিশ পেজ হেডার ও ফিল্টার -->
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-600 animate-pulse"></span>
            <span class="text-[11px] font-black uppercase text-purple-600 tracking-wider">Order Hub</span>
        </div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Orders Management</h1>
        <p class="text-sm text-slate-500 mt-1">কার্ড ভিউয়ের মাধ্যমে প্রতিটি অর্ডারের বিস্তারিত তথ্য ও ছবি।</p>
    </div>

    <!-- মডার্ন সেগমেন্টেড ফিল্টার বাটন -->
    <div class="inline-flex bg-slate-200/60 p-1.5 rounded-2xl overflow-x-auto w-full sm:w-auto shadow-inner border border-slate-200">
        <button onclick="filterStatus('ALL')" id="tab_ALL" class="status-tab active px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-white text-purple-700 shadow-sm border border-slate-100">
            <i class="ri-function-line"></i> All
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-600 font-mono"><?php echo $totalCount; ?></span>
        </button>

        <button onclick="filterStatus('Pending')" id="tab_Pending" class="status-tab px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-slate-600 hover:text-amber-600">
            <i class="ri-time-line"></i> Pending
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-700 font-mono"><?php echo $pendingCount; ?></span>
        </button>

        <button onclick="filterStatus('Completed')" id="tab_Completed" class="status-tab px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-slate-600 hover:text-emerald-600">
            <i class="ri-check-double-line"></i> Completed
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-700 font-mono"><?php echo $completedCount; ?></span>
        </button>

        <button onclick="filterStatus('Rejected')" id="tab_Rejected" class="status-tab px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-slate-600 hover:text-rose-600">
            <i class="ri-close-circle-line"></i> Rejected
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-100 text-rose-700 font-mono"><?php echo $rejectedCount; ?></span>
        </button>
    </div>
</div>

<!-- ইনস্ট্যান্ট সার্চ টুলবার -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
    <div class="relative w-full sm:w-[500px]">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="ri-search-2-line text-slate-400 text-lg"></i>
        </div>
        <input type="text" id="orderSearch" onkeyup="searchOrders()" placeholder="Player ID, User Name, Email, TrxID বা Game দিয়ে খুঁজুন..." class="w-full bg-white border-2 border-slate-200/80 rounded-2xl pl-11 pr-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-purple-500 shadow-sm transition">
    </div>
    <div class="text-sm font-bold text-slate-500 bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-2">
        <i class="ri-filter-3-line text-purple-600"></i> Showing: <span id="filteredCount" class="text-purple-600 font-black"><?php echo $totalCount; ?></span>
    </div>
</div>

<!-- 📦 গ্রিড কার্ড লেআউট (Grid Cards) -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pb-12" id="ordersGrid">
    <?php if(empty($orders)): ?>
        <div class="col-span-full py-16 text-center text-slate-400 bg-white rounded-3xl border border-slate-200 border-dashed">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="ri-inbox-archive-line text-4xl text-slate-300"></i>
            </div>
            <p class="font-bold text-slate-600 text-lg">কোনো অর্ডার পাওয়া যায়নি!</p>
            <p class="text-xs mt-1">গ্রাহকরা অর্ডার করলে তা এখানে কার্ড আকারে দেখা যাবে।</p>
        </div>
    <?php endif; ?>

    <?php foreach($orders as $o): ?>
    <?php 
        // পেমেন্ট মেথড লজিক
        $m = strtoupper($o['paymentMethod']);
        $payColor = 'text-slate-700 bg-slate-100 border-slate-200';
        $payIcon = 'ri-bank-card-line';

        if($m === 'BKASH') { $payColor = 'text-pink-700 bg-pink-50 border-pink-200'; $payIcon = 'ri-smartphone-line'; }
        elseif($m === 'NAGAD') { $payColor = 'text-orange-700 bg-orange-50 border-orange-200'; $payIcon = 'ri-smartphone-line'; }
        elseif($m === 'ROCKET') { $payColor = 'text-purple-700 bg-purple-50 border-purple-200'; $payIcon = 'ri-rocket-line'; }
        elseif($m === 'WALLET') { $payColor = 'text-indigo-700 bg-indigo-50 border-indigo-200'; $payIcon = 'ri-wallet-3-fill'; }
        elseif($m === 'MIXED') { $payColor = 'text-blue-700 bg-blue-50 border-blue-200'; $payIcon = 'ri-exchange-dollar-line'; }

        // স্ট্যাটাস লজিক
        $statusStyle = 'bg-amber-50 text-amber-600 border-amber-200';
        $statusDot = 'bg-amber-500 animate-pulse';
        if($o['status'] === 'Completed') {
            $statusStyle = 'bg-emerald-50 text-emerald-600 border-emerald-200';
            $statusDot = 'bg-emerald-500';
        } elseif($o['status'] === 'Rejected') {
            $statusStyle = 'bg-rose-50 text-rose-600 border-rose-200';
            $statusDot = 'bg-rose-500';
        }

        // ডিটেইলস JSON পার্স
        $details = json_decode($o['orderDetails'], true);
        $isUID = false;
        $uidNumber = "";
        $loginEmail = "";
        $loginPass = "";
        $copyText = "";

        if(is_array($details)) {
            if(isset($details['uid'])) {
                $isUID = true;
                $uidNumber = $details['uid'];
                $copyText = $uidNumber;
            } else {
                $loginEmail = $details['email'] ?? '';
                $loginPass = $details['pass'] ?? '';
                $copyText = "Email: {$loginEmail} | Pass: {$loginPass}";
            }
        } else {
            $copyText = $o['orderDetails'];
        }

        // ইউজার ও প্রোডাক্ট ইমেজ ফলব্যাক
        $userAvatar = "https://ui-avatars.com/api/?name=".urlencode($o['userName'] ?: 'User')."&background=e9d5ff&color=7e22ce&bold=true";
        $productImage = $o['product_image'] ?: 'https://placehold.co/100x100?text=Game';
    ?>
    <div class="order-card bg-white rounded-3xl shadow-sm hover:shadow-xl border border-slate-200 flex flex-col transition-all duration-300 transform hover:-translate-y-1 overflow-hidden" data-status="<?php echo $o['status']; ?>">
        
        <!-- 🏷️ Card Header (Order ID & Status) -->
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
            <span class="text-xs font-mono font-bold text-slate-500">Order #<?php echo str_pad($o['id'], 5, '0', STR_PAD_LEFT); ?></span>
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1.5 border <?php echo $statusStyle; ?>">
                <span class="w-1.5 h-1.5 rounded-full <?php echo $statusDot; ?>"></span>
                <?php echo $o['status']; ?>
            </span>
        </div>

        <!-- 📦 Card Body -->
        <div class="p-5 flex-1 flex flex-col gap-5">
            
            <!-- User Profile Row -->
            <div class="flex items-center gap-3">
                <img src="<?php echo $userAvatar; ?>" class="w-12 h-12 rounded-full shadow-sm border border-purple-100">
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-black text-slate-800 truncate"><?php echo htmlspecialchars($o['userName']); ?></h3>
                    <p class="text-[10px] text-slate-500 font-mono truncate mt-0.5">
                        <i class="ri-mail-line"></i> <?php echo htmlspecialchars($o['user_email'] ?: 'No Email'); ?>
                    </p>
                    <p class="text-[10px] text-slate-500 font-mono truncate">
                        <i class="ri-phone-line"></i> <?php echo htmlspecialchars($o['user_phone'] ?: 'No Phone'); ?>
                    </p>
                </div>
            </div>

            <!-- Product Selected Row -->
            <div class="flex items-center gap-3 bg-gradient-to-r from-purple-50 to-indigo-50 p-3 rounded-2xl border border-purple-100/60">
                <img src="<?php echo htmlspecialchars($productImage); ?>" class="w-12 h-12 rounded-xl object-cover shadow-sm border border-white">
                <div>
                    <h4 class="text-sm font-black text-slate-900 leading-tight"><?php echo htmlspecialchars($o['product']); ?></h4>
                    <span class="inline-block mt-1 text-[11px] font-bold text-purple-700 bg-white px-2 py-0.5 rounded shadow-sm">
                        <?php echo htmlspecialchars($o['package']); ?>
                    </span>
                </div>
            </div>

            <!-- Target Account Info Box -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 relative group">
                <button onclick="copyToClipboard('<?php echo htmlspecialchars($copyText, ENT_QUOTES); ?>', 'Copied!')" class="absolute top-3 right-3 text-xs bg-white text-slate-600 hover:text-purple-600 hover:border-purple-600 px-2 py-1 rounded-lg border shadow-sm transition opacity-0 group-hover:opacity-100 flex items-center gap-1">
                    <i class="ri-file-copy-line"></i> Copy
                </button>

                <?php if($isUID): ?>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Player UID</p>
                    <p class="text-lg font-black font-mono text-slate-800 select-all"><?php echo $uidNumber; ?></p>
                    <?php if(!empty($details['account']) && $details['account'] !== 'Unknown'): ?>
                        <p class="text-xs font-bold text-emerald-600 mt-1 flex items-center gap-1">
                            <i class="ri-user-smile-line"></i> Name: <?php echo htmlspecialchars($details['account']); ?>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1.5"><?php echo $details['account'] ?? 'Login'; ?> Details</p>
                    <div class="space-y-1.5 text-xs font-mono font-bold text-slate-700 select-all">
                        <p class="truncate bg-white px-2 py-1.5 rounded-lg border border-slate-100"><span class="text-slate-400 font-sans">Email:</span> <?php echo htmlspecialchars($loginEmail); ?></p>
                        <p class="truncate bg-white px-2 py-1.5 rounded-lg border border-slate-100 text-rose-600"><span class="text-slate-400 font-sans">Pass:</span> <?php echo htmlspecialchars($loginPass); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Payment Summary -->
            <div class="grid grid-cols-2 gap-4 mt-auto border-t border-slate-100 pt-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Total Paid</p>
                    <p class="text-xl font-black text-slate-900 leading-none mt-1">৳<?php echo $o['price']; ?></p>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md border inline-flex items-center gap-1 mt-2 <?php echo $payColor; ?>">
                        <i class="<?php echo $payIcon; ?>"></i> <?php echo $o['paymentMethod']; ?>
                    </span>
                </div>
                <div class="text-right flex flex-col justify-end">
                    <?php if((int)$o['walletUsed'] > 0): ?>
                        <p class="text-[10px] font-semibold text-slate-500">Wallet: ৳<?php echo $o['walletUsed']; ?></p>
                    <?php endif; ?>
                    <?php if(!empty($o['trxID'])): ?>
                        <div class="mt-1">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">TrxID</p>
                            <p class="text-[11px] font-mono font-black text-slate-700 select-all"><?php echo htmlspecialchars($o['trxID']); ?></p>
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5"><?php echo htmlspecialchars($o['senderNumber']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- 🚀 Card Footer (Action Buttons) -->
        <?php if($o['status'] === 'Pending'): ?>
            <div class="p-3 bg-slate-50 border-t border-slate-200 grid grid-cols-2 gap-3">
                <button onclick="updateOrderStatus(<?php echo $o['id']; ?>, 'Rejected')" class="py-2.5 rounded-xl bg-white border-2 border-rose-100 text-rose-600 hover:bg-rose-50 hover:border-rose-300 text-sm font-bold transition-all flex justify-center items-center gap-1.5">
                    <i class="ri-close-line text-lg"></i> Reject
                </button>
                <button onclick="updateOrderStatus(<?php echo $o['id']; ?>, 'Completed')" class="py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold shadow-md shadow-purple-600/20 transition-all flex justify-center items-center gap-1.5">
                    <i class="ri-check-double-line text-lg"></i> Deliver
                </button>
            </div>
        <?php else: ?>
            <div class="p-3.5 bg-slate-50 border-t border-slate-200 text-center">
                <span class="text-xs text-slate-500 font-bold flex items-center justify-center gap-1">
                    <i class="ri-history-line text-slate-400"></i> Processed on <?php echo date("M d, Y h:i A", strtotime($o['timestamp'])); ?>
                </span>
            </div>
        <?php endif; ?>

    </div>
    <?php endforeach; ?>
</div>

<script>
    // ১. ফিল্টার ট্যাব পরিবর্তন
    function filterStatus(status) {
        document.querySelectorAll('.status-tab').forEach(btn => {
            btn.className = "status-tab px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-slate-600 hover:bg-slate-100";
            const badge = btn.querySelector('span');
            if(badge) badge.className = "px-2 py-0.5 rounded-full text-[10px] bg-slate-200 text-slate-600 font-mono";
        });

        const activeBtn = document.getElementById(`tab_${status}`);
        if(activeBtn) {
            activeBtn.className = "status-tab active px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-white text-purple-700 shadow-sm border border-slate-100";
            const badge = activeBtn.querySelector('span');
            if(badge) badge.className = "px-2 py-0.5 rounded-full text-[10px] bg-purple-100 text-purple-700 font-mono";
        }

        let count = 0;
        const cards = document.querySelectorAll('.order-card');
        cards.forEach(c => {
            if(status === 'ALL' || c.getAttribute('data-status') === status) {
                c.style.display = "flex"; // CSS grid and flex layout logic
                count++;
            } else {
                c.style.display = "none";
            }
        });
        document.getElementById('filteredCount').innerText = count;
    }

    // ২. লাইভ ইনস্ট্যান্ট সার্চ
    function searchOrders() {
        const input = document.getElementById("orderSearch").value.toUpperCase();
        const cards = document.querySelectorAll('.order-card');
        let count = 0;

        cards.forEach(c => {
            const text = c.textContent || c.innerText;
            if(text.toUpperCase().indexOf(input) > -1) {
                c.style.display = "flex";
                count++;
            } else {
                c.style.display = "none";
            }
        });
        document.getElementById('filteredCount').innerText = count;
    }

    // ৩. ক্লিপবোর্ড কপি হেল্পার
    function copyToClipboard(text, msg) {
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: msg,
                showConfirmButton: false,
                timer: 1500
            });
        });
    }

    // ৪. অর্ডার স্ট্যাটাস আপডেট ও স্বয়ংক্রিয় রিফান্ড প্রম্পট
    async function updateOrderStatus(id, status) {
        const isDeliver = status === 'Completed';
        
        Swal.fire({
            title: isDeliver ? 'অর্ডারটি সম্পন্ন করবেন?' : 'অর্ডারটি বাতিল করবেন?',
            text: isDeliver 
                ? "গ্রাহককে অর্ডার বুঝিয়ে দেওয়া হয়েছে হিসেবে মার্ক করা হবে।" 
                : "অর্ডারটি বাতিল হবে এবং ওয়ালেটের টাকা স্বয়ংক্রিয়ভাবে রিফান্ড হয়ে যাবে।",
            icon: isDeliver ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: isDeliver ? '#9333ea' : '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: isDeliver ? 'হ্যাঁ, ডেলিভার করুন' : 'হ্যাঁ, বাতিল ও রিফান্ড'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch('admin_api.php', {
                        method: 'POST',
                        headers: {'Content-Type':'application/json'},
                        body: JSON.stringify({ action: 'update_order_status', id, status })
                    });
                    const d = await res.json();
                    if(d.status === 'success') {
                        Swal.fire({
                            icon: 'success', 
                            title: 'সফল!', 
                            text: d.message, 
                            timer: 1500, 
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        throw new Error(d.message);
                    }
                } catch(e) {
                    Swal.fire('Error', e.message || 'Action failed!', 'error');
                }
            }
        });
    }
</script>

<?php include 'footer.php'; ?>