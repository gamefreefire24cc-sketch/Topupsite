<?php 
include 'header.php'; 

// ডাটাবেস থেকে সব ইউজারের তালিকা লোড করা
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900">User Management</h1>
        <p class="text-sm text-slate-500">গ্রাহকদের তালিকা, ওয়ালেট ব্যালেন্স চেক এবং ম্যানুয়াল ব্যালেন্স অ্যাড/কাট করুন।</p>
    </div>
    <div class="text-sm font-bold text-slate-600 bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm">
        Total Users: <span class="text-purple-600 font-black"><?php echo count($users); ?></span>
    </div>
</div>

<!-- ইউজার টেবিল -->
<div class="admin-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] uppercase tracking-wider text-slate-400 font-extrabold">
                <tr>
                    <th class="p-4 pl-6">Customer Info</th>
                    <th class="p-4">UID</th>
                    <th class="p-4">Phone</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Wallet Balance</th>
                    <th class="p-4 text-right pr-6">Action / Balance Edit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                <?php if(empty($users)): ?>
                    <tr><td colspan="6" class="p-10 text-center text-slate-400">কোনো ইউজার পাওয়া যায়নি।</td></tr>
                <?php endif; ?>

                <?php foreach($users as $u): ?>
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="p-4 pl-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-600 to-indigo-600 text-white font-bold flex items-center justify-center shrink-0 shadow-sm">
                                <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 block"><?php echo htmlspecialchars($u['username']); ?></span>
                                <span class="text-xs text-slate-400 font-mono"><?php echo htmlspecialchars($u['email']); ?></span>
                            </div>
                        </div>
                    </td>

                    <td class="p-4 font-mono text-xs font-bold text-slate-600 select-all">
                        <?php echo htmlspecialchars($u['uid']); ?>
                    </td>

                    <td class="p-4 font-mono text-xs font-bold text-slate-600 select-all">
                        <?php echo htmlspecialchars($u['phone'] ?: 'N/A'); ?>
                    </td>

                    <td class="p-4">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wide <?php echo $u['role'] === 'admin' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-600'; ?>">
                            <?php echo $u['role']; ?>
                        </span>
                    </td>

                    <td class="p-4">
                        <span class="text-base font-black text-emerald-600">৳<?php echo number_format($u['balance']); ?></span>
                    </td>

                    <td class="p-4 pr-6 text-right">
                        <button onclick="openBalanceModal('<?php echo $u['uid']; ?>', '<?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>', <?php echo $u['balance']; ?>)" class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-600 text-purple-700 hover:text-white text-xs font-bold transition border border-purple-200 shadow-sm flex items-center gap-1.5 ml-auto">
                            <i class="ri-wallet-3-line"></i> Edit Balance
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Balance Edit Modal -->
<div id="balanceModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4 border border-slate-100 animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="font-black text-slate-900 text-lg flex items-center gap-2">
                <i class="ri-wallet-3-fill text-purple-600"></i> Adjust Wallet Balance
            </h3>
            <button onclick="closeBalanceModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>

        <div>
            <p class="text-xs text-slate-400 font-bold uppercase">Customer</p>
            <p id="modalUserName" class="text-base font-extrabold text-slate-800">...</p>
            <p class="text-xs text-slate-500 mt-0.5">Current Balance: <span id="modalCurrentBal" class="font-bold text-emerald-600">৳0</span></p>
        </div>

        <input type="hidden" id="modalUid">

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Action Type</label>
                <select id="adjType" class="w-full bg-slate-50 border-2 border-slate-200 p-3 rounded-xl outline-none text-sm font-bold text-slate-800">
                    <option value="add">➕ Add Balance (যোগ করুন)</option>
                    <option value="subtract">➖ Subtract Balance (বিয়োগ করুন)</option>
                    <option value="set">📌 Set Exact Balance (নির্দিষ্ট পরিমাণ সেট করুন)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Amount (৳)</label>
                <input type="number" id="adjAmount" placeholder="e.g. 500" min="0" required class="w-full bg-slate-50 border-2 border-slate-200 p-3 rounded-xl outline-none text-base font-black text-slate-800">
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button onclick="closeBalanceModal()" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-600 py-3.5 rounded-xl font-bold transition text-sm">Cancel</button>
            <button onclick="submitBalanceAdjustment()" class="w-1/2 bg-purple-600 hover:bg-purple-700 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-purple-600/25 transition text-sm">Save Changes</button>
        </div>
    </div>
</div>

<script>
    function openBalanceModal(uid, name, balance) {
        document.getElementById('modalUid').value = uid;
        document.getElementById('modalUserName').innerText = name;
        document.getElementById('modalCurrentBal').innerText = '৳' + balance;
        document.getElementById('adjAmount').value = '';
        document.getElementById('balanceModal').classList.remove('hidden');
    }

    function closeBalanceModal() {
        document.getElementById('balanceModal').classList.add('hidden');
    }

    async function submitBalanceAdjustment() {
        const uid = document.getElementById('modalUid').value;
        const type = document.getElementById('adjType').value;
        const amount = document.getElementById('adjAmount').value;

        if(!amount || amount <= 0) {
            return Swal.fire('সতর্কতা', 'সঠিক পরিমাণ অ্যামাউন্ট লিখুন!', 'warning');
        }

        const res = await fetch('admin_api.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                action: 'adjust_user_balance',
                uid: uid,
                type: type,
                amount: amount
            })
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
            Swal.fire('Error', d.message, 'error');
        }
    }
</script>

<?php include 'footer.php'; ?>