<?php declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use Components\Html;

$checks = new Checks($vars, $_POST);

$checks->apiChecks();

if (!isset($_POST['data'])) {
    return Response::output('Data is required', 400);
}

//Response::output(base64_encode($_POST['data']));
echo '<div class="container break-words">' . Html::code(base64_encode($_POST['data'])) . '</div>';
