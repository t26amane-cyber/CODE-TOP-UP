<?php
include 'common/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$msg = "";
$msgType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $phone === '' || $email === '' || $password === '') {

        $msg = "Please fill in all fields.";
        $msgType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $msg = "Please enter a valid email address.";
        $msgType = "error";

    } elseif (strlen($password) < 6) {

        $msg = "Password must be at least 6 characters.";
        $msgType = "error";

    } elseif ($password !== $confirm_password) {

        $msg = "Passwords do not match.";
        $msgType = "error";

    } else {

        // Check existing email or phone
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1"
        );

        $check->bind_param("ss", $email, $phone);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $msg = "Email or phone number is already registered.";
            $msgType = "error";

        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, phone, email, password)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $name,
                $phone,
                $email,
                $hash
            );

            if ($stmt->execute()) {

                $msg = "Account created successfully. Please login.";
                $msgType = "success";

            } else {

                $msg = "Registration failed. Please try again.";
                $msgType = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>CODE TOP UP - Register</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

body {
    font-family: Arial, sans-serif;
    background:
        radial-gradient(circle at top, #1e293b 0%, #020617 55%, #000 100%);
}

.glass {
    background: rgba(15, 23, 42, .94);
    backdrop-filter: blur(15px);
}

.input-box {
    background: #0f172a;
    border: 1px solid #334155;
}

.input-box:focus {
    border-color: #22c55e;
    outline: none;
}

.btn {
    background: linear-gradient(135deg, #22c55e, #16a34a);
}

</style>

</head>

<body class="min-h-screen flex items-center justify-center p-4">

<div class="glass w-full max-w-md rounded-2xl
            shadow-2xl border border-slate-700 overflow-hidden">

    <!-- Header -->

    <div class="text-center px-6 pt-8 pb-5">

        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl
                    bg-green-500/10 border border-green-500/30
                    flex items-center justify-center">

            <i class="fa-solid fa-user-plus
                      text-green-400 text-2xl"></i>

        </div>

        <h1 class="text-2xl font-bold text-white">
            CODE TOP UP
        </h1>

        <p class="text-sm text-slate-400 mt-2">
            Create your account
        </p>

    </div>


    <!-- Message -->

    <?php if ($msg): ?>

        <div class="mx-6 mb-4 p-3 rounded-lg
            <?php echo $msgType === 'success'
                ? 'bg-green-500/10 border-green-500/30 text-green-400'
                : 'bg-red-500/10 border-red-500/30 text-red-400'; ?>
            border text-sm text-center">

            <?php echo htmlspecialchars($msg); ?>

            <?php if ($msgType === 'success'): ?>

                <div class="mt-2">

                    <a href="login.php"
                       class="underline font-semibold">

                        Login Now

                    </a>

                </div>

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <!-- Register Form -->

    <form method="POST"
          class="px-6 pb-7 space-y-4">


        <!-- Name -->

        <div>

            <label class="block text-sm text-slate-300 mb-2">
                Full Name
            </label>

            <div class="relative">

                <i class="fa-solid fa-user
                    absolute left-4 top-1/2
                    -translate-y-1/2
                    text-slate-500"></i>

                <input
                    type="text"
                    name="name"
                    value="<?php echo htmlspecialchars($name ?? ''); ?>"
                    placeholder="Enter your full name"
                    required
                    class="input-box w-full rounded-xl
                           text-white py-3.5 pl-11 pr-4
                           text-sm"
                >

            </div>

        </div>


        <!-- Phone -->

        <div>

            <label class="block text-sm text-slate-300 mb-2">
                Phone Number
            </label>

            <div class="relative">

                <i class="fa-solid fa-phone
                    absolute left-4 top-1/2
                    -translate-y-1/2
                    text-slate-500"></i>

                <input
                    type="tel"
                    name="phone"
                    value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                    placeholder="01XXXXXXXXX"
                    required
                    class="input-box w-full rounded-xl
                           text-white py-3.5 pl-11 pr-4
                           text-sm"
                >

            </div>

        </div>


        <!-- Email -->

        <div>

            <label class="block text-sm text-slate-300 mb-2">
                Email Address
            </label>

            <div class="relative">

                <i class="fa-solid fa-envelope
                    absolute left-4 top-1/2
                    -translate-y-1/2
                    text-slate-500"></i>

                <input
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    placeholder="example@email.com"
                    required
                    class="input-box w-full rounded-xl
                           text-white py-3.5 pl-11 pr-4
                           text-sm"
                >

            </div>

        </div>


        <!-- Password -->

        <div>

            <label class="block text-sm text-slate-300 mb-2">
                Password
            </label>

            <div class="relative">

                <i class="fa-solid fa-lock
                    absolute left-4 top-1/2
                    -translate-y-1/2
                    text-slate-500"></i>

                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    required
                    class="input-box w-full rounded-xl
                           text-white py-3.5 pl-11 pr-12
                           text-sm"
                >

                <button
                    type="button"
                    onclick="togglePassword('password','eye1')"
                    class="absolute right-4 top-1/2
                           -translate-y-1/2
                           text-slate-500">

                    <i id="eye1"
                       class="fa-solid fa-eye"></i>

                </button>

            </div>

        </div>


        <!-- Confirm Password -->

        <div>

            <label class="block text-sm text-slate-300 mb-2">
                Confirm Password
            </label>

            <div class="relative">

                <i class="fa-solid fa-shield-halved
                    absolute left-4 top-1/2
                    -translate-y-1/2
                    text-slate-500"></i>

                <input
                    id="confirm_password"
                    type="password"
                    name="confirm_password"
                    placeholder="Re-enter your password"
                    required
                    class="input-box w-full rounded-xl
                           text-white py-3.5 pl-11 pr-12
                           text-sm"
                >

                <button
                    type="button"
                    onclick="togglePassword('confirm_password','eye2')"
                    class="absolute right-4 top-1/2
                           -translate-y-1/2
                           text-slate-500">

                    <i id="eye2"
                       class="fa-solid fa-eye"></i>

                </button>

            </div>

        </div>


        <!-- Register -->

        <button
            type="submit"
            class="btn w-full rounded-xl
                   text-white font-bold
                   py-3.5 transition">

            <i class="fa-solid fa-user-plus mr-2"></i>

            CREATE ACCOUNT

        </button>


        <!-- Login -->

        <div class="text-center pt-2">

            <span class="text-sm text-slate-500">
                Already have an account?
            </span>

            <a href="login.php"
               class="text-sm text-green-400
                      font-semibold hover:underline ml-1">

                Login

            </a>

        </div>

    </form>


    <!-- Footer -->

    <div class="border-t border-slate-700
                px-6 py-4 text-center">

        <p class="text-xs text-slate-500">
            © <?php echo date('Y'); ?> CODE TOP UP
        </p>

    </div>

</div>


<script>

function togglePassword(id, eyeId) {

    const input = document.getElementById(id);
    const eye = document.getElementById(eyeId);

    if (input.type === "password") {

        input.type = "text";

        eye.classList.remove("fa-eye");
        eye.classList.add("fa-eye-slash");

    } else {

        input.type = "password";

        eye.classList.remove("fa-eye-slash");
        eye.classList.add("fa-eye");

    }

}

</script>

</body>
</html>
