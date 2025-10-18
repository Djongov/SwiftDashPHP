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
            backgroundColor: ['odd', 'even', 'peer-focus', 'peer-checked'],
            textColor: ['odd', 'even', 'peer-focus', 'peer-checked'],
            borderColor: ['odd', 'even', 'peer-focus', 'peer-checked'],
            stroke: ['odd', 'even', 'hover', 'focus', 'dark', 'peer-focus', 'peer-checked'],
            fill: ['odd', 'even', 'hover', 'focus', 'dark', 'peer-focus', 'peer-checked'],
        }
    },
    safelist: [
        // Essential classes always needed
        'bg-clip-text',
        'text-transparent',
        
        // Flexbox and layout classes
        'flex', 'inline-flex', 'block', 'inline-block', 'inline', 'hidden',
        'flex-row', 'flex-row-reverse', 'flex-col', 'flex-col-reverse',
        'flex-wrap', 'flex-wrap-reverse', 'flex-nowrap',
        'justify-start', 'justify-end', 'justify-center', 'justify-between', 'justify-around', 'justify-evenly', 'justify-stretch',
        'items-start', 'items-end', 'items-center', 'items-baseline', 'items-stretch',
        'content-start', 'content-end', 'content-center', 'content-between', 'content-around', 'content-evenly', 'content-baseline', 'content-stretch',
        'self-auto', 'self-start', 'self-end', 'self-center', 'self-stretch', 'self-baseline',
        'flex-1', 'flex-auto', 'flex-initial', 'flex-none',
        'grow', 'grow-0', 'shrink', 'shrink-0',
        'order-1', 'order-2', 'order-3', 'order-4', 'order-5', 'order-6', 'order-7', 'order-8', 'order-9', 'order-10', 'order-11', 'order-12',
        'order-first', 'order-last', 'order-none',
        // Grid classes
        'grid', 'inline-grid', 'grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4', 'grid-cols-5', 'grid-cols-6', 
        'grid-cols-7', 'grid-cols-8', 'grid-cols-9', 'grid-cols-10', 'grid-cols-11', 'grid-cols-12', 'grid-cols-none',
        'grid-rows-1', 'grid-rows-2', 'grid-rows-3', 'grid-rows-4', 'grid-rows-5', 'grid-rows-6', 'grid-rows-none',
        'col-auto', 'col-span-1', 'col-span-2', 'col-span-3', 'col-span-4', 'col-span-5', 'col-span-6', 
        'col-span-7', 'col-span-8', 'col-span-9', 'col-span-10', 'col-span-11', 'col-span-12', 'col-span-full',
        'row-auto', 'row-span-1', 'row-span-2', 'row-span-3', 'row-span-4', 'row-span-5', 'row-span-6', 'row-span-full',
        'gap-0', 'gap-1', 'gap-2', 'gap-3', 'gap-4', 'gap-5', 'gap-6', 'gap-7', 'gap-8', 'gap-9', 'gap-10', 'gap-11', 'gap-12',
        'gap-x-0', 'gap-x-1', 'gap-x-2', 'gap-x-3', 'gap-x-4', 'gap-x-5', 'gap-x-6', 'gap-x-7', 'gap-x-8', 'gap-x-9', 'gap-x-10', 'gap-x-11', 'gap-x-12',
        'gap-y-0', 'gap-y-1', 'gap-y-2', 'gap-y-3', 'gap-y-4', 'gap-y-5', 'gap-y-6', 'gap-y-7', 'gap-y-8', 'gap-y-9', 'gap-y-10', 'gap-y-11', 'gap-y-12',
        
        // Width and Height classes
        'w-0', 'w-1', 'w-2', 'w-3', 'w-4', 'w-5', 'w-6', 'w-8', 'w-10', 'w-11', 'w-12', 'w-14', 'w-16', 'w-20', 'w-24', 'w-28', 'w-32', 'w-36', 'w-40', 'w-44', 'w-48', 'w-52', 'w-56', 'w-60', 'w-64', 'w-72', 'w-80', 'w-96',
        'w-auto', 'w-px', 'w-0.5', 'w-1.5', 'w-2.5', 'w-3.5', 'w-full', 'w-screen', 'w-min', 'w-max', 'w-fit',
        'w-1/2', 'w-1/3', 'w-2/3', 'w-1/4', 'w-2/4', 'w-3/4', 'w-1/5', 'w-2/5', 'w-3/5', 'w-4/5', 'w-1/6', 'w-2/6', 'w-3/6', 'w-4/6', 'w-5/6',
        'h-0', 'h-1', 'h-2', 'h-3', 'h-4', 'h-5', 'h-6', 'h-8', 'h-10', 'h-11', 'h-12', 'h-14', 'h-16', 'h-20', 'h-24', 'h-28', 'h-32', 'h-36', 'h-40', 'h-44', 'h-48', 'h-52', 'h-56', 'h-60', 'h-64', 'h-72', 'h-80', 'h-96',
        'h-auto', 'h-px', 'h-0.5', 'h-1.5', 'h-2.5', 'h-3.5', 'h-full', 'h-screen', 'h-min', 'h-max', 'h-fit',
        'h-1/2', 'h-1/3', 'h-2/3', 'h-1/4', 'h-2/4', 'h-3/4', 'h-1/5', 'h-2/5', 'h-3/5', 'h-4/5', 'h-1/6', 'h-2/6', 'h-3/6', 'h-4/6', 'h-5/6',
        
        // Padding and Margin (all directions and sizes)
        'p-0', 'p-1', 'p-2', 'p-3', 'p-4', 'p-5', 'p-6', 'p-7', 'p-8', 'p-9', 'p-10', 'p-11', 'p-12', 'p-14', 'p-16', 'p-20', 'p-24', 'p-28', 'p-32', 'p-36', 'p-40', 'p-44', 'p-48', 'p-52', 'p-56', 'p-60', 'p-64', 'p-72', 'p-80', 'p-96',
        'px-0', 'px-1', 'px-2', 'px-3', 'px-4', 'px-5', 'px-6', 'px-7', 'px-8', 'px-9', 'px-10', 'px-11', 'px-12', 'px-14', 'px-16', 'px-20', 'px-24', 'px-28', 'px-32', 'px-36', 'px-40', 'px-44', 'px-48', 'px-52', 'px-56', 'px-60', 'px-64', 'px-72', 'px-80', 'px-96',
        'py-0', 'py-1', 'py-2', 'py-3', 'py-4', 'py-5', 'py-6', 'py-7', 'py-8', 'py-9', 'py-10', 'py-11', 'py-12', 'py-14', 'py-16', 'py-20', 'py-24', 'py-28', 'py-32', 'py-36', 'py-40', 'py-44', 'py-48', 'py-52', 'py-56', 'py-60', 'py-64', 'py-72', 'py-80', 'py-96',
        'pt-0', 'pt-1', 'pt-2', 'pt-3', 'pt-4', 'pt-5', 'pt-6', 'pt-7', 'pt-8', 'pt-9', 'pt-10', 'pt-11', 'pt-12', 'pt-14', 'pt-16', 'pt-20', 'pt-24', 'pt-28', 'pt-32', 'pt-36', 'pt-40', 'pt-44', 'pt-48', 'pt-52', 'pt-56', 'pt-60', 'pt-64', 'pt-72', 'pt-80', 'pt-96',
        'pr-0', 'pr-1', 'pr-2', 'pr-3', 'pr-4', 'pr-5', 'pr-6', 'pr-7', 'pr-8', 'pr-9', 'pr-10', 'pr-11', 'pr-12', 'pr-14', 'pr-16', 'pr-20', 'pr-24', 'pr-28', 'pr-32', 'pr-36', 'pr-40', 'pr-44', 'pr-48', 'pr-52', 'pr-56', 'pr-60', 'pr-64', 'pr-72', 'pr-80', 'pr-96',
        'pb-0', 'pb-1', 'pb-2', 'pb-3', 'pb-4', 'pb-5', 'pb-6', 'pb-7', 'pb-8', 'pb-9', 'pb-10', 'pb-11', 'pb-12', 'pb-14', 'pb-16', 'pb-20', 'pb-24', 'pb-28', 'pb-32', 'pb-36', 'pb-40', 'pb-44', 'pb-48', 'pb-52', 'pb-56', 'pb-60', 'pb-64', 'pb-72', 'pb-80', 'pb-96',
        'pl-0', 'pl-1', 'pl-2', 'pl-3', 'pl-4', 'pl-5', 'pl-6', 'pl-7', 'pl-8', 'pl-9', 'pl-10', 'pl-11', 'pl-12', 'pl-14', 'pl-16', 'pl-20', 'pl-24', 'pl-28', 'pl-32', 'pl-36', 'pl-40', 'pl-44', 'pl-48', 'pl-52', 'pl-56', 'pl-60', 'pl-64', 'pl-72', 'pl-80', 'pl-96',
        'm-0', 'm-1', 'm-2', 'm-3', 'm-4', 'm-5', 'm-6', 'm-7', 'm-8', 'm-9', 'm-10', 'm-11', 'm-12', 'm-14', 'm-16', 'm-20', 'm-24', 'm-28', 'm-32', 'm-36', 'm-40', 'm-44', 'm-48', 'm-52', 'm-56', 'm-60', 'm-64', 'm-72', 'm-80', 'm-96', 'm-auto',
        'mx-0', 'mx-1', 'mx-2', 'mx-3', 'mx-4', 'mx-5', 'mx-6', 'mx-7', 'mx-8', 'mx-9', 'mx-10', 'mx-11', 'mx-12', 'mx-14', 'mx-16', 'mx-20', 'mx-24', 'mx-28', 'mx-32', 'mx-36', 'mx-40', 'mx-44', 'mx-48', 'mx-52', 'mx-56', 'mx-60', 'mx-64', 'mx-72', 'mx-80', 'mx-96', 'mx-auto',
        'my-0', 'my-1', 'my-2', 'my-3', 'my-4', 'my-5', 'my-6', 'my-7', 'my-8', 'my-9', 'my-10', 'my-11', 'my-12', 'my-14', 'my-16', 'my-20', 'my-24', 'my-28', 'my-32', 'my-36', 'my-40', 'my-44', 'my-48', 'my-52', 'my-56', 'my-60', 'my-64', 'my-72', 'my-80', 'my-96', 'my-auto',
        'mt-0', 'mt-1', 'mt-2', 'mt-3', 'mt-4', 'mt-5', 'mt-6', 'mt-7', 'mt-8', 'mt-9', 'mt-10', 'mt-11', 'mt-12', 'mt-14', 'mt-16', 'mt-20', 'mt-24', 'mt-28', 'mt-32', 'mt-36', 'mt-40', 'mt-44', 'mt-48', 'mt-52', 'mt-56', 'mt-60', 'mt-64', 'mt-72', 'mt-80', 'mt-96', 'mt-auto',
        'mr-0', 'mr-1', 'mr-2', 'mr-3', 'mr-4', 'mr-5', 'mr-6', 'mr-7', 'mr-8', 'mr-9', 'mr-10', 'mr-11', 'mr-12', 'mr-14', 'mr-16', 'mr-20', 'mr-24', 'mr-28', 'mr-32', 'mr-36', 'mr-40', 'mr-44', 'mr-48', 'mr-52', 'mr-56', 'mr-60', 'mr-64', 'mr-72', 'mr-80', 'mr-96', 'mr-auto',
        'mb-0', 'mb-1', 'mb-2', 'mb-3', 'mb-4', 'mb-5', 'mb-6', 'mb-7', 'mb-8', 'mb-9', 'mb-10', 'mb-11', 'mb-12', 'mb-14', 'mb-16', 'mb-20', 'mb-24', 'mb-28', 'mb-32', 'mb-36', 'mb-40', 'mb-44', 'mb-48', 'mb-52', 'mb-56', 'mb-60', 'mb-64', 'mb-72', 'mb-80', 'mb-96', 'mb-auto',
        'ml-0', 'ml-1', 'ml-2', 'ml-3', 'ml-4', 'ml-5', 'ml-6', 'ml-7', 'ml-8', 'ml-9', 'ml-10', 'ml-11', 'ml-12', 'ml-14', 'ml-16', 'ml-20', 'ml-24', 'ml-28', 'ml-32', 'ml-36', 'ml-40', 'ml-44', 'ml-48', 'ml-52', 'ml-56', 'ml-60', 'ml-64', 'ml-72', 'ml-80', 'ml-96', 'ml-auto',
        
        // Typography
        'text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl', 'text-5xl', 'text-6xl', 'text-7xl', 'text-8xl', 'text-9xl',
        'font-thin', 'font-extralight', 'font-light', 'font-normal', 'font-medium', 'font-semibold', 'font-bold', 'font-extrabold', 'font-black',
        'italic', 'not-italic', 'underline', 'overline', 'line-through', 'no-underline',
        'uppercase', 'lowercase', 'capitalize', 'normal-case',
        'text-left', 'text-center', 'text-right', 'text-justify', 'text-start', 'text-end',
        'leading-3', 'leading-4', 'leading-5', 'leading-6', 'leading-7', 'leading-8', 'leading-9', 'leading-10',
        'leading-none', 'leading-tight', 'leading-snug', 'leading-normal', 'leading-relaxed', 'leading-loose',
        'tracking-tighter', 'tracking-tight', 'tracking-normal', 'tracking-wide', 'tracking-wider', 'tracking-widest',
        'break-words', 'break-all', 'break-keep', 'whitespace-normal', 'whitespace-nowrap', 'whitespace-pre', 'whitespace-pre-line', 'whitespace-pre-wrap',
        
        // Positioning
        'static', 'fixed', 'absolute', 'relative', 'sticky',
        'inset-0', 'inset-x-0', 'inset-y-0', 'start-0', 'end-0', 'top-0', 'right-0', 'bottom-0', 'left-0',
        'inset-px', 'inset-x-px', 'inset-y-px', 'start-px', 'end-px', 'top-px', 'right-px', 'bottom-px', 'left-px',
        'inset-0.5', 'inset-1.5', 'inset-2.5', 'inset-3.5', 'inset-1', 'inset-2', 'inset-3', 'inset-4', 'inset-5', 'inset-6', 'inset-8', 'inset-10', 'inset-12', 'inset-16', 'inset-20', 'inset-24',
        'z-0', 'z-10', 'z-20', 'z-30', 'z-40', 'z-50', 'z-auto', '-z-10', '-z-20', '-z-30', '-z-40', '-z-50',
        
        // Borders and Rounded
        'rounded-none', 'rounded-sm', 'rounded', 'rounded-md', 'rounded-lg', 'rounded-xl', 'rounded-2xl', 'rounded-3xl', 'rounded-full',
        'rounded-t-none', 'rounded-t-sm', 'rounded-t', 'rounded-t-md', 'rounded-t-lg', 'rounded-t-xl', 'rounded-t-2xl', 'rounded-t-3xl',
        'rounded-r-none', 'rounded-r-sm', 'rounded-r', 'rounded-r-md', 'rounded-r-lg', 'rounded-r-xl', 'rounded-r-2xl', 'rounded-r-3xl',
        'rounded-b-none', 'rounded-b-sm', 'rounded-b', 'rounded-b-md', 'rounded-b-lg', 'rounded-b-xl', 'rounded-b-2xl', 'rounded-b-3xl',
        'rounded-l-none', 'rounded-l-sm', 'rounded-l', 'rounded-l-md', 'rounded-l-lg', 'rounded-l-xl', 'rounded-l-2xl', 'rounded-l-3xl',
        'border-0', 'border-2', 'border-4', 'border-8', 'border', 'border-x-0', 'border-x-2', 'border-x-4', 'border-x-8', 'border-x',
        'border-y-0', 'border-y-2', 'border-y-4', 'border-y-8', 'border-y', 'border-t-0', 'border-t-2', 'border-t-4', 'border-t-8', 'border-t',
        'border-r-0', 'border-r-2', 'border-r-4', 'border-r-8', 'border-r', 'border-b-0', 'border-b-2', 'border-b-4', 'border-b-8', 'border-b',
        'border-l-0', 'border-l-2', 'border-l-4', 'border-l-8', 'border-l',
        'border-solid', 'border-dashed', 'border-dotted', 'border-double', 'border-hidden', 'border-none',
        
        // Shadows and Effects
        'shadow-sm', 'shadow', 'shadow-md', 'shadow-lg', 'shadow-xl', 'shadow-2xl', 'shadow-inner', 'shadow-none',
        'opacity-0', 'opacity-5', 'opacity-10', 'opacity-20', 'opacity-25', 'opacity-30', 'opacity-40', 'opacity-50', 'opacity-60', 'opacity-70', 'opacity-75', 'opacity-80', 'opacity-90', 'opacity-95', 'opacity-100',
        
        // Outline
        'outline-none', 'outline', 'outline-dashed', 'outline-dotted', 'outline-double', 'outline-hidden',
        'outline-0', 'outline-1', 'outline-2', 'outline-4', 'outline-8',
        'outline-offset-0', 'outline-offset-1', 'outline-offset-2', 'outline-offset-4', 'outline-offset-8',
        
        // Ring (Focus rings)
        'ring-0', 'ring-1', 'ring-2', 'ring-4', 'ring-8', 'ring', 'ring-inset',
        'ring-offset-0', 'ring-offset-1', 'ring-offset-2', 'ring-offset-4', 'ring-offset-8',
        
        // Backdrop Filters
        'backdrop-blur-none', 'backdrop-blur-sm', 'backdrop-blur', 'backdrop-blur-md', 'backdrop-blur-lg', 'backdrop-blur-xl', 'backdrop-blur-2xl', 'backdrop-blur-3xl',
        'backdrop-brightness-0', 'backdrop-brightness-50', 'backdrop-brightness-75', 'backdrop-brightness-90', 'backdrop-brightness-95', 'backdrop-brightness-100', 'backdrop-brightness-105', 'backdrop-brightness-110', 'backdrop-brightness-125', 'backdrop-brightness-150', 'backdrop-brightness-200',
        'backdrop-contrast-0', 'backdrop-contrast-50', 'backdrop-contrast-75', 'backdrop-contrast-100', 'backdrop-contrast-125', 'backdrop-contrast-150', 'backdrop-contrast-200',
        'backdrop-grayscale-0', 'backdrop-grayscale',
        'backdrop-hue-rotate-0', 'backdrop-hue-rotate-15', 'backdrop-hue-rotate-30', 'backdrop-hue-rotate-60', 'backdrop-hue-rotate-90', 'backdrop-hue-rotate-180',
        'backdrop-invert-0', 'backdrop-invert',
        'backdrop-opacity-0', 'backdrop-opacity-5', 'backdrop-opacity-10', 'backdrop-opacity-20', 'backdrop-opacity-25', 'backdrop-opacity-30', 'backdrop-opacity-40', 'backdrop-opacity-50', 'backdrop-opacity-60', 'backdrop-opacity-70', 'backdrop-opacity-75', 'backdrop-opacity-80', 'backdrop-opacity-90', 'backdrop-opacity-95', 'backdrop-opacity-100',
        'backdrop-saturate-0', 'backdrop-saturate-50', 'backdrop-saturate-100', 'backdrop-saturate-150', 'backdrop-saturate-200',
        'backdrop-sepia-0', 'backdrop-sepia',
        
        // Filters
        'filter', 'filter-none',
        'blur-none', 'blur-sm', 'blur', 'blur-md', 'blur-lg', 'blur-xl', 'blur-2xl', 'blur-3xl',
        'brightness-0', 'brightness-50', 'brightness-75', 'brightness-90', 'brightness-95', 'brightness-100', 'brightness-105', 'brightness-110', 'brightness-125', 'brightness-150', 'brightness-200',
        'contrast-0', 'contrast-50', 'contrast-75', 'contrast-100', 'contrast-125', 'contrast-150', 'contrast-200',
        'drop-shadow-sm', 'drop-shadow', 'drop-shadow-md', 'drop-shadow-lg', 'drop-shadow-xl', 'drop-shadow-2xl', 'drop-shadow-none',
        'grayscale-0', 'grayscale',
        'hue-rotate-0', 'hue-rotate-15', 'hue-rotate-30', 'hue-rotate-60', 'hue-rotate-90', 'hue-rotate-180',
        'invert-0', 'invert',
        'saturate-0', 'saturate-50', 'saturate-100', 'saturate-150', 'saturate-200',
        'sepia-0', 'sepia',
        
        // Overflow and Visibility
        'overflow-auto', 'overflow-hidden', 'overflow-clip', 'overflow-visible', 'overflow-scroll',
        'overflow-x-auto', 'overflow-x-hidden', 'overflow-x-clip', 'overflow-x-visible', 'overflow-x-scroll',
        'overflow-y-auto', 'overflow-y-hidden', 'overflow-y-clip', 'overflow-y-visible', 'overflow-y-scroll',
        'visible', 'invisible', 'collapse',
        
        // Object Fit and Position
        'object-contain', 'object-cover', 'object-fill', 'object-none', 'object-scale-down',
        'object-bottom', 'object-center', 'object-left', 'object-left-bottom', 'object-left-top', 'object-right', 'object-right-bottom', 'object-right-top', 'object-top',
        
        // Background Size and Position
        'bg-auto', 'bg-cover', 'bg-contain',
        'bg-center', 'bg-top', 'bg-right-top', 'bg-right', 'bg-right-bottom', 'bg-bottom', 'bg-left-bottom', 'bg-left', 'bg-left-top',
        'bg-repeat', 'bg-no-repeat', 'bg-repeat-x', 'bg-repeat-y', 'bg-repeat-round', 'bg-repeat-space',
        'bg-origin-border', 'bg-origin-padding', 'bg-origin-content',
        'bg-clip-border', 'bg-clip-padding', 'bg-clip-content', 'bg-clip-text',
        
        // Background Attachment and Blend
        'bg-fixed', 'bg-local', 'bg-scroll',
        'bg-blend-normal', 'bg-blend-multiply', 'bg-blend-screen', 'bg-blend-overlay', 'bg-blend-darken', 'bg-blend-lighten', 'bg-blend-color-dodge', 'bg-blend-color-burn', 'bg-blend-hard-light', 'bg-blend-soft-light', 'bg-blend-difference', 'bg-blend-exclusion', 'bg-blend-hue', 'bg-blend-saturation', 'bg-blend-color', 'bg-blend-luminosity',
        'mix-blend-normal', 'mix-blend-multiply', 'mix-blend-screen', 'mix-blend-overlay', 'mix-blend-darken', 'mix-blend-lighten', 'mix-blend-color-dodge', 'mix-blend-color-burn', 'mix-blend-hard-light', 'mix-blend-soft-light', 'mix-blend-difference', 'mix-blend-exclusion', 'mix-blend-hue', 'mix-blend-saturation', 'mix-blend-color', 'mix-blend-luminosity', 'mix-blend-plus-lighter',
        
        // Table Layout
        'table', 'inline-table', 'table-caption', 'table-cell', 'table-column', 'table-column-group', 'table-footer-group', 'table-header-group', 'table-row-group', 'table-row',
        'table-auto', 'table-fixed',
        'border-collapse', 'border-separate',
        'caption-top', 'caption-bottom',
        
        // List Styles
        'list-inside', 'list-outside', 'list-none', 'list-disc', 'list-decimal',
        
        // Appearance and User Select
        'appearance-none', 'appearance-auto',
        'select-none', 'select-text', 'select-all', 'select-auto',
        
        // Resize
        'resize-none', 'resize-y', 'resize-x', 'resize',
        
        // Scroll Behavior and Snap
        'scroll-auto', 'scroll-smooth',
        'snap-none', 'snap-x', 'snap-y', 'snap-both', 'snap-mandatory', 'snap-proximity',
        'snap-start', 'snap-end', 'snap-center', 'snap-align-none',
        
        // Touch Action
        'touch-auto', 'touch-none', 'touch-pan-x', 'touch-pan-left', 'touch-pan-right', 'touch-pan-y', 'touch-pan-up', 'touch-pan-down', 'touch-pinch-zoom', 'touch-manipulation',
        
        // Will Change
        'will-change-auto', 'will-change-scroll', 'will-change-contents', 'will-change-transform',
        
        // Content
        'content-none',
        
        // Cursor and Pointer Events
        'cursor-auto', 'cursor-default', 'cursor-pointer', 'cursor-wait', 'cursor-text', 'cursor-move', 'cursor-help', 'cursor-not-allowed', 'cursor-none', 'cursor-context-menu', 'cursor-progress', 'cursor-cell', 'cursor-crosshair', 'cursor-vertical-text', 'cursor-alias', 'cursor-copy', 'cursor-no-drop', 'cursor-grab', 'cursor-grabbing',
        'pointer-events-none', 'pointer-events-auto',
        
        // Animations and Transitions
        'animate-none', 'animate-spin', 'animate-ping', 'animate-pulse', 'animate-bounce',
        'transition-none', 'transition-all', 'transition', 'transition-colors', 'transition-opacity', 'transition-shadow', 'transition-transform',
        'duration-75', 'duration-100', 'duration-150', 'duration-200', 'duration-300', 'duration-500', 'duration-700', 'duration-1000',
        'ease-linear', 'ease-in', 'ease-out', 'ease-in-out',
        
        // Transform and Scale
        'transform', 'transform-cpu', 'transform-gpu', 'transform-none',
        'scale-0', 'scale-50', 'scale-75', 'scale-90', 'scale-95', 'scale-100', 'scale-105', 'scale-110', 'scale-125', 'scale-150',
        'rotate-0', 'rotate-1', 'rotate-2', 'rotate-3', 'rotate-6', 'rotate-12', 'rotate-45', 'rotate-90', 'rotate-180', '-rotate-180', '-rotate-90', '-rotate-45', '-rotate-12', '-rotate-6', '-rotate-3', '-rotate-2', '-rotate-1',
        'translate-x-0', 'translate-x-1', 'translate-x-2', 'translate-x-3', 'translate-x-4', 'translate-x-5', 'translate-x-6', 'translate-x-8', 'translate-x-10', 'translate-x-12', 'translate-x-16', 'translate-x-20', 'translate-x-24',
        'translate-y-0', 'translate-y-1', 'translate-y-2', 'translate-y-3', 'translate-y-4', 'translate-y-5', 'translate-y-6', 'translate-y-8', 'translate-y-10', 'translate-y-12', 'translate-y-16', 'translate-y-20', 'translate-y-24',
        'skew-x-0', 'skew-x-1', 'skew-x-2', 'skew-x-3', 'skew-x-6', 'skew-x-12', 'skew-y-0', 'skew-y-1', 'skew-y-2', 'skew-y-3', 'skew-y-6', 'skew-y-12',
        
        // Max-width responsive classes
        'max-w-xs', 'max-w-sm', 'max-w-md', 'max-w-lg', 'max-w-xl', 'max-w-2xl', 'max-w-3xl', 'max-w-4xl', 'max-w-5xl', 'max-w-6xl', 'max-w-7xl',
        'max-w-full', 'max-w-fit', 'max-w-min', 'max-w-max', 'max-w-prose', 'max-w-screen-sm', 'max-w-screen-md', 'max-w-screen-lg', 'max-w-screen-xl', 'max-w-screen-2xl',
        // Responsive max-width classes
        'sm:max-w-xs', 'sm:max-w-sm', 'sm:max-w-md', 'sm:max-w-lg', 'sm:max-w-xl', 'sm:max-w-2xl', 'sm:max-w-3xl', 'sm:max-w-4xl', 'sm:max-w-5xl', 'sm:max-w-6xl', 'sm:max-w-7xl',
        'sm:max-w-full', 'sm:max-w-fit', 'sm:max-w-min', 'sm:max-w-max', 'sm:max-w-prose', 'sm:max-w-screen-sm', 'sm:max-w-screen-md', 'sm:max-w-screen-lg', 'sm:max-w-screen-xl', 'sm:max-w-screen-2xl',
        'md:max-w-xs', 'md:max-w-sm', 'md:max-w-md', 'md:max-w-lg', 'md:max-w-xl', 'md:max-w-2xl', 'md:max-w-3xl', 'md:max-w-4xl', 'md:max-w-5xl', 'md:max-w-6xl', 'md:max-w-7xl',
        'md:max-w-full', 'md:max-w-fit', 'md:max-w-min', 'md:max-w-max', 'md:max-w-prose', 'md:max-w-screen-sm', 'md:max-w-screen-md', 'md:max-w-screen-lg', 'md:max-w-screen-xl', 'md:max-w-screen-2xl',
        'lg:max-w-xs', 'lg:max-w-sm', 'lg:max-w-md', 'lg:max-w-lg', 'lg:max-w-xl', 'lg:max-w-2xl', 'lg:max-w-3xl', 'lg:max-w-4xl', 'lg:max-w-5xl', 'lg:max-w-6xl', 'lg:max-w-7xl',
        'lg:max-w-full', 'lg:max-w-fit', 'lg:max-w-min', 'lg:max-w-max', 'lg:max-w-prose', 'lg:max-w-screen-sm', 'lg:max-w-screen-md', 'lg:max-w-screen-lg', 'lg:max-w-screen-xl', 'lg:max-w-screen-2xl',
        'xl:max-w-xs', 'xl:max-w-sm', 'xl:max-w-md', 'xl:max-w-lg', 'xl:max-w-xl', 'xl:max-w-2xl', 'xl:max-w-3xl', 'xl:max-w-4xl', 'xl:max-w-5xl', 'xl:max-w-6xl', 'xl:max-w-7xl',
        'xl:max-w-full', 'xl:max-w-fit', 'xl:max-w-min', 'xl:max-w-max', 'xl:max-w-prose', 'xl:max-w-screen-sm', 'xl:max-w-screen-md', 'xl:max-w-screen-lg', 'xl:max-w-screen-xl', 'xl:max-w-screen-2xl',
        '2xl:max-w-xs', '2xl:max-w-sm', '2xl:max-w-md', '2xl:max-w-lg', '2xl:max-w-xl', '2xl:max-w-2xl', '2xl:max-w-3xl', '2xl:max-w-4xl', '2xl:max-w-5xl', '2xl:max-w-6xl', '2xl:max-w-7xl',
        '2xl:max-w-full', '2xl:max-w-fit', '2xl:max-w-min', '2xl:max-w-max', '2xl:max-w-prose', '2xl:max-w-screen-sm', '2xl:max-w-screen-md', '2xl:max-w-screen-lg', '2xl:max-w-screen-xl', '2xl:max-w-screen-2xl',
        
        // Responsive display and flexbox classes
        'sm:flex', 'sm:inline-flex', 'sm:block', 'sm:inline-block', 'sm:hidden',
        'md:flex', 'md:inline-flex', 'md:block', 'md:inline-block', 'md:hidden',
        'lg:flex', 'lg:inline-flex', 'lg:block', 'lg:inline-block', 'lg:hidden',
        'xl:flex', 'xl:inline-flex', 'xl:block', 'xl:inline-block', 'xl:hidden',
        'sm:flex-row', 'sm:flex-col', 'sm:justify-center', 'sm:justify-between', 'sm:items-center',
        'md:flex-row', 'md:flex-col', 'md:justify-center', 'md:justify-between', 'md:items-center',
        'lg:flex-row', 'lg:flex-col', 'lg:justify-center', 'lg:justify-between', 'lg:items-center',
        
        // Responsive typography
        'sm:text-xs', 'sm:text-sm', 'sm:text-base', 'sm:text-lg', 'sm:text-xl', 'sm:text-2xl', 'sm:text-3xl', 'sm:text-4xl',
        'md:text-xs', 'md:text-sm', 'md:text-base', 'md:text-lg', 'md:text-xl', 'md:text-2xl', 'md:text-3xl', 'md:text-4xl',
        'lg:text-xs', 'lg:text-sm', 'lg:text-base', 'lg:text-lg', 'lg:text-xl', 'lg:text-2xl', 'lg:text-3xl', 'lg:text-4xl',
        
        // Responsive spacing (most common ones)
        'sm:p-0', 'sm:p-1', 'sm:p-2', 'sm:p-4', 'sm:p-6', 'sm:p-8', 'sm:px-4', 'sm:py-2', 'sm:m-0', 'sm:m-2', 'sm:m-4', 'sm:mx-auto',
        'md:p-0', 'md:p-1', 'md:p-2', 'md:p-4', 'md:p-6', 'md:p-8', 'md:px-4', 'md:py-2', 'md:m-0', 'md:m-2', 'md:m-4', 'md:mx-auto',
        'lg:p-0', 'lg:p-1', 'lg:p-2', 'lg:p-4', 'lg:p-6', 'lg:p-8', 'lg:px-4', 'lg:py-2', 'lg:m-0', 'lg:m-2', 'lg:m-4', 'lg:mx-auto',
        
        // Responsive width/height
        'sm:w-full', 'sm:w-auto', 'sm:w-1/2', 'sm:w-1/3', 'sm:w-2/3', 'sm:w-1/4', 'sm:w-3/4',
        'md:w-full', 'md:w-auto', 'md:w-1/2', 'md:w-1/3', 'md:w-2/3', 'md:w-1/4', 'md:w-3/4',
        'lg:w-full', 'lg:w-auto', 'lg:w-1/2', 'lg:w-1/3', 'lg:w-2/3', 'lg:w-1/4', 'lg:w-3/4',
        
        // Odd/even table row classes
        'even:bg-gray-50', 'odd:bg-white', 'even:bg-gray-100', 'odd:bg-gray-50',
        'dark:even:bg-gray-700', 'dark:odd:bg-gray-800', 'dark:even:bg-gray-600', 'dark:odd:bg-gray-700',
        'hover:bg-gray-100', 'dark:hover:bg-gray-600', 'transition-colors', 'duration-150',
        
        // Toggle switch essential classes
        'peer', 'peer-focus:ring-4', 'peer-checked:after:translate-x-full', 'peer-checked:after:border-white',
        'after:content-[""]', 'after:absolute', 'after:top-0.5', 'after:left-[2px]', 'after:bg-white',
        'after:border-gray-300', 'after:border', 'after:rounded-full', 'after:h-5', 'after:w-5', 'after:transition-all',
        
        // Checkbox selection state classes - generated for all theme colors
        ...['sky', 'cyan', 'emerald', 'teal', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'green', 'pink', 'red', 'rose', 'orange', 'yellow', 'amber', 'lime', 'gray', 'slate', 'stone'].flatMap(color => [
            `has-[:checked]:bg-${color}-500`,
            `has-[:checked]:text-white`, 
            `dark:has-[:checked]:bg-${color}-600`,
            `dark:has-[:checked]:text-white`,
            `focus:bg-${color}-500`,
            `focus:text-white`,
            `dark:focus:bg-${color}-600`,
            `dark:focus:text-white`,
            // Toggle/peer-checked classes
            `peer-checked:bg-${color}-500`,
            `peer-checked:bg-${color}-600`,
            `dark:peer-checked:bg-${color}-500`,
            `dark:peer-checked:bg-${color}-600`,
            `peer-focus:ring-${color}-300`,
            `peer-focus:ring-${color}-400`,
            `dark:peer-focus:ring-${color}-800`,
            `dark:peer-focus:ring-${color}-900`
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
                `fill-${color}-${shade}`,
                // Hover states
                `hover:bg-${color}-${shade}`,
                `hover:text-${color}-${shade}`,  
                `hover:border-${color}-${shade}`,
                `hover:from-${color}-${shade}`,
                `hover:to-${color}-${shade}`,
                `hover:stroke-${color}-${shade}`,
                `hover:fill-${color}-${shade}`,
                // Dark mode
                `dark:bg-${color}-${shade}`,
                `dark:text-${color}-${shade}`,
                `dark:border-${color}-${shade}`,
                `dark:ring-${color}-${shade}`,
                `dark:stroke-${color}-${shade}`,
                `dark:fill-${color}-${shade}`,
                // Dark mode hover
                `dark:hover:bg-${color}-${shade}`,
                `dark:hover:text-${color}-${shade}`,
                `dark:hover:border-${color}-${shade}`,
                `dark:hover:stroke-${color}-${shade}`,
                `dark:hover:fill-${color}-${shade}`,
                // Focus states
                `focus:ring-${color}-${shade}`,
                `focus:border-${color}-${shade}`,
                `focus:stroke-${color}-${shade}`,
                `focus:fill-${color}-${shade}`,
                // Dark focus states
                `dark:focus:ring-${color}-${shade}`,
                `dark:focus:border-${color}-${shade}`,
                `dark:focus:stroke-${color}-${shade}`,
                `dark:focus:fill-${color}-${shade}`,
                // Peer focus states
                `peer-focus:bg-${color}-${shade}`,
                `peer-focus:text-${color}-${shade}`,
                `peer-focus:border-${color}-${shade}`,
                `peer-focus:ring-${color}-${shade}`,
                `peer-focus:stroke-${color}-${shade}`,
                `peer-focus:fill-${color}-${shade}`,
                // Dark peer focus states
                `dark:peer-focus:bg-${color}-${shade}`,
                `dark:peer-focus:text-${color}-${shade}`,
                `dark:peer-focus:border-${color}-${shade}`,
                `dark:peer-focus:ring-${color}-${shade}`,
                `dark:peer-focus:stroke-${color}-${shade}`,
                `dark:peer-focus:fill-${color}-${shade}`
            ])
        )
    ],
    plugins: [
        require('@tailwindcss/typography'),
    ],
}
