<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST["code"];
    $new_password = $_POST["new_password"];
    $email = isset($_SESSION["reset_email"]) ? $_SESSION["reset_email"] : null;
    $real_code = isset($_SESSION["reset_code"]) ? $_SESSION["reset_code"] : null;

    if ($email === null || $real_code === null) {
        $error = "Session expired or invalid request. Please request a new reset link.";
    } else {
        if ($entered_code == $real_code) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE email='$email'");
            $success = "Password reset successful! <a href='login.php'>Login here</a>";

            unset($_SESSION["reset_code"]);
            unset($_SESSION["reset_email"]);
        } else {
            $error = "Invalid verification code.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        /* Base */
        * { box-sizing: border-box; }
        html,body { height:100%; margin:0; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #f4f7fb; color: #333; }

        /* Centering container */
        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* Card */
        .card {
            width: 420px;
            max-width: 96%;
            background: #ffffff;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 8px 30px rgba(30, 41, 59, 0.08);
            border: 1px solid rgba(15,23,42,0.04);
        }

        .card h2 {
            margin: 0 0 18px 0;
            font-size: 20px;
            color: #111827;
            text-align: left;
            letter-spacing: 0.2px;
        }

        /* Form groups (ensures perfect alignment) */
        .form-group {
            margin-bottom: 14px;
            display: block;
        }

        label {
            display:block;
            margin-bottom: 6px;
            font-size: 13px;
            color: #374151;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            font-size: 15px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            transition: box-shadow .18s ease, border-color .18s ease;
        }

        input::placeholder { color: #9ca3af; }

        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 6px 20px rgba(37,99,235,0.12);
        }

        /* Primary button */
        .btn {
            display:inline-block;
            width:100%;
            padding: 12px 14px;
            background: linear-gradient(180deg,#2563eb,#1e40af);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(37,99,235,0.16);
            transition: transform .08s ease, box-shadow .08s ease, opacity .12s ease;
        }

        .btn:active { transform: translateY(1px); }
        .btn:hover { opacity: .97; }

        /* Messages */
        .msg { margin-top: 14px; font-size: 14px; }
        .msg.error { color: #b91c1c; background: #fff1f2; padding:10px 12px; border-radius:8px; border:1px solid rgba(185,28,28,0.06); }
        .msg.success { color: #065f46; background: #ecfdf5; padding:10px 12px; border-radius:8px; border:1px solid rgba(6,95,70,0.06); }

        /* Helper row for smaller notes/links */
        .meta { margin-top: 12px; font-size: 13px; color: #6b7280; text-align: center; }

        @media (max-width: 420px) {
            .card { padding: 20px; }
            .btn { padding: 12px; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="card" role="main" aria-labelledby="reset-heading">
            <h2 id="reset-heading">Reset Password</h2>

            <?php if (!empty($error)): ?>
                <div class="msg error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="msg success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (empty($success)): /* show form only when not successful */ ?>
            <form method="post" autocomplete="off" novalidate>
                <div class="form-group">
                    <label for="code">Verification Code</label>
                    <input id="code" name="code" type="text" required placeholder="Enter the code sent to your email" inputmode="numeric" autocomplete="one-time-code" />
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input id="new_password" name="new_password" type="password" required placeholder="Choose a strong password" autocomplete="new-password" />
                </div>

                <button type="submit" class="btn">Reset Password</button>
            </form>
            <?php endif; ?>

            <div class="meta">
                If you didn't receive a code, request a new reset from the login page.
            </div>
        </div>
    </div>
</body>
</html>
