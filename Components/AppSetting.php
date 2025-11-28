<?php

declare(strict_types=1);

namespace Components;

use Components\Html;
use Models\AppSettings;
use App\Security\CSRF;

class AppSetting
{
    public static function renderSettings(array $names, string $theme): string
    {
        $appSettingsModel = new AppSettings();
        
        // Generate CSRF token for API requests
        $csrfToken = CSRF::create();
        
        // Generate unique identifier for this settings group
        $uniqueId = 'app-settings-' . substr(md5(implode('-', $names)), 0, 8);
        
        // Start with a beautiful container
        $html = '<div class="max-w-4xl mx-auto my-4 p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">';
        
        // Header section
        $html .= '<div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">';
        $html .= '<div class="flex items-center gap-2 text-blue-800 dark:text-blue-200">';
        $html .= '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>';
        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
        $html .= '</svg>';
        $html .= '<span class="font-medium text-lg">Application Settings</span>';
        $html .= '</div>';
        $html .= '<p class="text-sm text-blue-700 dark:text-blue-300 mt-2 ml-7">Configure your application settings below. Changes are saved individually.</p>';
        $html .= '</div>';
        
        // Settings container
        $html .= '<div class="space-y-6" data-settings-group="' . $uniqueId . '">';

        foreach ($names as $name) {
            $setting = $appSettingsModel->get($name);

            $value = $setting['value'];
            $type  = $setting['type'];
            $id    = $setting['id'];

            $input = '';
            $inputId = "setting_$id";

            $inputClasses = [
                DATAGRID_TBODY_COLOR_SCHEME,
                'border',
                'border-gray-300',
                TEXT_COLOR_SCHEME,
                'text-sm',
                'rounded-lg',
                'focus:ring-' . $theme . '-500',
                'focus:border-' . $theme . '-500',
                'block',
                'w-full',
                'p-2.5',
                DATAGRID_THEAD_DARK_COLOR_SCHEME,
                'dark:border-gray-600',
                'dark:placeholder-gray-400',
                TEXT_DARK_COLOR_SCHEME,
                'dark:focus:ring-' . $theme . '-500',
                'dark:focus:border-' . $theme . '-500',
                'outline-none',
            ];

            switch ($type) {

                case 'string':
                    if (strlen($value) > 100) {
                        $input = '
                            <textarea
                                id="' . $inputId . '"
                                data-type="string"
                                data-name="' . $name . '"
                                data-id="' . $id . '"
                                class="' . implode(' ', $inputClasses) . '"
                                rows="4"
                            >' . htmlspecialchars($value) . '</textarea>';
                    } else {
                        $input = '
                            <input
                                id="' . $inputId . '"
                                type="text"
                                data-type="string"
                                data-name="' . $name . '"
                                data-id="' . $id . '"
                                value="' . htmlspecialchars($value) . '"
                                class="' . implode(' ', $inputClasses) . '"
                            >';
                    }
                    break;

                case 'int':
                    $input = '
                        <input
                            id="' . $inputId . '"
                            type="number"
                            step="1"
                            data-type="int"
                            data-name="' . $name . '"
                            data-id="' . $id . '"
                            value="' . htmlspecialchars($value) . '"
                            class="' . implode(' ', $inputClasses) . '"
                        >';
                    break;

                case 'float':
                    $input = '
                        <input
                            id="' . $inputId . '"
                            type="number"
                            step="0.01"
                            data-type="float"
                            data-name="' . $name . '"
                            data-id="' . $id . '"
                            value="' . htmlspecialchars($value) . '"
                            class="' . implode(' ', $inputClasses) . '"
                        >';
                    break;

                case 'bool':
                    $checked = ($value === '1' || $value === 'true');
                    // must wrap toggle in a <div id="setting_ID_wrapper">
                    $input = '<div id="' . $inputId . '_wrapper" 
                                 data-type="bool" 
                                 data-name="' . $name . '" 
                                 data-id="' . $id . '">'
                                 .
                                 Html::toggleCheckBox(
                                     $inputId,
                                     $name,
                                     $name,
                                     $checked,
                                     $theme,
                                     false
                                 )
                             . '</div>';
                    break;

                case 'date':
                    $dtValue = str_replace(' ', 'T', $value);
                    $input = '
                        <input
                            id="' . $inputId . '"
                            type="datetime-local"
                            data-type="date"
                            data-name="' . $name . '"
                            data-id="' . $id . '"
                            value="' . htmlspecialchars($dtValue) . '"
                            class="w-full rounded-lg border border-gray-300 p-3"
                        >';
                    break;

                case 'json':
                    $pretty = json_encode(json_decode($value, true), JSON_PRETTY_PRINT);
                    $input = '
                        <textarea
                            id="' . $inputId . '"
                            data-type="json"
                            data-name="' . $name . '"
                            data-id="' . $id . '"
                            class="w-full font-mono rounded-lg border border-gray-300 p-3"
                            rows="6"
                        >' . htmlspecialchars($pretty) . '</textarea>';
                    break;

                default:
                    $input = '<div class="text-red-600">Unknown type: ' . htmlspecialchars($type) . '</div>';
            }

            // Beautiful card for each setting
            $html .= '<div class="border border-gray-200 dark:border-gray-600 rounded-lg p-5 bg-gray-50 dark:bg-gray-700/50 hover:shadow-md transition-shadow duration-200" data-setting-card="' . $id . '">';
            $html .= '<div class="flex items-center justify-between mb-2">';
            $html .= '<label for="' . $inputId . '" class="block text-sm font-medium text-gray-900 dark:text-gray-300">';
            $html .= htmlspecialchars($name);
            $html .= '</label>';
            
            // Display owner badge
            $owner = $setting['owner'] ?? 'system';
            $html .= '<span class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">';
            $html .= '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>';
            $html .= '</svg>';
            $html .= htmlspecialchars($owner);
            $html .= '</span>';
            $html .= '</div>';
            
            // Display description if it exists
            if (!empty($setting['description'])) {
                $html .= '<p class="text-xs text-gray-600 dark:text-gray-400 mb-3 italic">';
                $html .= htmlspecialchars($setting['description']);
                $html .= '</p>';
            }
            
            // Setting type badge
            $typeBadgeColors = [
                'string' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                'int' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                'float' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300',
                'bool' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                'date' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                'json' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
            ];
            $badgeClass = $typeBadgeColors[$type] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
            $html .= '<span class="inline-block px-2 py-1 text-xs font-semibold rounded-md mb-3 ' . $badgeClass . '">' . strtoupper($type) . '</span>';
            
            $html .= $input;
            
            // Action buttons container
            $html .= '<div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">';
            
            // Reset button
            $html .= '<button type="button" ';
            $html .= 'class="app-setting-reset-btn px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200" ';
            $html .= 'data-setting-id="' . $id . '" ';
            $html .= 'data-original-value="' . htmlspecialchars(json_encode($value)) . '" ';
            $html .= 'data-input-id="' . $inputId . '">';
            $html .= '<svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>';
            $html .= '</svg>';
            $html .= 'Reset';
            $html .= '</button>';
            
            // Save button
            $html .= '<button type="button" ';
            $html .= 'class="app-setting-save-btn px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-lg transition-colors duration-200 opacity-50 cursor-not-allowed" ';
            $html .= 'data-setting-id="' . $id . '" ';
            $html .= 'data-setting-name="' . htmlspecialchars($name) . '" ';
            $html .= 'data-setting-type="' . $type . '" ';
            $html .= 'data-input-id="' . $inputId . '" ';
            $html .= 'data-is-changed="false">';
            $html .= '<svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>';
            $html .= '</svg>';
            $html .= 'Save';
            $html .= '</button>';
            
            // Status indicator
            $html .= '<span class="app-setting-status ml-auto text-sm" data-setting-id="' . $id . '"></span>';
            
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>'; // Close settings container
        $html .= '</div>'; // Close main container

        // Add comprehensive JavaScript for settings management
        $nonce = '1nL1n3JsRuN1192kwoko2k323WKE';
        $html .= '<script nonce="' . $nonce . '">';
        $html .= 'const CSRF_TOKEN = "' . $csrfToken . '";' . "\n";
        $html .= <<<'JAVASCRIPT'
(function() {
    'use strict';
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAppSettings);
    } else {
        initAppSettings();
    }
    
    function initAppSettings() {
        console.log('AppSettings: Initializing settings management');
        
        // Track original values for change detection
        const originalValues = new Map();
        
        // Initialize all settings
        const settingCards = document.querySelectorAll('[data-setting-card]');
        console.log('AppSettings: Found', settingCards.length, 'setting cards');
        
        settingCards.forEach(card => {
            const settingId = card.dataset.settingCard;
            const saveBtn = card.querySelector('.app-setting-save-btn');
            
            if (!saveBtn) {
                console.error('AppSettings: Save button not found for setting', settingId);
                return;
            }
            
            const inputId = saveBtn.dataset.inputId;
            const type = saveBtn.dataset.settingType;
            
            console.log('AppSettings: Initializing setting', settingId, 'type:', type, 'inputId:', inputId);
            
            // Find the input element
            let inputElement = document.getElementById(inputId);
            if (!inputElement && type === 'bool') {
                // For boolean toggles, find the wrapper and then the input
                const wrapper = document.getElementById(inputId + '_wrapper');
                if (wrapper) {
                    inputElement = wrapper.querySelector('input[type="checkbox"]');
                }
            }
            
            if (inputElement) {
                console.log('AppSettings: Found input element for', settingId);
                // Store original value
                let originalValue;
                if (type === 'bool') {
                    originalValue = inputElement.checked;
                } else {
                    originalValue = inputElement.value;
                }
                originalValues.set(settingId, originalValue);
                console.log('AppSettings: Stored original value for', settingId, ':', originalValue);
                
                // Add change listeners
                inputElement.addEventListener('input', () => handleInputChange(settingId, card));
                inputElement.addEventListener('change', () => handleInputChange(settingId, card));
            } else {
                console.error('AppSettings: Input element not found for', settingId, 'inputId:', inputId);
            }
        });
        
        // Handle input changes
        function handleInputChange(settingId, card) {
            console.log('AppSettings: Input changed for setting', settingId);
            const saveBtn = card.querySelector('.app-setting-save-btn');
            const inputId = saveBtn.dataset.inputId;
            const type = saveBtn.dataset.settingType;
            
            let inputElement = document.getElementById(inputId);
            if (!inputElement && type === 'bool') {
                const wrapper = document.getElementById(inputId + '_wrapper');
                if (wrapper) {
                    inputElement = wrapper.querySelector('input[type="checkbox"]');
                }
            }
            
            if (!inputElement) return;
            
            // Get current value
            let currentValue;
            if (type === 'bool') {
                currentValue = inputElement.checked;
            } else {
                currentValue = inputElement.value;
            }
            
            // Check if value changed
            const hasChanged = currentValue !== originalValues.get(settingId);
            console.log('AppSettings: Value changed?', hasChanged, 'Current:', currentValue, 'Original:', originalValues.get(settingId));
            
            // Update button state
            if (hasChanged) {
                console.log('AppSettings: Enabling save button for', settingId);
                saveBtn.dataset.isChanged = 'true';
                saveBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'opacity-50', 'cursor-not-allowed');
                saveBtn.classList.add('bg-yellow-500', 'hover:bg-yellow-600', 'cursor-pointer');
                saveBtn.innerHTML = `
                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Save Changes
                `;
            } else {
                console.log('AppSettings: Disabling save button for', settingId);
                saveBtn.dataset.isChanged = 'false';
                saveBtn.classList.remove('bg-yellow-500', 'hover:bg-yellow-600', 'cursor-pointer');
                saveBtn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'opacity-50', 'cursor-not-allowed');
                saveBtn.innerHTML = `
                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    Save
                `;
            }
        }
        
        // Handle reset buttons
        document.querySelectorAll('.app-setting-reset-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const settingId = this.dataset.settingId;
                const inputId = this.dataset.inputId;
                const originalValue = originalValues.get(settingId);
                const card = this.closest('[data-setting-card]');
                const type = card.querySelector('.app-setting-save-btn').dataset.settingType;
                
                // Find input element
                let inputElement = document.getElementById(inputId);
                if (!inputElement && type === 'bool') {
                    const wrapper = document.getElementById(inputId + '_wrapper');
                    if (wrapper) {
                        inputElement = wrapper.querySelector('input[type="checkbox"]');
                    }
                }
                
                if (!inputElement) return;
                
                // Reset value
                if (type === 'bool') {
                    inputElement.checked = originalValue;
                } else {
                    inputElement.value = originalValue;
                }
                
                // Trigger change event to update UI
                inputElement.dispatchEvent(new Event('input'));
                
                // Show feedback
                showStatus(settingId, 'Reset to original value', 'info');
            });
        });
        
        // Handle save buttons
        document.querySelectorAll('.app-setting-save-btn').forEach(btn => {
            console.log('AppSettings: Attaching click handler to save button', btn.dataset.settingId);
            btn.addEventListener('click', function(e) {
                console.log('AppSettings: Save button clicked!', this.dataset.settingId);
                console.log('AppSettings: Button isChanged state:', this.dataset.isChanged);
                
                // Only proceed if the setting has changed
                if (this.dataset.isChanged !== 'true') {
                    console.log('AppSettings: Ignoring click - no changes detected');
                    return;
                }
                
                const settingId = this.dataset.settingId;
                const settingName = this.dataset.settingName;
                const settingType = this.dataset.settingType;
                const inputId = this.dataset.inputId;
                const card = this.closest('[data-setting-card]');
                
                // Find input element
                let inputElement = document.getElementById(inputId);
                if (!inputElement && settingType === 'bool') {
                    const wrapper = document.getElementById(inputId + '_wrapper');
                    if (wrapper) {
                        inputElement = wrapper.querySelector('input[type="checkbox"]');
                    }
                }
                
                if (!inputElement) {
                    showStatus(settingId, 'Input element not found', 'error');
                    return;
                }
                
                // Get value based on type
                let value;
                if (settingType === 'bool') {
                    value = inputElement.checked ? '1' : '0';
                } else {
                    value = inputElement.value;
                    
                    // Validate JSON if type is json
                    if (settingType === 'json') {
                        try {
                            const parsed = JSON.parse(value);
                            value = JSON.stringify(parsed);
                        } catch (e) {
                            showStatus(settingId, 'Invalid JSON format', 'error');
                            return;
                        }
                    }
                }
                
                // Save the setting
                saveSetting({
                    id: settingId,
                    name: settingName,
                    type: settingType,
                    value: value,
                    csrf_token: CSRF_TOKEN
                }, btn, card, originalValues);
            });
        });
        
        // Function to save setting via API
        function saveSetting(data, btn, card, originalValues) {
            const settingId = data.id;
            const originalBtnHtml = btn.innerHTML;
            
            // Update button to show loading state
            btn.dataset.isChanged = 'false';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btn.innerHTML = `
                <svg class="animate-spin h-4 w-4 mr-1 inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving...
            `;
            
            // Make API request
            fetch('/api/app-settings', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': CSRF_TOKEN,
                    'secretheader': 'badass'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                // Always parse JSON first, regardless of status
                return response.json().then(result => {
                    return { ok: response.ok, status: response.status, result };
                });
            })
            .then(({ ok, status, result }) => {
                if (!ok) {
                    // Use the actual error message from the API
                    const errorMessage = result.data || result.message || `HTTP error! status: ${status}`;
                    throw new Error(errorMessage);
                }
                
                // Check for success - handle both { success: true } and { result: "success" }
                const isSuccess = result.success === true || result.result === 'success';
                
                if (isSuccess) {
                    // Update original value
                    const inputElement = document.getElementById(btn.dataset.inputId);
                    if (inputElement) {
                        if (data.type === 'bool') {
                            originalValues.set(settingId, inputElement.checked);
                        } else {
                            originalValues.set(settingId, inputElement.value);
                        }
                    }
                    
                    // Reset button state
                    btn.dataset.isChanged = 'false';
                    btn.classList.remove('bg-yellow-500', 'hover:bg-yellow-600', 'cursor-pointer');
                    btn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'opacity-50', 'cursor-not-allowed');
                    btn.innerHTML = originalBtnHtml;
                    
                    // Show success message
                    showStatus(settingId, result.message || result.data || 'Setting saved successfully!', 'success');
                } else {
                    throw new Error(result.message || result.data || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Error saving setting:', error);
                
                // Reset button
                btn.dataset.isChanged = 'true';
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.classList.add('cursor-pointer');
                btn.innerHTML = originalBtnHtml;
                
                // Show error message
                showStatus(settingId, 'Error: ' + error.message, 'error');
            });
        }
        
        // Function to show status messages
        function showStatus(settingId, message, type) {
            const statusEl = document.querySelector(`.app-setting-status[data-setting-id="${settingId}"]`);
            if (!statusEl) return;
            
            // Set colors based on type
            const colors = {
                success: 'text-green-600 dark:text-green-400',
                error: 'text-red-600 dark:text-red-400',
                info: 'text-blue-600 dark:text-blue-400'
            };
            
            const icons = {
                success: '<svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
                error: '<svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>',
                info: '<svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>'
            };
            
            statusEl.className = 'app-setting-status ml-auto text-sm font-medium ' + (colors[type] || colors.info);
            statusEl.innerHTML = icons[type] + message;
            
            // Clear after 5 seconds
            setTimeout(() => {
                statusEl.innerHTML = '';
                statusEl.className = 'app-setting-status ml-auto text-sm';
            }, 5000);
        }
    }
})();
JAVASCRIPT;
        $html .= '</script>';

        return $html;
    }
}
