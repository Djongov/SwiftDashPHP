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

// Modern tabbed interface container
echo '<div class="max-w-6xl mx-auto p-4">';
    echo Html::h1(translate('user_settings'), true);
    
    // Tab navigation
    echo '<div class="w-full max-w-full overflow-x-auto p-2 border-b border-gray-200 dark:border-gray-700 mb-6">';
        echo '<nav class="-mb-px flex space-x-8" aria-label="Tabs" role="tablist">';
            echo '<button data-tab="profile" id="tab-profile" class="tab-button border-' . $theme . '-500 text-' . $theme . '-600 hover:text-' . $theme . '-700 hover:border-' . $theme . '-400 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-offset-2" role="tab" aria-selected="true" aria-controls="content-profile">📋 Profile</button>';
            echo '<button data-tab="security" id="tab-security" class="tab-button border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-200 dark:hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-offset-2" role="tab" aria-selected="false" aria-controls="content-security">🔒 Security</button>';
            echo '<button data-tab="preferences" id="tab-preferences" class="tab-button border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-200 dark:hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-offset-2" role="tab" aria-selected="false" aria-controls="content-preferences">⚙️ Preferences</button>';
            if ($isAdmin) {
                echo '<button data-tab="admin" id="tab-admin" class="tab-button border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-200 dark:hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-offset-2" role="tab" aria-selected="false" aria-controls="content-admin">👑 Admin</button>';
            }
        echo '</nav>';
    echo '</div>';

    // Tab content containers
    echo '<div class="tab-content-wrapper">';
    
        // Profile Tab
        echo '<div id="content-profile" class="tab-content" role="tabpanel" aria-labelledby="tab-profile">';
            echo '<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">';
                
                // Profile Picture Section
                echo '<div class="' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700 p-6">';
                    echo Html::h3('Profile Picture');
                    echo '<div class="flex flex-col items-center space-y-4">';
                        echo '<div class="relative inline-block">';
                            echo '<img src="' . $usernameArray['picture'] . '" class="rounded-full w-32 h-32 object-cover border-4 border-gray-200 dark:border-gray-600" alt="Profile Picture">';
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
                                'theme' => 'red',
                                'method' => 'PUT',
                                'action' => '/api/user/' . $usernameArray['id'],
                                'confirm' => true,
                                'confirmText' => 'Are you sure you want to delete your profile picture? This will immediately attempt to update your profile picture with a new one',
                                'reloadOnSubmit' => true,
                                'submitButton' => [
                                    'text' => '🗑️',
                                    'size' => 'small',
                                    'title' => 'Delete Profile Picture',
                                    'style' => '🗑️'
                                ],
                            ];
                            echo '<div class="absolute -bottom-2 -right-2">' . Forms::render($deleteProfilePictureForm) . '</div>';
                        echo '</div>';
                        echo '<p class="text-sm text-gray-600 dark:text-gray-400 text-center">Click the delete button to refresh your profile picture</p>';
                    echo '</div>';
                echo '</div>';
                
                // Profile Information Section
                echo '<div class="' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700 p-6">';
                    echo Html::h3('Profile Information');
                    echo '<div class="space-y-4">';
                        foreach ($usernameArray as $name => $setting) {
                            if ($name === 'id' || $name === 'password' || $name === 'picture' || $name === 'enabled' || $name === 'watched_products' || $name === 'theme') {
                                continue;
                            }
                            
                            echo '<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">';
                                echo '<span class="font-medium text-gray-700 dark:text-gray-300 capitalize">' . str_replace('_', ' ', $name) . '</span>';
                                
                                // Check if date and format it
                                if ($setting !== null && is_string($setting) && General::isValidDatetime($setting)) {
                                    echo '<span class="text-gray-600 dark:text-gray-400">' . $fmt->format(strtotime($setting)) . '</span>';
                                }
                                // Boolean values
                                elseif (is_bool($setting)) {
                                    echo '<span class="px-2 py-1 text-xs rounded-full ' . ($setting ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100') . '">' . ($setting ? 'Yes' : 'No') . '</span>';
                                }
                                // Regular text
                                else {
                                    echo '<span class="text-gray-600 dark:text-gray-400 break-all max-w-xs text-right">' . (is_string($setting) ? htmlspecialchars($setting) : $setting) . '</span>';
                                }
                            echo '</div>';
                        }
                        echo '<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">';
                            echo '<span class="font-medium text-gray-700 dark:text-gray-300">' . translate('token_expiry') . '</span>';
                            echo '<span class="text-gray-600 dark:text-gray-400">' . $fmt->format(strtotime(date("Y-m-d H:i:s", (int)substr((string) JWT::parseTokenPayLoad(AuthToken::get())['exp'], 0, 10)))) . '</span>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
        
        // Security Tab
        echo '<div id="content-security" class="tab-content hidden" role="tabpanel" aria-labelledby="tab-security">';
            echo '<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">';
                
                // Password Change Section (for local users)
                if ($usernameArray['provider'] === 'local') {
                    echo '<div class="' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700 p-6">';
                        echo Html::h3(translate('change_password'));
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
                        echo Forms::render($changePasswordForm);
                        echo Html::small(translate('change_password_form_small_text'));
                    echo '</div>';
                }
                
                // Email Update Section
                if (empty($usernameArray['email']) || filter_var($usernameArray['email'], FILTER_VALIDATE_EMAIL) === false) {
                    echo '<div class="' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700 p-6">';
                        echo '<div class="flex items-center mb-4">';
                            echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-amber-500 mr-2">';
                                echo '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />';
                            echo '</svg>';
                            echo Html::h3(translate('missing_email'));
                        echo '</div>';
                        
                        if (filter_var($usernameArray['username'], FILTER_VALIDATE_EMAIL)) {
                            $missingEmailText = str_replace('{email}', '<b>' . $usernameArray['username'] . '</b>', translate('missing_email_text_username'));
                        } else {
                            $missingEmailText = translate('missing_email_text');
                        }
                        echo Html::p($missingEmailText);
                        
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
                        echo Forms::render($updateEmailFormOptions);
                    echo '</div>';
                }
                
                // Account Deletion Section
                echo '<div class="' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-red-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-red-700 p-6">';
                    echo Html::h3(translate('forget_about_me'));
                    echo Html::p(translate('forget_about_me_description'));
                    
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
                        'confirm' => true,
                        'confirmText' => translate('forget_about_me_modal_text'),
                        'doubleConfirm' => true,
                        'doubleConfirmKeyWord' => $usernameArray['username'],
                        'resultType' => 'text',
                        'submitButton' => [
                            'text' => translate('delete_user_button_text')
                        ],
                    ];
                    echo Forms::render($deleteUserFormOptions);
                echo '</div>';
            echo '</div>';
        echo '</div>';
        
        // Preferences Tab
        echo '<div id="content-preferences" class="tab-content hidden" role="tabpanel" aria-labelledby="tab-preferences">';
            echo '<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">';
                
                // Theme Settings Section
                echo '<div class="' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700 p-6">';
                    echo Html::h3(translate('theme'));
                    echo '<div class="space-y-4">';
                        echo '<p class="text-sm text-gray-600 dark:text-gray-400">Choose your preferred color theme</p>';
                        echo App\Security\CSRF::createTag();
                        echo '<div id="theme-selector" class="grid grid-cols-2 md:grid-cols-3 gap-3">';
                        foreach (THEME_COLORS as $color) {
                            $isSelected = ($usernameArray['theme'] === $color);
                            echo '<label class="relative cursor-pointer">';
                                echo '<input type="radio" name="theme" value="' . $color . '" ' . ($isSelected ? 'checked' : '') . ' class="sr-only theme-radio">';
                                echo '<div class="theme-option p-4 rounded-lg border-2 ' . ($isSelected ? 'border-' . $color . '-500 bg-' . $color . '-50 dark:bg-' . $color . '-900' : 'border-gray-200 dark:border-gray-700') . ' hover:border-' . $color . '-300 transition-colors">';
                                    echo '<div class="w-8 h-8 bg-' . $color . '-500 rounded-full mx-auto mb-2"></div>';
                                    echo '<p class="text-sm font-medium text-center capitalize">' . $color . '</p>';
                                echo '</div>';
                            echo '</label>';
                        }
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
                
                // Language & Region Settings (Placeholder for future enhancement)
                echo '<div class="' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700 p-6">';
                    echo Html::h3('Language & Region');
                    echo '<div class="space-y-4">';
                        echo '<div class="flex justify-between items-center py-2">';
                            echo '<span class="font-medium text-gray-700 dark:text-gray-300">Current Language</span>';
                            echo '<span class="text-gray-600 dark:text-gray-400">' . strtoupper($_SESSION['lang'] ?? DEFAULT_LANG) . '</span>';
                        echo '</div>';
                        if (isset($usernameArray['origin_country'])) {
                            echo '<div class="flex justify-between items-center py-2">';
                                echo '<span class="font-medium text-gray-700 dark:text-gray-300">Origin Country</span>';
                                echo '<span class="text-gray-600 dark:text-gray-400">' . $usernameArray['origin_country'] . '</span>';
                            echo '</div>';
                        }
                        echo '<p class="text-sm text-gray-500 dark:text-gray-400">Language switching is managed globally. Contact administrator for changes.</p>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
        
        // Admin Tab (only visible to admins)
        if ($isAdmin) {
            echo '<div id="content-admin" class="tab-content hidden" role="tabpanel" aria-labelledby="tab-admin">';
                echo '<div class="' . LIGHT_COLOR_SCHEME_CLASS . ' rounded-lg border border-gray-200 shadow-md ' . DARK_COLOR_SCHEME_CLASS . ' dark:border-gray-700 p-6">';
                    echo Html::h3(translate('session_info'));
                    echo '<div class="space-y-4">';
                        echo '<div class="py-2">';
                            echo '<span class="font-medium text-gray-700 dark:text-gray-300 block mb-2">' . translate('token') . '</span>';
                            echo '<textarea readonly class="w-full h-32 p-3 text-xs font-mono bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md resize-none">' . AuthToken::get() . '</textarea>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';
        }
    echo '</div>';
