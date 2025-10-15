document.addEventListener("DOMContentLoaded", function() {
    // Capture original form values on page load - be more specific with form selector
    const form = document.querySelector('form[action="/api/edit-json-settings"]');
    
    if (!form) {
        console.error('JSON Editor: Form not found');
        return;
    }
    
    const originalValues = {};
    const submitButton = form.querySelector('button[type=submit][name="save_json"]');
    const resetButton = document.getElementById("json-editor-reset-btn");
    
    if (!submitButton) {
        console.error('JSON Editor: Submit button not found');
        return;
    }
    
    // Store original values for all form elements
    if (form) {
        const inputs = form.querySelectorAll("input, textarea, select");
        inputs.forEach(function(input) {
            if (input.type === "hidden" || input.name === "csrf_token" || input.name === "save_json" || input.name === "json_file_path") {
                return; // Skip hidden fields, CSRF token, and submit button
            }
            
            if (input.type === "checkbox") {
                originalValues[input.name] = input.checked;
            } else {
                originalValues[input.name] = input.value;
            }
        });
    }
    
    // Create message container for feedback
    function createMessageContainer() {
        let messageContainer = document.getElementById("json-editor-messages");
        if (!messageContainer) {
            messageContainer = document.createElement("div");
            messageContainer.id = "json-editor-messages";
            messageContainer.className = "mb-4";
            form.parentNode.insertBefore(messageContainer, form);
        }
        return messageContainer;
    }
    
    // Show message function
    function showMessage(message, type = "success") {
        const messageContainer = createMessageContainer();
        const bgColor = type === "success" ? "bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-800 dark:text-green-200" : "bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700 text-red-800 dark:text-red-200";
        const icon = type === "success" ? "✅" : "❌";
        messageContainer.innerHTML = "<div class=\"p-3 " + bgColor + " border rounded-lg text-sm\">" + icon + " " + message + "</div>";
        setTimeout(function() { messageContainer.innerHTML = ""; }, 5000);
    }
    
    // AJAX form submission
    if (form && submitButton) {
        
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            
            // Disable submit button and show loading
            submitButton.disabled = true;
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = "<svg class=\"animate-spin -ml-1 mr-3 h-4 w-4 text-white inline\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\"><circle class=\"opacity-25\" cx=\"12\" cy=\"12\" r=\"10\" stroke=\"currentColor\" stroke-width=\"4\"></circle><path class=\"opacity-75\" fill=\"currentColor\" d=\"m4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z\"></path></svg>Saving...";
            
            // Create FormData from the form
            const formData = new FormData(form);
            
            // Explicitly add the save_json field since the button might not be included when disabled
            formData.append('save_json', '1');
            
            // Get CSRF token from session or form
            const csrfToken = form.querySelector('input[name="csrf_token"]')?.value;
            
            // Send fetch request
            fetch("/api/edit-json-settings", {
                method: "POST",
                body: formData,
                headers: {
                    "secretheader": "badass", // TODO: This should be configurable and not hardcoded
                    "x-csrf-token": csrfToken || ""
                }
            })
            .then(response => {
                return response.text().then(text => ({
                    status: response.status,
                    text: text
                }));
            })
            .then(result => {
                console.log('JSON Editor: Response received', result);
                if (result.status === 200) {
                    showMessage(result.text, "success");
                } else {
                    showMessage(result.text, "error");
                }
            })
            .catch(error => {
                console.error('JSON Editor: Fetch error', error);
                showMessage("Network error: " + error.message, "error");
            })
            .finally(() => {
                // Restore submit button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }
    
    // Reset button event listener
    const resetBtn = document.getElementById("json-editor-reset-btn");
    if (resetBtn) {
        resetBtn.addEventListener("click", function() {
            // Confirm reset action
            if (confirm("Are you sure you want to reset all fields to their original values? Any unsaved changes will be lost.")) {
                // Reset all form elements to original values
                const inputs = form.querySelectorAll("input, textarea, select");
                inputs.forEach(function(input) {
                    if (input.type === "hidden" || input.name === "csrf_token" || input.name === "save_json" || input.name === "json_file_path") {
                        return; // Skip hidden fields
                    }
                    
                    if (originalValues.hasOwnProperty(input.name)) {
                        if (input.type === "checkbox") {
                            input.checked = originalValues[input.name];
                        } else {
                            input.value = originalValues[input.name];
                        }
                    }
                });
                
                // Show success message
                const successMessage = document.createElement("div");
                successMessage.className = "mt-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg text-green-800 dark:text-green-200 text-sm";
                successMessage.innerHTML = "✅ Form has been reset to original values";
                
                // Insert message before form
                form.parentNode.insertBefore(successMessage, form);
                
                // Remove message after 3 seconds
                setTimeout(function() {
                    if (successMessage.parentNode) {
                        successMessage.parentNode.removeChild(successMessage);
                    }
                }, 3000);
            }
        });
    }
});