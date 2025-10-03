# Laravel Blog Frontend - Copilot Instructions

This is a React frontend application for the Laravel Blog API.

## Project Setup

- [x] Vite + React project scaffolded
- [x] React Router Dom for client-side routing
- [x] Axios for API communication
- [x] Bootstrap 5 for styling and components
- [x] Authentication context for user state management

## Key Components

- **AuthContext**: Manages user authentication state and tokens
- **Navbar**: Navigation with authentication-aware menu
- **PostsList**: Main page showing paginated blog posts
- **PostDetail**: Single post view with comments system
- **PostForm**: Create/edit post form with validation
- **Login/Register**: Authentication forms

## API Integration

- Base URL: `http://localhost:8000/api`
- JWT token authentication via localStorage
- Automatic token injection in requests
- Error handling with 401 redirect to login

## Development

- Development server: `npm run dev`
- Production build: `npm run build`
- Server runs on `http://localhost:5173`

## Authentication Flow

1. User logs in → receives JWT token
2. Token stored in localStorage
3. Axios interceptor adds token to requests
4. Protected routes check authentication status
5. Automatic logout on token expiry (401 errors)
