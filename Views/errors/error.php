<?php declare(strict_types=1);

use Components\ErrorPageHandler;

http_response_code(404);

echo ErrorPageHandler::render(404, 'Not found', 'The page you are looking for is not there.... or you are just trying random stuff...', $theme);
