<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIKD - Account Settings</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Be Vietnam Pro', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #222;
            color: #fff;
        }
        .container {
            display: flex;
            height: 100vh;
        }
        .left-column {
            width: 20%;
            background-color: #000;
            padding: 20px;
            box-sizing: border-box;
        }
        .right-column {
            width: 80%;
            background-color: #111;
            padding: 20px;
            box-sizing: border-box;
            overflow-y: auto;
        }
        .logo {
            text-align: center;
            font-size: 2em;
            padding: 20px 0;
        }
        .menu-item {
            margin: 20px 0;
            padding: 20px;
            background-color: #333;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .menu-item:not(.logout):hover {
            transform: translateX(10px);
        }
        .logout:hover {
            transform: translateX(-10px);
        }
        .content {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: #111;
        }
        .account-settings {
            margin: 20px 0;
            padding: 20px;
            background-color: #333;
        }
        #changePasswordForm {
            display: flex;
            flex-direction: column;
            max-width: 300px;
        }
        #changePasswordForm input {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #444;
            border: none;
            color: #fff;
        }
        #changePasswordForm button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #changePasswordForm button:hover {
            background-color: #45a049;
        }
        .email-support {
            margin-top: 20px;
        }
        .email-support a {
            color: #4CAF50;
            text-decoration: none;
        }
        .email-support a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-column">
            <div class="logo">
                PIKD
            </div>
            <div class="menu-item" onclick="location.href='/app';">Home</div>
            <div class="menu-item" onclick="location.href='/app/releases';">Releases</div>
            <div class="menu-item" onclick="location.href='/app/payouts';">Payouts</div>
            <div class="menu-item" onclick="location.href='/app/files';">File Sharing</div>
            <div class="menu-item" onclick="location.href='/app/settings';">Account Settings</div>
            <div class="menu-item logout" id="logoutButton">Log Out</div>
        </div>
        <div class="right-column">
            <div class="content">
                <h2>Account Settings</h2>
                <div class="account-settings">
                    <h3>Change Password</h3>
                    <form id="changePasswordForm">
                        <input type="password" id="currentPassword" placeholder="Current Password" required>
                        <input type="password" id="newPassword" placeholder="New Password" required>
                        <input type="password" id="confirmPassword" placeholder="Confirm New Password" required>
                        <button type="submit">Change Password</button>
                    </form>
                </div>
                <div class="email-support">
                    <h3>Need Help?</h3>
                    <p>If you need assistance, please <a href="mailto:portal@pikd.nl">email our support team</a>.</p>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        // Import the functions you need from the SDKs you need
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-app.js";
        import { getAuth, signOut, onAuthStateChanged, updatePassword, reauthenticateWithCredential, EmailAuthProvider } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-auth.js";

        // Your web app's Firebase configuration
        const firebaseConfig = {
            apiKey: "",
            authDomain: "",
            projectId: "",
            storageBucket: "",
            messagingSenderId: "",
            appId: "",
            measurementId: ""
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);

        // Check if user is logged in
        onAuthStateChanged(auth, (user) => {
            if (!user) {
                // No user is signed in, redirect to login page
                window.location.href = '/';
            }
        });

        // Logout functionality
        document.getElementById('logoutButton').addEventListener('click', () => {
            signOut(auth).then(() => {
                // Sign-out successful, redirect to login page
                window.location.href = '/';
            }).catch((error) => {
                // An error happened
                console.error("Logout Error:", error);
            });
        });

        // Change password functionality
        document.getElementById('changePasswordForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const user = auth.currentUser;
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                alert("New passwords don't match");
                return;
            }

            const credential = EmailAuthProvider.credential(user.email, currentPassword);

            reauthenticateWithCredential(user, credential).then(() => {
                updatePassword(user, newPassword).then(() => {
                    alert("Password updated successfully");
                    document.getElementById('changePasswordForm').reset();
                }).catch((error) => {
                    console.error("Error updating password:", error);
                    alert("Error updating password: " + error.message);
                });
            }).catch((error) => {
                console.error("Error re-authenticating:", error);
                alert("Current password is incorrect");
            });
        });
    </script>
</body>
</html>
