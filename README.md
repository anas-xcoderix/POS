# PartFlow

Web-based parts & operations platform (inventory, sales, purchase, workshop, finance, HR). Evolved from the legacy IAAPCO desktop ERP.

**Fresh Laravel 11 project** — not based on any other project in `htdocs`.

## Stack

- Laravel 11 + Breeze + Tailwind CSS
- MySQL (`iaapco_erp` database)
- Blade views with ERP sidebar layout

## Modules

| Module | Tables | Status |
|--------|--------|--------|
| Organization | branches, departments, employees | Migrations + CRUD (branches) |
| Master Data | brands, origins, franchises, locations, units, parts | Migrations + CRUD |
| Parties | customers, vendors | Migrations + CRUD |
| Inventory | stock_balances, stock_movements, stock_transfers | Migrations + stock view + logic |
| Sales | quotations, sales_invoices, sale_returns | Migrations + invoice CRUD + post stock |
| Purchase | purchase_orders, purchase_invoices | Migrations + CRUD + receive stock |
| Accounting | accounts, journal_entries | Migrations (UI pending) |
| Workshop | vehicles, job_cards | Migrations (UI pending) |

## Setup

```bash
cd /opt/lampp/htdocs/iaapco-erp
composer install
cp .env.example .env   # already configured for MySQL iaapco_erp
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Open: `http://localhost:8000`

**Login:** `admin@gmail.com` / `password`

## XAMPP

Point Apache document root or create a virtual host to `/opt/lampp/htdocs/iaapco-erp/public`.

Ensure MySQL is running and database `iaapco_erp` exists.

## Business Logic Services

- `App\Services\StockService` — stock adjustments, purchase receive, sale issue
- `App\Services\SalesService` — quotations & sales invoices with stock posting
- `App\Services\PurchaseService` — PO & purchase invoices with stock receive

## Note on Schema

Database structure is modeled after IAAPCO desktop ERP modules (parts master, multi-branch, sales, purchase, workshop). When SQL Server `InventoryHas` schema export is available, migrations can be aligned to match exact legacy table/column names for data import.
