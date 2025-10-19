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
  // Dynamic color-based classes (ALL shades including 50, 400, 500, 600, 800, 900)
  {
    pattern: /^(bg|text|border|ring|from|to|via|fill|stroke)-(slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-(50|100|200|300|400|500|600|700|800|900)$/,
    variants: ['hover', 'focus', 'active', 'dark', 'group-hover'],
  },

  // Opacity variations for colors
  {
    pattern: /^(bg|text|border|ring)-opacity-(0|5|10|20|25|30|40|50|60|70|75|80|90|95|100)$/,
    variants: ['hover', 'focus', 'dark'],
  },

  // Comprehensive spacing and sizing
  {
    pattern: /^(w|h|min-w|min-h|max-w|max-h)-(\d+|px|auto|full|screen|min|max|fit)$/,
    variants: ['sm', 'md', 'lg', 'xl', '2xl'],
  },
  {
    pattern: /^(p|px|py|pl|pr|pt|pb|m|mx|my|ml|mr|mt|mb)-(\d+|px|auto)$/,
    variants: ['sm', 'md', 'lg', 'xl', '2xl'],
  },

  // Layout and positioning
  {
    pattern: /^(relative|absolute|fixed|sticky|static|inset-\d+|inset-x-\d+|inset-y-\d+|top-\d+|right-\d+|bottom-\d+|left-\d+|-top-\d+|-right-\d+|-bottom-\d+|-left-\d+|inset-0|inset-px)$/,
  },

  // Flexbox and grid (comprehensive)
  {
    pattern: /^(flex|inline-flex|grid|inline-grid|flex-1|flex-auto|flex-initial|flex-none|flex-row|flex-col|flex-wrap|flex-nowrap|items-start|items-end|items-center|items-baseline|items-stretch|justify-start|justify-end|justify-center|justify-between|justify-around|justify-evenly|content-start|content-end|content-center|content-between|content-around|content-evenly)$/,
    variants: ['sm', 'md', 'lg', 'xl', '2xl'],
  },
  {
    pattern: /^(grid-cols-(\d+|none)|col-span-(\d+|full)|gap-(\d+|x-\d+|y-\d+)|space-(x|y)-(\d+|reverse))$/,
    variants: ['sm', 'md', 'lg', 'xl', '2xl'],
  },

  // Typography (comprehensive)
  {
    pattern: /^(text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)|font-(thin|extralight|light|normal|medium|semibold|bold|extrabold|black)|leading-(none|tight|snug|normal|relaxed|loose|\d+)|tracking-(tighter|tight|normal|wide|wider|widest))$/,
    variants: ['sm', 'md', 'lg', 'xl', '2xl'],
  },

  // Display and visibility
  {
    pattern: /^(block|inline-block|inline|flex|inline-flex|table|inline-table|table-caption|table-cell|table-column|table-column-group|table-footer-group|table-header-group|table-row-group|table-row|flow-root|grid|inline-grid|contents|list-item|hidden|visible|invisible)$/,
    variants: ['sm', 'md', 'lg', 'xl', '2xl'],
  },

  // Borders and rounded corners
  {
    pattern: /^(rounded|rounded-(t|r|b|l|tl|tr|br|bl)|border|border-(t|r|b|l|x|y))-(none|sm|md|lg|xl|2xl|3xl|full|\d+)$/,
    variants: ['hover', 'focus'],
  },

  // Shadows and effects
  {
    pattern: /^(shadow|shadow-(sm|md|lg|xl|2xl|inner|none)|opacity-(\d+|0|5|10|20|25|30|40|50|60|70|75|80|90|95|100))$/,
    variants: ['hover', 'focus', 'group-hover'],
  },

  // Transform and animation
  {
    pattern: /^(transform|transform-none|scale-(\d+|0|50|75|90|95|100|105|110|125|150)|rotate-(\d+|0|1|2|3|6|12|45|90|180)|-rotate-(\d+|1|2|3|6|12|45|90|180)|translate-(x|y)-(\d+|0|1|2|3|4|5|6|8|10|12|16|20|24)|-translate-(x|y)-(\d+|1|2|3|4|5|6|8|10|12|16|20|24))$/,
    variants: ['hover', 'focus', 'group-hover'],
  },

  // Transitions and duration
  {
    pattern: /^(transition|transition-(none|all|colors|opacity|shadow|transform)|duration-(\d+|75|100|150|200|300|500|700|1000)|ease-(linear|in|out|in-out))$/,
  },

  // Backdrop filters
  {
    pattern: /^(backdrop-filter|backdrop-filter-none|backdrop-blur|backdrop-blur-(none|sm|md|lg|xl|2xl|3xl)|backdrop-brightness-(\d+|0|50|75|90|95|100|105|110|125|150|200)|backdrop-contrast-(\d+|0|50|75|100|125|150|200)|backdrop-grayscale|backdrop-grayscale-0|backdrop-hue-rotate-(\d+|0|15|30|60|90|180)|backdrop-invert|backdrop-invert-0|backdrop-opacity-(\d+|0|5|10|20|25|30|40|50|60|70|75|80|90|95|100)|backdrop-saturate-(\d+|0|50|100|150|200)|backdrop-sepia|backdrop-sepia-0)$/,
  },

  // Special utility classes
  {
    pattern: /^(truncate|text-ellipsis|text-clip|break-words|break-all|whitespace-(normal|nowrap|pre|pre-line|pre-wrap)|select-(none|text|all|auto)|pointer-events-(none|auto)|resize|resize-(none|y|x)|cursor-(auto|default|pointer|wait|text|move|help|not-allowed)|outline-(none|white|black|\d+)|ring-(\d+|inset)|ring-offset-\d+|appearance-none|list-(none|disc|decimal)|sr-only|not-sr-only|overflow-(auto|hidden|visible|scroll)|overflow-(x|y)-(auto|hidden|visible|scroll))$/,
    variants: ['hover', 'focus', 'active'],
  },

  // Layout and screen classes
  'min-h-screen', 'max-w-7xl', 'max-w-2xl', 'mx-auto', 'text-center',
  
  // All possible theme color combinations (for dynamic PHP theme generation)
  // Blue theme classes
  'bg-blue-50', 'bg-blue-100', 'bg-blue-400', 'bg-blue-500', 'bg-blue-600', 'bg-blue-700', 'bg-blue-800', 'bg-blue-900',
  'text-blue-400', 'text-blue-500', 'text-blue-600', 'text-blue-700',
  'border-blue-400', 'border-blue-500', 'border-blue-600',
  'from-blue-400', 'from-blue-500', 'from-blue-600', 'to-blue-400', 'to-blue-500', 'to-blue-600', 'to-blue-700',
  'hover:bg-blue-500', 'hover:bg-blue-600', 'hover:bg-blue-700', 'hover:from-blue-600', 'hover:from-blue-700', 'hover:to-blue-700', 'hover:to-blue-800',
  'hover:border-blue-400', 'hover:border-blue-500', 'hover:text-blue-500', 'hover:text-blue-400',
  'dark:bg-blue-600', 'dark:bg-blue-700', 'dark:bg-blue-800', 'dark:bg-blue-900',
  'dark:text-blue-400', 'dark:text-blue-500',
  'dark:from-blue-400', 'dark:from-blue-500', 'dark:from-blue-600', 'dark:to-blue-400', 'dark:to-blue-500', 'dark:to-blue-600',
  'dark:hover:bg-blue-600', 'dark:hover:bg-blue-700', 'dark:hover:from-blue-700', 'dark:hover:to-blue-700',
  'dark:hover:border-blue-500', 'dark:hover:text-blue-400',
  'dark:group-hover:bg-blue-900', 'dark:group-hover:text-blue-400', 'group-hover:bg-blue-100', 'group-hover:text-blue-500',
  
  // Green theme classes  
  'bg-green-50', 'bg-green-100', 'bg-green-400', 'bg-green-500', 'bg-green-600', 'bg-green-700', 'bg-green-800', 'bg-green-900',
  'text-green-400', 'text-green-500', 'text-green-600', 'text-green-700', 'text-green-800',
  'border-green-400', 'border-green-500', 'border-green-600',
  'from-green-400', 'from-green-500', 'from-green-600', 'to-green-400', 'to-green-500', 'to-green-600', 'to-green-700',
  'hover:bg-green-500', 'hover:bg-green-600', 'hover:bg-green-700', 'hover:from-green-600', 'hover:from-green-700', 'hover:to-green-700', 'hover:to-green-800',
  'hover:border-green-400', 'hover:border-green-500', 'hover:text-green-500', 'hover:text-green-400',
  'dark:bg-green-600', 'dark:bg-green-700', 'dark:bg-green-800', 'dark:bg-green-900', 'dark:bg-green-900/30',
  'dark:text-green-400', 'dark:text-green-500',
  'dark:from-green-400', 'dark:from-green-500', 'dark:from-green-600', 'dark:to-green-400', 'dark:to-green-500', 'dark:to-green-600',
  'dark:hover:bg-green-600', 'dark:hover:bg-green-700', 'dark:hover:from-green-700', 'dark:hover:to-green-700',
  'dark:hover:border-green-500', 'dark:hover:text-green-400',
  'dark:group-hover:bg-green-900', 'dark:group-hover:text-green-400', 'group-hover:bg-green-100', 'group-hover:text-green-500',
  
  // Red theme classes
  'bg-red-50', 'bg-red-100', 'bg-red-400', 'bg-red-500', 'bg-red-600', 'bg-red-700', 'bg-red-800', 'bg-red-900',
  'text-red-400', 'text-red-500', 'text-red-600', 'text-red-700', 'text-red-800',
  'border-red-200', 'border-red-400', 'border-red-500', 'border-red-600', 'border-red-800',
  'from-red-400', 'from-red-500', 'from-red-600', 'to-red-400', 'to-red-500', 'to-red-600', 'to-red-700',
  'hover:bg-red-100', 'hover:bg-red-500', 'hover:bg-red-600', 'hover:bg-red-700', 'hover:from-red-600', 'hover:from-red-700', 'hover:to-red-700', 'hover:to-red-800',
  'hover:border-red-400', 'hover:border-red-500', 'hover:text-red-500', 'hover:text-red-400',
  'dark:bg-red-600', 'dark:bg-red-700', 'dark:bg-red-800', 'dark:bg-red-900', 'dark:bg-red-900/20', 'dark:bg-red-900/30',
  'dark:text-red-400', 'dark:text-red-500', 'dark:border-red-800',
  'dark:from-red-400', 'dark:from-red-500', 'dark:from-red-600', 'dark:to-red-400', 'dark:to-red-500', 'dark:to-red-600',
  'dark:hover:bg-red-600', 'dark:hover:bg-red-700', 'dark:hover:bg-red-900/30', 'dark:hover:from-red-700', 'dark:hover:to-red-700',
  'dark:hover:border-red-500', 'dark:hover:text-red-400',
  'dark:group-hover:bg-red-900', 'dark:group-hover:text-red-400', 'group-hover:bg-red-100', 'group-hover:text-red-500',
  
  // Purple theme classes
  'bg-purple-50', 'bg-purple-100', 'bg-purple-400', 'bg-purple-500', 'bg-purple-600', 'bg-purple-700', 'bg-purple-800', 'bg-purple-900',
  'text-purple-400', 'text-purple-500', 'text-purple-600', 'text-purple-700',
  'border-purple-400', 'border-purple-500', 'border-purple-600',
  'from-purple-400', 'from-purple-500', 'from-purple-600', 'to-purple-400', 'to-purple-500', 'to-purple-600', 'to-purple-700',
  'hover:bg-purple-500', 'hover:bg-purple-600', 'hover:bg-purple-700', 'hover:from-purple-600', 'hover:from-purple-700', 'hover:to-purple-700', 'hover:to-purple-800',
  'hover:border-purple-400', 'hover:border-purple-500', 'hover:text-purple-500', 'hover:text-purple-400',
  'dark:bg-purple-600', 'dark:bg-purple-700', 'dark:bg-purple-800', 'dark:bg-purple-900',
  'dark:text-purple-400', 'dark:text-purple-500',
  'dark:from-purple-400', 'dark:from-purple-500', 'dark:from-purple-600', 'dark:to-purple-400', 'dark:to-purple-500', 'dark:to-purple-600',
  'dark:hover:bg-purple-600', 'dark:hover:bg-purple-700', 'dark:hover:from-purple-700', 'dark:hover:to-purple-700',
  'dark:hover:border-purple-500', 'dark:hover:text-purple-400',
  'dark:group-hover:bg-purple-900', 'dark:group-hover:text-purple-400', 'group-hover:bg-purple-100', 'group-hover:text-purple-500',

  // Amber/Yellow theme classes
  'bg-amber-50', 'bg-amber-100', 'bg-amber-400', 'bg-amber-500', 'bg-amber-600', 'bg-amber-700', 'bg-amber-800', 'bg-amber-900',
  'text-amber-400', 'text-amber-500', 'text-amber-600', 'text-amber-700',
  'border-amber-400', 'border-amber-500', 'border-amber-600',
  'from-amber-400', 'from-amber-500', 'from-amber-600', 'to-amber-400', 'to-amber-500', 'to-amber-600', 'to-amber-700',
  'hover:bg-amber-500', 'hover:bg-amber-600', 'hover:bg-amber-700', 'hover:from-amber-600', 'hover:from-amber-700', 'hover:to-amber-700', 'hover:to-amber-800',
  'hover:border-amber-400', 'hover:border-amber-500', 'hover:text-amber-500', 'hover:text-amber-400',
  'dark:bg-amber-600', 'dark:bg-amber-700', 'dark:bg-amber-800', 'dark:bg-amber-900',
  'dark:text-amber-400', 'dark:text-amber-500',
  'dark:from-amber-400', 'dark:from-amber-500', 'dark:from-amber-600', 'dark:to-amber-400', 'dark:to-amber-500', 'dark:to-amber-600',
  'dark:hover:bg-amber-600', 'dark:hover:bg-amber-700', 'dark:hover:from-amber-700', 'dark:hover:to-amber-700',
  'dark:hover:border-amber-500', 'dark:hover:text-amber-400',
  'dark:group-hover:bg-amber-900', 'dark:group-hover:text-amber-400', 'group-hover:bg-amber-100', 'group-hover:text-amber-500',

  // Specific utility classes used in the file  
  'group', 'relative', 'absolute', 'inset-0', 'flex-1', 'min-w-0', 'truncate', 'font-mono', 'inline-flex', 'mr-1', 'mr-2',
  'transition-transform', 'transition-colors', 'duration-200', 'duration-300', 'group-hover:scale-110', 'group-hover:text-gray-700',
  'dark:group-hover:text-gray-300', 'backdrop-filter', 'backdrop-blur-sm', 'ring-opacity-50', 'dark:ring-opacity-50',
  'text-gray-400', 'text-gray-500', 'text-gray-600', 'text-gray-700', 'text-gray-900',
  'dark:text-gray-400', 'dark:text-gray-500', 'dark:text-gray-300', 'dark:text-white',
  'bg-gray-50', 'bg-gray-100', 'bg-gray-700', 'bg-gray-800', 'bg-gray-900',
  'dark:bg-gray-700', 'dark:bg-gray-800', 'dark:bg-gray-900',
  'hover:text-gray-100', 'hover:text-gray-900', 'dark:text-gray-200', 
  'border-gray-100', 'border-gray-200', 'border-gray-300', 'border-gray-600', 'border-gray-700',
  'dark:border-gray-600', 'dark:border-gray-700',
  'space-x-4', 'space-x-2', 'space-y-3', 'mb-4', 'mb-6', 'mb-8', 'mb-12', 'mt-1',
  'w-3', 'w-4', 'w-5', 'w-6', 'w-12', 'w-16', 'w-20', 'w-24',
  'h-2', 'h-3', 'h-4', 'h-5', 'h-6', 'h-12', 'h-16', 'h-20', 'h-24',
  'p-3', 'p-6', 'px-2', 'px-3', 'px-6', 'px-8', 'py-1', 'py-2', 'py-3', 'py-8', 'py-16',
  'ring-2', 'ring-white', 'dark:ring-gray-600', 'ring-green-500', 'ring-red-500', 'ring-offset-2',
  'border-2', 'border-dashed', 'overflow-hidden', 'cursor-pointer', 'cursor-not-allowed',
  'rounded-lg', 'rounded-xl', 'rounded-2xl', 'rounded-full',
  'shadow-lg', 'shadow-2xl', 'hover:shadow-xl', 'dark:hover:shadow-2xl',
  'text-xs', 'text-sm', 'text-lg', 'text-2xl', 'text-4xl',
  'font-medium', 'font-semibold', 'font-bold',
  'hover:scale-105', 'hover:-translate-y-1', 'transform', 'transition-all',
  'bg-clip-text', 'text-transparent', 'bg-gradient-to-r',
  'sm:px-6', 'lg:px-8', 'sm:grid-cols-2', 'lg:grid-cols-3', 'xl:grid-cols-4',
  'grid', 'grid-cols-1', 'gap-6', 'inline', 'fill', 'stroke',
]
,
    plugins: [
        require('@tailwindcss/typography'),
    ],
}
