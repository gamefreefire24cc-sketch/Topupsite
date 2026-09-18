<?php include 'Security.php'; include 'header.php'; ob_start(); ?>

<div class="pt-20 max-w-[600px] mx-auto min-h-screen bg-white p-6 shadow-sm pb-36">
    <div class="flex items-center gap-4 border-b border-gray-100 pb-4 mb-6">
        <a href="index.php" class="text-gray-500 hover:text-black"><i class="ri-arrow-left-line text-2xl"></i></a>
        <h1 class="text-xl font-bold text-gray-800">Checkout</h1>
    </div>

    <!-- Order Info -->
    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 mb-6">
        <h2 id="prodName" class="text-lg font-black text-gray-900">...</h2>
        <p id="pkgName" class="text-purple-600 font-bold text-sm mt-1">...</p>
        <p id="accInfo" class="text-xs text-gray-500 mt-2 font-mono bg-white p-2.5 rounded-lg border">...</p>
    </div>

    <!-- 🎟️ Promo Code Box -->
    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 mb-6 space-y-3">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Promo Code / Coupon</h3>
        <div class="flex gap-2">
            <input type="text" id="couponCode" placeholder="ENTER COUPON" class="w-full border p-3 rounded-xl outline-none uppercase font-bold text-sm bg-white">
            <button onclick="applyCoupon()" id="couponBtn" class="bg-purple-600 text-white px-5 py-3 rounded-xl font-bold text-xs shadow-sm hover:bg-purple-700 transition">Apply</button>
        </div>
        <div id="couponMsg" class="text-xs font-semibold hidden"></div>
    </div>

    <!-- Method -->
    <div class="space-y-3 mb-6">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Payment Method</h3>
        <div onclick="selectMethod('wallet')" id="opt_wallet" class="p-4 rounded-xl border-2 border-purple-600 bg-purple-50 flex justify-between items-center cursor-pointer">
            <div>
                <p class="font-bold text-gray-800 text-sm">Wallet Balance</p>
                <p class="text-xs text-gray-500">Available: ৳<span id="userBal">0</span></p>
            </div>
            <i class="ri-wallet-3-fill text-2xl text-purple-600"></i>
        </div>
        <div onclick="selectMethod('direct')" id="opt_direct" class="p-4 rounded-xl border-2 border-gray-100 flex justify-between items-center cursor-pointer">
            <div>
                <p class="font-bold text-gray-800 text-sm">Direct Payment</p>
                <p class="text-xs text-gray-500">bKash / Nagad / Rocket</p>
            </div>
            <i class="ri-secure-payment-fill text-2xl text-gray-400"></i>
        </div>
    </div>

    <div class="flex justify-between items-center p-4 bg-slate-900 text-white rounded-xl">
        <span class="font-bold text-sm">Total Payable</span>
        <span class="text-2xl font-black text-yellow-400">৳<span id="finalTotal">0</span></span>
    </div>
</div>

<div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 z-50 flex justify-center">
    <div class="max-w-[600px] w-full">
        <button id="payBtn" onclick="handlePay()" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg">CONFIRM PAYMENT</button>
    </div>
</div>

