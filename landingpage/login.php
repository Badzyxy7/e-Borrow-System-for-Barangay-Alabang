<?php
session_start();
include "db.php"; // login.php and db.php are in landingpage folder

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['name'] = $row['name'];

        if ($row['role'] == 'admin') {
            header("Location: ../adminside/admin_dashboard.php");
        } elseif ($row['role'] == 'staff') {
            header("Location: ../staffside/staff_dashboard.php");
        } else {
            header("Location: ../clientside/resident_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('../photos/niggapic.jpg') no-repeat center center/cover;
            position: relative;
        }
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(30, 58, 138, 0.75);
            z-index: 0;
        }
        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
        }
        .container h1 {
            font-size: 2rem;
            margin-bottom: 25px;
            font-weight: bold;
            text-shadow: 1px 2px 6px rgba(0,0,0,0.4);
        }
        .card {
            background: white;
            padding: 35px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
            width: 320px;
            margin: 0 auto;
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #1e3a8a;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #1e3a8a;
            border: none;
            color: white;
            font-size: 15px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #152c61;
        }
        .link {
            text-align: center;
            margin-top: 15px;
        }
        .link a {
            color: #1e3a8a;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        .link a:hover {
            text-decoration: underline;
        }
        .back-btn {
            display: block;
            text-align: center;
            margin-top: 15px;
            background: #6c757d;
            color: white;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        .back-btn:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Welcome back Batang Alabang</h1>
    <div class="card">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <div class="link">
            <a href="register.php">Register as Resident</a>
        </div>
        <a href="index.php" class="back-btn">← Back to Home</a>
    </div>
</div>
</body>
</html>
