<?php

namespace App\Services;

use App\Models\User;

class PermissionService
{
    public function can(User $user, string $permission): bool
    {
        $rolePermissions = config("erp.roles.{$user->role}", []);

        if (in_array('*', $rolePermissions, true)) {
            return true;
        }

        foreach ($rolePermissions as $allowed) {
            if ($allowed === $permission) {
                return true;
            }

            if (str_ends_with($allowed, '.*')) {
                $prefix = rtrim($allowed, '.*');
                if ($permission === $prefix || str_starts_with($permission, $prefix.'.')) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canRoute(User $user, ?string $routeName): bool
    {
        if (! $routeName) {
            return true;
        }

        $permission = $this->permissionFromRoute($routeName);

        return $this->can($user, $permission);
    }

    public function permissionFromRoute(string $routeName): string
    {
        $map = [
            'dashboard' => 'dashboard',
            'parts.index' => 'parts',
            'parts.store' => 'parts',
            'parts.update' => 'parts',
            'parts.destroy' => 'parts',
            'parts.import' => 'parts.import',
            'parts.import.store' => 'parts.import',
            'stock.index' => 'stock',
            'stock.movements' => 'stock',
            'stock.adjustment' => 'stock',
            'stock.adjustment.store' => 'stock',
            'quotations.index' => 'quotations',
            'quotations.create' => 'quotations',
            'quotations.store' => 'quotations',
            'quotations.show' => 'quotations',
            'quotations.convert' => 'quotations',
            'sales-invoices.index' => 'sales',
            'sales-invoices.create' => 'sales',
            'sales-invoices.store' => 'sales',
            'sales-invoices.post' => 'sales',
            'sale-returns.index' => 'sale-returns',
            'sale-returns.create' => 'sale-returns',
            'sale-returns.store' => 'sale-returns',
            'sale-returns.post' => 'sale-returns',
            'stock-transfers.index' => 'stock-transfers',
            'stock-transfers.create' => 'stock-transfers',
            'stock-transfers.store' => 'stock-transfers',
            'stock-transfers.complete' => 'stock-transfers',
            'purchase-orders.index' => 'purchase-orders',
            'purchase-orders.create' => 'purchase-orders',
            'purchase-orders.store' => 'purchase-orders',
            'purchase-orders.show' => 'purchase-orders',
            'purchase-orders.receive' => 'purchase-orders',
            'purchase-invoices.index' => 'purchase-invoices',
            'purchase-invoices.create' => 'purchase-invoices',
            'purchase-invoices.store' => 'purchase-invoices',
            'purchase-invoices.post' => 'purchase-invoices',
            'customers.index' => 'customers',
            'customers.store' => 'customers',
            'customers.update' => 'customers',
            'customers.destroy' => 'customers',
            'vendors.index' => 'vendors',
            'vendors.store' => 'vendors',
            'vendors.update' => 'vendors',
            'vendors.destroy' => 'vendors',
            'branches.index' => 'branches',
            'branches.store' => 'branches',
            'branches.update' => 'branches',
            'branches.destroy' => 'branches',
            'locations.index' => 'locations',
            'locations.store' => 'locations',
            'locations.update' => 'locations',
            'locations.destroy' => 'locations',
            'brands.index' => 'brands',
            'brands.store' => 'brands',
            'brands.update' => 'brands',
            'brands.destroy' => 'brands',
            'origins.index' => 'origins',
            'origins.store' => 'origins',
            'origins.update' => 'origins',
            'origins.destroy' => 'origins',
            'franchises.index' => 'franchises',
            'franchises.store' => 'franchises',
            'franchises.update' => 'franchises',
            'franchises.destroy' => 'franchises',
            'settings.index' => 'settings',
            'settings.update' => 'settings',
            'discount-rules.store' => 'settings',
            'discount-rules.destroy' => 'settings',
            'users.index' => 'users',
            'users.update' => 'users',
            'pricing.resolve' => 'sales',
            'documents.sales-invoice.pdf' => 'sales',
            'documents.purchase-invoice.pdf' => 'purchase-invoices',
            'documents.part.label' => 'parts',
            'documents.part.barcode' => 'parts',
            'accounts.index' => 'finance',
            'accounts.store' => 'finance',
            'accounts.update' => 'finance',
            'accounts.destroy' => 'finance',
            'journal-entries.index' => 'finance',
            'journal-entries.show' => 'finance',
            'finance.reports.index' => 'finance',
            'finance.reports.trial-balance' => 'finance',
            'finance.reports.income-statement' => 'finance',
            'finance.reports.balance-sheet' => 'finance',
            'finance.reports.customer-aging' => 'finance',
            'finance.reports.vendor-aging' => 'finance',
            'vehicles.index' => 'workshop',
            'vehicles.store' => 'workshop',
            'vehicles.update' => 'workshop',
            'vehicles.destroy' => 'workshop',
            'job-cards.index' => 'workshop',
            'job-cards.create' => 'workshop',
            'job-cards.store' => 'workshop',
            'job-cards.show' => 'workshop',
            'job-cards.update-status' => 'workshop',
            'job-cards.convert' => 'workshop',
            'workshop.reports.wip' => 'workshop',
            'departments.index' => 'hr',
            'departments.store' => 'hr',
            'departments.update' => 'hr',
            'departments.destroy' => 'hr',
            'employees.index' => 'hr',
            'employees.store' => 'hr',
            'employees.update' => 'hr',
            'employees.destroy' => 'hr',
            'attendance.index' => 'hr',
            'attendance.store' => 'hr',
            'payroll.index' => 'hr',
            'payroll.create' => 'hr',
            'payroll.store' => 'hr',
            'payroll.show' => 'hr',
            'payroll.post' => 'hr',
            'hr.reports.expiring-documents' => 'hr',
            'reports.index' => 'reports',
            'reports.show' => 'reports',
            'reports.pdf' => 'reports',
            'reports.csv' => 'reports',
        ];

        if (isset($map[$routeName])) {
            return $map[$routeName];
        }

        return explode('.', $routeName)[0] ?? $routeName;
    }
}
