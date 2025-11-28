<?php

declare(strict_types=1);

namespace App\Utilities;

/*
 Example json settings file:
 {
    "auth_expiry": {
        "value": 150029,
        "label": "Auth Expiry",
        "description": "Duration in seconds for which the authentication is valid",
        "type": "number"
    },
    "default_data_grid_engine": {
        "value": "DataGrid",
        "label": "Default Data Grid Engine",
        "description": "The default data grid engine to use ('AGGrid', 'DataGrid')",
        "type": "string"
    }
}
*/

class JsonSettingEditor
{
    public static function editJsonFile(string $filePath, array $newData, bool $overwrite = false): bool
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File does not exist: $filePath");
        }

        $jsonContent = @file_get_contents($filePath);
        if ($jsonContent === false) {
            $error = error_get_last();
            throw new \RuntimeException("Failed to read file '$filePath'. Error: " . ($error['message'] ?? 'unknown'));
        }

        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decode error in '$filePath': " . json_last_error_msg());
        }

        if ($overwrite) {
            $data = $newData;
        } else {
            if (!is_array($data)) {
                $data = [];
            }
        }

        $updatedData = array_merge($data, $newData);

        $newJsonContent = json_encode($updatedData, JSON_PRETTY_PRINT);
        if ($newJsonContent === false) {
            throw new \RuntimeException("Failed to encode JSON: " . json_last_error_msg());
        }

        $result = @file_put_contents($filePath, $newJsonContent);
        if ($result === false) {
            $error = error_get_last();
            throw new \RuntimeException("Failed to write file '$filePath'. Error: " . ($error['message'] ?? 'unknown'));
        }

        return true;
    }

    /**
     * Render a JSON file editor with proper form layout
     * Supports different field types including boolean toggles
     * 
     * @param string $filePath Path to the JSON file
     * @param string $theme Theme for styling (default: 'blue')
     * @param array $fieldConfig Configuration for field types and labels (deprecated - use self-describing JSON)
     * @return string HTML form for editing the JSON file
     */
    public static function renderJsonEditor(string $filePath, string $theme = 'blue', array $fieldConfig = []): string
    {
        // Load JSON data
        if (!file_exists($filePath)) {
            return \Components\Alerts::danger('JSON file not found: ' . htmlspecialchars($filePath));
        }

        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            return \Components\Alerts::danger('Unable to read JSON file: ' . htmlspecialchars($filePath));
        }

        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return \Components\Alerts::danger('Invalid JSON format: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            return \Components\Alerts::warning('JSON file does not contain an object/array structure');
        }

        // Generate unique identifier for this form based on filename
        $uniqueId = 'json-editor-' . preg_replace('/[^a-zA-Z0-9]/', '-', basename($filePath, '.json'));

        // Start building the form
        $html = '';
        
        // Form header
        $html .= '<div class="max-w-4xl mx-auto my-4 p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">';
        
        // File info
        $html .= '<div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">';
        $html .= '<div class="flex items-center gap-2 text-blue-800 dark:text-blue-200">';
        $html .= '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
        $html .= '</svg>';
        $html .= '<span class="font-medium">Editing:</span>';
        $html .= '<code class="ml-1 px-2 py-1 bg-blue-100 dark:bg-blue-800 rounded text-sm">' . htmlspecialchars(basename($filePath)) . '</code>';
        $html .= '</div>';
        $html .= '</div>';

        // Start form with unique data attribute
        $html .= '<form method="POST" action="/api/edit-json-settings" class="space-y-6 json-editor-form" data-editor-id="' . $uniqueId . '">';
        $html .= \App\Security\CSRF::createTag();
        $html .= '<input type="hidden" name="json_file_path" value="' . htmlspecialchars($filePath) . '">';
        
        // Generate fields
        $html .= self::renderJsonFields($data, $fieldConfig, $theme, '', $uniqueId);
        
        // Action buttons
        $html .= '<div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-600">';
        
        // Reset button with unique ID
        $html .= '<button type="button" id="' . $uniqueId . '-reset-btn" ';
        $html .= 'data-editor-id="' . $uniqueId . '" ';
        $html .= 'class="json-editor-reset-btn px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">';
        $html .= '<svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>';
        $html .= '</svg>';
        $html .= 'Reset to Original';
        $html .= '</button>';
        
        // Save button with unique ID
        $html .= '<button type="submit" name="save_json" value="1" ';
        $html .= 'data-editor-id="' . $uniqueId . '" ';
        $html .= 'class="json-editor-submit-btn px-6 py-2 text-sm font-medium text-white bg-' . $theme . '-600 hover:bg-' . $theme . '-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-' . $theme . '-500 rounded-lg transition-colors duration-200">';
        $html .= '<svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>';
        $html .= '</svg>';
        $html .= 'Save Configuration';
        $html .= '</button>';
        
        $html .= '</div>';
        $html .= '</form>';
        
        // Link external JavaScript file only once per page
        static $jsIncluded = false;
        if (!$jsIncluded) {
            $html .= '<script src="/assets/js/json-editor.js"></script>';
            $jsIncluded = true;
        }
        
        $html .= '</div>';

        return $html;
    }

    /**
     * Recursively render JSON fields with proper input types
     * 
     * @param array $data The JSON data to render
     * @param array $fieldConfig Configuration for field types (deprecated - use self-describing JSON)
     * @param string $theme Theme for styling
     * @param string $prefix Field name prefix for nested objects
     * @param string $uniqueId Unique identifier for this form instance
     * @return string HTML for the fields
     */
    private static function renderJsonFields(array $data, array $fieldConfig, string $theme, string $prefix = '', string $uniqueId = ''): string
    {
        $html = '';
        
        foreach ($data as $key => $value) {
            $fieldName = $prefix ? $prefix . '[' . $key . ']' : $key;
            $fieldId = $uniqueId . '-' . str_replace(['[', ']'], ['_', ''], $fieldName);
            
            $html .= '<div class="mb-6">';
            
            // Check if this is a self-describing field structure
            if (is_array($value) && isset($value['value']) && isset($value['label'])) {
                // Self-describing field
                $fieldValue = $value['value'];
                $displayLabel = $value['label'];
                $description = $value['description'] ?? '';
                $fieldType = $value['type'] ?? self::detectFieldType($fieldValue);
                
                // Update field name to include [value] for form submission
                $fieldName = $prefix ? $prefix . '[' . $key . '][value]' : $key . '[value]';
            } elseif (is_array($value)) {
                // Handle nested objects (legacy or complex structures)
                $displayLabel = $fieldConfig[$key]['label'] ?? ucwords(str_replace(['_', '-'], ' ', $key));
                $description = $fieldConfig[$key]['description'] ?? '';
                
                $html .= '<div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">';
                $html .= \Components\Html::h4($displayLabel, false, ['mb-4', 'text-gray-800', 'dark:text-gray-200']);
                if ($description) {
                    $html .= '<p class="text-sm text-gray-600 dark:text-gray-400 mb-4">' . htmlspecialchars($description) . '</p>';
                }
                $html .= self::renderJsonFields($value, $fieldConfig[$key]['fields'] ?? [], $theme, $fieldName, $uniqueId);
                $html .= '</div>';
                continue;
            } else {
                // Simple field (legacy format)
                $fieldValue = $value;
                $displayLabel = $fieldConfig[$key]['label'] ?? ucwords(str_replace(['_', '-'], ' ', $key));
                $description = $fieldConfig[$key]['description'] ?? '';
                $fieldType = $fieldConfig[$key]['type'] ?? self::detectFieldType($value);
            }
            
            // Label
            $html .= '<label for="' . $fieldId . '" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">';
            $html .= htmlspecialchars($displayLabel);
            $html .= '</label>';
            
            // Description
            if ($description) {
                $html .= '<p class="text-xs text-gray-500 dark:text-gray-400 mb-2">' . htmlspecialchars($description) . '</p>';
            }
            
            // Render the appropriate input field
            switch ($fieldType) {
                case 'boolean':
                case 'toggle':
                    $checked = (bool)$fieldValue;
                    $html .= \Components\Html::toggleCheckBox($fieldId, $fieldName, '', $checked, $theme, false);
                    break;
                    
                case 'textarea':
                case 'text_long':
                    $html .= '<textarea name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '" rows="4" ';
                    $html .= 'class="block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg ';
                    $html .= 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 ';
                    $html .= 'focus:ring-' . $theme . '-500 focus:border-' . $theme . '-500 ';
                    $html .= 'placeholder-gray-400 dark:placeholder-gray-500" ';
                    $html .= 'placeholder="Enter ' . htmlspecialchars(strtolower($displayLabel)) . '">';
                    $html .= htmlspecialchars((string)$fieldValue);
                    $html .= '</textarea>';
                    break;
                    
                case 'number':
                case 'integer':
                    $html .= '<input type="number" name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '" ';
                    $html .= 'value="' . htmlspecialchars((string)$fieldValue) . '" ';
                    $html .= 'class="block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg ';
                    $html .= 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 ';
                    $html .= 'focus:ring-' . $theme . '-500 focus:border-' . $theme . '-500 ';
                    $html .= 'placeholder-gray-400 dark:placeholder-gray-500" ';
                    $html .= 'placeholder="Enter ' . htmlspecialchars(strtolower($displayLabel)) . '">';
                    break;
                    
                case 'select':
                    $options = $fieldConfig[$key]['options'] ?? [];
                    $html .= '<select name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '" ';
                    $html .= 'class="block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg ';
                    $html .= 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 ';
                    $html .= 'focus:ring-' . $theme . '-500 focus:border-' . $theme . '-500">';
                    foreach ($options as $optionValue => $optionLabel) {
                        $selected = ($fieldValue == $optionValue) ? 'selected' : '';
                        $html .= '<option value="' . htmlspecialchars((string)$optionValue) . '" ' . $selected . '>';
                        $html .= htmlspecialchars($optionLabel);
                        $html .= '</option>';
                    }
                    $html .= '</select>';
                    break;
                    
                case 'password':
                    $html .= '<input type="password" name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '" ';
                    $html .= 'value="' . htmlspecialchars((string)$fieldValue) . '" ';
                    $html .= 'class="block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg ';
                    $html .= 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 ';
                    $html .= 'focus:ring-' . $theme . '-500 focus:border-' . $theme . '-500 ';
                    $html .= 'placeholder-gray-400 dark:placeholder-gray-500" ';
                    $html .= 'placeholder="Enter ' . htmlspecialchars(strtolower($displayLabel)) . '">';
                    break;
                    
                default: // text
                    $html .= '<input type="text" name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '" ';
                    $html .= 'value="' . htmlspecialchars((string)$fieldValue) . '" ';
                    $html .= 'class="block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg ';
                    $html .= 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 ';
                    $html .= 'focus:ring-' . $theme . '-500 focus:border-' . $theme . '-500 ';
                    $html .= 'placeholder-gray-400 dark:placeholder-gray-500" ';
                    $html .= 'placeholder="Enter ' . htmlspecialchars(strtolower($displayLabel)) . '">';
                    break;
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }

    /**
     * Auto-detect field type based on value
     * 
     * @param mixed $value The value to analyze
     * @return string The detected field type
     */
    private static function detectFieldType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        
        if (is_int($value) || is_float($value)) {
            return 'number';
        }
        
        if (is_string($value)) {
            // Auto-detect based on content
            if (strlen($value) > 100) {
                return 'textarea';
            }
            
            if (strpos(strtolower($value), 'password') !== false || 
                strpos(strtolower($value), 'secret') !== false ||
                strpos(strtolower($value), 'key') !== false) {
                return 'password';
            }
        }
        
        return 'text';
    }

    /**
     * Process form submission and save to JSON file
     * 
     * @param array $postData The $_POST data from form submission
     * @return array Result with success status and message
     */
    public static function processJsonEditorSubmission(array $postData): array
    {
        if (!isset($postData['json_file_path'])) {
            return ['success' => false, 'message' => 'Missing JSON file path in form submission'];
        }

        // The save_json field might not be present in AJAX submissions, so let's make it optional
        if (!isset($postData['save_json'])) {
            // For AJAX submissions, we can assume this is a save operation
            $postData['save_json'] = '1';
        }

        $filePath = $postData['json_file_path'];
        unset($postData['json_file_path'], $postData['save_json']);

        // Remove CSRF token if present
        if (isset($postData['csrf_token'])) {
            unset($postData['csrf_token']);
        }

        // Load original data to preserve boolean structure
        $originalData = [];
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            if ($jsonContent !== false) {
                $decoded = json_decode($jsonContent, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $originalData = $decoded;
                }
            }
        }

        // Process the form data to restore proper data types
        $processedData = self::processFormDataTypes($postData, $originalData);

        // Save to JSON file
        $success = self::editJsonFile($filePath, $processedData, true);

        if ($success) {
            return [
                'success' => true,
                'message' => 'Configuration saved successfully to ' . basename($filePath)
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to save configuration file'
            ];
        }
    }

    /**
     * Process form data to restore proper data types (boolean, numeric)
     * 
     * @param array $data The form data to process
     * @param array $originalData The original JSON data to compare against
     * @return array Processed data with correct types
     */
    private static function processFormDataTypes(array $data, array $originalData = []): array
    {
        $processed = [];
        
        // First, copy all original values to preserve structure
        foreach ($originalData as $key => $originalValue) {
            if (is_array($originalValue) && isset($originalValue['value'])) {
                // Handle self-describing field structure
                $originalFieldValue = $originalValue['value'];
                $fieldType = $originalValue['type'] ?? 'text';
                
                if ($fieldType === 'boolean' || $fieldType === 'toggle') {
                    // For booleans, check if the key[value] exists in form data
                    $processed[$key] = $originalValue; // Keep metadata
                    $processed[$key]['value'] = isset($data[$key]['value']);
                } elseif (isset($data[$key]['value'])) {
                    // For other field types, process the value
                    $submittedValue = $data[$key]['value'];
                    $processed[$key] = $originalValue; // Keep metadata
                    
                    // Convert string representations back to proper types
                    if (is_numeric($submittedValue)) {
                        if (strpos($submittedValue, '.') !== false) {
                            $processed[$key]['value'] = (float)$submittedValue;
                        } else {
                            $processed[$key]['value'] = (int)$submittedValue;
                        }
                    } else {
                        $processed[$key]['value'] = $submittedValue;
                    }
                } else {
                    // Keep original if no form data
                    $processed[$key] = $originalValue;
                }
            } elseif (is_bool($originalValue)) {
                // Legacy boolean handling
                $processed[$key] = isset($data[$key]);
            } elseif (is_array($originalValue)) {
                // Handle nested arrays recursively
                $nestedData = $data[$key] ?? [];
                $processed[$key] = self::processFormDataTypes($nestedData, $originalValue);
            } else {
                // Legacy simple field handling
                if (isset($data[$key])) {
                    $value = $data[$key];
                    
                    // Convert string representations back to proper types
                    if (is_numeric($value)) {
                        if (strpos($value, '.') !== false) {
                            $processed[$key] = (float)$value;
                        } else {
                            $processed[$key] = (int)$value;
                        }
                    } else {
                        $processed[$key] = $value;
                    }
                } else {
                    $processed[$key] = $originalValue;
                }
            }
        }
        
        // Add any new fields that weren't in the original data
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $originalData)) {
                if (is_array($value)) {
                    $processed[$key] = self::processFormDataTypes($value);
                } elseif ($value === 'true' || $value === '1') {
                    $processed[$key] = true;
                } elseif ($value === 'false' || $value === '0') {
                    $processed[$key] = false;
                } elseif (is_numeric($value)) {
                    if (strpos($value, '.') !== false) {
                        $processed[$key] = (float)$value;
                    } else {
                        $processed[$key] = (int)$value;
                    }
                } else {
                    $processed[$key] = $value;
                }
            }
        }
        
        return $processed;
    }
}
