<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] === 'GET') :
    if (file_exists(dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . '.env')) {
        die('No work to be done here');
    }
    ?>

<h2>This is a form to help you create a .env file automatically</h2>
<form id="env">
<div id="db-fields">
        <label for="DB_SSL">DB_SSL:</label>
        <select id="DB_SSL" name="DB_SSL">
            <option value="false">false</option>
            <option value="true">true</option>
        </select><br><br>

        <label for="DB_HOST">DB_HOST:</label>
        <input type="text" id="DB_HOST" name="DB_HOST" required placeholder="localhost" value="localhost"><br><br>

        <label for="DB_USER">DB_USER:</label>
        <input type="text" id="DB_USER" name="DB_USER" placeholder="root" value="root" required><br><br>

        <label for="DB_PASS">DB_PASS:</label>
        <input type="password" id="DB_PASS" name="DB_PASS" required><br><br>

        <label for="DB_PORT">DB_PORT:</label>
        <input type="number" id="DB_PORT" name="DB_PORT" value="3306" required><br><br>
    </div>
        <label for="DB_PORT">DB_NAME:</label>
        <input type="text" id="DB_NAME" name="DB_NAME" value="dashboard" required><br><br>
    <label for="DB_DRIVER">DB_DRIVER:</label>
    <select id="DB_DRIVER" name="DB_DRIVER">
        <option value="mysql">MySQL</option>
        <option value="pgsql">PostgreSQL</option>
        <!--<option value="sqlsrv">SQL Server</option>-->
        <option value="sqlite">SQLite</option>
    </select><br><br>

    
    <label for="ENTRA_ID_LOGIN_ENABLED">Entra ID Login (formerly Azure AD)</label>
        <input type="checkbox" id="ENTRA_ID_LOGIN_ENABLED" name="ENTRA_ID_LOGIN_ENABLED">
    <br><br>

    <label for="MSLIVE_LOGIN_ENABLED">Microsoft LIVE Login</label>
        <input type="checkbox" id="MSLIVE_LOGIN_ENABLED" name="MSLIVE_LOGIN_ENABLED">
    <br><br>

    <label for="GOOGLE_LOGIN_ENABLED">Google Login
        <input type="checkbox" id="GOOGLE_LOGIN_ENABLED" name="GOOGLE_LOGIN_ENABLED">
    </label>
    <br><br>

    <input type="checkbox" id="LOCAL_LOGIN_ENABLED" name="LOCAL_LOGIN_ENABLED" checked></label>
    <label for="LOCAL_LOGIN_ENABLED">Local Login<br><br>

    <label for="SENDGRID">SENDGRID
        <input type="checkbox" id="SENDGRID" name="SENDGRID">
    </label>
    <br><br>
    

    <button type="submit">Submit</button>
</form>

<script src="/assets/js/create-env.js?<?php echo time()?>"></script>

<?php endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists(dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . '.env')) {
        die('No work to be done here');
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
