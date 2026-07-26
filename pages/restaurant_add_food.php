<?php
include("../includes/auth_check.php");
requireRole("restaurant");
include("../includes/db.php");

$restaurant_name = $_SESSION["full_name"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $item_name = trim($_POST["item_name"]);
    $category = $_POST["category"];
    $price = $_POST["price"];
    $image = trim($_POST["image"]);

    if ($item_name != "" && $price != "" && $image != "") {

        $sql = "INSERT INTO menu_items
                (item_name, restaurant_name, category, price, image, availability)
                VALUES (?, ?, ?, ?, ?, 'Available')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssds",
            $item_name,
            $restaurant_name,
            $category,
            $price,
            $image
        );

        $stmt->execute();

        header("Location: restaurant_menu.php");
        exit();
    }

    $error = "Please complete all fields.";
}
?>

<?php include("../includes/header.php"); ?>

<div class="restaurant-dashboard">

<div class="restaurant-nav">

    <div class="logo">
        🍽️ <?php echo htmlspecialchars($restaurant_name); ?>
    </div>

    <div class="nav-links">
        <a href="restaurant_dashboard.php">Dashboard</a>
        <a href="restaurant_orders.php">Orders</a>
        <a class="active">Menu</a>
        <a href="restaurant_analytics.php">Analytics</a>
        <a href="restaurant_profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

</div>

<h1 class="restaurant-title">Add New Food</h1>

<div class="restaurant-profile-card">

<?php
if(isset($error)){
    echo "<p style='color:red;font-weight:bold;'>$error</p>";
}
?>

<form method="POST">

<div class="profile-row">
<label>Food Name</label>
<input
type="text"
name="item_name"
required>
</div>

<div class="profile-row">
<label>Category</label>

<select name="category">

<option>Rice</option>
<option>Noodles</option>
<option>Snacks</option>
<option>Drinks</option>
<option>Custom meal</option>

</select>

</div>

<div class="profile-row">
<label>Price (RM)</label>

<input
type="number"
step="0.10"
name="price"
required>

</div>

<div class="profile-row">
<label>Image Filename</label>

<input
type="text"
name="image"
placeholder="fried-rice.jpg"
required>

<small>
Image must already exist inside the images folder.
</small>

</div>

<button class="save-profile-btn">
Add Food
</button>

</form>

</div>

</div>

<?php include("../includes/footer.php"); ?>