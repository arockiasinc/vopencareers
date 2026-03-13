<?php
declare(strict_types=1);

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function isLocalRequest(): bool
{
    $remoteAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

    return in_array($remoteAddress, ['127.0.0.1', '::1'], true);
}

if (PHP_SAPI !== 'cli' && !isLocalRequest()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "This tool is only available from localhost.\n";
    exit;
}

if (PHP_SAPI === 'cli') {
    $password = (string) ($argv[1] ?? '');

    if ($password === '') {
        fwrite(STDERR, "Usage: php admin/generate-password-hash.php \"YourPassword\"\n");
        exit(1);
    }

    echo password_hash($password, PASSWORD_DEFAULT) . PHP_EOL;
    exit;
}

$password = '';
$hash = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');

    if ($password === '') {
        $error = 'Enter a password to generate the hash.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Admin Password Hash</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            line-height: 1.5;
        }

        form {
            max-width: 32rem;
        }

        label,
        input,
        button,
        textarea {
            display: block;
            width: 100%;
        }

        input,
        button,
        textarea {
            box-sizing: border-box;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            font: inherit;
        }

        textarea {
            min-height: 8rem;
        }

        .error {
            color: #b00020;
        }

        code {
            background: #f4f4f4;
            padding: 0.15rem 0.35rem;
        }
    </style>
</head>
<body>
    <h1>Admin Password Hash Generator</h1>
    <p>Use this to create a value for the <code>admin_users.password_hash</code> column.</p>

    <form method="post">
        <label for="password">Password</label>
        <input
            id="password"
            name="password"
            type="text"
            value="<?= escape($password) ?>"
            autocomplete="off"
        >

        <button type="submit">Generate Hash</button>
    </form>

    <?php if ($error !== ''): ?>
        <p class="error"><?= escape($error) ?></p>
    <?php endif; ?>

    <?php if ($hash !== ''): ?>
        <label for="hash">Generated Hash</label>
        <textarea id="hash" readonly><?= escape($hash) ?></textarea>
        <p>Example SQL:</p>
        <textarea readonly>UPDATE admin_users SET password_hash = '<?= escape($hash) ?>' WHERE username = 'admin';</textarea>
    <?php endif; ?>
</body>
</html>
