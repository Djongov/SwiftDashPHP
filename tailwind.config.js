/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './App/**/*.php',
        './Components/**/*.php', 
        './Controllers/**/*.php',
        './Models/**/*.php',
        './Views/**/*.php',
        './config/**/*.php',
        './public/**/*.php',
        './resources/**/*.php',
        './public/assets/js/**/*.js'
    ],
    darkMode: 'class',
    theme: {
        extend: {
            // Add any custom theme extensions here if needed
        }
    },
    variants: {
        extend: {
            backgroundColor: ['odd', 'even'],
            textColor: ['odd', 'even'],
            borderColor: ['odd', 'even'],
            stroke: ['odd', 'even', 'hover', 'focus', 'dark'],
        }
    },
    safelist: [
        // Essential classes always needed
        'bg-clip-text',
        'text-transparent',
        
        // Odd/even table row classes
        'even:bg-gray-50', 'odd:bg-white', 'even:bg-gray-100', 'odd:bg-gray-50',
        'dark:even:bg-gray-700', 'dark:odd:bg-gray-800', 'dark:even:bg-gray-600', 'dark:odd:bg-gray-700',
        'hover:bg-gray-100', 'dark:hover:bg-gray-600', 'transition-colors', 'duration-150',
        
        // Checkbox selection state classes - generated for all theme colors
        ...['sky', 'cyan', 'emerald', 'teal', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'green', 'pink', 'red', 'rose', 'orange', 'yellow', 'amber', 'lime', 'gray', 'slate', 'stone'].flatMap(color => [
            `has-[:checked]:bg-${color}-500`,
            `has-[:checked]:text-white`, 
            `dark:has-[:checked]:bg-${color}-600`,
            `dark:has-[:checked]:text-white`,
            `focus:bg-${color}-500`,
            `focus:text-white`,
            `dark:focus:bg-${color}-600`,
            `dark:focus:text-white`
        ]),
        
        // Generate all theme colors - this approach ensures all dynamic classes are included
        ...['sky', 'cyan', 'emerald', 'teal', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'green', 'pink', 'red', 'rose', 'orange', 'yellow', 'amber', 'lime', 'gray', 'slate', 'stone'].flatMap(color => 
            ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900'].flatMap(shade => [
                // Basic color classes
                `bg-${color}-${shade}`,
                `text-${color}-${shade}`,
                `border-${color}-${shade}`,
                `ring-${color}-${shade}`,
                `from-${color}-${shade}`,
                `to-${color}-${shade}`,
                `stroke-${color}-${shade}`,
                // Hover states
                `hover:bg-${color}-${shade}`,
                `hover:text-${color}-${shade}`,  
                `hover:border-${color}-${shade}`,
                `hover:from-${color}-${shade}`,
                `hover:to-${color}-${shade}`,
                `hover:stroke-${color}-${shade}`,
                // Dark mode
                `dark:bg-${color}-${shade}`,
                `dark:text-${color}-${shade}`,
                `dark:border-${color}-${shade}`,
                `dark:ring-${color}-${shade}`,
                `dark:stroke-${color}-${shade}`,
                // Dark mode hover
                `dark:hover:bg-${color}-${shade}`,
                `dark:hover:text-${color}-${shade}`,
                `dark:hover:border-${color}-${shade}`,
                `dark:hover:stroke-${color}-${shade}`,
                // Focus states
                `focus:ring-${color}-${shade}`,
                `focus:border-${color}-${shade}`,
                `focus:stroke-${color}-${shade}`,
                // Dark focus states
                `dark:focus:ring-${color}-${shade}`,
                `dark:focus:border-${color}-${shade}`,
                `dark:focus:stroke-${color}-${shade}`
            ])
        )
    ],
    plugins: [
        require('@tailwindcss/typography'),
    ],
}
