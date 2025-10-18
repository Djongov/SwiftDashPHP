<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] === 'GET') :
    if (file_exists(dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . '.env')) {
        die('No work to be done here');
    }
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environment Configuration Setup</title>
    <!-- Note: Tailwind CSS compiled file is used here -->
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <style>
        .form-section {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .dark .form-section {
            background-color: #1f2937;
            border-color: #374151;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .dark .form-label {
            color: #d1d5db;
        }
        .form-input, .form-select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            outline: none;
        }
        .form-input:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .dark .form-input, .dark .form-select {
            background-color: #374151;
            color: white;
            border-color: #4b5563;
        }
        .form-checkbox {
            width: 1.125rem;
            height: 1.125rem;
            color: #2563eb;
            background-color: #f3f4f6;
            border-color: #d1d5db;
            border-radius: 0.25rem;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }
        .dark .form-checkbox {
            background-color: #374151;
            border-color: #4b5563;
        }
        .checkbox-wrapper {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background-color: #f9fafb;
            transition: background-color 0.2s, border-color 0.2s;
        }
        .dark .checkbox-wrapper {
            background-color: #111827;
            border-color: #374151;
        }
        .checkbox-wrapper:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
        }
        .dark .checkbox-wrapper:hover {
            background-color: #1f2937;
            border-color: #4b5563;
        }
        .checkbox-label {
            margin-left: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
        .dark .checkbox-label {
            color: #d1d5db;
        }
        .required-field::after {
            content: " *";
            color: #ef4444;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border: none;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            color: white;
            background-color: #2563eb;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .btn-primary:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white gradient-text mb-2">
                Environment Configuration Setup
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Configure your application environment settings
            </p>
        </div>

        <form id="env" class="space-y-6">
            <!-- Database Configuration -->
            <div class="form-section">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c0-2.21-1.79-4-4-4H4V7z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7v10c0 2.21-1.79 4-4 4"></path>
                    </svg>
                    Database Configuration
                </h2>
                
                <div class="form-group">
                    <label for="DB_DRIVER" class="form-label required-field">Database Driver</label>
                    <select id="DB_DRIVER" name="DB_DRIVER" class="form-select" required>
                        <option value="mysql">MySQL</option>
                        <option value="pgsql">PostgreSQL</option>
                        <option value="sqlite">SQLite</option>
                    </select>
                </div>

                <div id="db-fields">
                    <div class="form-group">
                        <label for="DB_SSL" class="form-label">SSL Connection</label>
                        <select id="DB_SSL" name="DB_SSL" class="form-select">
                            <option value="false">Disabled</option>
                            <option value="true">Enabled</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="DB_HOST" class="form-label required-field">Database Host</label>
                            <input type="text" id="DB_HOST" name="DB_HOST" class="form-input" required placeholder="localhost" value="localhost">
                        </div>

                        <div class="form-group">
                            <label for="DB_PORT" class="form-label required-field">Database Port</label>
                            <input type="number" id="DB_PORT" name="DB_PORT" class="form-input" value="3306" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="DB_USER" class="form-label required-field">Database User</label>
                            <input type="text" id="DB_USER" name="DB_USER" class="form-input" placeholder="root" value="root" required>
                        </div>

                        <div class="form-group">
                            <label for="DB_PASS" class="form-label required-field">Database Password</label>
                            <input type="password" id="DB_PASS" name="DB_PASS" class="form-input" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="DB_NAME" class="form-label required-field">Database Name</label>
                    <input type="text" id="DB_NAME" name="DB_NAME" class="form-input" value="dashboard" required>
                </div>
            </div>

            <!-- Authentication Options -->
            <div class="form-section">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-3a1 1 0 011-1h2.586l6.414-6.414a6 6 0 017.743-5.743z"></path>
                    </svg>
                    Authentication Methods
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Choose which authentication methods to enable for your application.
                </p>

                <div class="space-y-4">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="LOCAL_LOGIN_ENABLED" name="LOCAL_LOGIN_ENABLED" class="form-checkbox" checked>
                        <label for="LOCAL_LOGIN_ENABLED" class="checkbox-label">
                            <strong>Local Login</strong>
                            <span class="block text-xs text-gray-500">Username/password authentication</span>
                        </label>
                    </div>

                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="ENTRA_ID_LOGIN_ENABLED" name="ENTRA_ID_LOGIN_ENABLED" class="form-checkbox">
                        <label for="ENTRA_ID_LOGIN_ENABLED" class="checkbox-label">
                            <strong>Entra ID Login</strong>
                            <span class="block text-xs text-gray-500">Microsoft Azure AD authentication</span>
                        </label>
                    </div>

                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="MSLIVE_LOGIN_ENABLED" name="MSLIVE_LOGIN_ENABLED" class="form-checkbox">
                        <label for="MSLIVE_LOGIN_ENABLED" class="checkbox-label">
                            <strong>Microsoft Live Login</strong>
                            <span class="block text-xs text-gray-500">Microsoft Live account authentication</span>
                        </label>
                    </div>

                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="GOOGLE_LOGIN_ENABLED" name="GOOGLE_LOGIN_ENABLED" class="form-checkbox">
                        <label for="GOOGLE_LOGIN_ENABLED" class="checkbox-label">
                            <strong>Google Login</strong>
                            <span class="block text-xs text-gray-500">Google account authentication</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Additional Services -->
            <div class="form-section">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Additional Services
                </h2>

                <div class="space-y-4">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="SENDGRID" name="SENDGRID" class="form-checkbox">
                        <label for="SENDGRID" class="checkbox-label">
                            <strong>SendGrid Email Service</strong>
                            <span class="block text-xs text-gray-500">Enable email sending via SendGrid</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Create Environment File
                </button>
            </div>
        </form>
    </div>
</body>
</html>

<script src="/assets/js/create-env.js?<?php echo time()?>"></script>

<?php endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists(dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . '.env')) {
        die('No work to be done here');
    }

    // Validate required Azure Service Principal fields
    $requiredFields = [
        'DB_DRIVER',
        'DB_NAME',
    ];
    
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        http_response_code(400);
        die('Missing required Azure configuration fields: ' . implode(', ', $missingFields) . '. These fields are required for the application to access Azure resources.');
    }

    // Validate database fields based on driver type
    $dbDriver = $_POST['DB_DRIVER'] ?? 'mysql';
    if ($dbDriver !== 'sqlite') {
        $requiredDbFields = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_PORT'];
        $missingDbFields = [];
        
        foreach ($requiredDbFields as $field) {
            if (empty($_POST[$field])) {
                $missingDbFields[] = $field;
            }
        }
        
        if (!empty($missingDbFields)) {
            http_response_code(400);
            die('Missing required database configuration fields: ' . implode(', ', $missingDbFields) . '. These fields are required for ' . strtoupper($dbDriver) . ' database connection.');
        }
    }

    // Create the .env file
    $envContentArray = $_POST;

    if (isset($_POST['LOCAL_LOGIN_ENABLED']) && $_POST["LOCAL_LOGIN_ENABLED"] === 'on') {
        if (!extension_loaded('openssl')) {
            die('Enable openssl extension');
        }

        // Generate private key
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        // Create the keypair
        $res = openssl_pkey_new($config);

        if (!$res) {
            die('You need openssl installed on the web server apart from having the extension enabled');
        }

        // Get private key
        openssl_pkey_export($res, $privKey);

        // Get public key
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        // Base64 encode private and public keys
        $base64PrivateKey = base64_encode($privKey);
        $base64PublicKey = base64_encode($pubKey);

        $envContentArray['JWT_PUBLIC_KEY'] = $base64PublicKey;
        $envContentArray['JWT_PRIVATE_KEY'] = $base64PrivateKey;

        $envContentArray['LOCAL_LOGIN_ENABLED'] = 'true';
    } else {
        $envContentArray['LOCAL_LOGIN_ENABLED'] = 'false';
    }

    if (isset($_POST['GOOGLE_LOGIN_ENABLED']) && $_POST["GOOGLE_LOGIN_ENABLED"] === 'on') {
        $envContentArray['GOOGLE_LOGIN_ENABLED'] = 'true';
    } else {
        $envContentArray['GOOGLE_LOGIN_ENABLED'] = 'false';
    }

    if (isset($_POST['MSLIVE_LOGIN_ENABLED']) && $_POST["MSLIVE_LOGIN_ENABLED"] === 'on') {
        $envContentArray['MSLIVE_LOGIN_ENABLED'] = 'true';
    } else {
        $envContentArray['MSLIVE_LOGIN_ENABLED'] = 'false';
    }

    if (isset($_POST['ENTRA_ID_LOGIN_ENABLED']) && $_POST["ENTRA_ID_LOGIN_ENABLED"] === 'on') {
        $envContentArray['ENTRA_ID_LOGIN_ENABLED'] = 'true';
    } else {
        $envContentArray['ENTRA_ID_LOGIN_ENABLED'] = 'false';
    }

    if (isset($_POST['SENDGRID']) && $_POST["SENDGRID"] === 'on') {
        $envContentArray['SENDGRID_ENABLED'] = 'true';
    } else {
        $envContentArray['SENDGRID_ENABLED'] = 'false';
    }

    // Let's unset the ones that we don't want to be in the .env file
    unset($envContentArray['SENDGRID']);

    $envContent = '';

    foreach ($envContentArray as $key => $value) {
        $updatedValue = '';
        if ($value === 'true' || $value === 'false') {
            $updatedValue = $value;
        } else {
            $updatedValue = '"' . $value . '"';
        }
        $envContent .= $key . '=' . $updatedValue . '' . PHP_EOL;
    }

    $os = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'windows' : 'linux';

    if ($os === 'linux') {
        $envContent .= 'ACCESS_LOGS="/var/log/apache2"' . PHP_EOL;
    }

    $envFilePath = dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . '.env';

    $fileHandle = fopen($envFilePath, 'w');

    // Write the content to the file
    if ($fileHandle) {
        fwrite($fileHandle, $envContent);
        fclose($fileHandle);
        echo "The .env file has been created successfully.";
    } else {
        http_response_code(404);
        echo "Unable to create the .env file.";
    }
}
