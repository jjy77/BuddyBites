<?php
include("../includes/auth_check.php");
requireRole("student");
include("../includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$sql = "SELECT * FROM custom_meals WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include("../includes/header.php"); ?>

<div class="order-page">

    <div class="order-nav">
        <div class="logo">BUDDY BITES</div>

        <div>
            <a href="home.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="custom_meal.php">Custom Meal</a>
            <a class="active" href="custom_history.php">Custom History</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h3 class="order-title">CUSTOM MEAL HISTORY</h3>

    <div class="order-box">
        <h3>🍱 My Custom Meals</h3>

        <?php if ($result->num_rows > 0) { ?>

            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="order-row">
                    <div class="order-icon">🍱</div>

                    <div class="order-info">
                        <b>Meal #<?php echo $row["custom_id"]; ?></b>
                        <p>
                            Base: <?php echo htmlspecialchars($row["base"]); ?> |
                            Protein: <?php echo htmlspecialchars($row["protein"]); ?> |
                            Topping: <?php echo htmlspecialchars($row["topping"]); ?> |
                            Sauce: <?php echo htmlspecialchars($row["sauce"]); ?>
                        </p>
                        <p><?php echo $row["created_at"]; ?></p>
                    </div>

                    <div class="order-price">
                        RM<?php echo number_format($row["total_price"], 2); ?>
                    </div>

                    <a class="order-btn" href="custom_meal.php">Build Again</a>
                </div>
            <?php } ?>

        <?php } else { ?>
            <p class="empty-order">No custom meals found yet.</p>
        <?php } ?>

    </div>

</div>

<?php include("../includes/footer.php"); ?>
