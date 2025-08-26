<?php
session_start();
include "db.php";

// Allow both students and teachers to access
if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["role"], ["student", "teacher"])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION["name"];
$user_role = $_SESSION["role"];
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

function pick_random_weekdays($start_day, $end_day, $year, $month, $count = 2) {
    $pool = [];
    for ($d = $start_day; $d <= $end_day; $d++) {
        if (checkdate($month, $d, $year)) {
            $weekday = date('w', strtotime("$year-$month-$d"));
            if ($weekday != 0) $pool[] = $d;
        }
    }
    mt_srand($year * 100 + $month + $start_day + $end_day);
    shuffle($pool);
    return array_slice($pool, 0, min($count, count($pool)));
}

function get_second_fourth_saturdays($year, $month) {
    $holidays = [];
    $daysInMonth = date('t', strtotime("$year-$month-01"));
    $saturday_count = 0;
    for ($d = 1; $d <= $daysInMonth; $d++) {
        if (date('w', strtotime("$year-$month-$d")) == 6) {
            $saturday_count++;
            if ($saturday_count == 2 || $saturday_count == 4) {
                $holidays[] = $d;
            }
        }
    }
    return $holidays;
}

function get_random_government_holidays($year, $month, $count = 2) {
    $pool = [];
    $daysInMonth = date('t', strtotime("$year-$month-01"));
    for ($d = 1; $d <= $daysInMonth; $d++) {
        if (date('w', strtotime("$year-$month-$d")) != 0) {
            $pool[] = $d;
        }
    }
    mt_srand($year * 200 + $month);
    shuffle($pool);
    return array_slice($pool, 0, min($count, count($pool)));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Academic Calendar - <?= $year ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #eef2f7; color: #333; line-height: 1.6; }
        
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #2c3e50; color: #fff; padding: 14px 24px; box-shadow: 0 3px 8px rgba(0,0,0,0.15); }
        .navbar h2 { font-size: 22px; margin: 0; }
        .navbar span { font-size: 14px; opacity: 0.8; }
        .navbar a { color: #fff; text-decoration: none; margin-left: 15px; padding: 8px 12px; border-radius: 6px; transition: background 0.3s ease; }
        .navbar a:hover { background: rgba(255,255,255,0.15); }
        .logout-btn { background: #e74c3c; }
        .logout-btn:hover { background: #c0392b; }

        .wrap { width: 95%; max-width: 1200px; margin: 30px auto; }
        .year-select { margin-bottom: 25px; text-align: center; }
        .year-select select { padding: 8px 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; }

        .legend { display: flex; gap: 12px; justify-content: center; margin-bottom: 25px; flex-wrap: wrap; }
        .badge { display: flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #ddd; padding: 8px 12px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 14px; }
        .dot { width: 14px; height: 14px; border-radius: 3px; }
        .dot.sun { background:#ff5252; }
        .dot.test { background:#4caf50; }
        .dot.lab { background:#ffca28; }
        .dot.gov { background:#2196f3; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .month { background: #fff; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.08); overflow: hidden; transition: transform 0.2s ease; }
        .month:hover { transform: translateY(-3px); }
        .month h3 { background: #3498db; color: #fff; padding: 10px; text-align: center; font-size: 18px; }
        
        .cal { width: 100%; border-collapse: collapse; }
        .cal th, .cal td { text-align: center; padding: 10px; font-size: 13px; border: 1px solid #eee; }
        .cal th { background: #f8f9fc; font-weight: 600; }
        .cal td.empty { background: #f5f5f5; color: #aaa; }
        .holiday { background:#ffeaea; color:#c0392b; font-weight: 600; }
        .test { background:#e8f5e9; font-weight: 600; }
        .lab { background:#fff9e6; font-weight: 600; }
        .gov { background:#eaf3fc; font-weight: 600; color:#1565c0; }
        
        .footer-note { margin-top: 15px; font-size: 12px; text-align: center; color: #666; }

        @media (max-width: 600px) {
            .navbar h2 { font-size: 18px; }
            .navbar span { display: block; margin-top: 5px; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Academic Calendar</h2>
        <div>
            <span>Welcome, <?= htmlspecialchars($user_name) ?> (<?= ucfirst($user_role) ?>)</span>
            <a href="<?= $user_role == 'teacher' ? 'teacher_portal.php' : 'student.php' ?>">Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="wrap">
        <form method="get" class="year-select">
            <label for="year">Select Year: </label>
            <select name="year" id="year" onchange="this.form.submit()">
                <?php for ($y = date('Y')-5; $y <= date('Y')+5; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>

        <div class="legend">
            <div class="badge"><span class="dot test"></span> Test (Week 1)</div>
            <div class="badge"><span class="dot lab"></span> Lab (Week 3)</div>
            <div class="badge"><span class="dot gov"></span> Govt. Holiday</div>
            <div class="badge"><span class="dot sun"></span> Sunday & 2nd/4th Saturday</div>
        </div>

        <div class="grid">
        <?php
        for ($month = 1; $month <= 12; $month++) {
            $firstDayTs = strtotime("$year-$month-01");
            $daysInMonth = (int)date('t', $firstDayTs);
            $firstWeekdayIndex = (int)date('w', $firstDayTs);

            $week1_days = pick_random_weekdays(1, 7, $year, $month, 2);
            $week3_days = pick_random_weekdays(15, 21, $year, $month, 2);
            $saturday_holidays = get_second_fourth_saturdays($year, $month);
            $govt_holidays = get_random_government_holidays($year, $month, 2);

            echo '<div class="month">';
            echo '<h3>' . date('F', $firstDayTs) . ' ' . $year . '</h3>';
            echo '<table class="cal">';
            echo '<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>';

            $day = 1;
            echo '<tr>';
            for ($i=0; $i<$firstWeekdayIndex; $i++) echo '<td class="empty"></td>';

            while ($day <= $daysInMonth) {
                $weekday = ($firstWeekdayIndex + ($day - 1)) % 7;
                $class = '';
                if ($weekday === 0 || in_array($day, $saturday_holidays)) $class = 'holiday';
                if (in_array($day, $week1_days)) $class .= ' test';
                if (in_array($day, $week3_days)) $class .= ' lab';
                if (in_array($day, $govt_holidays)) $class .= ' gov';

                echo '<td class="' . trim($class) . '">' . $day . '</td>';

                if ($weekday == 6 && $day != $daysInMonth) echo '</tr><tr>';
                $day++;
            }

            $lastWeekday = ($firstWeekdayIndex + $daysInMonth - 1) % 7;
            for ($j=$lastWeekday+1; $j<=6; $j++) echo '<td class="empty"></td>';

            echo '</tr></table>';
            echo '</div>';
        }
        ?>
        </div>

        <div class="footer-note">Academic Calendar for <?= $year ?> | Leave Portal</div>
    </div>
</body>
</html>
