<?php
session_start();

// File upload handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $userEmail = $_POST['email'];
        $uploadDir = "uploads/{$userEmail}/";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file = $_FILES['file'];
        $fileName = basename($file['name']);
        $targetFilePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Upload failed']);
        }
        exit;
    } elseif (isset($_POST['delete'])) {
        $userEmail = $_POST['email'];
        $filename = $_POST['filename'];
        $filePath = "uploads/{$userEmail}/{$filename}";
        
        if (file_exists($filePath) && unlink($filePath)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
        exit;
    }
}

// File listing
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email'])) {
    $userEmail = $_GET['email'];
    $uploadDir = "uploads/{$userEmail}/";
    $files = array_diff(scandir($uploadDir), array('..', '.'));
    echo json_encode(array_values($files));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIKD - File Sharing</title>
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
        #drop_zone {
            border: 2px dashed #ccc;
            border-radius: 20px;
            width: 100%;
            height: 200px;
            padding: 25px;
            text-align: center;
            font-size: 20px;
            box-sizing: border-box;
        }
        #file_list {
            margin-top: 20px;
        }
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #333;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .file-item a {
            color: #fff;
            text-decoration: none;
        }
        .file-item button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        .copy-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
            margin-left: 10px;
        }
        #progress_bar {
            width: 100%;
            background-color: #333;
            height: 20px;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            display: none;
        }
        #progress {
            width: 0%;
            height: 100%;
            background-color: #4CAF50;
            transition: width 0.3s;
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
                <h2>File Sharing</h2>
                <div id="drop_zone" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);">
                    <p>Drag one or more files to this Drop Zone ...</p>
                    <input type="file" id="fileElem" multiple accept="*" onchange="handleFiles(this.files)">
                </div>
                <div id="progress_bar">
                    <div id="progress"></div>
                </div>
                <div id="file_list"></div>
            </div>
        </div>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-app.js";
        import { getAuth, signOut, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-auth.js";

        const firebaseConfig = {
            apiKey: "",
            authDomain: "",
            projectId: "",
            storageBucket: "",
            messagingSenderId: "",
            appId: "",
            measurementId: ""
        };

        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);

        let userEmail = '';

        onAuthStateChanged(auth, (user) => {
            if (!user) {
                window.location.href = '/';
            } else {
                userEmail = user.email.split('@')[0];
                loadFileList();
            }
        });

        document.getElementById('logoutButton').addEventListener('click', () => {
            signOut(auth).then(() => {
                window.location.href = '/';
            }).catch((error) => {
                console.error("Logout Error:", error);
            });
        });

        window.dropHandler = (ev) => {
            ev.preventDefault();
            if (ev.dataTransfer.items) {
                [...ev.dataTransfer.items].forEach((item, i) => {
                    if (item.kind === 'file') {
                        const file = item.getAsFile();
                        uploadFile(file);
                    }
                });
            }
        }

        window.dragOverHandler = (ev) => {
            ev.preventDefault();
        }

        window.handleFiles = (files) => {
            [...files].forEach(uploadFile);
        }

        function uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('email', userEmail);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', window.location.href, true);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    document.getElementById('progress').style.width = percentComplete + '%';
                }
            };

            xhr.onloadstart = function() {
                document.getElementById('progress_bar').style.display = 'block';
                document.getElementById('progress').style.width = '0%';
            };

            xhr.onloadend = function() {
                setTimeout(() => {
                    document.getElementById('progress_bar').style.display = 'none';
                }, 1000);
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        loadFileList();
                    } else {
                        alert('Upload failed: ' + response.message);
                    }
                } else {
                    alert('Upload failed. Please try again.');
                }
            };

            xhr.onerror = function() {
                console.error('Error:', xhr.statusText);
                alert('Upload failed. Please try again.');
            };

            xhr.send(formData);
        }

        function loadFileList() {
            fetch(`${window.location.href}?email=${userEmail}`)
            .then(response => response.json())
            .then(files => {
                const fileList = document.getElementById('file_list');
                fileList.innerHTML = '';
                files.forEach(file => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item';
                    const fileUrl = `https://files.pikd.nl/uploads/${userEmail}/${file}`;
                    fileItem.innerHTML = `
                        <a href="${fileUrl}" target="_blank">${file}</a>
                        <div>
                            <button onclick="copyToClipboard('${fileUrl}')">Copy URL</button>
                            <button onclick="deleteFile('${file}')">Delete</button>
                        </div>
                    `;
                    fileList.appendChild(fileItem);
                });
            })
            .catch(error => console.error('Error:', error));
        }

        window.copyToClipboard = (text) => {
            navigator.clipboard.writeText(text).then(() => {
                alert('URL copied to clipboard');
            }, (err) => {
                console.error('Could not copy text: ', err);
            });
        }

        window.deleteFile = (filename) => {
            if (confirm(`Are you sure you want to delete ${filename}?`)) {
                const formData = new FormData();
                formData.append('delete', 'true');
                formData.append('email', userEmail);
                formData.append('filename', filename);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadFileList();
                    } else {
                        alert('Delete failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Delete failed. Please try again.');
                });
            }
        }
    </script>
</body>
</html>
