# Inventory Manager ERP System

<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="350" alt="Laravel Logo">
</p>

<p align="center">
    <strong>Professional Multi-Location ERP & Inventory Management System</strong><br>
    Built with Laravel, Livewire, PowerGrid & Bootstrap
</p>

---

# 📦 About The Project

Inventory Manager is a professional ERP-style inventory and business management system built for **MTOMAWE COMPANY LIMITED**.

The system is designed to support:

* Multi-location inventory management
* Purchases & receivings
* Sales & credit sales
* Internal requisitions & transfers
* FIFO costing
* Asset management
* User groups & permissions
* ERP-style navigation & workflows
* Audit-friendly stock architecture
* Restaurant / Bar / Zoo operational workflows

The application follows real ERP principles including:

* Separation of purchasing and receiving
* Stock movement tracking
* Permission-based module access
* Multi-department workflows
* Composite item kits
* Internal stock issue requests
* Credit and cash sales support
* Professional ERP UI/UX architecture

---

# 🚀 Core Features

## 📦 Inventory Management

* Item management
* Categories
* Departments
* Multi-location stock
* Stock adjustments
* Closing stock
* FIFO costing
* Item kits / composite products
* Stock movement architecture

---

## 🛒 Purchase & Receiving System

* Requisitions
* Purchase workflows
* Supplier purchases
* Cash purchases
* Receiving module
* Supplier tracking
* Purchase cost history

---

## 💰 Sales System

* Cash sales
* Credit sales
* Customer management
* Payment tracking
* Profit calculation
* Cost snapshotting at sale time

---

## 🔄 Internal Operations

* Internal issue requests
* Stock transfers between locations
* Approval workflows
* Departmental inventory tracking

---

## 🖥 Asset Management

* Asset inventory
* Asset purchases
* Damaged asset tracking
* Weighted average asset costing
* Asset valuation reporting

---

## 👥 User & Permission System

* Group-based permissions
* Permission-aware sidebar
* ERP-style role architecture
* Secure module access
* Professional user management

---

# 🧠 ERP Architecture Highlights

This system follows professional ERP architecture patterns including:

* Service-layer business logic
* Transaction-safe inventory operations
* Multi-location stock management
* Weighted Average Costing (WAC)
* Stock movement audit trails
* Permission-aware navigation
* Modular ERP layout system
* Scalable enterprise-ready structure

---

# 🏗 Technology Stack

## Backend

* PHP 8+
* Laravel
* Livewire

## Frontend

* Bootstrap 5
* PowerGrid
* Alpine.js
* Font Awesome
* Bootstrap Icons

## Database

* MySQL / MariaDB

## Development Tools

* Composer
* NPM
* Vite

---

# 📂 Project Structure

```bash
app/
├── Http/
├── Livewire/
├── Models/
├── Services/
├── PowerGrid/
├── Providers/

database/
├── migrations/
├── seeders/

resources/
├── views/
├── css/
├── js/
```

---

# ⚙️ System Requirements

Before installing, ensure your system has:

| Requirement   | Version   |
| ------------- | --------- |
| PHP           | 8.2+      |
| Composer      | Latest    |
| Node.js       | 18+       |
| NPM           | Latest    |
| MySQL/MariaDB | Supported |
| Git           | Latest    |

---

# 📥 Installation Guide

## 1️⃣ Clone The Repository

```bash
git clone <repository-url>
```

Example:

```bash
git clone https://github.com/your-username/inventory-manager.git
```

---

## 2️⃣ Enter The Project Directory

```bash
cd inventory-manager
```

---

## 3️⃣ Install PHP Dependencies

```bash
composer install
```

---

## 4️⃣ Install Frontend Dependencies

```bash
npm install
```

---

## 5️⃣ Create Environment File

```bash
cp .env.example .env
```

---

## 6️⃣ Generate Application Key

```bash
php artisan key:generate
```

---

# 🗄 Database Configuration

