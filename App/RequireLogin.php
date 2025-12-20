<?php

declare(strict_types=1);

namespace App;

use Models\User;
use App\Authentication\Azure\AzureAD;
use App\Authentication\JWT;
use App\Authentication\Google;
use App\Api\Response;
use App\Utilities\General;
use App\Authentication\AuthToken;

class RequireLogin
{
    public static function check(bool $apiRoute): array
    {
        $loginExemptions = array_unique(array_merge(LOGIN_EXEMPTIONS, SYSTEM_LOGIN_EXEMPTIONS));

        $loggedIn = false;

        $isAdmin = false;

        $usernameArray = [];

        $username = null;
        $provider = '';
        // If auth cookie exists
        if (AuthToken::get() !== null) {
            // First parse the JWT token
            $tokenPayload = JWT::parseTokenPayLoad(AuthToken::get());

            // If the issuer is $_SERVER['HTTP_HOST'], we are dealing with a local login
            if ($tokenPayload['iss'] === JWT_ISSUER) {
                // Check if valid
                if (JWT::checkToken(AuthToken::get())) {
                    $provider = 'local';
                    $loggedIn = true;
                } else {
                    if ($apiRoute) {
                        Response::output('Authentication failure', 401);
                    }
                    // If checks for JWT token fail - unset cookie and redirect to /login
                    JWT::handleValidationFailure();
                    header('Location: /login?destination=' . $_SERVER['REQUEST_URI']);
                    exit();
                }
            }

            // Now check if the issuer is the AzureAD endpoint
            if (str_starts_with($tokenPayload['iss'], 'https://login.microsoftonline.com/')) {
                // Check
                if (AzureAD::check(AuthToken::get())) {
                    $provider = 'azure';
                    $loggedIn = true;
                } else {
                    if ($apiRoute) {
                        Response::output('Authentication failure', 401);
                    }
                    // If checks for JWT token fail - unset cookie and redirect to /login
                    JWT::handleValidationFailure();
                    header('Location: /login?destination=' . $_SERVER['REQUEST_URI']);
                    exit();
                }
            }
            // Now check Google
            if ($tokenPayload['iss'] === 'https://accounts.google.com') {
                if (Google::check(AuthToken::get())) {
                    $provider = 'google';
                    $loggedIn = true;
                } else {
                    if ($apiRoute) {
                        Response::output('Authentication failure', 401);
                    }
                    // If checks for JWT token fail - unset cookie and redirect to /login
                    JWT::handleValidationFailure();
                    header('Location: /login');
                    exit();
                }
            }
            // Now check Microsoft Live
            if ($tokenPayload['iss'] === 'https://login.live.com') {
                // No current way of verifying the token so we will just check if it's not expired
                if (JWT::checkExpiration(AuthToken::get())) {
                    $provider = 'mslive';
                    $loggedIn = true;
                } else {
                    if ($apiRoute) {
                        Response::output('Authentication failure', 401);
                    }
                    // If checks for JWT token fail - unset cookie and redirect to /login
                    JWT::handleValidationFailure();
                    header('Location: /login?destination=' . $_SERVER['REQUEST_URI']);
                    exit();
                }
            }
        } else {
            // Redirect to /login but preserve the destination if auth_cookie is missing
            /*
                Do not redirect to /login if uri is in the list or exempt urls
                !str_contains($_SERVER['REQUEST_URI'], '/login') is to prevent infinite redirects
            */
            if (!General::matchRequestURI($loginExemptions) && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/login') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/auth/azure-ad') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/auth/google') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/auth/local')) {
                if ($apiRoute) {
                    Response::output('missing token', 401);
                } else {
                    $destination = $_SERVER['REQUEST_URI'] ?? '';
                    header('Location: /login?destination=' . $destination);
                    exit();
                }
            }
        }

        $idTokenInfoArray = [];

        if ($loggedIn && AuthToken::get() !== null && $provider === 'azure') {
            // Let's parse the JWT token from the auth cookie and look at the claims
            $authCookieArray = $tokenPayload;
            // We are mapping what the claims are called in the DB (keys) vs in the JWT token (values)
            $expectedClaims = [
                'username' => 'preferred_username',
                'email' => 'email',
                'name' => 'name',
                'last_ip' => 'ipaddr',
                'country' => 'ctry'
            ];
            foreach ($expectedClaims as $dbClaimName => $JWTClaimName) {
                $idTokenInfoArray[$dbClaimName] = isset($authCookieArray[$JWTClaimName]) ? $authCookieArray[$JWTClaimName] : null;
            }
            // Now the special ones
            $idTokenInfoArray["token_expiry"] = isset($authCookieArray['exp']) ? date("Y-m-d H:i:s", (int)substr((string)$authCookieArray['exp'], 0, 10)) : null;
            // If roles is not set, we set it to user, otherwise we set it to the roles array
            $idTokenInfoArray["roles"] = (isset($authCookieArray['roles'])) ? $authCookieArray['roles'] : ['user'];
            // Now we search for the administrator role as role is an array of roles

            $username = ($loggedIn) ? $idTokenInfoArray["username"] : null;
        }

        if ($provider === 'local') {
            $username = $tokenPayload['username'];
            $usernameArray = $tokenPayload;
            $idTokenInfoArray = $tokenPayload;
        }

        // Now Google
        if ($loggedIn && AuthToken::get() !== null && $provider === 'google') {
            $authCookieArray = $tokenPayload;
            $expectedClaims = [
                'username' => 'email',
                'email' => 'email',
                'name' => 'name',
                'last_ip' => 'ipaddr',
                'country' => 'locale',
                'picture' => 'picture'
            ];
            foreach ($expectedClaims as $dbClaimName => $JWTClaimName) {
                $idTokenInfoArray[$dbClaimName] = isset($authCookieArray[$JWTClaimName]) ? $authCookieArray[$JWTClaimName] : null;
            }
            // Now the special ones
            $idTokenInfoArray["token_expiry"] = isset($authCookieArray['exp']) ? date("Y-m-d H:i:s", (int)substr((string)$authCookieArray['exp'], 0, 10)) : null;
            // If roles is not set, we set it to user, otherwise we set it to the roles array
            $idTokenInfoArray["roles"] = (isset($authCookieArray['roles'])) ? $authCookieArray['roles'] : ['user'];
            // Now we search for the administrator role as role is an array of roles

            $username = ($loggedIn) ? $idTokenInfoArray["username"] : null;
        }
        // Microsoft LIVE
        if ($loggedIn && AuthToken::get() !== null && $provider === 'mslive') {
            $authCookieArray = $tokenPayload;
            $expectedClaims = [
                'username' => 'preferred_username',
                'email' => 'email',
                'name' => 'name'
            ];
            $idTokenInfoArray["last_ip"] = currentIP();
            foreach ($expectedClaims as $dbClaimName => $JWTClaimName) {
                $idTokenInfoArray[$dbClaimName] = isset($authCookieArray[$JWTClaimName]) ? $authCookieArray[$JWTClaimName] : null;
            }
            // Now the special ones
            $idTokenInfoArray["token_expiry"] = isset($authCookieArray['exp']) ? date("Y-m-d H:i:s", (int)substr((string)$authCookieArray['exp'], 0, 10)) : null;
            // If roles is not set, we set it to user, otherwise we set it to the roles array
            $idTokenInfoArray["roles"] = ['user'];
            // Now we search for the administrator role as role is an array of roles

            $username = ($loggedIn) ? $idTokenInfoArray["username"] : null;
        }

        // If we are logged in and we have an established username, we need to either fetch user data from the DB or create a new user in the DB
        if ($username !== null) {
            $user = new User();
            try {
                $usernameArray = $user->get($username);
            } catch (\Exception $e) {
                JWT::handleValidationFailure();
                    header('Location: /logout');
                    exit();
            }
            if ($usernameArray) {
                // Let's check if the last_ip is different from what we have in the DB, and update it
                if ($usernameArray['last_ips'] !== $idTokenInfoArray["last_ip"]) {
                     $user->update(['last_ips' => $idTokenInfoArray["last_ip"]], $usernameArray['id']);
                }
            } else {
                if (empty($usernameArray)) {
                    JWT::handleValidationFailure();
                    header('Location: /');
                    exit();
                } else {
                    JWT::handleValidationFailure();
                    header('Location: /logout');
                    exit();
                }
            }
            // Now some admin checks
            // If there is a role = administrator in both token and DB, give admin straight away
            foreach ($idTokenInfoArray["roles"] as $role) {
                if ($role === 'administrator' && $usernameArray['role'] === 'administrator') {
                    $isAdmin = true;
                }
            }
            // Also if the DB says admin, add admin
            if ($usernameArray['role'] === 'administrator') {
                $isAdmin = true;
            }
            // If token has admin role, but DB does not, we need to update the DB
            if (in_array('administrator', $idTokenInfoArray["roles"]) && $usernameArray['role'] !== 'administrator') {
                $user->update(['role' => 'administrator'], $usernameArray['id']);
                $isAdmin = true;
            }
        }

        // Kill disabled users early
        if (isset($usernameArray["enabled"])) {
            if ($usernameArray["enabled"] === 0 || $usernameArray["enabled"] === false) {
                echo 'Your user has been disabled';
                JWT::handleValidationFailure();
                exit();
            }
        }

        // If this gets executed on /login, we need to keep logged in users away from the login page
        if (str_contains($_SERVER['REQUEST_URI'], 'login') && $loggedIn) {
            if (isset($_GET['destination'])) {
                header('Location: ' . $_GET['destination']);
                exit();
            } else {
                header('Location: /');
                exit();
            }
        }

        return [
            'usernameArray' => $usernameArray,
            'loggedIn' => $loggedIn,
            'isAdmin' => $isAdmin,
        ];
    }
}
