<?php
include("../includes/db.php");

$message = "";
$allowed_roles = ["student", "restaurant", "rider", "admin"];

$nav_links = [
    ["href" => "home.php", "icon" => "fa-house", "label" => "Home"],
    ["href" => "menu.php", "icon" => "fa-utensils", "label" => "Menu"],
    ["href" => "about.php", "icon" => "fa-circle-info", "label" => "About"],
    ["href" => "contact.php", "icon" => "fa-envelope", "label" => "Contact"],
    ["href" => "login.php", "icon" => "fa-right-to-bracket", "label" => "Login"],
    ["href" => "register.php", "icon" => "fa-user-plus", "label" => "Register", "active" => true],
];

$role_cards = [
    [
        "value" => "student",
        "icon" => "fa-graduation-cap",
        "title" => "Student",
        "description" => "Order food and enjoy exclusive student deals"
    ],
    [
        "value" => "restaurant",
        "icon" => "fa-store",
        "title" => "Restaurant",
        "description" => "Manage your restaurant, menu and orders"
    ],
    [
        "value" => "rider",
        "icon" => "fa-motorcycle",
        "title" => "Rider",
        "description" => "Deliver orders and earn with us"
    ],
    [
        "value" => "admin",
        "icon" => "fa-user-shield",
        "title" => "Admin",
        "description" => "Manage users, restaurants, orders and reports"
    ]
];

$benefits = [
    ["icon" => "fa-shield-heart", "title" => "Secure & Safe", "description" => "Your data is protected with us"],
    ["icon" => "fa-truck-fast", "title" => "Fast Delivery", "description" => "Quick and reliable delivery"],
    ["icon" => "fa-headset", "title" => "24/7 Support", "description" => "We're here to help anytime"],
];

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

function generateUserCode($conn, $prefix) {
    $like = $prefix . "%";
    $stmt = $conn->prepare("
        SELECT user_code
        FROM users
        WHERE user_code LIKE ?
        ORDER BY CAST(SUBSTRING(user_code, 4) AS UNSIGNED) DESC
        LIMIT 1
    ");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $nextNumber = 1;
    if ($row && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $row["user_code"], $matches)) {
        $nextNumber = (int)$matches[1] + 1;
    }

    return $prefix . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $role = $_POST["role"] ?? "student";

    if (!in_array($role, $allowed_roles, true)) {
        $message = "Please choose a valid account type.";
    } elseif ($full_name === "" || $email === "" || $password === "" || $confirm_password === "") {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $message = "Password and confirm password do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $prefixes = [
            "student" => "STU",
            "restaurant" => "RES",
            "rider" => "RID",
            "admin" => "ADM"
        ];

        $prefix = $prefixes[$role];

        $emailCheck = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $emailCheck->bind_param("s", $email);
        $emailCheck->execute();

        if ($emailCheck->get_result()->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $sql = "INSERT INTO users (user_code, full_name, email, password, role)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $registered = false;
                $user_code = "";

                for ($attempt = 0; $attempt < 5; $attempt++) {
                    $user_code = generateUserCode($conn, $prefix);
                    $stmt->bind_param("sssss", $user_code, $full_name, $email, $hashed_password, $role);

                    try {
                        if ($stmt->execute()) {
                            $registered = true;
                            break;
                        }
                    } catch (mysqli_sql_exception $exception) {
                        if ($exception->getCode() !== 1062) {
                            throw $exception;
                        }
                    }
                }

                if ($registered) {
                    echo "<script>
                            alert('Registration Successful! Your ID is $user_code');
                            window.location = 'login.php';
                          </script>";
                    exit();
                }
            }

            $message = "Registration failed. Please try again.";
        }
    }
}
?>

<?php include("../includes/header.php"); ?>

