<?php 
include 'header.php'; 
// পেমেন্ট সেটিংস
$stmt = $pdo->prepare("SELECT setting_value FROM admin_settings WHERE setting_key = 'payment'");
$stmt->execute();
$s = json_decode($stmt->fetchColumn() ?: '{}', true);

// নোটিশ টেক্সট
$stmtN = $pdo->prepare("SELECT setting_value FROM admin_settings WHERE setting_key = 'notice'");
$stmtN->execute();
$noticeVal = $stmtN->fetchColumn() ?: "১৮ বছরের নিচে কেউ অর্ডার করবেন না! বাবা/মা বা পরিবারের টাকা চুরি করে অর্ডার করলে তার বিরুদ্ধে আইনগত ব্যবস্থা নেওয়া হবে!";
?>

<div class="max-w-3xl space-y-6">
    <!-- Notice Editor -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h2 class="text-lg font-bold text-slate-800 mb-1 flex items-center gap-2">
            <i class="ri-notification-3-line text-purple-600"></i> Home Page Marquee Notice
        </h2>
        <p class="text-xs text-slate-400 mb-4">হোমপেজের উপরে চলমান নোটিশের লেখাটি এখান থেকে পরিবর্তন করুন।</p>
        <form id="noticeForm" class="space-y-3">
            <textarea id="noticeInput" rows="3" required class="w-full border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-sm font-medium"><?php echo htmlspecialchars($noticeVal); ?></textarea>
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md transition text-sm">Update Notice</button>
        </form>
    </div>

    <!-- Payment Gateways Settings -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="ri-wallet-3-line text-purple-600"></i> Payment Gateways & Numbers
        </h2>
        <form id="setForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">bKash Personal Number</label>
                <input type="text" id="bkash" value="<?php echo $s['bkash'] ?? ''; ?>" class="w-full border p-3 rounded-xl outline-none font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nagad Personal Number</label>
                <input type="text" id="nagad" value="<?php echo $s['nagad'] ?? ''; ?>" class="w-full border p-3 rounded-xl outline-none font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Rocket Personal Number</label>
                <input type="text" id="rocket" value="<?php echo $s['rocket'] ?? ''; ?>" class="w-full border p-3 rounded-xl outline-none font-mono">
            </div>
            <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-bold py-3.5 rounded-xl shadow-lg transition">Save Gateways</button>
        </form>
    </div>
</div>

<script>
    // নোটিশ আপডেট
    document.getElementById('noticeForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const val = document.getElementById('noticeInput').value.trim();
        const res = await fetch('admin_api.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action: 'save_notice', notice: val })
        });
        const d = await res.json();
        if(d.status === 'success') Swal.fire('Success', 'Notice updated!', 'success');
    });

    // পেমেন্ট নাম্বার সেভ
    document.getElementById('setForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const settings = {
            bkash: document.getElementById('bkash').value,
            nagad: document.getElementById('nagad').value,
            rocket: document.getElementById('rocket').value
        };
        const res = await fetch('admin_api.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action: 'save_settings', key: 'payment', settings })
        });
        const d = await res.json();
        if(d.status === 'success') Swal.fire('Saved!', d.message, 'success');
    });
</script>

<?php include 'footer.php'; ?>