<?php
include 'common/config.php';

$msg = "";
$msgType = "";

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

$user = null;

if ($token !== '') {

    $stmt = $conn->prepare(
        "SELECT id, name
         FROM users
         WHERE reset_token = ?
         AND reset_expires > NOW()
         LIMIT 1"
    );

    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    }

    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {

        $msg = "Password must be at least 6 characters.";
        $msgType = "error";

    } elseif ($password !== $confirm) {

        $msg = "Passwords do not match.";
        $msgType = "error";

    } else {

        $hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $update = $conn->prepare(
            "UPDATE users
             SET password = ?,
                 reset_token = NULL,
                 reset_expires = NULL
             WHERE id = ?"
        );

        $update->bind_param(
            "si",
            $hash,
            $user['id']
        );

        if ($update->execute()) {

            $msg = "Your password has been changed successfully.";
            $msgType = "success";

            $user = null;

        } else {

            $msg = "Unable to reset password. Please try again.";
            $msgType = "error";
        }

        $update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>CODE TOP UP - Reset Password</title>

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

    <div class="text-center px-6 pt-8 pb-6">

        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl
                    bg-green-500/10 border border-green-500/30
                    flex items-center justify-center">

            <i class="fa-solid fa-lock-open
                      text-green-400 text-2xl"></i>

        </div>

        <h1 class="text-2xl font-bold text-white">
            Reset Password
        </h1>

        <p class="text-sm text-slate-400 mt-2">
            Create a new password for your account
        </p>

    </div>


    <?php if ($msg): ?>

        <div class="mx-6 mb-4 p-3 rounded-lg border text-sm text-center
            <?php echo $msgType === 'success'
                ? 'bg-green-500/10 border-green-500/30 text-green-400'
                : 'bg-red-500/10 border-red-500/30 text-red-400'; ?>">

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


    <?php if ($user): ?>

        <form method="POST"
              class="px-6 pb-7 space-y-5">

            <input
                type="hidden"
                name="token"
                value="<?php echo htmlspecialchars($token); ?>"
            >


            <!-- New Password -->

            <div>

                <label class="block text-sm text-slate-300 mb-2">
                    New Password
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
                        minlength="6"
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
                    Confirm New Password
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
                        minlength="6"
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


            <!-- Submit -->

            <button
                type="submit"
                class="btn w-full rounded-xl
                       text-white font-bold
                       py-3.5">

                <i class="fa-solid fa-key mr-2"></i>

                RESET PASSWORD

            </button>

        </form>


    <?php elseif ($msgType !== 'success'): ?>

        <div class="px-6 pb-7 text-center">

            <div class="p-4 rounded-xl
                        bg-red-500/10
                        border border-red-500/30
                        text-red-400 text-sm">

                This password reset link is invalid
                or has expired.

            </div>

            <a href="forgot-password.php"
               class="inline-block mt-5
                      text-green-400 text-sm
                      hover:underline">

                Request a New Reset Link

            </a>

        </div>

    <?php endif; ?>


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
