<?php 
include 'header.php'; 
$deposits = $pdo->query("SELECT * FROM deposit_requests ORDER BY id DESC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Wallet Deposits</h1>
        <p class="text-sm text-slate-500">Review and approve balance requests sent by users.</p>
    </div>
</div>

<div class="admin-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] uppercase tracking-wider text-slate-400 font-extrabold">
                <tr>
                    <th class="p-4 pl-6">Customer</th>
                    <th class="p-4">Amount</th>
                    <th class="p-4">Gateway & TrxID</th>
                    <th class="p-4">Sender Phone</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right pr-6">Verification</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                <?php if(empty($deposits)): ?>
                    <tr><td colspan="6" class="p-10 text-center text-slate-400 font-medium">কোন ডিপোজিট রিকোয়েস্ট নেই।</td></tr>
                <?php endif; ?>

                <?php foreach($deposits as $d): ?>
                <?php 
                    $m = strtoupper($d['method']);
                    $badgeBg = 'bg-slate-100 text-slate-700 border-slate-200';
                    if($m === 'BKASH') $badgeBg = 'bg-pink-50 text-pink-700 border-pink-200';
                    if($m === 'NAGAD') $badgeBg = 'bg-orange-50 text-orange-700 border-orange-200';
                    if($m === 'ROCKET') $badgeBg = 'bg-purple-50 text-purple-700 border-purple-200';
                ?>
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="p-4 pl-6 align-top">
                        <span class="font-extrabold text-slate-900 text-base block leading-tight"><?php echo htmlspecialchars($d['userName']); ?></span>
                        <span class="text-xs font-mono text-slate-400 block mt-1 select-all"><?php echo htmlspecialchars($d['uid']); ?></span>
                        <span class="text-[10px] text-slate-400 block mt-1"><?php echo $d['timestamp']; ?></span>
                    </td>

                    <td class="p-4 align-top">
                        <span class="text-lg font-black text-emerald-600 block">+৳<?php echo $d['amount']; ?></span>
                    </td>

                    <td class="p-4 align-top">
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md border inline-block mb-1 <?php echo $badgeBg; ?>">
                            <?php echo $d['method']; ?>
                        </span>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono font-bold text-xs text-slate-700 select-all"><?php echo htmlspecialchars($d['trxID']); ?></span>
                            <button onclick="navigator.clipboard.writeText('<?php echo $d['trxID']; ?>'); Swal.fire({toast:true, position:'top', icon:'success', title:'TrxID Copied!', timer:1200, showConfirmButton:false});" class="text-slate-400 hover:text-purple-600">
                                <i class="ri-file-copy-line"></i>
                            </button>
                        </div>
                    </td>

                    <td class="p-4 align-top font-mono font-bold text-xs select-all">
                        <?php echo htmlspecialchars($d['senderNumber']); ?>
                    </td>

                    <td class="p-4 align-top">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wide inline-block <?php echo $d['status'] === 'Approved' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : ($d['status'] === 'Rejected' ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-amber-50 text-amber-600 border border-amber-200'); ?>">
                            <?php echo $d['status']; ?>
                        </span>
                    </td>

                    <td class="p-4 pr-6 align-top text-right">
                        <?php if($d['status'] === 'Pending'): ?>
                            <div class="flex items-center justify-end gap-1.5">
                                <button onclick="verifyDeposit(<?php echo $d['id']; ?>, 'Approved', <?php echo $d['amount']; ?>)" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1">
                                    <i class="ri-check-line"></i> Approve
                                </button>
                                <button onclick="verifyDeposit(<?php echo $d['id']; ?>, 'Rejected', 0)" class="px-3 py-1.5 rounded-xl bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition">
                                    Reject
                                </button>
                            </div>
                        <?php else: ?>
                            <span class="text-xs text-slate-400 font-semibold italic">Verified</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    async function verifyDeposit(id, status, amount) {
        Swal.fire({
            title: status === 'Approved' ? `Approve ৳${amount}?` : 'Reject Request?',
            text: status === 'Approved' ? `৳${amount} will be instantly added to user wallet.` : "Deposit will be rejected.",
            icon: status === 'Approved' ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: status === 'Approved' ? '#059669' : '#e11d48',
            confirmButtonText: `Yes, ${status}`
        }).then(async (result) => {
            if(result.isConfirmed) {
                const res = await fetch('admin_api.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ action: 'update_deposit_status', id, status })
                });
                const d = await res.json();
                if(d.status === 'success') {
                    Swal.fire('Updated!', d.message, 'success').then(() => location.reload());
                }
            }
        });
    }
</script>

<?php include 'footer.php'; ?>