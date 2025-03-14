<?php declare(strict_types=1);

use App\Core\Cookies;
use App\Api\Response;

if (isset($_POST['consent'])) {
    if ($_POST['consent'] === 'accept') {
        Cookies::set('cookie-consent', 'accept', time() + 60 * 60 * 24 * 365); // 1 year
        Response::output('success');
    } else {
        header('Location: https://www.google.com');
        exit;
    }
} else {
    Response::output('invalid request', 400);
}

Response::output('something went wrong setting the cookie', 400);
