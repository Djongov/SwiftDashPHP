const loginForm = document.getElementById('local-login-form');
let submitCount = 0;
const maxAttempts = 55;

if (loginForm) {
    loginForm.addEventListener('submit', (event) => {
        event.preventDefault();
        submitCount++;
        console.log(`Attempt ${submitCount} of ${maxAttempts}`);
        // If maxAttempts is reached, disable the form
        if (submitCount >= maxAttempts) {
            console.log('Max attempts reached, disabling form');
            // Disable all form elements
            const formElements = loginForm.elements;
            for (const formElement of formElements) {
                console.log(formElement);
                formElement.disabled = true;
            }
            // Empty the action attribute to prevent the form from being submitted
            loginForm.action = '';
            // return a message
            if (document.getElementById('max-attempts-message')) {
                document.getElementById('max-attempts-message').remove();
            }
            const message = document.createElement('p');
            message.id = 'max-attempts-message';
            message.innerText = 'Max attempts reached, please try again later';
            loginForm.append(message);
        }
    });
}

