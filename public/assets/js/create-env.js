const form = document.getElementById('env');

form.action = 'POST';

form.addEventListener('submit', (event) => {
    event.preventDefault(); // Prevent the default form submission
    
    // Client-side validation for required Azure Service Principal fields
    const requiredFields = [
        { id: 'AZURE_SERVICE_PRINCIPAL_CLIENT_ID', name: 'Azure Service Principal Client ID' },
        { id: 'AZURE_SERVICE_PRINCIPAL_TENANT_ID', name: 'Azure Service Principal Tenant ID' },
        { id: 'AZURE_SERVICE_PRINCIPAL_CLIENT_SECRET', name: 'Azure Service Principal Client Secret' },
        { id: 'AZURE_WORKSPACE_ID', name: 'Azure Log Analytics Workspace ID' }
    ];
    
    const missingFields = [];
    requiredFields.forEach(field => {
        const input = document.getElementById(field.id);
        // Only validate if the field is visible and not disabled
        if (input && !input.disabled && input.offsetParent !== null) {
            if (!input.value.trim()) {
                missingFields.push(field.name);
                input.style.borderColor = '#ef4444';
            } else {
                input.style.borderColor = '';
            }
        }
    });
    
    if (missingFields.length > 0) {
        // Show validation error
        const existingResponseDiv = document.getElementById('responseDiv');
        if (existingResponseDiv) {
            existingResponseDiv.remove();
        }
        
        const errorDiv = document.createElement('div');
        errorDiv.id = 'responseDiv';
        errorDiv.className = 'mt-4 p-4 rounded-md bg-red-100 border border-red-400 text-red-700';
        errorDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <strong>Validation Error:</strong> The following required fields are missing:
                    <ul class="mt-1 ml-4 list-disc">
                        ${missingFields.map(field => `<li>${field}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;
        
        form.parentNode.insertBefore(errorDiv, form.nextSibling);
        return;
    }
    
    event.submitter.disabled = true;
    event.submitter.innerHTML = 'Creating environment file...';
    const formData = new FormData(form); // Serialize form data
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        event.submitter.innerHTML = 'Submit';
        event.submitter.disabled = false;
        
        if (!response.ok) {
            return response.text().then(errorText => {
                throw new Error(errorText || 'Network response was not ok');
            });
        }
        return response.text(); // Parse response as text
    })
    .then(data => {
        // Remove any existing div with id "responseDiv"
        const existingResponseDiv = document.getElementById('responseDiv');
        if (existingResponseDiv) {
            existingResponseDiv.remove();
        }
        
        // Create a new div element with better styling
        const responseDiv = document.createElement('div');
        responseDiv.id = 'responseDiv';
        responseDiv.className = 'mt-4 p-4 rounded-md';
        
        if (data === 'The .env file has been created successfully.') {
            responseDiv.className += ' bg-green-100 border border-green-400 text-green-700';
            responseDiv.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>${data} Redirecting to Installation...</span>
                </div>
            `;
            setTimeout(() => {
                window.location = '/install';
            }, 2000);
        } else {
            responseDiv.className += ' bg-blue-100 border border-blue-400 text-blue-700';
            responseDiv.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <span>${data}</span>
                </div>
            `;
        }
        
        // Insert the response div after the form
        const form = document.getElementById('env');
        form.parentNode.insertBefore(responseDiv, form.nextSibling);
    })
    .catch(error => {
        event.submitter.disabled = false;
        
        // Remove any existing div with id "responseDiv"
        const existingResponseDiv = document.getElementById('responseDiv');
        if (existingResponseDiv) {
            existingResponseDiv.remove();
        }
        
        // Create error message div
        const errorDiv = document.createElement('div');
        errorDiv.id = 'responseDiv';
        errorDiv.className = 'mt-4 p-4 rounded-md bg-red-100 border border-red-400 text-red-700';
        errorDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span><strong>Error:</strong> ${error.message}</span>
            </div>
        `;
        
        // Insert the error div after the form
        const form = document.getElementById('env');
        form.parentNode.insertBefore(errorDiv, form.nextSibling);
        
        console.error('There was a problem with your fetch operation:', error);
    });
});

function toggleAdditionalField(checkboxId, fieldName, placeholder, label = null, isSecret = false) {
    const checkbox = document.getElementById(checkboxId);
    const containerId = `${fieldName}Container`;
    const additionalFieldContainer = document.getElementById(containerId);
    
    if (checkbox.checked) {
        if (!additionalFieldContainer) {
            // Create form group container
            const fieldContainer = document.createElement('div');
            fieldContainer.id = containerId;
            fieldContainer.className = 'form-group';
            
            // Create label
            const labelElement = document.createElement('label');
            labelElement.setAttribute('for', fieldName);
            labelElement.className = 'form-label required-field';
            labelElement.textContent = label || placeholder;
            
            // Create input field
            const inputField = document.createElement('input');
            inputField.type = isSecret ? 'password' : 'text';
            inputField.name = fieldName;
            inputField.placeholder = placeholder;
            inputField.required = true;
            inputField.id = fieldName;
            inputField.className = 'form-input';
            
            // Assemble the field
            fieldContainer.appendChild(labelElement);
            fieldContainer.appendChild(inputField);
            
            // Find the checkbox wrapper and insert after it
            const checkboxWrapper = checkbox.closest('.checkbox-wrapper');
            if (checkboxWrapper && checkboxWrapper.parentNode) {
                checkboxWrapper.parentNode.insertBefore(fieldContainer, checkboxWrapper.nextSibling);
            }
        }
    } else if (additionalFieldContainer) {
        // If checkbox is unchecked, remove the input field container
        additionalFieldContainer.remove();
    }
}

function createAuthFieldsContainer(checkboxId, fields) {
    const checkbox = document.getElementById(checkboxId);
    const containerId = `${checkboxId}_fields_container`;
    const existingContainer = document.getElementById(containerId);
    
    if (checkbox.checked && !existingContainer) {
        // Create main container for all auth fields
        const mainContainer = document.createElement('div');
        mainContainer.id = containerId;
        mainContainer.className = 'mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600';
        
        // Create grid container for fields
        const gridContainer = document.createElement('div');
        gridContainer.className = 'grid grid-cols-1 gap-4';
        
        // Add each field
        fields.forEach(field => {
            const fieldGroup = document.createElement('div');
            fieldGroup.className = 'form-group';
            
            // Create label
            const label = document.createElement('label');
            label.setAttribute('for', field.name);
            label.className = 'form-label required-field';
            label.textContent = field.label;
            
            // Create input
            const input = document.createElement('input');
            input.type = field.type || 'text';
            input.name = field.name;
            input.id = field.name;
            input.placeholder = field.placeholder;
            input.required = true;
            input.className = 'form-input';
            
            fieldGroup.appendChild(label);
            fieldGroup.appendChild(input);
            gridContainer.appendChild(fieldGroup);
        });
        
        mainContainer.appendChild(gridContainer);
        
        // Insert after the checkbox wrapper
        const checkboxWrapper = checkbox.closest('.checkbox-wrapper');
        if (checkboxWrapper && checkboxWrapper.parentNode) {
            checkboxWrapper.parentNode.insertBefore(mainContainer, checkboxWrapper.nextSibling);
        }
    } else if (!checkbox.checked && existingContainer) {
        existingContainer.remove();
    }
}

// Add event listener to SENDGRID checkbox
document.getElementById('SENDGRID').addEventListener('change', () => {
    const fields = [
        { name: 'SENDGRID_API_KEY', label: 'SendGrid API Key', placeholder: 'Enter your SendGrid API key', type: 'password' }
    ];
    createAuthFieldsContainer('SENDGRID', fields);
});

// Add event listener to Entra ID Login
document.getElementById('ENTRA_ID_LOGIN_ENABLED').addEventListener('change', () => {
    const fields = [
        { name: 'ENTRA_ID_CLIENT_ID', label: 'Application (Client) ID', placeholder: '00000000-0000-0000-0000-000000000000' },
        { name: 'ENTRA_ID_TENANT_ID', label: 'Directory (Tenant) ID', placeholder: '00000000-0000-0000-0000-000000000000' },
        { name: 'ENTRA_ID_CLIENT_SECRET', label: 'Client Secret Value', placeholder: 'Enter client secret', type: 'password' }
    ];
    createAuthFieldsContainer('ENTRA_ID_LOGIN_ENABLED', fields);
    
    // Handle instructions
    if (document.getElementById('ENTRA_ID_LOGIN_ENABLED').checked) {
        addInstructions('ENTRA_ID_LOGIN_ENABLED', 'Entra_ID_instructions', 'Create a new App registration in Azure AD. Create a secret and copy the values to the fields below. Make sure to add the redirect URI to the App registration. More info in the README.md file.');
    } else {
        removeInstructions('Entra_ID_instructions');
    }
});

// MS Live
document.getElementById('MSLIVE_LOGIN_ENABLED').addEventListener('change', () => {
    const fields = [
        { name: 'MS_LIVE_CLIENT_ID', label: 'Application (Client) ID', placeholder: '00000000-0000-0000-0000-000000000000' },
        { name: 'MS_LIVE_TENANT_ID', label: 'Directory (Tenant) ID', placeholder: '00000000-0000-0000-0000-000000000000' },
        { name: 'MS_LIVE_CLIENT_SECRET', label: 'Client Secret Value', placeholder: 'Enter client secret', type: 'password' }
    ];
    createAuthFieldsContainer('MSLIVE_LOGIN_ENABLED', fields);
    
    if (document.getElementById('MSLIVE_LOGIN_ENABLED').checked) {
        addInstructions('MSLIVE_LOGIN_ENABLED', 'Microsoft_LIVE_instructions', 'Create a new App registration in Entra ID. Make sure that LIVE accounts are supported. Create a secret and copy the values to the fields below. Make sure to add the redirect URI to the App registration. More info in the README.md file.');
    } else {
        removeInstructions('Microsoft_LIVE_instructions');
    }
});

// Google login
document.getElementById('GOOGLE_LOGIN_ENABLED').addEventListener('change', () => {
    const fields = [
        { name: 'GOOGLE_CLIENT_ID', label: 'Google Client ID', placeholder: 'Enter Google OAuth Client ID' },
        { name: 'GOOGLE_CLIENT_SECRET', label: 'Google Client Secret', placeholder: 'Enter Google OAuth Client Secret', type: 'password' }
    ];
    createAuthFieldsContainer('GOOGLE_LOGIN_ENABLED', fields);

    if (document.getElementById('GOOGLE_LOGIN_ENABLED').checked) {
        addInstructions('GOOGLE_LOGIN_ENABLED', 'GOOGLE_LOGIN_ENABLED_instructions', 'Register new Credentials in Google Cloud Console. Go to API and Services → Credentials → Create Credentials → OAuth Client ID. Select Web Application and fill the form. Copy the Client ID and Client Secret to the fields below. Make sure to add the redirect URI to the Credentials. More info in the README.md file.');
    } else {
        removeInstructions('GOOGLE_LOGIN_ENABLED_instructions');
    }
});

const addInstructions = (checkboxId, instructionsId, text) => {
    const existingInstructions = document.getElementById(instructionsId);
    if (existingInstructions) {
        existingInstructions.remove();
    }
    
    const checkbox = document.getElementById(checkboxId);
    const instructions = document.createElement('div');
    instructions.className = 'mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md';
    instructions.id = instructionsId;
    
    // Create info icon and text container
    const contentDiv = document.createElement('div');
    contentDiv.className = 'flex items-start';
    
    const icon = document.createElement('svg');
    icon.className = 'w-4 h-4 mr-2 mt-0.5 text-blue-600 dark:text-blue-400 flex-shrink-0';
    icon.setAttribute('fill', 'currentColor');
    icon.setAttribute('viewBox', '0 0 20 20');
    icon.innerHTML = '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>';
    
    const textSpan = document.createElement('span');
    textSpan.className = 'text-sm text-blue-800 dark:text-blue-200';
    textSpan.innerText = text;
    
    contentDiv.appendChild(icon);
    contentDiv.appendChild(textSpan);
    instructions.appendChild(contentDiv);
    
    // Find the checkbox wrapper and insert after it
    const checkboxWrapper = checkbox.closest('.checkbox-wrapper');
    if (checkboxWrapper && checkboxWrapper.parentNode) {
        checkboxWrapper.parentNode.insertBefore(instructions, checkboxWrapper.nextSibling);
    }
}

const removeInstructions = (instructionsId) => {    
    const existingInstructions = document.getElementById(instructionsId);
    if (existingInstructions) {
        existingInstructions.remove();
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const dbDriver = document.getElementById("DB_DRIVER");
    const dbFieldsContainer = document.getElementById("db-fields");
    
    // Store references to fields that need required attribute management
    const requiredDbFields = ['DB_HOST', 'DB_PORT', 'DB_USER', 'DB_PASS'];
    
    function toggleFields() {
        const isSQLite = dbDriver.value === "sqlite";

        if (isSQLite) {
            // Hide the container and remove required attributes
            if (dbFieldsContainer) {
                dbFieldsContainer.style.display = 'none';
                
                // Remove required attribute from hidden fields to prevent validation errors
                requiredDbFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.removeAttribute('required');
                        field.disabled = true; // Also disable to ensure they're not submitted
                    }
                });
            }
        } else {
            // Show the container and restore required attributes
            if (dbFieldsContainer) {
                dbFieldsContainer.style.display = 'block';
                
                // Restore required attribute for visible fields
                requiredDbFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.setAttribute('required', 'required');
                        field.disabled = false; // Re-enable the fields
                    }
                });
            }
        }
    }

    // Trigger on page load in case 'sqlite' is preselected
    toggleFields();

    // Add change listener to DB_DRIVER
    dbDriver.addEventListener("change", toggleFields);
});
