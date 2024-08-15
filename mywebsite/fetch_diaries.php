<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "myweb");

if ($mysqli->connect_error) {
    die("连接失败: " . $mysqli->connect_error);
}

// 查询日记数据，并按照用户名、年份、月份排序
$query = "SELECT * FROM diaries ORDER BY username, YEAR(diary_date), MONTH(diary_date), DAY(diary_date)";
$result = $mysqli->query($query);

$diaries = [];
while ($row = $result->fetch_assoc()) {
    $year = date('Y', strtotime($row['diary_date']));
    $month = date('m', strtotime($row['diary_date']));
    $day = date('d', strtotime($row['diary_date']));
    $diaries[$row['username']][$year][$month][$day][] = $row['content'];
}

$mysqli->close();
?>
