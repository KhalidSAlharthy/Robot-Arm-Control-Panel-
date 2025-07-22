<?php
// api.php
// This consolidated script handles all API requests for the Robot Arm Control Panel.

include 'db_connect.php'; // Include the database connection file

header('Content-Type: application/json'); // Default to JSON response

// Function to set the status of all currently active 'run' entries to 0
function stopAllPreviousRuns($conn) {
    // Set status of all entries that are currently 1 to 0.
    // This ensures only the newly inserted run will have status 1.
    $sql_update_prev = "UPDATE run SET status = 0 WHERE status = 1";
    $conn->query($sql_update_prev); // Execute the update
}

// Determine the action based on the 'action' GET parameter
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'save_pose':
            // Saves the current servo positions as a new pose in the 'pose' table.
            if (isset($_POST['servo1'], $_POST['servo2'], $_POST['servo3'], $_POST['servo4'], $_POST['servo5'], $_POST['servo6'])) {
                $servo1 = filter_var($_POST['servo1'], FILTER_SANITIZE_NUMBER_INT);
                $servo2 = filter_var($_POST['servo2'], FILTER_SANITIZE_NUMBER_INT);
                $servo3 = filter_var($_POST['servo3'], FILTER_SANITIZE_NUMBER_INT);
                $servo4 = filter_var($_POST['servo4'], FILTER_SANITIZE_NUMBER_INT);
                $servo5 = filter_var($_POST['servo5'], FILTER_SANITIZE_NUMBER_INT);
                $servo6 = filter_var($_POST['servo6'], FILTER_SANITIZE_NUMBER_INT);

                $sql = "INSERT INTO pose (servo1, servo2, servo3, servo4, servo5, servo6) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    echo json_encode(["status" => "error", "message" => "SQL prepare error: " . $conn->error]);
                    break;
                }
                $stmt->bind_param("iiiiii", $servo1, $servo2, $servo3, $servo4, $servo5, $servo6);

                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Pose saved successfully"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error saving pose: " . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Missing servo parameters"]);
            }
            break;

        case 'get_poses':
            // Fetches all saved poses from the 'pose' table.
            $sql = "SELECT id, servo1, servo2, servo3, servo4, servo5, servo6 FROM pose ORDER BY id DESC";
            $result = $conn->query($sql);
            $poses = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $poses[] = $row;
                }
            }
            echo json_encode($poses);
            break;

        case 'load_pose':
            // Loads a specific pose from the 'pose' table and updates the 'run' table.
            if (isset($_POST['id'])) {
                $pose_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

                $sql_select = "SELECT servo1, servo2, servo3, servo4, servo5, servo6 FROM pose WHERE id = ?";
                $stmt_select = $conn->prepare($sql_select);
                if ($stmt_select === false) {
                    echo json_encode(["status" => "error", "message" => "SQL select prepare error: " . $conn->error]);
                    break;
                }
                $stmt_select->bind_param("i", $pose_id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();

                if ($result->num_rows > 0) {
                    $pose_data = $result->fetch_assoc();
                    $servo1 = $pose_data['servo1'];
                    $servo2 = $pose_data['servo2'];
                    $servo3 = $pose_data['servo3'];
                    $servo4 = $pose_data['servo4'];
                    $servo5 = $pose_data['servo5'];
                    $servo6 = $pose_data['servo6'];

                    // Stop all previously running poses
                    stopAllPreviousRuns($conn);

                    // Insert the new pose into the 'run' table with status 1
                    $sql_insert_run = "INSERT INTO run (servo1, servo2, servo3, servo4, servo5, servo6, status) VALUES (?, ?, ?, ?, ?, ?, 1)";
                    $stmt_run = $conn->prepare($sql_insert_run);
                    if ($stmt_run === false) {
                        echo json_encode(["status" => "error", "message" => "SQL insert run prepare error: " . $conn->error]);
                        $stmt_select->close();
                        break;
                    }
                    $stmt_run->bind_param("iiiiii", $servo1, $servo2, $servo3, $servo4, $servo5, $servo6);

                    if ($stmt_run->execute()) {
                        echo json_encode(["status" => "success", "message" => "Pose loaded and run table updated successfully", "pose" => $pose_data]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Error inserting into run table: " . $stmt_run->error]);
                    }

                    if ($stmt_run) {
                        $stmt_run->close();
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Pose not found"]);
                }
                $stmt_select->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Missing pose ID"]);
            }
            break;

        case 'remove_pose':
            // Removes a specific pose from the 'pose' table.
            if (isset($_POST['id'])) {
                $pose_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

                $sql = "DELETE FROM pose WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    echo json_encode(["status" => "error", "message" => "SQL prepare error: " . $conn->error]);
                    break;
                }
                $stmt->bind_param("i", $pose_id);

                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Pose removed successfully"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error removing pose: " . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Missing pose ID"]);
            }
            break;

        case 'run_pose':
            // Updates the 'run' table with the current servo values and sets the status to 1 (running).
            if (isset($_POST['servo1'], $_POST['servo2'], $_POST['servo3'], $_POST['servo4'], $_POST['servo5'], $_POST['servo6'])) {
                $servo1 = filter_var($_POST['servo1'], FILTER_SANITIZE_NUMBER_INT);
                $servo2 = filter_var($_POST['servo2'], FILTER_SANITIZE_NUMBER_INT);
                $servo3 = filter_var($_POST['servo3'], FILTER_SANITIZE_NUMBER_INT);
                $servo4 = filter_var($_POST['servo4'], FILTER_SANITIZE_NUMBER_INT);
                $servo5 = filter_var($_POST['servo5'], FILTER_SANITIZE_NUMBER_INT);
                $servo6 = filter_var($_POST['servo6'], FILTER_SANITIZE_NUMBER_INT);

                // Stop all previously running poses
                stopAllPreviousRuns($conn);

                // Insert the new pose into the 'run' table with status 1
                $sql = "INSERT INTO run (servo1, servo2, servo3, servo4, servo5, servo6, status) VALUES (?, ?, ?, ?, ?, ?, 1)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    echo json_encode(["status" => "error", "message" => "SQL insert prepare error: " . $conn->error]);
                    break;
                }
                $stmt->bind_param("iiiiii", $servo1, $servo2, $servo3, $servo4, $servo5, $servo6);

                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Run pose updated successfully"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error inserting run pose: " . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Missing servo parameters"]);
            }
            break;

        case 'get_run_status':
            // Fetches the current running pose and its status from the 'run' table.
            // It now fetches the latest entry based on timestamp.
            header('Content-Type: text/plain'); // Override to plain text for this specific action
            $sql = "SELECT servo1, servo2, servo3, servo4, servo5, servo6, status FROM run ORDER BY timestamp DESC LIMIT 1";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo $row['status'] . ",s" . $row['servo1'] . ",s" . $row['servo2'] . ",s" . $row['servo3'] . ",s" . $row['servo4'] . ",s" . $row['servo5'] . ",s" . $row['servo6'];
            } else {
                // If no record exists, output default values (stopped)
                echo "0,s90,s90,s90,s90,s90,s90";
            }
            break;

        case 'update_status':
            // Updates the status of the LATEST 'run' entry.
            if (isset($_POST['status'])) {
                $new_status = filter_var($_POST['status'], FILTER_SANITIZE_NUMBER_INT);

                // Check if any record exists in the 'run' table
                $sql_check = "SELECT COUNT(*) FROM run";
                $result_check = $conn->query($sql_check);
                $row_check = $result_check->fetch_row();
                $record_exists = $row_check[0] > 0;

                if ($record_exists) {
                    // Update the status of the most recent entry
                    $sql = "UPDATE run SET status = ? ORDER BY timestamp DESC LIMIT 1";
                    $stmt = $conn->prepare($sql);
                    if ($stmt === false) {
                        echo json_encode(["status" => "error", "message" => "SQL update prepare error: " . $conn->error]);
                        break;
                    }
                    $stmt->bind_param("i", $new_status);

                    if ($stmt->execute()) {
                        echo json_encode(["status" => "success", "message" => "Status updated successfully"]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Error updating status: " . $stmt->error]);
                    }
                    $stmt->close();
                } else {
                    // If no record exists, insert a default row with the new status
                    $sql = "INSERT INTO run (servo1, servo2, servo3, servo4, servo5, servo6, status) VALUES (90, 90, 90, 90, 90, 90, ?)";
                    $stmt = $conn->prepare($sql);
                    if ($stmt === false) {
                        echo json_encode(["status" => "error", "message" => "SQL insert prepare error: " . $conn->error]);
                        break;
                    }
                    $stmt->bind_param("i", $new_status);
                    if ($stmt->execute()) {
                        echo json_encode(["status" => "success", "message" => "Default status inserted successfully"]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Error inserting default status: " . $stmt->error]);
                    }
                    $stmt->close();
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Missing status parameter"]);
            }
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Invalid action"]);
            break;
    }
} else {
    echo json_encode(["status" => "error", "message" => "No action specified"]);
}

$conn->close(); // Close the database connection
?>
