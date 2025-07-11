<?php

declare(strict_types=1);

use App\Utilities\General;
use Components\Forms;
use Components\Html;
use App\Authentication\JWT;
use Models\User;
use App\Exceptions\UserExceptions;
use App\Request\HttpClient;
use App\Authentication\AuthToken;
use Components\Alerts;
use App\Authentication\Azure\AccessToken;

$user = new User();
/* Profile picture update logic */
$token = JWT::parseTokenPayLoad(AuthToken::get());
// This is mostly Google
if (!empty($usernameArray['picture']) && isset($token['picture'])) {
    $picture = $usernameArray['picture'];
    // Checkl the picture from the JWT token, it might be updated
    $token = JWT::parseTokenPayLoad(AuthToken::get());
    if ($picture !== $token['picture']) {
        $picture = $token['picture'];
        // Save the picture to the user
        try {
            $user->update(['picture' => $picture], $usernameArray['id']);
        } catch (UserExceptions $e) {
            // Handle user-specific exceptions
            echo $e->getMessage();
        } catch (\Exception $e) {
            // Handle other exceptions
            echo $e->getMessage();
        }
    }
} elseif ($usernameArray['picture'] === null || empty($usernameArray['picture'])) {
    // If Azure
    if ($usernameArray['provider'] === 'azure' || $usernameArray['provider'] === 'mslive') {
        $accessToken = AccessToken::get($usernameArray['username'], 'https://graph.microsoft.com/user.read'); // 'https://graph.microsoft.com/user.read'
        $client = new HttpClient('https://graph.microsoft.com/v1.0/me/photo/$value');
        $response = $client->call('GET', '', [], $accessToken, false, ['Accept: image/jpeg'], false, false);
        $userController = new Controllers\User();
        if (is_string($response)) {
            $userController->saveAzureProfilePicture($usernameArray['username'], $response);
        } else {
            // If no picture is set, use the ui-avatars.com service to generate a picture
            echo Alerts::danger('Failed to get profile picture from Azure: ' . json_encode($response));
            $picture = 'https://ui-avatars.com/api/?name=' . $usernameArray['name'] . '&background=0D8ABC&color=fff';
            // Save the picture to the user
            try {
                $user->update(['picture' => $picture], $usernameArray['id']);
            } catch (UserExceptions $e) {
                // Handle user-specific exceptions
                echo $e->getMessage();
            } catch (\Exception $e) {
                // Handle other exceptions
                echo $e->getMessage();
            }
        }
    // If Local account
    } else {
        // If no picture is set, use the ui-avatars.com service to generate a picture
        $picture = 'https://ui-avatars.com/api/?name=' . $usernameArray['name'] . '&background=0D8ABC&color=fff';
        // Save the picture to the user
        try {
            $user->update(['picture' => $picture], $usernameArray['id']);
        } catch (UserExceptions $e) {
            // Handle user-specific exceptions
            echo $e->getMessage();
        } catch (\Exception $e) {
            // Handle other exceptions
            echo $e->getMessage();
        }
    }
}