<script>
    let orderData = JSON.parse(localStorage.getItem('checkoutData'));
    let paymentMethod = 'wallet';
    let userBalance = 0;
    let appliedDiscount = 0;
    let finalPayablePrice = 0;
    let verifiedCouponCode = "";

    if(!orderData) window.location.href = 'index.php';

    originalPrice = parseInt(orderData.price);
    finalPayablePrice = originalPrice;

    document.getElementById('prodName').innerText = orderData.productName;
    document.getElementById('pkgName').innerText = orderData.pkgName;
    document.getElementById('finalTotal').innerText = finalPayablePrice;
    document.getElementById('accInfo').innerText = orderData.userData.type === 'UID' ? `UID: ${orderData.userData.uid}` : `Login: ${orderData.userData.email}`;

    async function getUserData() {
        const res = await fetch('ajax.php?action=get_profile');
        const d = await res.json();
        if(d.status === 'success') {
            userBalance = parseInt(d.data.balance || 0);
            document.getElementById('userBal').innerText = userBalance;
        } else {
            window.location.href = 'login.php';
        }
    }

    async function applyCoupon() {
        const code = document.getElementById('couponCode').value.trim();
        const msgBox = document.getElementById('couponMsg');
        
        if(!code) return Swal.fire('সতর্কতা', 'আগে কুপন কোড লিখুন!', 'warning');

        const res = await fetch('ajax.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                action: 'apply_coupon',
                code: code,
                subtotal: originalPrice
            })
        });
        const d = await res.json();

        if(d.status === 'success') {
            appliedDiscount = d.discount;
            finalPayablePrice = d.finalTotal;
            verifiedCouponCode = d.code;
            
            document.getElementById('finalTotal').innerText = finalPayablePrice;
            msgBox.classList.remove('hidden', 'text-rose-500');
            msgBox.classList.add('text-emerald-600');
            msgBox.innerText = `${d.message} আপনি ৳${d.discount} ছাড় পেয়েছেন!`;
            Swal.fire({toast:true, position:'top', icon:'success', title:d.message, timer:1500, showConfirmButton:false});
        } else {
            msgBox.classList.remove('hidden', 'text-emerald-600');
            msgBox.classList.add('text-rose-500');
            msgBox.innerText = d.message;
        }
    }

    function selectMethod(m) {
        paymentMethod = m;
        document.getElementById('opt_wallet').className = m === 'wallet' ? "p-4 rounded-xl border-2 border-purple-600 bg-purple-50 flex justify-between items-center cursor-pointer" : "p-4 rounded-xl border-2 border-gray-100 flex justify-between items-center cursor-pointer";
        document.getElementById('opt_direct').className = m === 'direct' ? "p-4 rounded-xl border-2 border-purple-600 bg-purple-50 flex justify-between items-center cursor-pointer" : "p-4 rounded-xl border-2 border-gray-100 flex justify-between items-center cursor-pointer";
    }

    async function handlePay() {
        const btn = document.getElementById('payBtn');

        if(paymentMethod === 'wallet') {
            if(userBalance < finalPayablePrice) {
                return Swal.fire('ব্যালেন্স কম!', 'আপনার ওয়ালেটে পর্যাপ্ত ব্যালেন্স নেই। ডিরেক্ট পেমেন্ট বেছে নিন।', 'warning');
            }

            btn.innerText = 'Processing...'; btn.disabled = true;

            const res = await fetch('api_server.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    action: 'process_wallet_order',
                    price: finalPayablePrice,
                    productName: orderData.productName,
                    pkgName: orderData.pkgName,
                    orderDetails: orderData.userData,
                    couponCode: verifiedCouponCode
                })
            });
            const d = await res.json();
            if(d.status === 'success') {
                localStorage.removeItem('checkoutData');
                Swal.fire('Success', d.message, 'success').then(() => window.location.href = 'history.php');
            } else {
                Swal.fire('Error', d.message, 'error');
                btn.innerText = 'CONFIRM PAYMENT'; btn.disabled = false;
            }
        } else {
            // Direct Payment টোকেন ক্রিয়েট
            const tokenRes = await fetch('create_token.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    productName: orderData.productName,
                    packageName: orderData.pkgName,
                    payable: finalPayablePrice,
                    totalPrice: finalPayablePrice,
                    details: orderData.userData,
                    couponCode: verifiedCouponCode
                })
            });
            const tData = await tokenRes.json();
            if(tData.status === 'success') {
                window.location.href = `payment.php?type=purchase&token=${tData.token}`;
            }
        }
    }

    getUserData();
</script>

<?php $raw_code = ob_get_clean(); echo renderSecurePage($raw_code); include 'footer.php'; ?>