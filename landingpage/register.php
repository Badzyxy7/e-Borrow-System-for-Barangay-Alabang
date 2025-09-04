<?php
include "db.php";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = md5($_POST['password']); 
    $role = "resident"; 

    // validate if email is gmail
    if (!preg_match("/@gmail\.com$/", $email)) {
        $error = "Only Gmail accounts are allowed.";
    } else {
        // check if name or email already exists
        $check = "SELECT * FROM users WHERE name='$name' OR email='$email' LIMIT 1";
        $result = $conn->query($check);

        if ($result && $result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            if ($existing['name'] === $name) {
                $error = "Name is already taken!";
            } elseif ($existing['email'] === $email) {
                $error = "Email is already registered!";
            }
        } else {
            // insert new record
            $sql = "INSERT INTO users (name, email, password, role) 
                    VALUES ('$name', '$email', '$password', '$role')";

            if ($conn->query($sql) === TRUE) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
            width: 350px;
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
    </style>
</head>
<body>
<div class="container">
    <h1>Create your Resident Account</h1>
    <div class="card">
        <h2>Register</h2>
        <?php if (!empty($error)) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email (Gmail only)" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Register</button>
        </form>
        <div class="link">
            <a href="login.php">← Back to Login</a>
        </div>
        
        </div>
    </div>
</div>
</body>
</html>
