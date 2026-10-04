<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include 'common/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email এবং Password দিন।';
    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, phone, password
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                header('Location: index.php');
                exit;

            } else {
                $error = 'Email অথবা Password ভুল।';
            }

        } else {
            $error = 'Email অথবা Password ভুল।';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - CODE TOP UP</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-950 text-white flex items-center justify-center p-4">

<div class="w-full max-w-md">

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-2xl">

        <div class="text-center mb-7">

            <h1 class="text-3xl font-bold">
                CODE TOP UP
            </h1>

            <p class="text-gray-400 mt-2">
                Login to your account
            </p>

        </div>

        <?php if ($error): ?>

            <div class="bg-red-500/10 border border-red-500/30
                        text-red-400 rounded-lg p-3 mb-5 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <!-- Google Login -->

        <a
            href="google-login.php"
            class="w-full flex items-center justify-center gap-3
                   bg-white text-gray-900 font-semibold
                   py-3 rounded-xl hover:bg-gray-100 transition mb-5"
        >

            <span class="text-xl">G</span>

            Continue with Google

        </a>


        <div class="flex items-center gap-3 my-5">

            <div class="h-px bg-gray-700 flex-1"></div>

            <span class="text-gray-500 text-sm">
                OR
            </span>

            <div class="h-px bg-gray-700 flex-1"></div>

        </div>


        <!-- Email Login -->

        <form method="POST">

            <label class="block text-sm text-gray-300 mb-2">
                Email
            </label>

            <input
                type="email"
                name="email"
                required
                autocomplete="email"
                placeholder="Enter your email"
                class="w-full bg-gray-800 border border-gray-700
                       rounded-xl px-4 py-3 mb-4
                       outline-none focus:border-blue-500"
            >


            <label class="block text-sm text-gray-300 mb-2">
                Password
            </label>

            <div class="relative">

                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="w-full bg-gray-800 border border-gray-700
                           rounded-xl px-4 py-3 pr-12
                           outline-none focus:border-blue-500"
                >

                <button
                    type="button"
                    onclick="togglePassword()"
                    class="absolute right-3 top-1/2
                           -translate-y-1/2 text-gray-400"
                >
                    👁
                </button>

            </div>


            <div class="text-right mt-3">

                <a
                    href="forgot-password.php"
                    class="text-blue-400 text-sm hover:underline"
                >
                    Forgot Password?
                </a>

            </div>


            <button
                type="submit"
                class="w-full mt-5 bg-blue-600
                       hover:bg-blue-700
                       py-3 rounded-xl
                       font-semibold transition"
            >
                Login
            </button>

        </form>


        <div class="text-center mt-6 text-sm text-gray-400">

            Don't have an account?

            <a
                href="register.php"
                class="text-blue-400 hover:underline"
            >
                Create Account
            </a>

        </div>

    </div>

</div>


<script>

function togglePassword() {

    const password =
        document.getElementById('password');

    password.type =
        password.type === 'password'
        ? 'text'
        : 'password';
}

</script>

</body>
</html>
