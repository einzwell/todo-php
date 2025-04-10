<?php

require_once(__DIR__ . '/../src/auth.php');
require_once(__DIR__ . '/../src/helper.php');
require_once(__DIR__ . '/../src/models/User.php');

use Todo\models\User;

session_start();

if (!is_logged_in()) {
    redirect('login.php', 'You must be logged in to access that page.', 'warning');
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$errors = [];
$messages = '';

switch ($_POST['_method'] ?? $_SERVER['REQUEST_METHOD']) {

    // Edit account
    case 'PUT':
        $_PUT = $_POST;
        if (!verify_csrf($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Invalid request.';
        }

        $new_username = $_PUT['new_username'];
        $old_password = $_PUT['old_password'];
        $new_password = $_PUT['new_password'];
        $confirm_new_password = $_PUT['new_password_confirm'];

        $user = new User($_SESSION['username'], $old_password);
        if ($user->authenticate() === true) {
            if ($new_username === $_SESSION['username'] && empty($new_password)) {
                $errors[] = 'No changes were made.';
            } elseif (!empty($confirm_new_password) && $new_password !== $confirm_new_password) {
                $errors[] = 'New password and confirmation do not match.';
            } else {
                if ($user->update($new_username, $new_password ?: $old_password) === true) {
                    $_SESSION['username'] = $user->username;
                    $success = 'Account updated successfully.';
                    redirect('account.php', 'User updated successfully.', 'success');
                } else {
                    $errors[] = 'Error updating user: ' . $user->error;
                }
            }
        } else {
            $errors[] = 'Old password is incorrect.';
        }
        break;

    // Delete account
    case 'DELETE':
        if (!verify_csrf($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Invalid request.';
        }

        $user = new User($_SESSION['username'], $_POST['password']);
        if ($user->authenticate() === true) {
            if ($user->delete() === true) {
                logout();
                redirect('login.php', 'Your account has been deleted. Goodbye!', 'success');
            } else {
                $errors[] = 'Error deleting user: ' . $user->error;
            }
        } else {
            $errors[] = 'Password is incorrect.';
        }
        break;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - Todo App</title>
    <link href="assets/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body>
<?php require_once(__DIR__ . '/partials/navbar.php');  ?>

<div class="container">

    <div class="row mb-3 mt-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="text-truncate me-2">Account Settings</b></h1>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?php echo $error; ?></p>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <p class="mb-0"><?php echo $success; ?></p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="account.php">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">

        <div class="mb-3">
            <label for="username" class="form-label"><b>Username</b></label>
            <input type="text" class="form-control" id="username" name="new_username" value="<?= $username ?>" required>
            <div class="form-text">Leave unchanged if you don't want to update your username</div>
        </div>

        <div class="mb-3">
            <label for="old_password" class="form-label"><b>Current Password</b></label>
            <input type="password" class="form-control" id="old_password" name="old_password" required>
        </div>

        <div class="mb-3">
            <label for="new_password" class="form-label"><b>New Password</b></label>
            <input type="password" class="form-control" id="new_password" name="new_password">
        </div>

        <div class="mb-3">
            <label for="new_password_confirm" class="form-label"><b>Confirm New Password</b></label>
            <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm">
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmUpdateModal">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <button type="reset" class="btn btn-secondary">
                <i class="fas fa-undo"></i> Reset
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                <i class="fas fa-trash"></i> Delete Account
            </button>
        </div>

        <div class="modal fade" id="confirmUpdateModal" tabindex="-1" aria-labelledby="confirmUpdateModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmUpdateModalLabel"><b>Confirm Account Update</b></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text">Are you sure you want to save changes to your account?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Delete Account Form -->
    <form id="deleteAccountForm" method="post" action="account.php">
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">

        <!-- Confirm Delete Modal -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel"><b>Confirm Account Deletion</b></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-danger">Warning: This action cannot be undone.</p>
                        <div class="mb-3">
                            <label for="password" class="form-label">Enter your password to confirm:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete My Account</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once(__DIR__ . '/partials/footer.php'); ?>

<script>
    // Auto-dismiss alerts after 10 seconds
    $(document).ready(function () {
        setTimeout(function () {
            $('.alert').alert('close');
        }, 10 * 1000);
    });
</script>
</body>

</html>