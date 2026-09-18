<?php
require_once 'db.php';
include 'header.php'; 

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

// ডাটাবেস থেকে প্রোডাক্ট লোড করা
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? OR code = ?");
$stmt->execute([$product_id, $product_id]);
$current_product = $stmt->fetch();

if (!$current_product) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$inputType = $current_product['input_type'] ?? 'UID';
$category_code = $current_product['code'];
?>

<div class="pt-20 max-w-[520px] mx-auto min-h-screen bg-white shadow-xl pb-16 px-4">
    <!-- Top Bar -->
    <div class="py-3.5 flex items-center gap-3 border-b border-gray-100 mb-4">
        <a href="index.php" class="w-9 h-9 rounded-full bg-slate-50 flex items-center justify-center text-gray-500 hover:text-black">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <h1 class="text-base md:text-lg font-bold text-gray-800 truncate"><?php echo htmlspecialchars($current_product['name']); ?></h1>
    </div>

    <!-- Product Card -->
    <div class="flex items-center gap-4 bg-gradient-to-r from-purple-50 to-indigo-50 p-4 rounded-2xl border border-purple-100/80 mb-6">
        <img src="<?php echo htmlspecialchars($current_product['image']); ?>" class="w-16 h-16 rounded-xl object-cover shadow-sm border border-white">
        <div>
            <h2 class="font-black text-gray-900 text-base md:text-lg"><?php echo htmlspecialchars($current_product['name']); ?></h2>
            <span class="inline-block mt-1 text-[11px] bg-purple-600 text-white font-bold px-2.5 py-0.5 rounded-md shadow-sm">
                <?php echo $inputType; ?> TOPUP
            </span>
        </div>
    </div>

    <!-- Step 1: Account Information -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2.5">
            <span class="w-6 h-6 rounded-full bg-purple-600 text-white text-xs font-bold flex items-center justify-center">1</span>
            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Account Information</h3>
        </div>

        <?php if($inputType === 'UID'): ?>
            <div class="flex gap-2">
                <input type="number" id="inputUID" placeholder="Enter Player ID (UID)" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 outline-none focus:border-purple-600 font-bold text-slate-800">
                <button onclick="checkUid()" id="uidCheckBtn" class="bg-purple-600 hover:bg-purple-700 text-white px-5 rounded-xl font-bold text-sm transition shadow-md">Check</button>
            </div>
            <p id="uidPlayerName" class="text-xs font-bold text-green-600 mt-2 hidden"></p>
        <?php else: ?>
            <div class="space-y-3">
                <select id="loginType" class="w-full border-2 border-gray-200 p-3 rounded-xl outline-none font-semibold text-sm">
                    <option value="Facebook">Facebook Login</option>
                    <option value="Gmail">Gmail Login</option>
                </select>
                <input type="text" id="inputEmail" placeholder="Email or Phone Number" class="w-full border-2 border-gray-200 p-3 rounded-xl outline-none text-sm font-semibold">
                <input type="text" id="inputPass" placeholder="Password" class="w-full border-2 border-gray-200 p-3 rounded-xl outline-none text-sm font-semibold">
            </div>
        <?php endif; ?>
    </div>

    <!-- Step 2: Select Package -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="w-6 h-6 rounded-full bg-purple-600 text-white text-xs font-bold flex items-center justify-center">2</span>
            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Select Package</h3>
        </div>
        <div id="pkgContainer" class="grid grid-cols-2 gap-3">
            <p class="text-sm text-gray-400 col-span-2 text-center py-6">প্যাকেজ লোড হচ্ছে...</p>
        </div>
    </div>

    <!-- Checkout Action Box -->
    <div id="checkoutBox" class="hidden p-4 bg-slate-50 border border-purple-200 rounded-2xl text-center space-y-3 mt-6 shadow-sm">
        <div class="flex justify-between items-center px-1">
            <span class="font-bold text-gray-600 text-sm">Total Payable:</span>
            <span class="text-2xl font-black text-purple-700">৳ <span id="finalPrice">0</span></span>
        </div>
        <button onclick="proceedToCheckout()" class="w-full bg-slate-900 hover:bg-black text-white py-4 rounded-xl font-bold shadow-lg transition transform active:scale-98">
            PROCEED TO CHECKOUT
        </button>
    </div>
