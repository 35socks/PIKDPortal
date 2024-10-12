<?php
session_start();

$webhookUrl = 'https://api.pikd.nl/v1/new-release';
$releasesFile = '../releases.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userEmail = $_POST['email'];
    $artistName = $_POST['artistName'];
    $title = $_POST['title'];
    $releaseDate = $_POST['releaseDate'];
    $tracks = json_decode($_POST['tracks'], true);

    $uploadDir = "uploads/{$userEmail}/";
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $coverArt = $_FILES['coverArt'];
    $coverArtName = strtolower(preg_replace('/[^a-zA-Z0-9.]/', '', basename($coverArt['name'])));
    $targetCoverArtPath = $uploadDir . $coverArtName;

    if (move_uploaded_file($coverArt['tmp_name'], $targetCoverArtPath)) {
        $coverArtUrl = "https://portal.pikd.nl/app/releases/create-new/uploads/{$userEmail}/{$coverArtName}";
        
        $message = "New release submitted by {$artistName} ({$userEmail}):\n" .
                   "Title: {$title}\n" .
                   "Release Date: {$releaseDate}\n" .
                   "Cover Art: {$coverArtUrl}\n" .
                   "Tracks:\n";

        $trackData = [];
        foreach ($tracks as $index => $track) {
            $trackFile = $_FILES["track_{$index}"];
            $trackFileName = strtolower(preg_replace('/[^a-zA-Z0-9.]/', '', $trackFile['name']));
            $targetTrackPath = $uploadDir . $trackFileName;
            
            if (move_uploaded_file($trackFile['tmp_name'], $targetTrackPath)) {
                $trackUrl = "https://portal.pikd.nl/app/releases/create-new/{$targetTrackPath}";
                $message .= "- {$track['title']}\n";
                $message .= "  Contributors: {$track['contributors']}\n";
                $message .= "  Language: {$track['language']}\n";
                $message .= "  Explicit: " . ($track['explicit'] ? 'Yes' : 'No') . "\n";
                $message .= "  File: {$trackUrl}\n";

                $trackData[] = [
                    "title" => $track['title'],
                    "isrc" => "NOT YET AVAILABLE",
                    "explicit" => $track['explicit'],
                    "language" => $track['language']
                ];
            }
        }
        
        $data = array('content' => $message);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($webhookUrl, false, $context);

        // Read existing JSON file
        $jsonContent = file_get_contents($releasesFile);
        $releasesData = json_decode($jsonContent, true);

        // Add new release
        $newRelease = [
            "email" => $userEmail,
            "title" => $title,
            "status" => "PENDING APPROVAL",
            "upc" => "NOT YET AVAILABLE",
            "release_date" => $releaseDate,
            "tracks" => $trackData,
            "links" => [
                "promo_player" => "NOT YET AVAILABLE",
                "platform_stream" => "NOT YET AVAILABLE"
            ],
            "cover_art" => $coverArtUrl
        ];

        $releasesData['releases'][] = $newRelease;

        // Write updated JSON back to file
        file_put_contents($releasesFile, json_encode($releasesData, JSON_PRETTY_PRINT));

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload failed']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIKD - Release Uploader</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .release-preview {
            display: flex;
            margin-bottom: 30px;
            background-color: #1a1a1a;
            border-radius: 10px;
            overflow: hidden;
        }
        .cover-art {
            width: 300px;
            height: 300px;
            background-color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #777;
        }
        .cover-art img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .release-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .release-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .artist-name {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .release-date {
            font-size: 14px;
            color: #777;
            margin-bottom: 20px;
        }
        .track-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .track-item {
            background-color: #333;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
            cursor: move;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .track-item:hover {
            background-color: #444;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input, textarea, select {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            border: none;
            background-color: #333;
            color: #fff;
            font-size: 16px;
        }
        button {
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 18px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
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
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            margin-top: 10px;
        }
        .file-input-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
        }
        .file-input-wrapper .btn {
            display: inline-block;
            padding: 8px 12px;
            cursor: pointer;
            background-color: #333;
            color: #fff;
            border-radius: 5px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #333;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }
        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .checkbox-container input[type="checkbox"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-column">
            <div class="logo">PIKD</div>
            <div class="menu-item" onclick="location.href='/app';">Home</div>
            <div class="menu-item" onclick="location.href='/app/releases';">Releases</div>
            <div class="menu-item" onclick="location.href='/app/payouts';">Payouts</div>
            <div class="menu-item" onclick="location.href='/app/files';">File Sharing</div>
            <div class="menu-item" onclick="location.href='/app/settings';">Account Settings</div>
            <div class="menu-item logout" id="logoutButton">Log Out</div>
        </div>
        <div class="right-column">
            <div class="content">
                <h2>Release Uploader</h2>
                <div class="release-preview">
                    <div class="cover-art" id="coverArtPreview">
                        No image selected
                    </div>
                    <div class="release-info">
                        <div class="release-title" id="titlePreview">RELEASE TITLE</div>
                        <div class="artist-name" id="artistNamePreview">Artist Name</div>
                        <div class="release-date" id="releaseDatePreview">release date</div>
                        <ul id="trackList" class="track-list"></ul>
                        <div class="file-input-wrapper" style="margin-top: auto;">
                            <button class="btn">Add Track (WAV)</button>
                            <input type="file" id="trackFile" name="trackFile" accept=".wav">
                        </div>
                    </div>
                </div>
                <form id="releaseForm">
                    <input type="hidden" id="email" name="email">
                    <input type="text" id="artistName" name="artistName" placeholder="Artist Name" required>
                    <input type="text" id="title" name="title" placeholder="Release Title" required>
                    <input type="date" id="releaseDate" name="releaseDate" required>
                    <div class="file-input-wrapper">
                        <button class="btn">Select Cover Art</button>
                        <input type="file" id="coverArt" name="coverArt" accept="image/*" required>
                    </div>
                    <button type="submit">Submit Release</button>
                </form>
                <div id="progress_bar">
                    <div id="progress"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="trackModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Track</h2>
            <input type="text" id="trackTitle" placeholder="Track Title">
            <input type="text" id="contributorsInput" placeholder="Enter contributors (comma separated)">
            <select id="trackLanguage">
                <option value="">Select Language</option>
                <option value="English">English</option>
                <option value="Spanish">Spanish</option>
                <option value="French">French</option>
                <option value="German">German</option>
                <option value="Italian">Italian</option>
                <option value="Japanese">Japanese</option>
                <option value="Korean">Korean</option>
                <option value="Chinese">Chinese</option>
                <option value="Instrumental">Instrumental (Contains No Lyrics)</option>
            </select>
            <div class="checkbox-container">
                <input type="checkbox" id="trackExplicit">
                <label for="trackExplicit">Explicit</label>
            </div>
            <button id="saveTrack">Save</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
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
        let tracks = [];
        let currentTrackIndex = -1;

        onAuthStateChanged(auth, (user) => {
            if (!user) {
                window.location.href = '/';
            } else {
                userEmail = user.email;
                document.getElementById('email').value = userEmail;
            }
        });

        document.getElementById('logoutButton').addEventListener('click', () => {
            signOut(auth).then(() => {
                window.location.href = '/';
            }).catch((error) => {
                console.error("Logout Error:", error);
            });
        });

        // Preview functionality
        document.getElementById('title').addEventListener('input', function() {
            document.getElementById('titlePreview').textContent = this.value || 'RELEASE TITLE';
        });

        document.getElementById('artistName').addEventListener('input', function() {
            document.getElementById('artistNamePreview').textContent = this.value || 'Artist Name';
        });

        document.getElementById('releaseDate').addEventListener('input', function() {
            const date = new Date(this.value);
            const formattedDate = date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('releaseDatePreview').textContent = formattedDate;
        });

        document.getElementById('coverArt').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    const coverArtPreview = document.getElementById('coverArtPreview');
                    coverArtPreview.innerHTML = '';
                    coverArtPreview.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('trackFile').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                tracks.push({
                    title: file.name.replace('.wav', ''),
                    file: file,
                    contributors: '',
                    language: '',
                    explicit: false
                });
                updateTrackList();
            }
        });

        function updateTrackList() {
            const trackList = document.getElementById('trackList');
            trackList.innerHTML = '';
            tracks.forEach((track, index) => {
                const li = document.createElement('li');
                li.className = 'track-item';
                li.innerHTML = `
                    <span>${index + 1}. ${track.title}</span>
                    <div>
                        <i class="fas fa-edit" style="cursor: pointer; margin-right: 10px;"></i>
                        <i class="fas fa-trash" style="cursor: pointer;"></i>
                    </div>
                `;
                li.querySelector('.fa-edit').addEventListener('click', () => openTrackModal(index));
                li.querySelector('.fa-trash').addEventListener('click', () => deleteTrack(index));
                trackList.appendChild(li);
            });
        }

        function deleteTrack(index) {
            tracks.splice(index, 1);
            updateTrackList();
        }

        new Sortable(trackList, {
            animation: 150,
            ghostClass: 'blue-background-class',
            onEnd: function() {
                const newOrder = Array.from(trackList.children).map(li => li.querySelector('span').textContent.trim().split('. ')[1]);
                tracks = newOrder.map(title => tracks.find(track => track.title === title));
                updateTrackList();
            }
        });

        function openTrackModal(index) {
            currentTrackIndex = index;
            const modal = document.getElementById('trackModal');
            const titleInput = document.getElementById('trackTitle');
            const contributorsInput = document.getElementById('contributorsInput');
            const languageSelect = document.getElementById('trackLanguage');
            const explicitCheckbox = document.getElementById('trackExplicit');

            titleInput.value = tracks[index].title;
            contributorsInput.value = tracks[index].contributors;
            languageSelect.value = tracks[index].language;
            explicitCheckbox.checked = tracks[index].explicit;

            modal.style.display = 'block';
        }

        document.querySelector('.close').addEventListener('click', closeModal);
        document.getElementById('saveTrack').addEventListener('click', saveTrack);

        function closeModal() {
            document.getElementById('trackModal').style.display = 'none';
        }

        function saveTrack() {
            const titleInput = document.getElementById('trackTitle');
            const contributorsInput = document.getElementById('contributorsInput');
            const languageSelect = document.getElementById('trackLanguage');
            const explicitCheckbox = document.getElementById('trackExplicit');

            tracks[currentTrackIndex].title = titleInput.value;
            tracks[currentTrackIndex].contributors = contributorsInput.value;
            tracks[currentTrackIndex].language = languageSelect.value;
            tracks[currentTrackIndex].explicit = explicitCheckbox.checked;

            closeModal();
            updateTrackList();
        }

        // Set minimum release date
        const releaseDate = document.getElementById('releaseDate');
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 21); // 3 weeks from now
        while (minDate.getDay() !== 5) { // Find the next Friday
            minDate.setDate(minDate.getDate() + 1);
        }
        releaseDate.min = minDate.toISOString().split('T')[0];
        releaseDate.value = minDate.toISOString().split('T')[0];

        document.getElementById('releaseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('tracks', JSON.stringify(tracks));
            
            tracks.forEach((track, index) => {
                formData.append(`track_${index}`, track.file);
            });

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
                        alert('Release submitted successfully!');
                        document.getElementById('releaseForm').reset();
                        document.getElementById('coverArtPreview').innerHTML = 'No image selected';
                        document.getElementById('titlePreview').textContent = 'RELEASE TITLE';
                        document.getElementById('artistNamePreview').textContent = 'Artist Name';
                        document.getElementById('releaseDatePreview').textContent = 'release date';
                        document.getElementById('trackList').innerHTML = '';
                        tracks = [];
                    } else {
                        alert('Submission failed: ' + response.message);
                    }
                } else {
                    alert('Submission failed. Please try again.');
                }
            };

            xhr.onerror = function() {
                console.error('Error:', xhr.statusText);
                alert('Submission failed. Please try again.');
            };

            xhr.send(formData);
        });
    </script>
</body>
</html>
