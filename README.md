# Ominimo

## Quick Start

### Prerequisites

Make sure you have the following installed:

- **PHP 8.2+** with required extensions
- **Composer**
- **MySQL 8.0+**
- **Node.js 18+** and **npm**
- **Git**

### 1. Backend Setup (Laravel API)

```bash
# Clone and setup backend
git clone <backend-repository-url> ominimoBackend
cd ominimoBackend

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
# Create database: CREATE DATABASE ominimo;
php artisan migrate
php artisan db:seed  # Optional

# Start backend server
php artisan serve
```

Backend API will be available at: `http://localhost:8000`

### 2. Frontend Setup (React)

```bash
# Setup frontend (in separate terminal/folder)
cd ../ominimoFront  # or wherever your React app is located

# Install dependencies
npm install

# Create environment file
cp .env.example .env.local
# Add: REACT_APP_API_URL=http://localhost:8000/api

# Start frontend development server
npm run dev
```

Frontend will be available at: http://localhost:5173/
