<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            /* General Styling */
            body {
                font-family: 'Arial', sans-serif;
                background: linear-gradient(to right, #74ebd5, #acb6e5);
            }

            /* Center the login container */
            .container {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }

            /* Card Styling */
            .card {
                background: #ffffff;
                border-radius: 15px;
                padding: 25px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                transition: transform 0.3s ease-in-out;
            }

            .card:hover {
                transform: scale(1.03);
            }

            /* Heading */
            h3 {
                font-weight: bold;
                color: #333;
            }

            /* Form Inputs */
            .form-control {
                border-radius: 10px;
                border: 1px solid #ddd;
                padding: 12px;
                font-size: 16px;
            }

            /* Button Styling */
            .btn-primary {
                background-color: #007bff;
                border: none;
                border-radius: 10px;
                padding: 12px;
                font-size: 18px;
                font-weight: bold;
                transition: background-color 0.3s ease-in-out;
            }

            .btn-primary:hover {
                background-color: #0056b3;
            }

            /* Error Message */
            .alert {
                border-radius: 10px;
                font-size: 14px;
                text-align: center;
            }

            /* Mobile Responsive */
            @media (max-width: 576px) {
                .card {
                    width: 90%;
                }
            }

            /* Password Field Container */
            .password-container {
                position: relative;
                display: flex;
                align-items: center;
            }

            /* Eye Icon */
            .toggle-password {
                position: absolute;
                right: 15px;
                cursor: pointer;
                color: #aaa;
                font-size: 18px;
                transition: color 0.3s;
            }

            .toggle-password:hover {
                color: #007bff;
            }

        </style>
    </head>
    <body class="bg-light">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
                <h3 class="text-center mb-4">Login</h3>

                <!-- Display Error Message -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error']); ?>
                    </div>
                    <?php unset($_SESSION['error']); // Clear error after displaying ?>
                <?php endif; ?>

                <form action="/AutomatedTuitionCentreAttendanceSystem/processLogin.php" method="POST">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="userID" placeholder="User ID" required>
                    </div>
                    <div class="mb-3 password-container">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <span class="toggle-password">
                            <i class="fa fa-eye" id="eyeIcon"></i>
                        </span>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <script>
                    // Password Toggle Functionality
                    document.getElementById("eyeIcon").addEventListener("click", function () {
                        let passwordField = document.getElementById("password");
                        if (passwordField.type === "password") {
                            passwordField.type = "text";
                            this.classList.replace("fa-eye", "fa-eye-slash");
                        } else {
                            passwordField.type = "password";
                            this.classList.replace("fa-eye-slash", "fa-eye");
                        }
                    });
                </script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>


            </div>
        </div>
    </body>
</html>