</div>

<script>
    let selectedPkg = null;
    const categoryCode = "<?php echo $category_code; ?>";

    async function loadPackages() {
        const res = await fetch(`ajax.php?action=get_packages&category=${categoryCode}`);
        const d = await res.json();
        const container = document.getElementById('pkgContainer');
        container.innerHTML = '';

        if(d.status === 'success' && d.data.length > 0) {
            d.data.forEach(pkg => {
                const btn = document.createElement('div');
                btn.className = "border-2 border-gray-200 p-3.5 rounded-xl cursor-pointer hover:border-purple-500 transition relative bg-white";
                btn.innerHTML = `
                    <p class="font-bold text-gray-800 text-sm leading-tight">${pkg.name}</p>
                    <p class="font-black text-purple-600 text-base mt-1.5">৳${pkg.price}</p>
                    ${pkg.tag ? `<span class="absolute top-2 right-2 bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded">${pkg.tag}</span>` : ''}
                `;
                btn.onclick = () => {
                    document.querySelectorAll('#pkgContainer > div').forEach(el => el.classList.remove('border-purple-600', 'bg-purple-50'));
                    btn.classList.add('border-purple-600', 'bg-purple-50');
                    selectedPkg = pkg;
                    document.getElementById('finalPrice').innerText = pkg.price;
                    document.getElementById('checkoutBox').classList.remove('hidden');
                };
                container.appendChild(btn);
            });
        } else {
            container.innerHTML = '<p class="col-span-2 text-center text-sm text-gray-400 py-6 bg-gray-50 rounded-xl border">এই আইটেমে কোনো প্যাকেজ যুক্ত করা নেই। অ্যাডমিন প্যানেল থেকে প্যাকেজ যুক্ত করুন।</p>';
        }
    }

    async function checkUid() {
        const uid = document.getElementById('inputUID').value.trim();
        if(!uid) return Swal.fire('Warning', 'Player ID (UID) লিখুন!', 'warning');
        const btn = document.getElementById('uidCheckBtn');
        btn.innerText = '...'; btn.disabled = true;

        try {
            const res = await fetch(`https://bhauxinfo2.vercel.app/bhau?uid=${uid}&region=BD`);
            const d = await res.json();
            if(d && d.basicInfo) {
                const p = document.getElementById('uidPlayerName');
                p.innerText = "Player: " + d.basicInfo.nickname;
                p.classList.remove('hidden');
                btn.innerText = 'OK';
            } else {
                Swal.fire('Error', 'Player Not Found!', 'error');
                btn.innerText = 'Check';
            }
        } catch(e) {
            btn.innerText = 'Check';
        } finally {
            btn.disabled = false;
        }
    }

    function proceedToCheckout() {
        if(!selectedPkg) return Swal.fire('Warning', 'একটি প্যাকেজ নির্বাচন করুন!', 'warning');
        
        let userData = {};
        const inputType = "<?php echo $inputType; ?>";
        if(inputType === 'UID') {
            const uid = document.getElementById('inputUID').value.trim();
            if(!uid) return Swal.fire('Warning', 'Player ID (UID) দিন!', 'warning');
            userData = { type: 'UID', uid: uid, account: document.getElementById('uidPlayerName').innerText || 'Player' };
        } else {
            const email = document.getElementById('inputEmail').value.trim();
            const pass = document.getElementById('inputPass').value.trim();
            if(!email || !pass) return Swal.fire('Warning', 'লগইন তথ্য পূরণ করুন!', 'warning');
            userData = { type: 'LOGIN', account: document.getElementById('loginType').value, email: email, pass: pass };
        }

        const checkoutData = {
            productName: "<?php echo htmlspecialchars($current_product['name']); ?>",
            pkgName: selectedPkg.name,
            pkgId: selectedPkg.id,
            price: selectedPkg.price,
            userData: userData
        };

        localStorage.setItem('checkoutData', JSON.stringify(checkoutData));
        window.location.href = 'checkout.php';
    }

    loadPackages();
</script>

<?php include 'footer.php'; ?>