echo '</div>';
?>

<script nonce="1nL1n3JsRuN1192kwoko2k323WKE">
document.addEventListener("DOMContentLoaded", function() {
    function switchTab(tabName) {
        const allButtons = document.querySelectorAll(".tab-button");
        const allContents = document.querySelectorAll(".tab-content");
        
        allButtons.forEach(btn => {
            btn.classList.remove("border-<?=$theme?>-500", "text-<?=$theme?>-600");
            btn.classList.add("border-transparent", "text-gray-500");
            btn.setAttribute("aria-selected", "false");
        });
        
        allContents.forEach(content => {
            content.classList.add("hidden");
        });
        
        const activeButton = document.getElementById("tab-" + tabName);
        const activeContent = document.getElementById("content-" + tabName);
        
        if (activeButton && activeContent) {
            activeButton.classList.remove("border-transparent", "text-gray-500");
            activeButton.classList.add("border-<?=$theme?>-500", "text-<?=$theme?>-600");
            activeButton.setAttribute("aria-selected", "true");
            activeContent.classList.remove("hidden");
        }
    }
    
    document.querySelectorAll(".tab-button").forEach(button => {
        button.addEventListener("click", function(e) {
            e.preventDefault();
            const tabName = this.getAttribute("data-tab");
            if (tabName) {
                switchTab(tabName);
            }
        });
        
        button.addEventListener("keydown", function(e) {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // Handle theme radio button changes with PUT request
    document.querySelectorAll('#theme-selector .theme-radio').forEach(radio => {
        radio.addEventListener('change', async function() {
            const selectedTheme = this.value;
            const userId = <?php echo $usernameArray['id']; ?>;
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
            
            try {
                const response = await fetch('/api/user/' + userId, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        secretheader: 'badass'
                    },
                    body: JSON.stringify({
                        theme: selectedTheme,
                        username: '<?php echo htmlspecialchars($usernameArray['username']); ?>',
                        csrf_token: csrfToken,
                    })
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to update theme: ' + response.status);
                }
            } catch (error) {
                alert('Error updating theme: ' + error.message);
            }
        });
    });
    
    // Initialize with profile tab active
    switchTab("profile");
});
</script>

<style>
.tab-button.active { 
    border-color: rgb(59 130 246) !important; 
    color: rgb(37 99 235) !important; 
}

.tab-content { 
    transition: opacity 0.2s ease-in-out; 
}

.tab-content.active { 
    display: block; 
}

.tab-content.hidden { 
    display: none; 
}

.theme-option:hover { 
    transform: translateY(-1px); 
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
    transition: all 0.2s ease-in-out;
}

.theme-radio:checked + .theme-option { 
    transform: translateY(-2px); 
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); 
}

/* Additional responsive improvements */
@media (max-width: 640px) {
    .tab-content-wrapper {
        padding: 0.5rem;
    }
    
    .grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>
