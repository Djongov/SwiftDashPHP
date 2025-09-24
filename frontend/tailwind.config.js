/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
    // Include PHP files to share Tailwind classes
    "../Views/**/*.php",
    "../Components/**/*.php",
  ],
  darkMode: 'class', // Enable class-based dark mode
  theme: {
    extend: {
      // Match the existing color scheme from the PHP app
      colors: {
        primary: {
          50: '#eff6ff',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
        }
      },
    },
  },
  plugins: [],
}