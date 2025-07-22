// JavaScript for interactivity
const numServos = 6;
const slidersContainer = document.getElementById('servo-sliders');
const posesTableBody = document.getElementById('posesTableBody');
const resetBtn = document.getElementById('resetBtn');
const savePoseBtn = document.getElementById('savePoseBtn');
const runBtn = document.getElementById('runBtn');

// Removed: overallStatusSpan, statusIndicator, currentServoValuesDiv

let sliders = [];
let values = [];

// --- Initialization ---
function initializeSliders() {
    for (let i = 1; i <= numServos; i++) {
        const servoDiv = document.createElement('div');
        servoDiv.className = 'flex items-center mb-4';
        servoDiv.innerHTML = `
            <label for="motor${i}" class="w-24 text-gray-700 font-medium">Motor ${i}:</label>
            <input type="range" id="motor${i}" min="0" max="180" value="90" class="flex-grow">
            <span id="motor${i}Value" class="w-12 text-right font-semibold text-gray-800">90</span>
        `;
        slidersContainer.appendChild(servoDiv);

        const slider = document.getElementById(`motor${i}`);
        const valueSpan = document.getElementById(`motor${i}Value`);

        slider.addEventListener('input', () => {
            valueSpan.textContent = slider.value;
        });
        sliders.push(slider);
        values.push(valueSpan);
    }
}

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

async function getPoses() {
    const poses = await fetchData('get_poses'); // Call with action 'get_poses'
    if (poses) {
        posesTableBody.innerHTML = ''; // Clear existing rows
        if (poses.length === 0) {
            posesTableBody.innerHTML = `<tr><td colspan="${numServos + 2}" class="text-center py-4 text-gray-500">No poses saved yet.</td></tr>`;
            return;
        }
        poses.forEach((pose, index) => {
            const row = posesTableBody.insertRow();
            row.className = 'hover:bg-gray-50';
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${pose.id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${pose.servo1}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${pose.servo2}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${pose.servo3}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${pose.servo4}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${pose.servo5}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${pose.servo6}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                    <button class="btn btn-green btn-sm load-pose-btn" data-id="${pose.id}">Load</button>
                    <button class="btn btn-red btn-sm remove-pose-btn" data-id="${pose.id}">Remove</button>
                </td>
            `;
        });
        attachPoseButtonListeners();
    }
}

function attachPoseButtonListeners() {
    document.querySelectorAll('.load-pose-btn').forEach(button => {
        button.onclick = (event) => loadPose(event.target.dataset.id);
    });
    document.querySelectorAll('.remove-pose-btn').forEach(button => {
        button.onclick = (event) => removePose(event.target.dataset.id);
    });
}

async function savePose() {
    const poseData = {};
    sliders.forEach((slider, index) => {
        poseData[`servo${index + 1}`] = slider.value;
    });

    const result = await fetchData('save_pose', 'POST', poseData); // Call with action 'save_pose'
    if (result && result.status === 'success') {
        alert('Pose saved successfully!');
        getPoses(); // Refresh table
    } else if (result) {
        alert('Error saving pose: ' + result.message);
    }
}

async function loadPose(id) {
    const result = await fetchData('load_pose', 'POST', { id: id }); // Call with action 'load_pose'
    if (result && result.status === 'success') {
        alert('Pose loaded successfully!');
        // Update sliders with loaded values
        for (let i = 1; i <= numServos; i++) {
            const servoKey = `servo${i}`;
            if (result.pose[servoKey] !== undefined) {
                sliders[i - 1].value = result.pose[servoKey];
                values[i - 1].textContent = result.pose[servoKey];
            }
        }
        // No longer calling updateServoStatus here, as it's on a separate page
    } else if (result) {
        alert('Error loading pose: ' + result.message);
    }
}

async function removePose(id) {
    if (!confirm('Are you sure you want to remove this pose?')) {
        return;
    }
    const result = await fetchData('remove_pose', 'POST', { id: id }); // Call with action 'remove_pose'
    if (result && result.status === 'success') {
        alert('Pose removed successfully!');
        getPoses(); // Refresh table
    } else if (result) {
        alert('Error removing pose: ' + result.message);
    }
}

async function runPose() {
    const poseData = {};
    sliders.forEach((slider, index) => {
        poseData[`servo${index + 1}`] = slider.value;
    });

    const result = await fetchData('run_pose', 'POST', poseData); // Call with action 'run_pose'
    if (result && result.status === 'success') {
        alert('Robot arm is running the pose!');
        // No longer calling updateServoStatus here, as it's on a separate page
    } else if (result) {
        alert('Error running pose: ' + result.message);
    }
}

// Removed: updateServoStatus function

// --- Event Listeners ---
resetBtn.addEventListener('click', () => {
    sliders.forEach((slider, index) => {
        slider.value = 90; // Default value
        values[index].textContent = 90;
    });
    // Optionally, update the run status to stopped when reset
    fetchData('update_status', 'POST', { status: 0 }); // Call with action 'update_status'
    // No longer calling updateServoStatus here, as it's on a separate page
});

savePoseBtn.addEventListener('click', savePose);
runBtn.addEventListener('click', runPose);

// --- Initial Load ---
document.addEventListener('DOMContentLoaded', () => {
    initializeSliders();
    getPoses();
    // Removed: setInterval(updateServoStatus, 2000); and initial updateServoStatus();
});

// Simple custom alert/confirm for better UX than browser defaults
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

function confirm(message) {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full text-center">
                <p class="text-lg font-semibold text-gray-800 mb-4">${message}</p>
                <div class="flex justify-around gap-4">
                    <button id="confirmYesBtn" class="btn btn-red flex-1">Yes</button>
                    <button id="confirmNoBtn" class="btn btn-gray flex-1">No</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        document.getElementById('confirmYesBtn').onclick = () => {
            document.body.removeChild(modal);
            resolve(true);
        };
        document.getElementById('confirmNoBtn').onclick = () => {
            document.body.removeChild(modal);
            resolve(false);
        };
    });
}

// Override window.alert and window.confirm
window.alert = alert;
window.confirm = confirm;
