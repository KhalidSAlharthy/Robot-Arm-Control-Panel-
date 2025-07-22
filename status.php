<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Robot Arm Status</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Link to external CSS file (same as main panel) -->
    <link rel="stylesheet" href="style.css">
    <style>
        /* Specific styles for the status page layout */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f2f5; /* This will be overridden by style.css body rule */
            font-family: 'Inter', sans-serif;
        }
        .status-container {
            background-color: #ffffff; /* This will be overridden by style.css .status-container rule */
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 2.5rem;
            width: 100%;
            max-width: 600px;
            text-align: center;
            box-sizing: border-box;
        }
    </style>
</head>
<body class="antialiased">
    <div class="status-container">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Robot Arm Current Status</h1>

        <!-- Servo Status Section -->
        <div class="p-6 bg-gray-50 rounded-lg shadow-inner">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Current Servo Status</h2>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-lg font-medium text-gray-700 mb-6">
                <span id="overallStatus">Status: Stopped</span>
                <span id="statusIndicator" class="status-indicator"></span>
            </div>
            <div id="currentServoValues" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4 mt-6 text-center text-gray-600">
                <!-- Current servo values will be displayed here -->
            </div>
            <div class="mt-8">
                <a href="index.php" class="btn btn-gray inline-block">Back to Control Panel</a>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for status page interactivity
        const overallStatusSpan = document.getElementById('overallStatus');
        const statusIndicator = document.getElementById('statusIndicator');
        const currentServoValuesDiv = document.getElementById('currentServoValues');

        // --- API Calls ---
        async function fetchData(action, method = 'GET', data = null) {
            try {
                const url = `api.php?action=${action}`; // Construct URL with action
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                };
                if (data) {
                    options.body = new URLSearchParams(data).toString();
                }
                const response = await fetch(url, options);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                // For get_run_status, response is plain text
                if (action === 'get_run_status') {
                    return await response.text();
                }
                // For other PHP actions, response is JSON
                return await response.json();
            } catch (error) {
                console.error('Fetch error:', error);
                // Display a user-friendly error message
                alert('An error occurred. Please check the console for details and ensure your PHP server is running correctly.');
                return null;
            }
        }

        async function updateServoStatus() {
            const statusText = await fetchData('get_run_status'); // Call with action 'get_run_status'
            if (statusText) {
                // Expected format: "status,sServo1,sServo2,sServo3,sServo4,sServo5,sServo6"
                const parts = statusText.split(',');
                const status = parseInt(parts[0]);
                const servoValues = parts.slice(1).map(s => s.substring(1)); // Remove 's' prefix

                if (status === 1) {
                    overallStatusSpan.textContent = 'Status: Running';
                    statusIndicator.classList.add('running');
                } else {
                    overallStatusSpan.textContent = 'Status: Stopped';
                    statusIndicator.classList.remove('running');
                }

                currentServoValuesDiv.innerHTML = ''; // Clear previous values
                servoValues.forEach((value, index) => {
                    const div = document.createElement('div');
                    div.className = 'p-2 bg-white rounded-md shadow-sm'; /* bg-white will be overridden by style.css */
                    div.innerHTML = `<span class="font-bold">Motor ${index + 1}:</span> ${value}`;
                    currentServoValuesDiv.appendChild(div);
                });
            }
        }

        // Simple custom alert/confirm for better UX than browser defaults (copied from script.js)
        function alert(message) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full text-center">
                    <p class="text-lg font-semibold text-gray-800 mb-4">${message}</p>
                    <button id="alertOkBtn" class="btn btn-blue w-full">OK</button>
                </div>
            `;
            document.body.appendChild(modal);
            document.getElementById('alertOkBtn').onclick = () => document.body.removeChild(modal);
        }

        // Override window.alert (confirm is not needed on this page)
        window.alert = alert;


        // --- Initial Load ---
        document.addEventListener('DOMContentLoaded', () => {
            // Update status every 2 seconds
            setInterval(updateServoStatus, 2000);
            updateServoStatus(); // Initial status update
        });
    </script>
</body>
</html>
