# Vendors & Expenses   API

A REST API for managing expenses with vendors and categories, built with Laravel 11 and Laravel Sanctum for authentication.

## Features

- **Authentication**: Token-based authentication using Laravel Sanctum
- **Role-Based Access Control**: Admin and Staff roles with different permissions
- **Expense Management**: Create, read, and delete expenses
- **Vendor Management**: Full CRUD operations for vendors (Admin only)
- **Category Management**: Full CRUD operations for expense categories (Admin only)
- **Reporting**: Summary reports by month and category
- **Soft Deletes**: All entities support soft deletion
- **Validation**: Comprehensive validation rules
- **Testing**: Feature tests covering key functionalities

## Requirements

- PHP >= 8.2
- Composer
- MySQL/PostgreSQL/SQLite
- Laravel 11.x

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd v-expenses
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=expense_db
DB_USERNAME=root
DB_PASSWORD=
```

6. Run migrations:
```bash
php artisan migrate
```

7. Seed the database (optional):
```bash
php artisan db:seed
```

This will create:
- Admin user: `admin@example.com` / `password`
- Staff user: `staff@example.com` / `password`
- Sample vendors, categories, and expenses

## API Documentation

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication

All protected endpoints require authentication via Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

### Authentication Endpoints

#### Register
```http
POST /api/v1/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "staff"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "staff"
  },
  "token": "1|...",
  "token_type": "Bearer"
}
```

#### Login
```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  },
  "token": "1|...",
  "token_type": "Bearer"
}
```

#### Logout
```http
POST /api/v1/logout
Authorization: Bearer {token}
```

### Vendor Endpoints

#### List Vendors
```http
GET /api/v1/vendors
Authorization: Bearer {token}
```

**Query Parameters:**
- `active` (boolean): Filter by active status

**Response:**
```json
[
  {
    "id": 1,
    "name": "Vendor Name",
    "contact_info": "contact@vendor.com",
    "is_active": true,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
]
```

#### Create Vendor (Admin only)
```http
POST /api/v1/vendors
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "New Vendor",
  "contact_info": "contact@vendor.com",
  "is_active": true
}
```

#### Update Vendor (Admin only)
```http
PUT /api/v1/vendors/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Vendor Name",
  "is_active": false
}
```

#### Delete Vendor (Admin only)
```http
DELETE /api/v1/vendors/{id}
Authorization: Bearer {token}
```

### Category Endpoints

#### List Categories
```http
GET /api/v1/categories
Authorization: Bearer {token}
```

**Query Parameters:**
- `active` (boolean): Filter by active status

#### Create Category (Admin only)
```http
POST /api/v1/categories
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Office Supplies",
  "is_active": true
}
```

#### Update Category (Admin only)
```http
PUT /api/v1/categories/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Category",
  "is_active": true
}
```

#### Delete Category (Admin only)
```http
DELETE /api/v1/categories/{id}
Authorization: Bearer {token}
```

**Note:** Categories with linked expenses cannot be deleted (returns 422).

### Expense Endpoints

#### List Expenses
```http
GET /api/v1/expenses
Authorization: Bearer {token}
```

**Query Parameters:**
- `date` (date): Filter by date (YYYY-MM-DD)
- `vendor_id` (integer): Filter by vendor
- `category_id` (integer): Filter by category

**Note:** Staff users can only see their own expenses.

**Response:**
```json
[
  {
    "id": 1,
    "category_id": 1,
    "vendor_id": 1,
    "amount": "100.50",
    "date": "2024-01-15",
    "description": "Office supplies",
    "created_by": 1,
    "category": {
      "id": 1,
      "name": "Office Supplies"
    },
    "vendor": {
      "id": 1,
      "name": "Vendor Name"
    },
    "creator": {
      "id": 1,
      "name": "User Name"
    }
  }
]
```

#### Create Expense
```http
POST /api/v1/expenses
Authorization: Bearer {token}
Content-Type: application/json

{
  "category_id": 1,
  "vendor_id": 1,
  "amount": 100.50,
  "date": "2024-01-15",
  "description": "Office supplies purchase"
}
```

**Validation Rules:**
- `category_id`: Required, must exist, must be active
- `vendor_id`: Optional, must exist and be active if provided
- `amount`: Required, numeric, minimum 0.01
- `date`: Required, valid date
- `description`: Optional, string

#### View Expense
```http
GET /api/v1/expenses/{id}
Authorization: Bearer {token}
```

**Note:** Staff users can only view their own expenses.

#### Delete Expense
```http
DELETE /api/v1/expenses/{id}
Authorization: Bearer {token}
```

**Note:** Staff users can only delete their own expenses. Admins can delete any expense.

### Report Endpoints

#### Summary Report
```http
GET /api/v1/reports/summary
Authorization: Bearer {token}
```

**Response:**
```json
{
  "summary": [
    {
      "month": "2024-01",
      "category_id": 1,
      "category_name": "Office Supplies",
      "total_amount": 500.00,
      "expense_count": 5
    }
  ],
  "total_expenses": 500.00,
  "total_count": 5
}
```

## Role-Based Access Control

### Admin Role
- Full CRUD access to all resources
- Can create, update, and delete vendors
- Can create, update, and delete categories
- Can view and delete any expense
- Can view all expenses in reports

### Staff Role
- Read-only access to vendors and categories
- Can create expenses
- Can only view and delete their own expenses
- Can only see their own expenses in reports

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "Unauthorized. This action requires admin role."
}
```

### 404 Not Found
```json
{
  "message": "No query results for model [App\\Models\\Vendor] 1"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "amount": [
      "The amount field must be at least 0.01."
    ]
  }
}
```

## Development

```
Note* postman collection attached in the code "v-expenses.postman_collection.json"
```
### Running the Server
```bash
php artisan serve
```

The API wil

## Testing

Run the test suite:
```bash
php artisan test
```

### Test Coverage

The test suite includes:
- Authentication tests (register, login, logout)
- Expense creation test
- Role restriction tests (admin vs staff permissions)
- Category deletion test (block when expenses exist)
- Validation tests
- Filter tests for expenses

Run specific test:
```bash
php artisan test --filter ExpenseTest
```

## Database Schema

### Users
- `id`, `name`, `email`, `password`, `role` (admin/staff), `timestamps`

### Vendors
- `id`, `name`, `contact_info`, `is_active`, `deleted_at`, `timestamps`

### Expense Categories
- `id`, `name`, `is_active`, `deleted_at`, `timestamps`

### Expenses
- `id`, `category_id`, `vendor_id` (nullable), `amount`, `date`, `description`, `created_by`, `deleted_at`, `timestamps`
l be available at `http://localhost:8000`
