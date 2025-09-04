<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 50px; }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 400px;
            margin: auto;
            text-align: center;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="card">
    <h2>Welcome, Staff <?php echo $_SESSION['name']; ?>!</h2>
    <p>This is your staff dashboard.</p>
    <a href="logout.php">Logout</a>
</div>
</body>
</html>
