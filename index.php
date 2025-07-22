<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Robot Arm Control Panel</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="antialiased">
    <div class="container">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Robot Arm Control Panel</h1>

        <!-- Servo Control Section -->
        <div class="mb-10 p-6 bg-gray-50 rounded-lg shadow-inner">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Motor Control</h2>
            <div id="servo-sliders">
                <!-- Sliders will be dynamically generated here by JS -->
            </div>
            <div class="flex flex-wrap gap-4 mt-8 justify-center">
                <button id="resetBtn" class="btn btn-gray">Reset</button>
                <button id="savePoseBtn" class="btn btn-blue">Save Pose</button>
                <button id="runBtn" class="btn btn-green">Run</button>
            </div>
            <div class="mt-8 text-center">
                <a href="status.php" class="btn btn-gray inline-block">View Robot Status</a>
            </div>
        </div>

        <!-- Saved Poses Table Section -->
        <div class="p-6 bg-gray-50 rounded-lg shadow-inner">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Saved Poses</h2>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">#</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motor 1</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motor 2</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motor 3</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motor 4</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motor 5</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motor 6</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">Action</th>
                        </tr>
                    </thead>
                    <tbody id="posesTableBody" class="divide-y divide-gray-200">
                        <!-- Poses will be loaded here by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Link to external JavaScript file -->
    <script src="script.js"></script>
</body>
</html>
