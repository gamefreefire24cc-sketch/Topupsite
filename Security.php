<?php
// ১. সেশন স্টার্ট করা
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ২. ব্রাউজার ক্যাশ ক্লিয়ারিং
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT"); 
header("X-Frame-Options: DENY"); 
header("X-XSS-Protection: 1; mode=block");

// ৩. টোকেন তৈরি
if (!isset($_SESSION['secure_render_token'])) {
    $_SESSION['secure_render_token'] = bin2hex(random_bytes(32)); 
}

function renderSecurePage($html_content) {
    $token = $_SESSION['secure_render_token'];
    
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN_DEVICE';
    $server_side_key_material = $token . $client_ip . $user_agent;
    
    $encryption_key = hash('sha256', $server_side_key_material); 
    $iv = substr(hash('sha256', 'OfferTopup_Server_Vault_2026'), 0, 16); 
    
    $encrypted_payload = openssl_encrypt($html_content, 'AES-256-CBC', $encryption_key, 0, $iv);
    
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Loading...</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
        <style>
            body { margin: 0; background-color: #f8fafc; font-family: sans-serif; }
            #secure-app-container { height: 100vh; width: 100vw; display: flex; flex-direction: column; align-items: center; justify-content: center; position: fixed; z-index: 9999999; background: #f8fafc; }
            .loader { border: 4px solid #e2e8f0; border-top: 4px solid #7c3aed; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin-bottom: 20px; }
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        </style>
    </head>
    <body>

    <div id="secure-app-container">
        <div class="loader"></div>
        <h2 style="color: #1e293b; margin:0;">Secure Connection</h2>
        <p style="color: #64748b; font-size: 14px;">Loading data...</p>
    </div>

    <script>
        (function() {
            const encryptedPayload = "{$encrypted_payload}";
            const baseToken = "{$token}";
            const clientIP = "{$client_ip}";
            const userAgent = "{$user_agent}";
            
            const keyMaterial = baseToken + clientIP + userAgent;

            function unlockData(ciphertext, keyString) {
                const key = CryptoJS.SHA256(keyString).toString(CryptoJS.enc.Hex);
                const iv = CryptoJS.enc.Utf8.parse(CryptoJS.SHA256('OfferTopup_Server_Vault_2026').toString(CryptoJS.enc.Hex).substring(0, 16));
                const keyParsed = CryptoJS.enc.Utf8.parse(key.substring(0, 32));

                try {
                    const decrypted = CryptoJS.AES.decrypt(ciphertext, keyParsed, {
                        iv: iv,
                        mode: CryptoJS.mode.CBC,
                        padding: CryptoJS.pad.Pkcs7
                    });
                    return decrypted.toString(CryptoJS.enc.Utf8);
                } catch(e) {
                    return null;
                }
            }

            function executeScripts(container) {
                const scripts = container.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    if (oldScript.src) {
                        newScript.src = oldScript.src;
                    } else {
                        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                    }
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
            }

            window.addEventListener('load', function() {
                setTimeout(() => {
                    const rawHTML = unlockData(encryptedPayload, keyMaterial);
                    
                    if(rawHTML) {
                        document.body.innerHTML = rawHTML; 
                        executeScripts(document.body);
                    } else {
                        document.body.innerHTML = "<h2 style='color:red; text-align:center; margin-top:20vh;'>Server validation failed. Please refresh.</h2>";
                    }
                }, 300); 
            });

        })();
    </script>
    </body>
    </html>
HTML;
}
?>