<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];
    $security_code = isset($_POST["security_code"]) ? $_POST["security_code"] : "";

    // Teacher must enter correct security code
    if ($role == "teacher" && $security_code !== "teacher123") {
        $error = "Invalid Security Code for Teacher!";
    } else {
        $sql = "SELECT * FROM users WHERE email='$email' AND role='$role'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["name"] = $user["name"];

                if ($user["role"] == "student") {
                    header("Location: student.php");
                } else {
                    header("Location: teacher.php");
                }
                exit();
            } else {
                $error = "Invalid Password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f6fa; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .form-container { background: #ffffff; padding: 40px 35px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); width: 360px; }
        .form-container h2 { text-align: center; margin-bottom: 25px; font-size: 26px; font-weight: 600; color: #333; }
        .form-container form { display: flex; flex-direction: column; }
        .form-container select, .form-container input[type="email"], .form-container input[type="password"], .form-container input[type="text"] {
            width: 100%; padding: 12px 14px; margin-bottom: 15px; font-size: 16px; border: 1px solid #ccc; border-radius: 8px; transition: all 0.3s ease;
        }
        .form-container input:focus, .form-container select:focus {
            border-color: #007bff; outline: none; box-shadow: 0 0 4px rgba(0, 123, 255, 0.4);
        }
        .form-container button {
            background: #007bff; color: #fff; border: none; padding: 12px; font-size: 16px; border-radius: 8px; cursor: pointer; transition: background 0.3s ease;
        }
        .form-container button:hover { background: #0056b3; }
        .extra-links { margin-top: 18px; text-align: center; }
        .link-btn { color: #007bff; text-decoration: none; font-size: 14px; margin: 0 5px; transition: color 0.3s ease; }
        .link-btn:hover { color: #0056b3; text-decoration: underline; }
        .error { color: red; font-size: 14px; text-align: center; margin-top: 12px; }
        @media (max-width: 420px) { .form-container { width: 90%; padding: 30px 20px; } }
    </style>
    <script>
        function toggleSecurityCode() {
            var role = document.getElementById("role").value;
            var securityField = document.getElementById("security-field");
            securityField.style.display = (role === "teacher") ? "block" : "none";
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <form method="post">
            <select name="role" id="role" onchange="toggleSecurityCode()" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <div id="security-field" style="display:none;">
                <input type="text" name="security_code" placeholder="Enter Security Code (Teachers only)">
            </div>
            <button type="submit">Login</button>
        </form>

        <div class="extra-links">
            <a href="index.php" class="link-btn">Create Account</a> | 
            <a href="forgot_password.php" class="link-btn">Forgot Password?</a>
        </div>

        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    </div>
</body>
</html>
