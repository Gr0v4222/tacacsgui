# TACACSGUI Frontend

This directory contains the Angular source code for the TACACSGUI web interface.

## Prerequisites

- Node.js 18+ and npm
- Angular CLI 21+

## Installation

```bash
npm install
```

## Development Server

```bash
npm start
```

Navigate to `http://localhost:4200/`. The application will automatically reload if you change any of the source files.

## Build

To build the project for production:

```bash
npm run build
```

The build artifacts will be stored in the `../web-build/browser/` directory. These files should then be copied to the `../web/` directory (keeping the `api/` and `assets/` folders intact).

## Project Structure

```
src/
├── app/
│   ├── core/
│   │   └── services/        # Core services (API, Auth)
│   ├── pages/
│   │   ├── auth/           # Authentication pages (login, etc.)
│   │   ├── dashboard/      # Dashboard page
│   │   ├── users/          # User management pages
│   │   └── devices/        # Device management pages
│   ├── app.ts              # Root component
│   ├── app.config.ts       # App configuration
│   └── app.routes.ts       # Routing configuration
├── index.html              # Main HTML file
├── main.ts                 # Application entry point
└── styles.scss             # Global styles
```

## Features Implemented

### Core Features
- **Authentication Service**: Sign in/out functionality with session management
- **API Service**: HTTP client wrapper for backend API calls
- **Routing**: Angular Router with lazy loading support

### Pages
- **Login Page**: User authentication interface
- **Dashboard**: Main dashboard with stats and quick actions
- **User List**: User management interface (stub)
- **Device List**: Device management interface (stub)

## API Integration

The application communicates with the PHP backend API located at `/api/`. All API endpoints are defined in `web/api/app/routes.php`.

### Available API Endpoints

#### Authentication
- `POST /api/auth/signin/` - User login
- `GET /api/auth/signout/` - User logout

#### Users
- `POST /api/user/datatables/` - Get users list
- `GET /api/user/add/` - Add user form
- `POST /api/user/add/` - Create user
- `GET /api/user/edit/` - Edit user form
- `POST /api/user/edit/` - Update user

#### Devices
- `POST /api/tacacs/device/datatables/` - Get devices list
- `POST /api/tacacs/device/add/` - Create device
- `POST /api/tacacs/device/edit/` - Update device

See `web/api/app/routes.php` for the complete API reference.

## Development Notes

- The application uses standalone components (Angular 21+ feature)
- All services are provided at the root level using `providedIn: 'root'`
- Forms use FormsModule for template-driven forms
- HTTP requests are handled via `HttpClient` with RxJS Observables

## Next Steps

To extend the application:

1. Implement remaining pages (reports, settings, etc.)
2. Add comprehensive error handling and loading states
3. Implement authentication guards for protected routes
4. Add data tables with sorting, filtering, and pagination
5. Implement forms for creating/editing users and devices
6. Add real-time updates using WebSockets or polling
7. Implement i18n (internationalization) support

## Original Project

This is a complete rewrite of the original TACACSGUI frontend, which only had compiled JavaScript files without source code. The new implementation maintains the same API compatibility while providing maintainable TypeScript source code.
