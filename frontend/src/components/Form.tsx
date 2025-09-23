import { useState } from 'react'
import type { ReactNode, FormEvent } from 'react'
import { Button } from './Html'
import apiService from '../services/api'

export interface FormField {
  name: string
  label?: string
  type: 'text' | 'email' | 'password' | 'number' | 'tel' | 'url' | 'textarea' | 'select' | 'checkbox' | 'radio' | 'file' | 'hidden'
  placeholder?: string
  required?: boolean
  disabled?: boolean
  readonly?: boolean
  value?: string | number
  description?: string
  options?: Array<{ value: string; label: string }>
  pattern?: string
  min?: number
  max?: number
  step?: number
  multiple?: boolean
  accept?: string
}

export interface FormConfig {
  fields: FormField[]
  action: string
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE'
  theme?: string
  onSubmit?: (data: Record<string, any>, event: FormEvent) => void | Promise<void>
  onSuccess?: (response: any) => void
  onError?: (error: string) => void
  submitButton?: {
    text?: string
    size?: 'small' | 'medium' | 'large'
    disabled?: boolean
  }
  className?: string
}

interface FormProps {
  config: FormConfig
  children?: ReactNode
}

export const Form = ({ config, children }: FormProps) => {
  const [formData, setFormData] = useState<Record<string, any>>({})
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [errors, setErrors] = useState<Record<string, string>>({})

  const handleInputChange = (name: string, value: any) => {
    setFormData(prev => ({ ...prev, [name]: value }))
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {}

    config.fields.forEach(field => {
      if (field.required && (!formData[field.name] || formData[field.name] === '')) {
        newErrors[field.name] = `${field.label || field.name} is required`
      }
    })

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    
    if (!validateForm()) {
      return
    }

    setIsSubmitting(true)

    try {
      if (config.onSubmit) {
        await config.onSubmit(formData, e)
      } else {
        // Default API submission
        const method = config.method?.toLowerCase() || 'post'
        let response

        switch (method) {
          case 'get':
            response = await apiService.get(config.action, { params: formData })
            break
          case 'put':
            response = await apiService.put(config.action, formData)
            break
          case 'delete':
            response = await apiService.delete(config.action)
            break
          default:
            response = await apiService.post(config.action, formData)
        }

        if (response.success) {
          config.onSuccess?.(response.data)
        } else {
          config.onError?.(response.error || 'Submission failed')
        }
      }
    } catch (error) {
      config.onError?.('Network error occurred')
    } finally {
      setIsSubmitting(false)
    }
  }

  const renderField = (field: FormField) => {
    const baseClasses = "w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
    const errorClasses = errors[field.name] ? "border-red-500 focus:ring-red-500 focus:border-red-500" : ""
    const inputClasses = `${baseClasses} ${errorClasses}`

    switch (field.type) {
      case 'textarea':
        return (
          <textarea
            name={field.name}
            id={field.name}
            placeholder={field.placeholder}
            required={field.required}
            disabled={field.disabled}
            readOnly={field.readonly}
            value={formData[field.name] || ''}
            onChange={(e) => handleInputChange(field.name, e.target.value)}
            className={inputClasses}
            rows={4}
          />
        )

      case 'select':
        return (
          <select
            name={field.name}
            id={field.name}
            required={field.required}
            disabled={field.disabled}
            value={formData[field.name] || ''}
            onChange={(e) => handleInputChange(field.name, e.target.value)}
            className={inputClasses}
          >
            <option value="">Select an option</option>
            {field.options?.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        )

      case 'checkbox':
        return (
          <input
            type="checkbox"
            name={field.name}
            id={field.name}
            disabled={field.disabled}
            checked={formData[field.name] || false}
            onChange={(e) => handleInputChange(field.name, e.target.checked)}
            className="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
          />
        )

      case 'file':
        return (
          <input
            type="file"
            name={field.name}
            id={field.name}
            required={field.required}
            disabled={field.disabled}
            multiple={field.multiple}
            accept={field.accept}
            onChange={(e) => handleInputChange(field.name, e.target.files)}
            className={inputClasses}
          />
        )

      case 'hidden':
        return (
          <input
            type="hidden"
            name={field.name}
            value={field.value || formData[field.name] || ''}
          />
        )

      default:
        return (
          <input
            type={field.type}
            name={field.name}
            id={field.name}
            placeholder={field.placeholder}
            required={field.required}
            disabled={field.disabled}
            readOnly={field.readonly}
            value={formData[field.name] || ''}
            pattern={field.pattern}
            min={field.min}
            max={field.max}
            step={field.step}
            onChange={(e) => handleInputChange(field.name, e.target.value)}
            className={inputClasses}
          />
        )
    }
  }

  return (
    <form onSubmit={handleSubmit} className={`space-y-4 ${config.className || ''}`}>
      {config.fields.map((field) => (
        <div key={field.name} className={field.type === 'hidden' ? 'hidden' : 'space-y-2'}>
          {field.label && field.type !== 'hidden' && field.type !== 'checkbox' && (
            <label htmlFor={field.name} className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              {field.label}
              {field.required && <span className="text-red-500 ml-1">*</span>}
            </label>
          )}
          
          {field.type === 'checkbox' ? (
            <div className="flex items-center space-x-2">
              {renderField(field)}
              {field.label && (
                <label htmlFor={field.name} className="text-sm font-medium text-gray-700 dark:text-gray-300">
                  {field.label}
                  {field.required && <span className="text-red-500 ml-1">*</span>}
                </label>
              )}
            </div>
          ) : (
            renderField(field)
          )}

          {field.description && (
            <p className="text-xs text-gray-500 dark:text-gray-400">{field.description}</p>
          )}

          {errors[field.name] && (
            <p className="text-xs text-red-500">{errors[field.name]}</p>
          )}
        </div>
      ))}

      {children}

      {config.submitButton !== null && (
        <div className="pt-4">
          <Button
            type="submit"
            disabled={isSubmitting || config.submitButton?.disabled}
            size={config.submitButton?.size || 'medium'}
            theme={config.theme || 'blue'}
          >
            {isSubmitting ? 'Submitting...' : (config.submitButton?.text || 'Submit')}
          </Button>
        </div>
      )}
    </form>
  )
}