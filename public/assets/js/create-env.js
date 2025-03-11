const form = document.getElementById('env');

form.action = 'POST';

form.addEventListener('submit', (event) => {
    event.preventDefault(); // Prevent the default form submission
    event.submitter.disabled = true;
    event.submitter.innerHTML = 'loading...';
    const formData = new FormData(form); // Serialize form data
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        event.submitter.innerHTML = 'Submit';
        return response.text(); // Parse response as text
    })
    .then(data => {
        event.submitter.disabled = false;
        // Remove any existing div with id "responseDiv"
        const existingResponseDiv = document.getElementById('responseDiv');
        if (existingResponseDiv) {
            existingResponseDiv.remove();
        }
        // Create a new div element
        const responseDiv = document.createElement('div');
        // Set the id of the div
        responseDiv.id = 'responseDiv';
        // Set the inner HTML of the div to the response text
        responseDiv.innerHTML = data;
        // Append the div to the document body
        document.body.appendChild(responseDiv);

        if (data === 'The .env file has been created successfully.') {
            responseDiv.innerText += ` Redirecting to root...`;
            responseDiv.style.color = 'green';
            setInterval(() => {
                window.location = '/';
            }, 2000)
        }
    })
    .catch(error => {
        // Handle errors
        console.error('There was a problem with your fetch operation:', error);
    });
});

function toggleAdditionalField(checkboxId, fieldName, placeholder) {
    const checkbox = document.getElementById(checkboxId);
    const additionalFieldContainer = document.getElementById(`${fieldName}Container`);
    if (checkbox.checked) {
        // If checkbox is checked, create and append the input field
        const inputField = document.createElement('input');
        inputField.type = 'text';
        inputField.name = fieldName;
        inputField.placeholder = placeholder;
        inputField.required = true; // Optionally make it required
        inputField.id = `${fieldName}Field`;
        inputField.style.margin = '6px';

        // Create a div container for the additional input field
        const fieldContainer = document.createElement('div');
        fieldContainer.id = `${fieldName}Container`;
        fieldContainer.appendChild(inputField);

        // Insert the container below the checkbox
        checkbox.parentNode.insertBefore(fieldContainer, checkbox.nextSibling);
    } else if (additionalFieldContainer) {
        // If checkbox is unchecked, remove the input field container
        additionalFieldContainer.remove();
    }
}

// Add event listener to SENDGRID checkbox
document.getElementById('SENDGRID').addEventListener('change', () => {
    toggleAdditionalField('SENDGRID', 'SENDGRID_API_KEY', 'SendGrid API Key');
});

// Add event listener to Entra ID Login
document.getElementById('ENTRA_ID_LOGIN_ENABLED').addEventListener('change', () => {
    toggleAdditionalField('ENTRA_ID_LOGIN_ENABLED', 'ENTRA_ID_CLIENT_SECRET', 'Client Secret');
    toggleAdditionalField('ENTRA_ID_LOGIN_ENABLED', 'ENTRA_ID_CLIENT_ID', 'App ID');
    toggleAdditionalField('ENTRA_ID_LOGIN_ENABLED', 'ENTRA_ID_TENANT_ID', 'Tenant ID');
    // Add a small paragraph with the instructions
    if (document.getElementById('ENTRA_ID_LOGIN_ENABLED').checked) {
        addInstructions('ENTRA_ID_LOGIN_ENABLED', 'Entra_ID_instructions', 'Create a new App registration in Azure AD. Create a secret and copy the values to the fields below. Make sure to add the redirect URI to the App registration. More info in the README.md file.');
    } else {
        removeInstructions('Entra_ID_instructions');
    }

});

// MS Live
document.getElementById('MSLIVE_LOGIN_ENABLED').addEventListener('change', () => {
    toggleAdditionalField('MSLIVE_LOGIN_ENABLED', 'MS_LIVE_CLIENT_SECRET', 'Client Secret');
    toggleAdditionalField('MSLIVE_LOGIN_ENABLED', 'MS_LIVE_CLIENT_ID', 'App ID');
    toggleAdditionalField('MSLIVE_LOGIN_ENABLED', 'MS_LIVE_TENANT_ID', 'Tenant ID');
    if (document.getElementById('MSLIVE_LOGIN_ENABLED').checked) {
        addInstructions('MSLIVE_LOGIN_ENABLED', 'Microsoft_LIVE_instructions', 'Create a new App registration in Entra ID. Make sure that LIVE accounts are supported. Create a secret and copy the values to the fields below. Make sure to add the redirect URI to the App registration. More info in the README.md file.');
    } else {
        removeInstructions('Microsoft_LIVE_instructions');
    }
});

// Google login
document.getElementById('GOOGLE_LOGIN_ENABLED').addEventListener('change', () => {
    toggleAdditionalField('GOOGLE_LOGIN_ENABLED', 'GOOGLE_CLIENT_SECRET', 'GOOGLE_CLIENT_SECRET');
    toggleAdditionalField('GOOGLE_LOGIN_ENABLED', 'GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_ID');

    if (document.getElementById('GOOGLE_LOGIN_ENABLED').checked) {
        addInstructions('GOOGLE_LOGIN_ENABLED', 'GOOGLE_LOGIN_ENABLED_instructions', 'Register new Credentials in GPC. Go to API and Services -> Credentials -> Create Credentials -> OAuth Client ID. Select Web Application and fill the form. Copy the Client ID and Client Secret to the fields below. Make sure to add the redirect URI to the Credentials. More info in the README.md file.');
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
    const instructions = document.createElement('p');
    instructions.innerText = text;
    instructions.style.color = 'gray';
    instructions.style.fontSize = '12px';
    instructions.style.margin = '6px';
    instructions.id = instructionsId;
    // Append it after the label of the checkbox
    checkbox.parentNode.insertBefore(instructions, checkbox.nextSibling);
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
    const form = document.getElementById("env"); // The form element

    let originalFieldsContainer = null; // To store the original container temporarily

    function toggleFields() {
        const isSQLite = dbDriver.value === "sqlite";

        if (isSQLite) {
            // Save the original container if it's still in the form
            if (dbFieldsContainer.parentNode === form) {
                originalFieldsContainer = dbFieldsContainer.cloneNode(true); // Clone for restoration
                dbFieldsContainer.remove(); // Remove from the form
            }
        } else {
            // Restore the container if it was previously removed
            if (!document.getElementById("db-fields") && originalFieldsContainer) {
                // Create the div
                dbFieldsContainer.id = "db-fields";
                // add at the start of the form
                form.insertBefore(dbFieldsContainer, form.firstChild);
                dbFieldsContainer.innerHTML = originalFieldsContainer.innerHTML;
            }
        }
    }

    // Trigger on page load in case 'sqlite' is preselected
    toggleFields();

    // Add change listener to DB_DRIVER
    dbDriver.addEventListener("change", toggleFields);
});
