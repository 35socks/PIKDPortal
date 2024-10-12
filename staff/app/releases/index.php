<?php
session_start();

$releasesFile = '../../../app/releases/releases.json';

// Read the existing releases
$jsonContent = file_get_contents($releasesFile);
$releasesData = json_decode($jsonContent, true);

// Handle form submission for creating or editing releases
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create']) || isset($_POST['edit'])) {
        $newRelease = [
            'email' => $_POST['email'],
            'title' => $_POST['title'],
            'status' => $_POST['status'],
            'upc' => $_POST['upc'],
            'release_date' => $_POST['release_date'],
            'tracks' => [],
            'links' => [
                'promo_player' => $_POST['promo_player'],
                'platform_stream' => $_POST['platform_stream']
            ],
            'cover_art' => $_POST['cover_art']
        ];

        // Process tracks
        $trackCount = count($_POST['track_title']);
        for ($i = 0; $i < $trackCount; $i++) {
            $newRelease['tracks'][] = [
                'title' => $_POST['track_title'][$i],
                'isrc' => $_POST['track_isrc'][$i],
                'explicit' => isset($_POST['track_explicit'][$i]),
                'language' => $_POST['track_language'][$i]
            ];
        }

        if (isset($_POST['edit'])) {
            // Edit existing release
            $editIndex = $_POST['edit_index'];
            $releasesData['releases'][$editIndex] = $newRelease;
        } else {
            // Create new release
            $releasesData['releases'][] = $newRelease;
        }

        // Save updated data
        file_put_contents($releasesFile, json_encode($releasesData, JSON_PRETTY_PRINT));
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle deletion
    if (isset($_POST['delete'])) {
        $deleteIndex = $_POST['delete'];
        array_splice($releasesData['releases'], $deleteIndex, 1);
        file_put_contents($releasesFile, json_encode($releasesData, JSON_PRETTY_PRINT));
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Prepare edit data if in edit mode
$editMode = isset($_GET['edit']);
$editData = [];
if ($editMode) {
    $editIndex = $_GET['edit'];
    $editData = $releasesData['releases'][$editIndex];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-app.js";
        import { getAuth, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-auth.js";

        // Firebase configuration
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

        // Hide the content div initially
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.display = 'none';
        });

        // Check authentication immediately
        onAuthStateChanged(auth, (user) => {
            if (!user) {
                window.location.href = '/staff';
            } else {
                // Only show the content if authenticated
                document.body.style.display = 'block';
                initializeApp(); // Function to initialize the rest of your app
            }
        });

    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIKD - Release Editor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/c4f218aa74.js" crossorigin="anonymous"></script>
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
        h1, h2 {
            color: #4CAF50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #2a2a2a;
        }
        th, td {
            padding: 12px;
            border: 1px solid #444;
            text-align: left;
        }
        th {
            background-color: #333;
            color: #4CAF50;
        }
        tr:hover {
            background-color: #3a3a3a;
        }
        .delete-btn, .edit-btn {
            padding: 8px 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .delete-btn {
            background-color: #d9534f;
            color: white;
        }
        .edit-btn {
            background-color: #5bc0de;
            color: white;
            margin-right: 5px;
        }
        .delete-btn:hover, .edit-btn:hover {
            opacity: 0.8;
        }
        .social-link {
            color: #4CAF50;
            text-decoration: none;
            margin-right: 10px;
            font-size: 1.2em;
        }
        .social-link:hover {
            text-decoration: underline;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #4CAF50;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #444;
            background-color: #2a2a2a;
            color: #fff;
            border-radius: 4px;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-actions {
            text-align: right;
        }
        .form-actions button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-actions button:hover {
            background-color: #45a049;
        }
        .track-container {
            border: 1px solid #444;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        #addTrackBtn {
            background-color: #5bc0de;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        #addTrackBtn:hover {
            background-color: #46b8da;
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
                <h1>PIKD Releases Admin</h1>
                <table>
                    <tr>
                        <th>Title</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Release Date</th>
                        <th>UPC</th>
                        <th>Promo Player</th>
                        <th>Platform Stream</th>
                        <th>Cover Art</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($releasesData['releases'] as $index => $release): ?>
                        <tr>
                            <td><?= htmlspecialchars($release['title']) ?></td>
                            <td><?= htmlspecialchars($release['email']) ?></td>
                            <td><?= htmlspecialchars($release['status']) ?></td>
                            <td><?= htmlspecialchars($release['release_date']) ?></td>
                            <td><?= htmlspecialchars($release['upc']) ?></td>
                            <td><a href="<?= htmlspecialchars($release['links']['promo_player']) ?>" class="social-link" target="_blank">Promo Player</a></td>
                            <td><a href="<?= htmlspecialchars($release['links']['platform_stream']) ?>" class="social-link" target="_blank">Stream/Presave</a></td>
                            <td><img src="<?= htmlspecialchars($release['cover_art']) ?>" alt="Cover Art" style="width: 50px; height: 50px;"></td>
                            <td>
                                <a href="?edit=<?= $index ?>" class="edit-btn">Edit</a>
                                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="delete" value="<?= $index ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this release?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <h2><?= $editMode ? 'Edit Release' : 'Create New Release' ?></h2>
                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="edit_index" value="<?= $_GET['edit'] ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($editData['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" value="<?= htmlspecialchars($editData['title'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" required>
                            <option value="PENDING APPROVAL" <?= ($editData['status'] ?? '') == 'PENDING APPROVAL' ? 'selected' : '' ?>>Pending Approval</option>
                            <option value="SENT TO STORES" <?= ($editData['status'] ?? '') == 'SENT TO STORES' ? 'selected' : '' ?>>Sent to Stores</option>
                            <option value="CORRECTION PENDING" <?= ($editData['status'] ?? '') == 'CORRECTION PENDING' ? 'selected' : '' ?>>CORRECTION PENDING</option>
                            <option value="LIVE" <?= ($editData['status'] ?? '') == 'LIVE' ? 'selected' : '' ?>>Live</option>
                            <option value="TAKEDOWN" <?= ($editData['status'] ?? '') == 'TAKEDOWN' ? 'selected' : '' ?>>TAKEDOWN</option>
                            <option value="ERROR" <?= ($editData['status'] ?? '') == 'ERROR' ? 'selected' : '' ?>>Error With Releases</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="upc">UPC</label>
                        <input type="text" name="upc" id="upc" value="<?= htmlspecialchars($editData['upc'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="release_date">Release Date</label>
                        <input type="date" name="release_date" id="release_date" value="<?= htmlspecialchars($editData['release_date'] ?? '') ?>" required>
                    </div>
                    <div id="tracksContainer">
                        <?php 
                        $tracks = $editData['tracks'] ?? [['title' => '', 'isrc' => '', 'explicit' => false, 'language' => '']];
                        foreach ($tracks as $index => $track):
                        ?>
                            <div class="track-container">
                                <h3>Track <?= $index + 1 ?></h3>
                                <div class="form-group">
                                    <label for="track_title_<?= $index ?>">Track Title</label>
                                    <input type="text" name="track_title[]" id="track_title_<?= $index ?>" value="<?= htmlspecialchars($track['title']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="track_isrc_<?= $index ?>">ISRC</label>
                                    <input type="text" name="track_isrc[]" id="track_isrc_<?= $index ?>" value="<?= htmlspecialchars($track['isrc']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="track_explicit[]" value="1" <?= $track['explicit'] ? 'checked' : '' ?>>
                                        Explicit
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label for="track_language_<?= $index ?>">Language</label>
                                    <input type="text" name="track_language[]" id="track_language_<?= $index ?>" value="<?= htmlspecialchars($track['language']) ?>" required>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="addTrackBtn">Add Track</button>
                    <div class="form-group">
                        <label for="promo_player">Promo Player Link</label>
                        <input type="url" name="promo_player" id="promo_player" value="<?= htmlspecialchars($editData['links']['promo_player'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="platform_stream">Platform Stream Link</label>
                        <input type="url" name="platform_stream" id="platform_stream" value="<?= htmlspecialchars($editData['links']['platform_stream'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="cover_art">Cover Art URL</label>
                        <input type="url" name="cover_art" id="cover_art" value="<?= htmlspecialchars($editData['cover_art'] ?? '') ?>" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="<?= $editMode ? 'edit' : 'create' ?>"><?= $editMode ? 'Save Changes' : 'Create Release' ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script type="module">
// Import the functions you need from the SDKs you need
import { getAuth, signOut, onAuthStateChanged, updatePassword, reauthenticateWithCredential, EmailAuthProvider } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-auth.js";

// Initialize Firebase Authentication
const auth = getAuth();

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

// Change password functionality
document.getElementById('changePasswordForm').addEventListener('submit', (e) => {
    e.preventDefault();

    // Make sure auth is initialized
    const user = auth.currentUser;

    if (!user) {
        alert("No user is logged in");
        return;
    }

    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Check if the new password and confirmation match
    if (newPassword !== confirmPassword) {
        alert("New passwords don't match");
        return;
    }

    // Reauthenticate the user before updating password
    const credential = EmailAuthProvider.credential(user.email, currentPassword);

    reauthenticateWithCredential(user, credential).then(() => {
        // After successful reauthentication, update password
        return updatePassword(user, newPassword);
    }).then(() => {
        alert("Password updated successfully");
        document.getElementById('changePasswordForm').reset();
    }).catch((error) => {
        if (error.code === 'auth/wrong-password') {
            alert("Current password is incorrect");
        } else {
            console.error("Error updating password:", error);
            alert("Error updating password: " + error.message);
        }
    });
});

         // Add Track functionality
        document.getElementById('addTrackBtn').addEventListener('click', function() {
            const tracksContainer = document.getElementById('tracksContainer');
            const trackCount = tracksContainer.children.length;
            const newTrackHtml = `
                <div class="track-container">
                    <h3>Track ${trackCount + 1}</h3>
                    <div class="form-group">
                        <label for="track_title_${trackCount}">Track Title</label>
                        <input type="text" name="track_title[]" id="track_title_${trackCount}" required>
                    </div>
                    <div class="form-group">
                        <label for="track_isrc_${trackCount}">ISRC</label>
                        <input type="text" name="track_isrc[]" id="track_isrc_${trackCount}" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="track_explicit[]" value="1">
                            Explicit
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="track_language_${trackCount}">Language</label>
                        <input type="text" name="track_language[]" id="track_language_${trackCount}" required>
                    </div>
                </div>
            `;
            tracksContainer.insertAdjacentHTML('beforeend', newTrackHtml);
        });

    </script>
</body>
</html>
