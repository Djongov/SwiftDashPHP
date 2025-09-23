import { useState } from 'react'
import { H1, P, DivBox } from '../components/Html'
import { Form, type FormField } from '../components/Form'
import { SuccessAlert, DangerAlert } from '../components/Alerts'
import { useAuth } from '../hooks/useAuth'

const LoginPage = () => {
  const { login, isLoading } = useAuth()
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null)

  const loginFields: FormField[] = [
    {
      name: 'username',
      label: 'Username or Email',
      type: 'text',
      placeholder: 'Enter your username or email',
      required: true
    },
    {
      name: 'password', 
      label: 'Password',
      type: 'password',
      placeholder: 'Enter your password',
      required: true
    }
  ]

  const handleLogin = async (data: Record<string, any>) => {
    setMessage(null)
    
    const result = await login(data.username, data.password)
    
    if (result.success) {
      setMessage({ type: 'success', text: 'Login successful! Redirecting...' })
      // Redirect handled by auth context
      setTimeout(() => {
        window.location.href = '/'
      }, 1500)
    } else {
      setMessage({ type: 'error', text: result.error || 'Login failed' })
    }
  }

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8">
        <div>
          <H1 center>Sign in to SwiftDashPHP</H1>
          <P center>Enter your credentials to access your account</P>
        </div>

        {message && (
          message.type === 'success' ? (
            <SuccessAlert>{message.text}</SuccessAlert>
          ) : (
            <DangerAlert>{message.text}</DangerAlert>
          )
        )}

        <DivBox>
          <Form
            config={{
              fields: loginFields,
              action: '/api/auth/login',
              theme: 'blue',
              onSubmit: handleLogin,
              submitButton: {
                text: 'Sign In',
                size: 'medium'
              }
            }}
          />

          <div className="mt-6 space-y-4">
            <div className="text-center">
              <P>Or sign in with</P>
            </div>
            
            {/* Google Login Button */}
            <button className="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
              <svg className="w-5 h-5 mr-2" viewBox="0 0 24 24">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              Continue with Google
            </button>

            {/* Microsoft Login Button */}
            <button className="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
              <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.4 24H0V12.6h11.4V24zM24 24H12.6V12.6H24V24zM11.4 11.4H0V0h11.4v11.4zM24 11.4H12.6V0H24v11.4z"/>
              </svg>
              Continue with Microsoft
            </button>
          </div>

          <div className="mt-6 text-center">
            <P className="text-sm text-gray-600 dark:text-gray-400">
              Don't have an account? Contact your administrator
            </P>
          </div>
        </DivBox>
      </div>
    </div>
  )
}

export default LoginPage