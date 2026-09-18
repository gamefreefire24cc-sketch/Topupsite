// admin/firebase-config.js
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import { getAuth, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
import { getDatabase, ref, get, set, push, update, remove, onValue } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-database.js";

// আপনার Firebase Config এখানে বসান
const firebaseConfig = {
  apiKey: "AIzaSyBWmIsKHlDCTwnwXy5P1q9xa2CQM2fHz2k",
  authDomain: "aide-free.firebaseapp.com",
  databaseURL: "https://aide-free-default-rtdb.firebaseio.com",
  projectId: "aide-free",
  storageBucket: "aide-free.firebasestorage.app",
  messagingSenderId: "350957018360",
  appId: "1:350957018360:web:d93824c086f0f9c9352f0d",
  measurementId: "G-YEB0LW6NE7"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getDatabase(app);

// Admin Check Logic
const allowedAdmin = "ops5mellin@gmail.com"; // আপনার এডমিন ইমেইল

function checkAdmin() {
    onAuthStateChanged(auth, (user) => {
        if (user) {
            // যদি ইমেইল না মিলে, লগআউট করে দিবে
            if(user.email.toLowerCase() !== allowedAdmin.toLowerCase()){
                alert("Access Denied!");
                signOut(auth).then(() => window.location.href = "../login.php");
            } else {
                // ইমেইল দেখাবে যদি এলিমেন্ট থাকে
                const emailEl = document.getElementById('adminEmailDisplay');
                if(emailEl) emailEl.innerText = user.email;
            }
        } else {
            // লগইন না থাকলে লগইন পেজে পাঠাবে
            window.location.href = "../login.php";
        }
    });
}

// Export functions to use in other pages
export { auth, db, ref, get, set, push, update, remove, onValue, signOut, checkAdmin };