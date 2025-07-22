# Robot-Arm-Control-Panel-
This project provides a web-based interface to control a robot arm, save and load specific joint positions (poses), and monitor its real-time status. It's designed for ease of use and features a clean, modern light theme.

# Features
- Servo Control: Individual sliders for controlling 6 robot arm servos (motors) from 0 to 180 degrees.

- Save Poses: Store current servo positions as named poses in a MySQL database.

- Load Poses: Retrieve and apply previously saved poses to the robot arm.

- Remove Poses: Delete unwanted poses from the database.

- Run Pose: Send the current slider values to the robot arm for execution.

- Real-time Status Monitoring: A dedicated "Robot Arm Status" page displays the current servo values and the overall running status (Running/Stopped) in real-time.

- Responsive Design: Optimized for various screen sizes using Tailwind CSS.

- Custom Modals: User-friendly alert and confirmation dialogs.

# Technologies Used
Backend:

PHP: For handling API requests and database interactions.

MySQL: Database to store saved poses and the robot's current run state.

Frontend:

HTML5: Structure of the web pages.

CSS3 (Tailwind CSS): Styling and responsive design.

JavaScript: Client-side interactivity and API calls.

# Screenshots

<img width="2123" height="1192" alt="Screenshot 2025-07-22 140659" src="https://github.com/user-attachments/assets/427867c7-d00c-4b29-8b67-5c5fa1ff548c" />

<img width="2131" height="1191" alt="Screenshot 2025-07-22 140715" src="https://github.com/user-attachments/assets/841cdc0f-9c17-4f39-aa83-990f18afeb79" />

# Demo Video

https://github.com/user-attachments/assets/90617b42-1e1d-4d7e-9e15-8104183d0bb5


