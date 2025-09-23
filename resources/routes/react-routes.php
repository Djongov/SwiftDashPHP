<?php

declare(strict_types=1);

// React Dashboard route
$router->addRoute('GET', '/react-dashboard', function () use ($viewsFolder, $metadataArray) {
    // Check if user is authenticated
    if (!isset($_SESSION['username'])) {
        http_response_code(401);
        header('Location: /login');
        exit;
    }

    $metadata = $metadataArray['main'];
    $metadata['title'] = 'React Dashboard';
    
    extract($metadata);
    
    // Set user data for React
    $usernameArray = [
        'username' => $_SESSION['username'],
        'name' => $_SESSION['name'] ?? $_SESSION['username'],
        'picture' => $_SESSION['picture'] ?? null,
        'isAdmin' => $_SESSION['isAdmin'] ?? false
    ];

    require $viewsFolder . '/react/layout.php';
});

// React Login route
$router->addRoute('GET', '/react-login', function () use ($viewsFolder, $metadataArray) {
    $metadata = $metadataArray['main'];
    $metadata['title'] = 'React Login';
    
    extract($metadata);
    
    require $viewsFolder . '/react/layout.php';
});

// API route for React authentication check
$router->addRoute('GET', '/api/auth/check', function () {
    header('Content-Type: application/json');
    
    $response = [
        'success' => false,
        'authenticated' => false,
        'data' => null,
        'session_info' => [
            'session_id' => session_id(),
            'session_status' => session_status(),
            'cookie_params' => session_get_cookie_params()
        ],
        'timestamp' => date('c'),
        'server_time' => time()
    ];
    
    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $response['success'] = true;
        $response['authenticated'] = true;
        $response['data'] = [
            'username' => $_SESSION['username'],
            'name' => $_SESSION['name'] ?? $_SESSION['username'],
            'picture' => $_SESSION['picture'] ?? null,
            'isAdmin' => $_SESSION['isAdmin'] ?? false,
            'theme' => $_SESSION['theme'] ?? COLOR_SCHEME,
            'lang' => $_SESSION['lang'] ?? DEFAULT_LANG,
            'login_time' => $_SESSION['login_time'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'permissions' => $_SESSION['permissions'] ?? [],
            'session_lifetime' => ini_get('session.gc_maxlifetime')
        ];
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        // Add session security info
        $response['session_info']['secure'] = [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'session_fingerprint' => $_SESSION['session_fingerprint'] ?? null
        ];
    } else {
        $response['error'] = 'Not authenticated';
        $response['debug'] = [
            'session_exists' => isset($_SESSION),
            'session_vars' => array_keys($_SESSION ?? []),
            'cookies' => array_keys($_COOKIE ?? [])
        ];
    }
    
    echo json_encode($response);
});

// API route for React login
$router->addRoute('POST', '/api/auth/login', function () {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    // Basic validation
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Username and password are required'
        ]);
        return;
    }

    // Here you would normally validate against your user database
    // For demo purposes, we'll use a simple check
    if ($username === 'admin' && $password === 'password') {
        // Set session data
        $_SESSION['username'] = $username;
        $_SESSION['name'] = 'Administrator';
        $_SESSION['isAdmin'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['theme'] = COLOR_SCHEME;
        $_SESSION['lang'] = DEFAULT_LANG;
        $_SESSION['permissions'] = ['read', 'write', 'admin'];
        
        // Create session fingerprint for security
        $_SESSION['session_fingerprint'] = hash('sha256',
            $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . session_id()
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'username' => $username,
                'name' => 'Administrator',
                'isAdmin' => true,
                'theme' => COLOR_SCHEME,
                'lang' => DEFAULT_LANG,
                'login_time' => $_SESSION['login_time'],
                'permissions' => $_SESSION['permissions']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid credentials',
            'debug' => [
                'provided_username' => $username,
                'expected_username' => 'admin',
                'password_provided' => !empty($password)
            ]
        ]);
    }
});

// API route for React logout
$router->addRoute('POST', '/api/auth/logout', function () {
    header('Content-Type: application/json');
    
    $logoutData = [
        'was_authenticated' => isset($_SESSION['username']),
        'username' => $_SESSION['username'] ?? null,
        'session_duration' => isset($_SESSION['login_time'])
            ? time() - $_SESSION['login_time']
            : null,
        'logout_time' => time()
    ];
    
    // Clear specific session variables first
    $sessionVars = ['username', 'name', 'isAdmin', 'login_time', 'last_activity',
                   'theme', 'lang', 'permissions', 'session_fingerprint'];
    
    foreach ($sessionVars as $var) {
        if (isset($_SESSION[$var])) {
            unset($_SESSION[$var]);
        }
    }
    
    // Destroy session completely
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'data' => $logoutData
    ]);
});

// API route for dashboard stats (demo data)
$router->addRoute('GET', '/api/dashboard/stats', function () {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['username'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Not authenticated'
        ]);
        return;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'users' => 15,
            'apiKeys' => 3,
            'recentActivity' => [
                [
                    'action' => 'User login',
                    'user' => 'admin',
                    'timestamp' => date('Y-m-d H:i:s')
                ],
                [
                    'action' => 'API key created',
                    'user' => 'admin',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                ],
                [
                    'action' => 'Data export',
                    'user' => 'admin',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours'))
                ]
            ]
        ]
    ]);
});

// API route for CSRF token
$router->addRoute('GET', '/api/csrf-token', function () {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    echo json_encode([
        'success' => true,
        'token' => $_SESSION['csrf_token']
    ]);
});