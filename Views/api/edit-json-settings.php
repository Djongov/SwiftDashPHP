<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use App\Utilities\JsonSettingEditor;

$checks = new Checks($loginInfo, $_POST);

$checks->apiChecks();

try {
    // Process the JSON editor form submission
    $result = JsonSettingEditor::processJsonEditorSubmission($_POST);
    
    if ($result['success']) {
        Response::output($result['message'], 200);
    } else {
        Response::output($result['message'], 400);
    }
} catch (Exception $e) {
    // Log error for debugging
    error_log('JSON Editor API Error: ' . $e->getMessage());
    Response::output('An error occurred while processing the request: ' . $e->getMessage(), 500);
}
