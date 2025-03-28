<?php

declare(strict_types=1);

use App\Api\Response;
use App\Authentication\AccessToken;

if (isset($_POST['error'], $_POST['error_description'])) {
    if (str_contains($_POST['error'], 'consent_required')) {
        // Send an Authorization request if the error is AADSTS65001 (consent_required)
        if (isset($_POST['state'])) {
            $split = explode("&", $_POST['state']);
            $state = $_POST['state'] ?? '/';
            $state = $split[0];
        } else {
            $state = '/';
        }
        $username = str_replace('username=', '', $split[1]);
        $data = [
            'client_id' => MS_LIVE_CLIENT_ID,
            'response_type' => 'code',
            'redirect_uri' => $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] , // redirect back to the same page
            'scope' => 'https://graph.microsoft.com/user.read',
            'response_mode' => 'form_post',
            'state' => $_POST['state'],
            'nonce' => $_SESSION['nonce'],
            'prompt' => 'consent',
            'login_hint' => $username
        ];
        $url = MS_LIVE_OAUTH_URL;

        $location = $url . http_build_query($data);

        header('Location: ' . $url . http_build_query($data));
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

    // If the user just refuses to give consent we end up here:
    if (isset($_POST['error'], $_POST['error_description'], $_POST['state']) && str_contains($_POST['error'], 'access_denied') && str_contains($_POST['error_description'], 'The user has denied access')) {
        header('Location: /');
        exit();
    }

    Response::output("Azure Error: " . $_POST['error'] . " with Description: " . $_POST['error_description']);
}

if (isset($_POST['code'], $_POST['state'], $_POST['session_state'])) {
    $code = $_POST['code'];

    $postData = [
        'grant_type' => 'authorization_code',
        'client_id' => MS_LIVE_CLIENT_ID,
        'client_secret' => MS_LIVE_CLIENT_SECRET,
        'code' => $code,
        'redirect_uri' => MS_LIVE_CODE_REDIRECT_URI
    ];

    $client = new App\Request\HttpClient(MS_LIVE_TOKEN_URL);

    $request = $client->call('POST', '', $postData, null, false, [], true);

    if (isset($request['response'])) {
        $response = json_decode($request['response'], true);
    } elseif (isset($request['access_token'])) {
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
                'client_id' => MS_LIVE_CLIENT_ID,
                'response_type' => 'code',
                'redirect_uri' => $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] , // redirect back to the same page
                'scope' => 'https://graph.microsoft.com/user.read',
                'response_mode' => 'form_post',
                'state' => $_POST['state'],
                'nonce' => $_SESSION['nonce'],
                'prompt' => 'consent',
                'login_hint' => $username
            ];

            header('Location: ' . MS_LIVE_OAUTH_URL . http_build_query($data));
            exit();
        } else {
            Response::output($response['error_description'], 400);
        }
    }
}

// MS Live will send code token here
if (isset($_POST['code'], $_POST['state'])) {
    $code = $_POST['code'];

    $postData = [
        'grant_type' => 'authorization_code',
        'client_id' => MS_LIVE_CLIENT_ID,
        'client_secret' => MS_LIVE_CLIENT_SECRET,
        'code' => $code,
        'redirect_uri' => MS_LIVE_CODE_REDIRECT_URI
    ];

    $client = new App\Request\HttpClient('https://login.microsoftonline.com/consumers/oauth2/v2.0/token');

    $request = $client->call('POST', '', $postData, null, false, [], true);

    if (isset($request['response'])) {
        $response = json_decode($request['response'], true);
    } elseif (isset($request['access_token'])) {
        // Remove the username query string from state
        if (isset($_POST['state'])) {
            $split = explode("&", $_POST['state']);
            $state = $_POST['state'] ?? '/';
            $state = $split[0];
        } else {
            $state = '/';
        }
        $username = str_replace('username=', '', $split[1]);

        try {
            $save = AccessToken::save($request['access_token'], $username);
        } catch (Exception $e) {
            Response::output($e->getMessage(), 400);
        }

        header('Location: ' . $state);
        exit();
    } else {
        Response::output(json_encode($request), 400);
    }

    if (isset($response['error'], $response['error_description'])) {
        Response::output($response['error'] . ' error with description: ' . $response['error_description'], 400);
    }
}

// MS live too
if (isset($_POST['access_token'], $_POST['token_type'], $_POST['state'], $_POST['scope'], $_POST['expires_in'])) {
    $split = explode("&", $_POST['state']);
    $state = $split[0];
    $username = str_replace('username=', '', $split[1]);

    AccessToken::save($_POST['access_token'], $username);

    header('Location: ' . $state);
}
