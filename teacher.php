<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "teacher") {
    header("Location: login.php");
    exit();
}
$teacher_name = $_SESSION["name"];

// Approve/Decline Leave Requests
if (isset($_GET["action"]) && isset($_GET["id"])) {
    $id = $_GET["id"];
    $action = $_GET["action"];
    if ($action == "approve") {
        $conn->query("UPDATE leave_requests SET status='Approved' WHERE id=$id");
    } elseif ($action == "decline") {
        $conn->query("UPDATE leave_requests SET status='Declined' WHERE id=$id");
    }
}

// Fetch Leave Requests
$result = $conn->query("SELECT leave_requests.*, users.name 
                        FROM leave_requests 
                        JOIN users ON leave_requests.student_id=users.id 
                        ORDER BY id DESC");

// Count Total Students
$student_count_query = $conn->query("SELECT COUNT(*) as total_students FROM users WHERE role='student'");
$student_count = $student_count_query->fetch_assoc()['total_students'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Portal</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

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
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            color: white;
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .nav-right span {
            font-size: 14px;
            color: #e0e0e0;
            margin-right: 10px;
        }
        .nav-right a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            background: rgba(255,255,255,0.15);
            padding: 8px 14px;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .nav-right a:hover { background: rgba(255,255,255,0.3); }
        .logout-btn:hover { background: #d32f2f !important; }

        /* Content */
        .container {
            width: 90%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.05);
        }
        h2 {
            margin-bottom: 15px;
            font-size: 20px;
            font-weight: 600;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.05);
        }
        table th, table td {
            text-align: left;
            padding: 12px 15px;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #007bff;
            color: white;
            font-weight: 600;
        }
        table tr:hover {
            background: #f9f9f9;
        }

        /* Status Colors */
        .status-approved { color: green; font-weight: bold; }
        .status-declined { color: red; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }

        /* Buttons */
        .approve-btn, .decline-btn {
            text-decoration: none;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 13px;
        }
        .approve-btn { background: #28a745; color: white; }
        .approve-btn:hover { background: #218838; }
        .decline-btn { background: #dc3545; color: white; }
        .decline-btn:hover { background: #b02a37; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Leave Portal</h2>
        <div class="nav-right">
            <span>Welcome, <?= htmlspecialchars($teacher_name) ?> (Teacher)</span>
            <a href="teacher_academic_calendar.php">Academic Calendar</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Total Students: <?= $student_count ?></h2>
        <h2>Leave Requests</h2>
        <table>
            <tr>
                <th>Student</th>
                <th>From</th>
                <th>To</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['start_date']." ".$row['start_time']) ?></td>
                <td><?= htmlspecialchars($row['end_date']." ".$row['end_time']) ?></td>
                <td><?= htmlspecialchars($row['reason']) ?></td>
                <td class="status-<?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <?php if($row['status']=="Pending"){ ?>
                        <a href="?action=approve&id=<?= $row['id'] ?>" class="approve-btn">Approve</a>
                        <a href="?action=decline&id=<?= $row['id'] ?>" class="decline-btn">Decline</a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
