<?php
include("../includes/auth_check.php");
requireRole("restaurant");
include("../includes/db.php");

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$restaurant_name = $_SESSION["full_name"];

if (!isset($_GET["id"])) {
    header("Location: restaurant_menu.php");
    exit();
}

$item_id = (int)$_GET["id"];

/* Load selected food */
$sql = "SELECT * FROM menu_items
        WHERE item_id = ?
        AND restaurant_name = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is",$item_id,$restaurant_name);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows==0){
    die("Food not found.");
}

$item = $result->fetch_assoc();

/* Save changes */
if($_SERVER["REQUEST_METHOD"]=="POST"){

    $item_name = trim($_POST["item_name"]);
    $category = $_POST["category"];
    $price = $_POST["price"];
    $image = trim($_POST["image"]);

    $update = $conn->prepare("
        UPDATE menu_items
        SET
            item_name=?,
            category=?,
            price=?,
            image=?
        WHERE
            item_id=?
        AND
            restaurant_name=?
    ");

    $update->bind_param(
        "ssdsis",
        $item_name,
        $category,
        $price,
        $image,
        $item_id,
        $restaurant_name
    );

    $update->execute();

    header("Location: restaurant_menu.php");
    exit();
}
?>

<?php include("../includes/header.php"); ?>

<div class="restaurant-dashboard">

<div class="restaurant-nav">

<div class="logo">
🍽️ <?php echo h($restaurant_name); ?>
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

<h1 class="restaurant-title">
Edit Food
</h1>

<div class="restaurant-profile-card">

<form method="POST">

<div class="profile-row">
<label>Food Name</label>

<input
type="text"
name="item_name"
value="<?php echo h($item["item_name"]); ?>"
required>

</div>

<div class="profile-row">

<label>Category</label>

<select name="category">

<?php

$categories = [
"Rice",
"Noodles",
"Snacks",
"Drinks",
"Custom meal"
];

foreach($categories as $cat){

$selected = ($item["category"]==$cat) ? "selected" : "";

echo "<option $selected>$cat</option>";

}

?>

</select>

</div>

<div class="profile-row">

<label>Price (RM)</label>

<input
type="number"
step="0.10"
name="price"
value="<?php echo $item["price"]; ?>"
required>

</div>

<div class="profile-row">

<label>Image Filename</label>

<input
type="text"
name="image"
value="<?php echo h($item["image"]); ?>"
required>

</div>

<button class="save-profile-btn">
Save Changes
</button>

</form>

</div>

</div>

<?php include("../includes/footer.php"); ?>