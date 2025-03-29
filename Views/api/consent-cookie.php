<?php

declare(strict_types=1);

use App\Core\Cookies;
use App\Api\Response;
use App\Core\Session;

if (isset($_POST['consent'])) {
    if ($_POST['consent'] === 'accept') {
        $httpsActive = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $secure = (str_contains($_SERVER['HTTP_HOST'], 'localhost') || str_contains($_SERVER['HTTP_HOST'], '[::1]')) ? false : $httpsActive;
        $sameSite = $secure ? 'None' : 'Lax';
        Cookies::set('cookie-consent', 'accept', time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST'], $secure, true, $sameSite); // 1 year
        Response::output('success');
    } else {
        header('Location: https://www.google.com');
        exit;
    }
} elseif (isset($_POST['get-consent'])) {
    if (isset($_COOKIE['cookie-consent'])) {
        Response::output(Cookies::get('cookie-consent'));
    } else {
        Response::output('no consent');
    }
} elseif (isset($_POST['delete-consent'])) {
    if (isset($_COOKIE['cookie-consent'])) {
        // So after a while I understood that we need to pass the same parameters to delete the cookie as we did to set it, otherwise it won't work
        $httpsActive = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $secure = (str_contains($_SERVER['HTTP_HOST'], 'localhost') || str_contains($_SERVER['HTTP_HOST'], '[::1]')) ? false : $httpsActive;
        Cookies::delete('cookie-consent', '/', $_SERVER['HTTP_HOST'], $secure, true);
        // Destroy the session too
        Session::reset();
        Response::output('consent deleted');
    } else {
        Response::output('no consent cookie to delete', 404);
    }
} else {
    Response::output('invalid request', 400);
}

Response::output('something went wrong setting or deleting the consent cookie', 400);
