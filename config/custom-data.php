<?php

declare(strict_types=1);

/**
 * Custom Data Configuration
 * 
 * This file injects project-specific data into all routes.
 * The callable receives $loginInfo from RequireLogin::check()
 * 
 * The returned data is available as $customData in all controllers and views.
 */

return function(array $loginInfo) {
    // Initialize the character array
    $characterArray = [];    
    return [
        // Character data for the current user
        'hasCharacter' => !empty($characterArray),
        'characterArray' => $characterArray,
    ];
};
