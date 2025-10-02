<?php

declare(strict_types=1);

use App\Api\Checks;
use App\Api\Response;

$usernameArray = $loginInfo['usernameArray'] ?? [];
$loggedIn = $loginInfo['loggedIn'] ?? false;
$isAdmin = $loginInfo['isAdmin'] ?? false;

    $check = \App\RequireLogin::check(true);
    Response::output($check, 401);


Response::output($usernameArray, 400);

