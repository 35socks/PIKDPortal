<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIKD - Releases</title>
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
            width: 90%;
            margin: 50px auto;
            padding: 20px;
            background-color: #111;
        }
        .releases-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .release-item {
            position: relative;
            cursor: pointer;
        }
        .release-cover {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .release-info {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .release-item:hover .release-info {
            opacity: 1;
        }
        .release-title {
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
        .release-status {
            font-size: 0.9em;
        }
        .create-new-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
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
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }
        .track-dropdown {
            margin-bottom: 10px;
        }
        .track-details {
            display: none;
            margin-left: 20px;
            margin-top: 10px;
        }
        .link-box {
            background-color: #444;
            padding: 10px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .copy-btn {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
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
                <h2>Your Releases</h2>
                <div id="releases" class="releases-grid"></div>
                <a href="/app/releases/create-new" class="create-new-button">Create New Release</a>
            </div>
        </div>
    </div>

<div id="releaseModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="modalContent"></div>
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

    onAuthStateChanged(auth, (user) => {
        if (!user) {
            window.location.href = '/';
        } else {
            loadReleaseData(user.email);
        }
    });

    document.getElementById('logoutButton').addEventListener('click', () => {
        signOut(auth).then(() => {
            window.location.href = '/';
        }).catch((error) => {
            console.error("Logout Error:", error);
        });
    });

    function loadReleaseData(userEmail) {
        fetch('releases.json')
            .then(response => response.json())
            .then(data => {
                const userReleases = data.releases.filter(release => release.email === userEmail);
                
                // Sort releases by release date (newest first)
                userReleases.sort((a, b) => new Date(b.release_date) - new Date(a.release_date));

                const releasesContainer = document.getElementById('releases');
                releasesContainer.innerHTML = userReleases.map(release => `
                    <div class="release-item" data-release='${JSON.stringify(release)}'>
                        <img src="${release.cover_art}" alt="${release.title}" class="release-cover">
                        <div class="release-info">
                            <div class="release-title">${release.title}</div>
                            <div class="release-status">${release.status}</div>
                        </div>
                    </div>
                `).join('');

                // Add click event listeners to release items
                document.querySelectorAll('.release-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const release = JSON.parse(this.getAttribute('data-release'));
                        showReleaseDetails(release);
                    });
                });
            })
            .catch(error => console.error('Error loading release data:', error));
    }

    function showReleaseDetails(release) {
        const modal = document.getElementById('releaseModal');
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = `
            <h2>${release.title}</h2>
            <p>Status: ${release.status}</p>
            <p>UPC: ${release.upc}</p>
            <p>Release Date: ${release.release_date}</p>
            <h3>Tracks:</h3>
            ${release.tracks.map((track, index) => `
                <div class="track-dropdown">
                    <button onclick="toggleTrackDetails(${index})">${track.title}</button>
                    <div id="trackDetails${index}" class="track-details">
                        <p>ISRC: ${track.isrc}</p>
                        <p>Explicit: ${track.explicit}</p>
                        <p>Language: ${track.language}</p>
                    </div>
                </div>
            `).join('')}
            <h3>Links:</h3>
            <div class="link-box">
                <span>Promo Player:</span>
                <input type="text" value="${release.links.promo_player}" readonly>
                <button class="copy-btn" onclick="copyToClipboard('${release.links.promo_player}')">Copy</button>
            </div>
            <div class="link-box">
                <span>Stream Link:</span>
                <input type="text" value="${release.links.platform_stream}" readonly>
                <button class="copy-btn" onclick="copyToClipboard('${release.links.platform_stream}')">Copy</button>
            </div>
        `;
        modal.style.display = "block";

        // Close modal when clicking on <span> (x)
        document.querySelector('.close').onclick = function() {
            modal.style.display = "none";
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }

    // Add these functions to the global scope
    window.toggleTrackDetails = function(index) {
        const details = document.getElementById(`trackDetails${index}`);
        details.style.display = details.style.display === 'none' ? 'block' : 'none';
    }

    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
</script>


</body>
</html>