# Laravel Blog Frontend

A React frontend application for the Laravel Blog API built with Vite, React Router, Axios, and Bootstrap 5.

## Features

- **Authentication**: Login, register, logout with JWT tokens
- **Posts Management**: Create, read, update, delete blog posts
- **Comments System**: Add and delete comments on posts
- **Responsive Design**: Bootstrap 5 components and styling
- **Protected Routes**: Authentication-based navigation
- **Real-time Updates**: Dynamic content loading and updates

## Tech Stack

- **React 19** - UI Library
- **Vite** - Build tool and development server
- **React Router Dom** - Client-side routing
- **Axios** - HTTP client for API calls
- **Bootstrap 5** - CSS framework and components
- **Context API** - State management for authentication

## Prerequisites

- Node.js 16+
- NPM or Yarn
- Laravel Blog API running on `http://localhost:8000`

## Installation & Setup

1. **Install dependencies:**

   ```bash
   npm install
   ```

2. **Configure API endpoint:**
   The app is configured to connect to Laravel API at `http://localhost:8000/api`
   If your API runs on a different URL, update it in `src/services/api.js`

3. **Start development server:**
   ```bash
   npm run dev
   ```
   The app will run on `http://localhost:5173` (or next available port)

## Project Structure

```
src/
├── components/
│   └── Navbar.jsx          # Navigation component
├── contexts/
│   └── AuthContext.jsx     # Authentication state management
├── pages/
│   ├── Login.jsx           # Login page
│   ├── Register.jsx        # Registration page
│   ├── PostsList.jsx       # Posts listing with pagination
│   ├── PostDetail.jsx      # Single post view with comments
│   └── PostForm.jsx        # Create/edit post form
├── services/
│   └── api.js              # API service layer with Axios
├── App.jsx                 # Main app component with routing
└── main.jsx                # App entry point
```

## API Integration

The app connects to Laravel Blog API with the following endpoints:

### Authentication

- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user

### Posts

- `GET /api/posts` - Get paginated posts
- `POST /api/posts` - Create new post (auth required)
- `GET /api/posts/{id}` - Get single post with comments
- `PUT /api/posts/{id}` - Update post (owner only)
- `DELETE /api/posts/{id}` - Delete post (owner only)

### Comments

- `GET /api/posts/{id}/comments` - Get post comments
- `POST /api/posts/{id}/comments` - Add comment (auth required)
- `DELETE /api/comments/{id}` - Delete comment (owner or post owner)

## Usage

1. **Start Laravel API** first on `http://localhost:8000`
2. **Start React app** with `npm run dev`
3. **Open browser** to `http://localhost:5173`
4. **Register/Login** to access protected features
5. **Create posts** and interact with the blog

## Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run preview` - Preview production build
- `npm run lint` - Run ESLint

## Features Overview

### Public Features

- View all blog posts
- Read individual posts and comments
- User registration and login

### Authenticated Features

- Create new blog posts
- Edit your own posts
- Delete your own posts
- Add comments to any post
- Delete your own comments
- Delete comments on your posts (as post owner)

## Authentication Flow

The app uses JWT tokens stored in localStorage:

1. User logs in → receives token
2. Token included in all authenticated API requests
3. Token cleared on logout or 401 errors
4. Automatic redirect to login for protected routes

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes and test
4. Submit a pull request

## License

MIT License+ Vite

This template provides a minimal setup to get React working in Vite with HMR and some ESLint rules.

Currently, two official plugins are available:

- [@vitejs/plugin-react](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react) uses [Babel](https://babeljs.io/) for Fast Refresh
- [@vitejs/plugin-react-swc](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react-swc) uses [SWC](https://swc.rs/) for Fast Refresh

## React Compiler

The React Compiler is not enabled on this template. To add it, see [this documentation](https://react.dev/learn/react-compiler/installation).

## Expanding the ESLint configuration

If you are developing a production application, we recommend using TypeScript with type-aware lint rules enabled. Check out the [TS template](https://github.com/vitejs/vite/tree/main/packages/create-vite/template-react-ts) for information on how to integrate TypeScript and [`typescript-eslint`](https://typescript-eslint.io) in your project.
