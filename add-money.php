<?php include 'Security.php'; include 'header.php'; ob_start(); ?>

<div class="pt-20 max-w-[500px] mx-auto min-h-screen bg-white p-6 shadow-sm pb-24">
    <div class="flex items-center gap-4 border-b border-gray-100 pb-4 mb-6">
        <a href="profile.php" class="text-gray-500 hover:text-black"><i class="ri-arrow-left-line text-2xl"></i></a>
        <h1 class="text-xl font-bold text-gray-800">Add Money</h1>
    </div>

    <!-- Method Selection -->
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div onclick="selectGateway('bkash')" id="btn_bkash" class="border-2 border-gray-200 rounded-xl p-3 text-center cursor-pointer hover:border-pink-500">
            <span class="font-bold text-sm text-pink-600">bKash</span>
        </div>
        <div onclick="selectGateway('nagad')" id="btn_nagad" class="border-2 border-gray-200 rounded-xl p-3 text-center cursor-pointer hover:border-orange-500">
            <span class="font-bold text-sm text-orange-500">Nagad</span>
        </div>
        <div onclick="selectGateway('rocket')" id="btn_rocket" class="border-2 border-gray-200 rounded-xl p-3 text-center cursor-pointer hover:border-purple-500">
            <span class="font-bold text-sm text-purple-600">Rocket</span>
        </div>
    </div>

    <!-- Send Money Details -->
    <div id="detailsBox" class="hidden space-y-4">
        <div class="bg-slate-900 text-white p-5 rounded-2xl flex justify-between items-center">
            <div>
                <p class="text-xs text-slate-400 font-bold uppercase" id="gwLabel">Send Money To</p>
                <p class="text-xl font-bold font-mono select-all mt-1" id="adminPhone">...</p>
            </div>
            <button onclick="navigator.clipboard.writeText(document.getElementById('adminPhone').innerText); Swal.fire({toast:true, position:'top', icon:'success', title:'Copied!', timer:1500, showConfirmButton:false});" class="bg-white/10 px-3 py-2 rounded-xl text-xs font-bold">Copy</button>
        </div>

        <p class="text-xs text-slate-500 bg-slate-50 p-3 rounded-xl border" id="insText">...</p>

        <form id="depForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Amount (৳)</label>
                <input type="number" id="depAmount" required min="10" placeholder="e.g. 500" class="w-full border p-3 rounded-xl outline-none font-bold">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Sender Phone Number</label>
                <input type="tel" id="senderNum" required placeholder="01XXXXXXXXX" class="w-full border p-3 rounded-xl outline-none font-bold">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Transaction ID (TrxID)</label>
                <input type="text" id="trxID" required placeholder="TrxID" class="w-full border p-3 rounded-xl outline-none font-bold uppercase">
            </div>
            <button type="submit" id="depBtn" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg">SUBMIT DEPOSIT</button>
        </form>
    </div>
</div>

<script>
    let currentGateway = null;
    let settings = {};

    async function loadSettings() {
        const res = await fetch('ajax.php?action=get_settings&key=payment');
        settings = await res.json();
    }

    function selectGateway(gw) {
        currentGateway = gw;
        document.querySelectorAll('#btn_bkash, #btn_nagad, #btn_rocket').forEach(el => el.classList.remove('border-slate-900', 'bg-slate-50'));
        document.getElementById('btn_' + gw).classList.add('border-slate-900', 'bg-slate-50');

        document.getElementById('detailsBox').classList.remove('hidden');
        document.getElementById('gwLabel').innerText = `${gw.toUpperCase()} Personal Number`;
        document.getElementById('adminPhone').innerText = settings[gw] || '01700000000';
        document.getElementById('insText').innerText = settings[gw + '_ins'] || `Send Money to this ${gw} number and enter details below.`;
    }

    document.getElementById('depForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('depBtn');
        btn.innerText = 'Submitting...'; btn.disabled = true;

        const res = await fetch('api_server.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                action: 'add_money',
                method: currentGateway,
                amount: document.getElementById('depAmount').value,
                senderNumber: document.getElementById('senderNum').value,
                trxID: document.getElementById('trxID').value
            })
        });
        const d = await res.json();
        if(d.status === 'success') {
            Swal.fire('Success', d.message, 'success').then(() => window.location.href = 'history.php');
        } else {
            Swal.fire('Error', d.message, 'error');
            btn.innerText = 'SUBMIT DEPOSIT'; btn.disabled = false;
        }
    });

    loadSettings();
</script>

<?php $raw_code = ob_get_clean(); echo renderSecurePage($raw_code); include 'footer.php'; ?>