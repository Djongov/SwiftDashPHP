import { useState, useEffect } from 'react'
import { H1, P, DivBox, HorizontalLine } from '../components/Html'
import { SuccessAlert, InfoAlert } from '../components/Alerts'
import { useAuth } from '../hooks/useAuth'
import apiService from '../services/api'

interface DashboardStats {
  users: number
  apiKeys: number
  recentActivity: Array<{
    action: string
    timestamp: string
    user: string
  }>
}

const Dashboard = () => {
  const { user, isAuthenticated } = useAuth()
  const [stats, setStats] = useState<DashboardStats | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (isAuthenticated) {
      loadDashboardData()
    }
  }, [isAuthenticated])

  const loadDashboardData = async () => {
    try {
      const response = await apiService.get('/api/dashboard/stats')
      if (response.success) {
        setStats(response.data as DashboardStats)
      }
    } catch (error) {
      console.error('Failed to load dashboard data:', error)
    } finally {
      setLoading(false)
    }
  }

  if (!isAuthenticated) {
    return (
      <div className="container mx-auto px-4 py-8">
        <InfoAlert>
          Please log in to access the dashboard.
        </InfoAlert>
      </div>
    )
  }

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="animate-pulse space-y-4">
          <div className="h-8 bg-gray-300 rounded w-1/3"></div>
          <div className="h-4 bg-gray-300 rounded w-1/2"></div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {[1, 2, 3].map((i) => (
              <div key={i} className="h-32 bg-gray-300 rounded"></div>
            ))}
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <H1 extraClasses={['gradient-text']}>
            Welcome to SwiftDashPHP
          </H1>
          <P>
            Hello {user?.name || user?.username}! Here's your dashboard overview.
          </P>
        </div>

        {/* Success Message */}
        <SuccessAlert>
          Successfully connected to the database and React frontend!
        </SuccessAlert>

        <HorizontalLine />

        {/* Features Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          {/* Authentication Card */}
          <DivBox className="hover:shadow-lg transition-shadow duration-300">
            <div className="text-center p-4">
              <div className="w-12 h-12 mx-auto mb-4 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Authentication
              </h3>
              <p className="text-gray-600 dark:text-gray-400 text-sm">
                Support for local, Google, MS (live and Azure) authentication
              </p>
            </div>
          </DivBox>

          {/* Database Card */}
          <DivBox className="hover:shadow-lg transition-shadow duration-300">
            <div className="text-center p-4">
              <div className="w-12 h-12 mx-auto mb-4 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                </svg>
              </div>
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Database Support
              </h3>
              <p className="text-gray-600 dark:text-gray-400 text-sm">
                MySQL/MariaDB/SQLite/PostgreSQL support
              </p>
            </div>
          </DivBox>

          {/* React Integration Card */}
          <DivBox className="hover:shadow-lg transition-shadow duration-300">
            <div className="text-center p-4">
              <div className="w-12 h-12 mx-auto mb-4 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 10.11c1.03 0 1.87.84 1.87 1.89 0 1-.84 1.85-1.87 1.85S10.13 13 10.13 12c0-1.05.84-1.89 1.87-1.89M7.37 20c.63.38 2.01-.2 3.6-1.7-.52-.59-1.03-1.23-1.51-1.9a22.7 22.7 0 0 1-2.4-.36c-.51 2.14-.32 3.61.31 3.96m9.25 0c.63-.35.82-1.82.31-3.96-.54.1-1.05.19-1.6.23-.49.67-1 1.31-1.51 1.9 1.59 1.5 2.97 2.08 3.8 1.83m2.85-13.73c-.62-.35-.97 1.71-1.04 4.19.54.08 1.05.17 1.6.27.39-2.4.18-3.82-.56-4.46M4.56 6.27c-.74.64-.97 2.06-.58 4.46.54-.1 1.05-.19 1.6-.27-.07-2.48-.42-4.54-1.02-4.19M12 1c5.52 0 10 4.48 10 10s-4.48 10-10 10S2 16.52 2 12 6.48 2 12 2" />
                </svg>
              </div>
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                React + Vite
              </h3>
              <p className="text-gray-600 dark:text-gray-400 text-sm">
                Modern React frontend with Vite bundling
              </p>
            </div>
          </DivBox>

          {/* DataGrid Card */}
          <DivBox className="hover:shadow-lg transition-shadow duration-300">
            <div className="text-center p-4">
              <div className="w-12 h-12 mx-auto mb-4 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                DataGrid
              </h3>
              <p className="text-gray-600 dark:text-gray-400 text-sm">
                Powerful data tables with sorting, filtering, pagination
              </p>
            </div>
          </DivBox>

          {/* Charts Card */}
          <DivBox className="hover:shadow-lg transition-shadow duration-300">
            <div className="text-center p-4">
              <div className="w-12 h-12 mx-auto mb-4 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Charts
              </h3>
              <p className="text-gray-600 dark:text-gray-400 text-sm">
                Chart.js and QuickChart.io integration
              </p>
            </div>
          </DivBox>

          {/* API Card */}
          <DivBox className="hover:shadow-lg transition-shadow duration-300">
            <div className="text-center p-4">
              <div className="w-12 h-12 mx-auto mb-4 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                </svg>
              </div>
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                API Ready
              </h3>
              <p className="text-gray-600 dark:text-gray-400 text-sm">
                Built-in API endpoints with JWT and API keys support
              </p>
            </div>
          </DivBox>
        </div>

        {/* Quick Stats */}
        {stats && (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <DivBox>
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                System Overview
              </h3>
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Active Users:</span>
                  <span className="font-medium text-gray-900 dark:text-white">{stats.users}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">API Keys:</span>
                  <span className="font-medium text-gray-900 dark:text-white">{stats.apiKeys}</span>
                </div>
              </div>
            </DivBox>

            <DivBox>
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Recent Activity
              </h3>
              <div className="space-y-3">
                {stats.recentActivity.map((activity, index) => (
                  <div key={index} className="flex justify-between text-sm">
                    <span className="text-gray-600 dark:text-gray-400">
                      {activity.action} by {activity.user}
                    </span>
                    <span className="text-gray-500 dark:text-gray-500">
                      {new Date(activity.timestamp).toLocaleDateString()}
                    </span>
                  </div>
                ))}
              </div>
            </DivBox>
          </div>
        )}
      </div>
    </div>
  )
}

export default Dashboard