<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use Models\BasicModel;
use App\Utilities\IP;

function formatIp(string $ip): string
{
    // Now let's format the IP to CIDR notation
    $ipExplode = explode('/', $ip);
    $ip = $ipExplode[0];
    // First run through the validation
    if (!IP::isValidIp($ip)) {
        throw new Exception('invalid IP address format - ' . $ip . '', 400);
    }
    if (!isset($ipExplode[1])) {
        $mask = 32;
    } else {
        $mask = $ipExplode[1];
    }
    return $ip . '/' . $mask;
}

// This is the API view for the firewall. It allows to add, update, delete and get IPs from the firewall

// api/firewall GET, accepts a "cidr" parameter in the query string. If no query string provided, returns all IPs in the firewall table.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // This endpoint is for creating a new local user. Cloud users are create in /auth-verify

    $checks = new Checks($loginInfo, $_GET);
    $checks->apiChecksNoCSRF();

    if (empty($_GET)) {
        // Return all firewall entries
        $firewall = new BasicModel('firewall');

        // Do an admin check for this one
        $checks->adminCheck();

        try {
            $result = $firewall->getAll();
            Response::output($result);
        } catch (Exception $e) {
            Response::output($e->getMessage(), (int) $e->getCode());
        }
    }

    $ip = $_GET['cidr'] ?? Response::output('missing cidr parameter', 400);

    try {
        $ip = formatIp($ip);
    } catch (Exception $e) {
        Response::output($e->getMessage(), (int) $e->getCode());
    }

    $firewall = new BasicModel('firewall');

    // need to set the main column to ip_cidr for the exists check
    $firewall->setter('firewall', 'ip_cidr');

    try {
        $result = $firewall->get($ip);
        Response::output($result);
    } catch (Exception $e) {
        Response::output($e->getMessage(), (int) $e->getCode());
    }
}

// api/firewall POST, accepts form data with the "cidr" parameter and optional "comment". The user making the request is taken from the router data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checks = new Checks($loginInfo, $_POST);
    $checks->apiChecks();

    $checks->checkParams(['ip_cidr'], $_POST);

    $comment = $_POST['comment'] ?? '';

    $createdBy = $loginInfo['usernameArray']['username'];

    $ip = $_POST['ip_cidr'];

    try {
        $ip = formatIp($ip);
    } catch (Exception $e) {
        Response::output($e->getMessage(), (int) $e->getCode());
    }

    $save = new BasicModel('firewall');

    $data = [
        'ip_cidr' => $ip,
        'comment' => $comment,
    ];

    try {
        $result = $save->create($data, $createdBy);
        Response::output($result);
    } catch (Exception $e) {
        Response::output($e->getMessage(), (int) $e->getCode());
    }
}

// api/firewall/{id} PUT, accepts json body wit the data to update, id in the path. The user making the request is taken from the router data
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Check if content type is json
    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        Response::output('content type must be application/json', 400);
        exit();
    }
    // Let's catch php input stream
    $data = Checks::jsonBody();

    // Also the router info should bring us the id
    if (!isset($routeInfo[2]['id'])) {
        Response::output('missing id paramter', 400);
        exit();
    }

    if (isset($data['ip_cidr'])) {
        try {
            $data['ip_cidr'] = formatIp($data['ip_cidr']);
        } catch (Exception $e) {
            Response::output($e->getMessage(), (int) $e->getCode());
        }
    }

    $update = new BasicModel('firewall');

    try {
        $result = $update->update($data, $routeInfo[2]['id']);
        Response::output($result);
    } catch (Exception $e) {
        Response::output($e->getMessage(), (int) $e->getCode());
    }
}

// api/firewall/{id}?csrf_token={} DELETE, empty body, param in path, csrf token in query string. The user making the request is taken from the router data
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Check if body is empty
    if (!empty($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        Response::output('body must be empty in DELETE requests', 400);
        exit();
    }
    // Let's check if the csrf token is passed as a query string in the DELETE request
    if (!isset($_GET['csrf_token'])) {
        Response::output('missing csrf token', 401);
        exit();
    }

    // Also the router info should bring us the id
    if (!isset($routeInfo[2]['id'])) {
        Response::output('missing id parameter', 400);
        exit();
    }

    $checks = new Checks($loginInfo, $_GET);

    $checks->checkCSRFDelete($_GET['csrf_token']);

    $id = $routeInfo[2]['id'];

    $delete = new BasicModel('firewall');

    try {
        $result = $delete->delete($id);
        Response::output($result);
    } catch (Exception $e) {
        Response::output($e->getMessage(), (int) $e->getCode());
    }
}

Response::output('Invalid api action', 400);