<main class="register-shell">
    <section class="register-panel">
        <nav class="register-topbar">
            <a class="register-brand" href="../index.php">
                <span class="brand-mark"><i class="fa-solid fa-utensils"></i></span>
                <span>
                    <b>BUDDY BITES</b>
                    <small>Good Food, Good Mood</small>
                </span>
            </a>

            <div class="register-nav">
                <?php foreach ($nav_links as $link) { ?>
                    <a href="<?php echo e($link["href"]); ?>" class="<?php echo !empty($link["active"]) ? "active" : ""; ?>">
                        <i class="fa-solid <?php echo e($link["icon"]); ?>"></i>
                        <?php echo e($link["label"]); ?>
                    </a>
                <?php } ?>
            </div>
        </nav>

        <div class="register-content">
            <div class="register-dots dots-left"></div>
            <div class="register-dots dots-right"></div>

            <div class="register-heading">
                <h1>Create Your Account</h1>
                <p>Join Buddy Bites and start saving on food</p>
            </div>

            <?php if ($message !== "") { ?>
                <p class="register-message"><?php echo e($message); ?></p>
            <?php } ?>

            <form method="POST" action="register.php" class="register-form" onsubmit="return validateRegisterForm()">
                <fieldset class="role-fieldset">
                    <legend>I am registering as:</legend>

                    <div class="register-role-grid">
                        <?php foreach ($role_cards as $index => $role_card) {
                            $is_selected = $index === 0;
                        ?>
                            <label class="role-tile <?php echo $is_selected ? "selected" : ""; ?>">
                                <input
                                    type="radio"
                                    name="role"
                                    value="<?php echo e($role_card["value"]); ?>"
                                    <?php echo $is_selected ? "checked" : ""; ?>
                                >
                                <span class="role-check">
                                    <i class="<?php echo $is_selected ? "fa-solid fa-circle-dot" : "fa-regular fa-circle"; ?>"></i>
                                </span>
                                <span class="role-icon">
                                    <i class="fa-solid <?php echo e($role_card["icon"]); ?>"></i>
                                </span>
                                <strong><?php echo e($role_card["title"]); ?></strong>
                                <small><?php echo e($role_card["description"]); ?></small>
                            </label>
                        <?php } ?>
                    </div>
                </fieldset>

                <div class="register-form-grid">
                    <div class="input-group">
                        <label for="full_name">Full Name</label>
                        <div class="input-shell">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="full_name" id="full_name" placeholder="Enter your full name">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <div class="input-shell">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="email" id="email" placeholder="Enter your email">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="phone">Phone Number (Optional)</label>
                        <div class="input-shell">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" name="phone" id="phone" placeholder="Enter your phone number">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="input-shell">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Create a password">
                            <i class="fa-regular fa-eye toggle-password" data-target="password" role="button" tabindex="0" aria-label="Show password"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-shell">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password">
                            <i class="fa-regular fa-eye toggle-password" data-target="confirm_password" role="button" tabindex="0" aria-label="Show confirm password"></i>
                        </div>
                    </div>
                </div>

                <label class="terms-row">
                    <input type="checkbox" id="terms">
                    <span>I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></span>
                </label>

                <button class="register-submit" type="submit">
                    <i class="fa-solid fa-user-plus"></i>
                    CREATE ACCOUNT
                </button>
            </form>

            <div class="register-divider"><span>or</span></div>

            <p class="register-login">Already have an account? <a href="login.php">Login here</a></p>
        </div>

        <div class="register-footer-band">
            <?php foreach ($benefits as $benefit) { ?>
                <div class="benefit">
                    <span><i class="fa-solid <?php echo e($benefit["icon"]); ?>"></i></span>
                    <b><?php echo e($benefit["title"]); ?></b>
                    <small><?php echo e($benefit["description"]); ?></small>
                </div>
            <?php } ?>

            <img src="../images/rice-bowl.jpg" alt="BuddyBites meal bowl" class="register-food-img">
        </div>
    </section>

    <footer class="register-copyright">&copy; 2026 Buddy Bites. All rights reserved.</footer>
</main>

<script>
document.querySelectorAll(".role-tile").forEach(function(tile) {
    tile.addEventListener("click", function() {
        document.querySelectorAll(".role-tile").forEach(function(item) {
            item.classList.remove("selected");
            item.querySelector(".role-check i").className = "fa-regular fa-circle";
        });

        tile.classList.add("selected");
        tile.querySelector(".role-check i").className = "fa-solid fa-circle-dot";
    });
});

document.querySelectorAll(".toggle-password").forEach(function(toggle) {
    function togglePasswordVisibility() {
        const input = document.getElementById(toggle.dataset.target);

        if (!input) {
            return;
        }

        const shouldShow = input.type === "password";
        input.type = shouldShow ? "text" : "password";
        toggle.className = shouldShow
            ? "fa-regular fa-eye-slash toggle-password"
            : "fa-regular fa-eye toggle-password";
        toggle.setAttribute("aria-label", shouldShow ? "Hide password" : "Show password");
    }

    toggle.addEventListener("click", togglePasswordVisibility);
    toggle.addEventListener("keydown", function(event) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            togglePasswordVisibility();
        }
    });
});

function validateRegisterForm() {
    const fullName = document.getElementById("full_name").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;
    const terms = document.getElementById("terms").checked;

    if (fullName === "" || email === "" || password === "" || confirmPassword === "") {
        alert("Please fill in all fields.");
        return false;
    }

    if (!email.includes("@") || !email.includes(".")) {
        alert("Please enter a valid email address.");
        return false;
    }

    if (password !== confirmPassword) {
        alert("Password and confirm password do not match.");
        return false;
    }

    if (!terms) {
        alert("Please agree to the Terms & Conditions and Privacy Policy.");
        return false;
    }

    return true;
}
</script>

<?php include("../includes/footer.php"); ?>
