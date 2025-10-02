/* eslint-disable react-refresh/only-export-components */
import { useState, useEffect, useContext, createContext, useMemo, useCallback, useRef } from 'react'
import type { ReactNode } from 'react'

export interface User {
  username: string
  name: string
  picture?: string
  isAdmin: boolean
}

interface ApiResponseDebug {
  status?: number
  statusText?: string
  url?: string
  headers?: Headers
  data?: unknown
}

export interface LoginResult {
  success: boolean
  error?: string
  apiResponse?: ApiResponseDebug | null
  networkError?: Error | string | null
}

export interface AuthContextType {
  user: User | null
  isAuthenticated: boolean
  isLoading: boolean
  login: (username: string, password: string) => Promise<LoginResult>
  logout: () => Promise<void>
  checkAuth: () => Promise<void>
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [user, setUser] = useState<User | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const isCheckingAuth = useRef(false)
  const hasInitiallyChecked = useRef(false)

  const isAuthenticated = !!user

  // Utility function to get CSRF token
  const getCsrfToken = async (): Promise<string> => {
    const response = await fetch('https://swiftdashphp.gamerz-bg.com/auth/csrf', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include'
    })
    
    const data = await response.json()
    if (!data.success) {
      throw new Error('Failed to get CSRF token')
    }
    
