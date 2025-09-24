/* eslint-disable react-refresh/only-export-components */
import { useState, useEffect, useContext, createContext, useMemo, useCallback, useRef } from 'react'
import type { ReactNode } from 'react'
import apiService from '../services/api'

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

  const checkAuth = useCallback(async () => {
    // Prevent multiple simultaneous auth checks
    if (isCheckingAuth.current) {
      console.log('🔄 Auth check already in progress, skipping...')
      return
    }

    isCheckingAuth.current = true
    setIsLoading(true)
    
    try {
      console.log('🔍 Checking authentication...')
      const response = await apiService.get('/auth/check')
      if (response.success && response.data) {
        setUser(response.data as User)
        console.log('✅ User authenticated:', response.data)
      } else {
        setUser(null)
        console.log('❌ User not authenticated:', response.error)
      }
    } catch (error) {
      console.error('🚨 Auth check error:', error)
      setUser(null)
    } finally {
      setIsLoading(false)
      isCheckingAuth.current = false
    }
  }, [])

  const login = useCallback(async (username: string, password: string) => {
    try {
      console.log('🔐 Login API call starting...')
      const response = await apiService.post('/auth/login', {
        username,
        password
      })
      
      console.log('📡 Raw API response:', response)
      
      if (response.success && response.data) {
        setUser(response.data as User)
        return { 
          success: true,
          apiResponse: response as ApiResponseDebug
        }
      } else {
        return { 
          success: false, 
          error: response.error || 'Login failed',
          apiResponse: response as ApiResponseDebug
        }
      }
    } catch (error) {
      console.error('Login error:', error)
      console.log('🔍 Full error object:', error)
      
      // Check if it's an axios error with response data
      const isAxiosError = error && typeof error === 'object' && 'response' in error
      
      if (isAxiosError) {
        const axiosError = error as { response: { status: number, statusText: string, data: unknown, config: { url?: string }, headers?: unknown } }
        const axiosResponse = axiosError.response
        
        console.log('📡 Detailed axios response:', {
          status: axiosResponse.status,
          statusText: axiosResponse.statusText,
          url: axiosResponse.config?.url,
          data: axiosResponse.data,
          fullResponse: axiosResponse
        })
        
        // This is a server response (like 401, 400, etc.)
        return { 
          success: false, 
          error: (axiosResponse.data as { error?: string })?.error || axiosResponse.statusText || 'Server error',
          apiResponse: {
            status: axiosResponse.status,
            statusText: axiosResponse.statusText,
            url: axiosResponse.config?.url,
            data: axiosResponse.data
          },
          networkError: error instanceof Error ? error.message : 'Unknown error'
        }
      } else {
        // This is a network error (no response from server)
        console.log('🚨 Network error (no response):', error)
        return { 
          success: false, 
          error: 'Network error occurred',
          apiResponse: null,
          networkError: error instanceof Error ? error : String(error)
        }
      }
    }
  }, [])

  const logout = useCallback(async () => {
    try {
      console.log('🚪 Logging out...')
      await apiService.post('/auth/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      setUser(null)
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