<?php declare(strict_types=1);

use App\Api\Response;
use App\Authentication\JWT;
use App\Authentication\AccessToken;

if (isset($_POST['error'], $_POST['error_subcode'], $_POST['state'], $_POST['canary'])) {
    header('Location: /');
}

if (isset($_POST['error'], $_POST['error_description'])) {
    if (str_contains($_POST['error'], 'consent_required')) {
        // Send an Authorization request if the error is AADSTS65001 (consent_required)
        $data = [
            'client_id' => ENTRA_ID_CLIENT_ID,
            'response_type' => 'code',
            'redirect_uri' => $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] , // redirect back to the same page
            'scope' => 'https://graph.microsoft.com/user.read',
            'response_mode' => 'form_post',
            'state' => $_POST['state'],
            'nonce' => $_SESSION['nonce'],
            'prompt' => 'consent',
            'login_hint' => $username
        ];

        header('Location: ' . ENTRA_ID_OAUTH_URL . http_build_query($data));
        exit();
    }
    if (str_contains($_POST['error'], 'login_required')) {
        // Send an Authorization request if the error is AADSTS50058 (login_required)
        // $data = [
        //     'client_id' => ENTRA_ID_CLIENT_ID,
        //     'response_type' => 'code',
        //     'redirect_uri' => $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] , // redirect back to the same page
        //     'scope' => 'https://graph.microsoft.com/user.read',
        //     'response_mode' => 'form_post',
        //     'state' => $_POST['state'],
        //     'nonce' => $_SESSION['nonce'],
        //     'prompt' => 'login',
        //     'login_hint' => $username
        // ];

        // header('Location: ' . ENTRA_ID_OAUTH_URL . http_build_query($data));
        // exit();
        Response::output("App Registration Error: " . $_POST['error'] . " with Description: " . $_POST['error_description']);
    }

    Response::output("Azure Error: " . $_POST['error'] . " with Description: " . $_POST['error_description']);
}

if (isset($_POST['code'], $_POST['state'], $_POST['session_state'])) {
    $code = $_POST['code'];

    $tokenUrl = ENTRA_ID_TOKEN_URL;
    $postData = [
        'grant_type' => 'authorization_code',
        'client_id' => ENTRA_ID_CLIENT_ID,
        'client_secret' => ENTRA_ID_CLIENT_SECRET,
        'code' => $code,
        'redirect_uri' => ENTRA_ID_CODE_REDIRECT_URI
    ];

    $client = new App\Request\HttpClient($tokenUrl);

    $request = $client->call('POST', '', $postData, null, false, [], true);

    if (isset($request['response'])) {
        $response = json_decode($request['response'], true);
    } elseif (isset($request['access_token'])) {
        // Find out the username in the token
        $username = JWT::parseTokenPayLoad($request['access_token'])['upn'];
        
        try {
            AccessToken::save($request['access_token'], $username);
        } catch (Exception $e) {
            Response::output($e->getMessage(), 400);
        }
        
        // Remove the username query string from state
        if (isset($_POST['state'])) {
            $split = explode("&", $_POST['state']);
            $state = $_POST['state'] ?? '/';
            $state = $split[0];
        } else {
            $state = '/';
        }
        header('Location: ' . $state);
        exit();
    } else {
        Response::output('Error: ' . $request['error'], 400);
    }

    if (isset($response['error_description'])) {
        // AADSTS70008: The provided authorization code or refresh token has expired due to inactivity. Send a new interactive authorization request for this user and resource
        if (str_contains($response['error_description'], 'AADSTS70008') || str_contains($response['error_description'], 'AADSTS54005')) {

            $data = [
                'client_id' => ENTRA_ID_CLIENT_ID,
                'response_type' => 'code',
                'redirect_uri' => $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] , // redirect back to the same page
                'scope' => 'https://graph.microsoft.com/user.read',
                'response_mode' => 'form_post',
                'state' => $_POST['state'],
                'nonce' => $_SESSION['nonce'],
                'prompt' => 'consent',
                'login_hint' => $username
            ];

            header('Location: ' . ENTRA_ID_OAUTH_URL . http_build_query($data));
            exit();
        } else {
            Response::output($response['error_description'], 400);
        }
    }
}

// Azure AD access token here
if (isset($_POST['access_token'], $_POST['token_type'], $_POST['expires_in'], $_POST['scope'], $_POST['state'], $_POST['session_state'])) {
    // Find out the username in the token
    $username = JWT::parseTokenPayLoad($_POST['access_token'])['upn'];
    try {
        AccessToken::save($_POST['access_token'], $username);
    } catch (Exception $e) {
        Response::output($e->getMessage(), 400);
    }
    // Remove the username query string from state
    $split = explode("&", $_POST['state']);
    $state = $_POST['state'] ?? '/';
    $state = $split[0];
    header('Location: ' . $state);
    exit();
}

// MS Live, only code and state are returned
if (isset($_POST['code'], $_POST['state'])) {
    $code = $_POST['code'];

    $tokenUrl = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/token';
    $postData = [
        'grant_type' => 'authorization_code',
        'client_id' => MS_LIVE_CLIENT_ID,
        'client_secret' => MS_LIVE_CLIENT_SECRET,
        'code' => $code,
        'redirect_uri' => MS_LIVE_REDIRECT_URI
    ];

    $client = new App\Request\HttpClient($tokenUrl);

    $request = $client->call('POST', '', $postData, null, false, [], true);

    if (isset($request['access_token'])) {
        // Find out the username in the token, but upn is not available in MS Live tokens, so let's extract it from the state
        $username = $_POST['state'];
        // However it is in /url&username=upn format, so let's extract the username
        $username = explode('=', $username)[1];
        
        try {
            AccessToken::save($request['access_token'], $username);
        } catch (Exception $e) {
            Response::output($e->getMessage(), 400);
        }
        
        // Remove the username query string from state
        $split = explode("&", $_POST['state']);
        $state = $_POST['state'] ?? '/';
        $state = $split[0];
        header('Location: ' . $state);
        exit();
    } else {
        Response::output('Error: ' . json_encode($request), 400);
    }
}
