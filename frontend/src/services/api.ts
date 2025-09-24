import axios from 'axios'
import type { AxiosInstance, AxiosRequestConfig } from 'axios'

export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  error?: string
  message?: string
}

class ApiService {
  private api: AxiosInstance

  constructor() {
    const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || (import.meta.env.DEV ? 'https://swiftdashphp.gamerz-bg.com' : '')
    
    this.api = axios.create({
      baseURL: API_BASE_URL,
      timeout: 10000,
      withCredentials: true, // Important for PHP sessions
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    // Request interceptor to add CSRF token
    this.api.interceptors.request.use((config) => {
      // Get CSRF token from meta tag or localStorage
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                        localStorage.getItem('csrf_token')
      
      if (csrfToken) {
        config.headers['X-CSRF-Token'] = csrfToken
      }

      return config
    })

    // Response interceptor to handle common errors
    this.api.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          // Only redirect on 401 if it's NOT an auth-related endpoint
          const isAuthEndpoint = error.config?.url?.includes('/auth/login') || 
                                 error.config?.url?.includes('/auth/check')
          
          console.log('🔍 401 Response Details:', {
            url: error.config?.url,
            isAuthEndpoint,
            status: error.response?.status,
            statusText: error.response?.statusText
          })
          
          if (!isAuthEndpoint) {
            // Handle authentication errors for authenticated endpoints
            console.log('🔒 401 Unauthorized - Redirecting to login')
            window.location.href = '/login'
          } else {
            // For auth endpoints, let the calling code handle the 401
            console.log('🔍 401 on auth endpoint - NOT redirecting, letting caller handle it')
          }
        } else if (error.response?.status === 403) {
          // Handle CSRF token errors
          this.refreshCsrfToken()
        }
        return Promise.reject(error)
      }
    )
  }

  private async refreshCsrfToken(): Promise<void> {
    try {
      const response = await this.api.get('/api/csrf-token')
      const token = response.data.token
      localStorage.setItem('csrf_token', token)
    } catch (error) {
      console.error('Failed to refresh CSRF token:', error)
    }
  }

  // Generic GET request
  async get<T>(url: string, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    try {
      const response = await this.api.get(url, config)
      return response.data
    } catch (error: any) {
      return {
        success: false,
        error: error.response?.data?.error || error.message
      }
    }
  }

  // Generic POST request
  async post<T>(url: string, data?: any, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    try {
      const response = await this.api.post(url, data, config)
      return response.data
    } catch (error: any) {
      return {
        success: false,
        error: error.response?.data?.error || error.message
      }
    }
  }

  // Generic PUT request
  async put<T>(url: string, data?: any, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    try {
      const response = await this.api.put(url, data, config)
      return response.data
    } catch (error: any) {
      return {
        success: false,
        error: error.response?.data?.error || error.message
      }
    }
  }

  // Generic DELETE request
  async delete<T>(url: string, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    try {
      const response = await this.api.delete(url, config)
      return response.data
    } catch (error: any) {
      return {
        success: false,
        error: error.response?.data?.error || error.message
      }
    }
  }

  // Upload file
  async uploadFile<T>(url: string, file: File, onUploadProgress?: (progress: number) => void): Promise<ApiResponse<T>> {
    const formData = new FormData()
    formData.append('file', file)

    try {
      const response = await this.api.post(url, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          if (onUploadProgress && progressEvent.total) {
            const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
            onUploadProgress(percentCompleted)
          }
        }
      })
      return response.data
    } catch (error: any) {
      return {
        success: false,
        error: error.response?.data?.error || error.message
      }
    }
  }
}

export default new ApiService()