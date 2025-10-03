# Ominimo

## Quick Start

### Prerequisites

Make sure you have the following installed:

- **PHP 8.2+** with required extensions
- **Composer**
- **MySQL 8.0+**
- **Node.js 18+** and **npm**

### 1. Backend Setup (Laravel API)

```bash
# Navigate to backend directory
cd ominimoBackend

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
# Create MySQL database: CREATE DATABASE ominimo;
php artisan migrate
php artisan db:seed

# Start backend server
php artisan serve
```

Backend API will be available at: `http://localhost:8000`

### 2. Frontend Setup (React)

```bash
# Navigate to frontend directory (in separate terminal)
cd ominimoFront

# Install dependencies
npm install

# Create environment file
cp .env.example .env.local

# Start frontend development server
npm run dev
```

Frontend will be available at: http://localhost:5173/
