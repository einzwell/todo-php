<?php

function navbar(bool $logged_in = false): string {
    $csrf = generate_csrf();

    $button_list = $logged_in ?
        <<<HTML
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="account.php">Account</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="about.php">About</a>
                </li>
                <li class="nav-item ms-2" style="text-align: right">
                    <form action="index.php" method="POST">
                        <input type="hidden" name="csrf_token" value="$csrf">
                        <button type="submit" class="btn btn-danger" name="_logout" value="true">Logout</button>
                    </form>
                </li>
            </ul>
        HTML :
        <<<HTML
             <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="register.php">Register</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="about.php">About</a>
                </li>
            </ul>
        HTML;

    return <<<HTML
        <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">Todo App</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    $button_list
                </div>
            </div>
        </nav>
    HTML;


}

echo navbar(isset($_SESSION['username']) && isset($_SESSION['user_id']));