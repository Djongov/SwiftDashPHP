<?php

declare(strict_types=1);

use Components\Forms;
use Components\Html;

echo Html::h1('Forms', true);

echo Html::p('This is a forms page. Here is how we can use the form abilities built into the system.', ['text-center']);

echo Html::h2('Form example');

echo Html::p('This is a sample form array to render a form with the least amount of options. It only produces a button and a hidden input field, suitable for button click actions.');

$formOptions = [
    'inputs' => [
        'hidden' => [
            [
                'name' => 'hidden',
                'value' => 'hidden value',
            ]
        ],
    ],
    'action' => '/api/example',
    'stopwatch' => 'example',
    'submitButton' => [
        'text' => 'Submit',
        'size' => 'small',
    ]
];

echo '<div class="ml-2">';
    echo Forms::render($formOptions, $theme);
echo '</div>';

echo Html::code('
$formOptions = [
    "inputs" => [
        "hidden" => [
            [
                "name" => "hidden",
                "value" => "hidden value",
            ]
        ],
    ],
    "action" => "/api/example",
    "stopwatch" => "example",
    "submitButton" => [
        "text" => "Submit",
        "size" => "large",
    ]
];
');

echo Html::h2('Complex form example');

echo Html::p('This is a more complex form example which showcases all the powers of the Form component.');

$formOptions = [
    // If you need input fields, open an 'inputs' key
    'inputs' => [
        'input' => [
            [
                'label' => 'Email Address',
                'type' => 'email',
                'placeholder' => 'John.Doe@example.com',
                'name' => 'email',
                'description' => 'Provide a valid email',
                'id' => 'email-form',
                'value' => 'John.Doe@example.com',
                'dataAttributes' => [
                    'foo' => 'bar',
                    'bar' => 'foo'
                ],
                'extraClasses' => ['brainpower'],
            ],
            [
                'label' => 'Password',
                'type' => 'password',
                'placeholder' => 'Password',
                'name' => 'password',
                'description' => 'Provide a valid password that contains at least one uppercase letter, one lowercase letter, one number, and is at least 4 characters long',
                'id' => 'password-form',
                'value' => 'P@ssw0rd',
                'dataAttributes' => [
                    'foo' => 'bar',
                    'bar' => 'foo'
                ],
                //'regex' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{4,}$',
                'extraClasses' => ['brainpower'],
            ],
            [
                'label' => 'First Name',
                'type' => 'text',
                'placeholder' => 'John',
                'name' => 'first_name',
                'description' => 'Provide a valid first name',
                'id' => 'first-name-form',
                'value' => 'John',
                'dataAttributes' => [
                    'foo' => 'bar',
                    'bar' => 'foo'
                ],
                'extraClasses' => ['brainpower'],
            ],
            [
                'label' => 'Last Name',
                'type' => 'text',
                'placeholder' => 'Doe',
                'name' => 'last_name',
                'description' => 'Provide a valid last name',
                'id' => 'last-name-form',
                'value' => 'Doe',
                'dataAttributes' => [
                    'foo' => 'bar',
                    'bar' => 'foo'
                ],
                'extraClasses' => ['brainpower'],
            ],
            [
                'label' => 'Phone Number',
                'type' => 'tel',
                'placeholder' => '123-456-7890',
                'name' => 'phone',
                'value' => '123-456-7890',
                'description' => 'Provide a valid phone number'
            ],
            [
                'label' => 'Date of Birth',
                'type' => 'datetime-local',
                'name' => 'dob',
                'value' => '1986-01-01T00:00',
                'description' => 'Provide a valid date of birth'
            ],
        ],
        'checkbox' => [
            [
                'label' => 'I agree to the terms and conditions',
                'name' => 'terms',
                'id' => 'terms-form',
                'checked' => false,
                'required' => true,
                'description' => 'Please read the terms and conditions before checking this box'
            ]
        ],
        'select' => [
            [
                'label' => 'Country',
                'name' => 'country',
                'id' => 'country-form',
                'title' => 'Country',
                'options' => [
                    [
                        'value' => 'us',
                        'text' => 'United States'
                    ],
                    [
                        'value' => 'ca',
                        'text' => 'Canada'
                    ],
                    [
                        'value' => 'mx',
                        'text' => 'Mexico'
                    ],
                    [
                        'value' => 'eu',
                        'text' => 'European Union'
                    ],
                    [
                        'value' => 'au',
                        'text' => 'Australia'
                    ]
                ],
                'selected' => 'ca',
                'searchable' => true,
                'searchFlex' => 'flex-col',
                'description' => 'Select your country',
                'required' => true
            ]
        ],
        'textarea' => [
            [
                'label' => 'Comments',
                'name' => 'comments',
                'id' => 'comments-form',
                'placeholder' => 'Enter your comments here',
                'rows' => 4,
                'cols' => 50,
                'description' => 'Enter your comments here',
                'required' => false
            ]
        ],
        'toggle' => [
            [
                'name' => 'enabled',
                'id' => 'enabled',
                'checked' => true,
                'disabled' => isset($usernameArray['username']) ? false : true,
                'description' => 'Enabled?'
            ]
        ],
        'checkboxGroup' => [
            // First checkbox group
            [
                'label' => 'Return type',
                'description' => 'Select the return type',
                'name' => 'return',
                'checkboxes' => [
                    [
                        'label' => 'Return text',
                        'checked' => true,
                        'description' => 'Check this to return text response',
                        'value' => 'text'
                    ],
                    [
                        'label' => 'Return json',
                        'checked' => false,
                        'description' => 'Check this to return json response',
                        'value' => 'json'
                    ]
                ]
            ],
            // Second checkbox group
            [
                'label' => 'Choice of colors',
                'description' => 'Select a color to draw a square with this color',
                'name' => 'colors',
                'checkboxes' => [
                    [
                        'label' => 'Red',
                        'checked' => true,
                        'description' => 'Check this to draw a red div if return type is text',
                        'value' => 'red'
                    ],
                    [
                        'label' => 'Green',
                        'checked' => false,
                        'description' => 'Check this to draw a green div if return type is text',
                        'value' => 'green'
                    ],
                    [
                        'label' => 'Blue',
                        'checked' => false,
                        'description' => 'Check this to draw a blue div if return type is text',
                        'value' => 'blue'
                    ]
                ]
            ]
        ],
        'hidden' => [
            [
                'name' => 'submitter',
                'value' => $usernameArray['username'] ?? 'unknown'
            ]
        ],
    ],
    // Now come the form options and the submit button options
    'theme' => $theme, // Optional, defaults to COLOR_SCHEME
    //'target' => '_blank', // Optional, defaults to _self
    'method' => 'POST', // Optional, defaults to POST
    'action' => '/api/example', // Required
    'additionalClasses' => 'qwerty power', // Optional
    //'reloadOnSubmit' => true,
    //'redirectOnSubmit' => '/dashboard',
    //'deleteCurrentRowOnSubmit' => false,
    //'confirm' => true,
    //'confirmText' => 'Are you sure you want to send this quack?', // Optional, defaults to "Are you sure?" if ommited
    'resultType' => 'html', // html or text, optional defaults to text
    //'doubleConfirm' => true,
    //'doubleConfirmKeyWord' => 'delete',
    "stopwatch" => "userform",
    'submitButton' => [
        'text' => 'Submit',
        'id' => uniqid(),
        'name' => 'submit',
        'type' => 'submit',
        'size' => 'medium',
        'disabled' => false,
        'title' => 'Replaced button',
        'style' => '&#10060;'
    ]
];

echo Components\Html::divBox(Forms::render($formOptions));

echo Html::p('And here is the code for this form:');

echo Html::code('
$formOptions = [
    // If you need input fields, open an \'inputs\' key
    \'inputs\' => [
        \'input\' => [
            [
                \'label\' => \'Email Address\',
                \'type\' => \'email\',
                \'placeholder\' => \'John.Doe@example.com\',
                \'name\' => \'email\',
                \'description\' => \'Provide a valid email\',
                \'id\' => \'email-form\',
                \'value\' => \'John.Doe@example.com\',
                \'dataAttributes\' => [
                    \'foo\' => \'bar\',
                    \'bar\' => \'foo\'
                ],
                \'extraClasses\' => [\'brainpower\'],
            ],
            [
                \'label\' => \'Password\',
                \'type\' => \'password\',
                \'placeholder\' => \'Password\',
                \'name\' => \'password\',
                \'description\' => \'Provide a valid password that contains at least one uppercase letter, one lowercase letter, one number, and is at least 4 characters long\',
                \'id\' => \'password-form\',
                \'value\' => \'P@ssw0rd\',
                \'dataAttributes\' => [
                    \'foo\' => \'bar\',
                    \'bar\' => \'foo\'
                ],
                //\'regex\' => \'^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{4,}$\',
                \'extraClasses\' => [\'brainpower\'],
            ],
            [
                \'label\' => \'First Name\',
                \'type\' => \'text\',
                \'placeholder\' => \'John\',
                \'name\' => \'first_name\',
                \'description\' => \'Provide a valid first name\',
                \'id\' => \'first-name-form\',
                \'value\' => \'John\',
                \'dataAttributes\' => [
                    \'foo\' => \'bar\',
                    \'bar\' => \'foo\'
                ],
                \'extraClasses\' => [\'brainpower\'],
            ],
            [
                \'label\' => \'Last Name\',
                \'type\' => \'text\',
                \'placeholder\' => \'Doe\',
                \'name\' => \'last_name\',
                \'description\' => \'Provide a valid last name\',
                \'id\' => \'last-name-form\',
                \'value\' => \'Doe\',
                \'dataAttributes\' => [
                    \'foo\' => \'bar\',
                    \'bar\' => \'foo\'
                ],
                \'extraClasses\' => [\'brainpower\'],
            ],
            [
                \'label\' => \'Phone Number\',
                \'type\' => \'tel\',
                \'placeholder\' => \'123-456-7890\',
                \'name\' => \'phone\',
                \'value\' => \'123-456-7890\',
                \'description\' => \'Provide a valid phone number\'
            ],
            [
                \'label\' => \'Date of Birth\',
                \'type\' => \'datetime-local\',
                \'name\' => \'dob\',
                \'value\' => \'1986-01-01T00:00\',
                \'description\' => \'Provide a valid date of birth\'
            ],
        ],
        \'checkbox\' => [
            [
                \'label\' => \'I agree to the terms and conditions\',
                \'name\' => \'terms\',
                \'id\' => \'terms-form\',
                \'checked\' => false,
                \'required\' => true,
                \'description\' => \'Please read the terms and conditions before checking this box\'
            ]
        ],
        \'select\' => [
            [
                \'label\' => \'Country\',
                \'name\' => \'country\',
                \'id\' => \'country-form\',
                \'title\' => \'Country\',
                \'options\' => [
                    [
                        \'value\' => \'us\',
                        \'text\' => \'United States\'
                    ],
                    [
                        \'value\' => \'ca\',
                        \'text\' => \'Canada\'
                    ],
                    [
                        \'value\' => \'mx\',
                        \'text\' => \'Mexico\'
                    ],
                    [
                        \'value\' => \'eu\',
                        \'text\' => \'European Union\'
                    ],
                    [
                        \'value\' => \'au\',
                        \'text\' => \'Australia\'
                    ]
                ],
                \'selected\' => \'ca\',
                \'searchable\' => true,
                \'searchFlex\' => \'flex-col\',
                \'description\' => \'Select your country\'
            ]
        ],
        \'textarea\' => [
            [
                \'label\' => \'Comments\',
                \'name\' => \'comments\',
                \'id\' => \'comments-form\',
                \'placeholder\' => \'Enter your comments here\',
                \'rows\' => 4,
                \'cols\' => 50,
                \'description\' => \'Enter your comments here\'
            ]
        ],
        \'toggle\' => [
            [
                \'name\' => \'enabled\',
                \'id\' => \'enabled\',
                \'checked\' => true,
                \'disabled\' => isset($usernameArray[\'username\']) ? false : true,
                \'description\' => \'Enabled?\'
            ]
        ],
        \'checkboxGroup\' => [
            // First checkbox group
            [
                \'label\' => \'Return type\',
                \'description\' => \'Select the return type\',
                \'name\' => \'return\',
                \'checkboxes\' => [
                    [
                        \'label\' => \'Return text\',
                        \'checked\' => true,
                        \'description\' => \'Check this to return text response\',
                        \'value\' => \'text\'
                    ],
                    [
                        \'label\' => \'Return json\',
                        \'checked\' => false,
                        \'description\' => \'Check this to return json response\',
                        \'value\' => \'json\'
                    ]
                ]
            ],
            // Second checkbox group
            [
                \'label\' => \'Choice of colors\',
                \'description\' => \'Select a color to draw a square with this color\',
                \'name\' => \'colors\',
                \'checkboxes\' => [
                    [
                        \'label\' => \'Red\',
                        \'checked\' => true,
                        \'description\' => \'Check this to draw a red div if return type is text\',
                        \'value\' => \'red\'
                    ],
                    [
                        \'label\' => \'Green\',
                        \'checked\' => false,
                        \'description\' => \'Check this to draw a green div if return type is text\',
                        \'value\' => \'green\'
                    ],
                    [
                        \'label\' => \'Blue\',
                        \'checked\' => false,
                        \'description\' => \'Check this to draw a blue div if return type is text\',
                        \'value\' => \'blue\'
                    ]
                ]
            ]
        ],
        \'hidden\' => [
            [
                \'name\' => \'submitter\',
                \'value\' => $usernameArray[\'username\'] ?? \'unknown\'
            ]
        ],
    ],
    // Now come the form options and the submit button options
    \'theme\' => $theme, // Optional, defaults to COLOR_SCHEME
    //\'target\' => \'_blank\', // Optional, defaults to _self
    \'method\' => \'POST\', // Optional, defaults to POST
    \'action\' => \'/api/example\', // Required
    \'additionalClasses\' => \'qwerty power\', // Optional
    //\'reloadOnSubmit\' => true,
    //\'redirectOnSubmit\' => \'/dashboard\',
    //\'deleteCurrentRowOnSubmit\' => false,
    //\'confirm\' => true,
    //\'confirmText\' => \'Are you sure you want to send this quack?\', // Optional, defaults to "Are you sure?" if omitted
    \'resultType\' => \'html\', // html or text, optional defaults to text
    //\'doubleConfirm\' => true,
    //\'doubleConfirmKeyWord\' => \'delete\',
    \'submitButton\' => [
        \'text\' => \'Submit\',
        \'id\' => uniqid(),
        \'name\' => \'submit\',
        \'type\' => \'submit\',
        \'size\' => \'medium\',
        \'disabled\' => false,
        \'title\' => \'Replaced button\',
        \'style\' => \'&#10060;\'
    ]
];

echo Components\Html::divBox(Components\Forms::render($formOptions));
');
