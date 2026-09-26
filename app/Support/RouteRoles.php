<?php

namespace App\Support;

/** Server-side grants. New controllers/actions remain admin-only until reviewed. */
final class RouteRoles
{
    public const APP_ROLES = ['admin', 'accountant', 'estimate', 'dispatch'];

    public static function forAction(string $action): array
    {
        [$controller, $method] = array_pad(explode('@', $action, 2), 2, '');
        $controller = class_basename($controller);

        $shared = [
            'UsersController' => ['getBootstrap'],
            'CompanyController' => ['getAdmin', 'getNotifications'],
        ];
        if (in_array($method, $shared[$controller] ?? [], true)) {
            return self::APP_ROLES;
        }

        $accounting = [
            'CustomersController' => ['index', 'show'],
            'ItemsController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete', 'getDispatch'],
            'InvoicesController' => ['index', 'bulk', 'create', 'store', 'show', 'referenceNumber', 'getInvoiceEstimate'],
            'EstimatesController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete', 'sendEstimate', 'referenceNumber'],
            'OrdersController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete', 'referenceNumber'],
            'ExpensesController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete', 'showReceipt', 'uploadReceipts'],
            'ExpenseCategoryController' => ['index'],
            'PaymentController' => ['index', 'create', 'store', 'show'],
            'ReceiptController' => ['index', 'create', 'store', 'show', 'edit', 'update'],
            'CompanyController' => ['getColors', 'getInventoryType', 'getCustomizeSetting'],
            'NoteController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete'],
            'InventoryController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete', 'increasePrice', 'getInventoryStock', 'getInvoiceStock'],
            'AccountMastersController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete', 'checkName'],
            'AccountGroupsController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete'],
            'AccountLedgersController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete', 'display', 'daysheet'],
            'VouchersController' => ['index', 'create', 'store', 'show', 'edit', 'book', 'getDaybook'],
            'StatesController' => ['index', 'show'],
            'ReportController' => ['getLedgersInReport'],
            'PublicShareController' => ['index', 'store', 'destroy'],
            'BanksController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete'],
            'DispatchController' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete', 'multiple', 'updateDispatch', 'updateToBeDispatch', 'tobeEdit', 'getInvoices', 'dashboard', 'pending', 'completedList'],
            'WhatsappController' => ['sendPdf'],
        ];
        $roles = ['admin'];
        if (in_array($method, $accounting[$controller] ?? [], true)) {
            $roles[] = 'accountant';
            if ($controller === 'EstimatesController') $roles[] = 'estimate';
            if ($controller === 'DispatchController') $roles[] = 'dispatch';
        }

        // Estimate pickers need these reads, but never inventory/customer writes.
        if (($controller === 'InventoryController' && $method === 'index')
            || ($controller === 'CustomersController' && in_array($method, ['index', 'show'], true))
            || ($controller === 'PublicShareController' && $method === 'store')) {
            $roles[] = 'estimate';
        }

        return $roles;
    }
}
