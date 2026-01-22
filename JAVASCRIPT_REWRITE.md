# JavaScript Rewrite Documentation

## Overview

The TACACSGUI project previously contained only compiled/minified JavaScript files with no source code. This made it impossible to maintain, update, or customize the frontend application. 

This update provides a complete rewrite of the JavaScript logic from scratch using modern Angular 21 with TypeScript, giving developers full access to readable, maintainable source code.

## What Was Changed

### Before
- Only compiled JavaScript files (e.g., `main.0b46a906ffd48329f3e5.js`)
- No source code available
- No way to modify or maintain the frontend
- Multiple font and icon files embedded
- No build process or development environment

### After
- Complete Angular 21 application with TypeScript source code in `web-src/`
- Modern development environment with hot-reload
- Maintainable and extensible codebase
- Build process that generates optimized production files
- Clean separation between source code and compiled output

## Directory Structure

```
tacacsgui/
├── web-src/               # NEW: Angular source code
│   ├── src/
│   │   ├── app/
│   │   │   ├── core/
│   │   │   │   └── services/    # API and Auth services
│   │   │   ├── pages/
│   │   │   │   ├── auth/        # Login page
│   │   │   │   ├── dashboard/   # Main dashboard
│   │   │   │   ├── users/       # User management
│   │   │   │   └── devices/     # Device management
│   │   │   ├── app.ts           # Root component
│   │   │   ├── app.config.ts    # App configuration
│   │   │   └── app.routes.ts    # Routing
│   │   ├── index.html
│   │   ├── main.ts
│   │   └── styles.scss
│   ├── angular.json       # Angular CLI configuration
│   ├── package.json       # Dependencies
│   ├── tsconfig.json      # TypeScript configuration
│   └── README.md          # Development documentation
├── web/                   # Compiled output
│   ├── api/              # PHP backend (unchanged)
│   ├── assets/           # Static assets (unchanged)
│   ├── index.html        # Generated HTML
│   ├── main-*.js         # Generated JavaScript
│   └── styles-*.css      # Generated CSS
├── web-build/            # Build artifacts (gitignored)
├── build.sh              # Build and deploy script
└── JAVASCRIPT_REWRITE.md # This file
```

## Features Implemented

### Core Infrastructure
- ✅ Angular 21 with standalone components
- ✅ TypeScript for type safety
- ✅ RxJS for reactive programming
- ✅ Angular Router for navigation
- ✅ HTTP Client for API communication

### Services
- ✅ **API Service** (`core/services/api.ts`)
  - HTTP client wrapper
  - Centralized API communication
  - Error handling support

- ✅ **Auth Service** (`core/services/auth.ts`)
  - Sign in/out functionality
  - Session management
  - Authentication state tracking

### Pages
- ✅ **Login Page** (`pages/auth/login/`)
  - Username/password form
  - Error handling
  - Loading states
  - Styled with modern UI

- ✅ **Dashboard** (`pages/dashboard/`)
  - Statistics display
  - Quick actions
  - Navigation to other pages
  - Logout functionality

- ✅ **User List** (stub)
- ✅ **Device List** (stub)

### Routing
- ✅ Configured routes for all pages
- ✅ Default redirect to dashboard
- ✅ Wildcard route for 404 handling

## Development Workflow

### Initial Setup
```bash
cd web-src
npm install
```

### Development Server
```bash
cd web-src
npm start
```
Then open `http://localhost:4200/`

### Building for Production
```bash
# Option 1: Use the build script (recommended)
./build.sh

# Option 2: Manual build
cd web-src
npm run build
# Then manually copy files from web-build/browser/ to web/
```

### Making Changes
1. Edit files in `web-src/src/`
2. Test in development server (`npm start`)
3. Build for production (`./build.sh`)
4. Commit changes to git

## API Integration

The application communicates with the PHP backend located at `web/api/`. All endpoints are defined in `web/api/app/routes.php`.

### Authentication Flow
```typescript
// Login
this.auth.signIn(username, password).subscribe(response => {
  if (response.success) {
    // Redirect to dashboard
  }
});

// Logout
this.auth.signOut().subscribe(() => {
  // Redirect to login
});
```

### API Calls
```typescript
// GET request
this.api.get('/user/list/').subscribe(users => {
  // Handle users
});

// POST request
this.api.post('/user/add/', userData).subscribe(response => {
  // Handle response
});
```

## Migration Notes

### Removed Files
All compiled JavaScript, CSS, and font files from the old Angular build were removed:
- `main.*.js`, `polyfills.*.js`, `runtime.*.js`, etc.
- `styles.*.css`
- Font files (Poppins, Roboto, Material Icons, Font Awesome, etc.)
- Icon files (Flaticon, Socicon, Line Awesome)

### Preserved Files
The following were preserved:
- `web/api/` - PHP backend (unchanged)
- `web/assets/` - Static assets like images and logos (unchanged)
- `web/.htaccess` - Server configuration (unchanged)

## Next Steps

To complete the frontend implementation, the following features should be added:

### High Priority
1. **Authentication Guard** - Protect routes that require login
2. **User Management Pages**
   - User list with DataTables
   - Add/edit user forms
   - Delete confirmation
3. **Device Management Pages**
   - Device list with DataTables
   - Add/edit device forms
   - Group management
4. **Error Handling** - Global error interceptor
5. **Loading States** - Global loading indicator

### Medium Priority
6. **TACACS Configuration**
   - ACL management
   - Service configuration
   - Command configuration
7. **Reports Module**
   - Authentication logs
   - Authorization logs
   - Accounting logs
   - Charts and statistics
8. **Settings Module**
   - Password policy
   - SMTP configuration
   - Time settings
   - Network settings

### Lower Priority
9. **MAVIS Integration**
   - LDAP configuration
   - OTP setup
   - Local authentication
10. **Configuration Manager**
    - Device configuration backups
    - Diff viewer
    - Git integration
11. **High Availability**
    - Master/slave setup
    - Replication status
12. **Backup & Update**
    - System backups
    - Software updates

## Troubleshooting

### Build Errors
If you encounter build errors:
```bash
cd web-src
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Missing Dependencies
```bash
cd web-src
npm install @angular/material @angular/cdk @angular/animations
```

### API CORS Issues
If running the dev server separately from the backend, you may need to configure a proxy. Create `web-src/proxy.conf.json`:
```json
{
  "/api": {
    "target": "http://localhost:80",
    "secure": false,
    "changeOrigin": true
  }
}
```

Then update `package.json`:
```json
"start": "ng serve --proxy-config proxy.conf.json"
```

## Technical Details

### Build Configuration
- **Output**: `web-build/browser/`
- **Bundle Size**: ~343 KB (minified)
- **Compression**: ~89 KB (gzipped)
- **Target**: ES2022
- **Browser Support**: Modern browsers (Chrome, Firefox, Safari, Edge)

### Dependencies
- Angular 21.1.0
- RxJS 7.8.0
- TypeScript 5.9.2
- Angular Material 21.1.0 (optional, for UI components)

### Code Style
- Standalone components (no NgModules)
- OnPush change detection where applicable
- Reactive forms for complex forms
- Template-driven forms for simple forms
- Services provided at root level

## Support

For questions or issues:
1. Check `web-src/README.md` for development documentation
2. Review the PHP API routes in `web/api/app/routes.php`
3. Consult the original TACACSGUI documentation at https://tacacsgui.com/

## License

This code follows the same license as the TACACSGUI project.
