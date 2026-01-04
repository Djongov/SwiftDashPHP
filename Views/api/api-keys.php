<?php

declare(strict_types=1);

use App\Api\Checks;
use App\Api\Response;
use Models\APIKeys;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $checks = new Checks($loginInfo, $_POST);

    $checks->apiChecks();

    $checks->checkParams([], $_POST);

    $apiKeyModel = new APIKeys();

    // First let's check if the user already has an API key
    try {
        $existingKey = $apiKeyModel->getApiKeyByNote($loginInfo['usernameArray']['username']);
    } catch (Exception $e) {
        if (ERROR_VERBOSE) {
            Response::output('Failed to fetch existing API key: ' . $e->getMessage(), 500);
        } else {
            Response::output('Failed to fetch existing API key', 500);
        }
    }

    if ($existingKey) {
        Response::output(['api_key' => $existingKey]);
    }

    $data = [
        'note' => $loginInfo['usernameArray']['username'],
        'type' => 'read',
        'created_by' => $loginInfo['usernameArray']['username'],
        'owner' => $loginInfo['usernameArray']['username'],
        'daily_execution_limit' => FREE_TIER_DAILY_EXECUTION_LIMIT ?? 1000,
    ];

    // If no existing key, create a new one
    try {
        $save = $apiKeyModel->create($data);
        // Ok we get back an id from create, we need to fetch the api_key value to return it
        try {
            $newApiKey = $apiKeyModel->get((int) $save);
        } catch (Exception $e) {
            if (ERROR_VERBOSE) {
                Response::output('Failed to fetch new API key: ' . $e->getMessage(), 500);
            } else {
                Response::output('Failed to fetch new API key', 500);
            }
        }

        Response::output(['api_key' => $newApiKey['api_key']]);
    } catch (Exception $e) {
        if (ERROR_VERBOSE) {
            Response::output('Failed to create API key: ' . $e->getMessage(), 500);
        } else {
            Response::output('Failed to create API key', 500);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    if (!empty($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        Response::output('body must be empty in DELETE requests', 400);
    }
    // Let's check if the csrf token is passed as a query string in the DELETE request
    if (!isset($_GET['csrf_token'])) {
        Response::output('missing csrf token', 401);
    }

    $checks = new Checks($loginInfo, $_GET);

    $checks->apiChecksDelete($_GET['csrf_token']);

    $apiKeyModel = new APIKeys();

    // First let's check if the user has an API key
    try {
        $existingKey = $apiKeyModel->getApiKeyByNote($loginInfo['usernameArray']['username']);
    } catch (Exception $e) {
        if (ERROR_VERBOSE) {
            Response::output('Failed to fetch existing API key: ' . $e->getMessage(), 500);
        } else {
            Response::output('Failed to fetch existing API key', 500);
        }
    }

    if (!$existingKey) {
        Response::output('No API key found for this user', 404);
    }

    // If the user has an API key, delete it
    try {
        $delete = $apiKeyModel->delete((int) $existingKey['id']);
        Response::output('API key deleted successfully');
    } catch (Exception $e) {
        if (ERROR_VERBOSE) {
            Response::output('Failed to delete API key: ' . $e->getMessage(), 500);
        } else {
            Response::output('Failed to delete API key', 500);
        }
    }
}

Response::output('Invalid api action', 400);
