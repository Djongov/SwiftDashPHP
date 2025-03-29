<?php

declare(strict_types=1);

use Controllers\UserController;

$controller = new UserController();

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->getUser($routeInfo, $vars);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->createUser($_POST);
}

// Handle PUT requests
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $controller->updateUser($routeInfo, $loginInfo);
}

// Handle DELETE requests
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $controller->deleteUser($routeInfo, $loginInfo);
}