    return data.csrf_token
  }

  const checkAuth = useCallback(async () => {
    if (isCheckingAuth.current) {
      console.log('🔍 Auth check already in progress, skipping...')
      return
    }

    isCheckingAuth.current = true
    console.log('🔍 Checking authentication...')

    try {
      // First check localStorage for cached user data
      const cachedUserData = localStorage.getItem('user_data')
      const authToken = localStorage.getItem('auth_token')
      const sessionId = localStorage.getItem('session_id')
      
      if (cachedUserData && authToken) {
        console.log('📱 Found cached user data and token, setting user...')
        setUser(JSON.parse(cachedUserData))
        console.log('✅ User authenticated from cache')
        return
      } else if (cachedUserData && !authToken) {
        console.log('⚠️ Found cached user data but no token, clearing cache...')
        localStorage.removeItem('user_data')
        localStorage.removeItem('auth_token')
        localStorage.removeItem('session_id')
      }

      // Fallback to server check with auth headers
      const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
      
      if (authToken) {
        headers['Authorization'] = `Bearer ${authToken}`
      }
      if (sessionId) {
        headers['X-Session-ID'] = sessionId
      }

      const response = await fetch('https://swiftdashphp.gamerz-bg.com/auth/check', {
        method: 'GET',
        headers,
        credentials: 'include'
      })
      
      const data = await response.json()
      console.log('📡 Auth check response:', data)
      console.log('🔍 Auth check - Expected: data.success && data.authenticated')
      console.log('🔍 Auth check - Got:', { success: data.success, authenticated: data.authenticated })
      
      if (data.success && data.authenticated) {
        setUser(data.data)
        localStorage.setItem('user_data', JSON.stringify(data.data))
        console.log('✅ User is authenticated:', data.data)
      } else {
        console.log('❌ Auth check failed - clearing localStorage and setting user to null')
        console.log('❌ Reason: success=' + data.success + ', authenticated=' + data.authenticated)
        setUser(null)
        // Clear localStorage if auth failed
        localStorage.removeItem('user_data')
        localStorage.removeItem('auth_token')
        localStorage.removeItem('session_id')
        console.log('❌ User is not authenticated')
      }
    } catch (error) {
      console.error('❌ Auth check failed:', error)
      setUser(null)
      // Clear localStorage on error
      localStorage.removeItem('user_data')
      localStorage.removeItem('auth_token')
      localStorage.removeItem('session_id')
    } finally {
      isCheckingAuth.current = false
      setIsLoading(false) // Always set loading to false when auth check completes
    }
  }, [])

  const login = async (username: string, password: string): Promise<LoginResult> => {
    setIsLoading(true)
    
    try {
      console.log('🔐 Attempting login with:', { username })
      console.log('🔐 Login API call starting...')
      
      // Get CSRF token using utility function
      console.log('🔒 Fetching CSRF token...')
      const csrfToken = await getCsrfToken()
      console.log('🔒 CSRF token received:', csrfToken)
      
      // Make login request with CSRF token
      const response = await fetch('https://swiftdashphp.gamerz-bg.com/auth/local', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify({ 
          username, 
          password,
          csrf_token: csrfToken
        })
      })
      
      const data = await response.json()
      console.log('📡 Raw API response:', data)
      
      // Handle new API response format with nested data structure
      if (data.result === "success" && data.data?.success) {
        // Store user data in state and localStorage
        const userData = data.data.data || { username }
        setUser(userData)
        
        // Store auth token from the nested response
        if (data.data.token) {
          localStorage.setItem('auth_token', data.data.token)
          console.log('💾 Stored auth token in localStorage')
        }
        if (data.data.session_id) {
          localStorage.setItem('session_id', data.data.session_id)
          console.log('💾 Stored session ID in localStorage')
        }
        localStorage.setItem('user_data', JSON.stringify(userData))
        
        console.log('✅ Login successful, user data set:', userData)
        
        return {
          success: true,
          apiResponse: {
            status: response.status,
            statusText: response.statusText,
            url: response.url,
            data: data
          }
        }
      } else if (data.result === "error" && response.status > 400) {
        // Handle error responses with status codes > 400
        const errorMessage = data.data?.message || data.message || 'Login failed'
        console.log('❌ Login failed with error response:', errorMessage)
        return {
          success: false,
          error: errorMessage,
          apiResponse: {
            status: response.status,
            statusText: response.statusText,
            url: response.url,
            data: data
          }
        }
      } else {
        // Handle other failure cases
        const errorMessage = data.data?.message || data.message || data.error || 'Login failed'
        console.log('❌ Login failed:', errorMessage)
        return {
          success: false,
          error: errorMessage,
          apiResponse: {
            status: response.status,
            statusText: response.statusText,
            url: response.url,
            data: data
          }
        }
      }
    } catch (error) {
      console.error('❌ Login network error:', error)
      
      // Enhanced error handling for axios errors
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response: { data: unknown; status: number; statusText: string; config: { url?: string } } }
        console.log('📡 Axios error response data:', axiosError.response.data)
        
        return {
          success: false,
          error: (axiosError.response.data as { error?: string })?.error || 'Authentication failed',
          apiResponse: axiosError.response.data,
          networkError: error
        }
      }
      
      return {
        success: false,
        error: 'Network error occurred',
        apiResponse: null,
        networkError: error
      }
    } finally {
      setIsLoading(false)
    }
  }

  const logout = useCallback(async () => {
    try {
      console.log('🚪 Logging out...')
      
      // Send logout request with auth headers
      const authToken = localStorage.getItem('auth_token')
      const sessionId = localStorage.getItem('session_id')
      
      const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
      
      if (authToken) {
        headers['Authorization'] = `Bearer ${authToken}`
      }
      if (sessionId) {
        headers['X-Session-ID'] = sessionId
      }

      await fetch('https://swiftdashphp.gamerz-bg.com/auth/logout', {
        method: 'POST',
        headers,
        credentials: 'include'
      })
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      setUser(null)
      // Clear all auth data from localStorage
      localStorage.removeItem('user_data')
      localStorage.removeItem('auth_token') 
      localStorage.removeItem('session_id')
      console.log('✅ Logout successful, redirecting to login...')
      // Use window.location.href to ensure a full page reload and clear any cached state
      window.location.href = '/login'
    }
  }, [])

  useEffect(() => {
    // Only run the initial auth check once
    if (!hasInitiallyChecked.current) {
      hasInitiallyChecked.current = true
      checkAuth()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []) // Empty dependency array - only run once on mount

  const contextValue = useMemo((): AuthContextType => ({
    user,
    isAuthenticated,
    isLoading,
    login,
    logout,
    checkAuth
  }), [user, isAuthenticated, isLoading, login, logout, checkAuth])

  return (
    <AuthContext.Provider value={contextValue}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext)
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}