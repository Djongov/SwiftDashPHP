const loginForm = document.getElementById('local-login-form');
let submitCount = 0;
const maxAttempts = 3;

if (loginForm) {
    loginForm.addEventListener('submit', (event) => {
        event.preventDefault();
        submitCount++;
        console.log(`Attempt ${submitCount} of ${maxAttempts}`);

        if (submitCount >= maxAttempts) {
            console.log('Max attempts reached, disabling form');
            
            // Disable all form elements
            for (const formElement of loginForm.elements) {
                formElement.disabled = true;
            }

            // Explicitly target the submit button
            const submitButton = loginForm.querySelector('[type="submit"]');
            if (submitButton) {
                console.log(submitButton);
                submitButton.disabled = true;
                submitButton.setAttribute('disabled', 'true'); // Ensure it's applied
            }

            // Prevent form submission
            loginForm.action = '';

            // Display a message
            let message = document.getElementById('max-attempts-message');
            if (!message) {
                message = document.createElement('p');
                message.id = 'max-attempts-message';
                message.classList.add('text-red-500', 'font-bold');
                loginForm.appendChild(message);
            }
            message.innerText = 'Max attempts reached, please try again later';
        }
    });
}