$locale = (isset($usernameArray['origin_country'])) ? General::countryCodeToLocale($usernameArray['origin_country']) : $_SESSION['lang'] ?? DEFAULT_LANG;
$fmt = new IntlDateFormatter($locale, IntlDateFormatter::LONG, IntlDateFormatter::GREGORIAN);
echo '<div class="flex flex-row flex-wrap items-start mb-4 justify-center">';
    echo '<div class="p-4 m-4 max-w-lg ' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700">';
        echo Html::h2(translate('user_settings'));
        // Now let's put inside the image, a delete button
        echo '<div class="relative inline-block">';
            echo '<img src="' . $usernameArray['picture'] . '" class="rounded-full w-32 h-32" alt="Profile Picture">';
                $deleteProfilePictureForm = [
                    'inputs' => [
                        'hidden' => [
                            [
                                'name' => 'picture',
                                'value' => ''
                            ],
                            [
                                'name' => 'username',
                                'value' => $usernameArray['username']
                            ]
                        ],
                    ],
                    'theme' => $theme, // Optional, defaults to COLOR_SCHEME
                    'method' => 'PUT',
                    'action' => '/api/user/' . $usernameArray['id'],
                    'confirm' => true,
                    'confirmText' => 'Are you sure you want to delete your profile picture? This will immediately attempt to update your profile picture with a new one',
                    'reloadOnSubmit' => true,
                    'submitButton' => [
                        'text' => 'Update',
                        'size' => 'medium',
                        'style' => '&#10060;',
                        'title' => 'Delete Profile Picture'
                    ],
                ];
                echo '<div class="absolute bottom-0 right-0 p-1 text-xs font-bold">' . Forms::render($deleteProfilePictureForm) . '</div>';
            echo '</div>';
            echo '<table class="w-auto">';
                foreach ($usernameArray as $name => $setting) {
                    echo '<tr>';
                    if ($name === 'id' || $name === 'password' || $name === 'picture' || $name === 'enabled' || $name === 'watched_products') {
                        continue;
                    }
                    // Check if date and format it
                    if ($setting !== null && is_string($setting) && General::isValidDatetime($setting)) {
                        echo ' <td class="w-full"><strong>' . $name . '</strong> : ' . $fmt->format(strtotime($setting)) . '  </td>';
                        continue;
                    }
                    // Theme changer
                    if ($name === 'theme') {
                        echo '<td class="w-full">
                    <div class="flex my-2 flex-row"><strong>' . translate('theme') . '</strong> : ';
                        echo '<form class="select-submitter" data-reload="true" method="PUT" action="/api/user/' . $usernameArray['id'] . '">';
                            echo '<select name="theme" class="' . Html::selectInputClasses($theme) . '">';
                        foreach (THEME_COLORS as $color) {
                            echo '<option value="' . $color . '" ' . (($setting === $color) ? 'selected' : '') . '>' . $color . '</option>';
                        }
                            echo '</select>';
                            echo '<input type="hidden" name="username" value="' . $usernameArray['username'] . '">';
                            echo App\Security\CSRF::createTag();
                        echo '</form>';
                        echo '</div>
                </td>';
                        continue;
                    }
                    // Boolean
                    if (is_bool($setting)) {
                        echo ' <td class="w-full"><strong>' . $name . '</strong> : ' . (($setting) ? 'true' : 'false') . '  </td>';
                        continue;
                    // The rest
                    } else {
                        echo ' <td class="w-full"><strong>' . $name . '</strong> : <span class="break-all">' . $setting . '</span>  </td>';
                    }
                    echo '</tr>';
                }
            echo '</table>';
        echo '</div>';
        // Only show session info to admins
        if ($isAdmin) {
            echo '<div class="p-4 m-4 max-w-lg ' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700">';
            echo Html::h2(translate('session_info'));
            echo '<p><strong>' . translate('token_expiry') . '</strong>: ' . $fmt->format(strtotime(date("Y-m-d H:i:s", (int)substr((string) JWT::parseTokenPayLoad(AuthToken::get())['exp'], 0, 10)))) . '</p>';
            echo '<p><strong>' . translate('token') . ': </strong></p><p class="break-all c0py">' . AuthToken::get() . '</p>';
            $token = JWT::parseTokenPayLoad(AuthToken::get());
            echo '</div>';
        }
        // If the user is missing an email, ask for it
        if (empty($usernameArray['email']) || filter_var($usernameArray['email'], FILTER_VALIDATE_EMAIL) === false) {
            $updateEmailHtml = '<div class="flex flex-row flex-wrap items-center mb-4">';
            // Email svg icon
            $updateEmailHtml .= '
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline-block w-6 h-6 fill-amber-500">
                    <title>Missing Email</title>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>';
            $updateEmailHtml .= Html::h2(translate('missing_email'));
            $updateEmailHtml .= '</div>';
            if (filter_var($usernameArray['username'], FILTER_VALIDATE_EMAIL)) {
                $missingEmailText = str_replace('{email}', '<b>' . $usernameArray['username'] . '</b>', translate('missing_email_text_username'));
            } else {
                $missingEmailText = translate('missing_email_text');
            }
            $updateEmailHtml .= Html::p($missingEmailText);
            // Update email form
            $updateEmailFormOptions = [
                'inputs' => [
                    'input' => [
                        [
                            'label' => translate('email_address'),
                            'type' => 'email',
                            'placeholder' => '',
                            'name' => 'email',
                            'description' => translate('email_address_description'),
                            'disabled' => false,
                            'required' => true,
                        ]
                    ],
                    'hidden' => [
                        [
                            'name' => 'username',
                            'value' => $usernameArray['username']
                        ]
                    ],
                ],
                'theme' => $theme,
                'method' => 'PUT',
                'action' => '/api/user/' . $usernameArray['id'],
                'reloadOnSubmit' => true,
                'submitButton' => [
                    'text' => translate('email_address_change_button_text')
                ],
            ];
            $updateEmailHtml .= Forms::render($updateEmailFormOptions);
            echo Html::divBox($updateEmailHtml);
        }
        // Change password for local users
        if ($usernameArray['provider'] === 'local') {
            $changePasswordForm = [
                'inputs' => [
                    'input' => [
                        [
                            'label' => translate('new_password'),
                            'type' => 'password',
                            'placeholder' => '',
                            'name' => 'password',
                            'description' => translate('new_password_description'),
                            'disabled' => false,
                            'required' => true,
                        ],
                        [
                            'label' => translate('confirm_new_password'),
                            'type' => 'password',
                            'placeholder' => '',
                            'name' => 'confirm_password',
                            'description' => translate('confirm_new_password_description'),
                            'disabled' => false,
                            'required' => true,
                        ]
                    ],
                    'hidden' => [
                        [
                            'name' => 'username',
                            'value' => $usernameArray['username']
                        ]
                    ]
                ],
                'theme' => $theme,
                'method' => 'PUT',
                'action' => '/api/user/' . $usernameArray['id'],
                'redirectOnSubmit' => '/logout',
                'submitButton' => [
                    'text' => translate('change_password')
                ],
            ];

            $changePassordHtml = Html::h2(translate('change_password'));
            $changePassordHtml .= Forms::render($changePasswordForm);
            $changePassordHtml .= Html::small(translate('change_password_form_small_text'));
            echo Html::divBox($changePassordHtml);
        }

        // Delete/Forget user
        $deleteUserFormOptions = [
            'inputs' => [
                'hidden' => [
                    [
                        'name' => 'username',
                        'value' => $usernameArray['username']
                    ]
                ],
            ],
            'theme' => 'red',
            'method' => 'DELETE',
            'action' => '/api/user/' . $usernameArray['id'] . '?csrf_token=' . $_SESSION['csrf_token'],
            'redirectOnSubmit' => '/logout',
            'confirm' => true,
            'confirmText' => translate('forget_about_me_modal_text'),
            'doubleConfirm' => true,
            'doubleConfirmKeyWord' => $usernameArray['username'],
            'resultType' => 'text',
            'submitButton' => [
                'text' => translate('delete_user_button_text')
            ],
        ];

        $deleteUserHtml = Html::h2(translate('forget_about_me'));
        $deleteUserHtml .= Html::p(translate('forget_about_me_description'));
        $deleteUserHtml .= Forms::render($deleteUserFormOptions);

        echo Html::divBox($deleteUserHtml);

    echo '</div>';
