<?php
include("../includes/auth_check.php");
requireRole("restaurant");
include("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: restaurant_dashboard.php");
    exit();
}

$order_id = (int)($_POST["order_id"] ?? 0);
$status = trim($_POST["status"] ?? "");
$allowed_statuses = ["Ready for Pickup", "Rejected"];

if ($order_id <= 0 || !in_array($status, $allowed_statuses, true)) {
    header("Location: restaurant_dashboard.php");
    exit();
}

$stmt = $conn->prepare("
    UPDATE orders
    SET status = ?
    WHERE order_id = ?
");
$stmt->bind_param("si", $status, $order_id);
$stmt->execute();

header("Location: restaurant_dashboard.php");
exit();
