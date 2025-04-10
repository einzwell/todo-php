<?php

require_once(__DIR__ . '/models/User.php');
use Todo\Models\User;
generate_csrf();

function register($username, $password, $confirm_password): array {
    // Sanity check & validation
    if (empty($username) || empty($password) || empty($confirm_password)) {
        return [
            'success' => false,
            'message' => 'All fields are required.'
        ];
    } elseif (strlen($username) > 254) {
        return [
            'success' => false,
            'message' => 'Username must be within 254 characters.'
        ];
    } elseif (!preg_match("/^[a-zA-Z0-9_]*$/", $username)) {
        return [
            'success' => false,
            'message' => 'Username can only contain letters, numbers, and underscores.'
        ];
    } elseif ($password !== $confirm_password) {
        return [
            'success' => false,
            'message' => 'Passwords do not match.'
        ];
    }

    $user = new User($username, $password);
    if ($user->create() === false) {
        return [
            'success' => false,
            'message' => $user->error
        ];
    } else {
        return [
            'success' => true,
            'message' => 'User registered successfully.'
        ];
    }
}

function login($username, $password): array {
    // Sanity check & validation
    if (empty($username) || empty($password) || strlen($username) > 254 || !preg_match("/^[a-zA-Z0-9_]*$/", $username)) {
        return [
            'success' => false,
            'message' => 'Invalid username or password.'
        ];
    }

    $user = new User($username, $password);
    if ($user->authenticate() === false) {
        return [
            'success' => false,
            'message' => $user->error
        ];
    } else {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;

        return [
            'success' => true,
            'message' => 'User logged in successfully.'
        ];
    }
}

function logout(): array {
    // Sanity check
    if (!isset($_SESSION['user_id'])) {
        return [
            'success' => false,
            'message' => 'User is not logged in.'
        ];
    }

    unset($_SESSION);
    session_destroy();

    return [
        'success' => true,
        'message' => 'User logged out successfully.'
    ];
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function get_current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function generate_csrf(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $CSRF_token = sha1(openssl_random_pseudo_bytes(32));
        $_SESSION['csrf_token'] = $CSRF_token;
    } else {
        $CSRF_token = $_SESSION['csrf_token'];
    }
    return $CSRF_token;
}

function verify_csrf($csrf_token = NULL): bool {
    $csrf_token = $csrf_token ?? $_POST['csrf_token'];
    return hash_equals($_SESSION['csrf_token'], $csrf_token);
}

function require_login(): void {
    if (!is_logged_in()) {
        redirect('login.php', 'You must be logged in to access that page.', 'warning');
    }
}