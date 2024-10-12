<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PIKD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/c4f218aa74.js" crossorigin="anonymous"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-black min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">PIKD Staff Portal</h1>
            <p class="text-gray-600 mt-2">Please sign in to your account</p>
        </div>
        
        <form id="loginForm" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input type="email" id="email" required 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" required 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="text-red-500 text-sm hidden error">
                <i class="fas fa-exclamation-triangle"></i> Please check your email and password.
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-black hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Sign in
            </button>
        </form>

        <div class="mt-6 space-y-2">
            <button id="forgotPassword" class="text-sm text-black hover:text-slate-700 w-full text-center">
                Forgot your password?
            </button>
            <button onclick="location.href='/';" class="text-sm text-black hover:text-slate-700 w-full text-center">
                Artist Login
            </button>
        </div>
    </div>

    <script type="module">
        // Import the functions you need from the SDKs you need
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-app.js";
        import { getAuth, signInWithEmailAndPassword, sendPasswordResetEmail, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-auth.js";

        // Your web app's Firebase configuration
        const firebaseConfig = {
            apiKey: "",
            authDomain: "",
            projectId: "",
            storageBucket: "",
            messagingSenderId: "",
            appId: ""
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);

        // Check if user is already logged in
        onAuthStateChanged(auth, (user) => {
            if (user) {
                window.location.href = 'app';
            }
        });

        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            signInWithEmailAndPassword(auth, email, password)
                .then((userCredential) => {
                    window.location.href = 'app';
                })
                .catch((error) => {
                    document.querySelector('.error').classList.remove('hidden');
                    console.error("Error:", error.message);
                });
        });

        document.getElementById('forgotPassword').addEventListener('click', () => {
            const email = document.getElementById('email').value;
            if (email) {
                sendPasswordResetEmail(auth, email)
                    .then(() => {
                        alert("Password reset email sent!");
                    })
                    .catch((error) => {
                        alert("Error: " + error.message);
                    });
            } else {
                alert("Please enter your email address.");
            }
        });
    </script>
</body>
</html>	

   