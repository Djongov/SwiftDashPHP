const copyToClipboard = () => {
    // Find all elements with class "c0py"
    const elements = document.getElementsByClassName("c0py");

    const theme = document.querySelector('input[type="hidden"][name="theme"]').value;

    Array.from(elements).forEach(element => {
        if (!element.dataset.hasCopyToClipboard) {
            element.dataset.hasCopyToClipboard = true;

            // Create the copy button
            const button = document.createElement("button");
            button.classList.add('inline-flex', 'items-center', 'relative');
            button.setAttribute('data-processed', 'true');
            button.innerHTML = `<svg class="w-6 h-6 text-${theme}-500 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="8" y="8" width="12" height="12" rx="2" /><path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />
            </svg>`;

            button.title = "Copy to clipboard";

            // Create the tooltip div element
            const div1 = document.createElement('div');
            div1.setAttribute('role', 'tooltip');
            div1.classList.add('relative', 'hidden', `bg-${theme}-500`, 'text-gray-50', 'p-1', 'text-sm', 'rounded', 'w-14', 'h-fit');

            // Create a container element
            const container = document.createElement('div');
            container.classList.add('copy-container', 'relative'); // Added 'relative' for positioning context

            // Append the new div elements to the container
            container.appendChild(button);
            container.appendChild(div1);

            // Append the container after the element
            element.insertAdjacentElement('afterend', container);

            // Set the click event handler for copying to clipboard
            button.addEventListener("click", () => {
                const textToCopy = element.textContent || element.innerText;
                navigator.clipboard.writeText(textToCopy)
                    .then(() => {
                        div1.textContent = 'Copied';
                        button.querySelector("svg").classList.replace(`text-${theme}-500`, "text-red-500");
                    })
                    .catch(error => {
                        div1.textContent = 'Failed to copy';
                        console.error("Failed to copy text:", error);
                    });

                div1.classList.remove('hidden');

                setTimeout(() => {
                    div1.classList.add('hidden');
                    button.querySelector("svg").classList.replace("text-red-500", `text-${theme}-500`);
                }, 1000);
            });
        }
    });
};

document.addEventListener("DOMContentLoaded", copyToClipboard);
