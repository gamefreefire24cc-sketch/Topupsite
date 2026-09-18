<?php 
require_once 'db.php';
include 'jwt_helper.php';

$type = $_GET['type'] ?? '';
$token = $_GET['token'] ?? '';
$secureData = null;

if (!empty($token)) {
    $secureData = JWT::decode($token);
    if (!$secureData || (isset($secureData['exp']) && time() > $secureData['exp'])) die("Invalid/Expired Token!");
}
include 'Security.php';
ob_start(); 
include 'header.php'; 
?>

<div class="pt-20 max-w-[500px] mx-auto min-h-screen bg-white p-6 shadow-sm pb-24">
    <div class="flex items-center gap-4 border-b border-gray-100 pb-4 mb-6">
        <button onclick="history.back()" class="text-gray-500 hover:text-black"><i class="ri-arrow-left-line text-2xl"></i></button>
        <h1 class="text-xl font-bold text-gray-800">Complete Payment</h1>
    </div>

    <!-- Amount Card -->
    <div class="bg-slate-900 text-white p-6 rounded-2xl text-center mb-6">
        <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">Total Payable</p>
        <h2 class="text-4xl font-black text-yellow-400 mt-1">৳ <span id="payableAmount"><?php echo $secureData['payable'] ?? 0; ?></span></h2>
    </div>

    <!-- Select Method -->
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div onclick="selectMethod('bkash')" id="m_bkash" class="border-2 border-gray-200 rounded-xl p-3 text-center cursor-pointer font-bold text-pink-600">bKash</div>
        <div onclick="selectMethod('nagad')" id="m_nagad" class="border-2 border-gray-200 rounded-xl p-3 text-center cursor-pointer font-bold text-orange-500">Nagad</div>
        <div onclick="selectMethod('rocket')" id="m_rocket" class="border-2 border-gray-200 rounded-xl p-3 text-center cursor-pointer font-bold text-purple-600">Rocket</div>
    </div>

    <div id="formSection" class="hidden space-y-4">
        <div class="bg-gray-50 p-4 rounded-xl border flex justify-between items-center">
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase">Send Money To</p>
                <p class="text-lg font-bold font-mono select-all text-slate-800" id="adminNum">...</p>
            </div>
            <button onclick="navigator.clipboard.writeText(document.getElementById('adminNum').innerText); Swal.fire({toast:true, position:'top', icon:'success', title:'Copied!', timer:1500, showConfirmButton:false});" class="bg-white border px-3 py-1.5 rounded-lg text-xs font-bold">Copy</button>
        </div>

        <form id="payForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Sender Number</label>
                <input type="tel" id="senderPhone" required placeholder="01XXXXXXXXX" class="w-full border p-3 rounded-xl outline-none font-bold">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Transaction ID (TrxID)</label>
                <input type="text" id="trxID" required placeholder="TrxID" class="w-full border p-3 rounded-xl outline-none font-bold uppercase">
            </div>
            <button type="submit" id="btnConfirm" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg">CONFIRM PAYMENT</button>
        </form>
    </div>
</div>

<script>
    let settings = {};
    let selectedMethod = null;
    const secureData = <?php echo json_encode($secureData); ?>;

    async function loadSettings() {
        const res = await fetch('ajax.php?action=get_settings&key=payment');
        settings = await res.json();
    }

    function selectMethod(m) {
        selectedMethod = m;
        document.querySelectorAll('#m_bkash, #m_nagad, #m_rocket').forEach(b => b.classList.remove('border-slate-900', 'bg-slate-50'));
        document.getElementById('m_' + m).classList.add('border-slate-900', 'bg-slate-50');

        document.getElementById('formSection').classList.remove('hidden');
        document.getElementById('adminNum').innerText = settings[m] || '01700000000';
    }

    document.getElementById('payForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnConfirm');
        btn.innerText = 'Processing...'; btn.disabled = true;

        const payload = {
            action: 'process_direct_order',
            productName: secureData.productName,
            packageName: secureData.packageName,
            payable: secureData.payable,
            orderDetails: secureData.details,
            method: selectedMethod,
            senderNumber: document.getElementById('senderPhone').value,
            trxID: document.getElementById('trxID').value
        };

        const res = await fetch('api_server.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        const d = await res.json();

        if(d.status === 'success') {
            Swal.fire('Success', d.message, 'success').then(() => window.location.href = 'history.php');
        } else {
            Swal.fire('Error', d.message, 'error');
            btn.innerText = 'CONFIRM PAYMENT'; btn.disabled = false;
        }
    });

    loadSettings();
</script>

<?php 
include 'footer.php'; 
$raw_code = ob_get_clean(); 
echo renderSecurePage($raw_code); 
?>