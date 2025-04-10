<?php

use JetBrains\PhpStorm\NoReturn;

#[NoReturn]
function redirect(string $location, ?string $message = null, ?string $message_type = null): void {
    if (isset($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $message_type;
    }
    header('Location: ' . $location);
    exit();
}

function display_message(): string {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];

        unset($_SESSION['message']);
        unset($_SESSION['message_type']);

        return <<<EOS
            <div class="alert alert-$message_type">
                $message
            </div>
        EOS;
    }
    return '';
}