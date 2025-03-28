<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use App\Database\DB;
use App\Security\Firewall;

Firewall::activate();

$checks = new Checks($loginInfo, $_POST);

// Perform the API checks
$checks->apiAdminChecks();

// Awaiting parameters
$allowedParams = ['domain', 'csrf_token'];

// Check if the required parameters are present
$checks->checkParams($allowedParams, $_POST);

$domain = htmlspecialchars($_POST['domain']);

$domain = trim($domain);

// Check if domain is valid
if (!filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
    Response::output('Invalid domain');
}

// Strip the csrf_token from the POST array and check if we have such a column in the firewall table
unset($_POST['csrf_token']);

$db = new DB();

$pdo = $db->getConnection();

$stmt = $pdo->prepare('SELECT id FROM csp_approved_domains WHERE domain = ?');

$stmt->execute([$domain]);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rowCount = count($rows);

if ($rowCount > 0) {
    echo Response::output('Domain already exists', 409);
}

$stmt = $pdo->prepare('INSERT INTO csp_approved_domains (domain, created_by) VALUES (?,?)');

$stmt->execute([$domain, $loginInfo['usernameArray']['username']]);

$rowCount = $stmt->rowCount();

if ($rowCount === 1) {
    Response::output('Domain added');
} else {
    Response::output('Domain not added', 500);
}
