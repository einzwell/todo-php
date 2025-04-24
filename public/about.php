<?php

session_start();
require_once(__DIR__ . '/../src/helper.php');
require_once(__DIR__ . '/../src/auth.php');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Todo App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>

<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>

<div class="container pt-5">
    <div class="row mb-3 mt-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="me-2">About</h1>
            </div>
        </div>
    </div>

    <div>
        <p>
            <b>Todo App</b> is a simple web application that allows users to create, read, update, and delete tasks (a.k.a. todos).
            Written in PHP and utilising MariaDB, It is designed to be a lightweight and easy-to-use tool for managing personal tasks and to-do lists.
        </p>

        <p>
            This application is built as a mid-semester project for the course "Web-Based Programming" at Bina Nusantara University
            in 2025.
        </p>

        <p>
            For more information, please check out the <a target="_blank" rel="noopener noreferrer" href="https://github.com/einzwell/todo-php">GitHub repository.</a>
        </p>

        <h3 class="pt-2">Credits</h3>
        <p>
            This app is created by Yoga Smara (<a target="_blank" rel="noopener noreferrer" href="https://einzwell.dev">@einzwell</a>)
    </div>
</div>

<?php require_once(__DIR__ . '/partials/footer.php'); ?>

</body>