<?php declare(strict_types=1);

use App\Api\Response;
use App\Logs\SystemLog;
use Models\ContentSecurityPolicy\CSPReports;
use Models\ContentSecurityPolicy\CSPApprovedDomains;
use App\Exceptions\ContentSecurityPolicyExceptions;

//First of all check if Content-Type is application/csp-report
if ($_SERVER['CONTENT_TYPE'] !== 'application/csp-report') {
    Response::output('Incorrect Content-Type', 400);
}

$jsonData = file_get_contents('php://input'); // Read the data from the POST request as it's JSON and won't show up on $_POST

// Check if the data is JSON
if (!($jsonArray = json_decode($jsonData, true))) {
    Response::output('Needs json data', 400);
}

// Now let's check if the expected keys of a csp-report document are present
if (!isset($jsonArray['csp-report'])) {
    Response::output('csp-report missing', 400);
}

// Now let's check if the directives are present
$expectedDirectives = ['document-uri', 'referrer', 'violated-directive', 'effective-directive', 'original-policy', 'disposition', 'blocked-uri', 'status-code'];

foreach ($expectedDirectives as $directive) {
    if (!isset($jsonArray['csp-report'][$directive])) {
        Response::output($directive . ' missing', 400);
    }
}

// There are cases where the document-uri is "about". We want to ignore these cases. We get about when something like TinyMCE is used to add content from somewhere else, very dynamic and not a security issue but it can flood the database and there is not way to understand where it is coming from.
// if ($jsonArray['csp-report']['document-uri'] === 'about') {
//     Response::output('Document URI is about', 400);
// }

// Now let's check if the domain is allowed
$domain = parse_url($jsonArray['csp-report']['document-uri'], PHP_URL_HOST);

if (!$domain && $jsonArray['csp-report']['document-uri'] === 'about') {
    $domain = 'about';
}

if ($domain === false || $domain === null || $domain === '' && $domain !== 'about') {
    SystemLog::write('Invalid domain, got \'\', null or false', 'CSP Domain Not Allowed');
    Response::output('Invalid domain', 400);
}

$cspApprovedDomains = new CSPApprovedDomains();

if (!$cspApprovedDomains->domainExist($domain) && $domain !== 'about') {
    SystemLog::write($domain . ' attempted to send report', 'CSP Domain Not Allowed');
    Response::output('Domain not allowed', 401);
}

$csp = new CSPReports();

// All god, let's save the data
try {
    $csp->create($jsonArray);
    http_response_code(204); // Send HTTP 204 No Content response and content is also empty at this point
    exit();
} catch (ContentSecurityPolicyExceptions $e) {
    SystemLog::write($e->getMessage(), 'CSP Report Not Saved');
    Response::output($e->getMessage(), $e->getCode());
}

