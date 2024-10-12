<?php
$dir = __DIR__ . '/';
if (!is_dir($dir) || !is_readable($dir)) {
    die("Error: Unable to access the directory.");
}

$files = array_filter(scandir($dir), function($file) use ($dir) {
    return is_file($dir . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'json';
});

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $fileToDelete = $dir . basename($_POST['delete']);
    if (file_exists($fileToDelete) && is_file($fileToDelete) && pathinfo($fileToDelete, PATHINFO_EXTENSION) === 'json') {
        if (unlink($fileToDelete)) {
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Failed to delete file.";
        }
    }
}

function getSubmissions($files, $dir) {
    $submissions = [];
    foreach ($files as $file) {
        $content = file_get_contents($dir . $file);
        $jsonData = json_decode($content, true);
        if (is_array($jsonData)) {
            $jsonData['file'] = $file;
            $submissions[] = $jsonData;
        }
    }
    return $submissions;
}

$submissions = getSubmissions($files, $dir);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIKD - Contact Requests</title>
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
            width: 100%;
            margin: 20px auto;
            padding: 20px;
            background-color: #222;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        th {
            background-color: #333;
        }
        tr:hover {
            background-color: #2a2a2a;
        }
        .delete-btn {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
        }
        .delete-btn:hover {
            background-color: #c9302c;
        }
        .error {
            color: #d9534f;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-column">
            <div class="logo">
                PIKD Admin Portal
            </div>
            <div class="menu-item" onclick="location.href='/staff/app';">Home</div>
            <div class="menu-item" onclick="location.href='/staff/app/releases';">Release Editor</div>
            <div class="menu-item" onclick="location.href='/staff/app/contact';">Contact Form Requests</div>
            <div class="menu-item" onclick="location.href='/staff/app/applications';">Artist Applications</div>
            <div class="menu-item" onclick="location.href='/staff/app/files';">File Sharing</div>
            <div class="menu-item logout" id="logoutButton">Log Out</div>
        </div>
        <div class="right-column">
            <div class="content">
                <h1>Contact Form Requests</h1>
                <?php if (isset($error)): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Body</th>
                        <th>File</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?= htmlspecialchars($submission['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($submission['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($submission['subject'] ?? '') ?></td>
                            <td><?= htmlspecialchars($submission['body'] ?? '') ?></td>
                            <td><?= htmlspecialchars($submission['file']) ?></td>
                            <td>
                                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="delete" value="<?= htmlspecialchars($submission['file']) ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <script type="module">
        // Import the functions you need from the SDKs you need
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-app.js";
        import { getAuth, signOut, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-auth.js";

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

        // Check if user is logged in
        onAuthStateChanged(auth, (user) => {
            if (!user) {
                // No user is signed in, redirect to login page
                window.location.href = '/staff';
            }
        });

        // Logout functionality
        document.getElementById('logoutButton').addEventListener('click', () => {
            signOut(auth).then(() => {
                // Sign-out successful, redirect to login page
                window.location.href = '/staff';
            }).catch((error) => {
                // An error happened
                console.error("Logout Error:", error);
            });
        });
    </script>
</body>
</html>
