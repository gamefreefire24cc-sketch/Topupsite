<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <div class="pb-20 md:pb-0">
        <footer class="bg-gradient-to-br from-indigo-900 via-purple-900 to-indigo-800 text-gray-200 border-t-2 border-purple-700">
            
            <section class="container mx-auto pb-8 pt-8">
                <div class="flex flex-wrap">
                    
                    <div class="w-full md:w-4/6 mx-auto flex flex-wrap">
                        
                        <!-- Social Links -->
                        <div class="w-full md:w-1/3 px-5 md:px-0 text-center md:text-left">
                            <div class="text-lg uppercase text-white font-semibold tracking-wider">STAY CONNECTED</div>
                            <div class="mt-4 flex gap-3 justify-center md:justify-start">
                                <a id="ft_fb" href="#" target="_blank" class="hidden w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-colors cursor-pointer">
                                    <i class="ri-facebook-fill text-xl text-white"></i>
                                </a>
                                <a id="ft_ig" href="#" target="_blank" class="hidden w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-colors cursor-pointer">
                                    <i class="ri-instagram-line text-xl text-white"></i>
                                </a>
                                <a id="ft_yt" href="#" target="_blank" class="hidden w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-colors cursor-pointer">
                                    <i class="ri-youtube-fill text-xl text-white"></i>
                                </a>
                                <a id="ft_email" href="#" class="hidden w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-colors cursor-pointer">
                                    <i class="ri-mail-fill text-xl text-white"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Mobile App -->
                        <div class="w-full md:w-2/3 md:text-center md:pl-20 hidden md:block">
                            <div class="p-5 w-full">
                                <div class="text-lg uppercase text-white font-semibold tracking-wider">Our Mobile App</div>
                                <div class="mt-4 flex justify-center">
                                    <a id="ft_app" href="#" target="_blank" class="inline-block cursor-pointer hover:opacity-80 transition-opacity">
                                        <img src="https://offertopup.com/_nuxt/google-play.nDtcExnl.png" alt="Get it on Google Play" class="h-12">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Support Center -->
                    <div class="w-full md:w-2/6 px-5 md:px-0 mt-6 md:mt-0">
                        <div class="md:ml-20 text-center md:text-left">
                            <div class="text-lg uppercase text-white font-semibold tracking-wider pb-3">Support Center</div>
                            <a id="ft_tg" href="#" target="_blank" class="rounded-md p-3 mt-2 md:mt-4 flex items-center justify-center md:justify-start bg-white/10 hover:bg-white/20 transition-colors cursor-pointer border border-white/5">
                                <div class="w-10 h-10 flex items-center justify-center bg-purple-600 rounded-full shrink-0">
                                    <i class="ri-telegram-fill text-xl text-white"></i>
                                </div>
                                <div class="ml-4 text-left">
                                    <p class="text-purple-300 text-xs font-normal">Contact Admin</p>
                                    <span class="text-white font-medium">টেলিগ্রামে সাপোর্ট</span>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>
            </section>

            <!-- Copyright Section -->
            <div class="pb-5 px-5 mx-auto pt-5 text-white text-sm flex justify-center border-t border-white/10">
                <div class="mt-2 text-center tracking-wide leading-relaxed">
                    © <span id="ft_year">2026</span> | All Rights Reserved | Developed by 
                    <a id="ft_dev_link" href="#" target="_blank" class="text-green-300 hover:text-green-200 font-medium transition-colors"><span id="ft_dev_name">Developer</span></a> | Web Developer
                </div>
            </div>
        </footer>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-50 border-t border-gray-200">
        <div class="flex justify-around py-2">
            <a href="index.php" class="flex flex-col items-center justify-center w-full py-1 text-purple-600 cursor-pointer hover:bg-gray-50">
                <i class="ri-home-5-fill text-xl"></i>
                <span class="text-[10px] font-medium mt-1">Home</span>
            </a>
            <a href="add-money.php" class="flex flex-col items-center justify-center w-full py-1 text-gray-600 hover:text-purple-600 cursor-pointer hover:bg-gray-50 transition-colors">
                <i class="ri-wallet-3-fill text-xl"></i>
                <span class="text-[10px] font-medium mt-1">Add Money</span>
            </a>
            <a href="history.php" class="flex flex-col items-center justify-center w-full py-1 text-gray-600 hover:text-purple-600 cursor-pointer hover:bg-gray-50 transition-colors">
                <i class="ri-history-line text-xl"></i>
                <span class="text-[10px] font-medium mt-1">History</span>
            </a>
            <a href="contact.php" class="flex flex-col items-center justify-center w-full py-1 text-gray-600 hover:text-purple-600 cursor-pointer hover:bg-gray-50 transition-colors">
                <i class="ri-customer-service-2-fill text-xl"></i>
                <span class="text-[10px] font-medium mt-1">Contact</span>
            </a>
        </div>
    </div>

    <!-- Floating Help Button -->
    <a href="contact.php" class="fixed bottom-20 md:bottom-6 right-4 z-40 bg-red-600 hover:bg-red-700 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg transition-all cursor-pointer group animate-bounce-slow">
        <i class="ri-customer-service-2-fill text-2xl"></i>
        <span class="absolute right-16 bg-gray-800 text-white px-3 py-1 rounded text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300">সাহায্য লাগবে?</span>
    </a>

    <!-- Footer Dynamic Logic -->
    <script type="module">
        // Import database directly (works safely since header.php initializes firebase first)
        import { db } from "./firebase-config.js";
        import { ref, onValue } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-database.js";

        // Set dynamic year
        document.getElementById('ft_year').innerText = new Date().getFullYear();

        // Fetch Data from Admin Panel
        onValue(ref(db, 'admin_settings/contact'), (snapshot) => {
            if (snapshot.exists()) {
                const data = snapshot.val();
                
                // Social Links (Show only if admin provided a link)
                const fb = document.getElementById('ft_fb');
                if(data.fb) { fb.href = data.fb; fb.classList.remove('hidden'); } else fb.classList.add('hidden');
                
                const ig = document.getElementById('ft_ig');
                if(data.ig) { ig.href = data.ig; ig.classList.remove('hidden'); } else ig.classList.add('hidden');
                
                const yt = document.getElementById('ft_yt');
                if(data.yt) { yt.href = data.yt; yt.classList.remove('hidden'); } else yt.classList.add('hidden');
                
                const email = document.getElementById('ft_email');
                if(data.email) { email.href = 'mailto:' + data.email; email.classList.remove('hidden'); } else email.classList.add('hidden');

                // App & TG Support
                document.getElementById('ft_app').href = data.app || '#';
                document.getElementById('ft_tg').href = data.tg || '#';

                // Developer Credits
                document.getElementById('ft_dev_name').innerText = data.devName || 'Developer';
                document.getElementById('ft_dev_link').href = data.devLink || '#';
            }
        });
    </script>
</body>
</html>