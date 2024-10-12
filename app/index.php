<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIKD - Home</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Be Vietnam Pro', Arial, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <div class="flex h-screen">
        <div class="w-1/5 bg-gray-800 p-6 shadow-lg">
            <div class="text-4xl text-green-400 text-center mb-8">PIKD</div>
            <div class="menu-item mb-4 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600" onclick="location.href='/app';">Home</div>
            <div class="menu-item mb-4 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600" onclick="location.href='/app/releases';">Releases</div>
            <div class="menu-item mb-4 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600" onclick="location.href='/app/payouts';">Payouts</div>
            <div class="menu-item mb-4 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600" onclick="location.href='/app/files';">File Sharing</div>
            <div class="menu-item mb-4 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600" onclick="location.href='/app/settings';">Account Settings</div>
            <div class="menu-item mb-4 p-4 bg-red-600 rounded-lg cursor-pointer hover:bg-red-500" id="logoutButton">Log Out</div>
        </div>
        <div class="w-4/5 bg-gray-800 p-6 overflow-y-auto">
            <div class="bg-gray-700 p-6 rounded-lg shadow-lg">
                <h2 id="welcomeMessage" class="text-2xl font-semibold"></h2>
                <h3 class="text-xl font-bold mt-4">Latest Release</h3>
                <div id="latestRelease"></div>
                <a href="/app/releases" class="inline-block mt-4 bg-green-500 hover:bg-green-400 text-white font-bold py-2 px-4 rounded">See All Releases</a>
                <div class="mt-6">
                    <div class="bg-gray-700 p-4 rounded-lg shadow-md">
                        <h3 class="text-xl font-bold">News</h3>
                        <div id="newsUpdates"></div>
                    </div>
                </div>
                <h3 class="text-xl font-bold mt-4">Royalties</h3>
                <div class="bg-gray-700 p-4 rounded-lg shadow-md">
                    <h5 id="latestRoyalty" class="text-lg font-semibold"></h5>
                    <canvas id="royaltyChart" class="w-full h-72 mt-4"></canvas>
                </div>
                <div class="bg-gray-700 p-4 rounded-lg shadow-md mt-4">
                    <h5 class="text-lg font-semibold">Change Password</h5>
                    <form id="changePasswordForm" class="flex flex-col mt-2">
                        <input type="password" id="currentPassword" placeholder="Current Password" required class="p-2 mb-2 rounded bg-gray-600 text-white">
                        <input type="password" id="newPassword" placeholder="New Password" required class="p-2 mb-2 rounded bg-gray-600 text-white">
                        <input type="password" id="confirmPassword" placeholder="Confirm New Password" required class="p-2 mb-4 rounded bg-gray-600 text-white">
                        <button type="submit" class="bg-green-500 hover:bg-green-400 text-white font-bold py-2 px-4 rounded">Change Password</button>
                    </form>
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
        apiKey: "AIzaSyAWZXRVxPdQcBruEYVWalpCX1xF7zn-W7s",
        authDomain: "pikd-3204a.firebaseapp.com",
        projectId: "pikd-3204a",
        storageBucket: "pikd-3204a.appspot.com",
        messagingSenderId: "238940398854",
        appId: "1:238940398854:web:6adc63c41a3fa7664d2a87",
        measurementId: "G-PW90206Q87"
    };

    // Initialize Firebase
    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);

    // Check if user is logged in
    onAuthStateChanged(auth, (user) => {
        if (!user) {
            // No user is signed in, redirect to login page
            window.location.href = '/';
        } else {
            // User is signed in, load the data
            document.getElementById('welcomeMessage').textContent = `Welcome, ${user.displayName || user.email}`;
            loadRoyaltyData(user.email);
            loadNewsData();
            loadLatestRelease(user.email);
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

    // Function to load and display royalty data
    function loadRoyaltyData(userEmail) {
        fetch('royaltiesfile.txt')
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