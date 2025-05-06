# Architecture Overview

## Overview

This repository contains a PHP-based e-commerce application focused on selling baby clothing products. The application follows a traditional Model-View-Controller (MVC) architectural pattern and is built with vanilla PHP without relying on a specific framework.

The application provides standard e-commerce functionality including:
- Product browsing and searching
- User registration and authentication
- Shopping cart management
- Order processing
- Admin dashboard

## System Architecture

### High-Level Architecture

The application follows a classic MVC (Model-View-Controller) pattern:

1. **Models**: Represent the data structures and database interactions (located in `/models`)
2. **Views**: Handle the presentation layer (located in `/views`)
3. **Controllers**: Process user input and coordinate between models and views (located in `/controllers`)

The application uses a simple front controller pattern with the main entry point being `index.php` which handles routing to the appropriate controller and action based on URL parameters.

### Directory Structure

```
├── admin/                  # Admin panel files
│   ├── index.php           # Admin entry point
│   ├── dashboard.php       # Admin dashboard
│   ├── products.php        # Product management
│   └── ...                 # Other admin modules
├── config/                 # Configuration files
│   ├── config.php          # Application settings
│   └── database.php        # Database connection
├── controllers/            # Application controllers
│   ├── HomeController.php  # Home page controller
│   ├── UserController.php  # User authentication
│   └── ...                 # Other controllers
├── models/                 # Data models
│   ├── User.php            # User model
│   ├── Product.php         # Product model
│   └── ...                 # Other models
├── views/                  # View templates
│   ├── layouts/            # Layout templates
│   ├── home/               # Home page views
│   ├── products/           # Product views
│   └── ...                 # Other view folders
└── index.php               # Main entry point
```

## Key Components

### Backend Components

1. **Routing System**
   - Implemented in `index.php` using URL parameters (`controller` and `action`)
   - Example: `index.php?controller=product&action=list`
   - Controllers and actions are dynamically loaded based on these parameters

2. **Database Layer**
   - Uses PDO for database abstraction
   - Database configuration in `config/database.php`
   - Each model encapsulates database operations related to a specific entity

3. **Authentication System**
   - Session-based authentication implemented in `UserController.php`
   - Role-based access control (customer, staff, admin)
   - Password hashing and verification handled by the User model

4. **Admin Dashboard**
   - Separate admin area with restricted access
   - Manages products, orders, users, promotions, and banners
   - Different access levels for staff and administrators

### Frontend Components

1. **View Templates**
   - Plain PHP templates with HTML
   - Common layouts extracted to `views/layouts/`
   - Minimal frontend processing with most business logic in controllers

2. **CSS Framework**
   - Uses Bootstrap 5 for responsive layouts and UI components
   - Loaded via CDN to minimize server load

3. **JavaScript Libraries**
   - Minimal client-side functionality
   - Uses jQuery for AJAX operations and DOM manipulation
   - Font Awesome for icons

### Database Schema

The application uses a MySQL database with the following primary tables:

1. **users**
   - Stores user accounts, credentials, and profile information
   - Contains role information for authorization

2. **products**
   - Product details including name, description, price, stock, etc.
   - Tracks whether products are featured or on sale

3. **categories**
   - Product categorization hierarchy

4. **orders**
   - Order information including status and payment details
   - Related to users

5. **order_items**
   - Individual items within each order
   - Related to products and orders

6. **promotions**
   - Promotional codes and discounts

7. **banners**
   - Homepage banner images and content

8. **settings**
   - Application configuration settings

## Data Flow

### User Interaction Flow

1. **User Registration and Login**
   - User submits registration form to `UserController::register()`
   - Data validated and stored in database
   - Login process creates user session with role information

2. **Product Browsing**
   - `ProductController::list()` fetches products based on category or search parameters
   - Products are displayed with pagination

3. **Shopping Cart Flow**
   - `CartController` manages cart operations using session storage
   - Cart data persists across pages using PHP sessions
   - Products added to cart are validated against inventory

4. **Checkout Process**
   - User enters shipping and payment information
   - Order created and saved to database
   - Inventory levels updated
   - Confirmation email sent

### Admin Flow

1. **Authentication**
   - Admin/staff login through the same system as customers
   - Role-based access control restricts certain functions

2. **Product Management**
   - Adding, editing, deleting products
   - Managing inventory levels
   - Uploading product images

3. **Order Processing**
   - Viewing order details
   - Updating order status
   - Managing customer communications

## External Dependencies

The application relies on the following external dependencies:

1. **Bootstrap 5**
   - Used for responsive UI components
   - Loaded via CDN: `https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css`

2. **Font Awesome**
   - Provides icons throughout the interface
   - Loaded via CDN: `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css`

3. **Google Fonts**
   - Provides web fonts for typography
   - Uses Nunito and Quicksand fonts

4. **jQuery**
   - Used for AJAX operations and DOM manipulation
   - Minimal usage, primarily for cart interactions

## Deployment Strategy

The application is designed to be deployed on shared PHP hosting environments like InfinityFree, as indicated by the database configuration. Key deployment aspects include:

1. **Server Requirements**
   - PHP 7.0+ with PDO MySQL extension
   - MySQL or MariaDB database
   - Standard shared hosting environment

2. **Deployment Process**
   - Upload files to web server
   - Create MySQL database and import schema
   - Configure database connection in `config/database.php`
   - Set application settings in `config/config.php`

3. **Development Environment**
   - Uses Replit for development, as indicated by the `.replit` configuration
   - PHP development server for testing: `php -S 0.0.0.0:5000`

4. **Security Considerations**
   - Input validation and sanitization throughout controllers
   - Parameterized SQL queries via PDO
   - Password hashing for user credentials
   - CSRF protection for forms (implemented or planned)

## Future Considerations

Potential architectural improvements for future development:

1. **Modernization Options**
   - Migration to a PHP framework like Laravel or Symfony
   - Implementing a frontend framework like Vue.js or React
   - Converting to API-based architecture with separate frontend/backend

2. **Performance Enhancements**
   - Implementing caching for product listings
   - Optimizing database queries
   - Adding image optimization

3. **Scalability**
   - Implementing a CDN for static assets
   - Database sharding for larger product catalogs
   - Containerization with Docker for consistent deployment