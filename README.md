# Mini Inventory & Product Management System

This is a Laravel-based inventory and product management application. It supports dynamic product variants, stock ledger tracking, bulk data export, and role-based access control.

## Prerequisites
- PHP >= 8.2
- Composer
- MySQL or SQLite (configured in `.env`)

## Setup Instructions

1. **Clone & Install Dependencies**
   ```bash
   composer install
   ```

2. **Environment Configuration**
   Copy `.env.example` to `.env` and configure your database settings.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Storage Link**
   Because products support thumbnail uploads, you must link the storage folder:
   ```bash
   php artisan storage:link
   ```

4. **Database Migration & Seeding**
   Run the migrations and seed the database with the default roles and users.
   ```bash
   php artisan migrate --seed
   ```

5. **Big Data Testing (Optional)**
   If you wish to test the system with a massive dataset (50,000+ variants), run the big data seeder:
   ```bash
   php artisan db:seed --class=BigDataSeeder
   ```

6. **Queue Worker**
   Bulk CSV exporting is processed in the background via jobs. You must run a queue worker to process export requests:
   ```bash
   php artisan queue:work
   ```

7. **Run the Application**
   ```bash
   php artisan serve
   ```

## Testing

This application includes automated tests to ensure reliability. To run the test suite, simply run:
```bash
php artisan test
```

## Test Credentials

The `DatabaseSeeder` automatically generates three default users for testing the Role-Based Access Control (RBAC). The password for all accounts is **`password`**.

| Role | Email | Privileges |
| :--- | :--- | :--- |
| **Admin** | `admin@example.com` | Full access. Can manage users, master data (brands, categories, attributes), products, and stock. |
| **Manager** | `manager@example.com` | Can manage products, edit variants, and adjust stock. Cannot manage master data. |
| **Staff** | `staff@example.com` | Read-only access to products and variants. Cannot add/edit products or export data. |

## Assumptions & Design Decisions
- **Roles & Authentication**: A custom role system (`role` column on the `users` table) is used rather than Spatie Permissions for simplicity and speed. Middleware enforces role restrictions across routes.
- **Dynamic Variants**: Product variants are generated dynamically via JavaScript based on a Cartesian product algorithm when combining multiple attributes (e.g., Color + Size).
- **Stock Ledger**: Stock cannot be directly modified. All modifications (In/Out) are recorded in a `stock_movements` ledger table, and the variant's `stock` column acts as a cached total.
- **Concurrency**: `lockForUpdate()` is utilized during stock adjustments to prevent race conditions during concurrent modifications.
- **Exports**: Due to potential memory limits with big data, the bulk export functionality utilizes Laravel Jobs, chunking, and streams to a CSV file stored in the public disk. Progress is tracked via an `export_logs` table.
- **Service Layer**: Complex business logic (saving product combinations, re-hydrating variants during updates, stock ledger logic) is centralized in `App\Services\ProductService.php` to keep controllers thin.
