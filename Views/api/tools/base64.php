<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use Components\Html;

$checks = new Checks($loginInfo, $_POST);

$checks->apiChecks();

if (!isset($_POST['encode']) && !isset($_POST['decode'])) {
    Response::output('encode or decode param is required', 400);
}

if (isset($_POST['encode']) && !isset($_POST['decode'])) {
    if (!is_string($_POST['encode'])) {
        Response::output('encode param must be a string', 400);
    }
    echo '<div class="container break-words">' . Html::code(base64_encode($_POST['encode'])) . '</div>';
}

if (isset($_POST['decode']) && !isset($_POST['encode'])) {
    if (!is_string($_POST['decode'])) {
        Response::output('decode param must be a string', 400);
    }
    echo '<div class="container break-words">' . Html::code(htmlspecialchars(base64_decode($_POST['decode']))) . '</div>';
}
