<?php

declare(strict_types=1);

namespace Components;

use Components\Html;
use App\Security\CSRF;

class Forms
{
    public static function render(array $options, string $theme = COLOR_SCHEME): string
    {
        $html = '';

        $theme = (isset($options['theme'])) ? $options['theme'] : $theme;

        // Optional target attribute
        $target = (isset($options['target'])) ? 'target="' . $options['target']  . '"' : '';

        // Let's make some checks on required form options
        $formOptionsRequired = ['action'];

        foreach ($formOptionsRequired as $formOptionRequired) {
            if (!isset($options[$formOptionRequired])) {
                throw new \Exception($formOptionRequired . ' is a required form option');
            }
        }

        $formAttributes = self::formAttributes($options);

        $html .= '<div class="w-max-full">';
            // Prepare an id for the form, if passed
            $id = (isset($options['id'])) ? 'id="' . $options['id'] . '"' : 'id="' . uniqid() . '"';
            $method = (isset($options['method'])) ? $options['method'] : 'POST';
            $html .= '<form ' . $id . ' class="' . self::formClasses($options) . '" action="' . $options['action'] . '"' . $formAttributes . ' ' . $target . ' method="' . $method . '">';

        if (!isset($options['inputs'])) {
            throw new \Exception('inputs is a required form option');
        }

        foreach ($options['inputs'] as $inputType => $inputArray) {
            foreach ($inputArray as $inputOptionsArray) {
                // Now let's conditionally set the optional input options
                $value = (isset($inputOptionsArray['value'])) ? $inputOptionsArray['value'] : '';
                $label = (isset($inputOptionsArray['label'])) ? $inputOptionsArray['label'] : '';
                $description = (isset($inputOptionsArray['description'])) ? $inputOptionsArray['description'] : '';
                $title = (isset($inputOptionsArray['title'])) ? $inputOptionsArray['title'] : '';
                $id = (isset($inputOptionsArray['id'])) ? $inputOptionsArray['id'] : uniqid();

                // Setup the meta such as readonly, disabled, required
                $disabled = (isset($inputOptionsArray['disabled']) && $inputOptionsArray['disabled']) ? true : false;
                $required = (isset($inputOptionsArray['required']) && $inputOptionsArray['required']) ? true : false;
                $readonly = (isset($inputOptionsArray['readonly']) && $inputOptionsArray['readonly']) ? true : false;
                $checked = (isset($inputOptionsArray['checked']) && $inputOptionsArray['checked']) ? true : false;
                $placeholder = isset($inputOptionsArray['placeholder']) ? $inputOptionsArray['placeholder'] : '';
                $regex = (isset($inputOptionsArray['regex'])) ? $inputOptionsArray['regex'] : '';
                $extraClasses = (isset($inputOptionsArray['extraClasses'])) ? $inputOptionsArray['extraClasses'] : [];
                $dataAttributes = (isset($inputOptionsArray['dataAttributes'])) ? $inputOptionsArray['dataAttributes'] : [];
                // Set min max values if provided
                $min = (isset($inputOptionsArray['min'])) ? $inputOptionsArray['min'] : null;
                $max = (isset($inputOptionsArray['max'])) ? $inputOptionsArray['max'] : null;
                // Step
                $step = (isset($inputOptionsArray['step'])) ? $inputOptionsArray['step'] : null;

                // Inputs such as text, email .. etc
                if ($inputType === 'input') {
                    // These are the required input options
                    $requiredInputOptions = ['type', 'name'];

                    foreach ($requiredInputOptions as $requiredInputOption) {
                        if (!isset($inputOptionsArray[$requiredInputOption])) {
                            throw new \Exception($requiredInputOption . ' is a required input option');
                        }
                    }


                    // Size of the input
                    $allowedSizes = ['default', 'small', 'large'];
                    // Kill if not the correct size but size is optional
                    if (isset($inputOptionsArray['size']) && !in_array($inputOptionsArray['size'], $allowedSizes)) {
                        throw new \Exception('Size must be one of the following: ' . implode(', ', $allowedSizes));
                    }
                    $size = (isset($inputOptionsArray['size'])) ? $inputOptionsArray['size'] : 'default';
                    // Pull the input from the Html::input method
                    $html .= Html::input($size, $inputOptionsArray['type'], $id, $inputOptionsArray['name'], $title, $value, $placeholder, $description, $label, $theme, $disabled, $required, $readonly, true, $min, $max, $step, $regex, $extraClasses, $dataAttributes);
                }
                // If textarea
                if ($inputType === 'textarea') {
                    $rows = (isset($inputOptionsArray['rows'])) ? $inputOptionsArray['rows'] : 10;
                    $cols = (isset($inputOptionsArray['cols'])) ? $inputOptionsArray['cols'] : 100;
                    $html .= Html::textArea($id, $inputOptionsArray['name'], $value, $placeholder, $title, $description, $label, $theme, $disabled, $required, $readonly, $rows, $cols, $extraClasses, $dataAttributes);
                }
                // If tinymce
                if ($inputType === 'tinymce') {
                    $html .= '<div class="my-4">';
                        // If id is not passed, let's create a unique id
                    if ($id === null || empty($id)) {
                        $id = 'tinymce-' . uniqid();
                    }
                        $html .= Html::label($id, $label, $required);
                        $html .= '<textarea id="' . $id . '" class="tinymce" name="' . $inputOptionsArray['name'] . '"></textarea>';
                        $html .= Html::p($description);
                        $html .= '</div>';
                }
                // Inputs such as checkbox
                if ($inputType === 'checkbox') {
                    $html .= Html::checkbox($id, $inputOptionsArray['name'], $value, $label, $description, $required, $checked, $disabled, $readonly, $theme, $extraClasses, $dataAttributes);
                }
                // Inputs checbox group
                if ($inputType === 'checkboxGroup') {
                    $checboxGroupClass = 'checkbox-group-' . uniqid();
                    $html .= Html::h5($label);
                    $html .= '<div class="border border-gray-200 dark:border-gray-700 p-4 rounded-lg my-4">';
                        array_push($extraClasses, $checboxGroupClass);
                        $name = $inputOptionsArray['name'];
                        // Loop through the checkbox groups
                        $idConcurrence = 0;
                    foreach ($inputOptionsArray['checkboxes'] as $checkbox) {
                        $idConcurrence++;
                        $readonly = (isset($checkbox['readonly']) && $checkbox['readonly']) ? true : false;
                        $disabled = (isset($checkbox['disabled']) && $checkbox['disabled']) ? true : false;
                        //$html .= Html::label($id . '-' . $idConcurrence, $label);
                        $html .= Html::checkbox($id . '-' . $idConcurrence, $name, $checkbox['value'], $checkbox['label'], $checkbox['description'], false, $checkbox['checked'], $disabled, $readonly, $theme, $extraClasses, $dataAttributes);
                    }
                        $html .= '<p class="mt-2 text-xs text-gray-500 dark:text-gray-400">' . $inputOptionsArray['description'] . '</p>';
                        $html .= '</div>';
                }
                if ($inputType === 'toggle') {
                    $html .= '<div class="my-2">';
                        $html .= Html::toggleCheckBox($id, $inputOptionsArray['name'], $inputOptionsArray['description'], $checked, $theme, $disabled);
                    $html .= '</div>';
                }
                if ($inputType === 'select') {
                    $html .= '<div class="mb-6">';
                        $html .= '<div class="my-2">';
                            $title = (isset($inputOptionsArray['title'])) ? 'title="' . $inputOptionsArray['title'] . '"' : '';
                            // Add a search input if searchable is set to true
                    if (isset($inputOptionsArray['searchable']) && $inputOptionsArray['searchable']) {
                        $html .= Html::searchInput($theme);
                    }
                            $disabled = (isset($inputOptionsArray['disabled']) && $inputOptionsArray['disabled']) ? 'disabled' : '';
                            $requiredAsterix = (isset($inputOptionsArray['required']) && $inputOptionsArray['required']) ? '<span class="text-red-500"> *</span>' : '';
                            $html .= '<label for="' . $id . '" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">' . $inputOptionsArray['label'] . $requiredAsterix . '</label>';
                            $selectDivFlex = (isset($inputOptionsArray['searchFlex'])) ? $inputOptionsArray['searchFlex'] : 'flex-row';
                            $html .= '<div class="w-fit flex ' . $selectDivFlex . ' flex-wrap">';
                    if (isset($inputOptionsArray['search']) && $inputOptionsArray['search']) {
                        $html .= Html::searchInput($theme);
                    }
                                $html .= '<select id="' . $id . '" name="' . $inputOptionsArray['name'] . '" class="' . Html::selectInputClasses($theme) . '" ' . $disabled . $title . ' autocomplete="on">';
                                $selectedOption = $inputOptionsArray['selected_option'] ?? null;
                    foreach ($inputOptionsArray['options'] as $array) {
                        if ($selectedOption !== null && $selectedOption === $array['value']) {
                            $html .= '<option value="' . $array['value'] . '" selected>' . $array['text'] . '</option>';
                        } else {
                            $html .= '<option value="' . $array['value'] . '">' . $array['text'] . '</option>';
                        }
                    }
                                $html .= '</select>';
                                $html .= (isset($inputOptionsArray['description'])) ? '<p class="mt-2 text-xs text-gray-500 dark:text-gray-400">' . $inputOptionsArray['description'] . '</p>' : null;
                                $html .= '</div>';
                                $html .= '</div>';
                                $html .= '</div>';
                }
                // Hidden inputs
                if ($inputType === 'hidden') {
                    $html .= '<input type="hidden" name="' . $inputOptionsArray['name'] . '" value="' . $inputOptionsArray['value'] . '" />';
                }
            }
        }
            $html .= CSRF::createTag();
            // Submit button now
        if (!isset($options['submitButton'])) {
            throw new \Exception('submitButton is a required form option');
        }

            $additionalButtonClasses = [];

        if (!isset($options['submitButton']['size'])) {
            $options['submitButton']['size'] = 'medium';
        }

        if ($options['submitButton']['size'] === 'large') {
            array_push($additionalButtonClasses, 'p-3', 'text-lg');
        } elseif ($options['submitButton']['size'] === 'medium') {
            array_push($additionalButtonClasses, 'px-2', 'py-1', 'text-md');
        } elseif ($options['submitButton']['size'] === 'small') {
            array_push($additionalButtonClasses, 'p-1', 'text-sm');
        }
        if (isset($options['submitButton']['disabled']) && $options['submitButton']['disabled'] === true) {
            array_push($additionalButtonClasses, 'opacity-50', 'cursor-not-allowed');
            $buttonDisabled = 'disabled';
        } else {
            $buttonDisabled = '';
        }
        if (isset($options['submitButton']['title'])) {
            $buttonTitle = 'title="' . $options['submitButton']['title'] . '"';
        } else {
            $buttonTitle = '';
        }
            // Name
        if (isset($options['submitButton']['name'])) {
            $buttonName = 'name="' . $options['submitButton']['name'] . '"';
        } else {
            $buttonName = '';
        }
            // Type
        if (isset($options['submitButton']['type'])) {
            $buttonType = 'type="' . $options['submitButton']['type'] . '"';
        } else {
            $buttonType = 'type="submit"';
        }
            // Id
        if (isset($options['submitButton']['id'])) {
            $buttonId = 'id="' . $options['submitButton']['id'] . '"';
        } else {
            $buttonId = '';
        }
        if (isset($options['submitButton']['style'])) {
            $html .= '<div class="my-2"><button ' . $buttonTitle . ' class="' . implode(' ', $additionalButtonClasses) . ' cursor-pointer" ' . $buttonDisabled . '>' . $options['submitButton']['style'] . '</button></div>';
        } elseif (isset($options['submitButton']['text'])) {
            $html .= '
                        <div class="mt-4">
                            <button ' . $buttonId . ' ' . $buttonTitle . ' ' . $buttonType . ' ' . $buttonName . ' class="' . implode(' ', $additionalButtonClasses) . ' my-2 inline-flex items-center justify-center text-md leading-7 text-' . $theme . '-50 bg-' . $theme . '-500 hover:bg-' . $theme . '-600 font-medium focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-opacity-50 border border-transparent rounded-md shadow-sm" ' . $buttonDisabled . '>
                                ' . $options['submitButton']['text'] . '
                            </button>
                        </div>';
        } else {
            throw new \Exception('submitButton text or style is a required form option');
        }
            $html .= '</form>';
        $html .= '</div>';
        return $html;
    }
    public static function formAttributes(array $options): string
    {
        // Now let's create an array that will hold the form data-attributes
        $formAttributesArray = [];

        if (isset($options['headers'])) {
            $formAttributesArray['data-headers'] = htmlspecialchars(json_encode($options['headers']));
        }

        // confirmText is optional, if it's not passed, we will use the default text
        if (isset($options['confirmText'])) {
            $formAttributesArray['data-confirm'] = $options['confirmText'];
        } else {
            $formAttributesArray['data-confirm'] = 'Are you sure?';
        }

        if (isset($options['triggerJS'])) {
            // If triggerJS is set to true, we will add a data-trigger attribute to the form
            if (!is_bool($options['triggerJS'])) {
                throw new \Exception('triggerJS option must be a boolean');
            }
            if (!$options['triggerJS']) {
                $formAttributesArray['data-trigger'] = 'false';
            }
        }

        // Double confirm feature, first let's see if it's passed
        if (isset($options['doubleConfirm'])) {
            // If it is passed, let's make sure it's a boolean, if not throw an exception
            if (!isset($options['doubleConfirm']) || !is_bool($options['doubleConfirm'])) {
                throw new \Exception('doubleConfirm option must be a boolean');
            }
            // We need confirmText to be passed as well for double confirm to work
            if (!isset($options['doubleConfirmKeyWord'])) {
                throw new \Exception('doubleConfirmKeyWord option must be passed if doubleConfirm is defined');
            }
            if ($options['doubleConfirmKeyWord']) {
                $formAttributesArray['data-double-confirm-keyword'] = $options['doubleConfirmKeyWord'];
            }
        }

        // Now reloadOnSubmit feature, first let's see if it's passed
        if (isset($options['reloadOnSubmit'])) {
            // If it is passed, let's make sure it's a boolean, if not throw an exception
            if (!is_bool($options['reloadOnSubmit'])) {
                throw new \Exception('reloadOnSubmit option must be a boolean');
            }
            if ($options['reloadOnSubmit']) {
                $formAttributesArray['data-reload'] = 'true';
            }
        }

        // Now redirectOnSubmit feature, first let's see if it's passed
        if (isset($options['redirectOnSubmit'])) {
            // If it is passed, let's make sure it's a boolean, if not throw an exception
            if (!is_string($options['redirectOnSubmit'])) {
                throw new \Exception('redirectOnSubmit option must be a string');
            }
            $formAttributesArray['data-redirect'] = $options['redirectOnSubmit'];
        }

        // Now resultType feature, first let's see if it's passed
        if (isset($options['resultType'])) {
            // If it is passed, let's make sure it's a string, if not throw an exception
            if (
                !is_string($options['resultType']) && !is_null($options['resultType']) && !empty($options['resultType'])
            ) {
                throw new \Exception('resultType option must be a string');
            }
            // Let's make another check and only allow values of html and text
            if ($options['resultType'] !== 'html' && $options['resultType'] !== 'text') {
                throw new \Exception('resultType option must be either html or text');
            }
            $formAttributesArray['data-result'] = $options['resultType'];
        }
        // Now deleteCurrentRowOnSubmit feature, first let's see if it's passed
        if (isset($options['deleteCurrentRowOnSubmit'])) {
            // If it is passed, let's make sure it's a boolean, if not throw an exception
            if (!is_bool($options['deleteCurrentRowOnSubmit'])) {
                throw new \Exception('deleteCurrentRowOnSubmit option must be a boolean');
            }
            $formAttributesArray['data-delete-current-row'] = $options['deleteCurrentRowOnSubmit'];
        }

        if (isset($options['method'])) {
            $formAttributesArray['data-method'] = $options['method'];
        } else {
            $formAttributesArray['data-method'] = 'POST';
        }

        // We are done collecting data-attributes, let's add them to the form by first assigning them to a variable
        $formAttributes = '';

        if (!empty($formAttributesArray)) {
            foreach ($formAttributesArray as $key => $value) {
                $formAttributes .= $key . '="' . $value . '" ';
            }
        }

        // Stopwatch
        if (isset($options['stopwatch'])) {
            $formAttributes .= 'data-stopwatch="' . $options['stopwatch'] . '" ';
        }

        return $formAttributes;
    }
    public static function formClasses(array $options): string
    {
        $formClassesArray = [];

        array_push($formClassesArray, 'generic-form');
        array_push($formClassesArray, 'mb-4');

        // Let's see if additionalClasses is passed, if it is, let's add it to the formClassesArray
        if (isset($options['additionalClasses'])) {
            // additionalClasses will be a string of classes separated by a space
            $additionalClassesArray = explode(' ', $options['additionalClasses']);
            // Let's loop through the array and add each class to the formClassesArray
            foreach ($additionalClassesArray as $additionalClass) {
                array_push($formClassesArray, $additionalClass);
            }
        }

        if (isset($options['doubleConfirmKeyWord'])) {
            array_push($formClassesArray, 'double-confirm');
        }

        // Confirm feature, first let's see if it's passed
        if (isset($options['confirm'])) {
            // If it is passed, let's make sure it's a boolean, if not throw an exception
            if (!is_bool($options['confirm'])) {
                throw new \Exception('confirm option must be a boolean');
            }
            if ($options['confirm']) {
                array_push($formClassesArray, 'confirm');
            }
        }

        return implode(' ', $formClassesArray);
    }
}
