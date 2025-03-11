---
title: Forms
description: Forms component provides nice and easy way to create forms
keywords: forms, components
#author: John Doe
#date: 2024-05-17
---

## Forms

If you want to create a form.

``` php
use Components\Forms;
```

Then you need to create an options array

``` php
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
                'description' => 'Select your country'
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
                'description' => 'Enter your comments here'
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
    "stopwatch" => "example",
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
```

to render it

``` php
echo Components\Html::divBox(Components\Forms::render($formOptions))
```

### Options

Here are the options that are accepted for Forms.

#### Form Options

These form options are presneted into the options array

| Option   | Type     | Required | Default | Description           |
|----------|----------|----------|---------|-----------------------|
| inputs  | Array   | Yes      | -       | This is the main holder for ```<input>``` type of fields|
| theme  | string   | No       | COLOR_SCHEME      | This sets the theme color for the form. You could set it to the current user theme by giving it value of ```$theme```|
| target  | string   | No       | _self      | We leave the option to have target ```_blank``` if needed|
| id  | string   | No      | ''       | value for the id="" attr|
| stopwatch  | string   | No      | ''       | whether to apply a stopwatch for the form submission under the submit button|
| method  | string     | No      | POST  | ```GET```, ```POST```, ```PUT```, ```DELETE```|
| action  | string     | Yes      | -  | Fetch URI |
| additionalClasses  | string     | No      | -  | String comprised of additional classes to be added to the ```<form>```. Syntax is comma-separated classes such as ``` 'customClass, customClass2' ``` |
| reloadOnSubmit  | bool     | No      | -  | Should the page refresh after the form has successfully submitted and received a HTTP Status OK. If status code is >= 300, it will not refresh |
| redirectOnSubmit  | string     | No      | -  | URL where we redirect after a successful form submission |
| deleteCurrentRowOnSubmit  | bool     | No      | -  | If the form is in a table, provides the ability to remove the current tr tag, useful for deleting operations |
| confirm  | bool     | No      | -  | If the form will present a Modal before submitting the form |
| confirmText  | string     | No      | 'Are you sure?'  | What text should appear on the modal. It can support text or html. Needs ```confirm``` to be set and be ```true``` |
| doubleConfirm  | bool     | No      | -  | If this is set and set to ```true``` the form will present a special Modal that would require a special ```keyword``` to be provided before the Submit button is enabled |
| doubleConfirmKeyWord  | string     | No      | -  | Provides the ```keyword``` to be put if ```doubleConfirm``` is set to ```true``` |
| resultType | string | No | ' | Values of ```html``` or ```text``` only. If ```html```, the result will be displayed as ```.innerHTML```, otherwise as ```.innerText``` in the result div |
| submitButton | Array | Yes | - | Array of settings for the submit Button of the form, check below for more info |

##### inputs Array

| Option   | Type     | Required | Default | Description           |
|----------|----------|----------|---------|-----------------------|
| input  | string   | No      | -       | text of the button|
| textarea  | string   | No      | ''       | unlike ```text```, style will completely replace the button markup, useful for example with ```'&#10060;'``` as value|
| tinymce  | string   | No      | -       | text of the button|
| checkbox  | string   | No      | -       | text of the button|
| checkboxGroup  | string   | No      | -       | text of the button|
| toggle  | string   | No      | -       | text of the button|
| select  | string   | No      | -       | text of the button|
| hidden  | string   | No      | -       | text of the button|

###### Generic attributes

| Attr   | Type     | Required | Default | Description           |
|----------|----------|----------|---------|-----------------------|
| value  | string   | No      | -       | value for the value="" attr|
| label  | string   | No      | -       | label text, shows above the input|
| description  | string   | No      | -       | description text, shows below the input|
| title  | string   | No      | -       | value for the title="" attr|
| id  | string   | No      | -       | value for the id="" attr|
| disabled  | bool   | No      | -       | whether to mark the input as disabled|
| required  | bool   | No      | -       | whether to mark the input as required|
| readonly  | string   | No      | -       | whether to mark the input as readonly|
| checked  | bool   | No      | -       | for checkboxes, if it's checked or not|
| placeholder  | string   | No      | -       | value for the placeholder="" attr|
| regex  | string   | No      | -       | value for the pattern="" attr|
| extraClasses  | array   | No      | -       | to add custom classes. Syntax is array ``` ['customClass', 'customClass2'] ```|
| dataAttributes  | array   | No      | -       | text of the button|
| min  | string   | No      | -       | value for the min="" attr in input type number|
| max  | string   | No      | -       | value for the min="" attr in input type number|
| step  | string   | No      | -       | value for the step="" attr in input type number|

###### Input-specific attributes

```Input```

| Attr   | Type     | Required | Default | Description           |
|----------|----------|----------|---------|-----------------------|
| type  | string   | Yes      | -       | you can place the original types freely such as ```text```, ```email```, ```password``` and etc|
| name  | string   | Yes      | -       | value for the name="" attr|
| size  | string   | No      | default       | ```default```, ```small``` or ```large```|

##### submitButton Array

| Option   | Type     | Required | Default | Description           |
|----------|----------|----------|---------|-----------------------|
| text  | string   | No      | medium       | text of the button|
| style  | string   | No      | ''       | unlike ```text```, style will completely replace the button markup, useful for example with ```'&#10060;'``` as value|
| id  | string   | No      | ''       | value for the id="" attr|
| size  | string   | No      | medium       | Values are small, medium, big|
| disabled  | bool   | No      | false       | true or false|
| title  | string   | No      | ''       | value for the title="" attr|
| name  | string   | No      | ''       | value for the name="" attr|
| type  | string   | No      | submit       | value for the type="" attr|
