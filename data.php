<?php
// ১. স্লাইডার ব্যানার (সঠিক ও কার্যকর লিঙ্ক)
$banners = [
    [
        "id" => 1, 
        "image" => "https://i.postimg.cc/HxszRHqm/Generated-Image-February-21-2026-10-09PM.png", 
        "link" => "https://t.me/+EvHphCT_f5BiMjE1"
    ],
    [
        "id" => 2, 
        "image" => "https://i.ibb.co.com/mC5DVV3N/20260306-201020.jpg", 
        "link" => "https://t.me/+EvHphCT_f5BiMjE1"
    ],
    [
        "id" => 3, 
        "image" => "https://i.ibb.co.com/Mk3R5qfD/Generated-Image-March-06-2026-8-15-PM.png", 
        "link" => "https://t.me/+EvHphCT_f5BiMjE1"
    ]
];

// ২. প্রোডাক্ট ক্যাটাগরি এবং প্যাকেজ কোড
$categories = [
    "SPECIAL OFFER" => [
        ["name" => "WEEKLY OFFER", "image" => "https://admin.offertopup.com/products/1753834804.jpg", "link" => "topup.php?id=138", "code" => "offer_weekly"],
        ["name" => "MONTHLY OFFER", "image" => "https://admin.offertopup.com/products/1753834812.jpg", "link" => "topup.php?id=139", "code" => "offer_monthly"]
    ],
    "FREE FIRE" => [
        ["name" => "Free Fire TopUp (BD)", "image" => "https://admin.offertopup.com/products/1753834576.jpg", "link" => "topup.php?id=1", "code" => "ff_diamond"],
        ["name" => "Weekly/Monthly Membership", "image" => "https://admin.offertopup.com/products/1753834607.jpg", "link" => "topup.php?id=77", "code" => "ff_membership"],
        ["name" => "Free Fire Airdrop", "image" => "https://admin.offertopup.com/products/1753834633.jpg", "link" => "topup.php?id=promo", "code" => "ff_airdrop"],
        ["name" => "Weekly Lite (BD Server)", "image" => "https://admin.offertopup.com/products/1753834679.jpg", "link" => "topup.php?id=88", "code" => "ff_lite"],
        ["name" => "E-Badge/Evo Access", "image" => "https://admin.offertopup.com/products/1753834688.jpg", "link" => "topup.php?id=91", "code" => "ff_badge"],
        ["name" => "LEVEL UP PASS", "image" => "https://admin.offertopup.com/products/1753834828.jpg", "link" => "topup.php?id=116", "code" => "ff_levelup"],
        ["name" => "Indonesia Server (UID)", "image" => "https://admin.offertopup.com/products/1753834935.jpg", "link" => "topup.php?id=78", "code" => "ff_indonesia"]
    ],
    "INGAME TOPUP" => [
        ["name" => "E-Football", "image" => "https://admin.offertopup.com/products/1765198798.jpg", "link" => "topup.php?id=200", "code" => "efootball"],
        ["name" => "Clash Of clans", "image" => "https://admin.offertopup.com/products/1765198858.jpg", "link" => "topup.php?id=201", "code" => "coc"],
        ["name" => "Clash Royale", "image" => "https://admin.offertopup.com/products/1765198951.jpg", "link" => "topup.php?id=202", "code" => "clash_royale"]
    ],
    "SUBSCRIPTION" => [
        ["name" => "YOUTUBE PREMIUM", "image" => "https://admin.offertopup.com/products/1753835965.jpg", "link" => "topup.php?id=122", "code" => "sub_youtube"],
        ["name" => "CHATGPT PLUS", "image" => "https://admin.offertopup.com/products/1753835981.jpg", "link" => "topup.php?id=124", "code" => "sub_chatgpt"],
        ["name" => "SPOTIFY PREMIUM", "image" => "https://admin.offertopup.com/products/1753835988.jpg", "link" => "topup.php?id=126", "code" => "sub_spotify"],
        ["name" => "NETFLIX", "image" => "https://upload.wikimedia.org/wikipedia/commons/7/75/Netflix_icon.svg", "link" => "topup.php?id=130", "code" => "sub_netflix"]
    ]
];

// ৩. লেটেস্ট অর্ডার হিস্ট্রি (ডেমো)
$orders = [
    ["name" => "Najmul Hasan", "avatar" => null, "product" => "1X WEEKLY", "price" => "৳148", "status" => "Running"],
    ["name" => "Noyun", "avatar" => null, "product" => "WEEKLY", "price" => "৳154", "status" => "Completed"],
    ["name" => "Xbd Fahim", "avatar" => null, "product" => "1X Weekly Lite", "price" => "৳42", "status" => "Completed"],
    ["name" => "Dk Apon", "avatar" => null, "product" => "850 Diamond", "price" => "৳555", "status" => "Completed"]
];