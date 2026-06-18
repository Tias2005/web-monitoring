## Web Monitoring System

A full-stack web application designed for monitoring purposes, featuring a Laravel backend API and a React + Vite frontend.

## Features

- Backend: Robust API built with Laravel 12.x.
- Frontend: Responsive UI built with React 19 and Vite.
- Data Visualization: Integrated with `recharts` and `leaflet` for maps.
- External Integrations: Google API Client for extended monitoring capabilities.
- State Management: React Router for navigation and Axios for API requests.

## Prerequisites

Ensure you have the following installed on your machine:
- PHP**: `^8.2`
- Composer: For PHP dependency management.
- Node.js & NPM: For frontend development.
- Database: PostgreSQL 

## Installation
### 1. Clone the Repository
git clone https://github.com/Tias2005/web-monitoring.git
cd web-monitoring

## 2. Backend Setup Laravel
cd backend

# Install PHP dependencies
composer install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in the .env file, then run migrations
php artisan migrate

## 3. Frontend Setup React
cd ../frontend

# Install JavaScript dependencies
npm install

## Usage
Running the Backend
In the backend directory, start the Laravel development server:
php artisan serve

Running the Frontend
In the frontend directory, start the Vite development server:
npm run dev

## Environment Variables
Ensure the following are configured in your production .env:

APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
Database credentials and any required Google API keys.

## Project Structure
/backend: Laravel framework source code.
/frontend: React application source code.
