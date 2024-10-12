<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIKD - Payouts</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        #royaltyChart {
            width: 100%;
            height: 300px;
            margin-bottom: 20px;
        }
        .request-payout-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
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
                <h2>Your Payouts</h2>
                <canvas id="royaltyChart"></canvas>
                <h3 id="totalRoyalties"></h3>
                <button id="requestPayoutButton" class="request-payout-button">Request Payout</button>
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
        let totalRoyalties = 0;

        onAuthStateChanged(auth, (user) => {
            if (!user) {
                window.location.href = '/';
            } else {
                userEmail = user.email;
                loadRoyaltyData(userEmail);
            }
        });

        document.getElementById('logoutButton').addEventListener('click', () => {
            signOut(auth).then(() => {
                window.location.href = '/';
            }).catch((error) => {
                console.error("Logout Error:", error);
            });
        });

        document.getElementById('requestPayoutButton').addEventListener('click', requestPayout);

        function loadRoyaltyData(userEmail) {
            fetch('../royalty.txt')
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

                    // Set total royalties to the latest amount
                    totalRoyalties = royalties[royalties.length - 1] || 0;

                    document.getElementById('totalRoyalties').textContent = `Total Royalties: $${totalRoyalties.toFixed(2)}`;

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
                })
                .catch(error => console.error('Error loading royalty data:', error));
        }

        function requestPayout() {
            const webhookUrl = 'https://api.pikd.nl/v1/payout';
            const message = {
                content: `${userEmail} has requested a payout of $${totalRoyalties.toFixed(2)}`
            };

            fetch(webhookUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(message)
            })
            .then(response => {
                if (response.ok) {
                    alert('Payout request sent successfully!');
                } else {
                    throw new Error('Failed to send payout request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to send payout request. Please try again later.');
            });
        }
    </script>
</body>
</html>
