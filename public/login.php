<?php

require_once(__DIR__ . '/../src/auth.php');
require_once(__DIR__ . '/../src/helper.php');

session_start();
if (is_logged_in()) {
    redirect('index.php');
}

$error = null;
if (isset($_SESSION['message_type']) && $_SESSION['message_type'] === 'success') {
    $success = $_SESSION['message'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
} else {
    $success = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid request.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $result = login($username, $password);

        if ($result['success'] === true) {
            redirect('index.php', $result['message'], 'success');
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Todo App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>

<body>

<?php require_once(__DIR__ . '/partials/navbar.php'); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                        <button type="submit" class="btn btn-primary">Login</button>
                        <a href="register.php" class="btn btn-link">Don't have an account?</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/partials/footer.php'); ?>

</body>

</html>