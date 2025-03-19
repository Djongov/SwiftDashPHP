<?php declare(strict_types=1);

use App\Core\Cookies;
use App\Api\Response;
use App\Core\Session;

if (isset($_POST['consent'])) {
    if ($_POST['consent'] === 'accept') {
        Cookies::set('cookie-consent', 'accept', time() + 60 * 60 * 24 * 365); // 1 year
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
        Cookies::delete('cookie-consent');
        // Destroy the session too
        Session::reset();
        Response::output('consent deleted');
    } else {
        Response::output('no consent cookie to delete', 404);
    }
} else {
    Response::output('invalid request', 400);
}

Response::output('something went wrong setting the cookie', 400);
