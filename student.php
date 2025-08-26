<?php
// =========================
// FILE: student.php
// =========================
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["user_id"];
$student_name = $_SESSION["name"];

// ✅ Fetch student details including 'year'
$user_query = $conn->query("SELECT id, name, email, phone, age, year FROM users WHERE id='" . $conn->real_escape_string($student_id) . "'");
$user = $user_query->fetch_assoc();

// ✅ Handle leave request form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["apply_leave"])) {
    $start_date = $conn->real_escape_string($_POST["start_date"]);
    $end_date   = $conn->real_escape_string($_POST["end_date"]);
    $start_time = $conn->real_escape_string($_POST["start_time"]);
    $end_time   = $conn->real_escape_string($_POST["end_time"]);
    $reason     = $conn->real_escape_string($_POST["reason"]);

    $sql = "INSERT INTO leave_requests (student_id, start_date, end_date, start_time, end_time, reason)
            VALUES ('$student_id','$start_date','$end_date','$start_time','$end_time','$reason')";
    $conn->query($sql);
}

// ✅ Handle profile update form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    $phone = $conn->real_escape_string($_POST["phone"]);
    $age   = (int) $_POST["age"];

    $update_sql = "UPDATE users SET phone='$phone', age='$age' WHERE id='$student_id'";
    $conn->query($update_sql);
    header("Location: student.php"); // Refresh to see updated details
    exit();
}

$result = $conn->query("SELECT * FROM leave_requests WHERE student_id='" . $conn->real_escape_string($student_id) . "' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Portal</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reset & Base */
        html, body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #eef1f7;
            color: #333;
        }
        * { box-sizing: border-box; }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(1000deg, #1961ba, #0f3d91);
            padding: 14px 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .navbar h2 {
            font-size: 22px;
            font-weight: 600;
            color: white;
            margin: 0;
        }
        .links {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .links span {
            font-size: 14px;
            font-weight: 500;
            color: #e0e0e0;
            margin-right: 10px;
        }
        .links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            background: rgba(255,255,255,0.15);
            padding: 8px 14px;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .links a:hover { background: rgba(255,255,255,0.3); }
        .logout-btn:hover { background: #d32f2f !important; }

        /* Container Layout */
        .container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
        }

        /* Profile Card */
        .profile-container {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease-in-out;
            max-height: 450px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        .profile-container h2 {
            font-size: 20px;
            font-weight: 600;
            color: #1961ba;
            margin-bottom: 15px;
            border-bottom: 2px solid #eee;
            padding-bottom: 6px;
        }
        .profile-container p {
            margin: 8px 0;
            font-size: 14px;
        }
        .profile-container form {
            display: flex;
            flex-direction: column;
        }

        /* Forms */
        label {
            display: block;
            font-weight: 500;
            margin: 10px 0 6px;
            color: #444;
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            margin-bottom: 12px;
            transition: border 0.3s;
        }
        input:focus, textarea:focus {
            border-color: #1961ba;
            outline: none;
        }
        button {
            background: #1961ba;
            color: white;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover { background: #0f3d91; }

        /* Leave Application Card */
        .form-container {
            background: #fff;
            width: 550px;
            padding: 25px;
            border-radius: 14px;
            margin-left: 150px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            animation: fadeIn 0.7s ease-in-out;
        }
        .form-container h2 {
            font-size: 20px;
            font-weight: 600;
            color: #1961ba;
            margin-bottom: 18px;
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
        }
        .form-container h3 {
            margin-top: 30px;
            font-size: 18px;
            color: #333;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            background: #1961ba;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) td { background: #f8f9fb; }
        tr:hover td { background: #eef4ff; }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Leave Portal</h2>
        <div class="links">
            <span>Welcome, <?= htmlspecialchars($student_name) ?> (Student)</span>
            <a href="academic_calendar.php">Academic Calendar</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="profile-container">
            <h2>Profile</h2>
            <form method="post">
                <input type="hidden" name="update_profile" value="1">
                <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Year:</strong> <?= htmlspecialchars($user['year'] ?? 'Not Set') ?></p>
                <label>Phone:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                <label>Age:</label>
                <input type="number" name="age" value="<?= htmlspecialchars($user['age'] ?? '') ?>" required>
                <button type="submit">Update Profile</button>
            </form>
        </div>

        <div class="form-container">
            <h2>Apply for Leave</h2>
            <form method="post">
                <input type="hidden" name="apply_leave" value="1">
                <label>Start Date:</label>
                <input type="date" name="start_date" required>
                <label>End Date:</label>
                <input type="date" name="end_date" required>
                <label>Start Time:</label>
                <input type="time" name="start_time" required>
                <label>End Time:</label>
                <input type="time" name="end_time" required>
                <label>Reason:</label>
                <textarea name="reason" placeholder="Enter reason..." required></textarea>
                <button type="submit">Apply</button>
            </form>

            <h3>Your Leave Requests</h3>
            <table>
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
                <?php while($row = $result->fetch_assoc()) { 
                    $status = strtolower($row['status']);
                    $status_color = "#f1c40f";
                    if ($status == "accepted" || $status == "approved") {
                        $status_color = "#2ecc71";
                    } elseif ($status == "rejected" || $status == "declined" || $status == "denied" || $status == "not accepted") {
                        $status_color = "#e74c3c";
                    }
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['start_date']." ".$row['start_time']) ?></td>
                    <td><?= htmlspecialchars($row['end_date']." ".$row['end_time']) ?></td>
                    <td><?= htmlspecialchars($row['reason']) ?></td>
                    <td style="font-weight:bold; color:white; background:<?= $status_color ?>;">
                        <?= htmlspecialchars(ucfirst($row['status'])) ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>
</html>
