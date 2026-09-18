<?php 
require_once 'db.php';
if(!isset($_SESSION['user_uid'])) { header("Location: login.php"); exit(); }
include 'header.php'; 
?>

<div class="pt-20 max-w-[600px] mx-auto min-h-screen bg-white p-6 shadow-sm pb-24">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Transaction History</h1>

    <div class="flex gap-2 mb-6 border-b pb-3">
        <button onclick="switchTab('orders')" id="tOrders" class="px-4 py-2 font-bold text-sm bg-purple-600 text-white rounded-xl">Orders</button>
        <button onclick="switchTab('deposits')" id="tDeposits" class="px-4 py-2 font-bold text-sm bg-gray-100 text-gray-600 rounded-xl">Deposits</button>
    </div>

    <!-- Orders Section -->
    <div id="secOrders" class="space-y-3">
        <p class="text-sm text-gray-400 text-center py-6">লোডিং...</p>
    </div>

    <!-- Deposits Section -->
    <div id="secDeposits" class="space-y-3 hidden">
        <p class="text-sm text-gray-400 text-center py-6">লোডিং...</p>
    </div>
</div>

<script>
    async function loadHistory() {
        const res = await fetch('ajax.php?action=get_user_history');
        const d = await res.json();

        if(d.status === 'success') {
            // Orders
            const oBox = document.getElementById('secOrders');
            oBox.innerHTML = '';
            if(d.orders.length > 0) {
                d.orders.forEach(o => {
                    oBox.innerHTML += `
                        <div class="p-4 rounded-xl border flex justify-between items-center">
                            <div>
                                <p class="font-bold text-gray-800">${o.product}</p>
                                <p class="text-xs text-purple-600 font-semibold">${o.package} • ${o.paymentMethod}</p>
                                <p class="text-[10px] text-gray-400 mt-1">${o.timestamp}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-base font-black">৳${o.price}</span>
                                <span class="block text-[10px] font-bold uppercase mt-1 ${o.status === 'Completed' ? 'text-green-600' : 'text-orange-500'}">${o.status}</span>
                            </div>
                        </div>
                    `;
                });
            } else {
                oBox.innerHTML = '<p class="text-center text-gray-400 text-sm py-6">কোন অর্ডার পাওয়া যায়নি।</p>';
            }

            // Deposits
            const dpBox = document.getElementById('secDeposits');
            dpBox.innerHTML = '';
            if(d.deposits.length > 0) {
                d.deposits.forEach(dp => {
                    dpBox.innerHTML += `
                        <div class="p-4 rounded-xl border flex justify-between items-center">
                            <div>
                                <p class="font-bold text-gray-800">Add Money (${dp.method})</p>
                                <p class="text-xs text-gray-500 font-mono">Trx: ${dp.trxID}</p>
                                <p class="text-[10px] text-gray-400 mt-1">${dp.timestamp}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-base font-black text-green-600">+৳${dp.amount}</span>
                                <span class="block text-[10px] font-bold uppercase mt-1 ${dp.status === 'Approved' ? 'text-green-600' : 'text-orange-500'}">${dp.status}</span>
                            </div>
                        </div>
                    `;
                });
            } else {
                dpBox.innerHTML = '<p class="text-center text-gray-400 text-sm py-6">কোন ডিপোজিট রিকোয়েস্ট পাওয়া যায়নি।</p>';
            }
        }
    }

    function switchTab(t) {
        if(t === 'orders') {
            document.getElementById('secOrders').classList.remove('hidden');
            document.getElementById('secDeposits').classList.add('hidden');
            document.getElementById('tOrders').className = "px-4 py-2 font-bold text-sm bg-purple-600 text-white rounded-xl";
            document.getElementById('tDeposits').className = "px-4 py-2 font-bold text-sm bg-gray-100 text-gray-600 rounded-xl";
        } else {
            document.getElementById('secOrders').classList.add('hidden');
            document.getElementById('secDeposits').classList.remove('hidden');
            document.getElementById('tOrders').className = "px-4 py-2 font-bold text-sm bg-gray-100 text-gray-600 rounded-xl";
            document.getElementById('tDeposits').className = "px-4 py-2 font-bold text-sm bg-purple-600 text-white rounded-xl";
        }
    }

    loadHistory();
</script>

<?php include 'footer.php'; ?>