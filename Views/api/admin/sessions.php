<?php

declare(strict_types=1);

use App\Api\Response;
use App\Api\Checks;
use Models\Sessions;
use Components\Alerts;

// Admin check
if (!$isAdmin) {
    Response::output('You are not authorized to perform this action', 403);
}

$checks = new Checks($loginInfo, $_POST);

$expectedParams = ['api-action'];

$checks->checkParams($expectedParams, $_POST);

$checks->adminCheck();

// Check if using database sessions
if (SESSION_STORAGE !== 'database') {
    Response::output('Session management is only available with database session storage', 400);
}

$action = $_POST['api-action'] ?? $_GET['api-action'] ?? '';

try {
    $sessionsModel = new Sessions();
    
    switch ($action) {
        case 'get-sessions':
            $sessions = $sessionsModel->getAll();
            
            if (empty($sessions)) {
                echo Alerts::info('No active sessions found');
            } else {
                $currentSessionId = session_id();
                
                // Custom table with action buttons
                echo '<div class="overflow-x-auto">';
                echo '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">';
                echo '<thead class="bg-gray-50 dark:bg-gray-800">';
                echo '<tr>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Session ID</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Activity</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Expires At</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';
                
                foreach ($sessions as $session) {
                    $sessionId = htmlspecialchars($session['id']);
                    $shortId = substr($sessionId, 0, 16) . '...';
                    $isCurrent = ($sessionId === $currentSessionId);
                    
                    // Parse session data to get user info
                    $userData = $sessionsModel->parseUsername($session['data'], $isCurrent);
                    
                    echo '<tr class="' . ($isCurrent ? 'bg-blue-50 dark:bg-blue-900/20' : '') . '">';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100">';
                    echo $shortId;
                    if ($isCurrent) {
                        echo ' <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">Current</span>';
                    }
                    echo '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' . $userData . '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' . htmlspecialchars($session['last_activity']) . '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' . htmlspecialchars($session['expires_at']) . '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm">';
                    
                    if ($isCurrent) {
                        echo '<span class="text-gray-400 dark:text-gray-500">Cannot revoke current session</span>';
                    } else {
                        // Use Forms component for CSP compliance
                        $revokeFormArray = [
                            'inputs' => [
                                'hidden' => [
                                    [
                                        'name' => 'api-action',
                                        'value' => 'revoke-session'
                                    ],
                                    [
                                        'name' => 'session_id',
                                        'value' => $sessionId
                                    ]
                                ]
                            ],
                            'theme' => $theme,
                            'action' => '/api/admin/sessions',
                            'resultType' => 'text',
                            'reloadOnSubmit' => false,
                            'submitButton' => [
                                'text' => 'Revoke',
                                'size' => 'small',
                                'color' => 'red'
                            ],
                            'confirmMessage' => 'Are you sure you want to revoke this session? The user will be logged out.'
                        ];
                        echo \Components\Forms::render($revokeFormArray);
                    }
                    
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            }
            break;
            
        case 'revoke-session':
            $sessionId = $_POST['session_id'] ?? '';
            
            if (empty($sessionId)) {
                Response::output('Session ID is required', 400);
            }
            
            $result = $sessionsModel->delete($sessionId, $loginInfo['usernameArray']['username'] ?? Response::output('username not found', 500));
            
            if ($result) {
                Response::output(['success' => true, 'message' => 'Session revoked successfully'], 200);
            } else {
                Response::output(['success' => false, 'message' => 'Session not found'], 404);
            }
            break;
            
        case 'clear-all-sessions':
            $currentSessionId = session_id();
            $deletedCount = $sessionsModel->deleteAllExcept($currentSessionId, $username);
            
            Response::output([
                'success' => true, 
                'message' => "Cleared $deletedCount sessions (kept your current session)"
            ], 200);
            break;
            
        default:
            Response::output('Invalid action', 400);
    }
    
} catch (\Exception $e) {
    Response::output('Error: ' . $e->getMessage(), 500);
}
