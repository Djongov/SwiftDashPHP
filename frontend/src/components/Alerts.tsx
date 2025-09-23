import type { ReactNode } from 'react'

interface AlertProps {
  children: ReactNode
  onClose?: () => void
}

const AlertBase = ({ 
  children, 
  onClose, 
  bgColor, 
  textColor, 
  borderColor, 
  iconPath 
}: AlertProps & {
  bgColor: string
  textColor: string
  borderColor: string
  iconPath: string
}) => (
  <div className={`flex items-center p-4 mb-4 text-sm ${textColor} border ${borderColor} rounded-lg ${bgColor}`} role="alert">
    <svg className="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
      <path d={iconPath}/>
    </svg>
    <div className="flex-1">
      {children}
    </div>
    {onClose && (
      <button
        type="button"
        className="ms-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 p-1.5 inline-flex items-center justify-center h-8 w-8"
        onClick={onClose}
        aria-label="Close"
      >
        <svg className="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
        </svg>
      </button>
    )}
  </div>
)

export const InfoAlert = ({ children, onClose }: AlertProps) => (
  <AlertBase
    bgColor="bg-blue-50 dark:bg-gray-800"
    textColor="text-blue-800 dark:text-blue-400"
    borderColor="border-blue-300 dark:border-blue-800"
    iconPath="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"
    onClose={onClose}
  >
    {children}
  </AlertBase>
)

export const SuccessAlert = ({ children, onClose }: AlertProps) => (
  <AlertBase
    bgColor="bg-green-50 dark:bg-gray-800"
    textColor="text-green-800 dark:text-green-400"
    borderColor="border-green-300 dark:border-green-800"
    iconPath="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"
    onClose={onClose}
  >
    {children}
  </AlertBase>
)

export const DangerAlert = ({ children, onClose }: AlertProps) => (
  <AlertBase
    bgColor="bg-red-50 dark:bg-gray-800"
    textColor="text-red-800 dark:text-red-400"
    borderColor="border-red-300 dark:border-red-800"
    iconPath="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"
    onClose={onClose}
  >
    {children}
  </AlertBase>
)

export const WarningAlert = ({ children, onClose }: AlertProps) => (
  <AlertBase
    bgColor="bg-yellow-50 dark:bg-gray-800"
    textColor="text-yellow-800 dark:text-yellow-300"
    borderColor="border-yellow-300 dark:border-yellow-800"
    iconPath="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 5a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0V6a1 1 0 0 1 1-1Zm0 9a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"
    onClose={onClose}
  >
    {children}
  </AlertBase>
)

// Convenience exports matching PHP component names
export const Alerts = {
  info: InfoAlert,
  success: SuccessAlert,
  danger: DangerAlert,
  warning: WarningAlert
}