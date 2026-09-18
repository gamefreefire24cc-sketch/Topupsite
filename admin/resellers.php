<?php 
include 'header.php'; 

// ব্যালেন্স টপ-আপ রিকুয়েস্ট হ্যান্ডেল করা
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topup_reseller_uid'])) {
    $rUid = $_POST['topup_reseller_uid'];
    $addAmt = (int)$_POST['topup_amount'];
    if($addAmt > 0) {
        $pdo->prepare("UPDATE resellers SET wallet_balance = wallet_balance + ? WHERE uid = ?")->execute([$addAmt, $rUid]);
        echo "<script>Swal.fire('Success', 'Reseller balance updated!', 'success').then(()=>window.location='resellers.php');</script>";
    }
}

$resellers = $pdo->query("SELECT * FROM resellers ORDER BY id DESC")->fetchAll();
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Reseller API Management</h1>
        <p class="text-sm text-slate-500">রিসেলার অ্যাকাউন্ট, এপিআই টোকেন এবং ওয়ালেট ব্যালেন্স নিয়ন্ত্রণ করুন।</p>
    </div>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-md transition">
        <i class="ri-user-add-line"></i> Add New Reseller
    </button>
</div>

<!-- রিসেলার টেবিল -->
<div class="admin-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b text-[11px] uppercase tracking-wider text-slate-400 font-extrabold">
                <tr>
                    <th class="p-4 pl-6">Reseller Name</th>
                    <th class="p-4">API Token</th>
                    <th class="p-4">Wallet Balance</th>
                    <th class="p-4 text-right pr-6">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm">
                <?php if(empty($resellers)): ?>
                    <tr><td colspan="4" class="p-10 text-center text-slate-400">কোনো রিসেলার নেই।</td></tr>
                <?php endif; ?>
                <?php foreach($resellers as $r): ?>
                <tr>
                    <td class="p-4 pl-6">
                        <span class="font-bold text-slate-900 block"><?php echo htmlspecialchars($r['name']); ?></span>
                        <span class="text-xs text-slate-400 font-mono"><?php echo htmlspecialchars($r['email']); ?></span>
                    </td>
                    <td class="p-4 font-mono text-xs select-all bg-slate-50 text-purple-600 font-bold">
                        <?php echo htmlspecialchars($r['api_token']); ?>
                    </td>
                    <td class="p-4 font-black text-emerald-600 text-base">
                        ৳<?php echo number_format($r['wallet_balance']); ?>
                    </td>
                    <td class="p-4 pr-6 text-right">
                        <button onclick="openTopupModal('<?php echo $r['uid']; ?>', '<?php echo htmlspecialchars($r['name'], ENT_QUOTES); ?>')" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-600 text-purple-700 hover:text-white rounded-xl text-xs font-bold transition border border-purple-200 shadow-sm">
                            Add Balance
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Reseller Modal -->
<div id="addModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-900 text-lg">Create Reseller Account</h3>
        <form id="resellerForm" class="space-y-3">
            <input type="text" id="rName" placeholder="Reseller Name" required class="w-full border p-3 rounded-xl outline-none text-sm font-bold">
            <input type="email" id="rEmail" placeholder="Email Address" required class="w-full border p-3 rounded-xl outline-none text-sm font-bold">
            <button type="submit" class="w-full bg-purple-600 text-white font-bold py-3 rounded-xl shadow-md">Create Account</button>
        </form>
        <button onclick="document.getElementById('addModal').classList.add('hidden')" class="w-full bg-gray-100 text-gray-600 font-bold py-2.5 rounded-xl">Cancel</button>
    </div>
</div>

<!-- Topup Modal -->
<div id="topupModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-900 text-lg">Add Balance to <span id="topupResName"></span></h3>
        <form method="POST" class="space-y-3">
            <input type="hidden" name="topup_reseller_uid" id="topupResUid">
            <input type="number" name="topup_amount" placeholder="Amount (BDT)" required min="1" class="w-full border p-3 rounded-xl outline-none text-base font-black text-emerald-600">
            <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3 rounded-xl shadow-md">Confirm Top-up</button>
        </form>
        <button onclick="document.getElementById('topupModal').classList.add('hidden')" class="w-full bg-gray-100 text-gray-600 font-bold py-2.5 rounded-xl">Cancel</button>
    </div>
</div>

<script>
    function openTopupModal(uid, name) {
        document.getElementById('topupResUid').value = uid;
        document.getElementById('topupResName').innerText = name;
        document.getElementById('topupModal').classList.remove('hidden');
    }

    document.getElementById('resellerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await fetch('admin_api.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                action: 'add_reseller',
                name: document.getElementById('rName').value,
                email: document.getElementById('rEmail').value
            })
        });
        const d = await res.json();
        if(d.status === 'success') {
            Swal.fire('Success', 'Reseller created successfully!', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', d.message, 'error');
        }
    });
</script>

<?php include 'footer.php'; ?>