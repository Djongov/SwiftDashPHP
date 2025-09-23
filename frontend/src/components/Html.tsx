import type { ReactNode, HTMLAttributes } from 'react'

interface BaseProps extends HTMLAttributes<HTMLElement> {
  children: ReactNode
  center?: boolean
  extraClasses?: string[]
}

interface LinkProps extends HTMLAttributes<HTMLAnchorElement> {
  href: string
  children: ReactNode
  theme?: string
}

interface ButtonProps extends HTMLAttributes<HTMLButtonElement> {
  children: ReactNode
  theme?: string
  size?: 'small' | 'medium' | 'large'
  variant?: 'primary' | 'secondary' | 'danger'
  disabled?: boolean
  type?: 'button' | 'submit' | 'reset'
}

// Heading components
export const H1 = ({ children, center = false, extraClasses = [], className, ...props }: BaseProps) => {
  const classes = [
    'mx-2 my-2 text-2xl md:text-3xl lg:text-4xl font-bold leading-none',
    'text-gray-900 dark:text-white',
    center && 'text-center',
    ...extraClasses,
    className
  ].filter(Boolean).join(' ')

  return <h1 className={classes} {...props}>{children}</h1>
}

export const H2 = ({ children, center = false, extraClasses = [], className, ...props }: BaseProps) => {
  const classes = [
    'mx-2 my-2 text-xl md:text-2xl lg:text-3xl font-bold leading-none',
    'text-gray-900 dark:text-white',
    center && 'text-center',
    ...extraClasses,
    className
  ].filter(Boolean).join(' ')

  return <h2 className={classes} {...props}>{children}</h2>
}

export const H3 = ({ children, center = false, extraClasses = [], className, ...props }: BaseProps) => {
  const classes = [
    'mx-2 my-2 text-lg md:text-xl font-bold',
    'text-gray-900 dark:text-white break-words',
    center && 'text-center',
    ...extraClasses,
    className
  ].filter(Boolean).join(' ')

  return <h3 className={classes} {...props}>{children}</h3>
}

export const H4 = ({ children, center = false, extraClasses = [], className, ...props }: BaseProps) => {
  const classes = [
    'mx-2 my-2 text-md font-bold',
    'text-gray-900 dark:text-white break-words',
    center && 'text-center',
    ...extraClasses,
    className
  ].filter(Boolean).join(' ')

  return <h4 className={classes} {...props}>{children}</h4>
}

export const H5 = ({ children, center = false, extraClasses = [], className, ...props }: BaseProps) => {
  const classes = [
    'mx-2 my-2 text-sm font-bold',
    'text-gray-900 dark:text-white break-words',
    center && 'text-center',
    ...extraClasses,
    className
  ].filter(Boolean).join(' ')

  return <h5 className={classes} {...props}>{children}</h5>
}

// Paragraph component
export const P = ({ children, center = false, extraClasses = [], className, ...props }: BaseProps) => {
  const classes = [
    'mx-2 my-2 text-gray-700 dark:text-gray-300',
    center && 'text-center',
    ...extraClasses,
    className
  ].filter(Boolean).join(' ')

  return <p className={classes} {...props}>{children}</p>
}

// Link component
export const A = ({ href, children, theme = 'blue', className, ...props }: LinkProps) => {
  const classes = [
    `text-${theme}-600 dark:text-${theme}-400`,
    `hover:text-${theme}-800 dark:hover:text-${theme}-300`,
    'underline transition-colors duration-200',
    className
  ].filter(Boolean).join(' ')

  return <a href={href} className={classes} {...props}>{children}</a>
}

// Button component
export const Button = ({ 
  children, 
  theme = 'blue', 
  size = 'medium',
  variant = 'primary',
  disabled = false,
  type = 'button',
  className,
  ...props 
}: ButtonProps) => {
  const sizeClasses = {
    small: 'px-3 py-1.5 text-sm',
    medium: 'px-4 py-2 text-base',
    large: 'px-6 py-3 text-lg'
  }

  const variantClasses = {
    primary: `bg-${theme}-600 hover:bg-${theme}-700 text-white`,
    secondary: `bg-gray-200 hover:bg-gray-300 text-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white`,
    danger: 'bg-red-600 hover:bg-red-700 text-white'
  }

  const classes = [
    'font-medium rounded-lg transition-colors duration-200',
    'focus:outline-none focus:ring-2 focus:ring-offset-2',
    `focus:ring-${theme}-500`,
    sizeClasses[size],
    variantClasses[variant],
    disabled && 'opacity-50 cursor-not-allowed',
    className
  ].filter(Boolean).join(' ')

  return (
    <button 
      type={type}
      disabled={disabled}
      className={classes}
      {...props}
    >
      {children}
    </button>
  )
}

// Small text component
export const Small = ({ children, center = false, extraClasses = [], className, ...props }: BaseProps) => {
  const classes = [
    'mx-2 my-1 text-sm text-gray-600 dark:text-gray-400',
    center && 'text-center',
    ...extraClasses,
    className
  ].filter(Boolean).join(' ')

  return <small className={classes} {...props}>{children}</small>
}

// Divider component
export const HorizontalLine = ({ className, ...props }: HTMLAttributes<HTMLHRElement>) => {
  const classes = [
    'my-4 border-0 border-t border-gray-300 dark:border-gray-600',
    className
  ].filter(Boolean).join(' ')

  return <hr className={classes} {...props} />
}

// Box component
interface BoxProps extends HTMLAttributes<HTMLDivElement> {
  children: ReactNode
}

export const DivBox = ({ children, className, ...props }: BoxProps) => {
  const classes = [
    'p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700',
    className
  ].filter(Boolean).join(' ')

  return <div className={classes} {...props}>{children}</div>
}

// Badge component
interface BadgeProps {
  text: string
  theme?: string
}

export const Badge = ({ text, theme = 'blue' }: BadgeProps) => (
  <span className={`inline-block py-px px-2 mb-4 text-xs leading-5 text-gray-900 bg-${theme}-200 font-medium uppercase rounded-full shadow-sm`}>
    {text}
  </span>
)

// Loading spinner
interface SpinnerProps {
  theme?: string
  size?: 'sm' | 'md' | 'lg'
}

export const Spinner = ({ theme = 'blue', size = 'md' }: SpinnerProps) => {
  const sizeClasses = {
    sm: 'w-4 h-4',
    md: 'w-8 h-8',
    lg: 'w-12 h-12'
  }

  return (
    <svg 
      className={`animate-spin ${sizeClasses[size]} text-gray-300 dark:text-white`} 
      viewBox="0 0 100 101" 
      fill="none"
    >
      <path 
        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" 
        fill="currentColor"
      />
      <path 
        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" 
        fill={`rgb(var(--color-${theme}-500))`}
      />
    </svg>
  )
}