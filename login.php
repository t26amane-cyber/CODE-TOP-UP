<?php
include 'common/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$msg = "";
$msgType = "error";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type = $_POST['type'] ?? 'login';

    if ($type === 'login') {

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $msg = "Please enter your email/phone and password.";
        } else {

            $stmt = $conn->prepare(
                "SELECT id, name, email, phone, password FROM users
                 WHERE email = ? OR phone = ?
                 LIMIT 1"
            );

            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];

                    header("Location: index.php");
                    exit;

                } else {
                    $msg = "Incorrect password.";
                }

            } else {
                $msg = "Account not found.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>CODE TOP UP - Login</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

body {
    font-family: Arial, sans-serif;
    background:
        radial-gradient(circle at top, #1e293b 0%, #020617 55%, #000000 100%);
}

.glass {
    background: rgba(15, 23, 42, 0.92);
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

.btn:hover {
    opacity: .9;
}

</style>

</head>

<body class="min-h-screen flex items-center justify-center p-4">

<div class="glass w-full max-w-md rounded-2xl shadow-2xl border border-slate-700 overflow-hidden">

    <!-- Header -->

    <div class="text-center px-6 pt-8 pb-5">

        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl
                    bg-green-500/10 border border-green-500/30
                    flex items-center justify-center">

            <i class="fa-solid fa-bolt text-green-400 text-2xl"></i>

        </div>

        <h1 class="text-2xl font-bold text-white">
            CODE TOP UP
        </h1>

        <p class="text-sm text-slate-400 mt-2">
            Login to your account
        </p>

    </div>


    <!-- Message -->

    <?php if ($msg): ?>

        <div class="mx-6 mb-4 p-3 rounded-lg
                    bg-red-500/10
                    border border-red-500/30
                    text-red-400 text-sm text-center">

            <?php echo htmlspecialchars($msg); ?>

        </div>

    <?php endif; ?>


    <!-- Login Form -->

    <form method="POST" class="px-6 pb-7 space-y-5">

        <input type="hidden"
               name="type"
               value="login">


        <!-- Email / Phone -->

        <div>

            <label class="block text-sm text-slate-300 mb-2">
                Email or Phone
            </label>

            <div class="relative">

                <i class="fa-solid fa-user
                          absolute left-4 top-1/2
                          -translate-y-1/2
                          text-slate-500"></i>

                <input
                    type="text"
                    name="email"
                    autocomplete="username"
                    placeholder="Enter email or phone"
                    required
                    class="input-box w-full rounded-xl
                           text-white py-3.5 pl-11 pr-4
                           text-sm"
                >

            </div>

        </div>


        <!-- Password -->

        <div>

            <div class="flex justify-between items-center mb-2">

                <label class="text-sm text-slate-300">
                    Password
                </label>

                <a href="forgot-password.php"
                   class="text-xs text-green-400 hover:underline">

                    Forgot Password?

                </a>

            </div>


            <div class="relative">

                <i class="fa-solid fa-lock
                          absolute left-4 top-1/2
                          -translate-y-1/2
                          text-slate-500"></i>

                <input
                    id="password"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    placeholder="Enter password"
                    required
                    class="input-box w-full rounded-xl
                           text-white py-3.5 pl-11 pr-12
                           text-sm"
                >

                <button
                    type="button"
                    onclick="togglePassword()"
                    class="absolute right-4 top-1/2
                           -translate-y-1/2
                           text-slate-500">

                    <i id="eye"
                       class="fa-solid fa-eye"></i>

                </button>

            </div>

        </div>


        <!-- Login -->

        <button
            type="submit"
            class="btn w-full rounded-xl
                   text-white font-bold
                   py-3.5 transition">

            <i class="fa-solid fa-right-to-bracket mr-2"></i>

            LOGIN

        </button>


        <!-- Register -->

        <div class="text-center pt-2">

            <span class="text-sm text-slate-500">
                Don't have an account?
            </span>

            <a href="register.php"
               class="text-sm text-green-400
                      font-semibold hover:underline ml-1">

                Create Account

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

function togglePassword() {

    const password =
        document.getElementById("password");

    const eye =
        document.getElementById("eye");

    if (password.type === "password") {

        password.type = "text";

        eye.classList.remove("fa-eye");
        eye.classList.add("fa-eye-slash");

    } else {

        password.type = "password";

        eye.classList.remove("fa-eye-slash");
        eye.classList.add("fa-eye");

    }

}

</script>

</body>
</html>
