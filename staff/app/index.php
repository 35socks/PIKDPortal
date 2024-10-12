<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 

    <title>PIKD - Home</title>
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
        .news-updates {
            margin: 20px 0;
            padding: 20px;
            background-color: #666;
            max-height: 200px;
            overflow-y: auto;
        }
        .news-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #999;
            padding-bottom: 10px;
        }
        .news-item:last-child {
            border-bottom: none;
        }
        .news-item h5 {
            margin: 0;
        }
        .news-item p {
            margin: 5px 0;
        }
        .read-more {
            cursor: pointer;
            color: #4CAF50;
        }
        .price {
            margin: 20px 0;
            padding: 20px;
            background-color: #111;
        }
        .account-settings {
            margin: 20px 0;
            padding: 20px;
            background-color: #333;
        }
        #royaltyChart {
            width: 100%;
            height: 300px;
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
        .release-item {
            background-color: #333;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        .release-cover {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 20px;
        }
        .release-info {
            flex-grow: 1;
        }
        .see-all-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
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
                <h2 id="welcomeMessage"></h2>
                <div class="news-updates" id="newsUpdates">
                    <h3>News</h3>
                </div>
                    <div class="account-settings">
                    <h5>Change Password</h5>
                    <form id="changePasswordForm">
                        <input type="password" id="currentPassword" placeholder="Current Password" required>
                        <input type="password" id="newPassword" placeholder="New Password" required>
                        <input type="password" id="confirmPassword" placeholder="Confirm New Password" required>
                        <button type="submit">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script type="module">
// Import the functions you need from the SDKs you need
import { getAuth, signOut, onAuthStateChanged, updatePassword, reauthenticateWithCredential, EmailAuthProvider } from "https://www.gstatic.com/firebasejs/10.13.0/firebase-auth.js";

// Initialize Firebase Authentication
const auth = getAuth();

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

    // Function to load and display royalty data
    function loadRoyaltyData(userEmail) {
        fetch('royalty.txt')
            .then(response => response.text())
            .then(data => {
                const lines = data.split('\n');
                const royalties = [];
                const labels = [];
                let currentUser = '';

                lines.forEach(line => {
                    if (line.endsWith(':')) {
                        currentUser = line.slice(0, -1);
                    } else if (currentUser === userEmail) {
                        const match = line.match(/\$(\d+\.\d+)\s*\{time:\s*(\w+-\d+)\}/);
                        if (match) {
                            const amount = parseFloat(match[1]);
                            royalties.push(amount);
                            labels.push(match[2]);
                        }
                    }
                });

                if (royalties.length > 0) {
                    document.getElementById('latestRoyalty').textContent = `Latest Royalty: $${royalties[royalties.length - 1].toFixed(2)}`;

                    const ctx = document.getElementById('royaltyChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Royalties',
                                data: royalties,
                                borderColor: 'rgb(75, 192, 192)',
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value, index, values) {
                                            return '$' + value;
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    document.getElementById('latestRoyalty').textContent = 'No royalty data available';
                }
            })
            .catch(error => console.error('Error loading royalty data:', error));
    }

    // Function to load and display news data
    function loadNewsData() {
        fetch('notices.txt')
            .then(response => response.text())
            .then(data => {
                const newsItems = data.split('--end--').filter(item => item.trim() !== '');
                const newsContainer = document.getElementById('newsUpdates');

                newsItems.forEach(item => {
                    const lines = item.trim().split('\n');
                    const date = lines[1].trim();
                    const title = lines[2].replace('# ', '').trim();
                    const body = lines.slice(3).join('\n').trim();

                    const newsItem = document.createElement('div');
                    newsItem.className = 'news-item';
                    newsItem.innerHTML = `
                        <h5>${title}</h5>
                        <p>${date}</p>
                        <p class="news-body">${body.substring(0, 100)}...</p>
                        <span class="read-more">Read More</span>
                    `;

                    const newsBody = newsItem.querySelector('.news-body');
                    const readMore = newsItem.querySelector('.read-more');

                    readMore.addEventListener('click', () => {
                        if (readMore.textContent === 'Read More') {
                            newsBody.textContent = body;
                            readMore.textContent = 'Show Less';
                        } else {
                            newsBody.textContent = body.substring(0, 100) + '...';
                            readMore.textContent = 'Read More';
                        }
                    });

                    newsContainer.appendChild(newsItem);
                });
            })
            .catch(error => console.error('Error loading news data:', error));
    }

    // Function to load and display the latest release
    function loadLatestRelease(userEmail) {
        fetch('releases/releases.json')
            .then(response => response.json())
            .then(data => {
                const userReleases = data.releases.filter(release => release.email === userEmail);
                if (userReleases.length > 0) {
                    // Sort releases by release date (newest first)
                    userReleases.sort((a, b) => new Date(b.release_date) - new Date(a.release_date));
                    
                    const latestRelease = userReleases[0];
                    const releaseContainer = document.getElementById('latestRelease');
                    releaseContainer.innerHTML = `
                        <div class="release-item">
                            <img src="${latestRelease.cover_art}" alt="${latestRelease.title}" class="release-cover">
                            <div class="release-info">
                                <h4>${latestRelease.title}</h4>
                                <p>Status: ${latestRelease.status}</p>
                                <p>Release Date: ${latestRelease.release_date}</p>
                                <p>UPC: ${latestRelease.upc}</p>
                            </div>
                        </div>
                    `;
                } else {
                    document.getElementById('latestRelease').innerHTML = '<p>No releases found.</p>';
                }
            })
            .catch(error => console.error('Error loading release data:', error));
    }
</script>


</body>
</html>