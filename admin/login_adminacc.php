<?php
include '../db.php'; // Include your database connection file
session_start(); // Start a session for user login tracking

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize user inputs
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim($_POST['password']);

    // Error handling
    $errors = [];

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    // If no errors, check user credentials
    if (empty($errors)) {
        $query = "SELECT * FROM admins WHERE email = '$email' LIMIT 1";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                // Regenerate session ID
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['username'] = $admin['username'];
            
                // Redirect to the admin index page
                header("Location: ../admin/index.php");
                exit();
            } else {
                $errors['password'] = 'Incorrect password.';
            }
        } else {
            $errors['email'] = 'Email not found.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MViTour</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Montserrat', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                        url('../assets/loginreg_background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #007bff;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 400;
            display: flex;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
            color: white;
            text-decoration: none;
        }

        .logo img {
            height: 40px;
            margin-right: 10px;
        }

        .auth-container {
            max-width: 500px;
            width: 90%;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-height: 90vh;
            overflow-y: auto;
        }

        .auth-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1.2rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .error-message {
            background-color: #fee;
            color: #e33;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .form-group .password-input {
            padding-right: 45px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-image {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }

        .website-name {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body>

    <header class="header">
        <a class="logo">
            <img src="../assets/mvitour_logo.png" alt="MViTour Logo">
            MViTour
        </a>
    </header>

    <div class="auth-container">
        <h2 class="auth-title">Admin Login</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php 
                    foreach ($errors as $error) {
                        echo $error . '<br>';
                    }
                ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-toggle">
                    <input type="password" id="password" name="password" class="password-input" required>
                    <i class="fas fa-eye" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" class="submit-btn">Login</button>
        </form>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.className = `fas fa-eye${type === 'password' ? '' : '-slash'}`;
        });
    </script>
</body>
</html>