Open the `.env` file and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_manager
DB_USERNAME=root
DB_PASSWORD=
```

---

# 🧱 Run Migrations & Seeders

```bash
php artisan migrate --seed
```

This will:

* Create all database tables
* Seed permissions
* Seed default user groups
* Seed default payment methods
* Create the super administrator account

---

# 🔐 Default Login Credentials

After seeding:

| Role                | Email                                         | Password |
| ------------------- | --------------------------------------------- | -------- |
| Super Administrator | [super@example.com](mailto:super@example.com) | password |

⚠️ Change the password immediately in production.

---

# ▶️ Running The Application

## Start Laravel Server

```bash
php artisan serve
```

Application URL:

```bash
http://127.0.0.1:8000
```

---

## Start Vite Development Server

In another terminal:

```bash
npm run dev
```

---

# 🧹 Useful Artisan Commands

## Run Migrations

```bash
php artisan migrate
```

---

## Refresh Database

```bash
php artisan migrate:fresh --seed
```

---

## Clear Application Cache

```bash
php artisan optimize:clear
```

---

## Cache Configuration

```bash
php artisan config:cache
```

---

## Cache Routes

```bash
php artisan route:cache
```

---

## Cache Views

```bash
php artisan view:cache
```

---

# 📦 Important Installed Packages

This project uses several important Laravel ecosystem packages.

## Livewire

```bash
composer require livewire/livewire
```

---

## PowerGrid

```bash
composer require power-components/livewire-powergrid
```

Publish PowerGrid:

```bash
php artisan vendor:publish --tag=livewire-powergrid-config
```

---

## Laravel Excel

```bash
composer require maatwebsite/excel
```

Used for:

* Imports
* Exports
* Bulk uploads
* Reporting

---

## Bootstrap Icons

```bash
npm install bootstrap-icons
```

---

## Font Awesome

Loaded via CDN.

---

# 🧱 ERP Layout Features

The system includes a professional ERP interface with:

* Responsive sidebar
* Mobile overlay navigation
* Permission-aware menus
* Modular ERP navigation groups
* Professional PowerGrid styling
* ERP dashboard architecture
* Independent sidebar scrolling
* Responsive modal sizing

---

# 📊 Inventory Logic

## Weighted Average Costing (WAC)

The system uses:

```text
Weighted Average Costing
```

Average cost is recalculated during receiving.

---

## Multi-Location Stock

Stock is tracked per location using:

```text
locations
location_items
stock_movements
```

---

## Item Kits

Composite items:

* reduce component stock
* calculate dynamic kit cost
* support recipe-style inventory logic

---

# 🔒 Permission System

The application uses a permission-based access control system.

Example permissions:

```text
items.view
sales.view
purchases.view
users.view
groups.view
reports.view_financial
```

Navigation automatically adapts based on permissions.

---

# 🧪 Development Notes

## Recommended Development Workflow

```bash
git pull
composer install
npm install
php artisan migrate
npm run dev
php artisan serve
```

---

## Recommended Coding Practices

* Use service classes for business logic
* Avoid direct stock manipulation
* Use stock movement records
* Never delete transactional records
* Prefer inactive flags over hard deletes
* Keep historical accounting data immutable

---

# 🏛 ERP Business Flow

## Purchase Flow

```text
Requisition
    ↓
Purchase
    ↓
Receiving
    ↓
Stock Available
```

---

## Internal Transfer Flow

```text
Issue Request
    ↓
Approval
    ↓
Transfer
    ↓
Stock Movement
```

---

## Sales Flow

```text
Sale
    ↓
Payment
    ↓
Stock Reduction
    ↓
Profit Tracking
```

---

# 📸 UI/UX Features

* ERP-style responsive dashboard
* Professional sidebar architecture
* Permission-aware navigation
* Bootstrap 5 modern UI
* Livewire dynamic components
* PowerGrid professional tables
* Responsive modals
* Mobile-friendly design

---

# 🛡 Security Recommendations

For production deployment:

* Change default admin password
* Set `APP_ENV=production`
* Set `APP_DEBUG=false`
* Use HTTPS
* Configure proper database backups
* Use queue workers for heavy operations
* Configure proper file permissions

---

# 📈 Future Expansion

The system architecture is prepared for future modules such as:

* Accounting & ledgers
* Payroll
* HR management
* Room booking integration
* Notifications
* Audit logs
* Advanced analytics
* Financial reporting
* Multi-branch support

---

# 👨‍💻 Developer Notes

This project was designed with:

* scalability
* maintainability
* ERP workflow accuracy
* professional architecture
* operational accountability

in mind.

It is intended to evolve into a full business management platform for hospitality, restaurant, retail, and operational environments.

---

# 🤝 Contributing

## Contribution Workflow

```bash
git checkout -b feature/your-feature-name
```

Commit changes:

```bash
git commit -m "feat: add new feature"
```

Push branch:

```bash
git push origin feature/your-feature-name
```

---

# 📝 Recommended Commit Convention

```text
feat: add purchase receiving workflow
fix: resolve stock adjustment bug
refactor: improve stock movement service
ui: redesign ERP sidebar navigation
```

---

# 📄 License

This project is proprietary software developed for internal ERP and inventory management operations.

---

# 🙌 Acknowledgements

Built using:

* [Laravel](https://laravel.com?utm_source=chatgpt.com)
* [Livewire](https://livewire.laravel.com?utm_source=chatgpt.com)
* [PowerGrid](https://livewire-powergrid.com?utm_source=chatgpt.com)
* [Bootstrap](https://getbootstrap.com?utm_source=chatgpt.com)

---

# 👨‍💻 Author

Developed by Eddy for **MTOMAWE COMPANY LIMITED**.

Professional ERP architecture for real-world operational management.
