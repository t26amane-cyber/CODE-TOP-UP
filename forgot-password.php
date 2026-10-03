<?php
include 'common/config.php';

$msg = "";
$msgType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $msg = "Please enter a valid email address.";
        $msgType = "error";

    } else {

        // Create reset fields if they don't exist
        $checkToken = $conn->query("SHOW COLUMNS FROM users LIKE 'reset_token'");

        if ($checkToken && $checkToken->num_rows === 0) {
            $conn->query("
                ALTER TABLE users
                ADD reset_token VARCHAR(255) NULL,
                ADD reset_expires DATETIME NULL
            ");
        }

        $stmt = $conn->prepare(
            "SELECT id, name FROM users WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", time() + 3600);

            $update = $conn->prepare(
                "UPDATE users
                 SET reset_token = ?, reset_expires = ?
                 WHERE id = ?"
            );

            $update->bind_param(
                "ssi",
                $token,
                $expires,
                $user['id']
            );

            $update->execute();
            $update->close();

            $scheme = (!empty($_SERVER['HTTPS'])
                       && $_SERVER['HTTPS'] !== 'off')
                       ? 'https'
                       : 'http';

            $host = $_SERVER['HTTP_HOST'];

            $path = rtrim(
                dirname($_SERVER['PHP_SELF']),
                '/\\'
            );

            $resetLink =
                $scheme . "://" .
                $host .
                $path .
                "/reset-password.php?token=" .
                urlencode($token);

            $subject = "CODE TOP UP - Password Reset";

            $message =
                "Hello " . $user['name'] . ",\n\n" .
                "You requested a password reset for your CODE TOP UP account.\n\n" .
                "Reset your password using this link:\n" .
                $resetLink . "\n\n" .
                "This link will expire in 1 hour.\n\n" .
                "If you did not request this, you can ignore this email.";

            $headers =
                "From: CODE TOP UP <no-reply@" . $host . ">\r\n" .
                "Reply-To: no-reply@" . $host . "\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n";

            if (mail($email, $subject, $message, $headers)) {

                $msg =
                    "A password reset link has been sent to your email.";

                $msgType = "success";

            } else {

                $msg =
                    "Reset request created, but email delivery is not configured on this server.";

                $msgType = "error";
            }

        } else {

            // Don't reveal whether an email exists
            $msg =
                "If this email is registered, a reset link will be sent.";

            $msgType = "success";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>CODE TOP UP - Forgot Password</title>

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

            <i class="fa-solid fa-key text-green-400 text-2xl"></i>

        </div>

        <h1 class="text-2xl font-bold text-white">
            Forgot Password?
        </h1>

        <p class="text-sm text-slate-400 mt-2">
            Enter your email to reset your password
        </p>

    </div>


    <?php if ($msg): ?>

        <div class="mx-6 mb-4 p-3 rounded-lg border text-sm text-center
            <?php echo $msgType === 'success'
                ? 'bg-green-500/10 border-green-500/30 text-green-400'
                : 'bg-red-500/10 border-red-500/30 text-red-400'; ?>">

            <?php echo htmlspecialchars($msg); ?>

        </div>

    <?php endif; ?>


    <form method="POST" class="px-6 pb-7 space-y-5">

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
                    placeholder="Enter your registered email"
                    required
                    class="input-box w-full rounded-xl
                           text-white py-3.5 pl-11 pr-4
                           text-sm"
                >

            </div>

        </div>


        <button
            type="submit"
            class="btn w-full rounded-xl
                   text-white font-bold
                   py-3.5">

            <i class="fa-solid fa-paper-plane mr-2"></i>

            SEND RESET LINK

        </button>


        <div class="text-center">

            <a href="login.php"
               class="text-sm text-green-400 hover:underline">

                <i class="fa-solid fa-arrow-left mr-1"></i>
                Back to Login

            </a>

        </div>

    </form>


    <div class="border-t border-slate-700
                px-6 py-4 text-center">

        <p class="text-xs text-slate-500">
            © <?php echo date('Y'); ?> CODE TOP UP
        </p>

    </div>

</div>

</body>
</html>
