import { H1, P, DivBox, Button } from './Html'

interface WelcomeScreenProps {
  onLoginClick: () => void
}

export const WelcomeScreen = ({ onLoginClick }: WelcomeScreenProps) => {
  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-4">
      <DivBox className="max-w-md w-full text-center">
        <div className="mb-8">
          {/* Logo */}
          <div className="mx-auto w-20 h-20 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mb-6">
            <svg className="w-10 h-10 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <rect x="3" y="3" width="7" height="9"></rect>
              <rect x="14" y="3" width="7" height="5"></rect>
              <rect x="14" y="12" width="7" height="9"></rect>
              <rect x="3" y="16" width="7" height="5"></rect>
            </svg>
          </div>
          
          <H1 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            Welcome to SwiftDash
          </H1>
          
          <P className="text-gray-600 dark:text-gray-400 mb-8">
            A powerful PHP dashboard framework with modern React frontend integration. 
            Please sign in to access your dashboard and manage your applications.
          </P>
        </div>

        <div className="space-y-4">
          <Button
            onClick={onLoginClick}
            className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg transition-colors"
          >
            Sign In to Dashboard
          </Button>
          
          <div className="text-sm text-gray-500 dark:text-gray-400">
            <P>Secure authentication • Session management • CSRF protection</P>
          </div>
        </div>

        <div className="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
          <div className="flex items-center justify-center space-x-4 text-sm text-gray-400">
            <span>SwiftDashPHP Framework</span>
            <span>•</span>
            <span>Powered by React</span>
          </div>
        </div>
      </DivBox>
    </div>
  )
}

export default WelcomeScreen