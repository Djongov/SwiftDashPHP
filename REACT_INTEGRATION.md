# React + Vite Integration with SwiftDashPHP

This document explains how to use the React + Vite frontend integration with your SwiftDashPHP application.

## 🚀 Quick Start

### Development Mode

1. **Start the Vite development server:**
   ```bash
   cd frontend
   npm run dev
   ```
   This starts the development server on `http://localhost:3000` with hot reloading.

2. **Access React pages:**
   - React Dashboard: `http://localhost/react-dashboard`  
   - React Login: `http://localhost/react-login`

### Production Build

1. **Build the React application:**
   ```bash
   cd frontend
   npm run build
   ```
   This creates optimized production files in `public/assets/react/`

2. **Deploy:**
   The built files are automatically placed in the correct location for your PHP application to serve them.

## 🏗️ Architecture Overview

### Hybrid Approach
- **PHP Backend**: Handles authentication, database operations, and API endpoints
- **React Frontend**: Provides modern UI components and interactivity
- **Shared State**: Authentication and user data flow between PHP sessions and React state

### File Structure
```
SwiftDashPHP/
├── frontend/                     # React + Vite application
│   ├── src/
│   │   ├── components/          # React components (Html, Forms, Alerts)
│   │   ├── pages/               # Page components (Dashboard, Login)
│   │   ├── hooks/               # Custom hooks (useAuth)
│   │   └── services/            # API communication (api.ts)
│   ├── package.json
│   └── vite.config.ts
├── App/Core/ViteIntegration.php  # PHP helper for Vite assets
├── Views/react/layout.php        # React-enabled page template
├── resources/routes/react-routes.php # React-specific routes
└── public/assets/react/          # Built React assets (production)
```

## 🔗 API Integration

### Authentication
React components automatically handle authentication through:
- `useAuth` hook for React state management
- PHP session integration via API endpoints
- CSRF token handling for security

### Available API Endpoints
- `GET /api/auth/check` - Check authentication status
- `POST /api/auth/login` - Login with credentials  
- `POST /api/auth/logout` - Logout user
- `GET /api/dashboard/stats` - Dashboard statistics
- `GET /api/csrf-token` - Get CSRF token

### Making API Calls
```typescript
import apiService from '../services/api'

// GET request
const response = await apiService.get('/api/some-endpoint')

// POST request
const response = await apiService.post('/api/users', { name: 'John' })
```

## 🎨 Using React Components

### HTML Components
React equivalents of your PHP HTML components:

```tsx
import { H1, H2, P, Button, DivBox } from '../components/Html'

// Usage
<H1 center>Page Title</H1>
<P>Some content</P>
<Button theme="blue" size="medium" onClick={handleClick}>
  Click me
</Button>
<DivBox>Styled container</DivBox>
```

### Forms
Enhanced form handling with validation:

```tsx
import { Form } from '../components/Form'

const formConfig = {
  fields: [
    {
      name: 'email',
      label: 'Email',
      type: 'email',
      required: true
    }
  ],
  action: '/api/users',
  onSuccess: (data) => console.log('Success!', data)
}

<Form config={formConfig} />
```

### Alerts
Consistent alert styling:

```tsx
import { SuccessAlert, DangerAlert, InfoAlert } from '../components/Alerts'

<SuccessAlert>Operation completed successfully!</SuccessAlert>
<DangerAlert>Something went wrong.</DangerAlert>
```

## 🔄 Development Workflow

### Adding New React Pages

1. **Create the React component:**
   ```tsx
   // frontend/src/pages/NewPage.tsx
   const NewPage = () => {
     return <div>New page content</div>
   }
   export default NewPage
   ```

2. **Add route to React router:**
   ```tsx
   // frontend/src/App.tsx
   <Route path="/new-page" element={<NewPage />} />
   ```

3. **Add PHP route (optional):**
   ```php
   // resources/routes/react-routes.php
   $router->addRoute('GET', '/react-new-page', function () use ($viewsFolder, $metadataArray) {
       // Route handler
   });
   ```

### Extending Components

Create new components following the existing patterns:

```tsx
// frontend/src/components/MyComponent.tsx
import { ReactNode } from 'react'

interface Props {
  children: ReactNode
  variant?: 'primary' | 'secondary'
}

export const MyComponent = ({ children, variant = 'primary' }: Props) => {
  return (
    <div className={`custom-component ${variant}`}>
      {children}
    </div>
  )
}
```

## 🔧 Configuration

### Vite Configuration
The `vite.config.ts` is configured to:
- Output assets to `public/assets/react/`
- Proxy API calls to PHP backend during development
- Generate manifest for production asset loading

### PHP Integration
The `ViteIntegration` class handles:
- Development vs production asset loading
- Manifest parsing for cache-busted filenames
- CSS and JS file injection

### Environment Variables
- **Development**: Vite dev server on port 3000
- **Production**: Assets served from `public/assets/react/`

## 🚀 Deployment

### Production Checklist
1. ✅ Run `npm run build` in frontend directory
2. ✅ Ensure `public/assets/react/` contains built files
3. ✅ PHP routes properly configured
4. ✅ Authentication endpoints working
5. ✅ CSRF tokens configured

### Performance Optimization
- React components are code-split automatically
- CSS is extracted and minified
- Assets are cache-busted with hashes
- Gzip compression recommended for static assets

## 🔒 Security Considerations

- CSRF tokens are automatically handled
- PHP sessions integrate with React authentication
- API endpoints require proper authentication
- XSS protection through React's built-in escaping

## 🧪 Testing

Run the React development server alongside your PHP server:
- PHP: `http://localhost:80` (or your server URL)
- React Dev: `http://localhost:3000`
- React Pages: Access via PHP routes like `/react-dashboard`

## 🤝 Migration Strategy

1. **Phase 1**: Keep existing PHP pages, add new React pages
2. **Phase 2**: Convert high-value pages to React incrementally  
3. **Phase 3**: Maintain hybrid approach or migrate fully as needed

This approach allows you to gradually adopt React while maintaining all existing functionality.

## 📚 Next Steps

- Add more React components (DataGrid, Charts, etc.)
- Implement React versions of existing PHP forms
- Add state management (Redux/Zustand) if needed
- Enhance API endpoints for better React integration
- Add unit tests for React components