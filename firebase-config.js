// --- START OF FILE firebase-config.js ---

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
import { getDatabase } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-database.js";

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

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getDatabase(app);

// Export instances
export { app, auth, db };