<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];
    $year = isset($_POST["year"]) ? $_POST["year"] : NULL;

    // check if email exists
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        $sql = "INSERT INTO users (name,email,password,role,year) VALUES ('$name','$email','$password','$role','$year')";
        if ($conn->query($sql)) {
            $success = "Account created! You can now <a href='login.php'>Login</a>";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background: #ffffff;
            padding: 40px 35px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 360px;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 26px;
            font-weight: 600;
            color: #333;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
        }

        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="password"],
        .form-container select {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-container input:focus,
        .form-container select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 4px rgba(0, 123, 255, 0.4);
        }

        .form-container button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .form-container button:hover {
            background: #0056b3;
        }

        .form-container p {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .form-container p a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .form-container p a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .error {
            color: red;
            font-size: 14px;
            text-align: center;
            margin-top: 12px;
        }

        .success {
            color: green;
            font-size: 14px;
            text-align: center;
            margin-top: 12px;
        }

        #yearField {
            display: none;
        }

        @media (max-width: 420px) {
            .form-container {
                width: 90%;
                padding: 30px 20px;
            }
        }
    </style>
    <script>
        function toggleYearField(role) {
            document.getElementById('yearField').style.display = (role === 'student') ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Create Account</h2>
        <form method="post">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required onchange="toggleYearField(this.value)">
                <option value="">-- Select Role --</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>

            <select name="year" id="yearField">
                <option value="">-- Select Year --</option>
                <option value="I">1st Year</option>
                <option value="II">2nd Year</option>
                <option value="III">3rd Year</option>
                <option value="IV">4th Year</option>
            </select>

            <button type="submit">Signup</button>
        </form>

        <?php 
            if(isset($error)) echo "<p class='error'>$error</p>"; 
            if(isset($success)) echo "<p class='success'>$success</p>"; 
        ?>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
