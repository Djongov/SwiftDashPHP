import { useState, useEffect, useContext, createContext, useMemo, useCallback } from 'react'
import type { ReactNode } from 'react'
import apiService from '../services/api'

export interface User {
  username: string
  name: string
  picture?: string
  isAdmin: boolean
}

export interface AuthContextType {
  user: User | null
  isAuthenticated: boolean
  isLoading: boolean
  login: (username: string, password: string) => Promise<{ success: boolean; error?: string }>
  logout: () => Promise<void>
  checkAuth: () => Promise<void>
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [user, setUser] = useState<User | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  const isAuthenticated = !!user

  const checkAuth = useCallback(async () => {
    setIsLoading(true)
    try {
      const response = await apiService.get('/auth/check')
      if (response.success && response.data) {
        setUser(response.data as User)
      } else {
        setUser(null)
      }
    } catch (error) {
      setUser(null)
    } finally {
      setIsLoading(false)
    }
  }, [])

  const login = useCallback(async (username: string, password: string) => {
    try {
      const response = await apiService.post('/auth/login', {
        username,
        password
      })
      
      if (response.success && response.data) {
        setUser(response.data as User)
        return { success: true }
      } else {
        return { 
          success: false, 
          error: response.error || 'Login failed' 
        }
      }
    } catch (error) {
      return { 
        success: false, 
        error: 'Network error occurred' 
      }
    }
  }, [])

  const logout = useCallback(async () => {
    try {
      await apiService.post('/auth/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      setUser(null)
      // Redirect to login or home page
      window.location.href = '/login'
    }
  }, [])

  useEffect(() => {
    checkAuth()
  }, [checkAuth])

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