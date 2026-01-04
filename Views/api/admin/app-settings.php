<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use Models\AppSettings;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request
    // e.g., fetch app settings
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $checks = new Checks($loginInfo, $_POST);

    $checks->checkParams(['name', 'value', 'type'], $_POST);

    $checks->apiAdminChecks();

    try {
        $appSettingModel = new AppSettings();
        
        // Get the user who is creating the setting
        $owner = $loginInfo['usernameArray']['username'] ?? 'system';
        $adminSetting = isset($_POST['admin_setting']) ? (bool)$_POST['admin_setting'] : false;
        $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : null;

        $data = [
            'name' => htmlspecialchars($_POST['name']),
            'value' => htmlspecialchars($_POST['value']),
            'type' => htmlspecialchars($_POST['type'])
        ];
        
        $appSetting = $appSettingModel->create($data,
            $owner,
            $adminSetting,
            $description
        );
        Response::output('app setting created successfully with ID ' . $appSetting, 201);
    } catch (Exception $e) {
        if (ERROR_VERBOSE) {
            Response::output($e->getMessage(), $e->getCode());
        } else {
            Response::output('An error occurred while creating App Setting', 500);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Handle PUT request - update an existing app setting
    
    // Parse JSON body for PUT requests
    $putData = json_decode(file_get_contents('php://input'), true);
    
    if (!$putData) {
        Response::output('Invalid JSON data', 400);
    }
    
    $checks = new Checks($loginInfo, $putData);
    
    $checks->checkParams(['id', 'name', 'value', 'type'], $putData);
    
    $checks->apiAdminChecks();
    
    $appSettingModel = new AppSettings();
    
    // Prepare the data array for update
    $updateData = [
        'name' => htmlspecialchars($putData['name']),
        'value' => htmlspecialchars($putData['value']),
        'type' => htmlspecialchars($putData['type'])
    ];
    
    // Include owner if provided
    if (isset($putData['owner'])) {
        $updateData['owner'] = htmlspecialchars($putData['owner']);
    }
    
    // Include admin_setting if provided
    if (isset($putData['admin_setting'])) {
        $updateData['admin_setting'] = $putData['admin_setting'] ? 1 : 0;
    }
    
    // Include description if provided
    if (isset($putData['description'])) {
        $updateData['description'] = htmlspecialchars($putData['description']);
    }
    
    // Get the user who is updating
    $updatedBy = $loginInfo['usernameArray']['username'] ?? Response::output('Unauthorized', 401);
    
    try {
        // Update the setting - let exceptions propagate
        $updated = $appSettingModel->update($updateData, (int)$putData['id'], $updatedBy);
        if ($updated) {
            Response::output('Setting updated successfully', 200);
        } else {
            Response::output('No changes made to the setting', 409);
        }
        
    } catch (Exception $e) {
        if (ERROR_VERBOSE) {
            Response::output($e->getMessage(), $e->getCode() ?: 500);
        } else {
            Response::output('An error occurred while updating App Setting', 500);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Handle DELETE request
    // e.g., delete an app setting
}

Response::output('unknown action', 400);
