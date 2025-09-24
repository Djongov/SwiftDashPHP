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

  const handleLogin = async (data: Record<string, string>) => {
    setMessage(null)
    
    console.log('🔐 Attempting login with:', { username: data.username })
    
    const result = await login(data.username, data.password)
    
    console.log('📡 Full login API response:', result)
    
    if (result.success) {
      setMessage({ type: 'success', text: 'Login successful! Redirecting to dashboard...' })
      // Give user feedback, then redirect to dashboard
      setTimeout(() => {
        window.location.href = '/dashboard'
      }, 1500)
    } else {
      // Enhanced error handling with detailed HTTP response info
      console.error('❌ Login failed - Full Details:', {
        error: result.error,
        networkError: result.networkError,
        apiResponse: result.apiResponse,
        fullResult: result
      })

      // Extract detailed response information from the actual error response
      const networkErrorResponse = (result.networkError && typeof result.networkError === 'object' && 'response' in result.networkError) 
        ? (result.networkError as { response: unknown }).response 
        : null
      const actualResponse = networkErrorResponse || result.apiResponse
      
      // Type guard for response objects
      const typedResponse = actualResponse as { status?: number, statusText?: string, config?: { url?: string }, url?: string, data?: unknown } | null
      
      const httpStatus = typedResponse?.status || 'Unknown'
      const statusText = typedResponse?.statusText || 'Unknown' 
      const responseUrl = typedResponse?.config?.url || typedResponse?.url || 'Unknown'
      
      // Create comprehensive error message
      const errorDetails = result.error || 'Login failed'
      const debugInfo = {
        httpStatus: `${httpStatus} ${statusText}`,
        url: responseUrl,
        networkError: result.networkError || 'None',
        timestamp: new Date().toISOString(),
        rawResponse: result.apiResponse || 'No response data'
      }
      
      let networkErrorStr = 'None'
      if (result.networkError) {
        networkErrorStr = typeof result.networkError === 'string' 
          ? result.networkError 
          : result.networkError.toString()
      }

      // Extract server debug info if available
      const responseData = typedResponse?.data || (result.apiResponse as { data?: unknown })?.data
      const serverDebugInfo = (responseData as { debug?: unknown })?.debug
      
      const fullError = `${errorDetails}

🔍 HTTP Status: ${httpStatus} ${statusText}
🌐 Request URL: ${responseUrl}
⚠️ Network Error: ${networkErrorStr}
⏰ Timestamp: ${debugInfo.timestamp}

${serverDebugInfo ? `🐛 Server Debug Info:
${JSON.stringify(serverDebugInfo, null, 2)}

` : ''}📋 Full Debug Data:
${JSON.stringify(debugInfo, null, 2)}

🔧 Raw API Response:
${JSON.stringify(result.apiResponse, null, 2)}

📡 Complete Server Response:
${JSON.stringify(typedResponse?.data || (result.apiResponse as { data?: unknown })?.data, null, 2)}`
      
      setMessage({ 
        type: 'error', 
        text: fullError
      })

      // DO NOT redirect on error - keep user on login page to see the error
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
            <div className="space-y-3">
              <DangerAlert>{message.text}</DangerAlert>
              {/* API Debug Information */}
              <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <details className="cursor-pointer">
                  <summary className="font-medium text-red-800 dark:text-red-200 mb-2">
                    🔍 API Debug Information (Click to expand)
                  </summary>
                  <pre className="text-xs text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/40 p-3 rounded overflow-auto max-h-40">
                    {message.text}
                  </pre>
                </details>
              </div>
            </div>
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