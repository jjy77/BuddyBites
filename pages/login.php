<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = "";

$nav_links = [
    ["href" => "home.php", "icon" => "fa-house", "label" => "Home"],
    ["href" => "menu.php", "icon" => "fa-utensils", "label" => "Menu"],
    ["href" => "about.php", "icon" => "fa-circle-info", "label" => "About"],
    ["href" => "contact.php", "icon" => "fa-envelope", "label" => "Contact"],
    ["href" => "login.php", "icon" => "fa-right-to-bracket", "label" => "Login", "active" => true],
];

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $message = "Please enter email and password.";
    } else {
        include("../includes/db.php");

        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["full_name"] = $user["full_name"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] === "admin") {
                    header("Location: admin_dashboard.php");
                    exit();
                }

                if ($user["role"] === "restaurant") {
                    header("Location: restaurant_dashboard.php");
                    exit();
                }

                if ($user["role"] === "rider") {
                    header("Location: rider_dashboard.php");
                    exit();
                }

                header("Location: home.php");
                exit();
            }

            $message = "Incorrect password.";
        } else {
            $message = "Email not found.";
        }
    }
}
?>

<?php include("../includes/header.php"); ?>

<main class="login-shell">
    <nav class="login-topbar">
        <a class="login-brand" href="../index.php">
            <span class="brand-mark"><i class="fa-solid fa-utensils"></i></span>
            <span>
                <b>BUDDY BITES</b>
                <small>Good Food, Good Mood</small>
            </span>
        </a>

        <div class="login-nav">
            <?php foreach ($nav_links as $link) { ?>
                <a href="<?php echo e($link["href"]); ?>" class="<?php echo !empty($link["active"]) ? "active" : ""; ?>">
                    <i class="fa-solid <?php echo e($link["icon"]); ?>"></i>
                    <?php echo e($link["label"]); ?>
                </a>
            <?php } ?>
        </div>
    </nav>

    <section class="login-page">
        <div class="login-heading">
            <div>
                <h1>Login to Your Account</h1>
                <p>Welcome back! Please login to continue</p>
            </div>
        </div>

        <section class="login-card">
            <div class="login-hero">
                <span class="login-avatar"><i class="fa-solid fa-user"></i></span>
                <h2>Welcome back!</h2>
                <p>Login to your Buddy Bites account</p>
            </div>

            <div class="login-role-tabs">
                <button type="button" class="active"><i class="fa-solid fa-graduation-cap"></i> Student</button>
                <button type="button"><i class="fa-solid fa-store"></i> Restaurant</button>
                <button type="button"><i class="fa-solid fa-truck-fast"></i> Rider</button>
                <button type="button"><i class="fa-solid fa-user-shield"></i> Admin</button>
            </div>

            <?php if ($message !== "") { ?>
                <p class="login-message"><?php echo e($message); ?></p>
            <?php } ?>

            <form method="POST" action="login.php" onsubmit="return validateLoginForm()" class="login-form">
                <label for="email">Email Address</label>
                <div class="login-input">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="Enter your email">
                </div>

                <label for="password">Password</label>
                <div class="login-input">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Enter your password">
                    <button class="toggle-password" type="button" data-target="password" aria-label="Show password">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>

                <a class="login-forgot" href="#">Forgot password?</a>

                <button class="login-submit" type="submit">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    LOGIN
                </button>
            </form>

            <div class="login-divider"><span>or</span></div>

            <p class="login-signup">Don't have an account? <a href="register.php">Sign up</a></p>
        </section>
    </section>
</main>

<script>
document.querySelectorAll(".login-role-tabs button").forEach(function(button) {
    button.addEventListener("click", function() {
        document.querySelectorAll(".login-role-tabs button").forEach(function(item) {
            item.classList.remove("active");
        });
        button.classList.add("active");
    });
});

document.querySelectorAll(".toggle-password").forEach(function(toggle) {
    function togglePasswordVisibility() {
        const input = document.getElementById(toggle.dataset.target);
        const icon = toggle.querySelector("i");

        if (!input) {
            return;
        }

        const shouldShow = input.type === "password";
        input.type = shouldShow ? "text" : "password";

        if (icon) {
            icon.className = shouldShow ? "fa-regular fa-eye-slash" : "fa-regular fa-eye";
        }

        toggle.classList.toggle("is-visible", shouldShow);
        toggle.setAttribute("aria-label", shouldShow ? "Hide password" : "Show password");
    }

    toggle.addEventListener("click", togglePasswordVisibility);
});

function validateLoginForm() {
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    if (email === "" || password === "") {
        alert("Please enter email and password.");
        return false;
    }

    if (!email.includes("@") || !email.includes(".")) {
        alert("Please enter a valid email address.");
        return false;
    }

    return true;
}
</script>

<?php include("../includes/footer.php"); ?>
