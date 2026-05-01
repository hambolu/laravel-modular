<?php

namespace App\Modules\Order\Services;

use LaravelModular\Abstracts\AbstractService;
use LaravelModular\Facades\Module;

/**
 * EXAMPLE: Inter-Module Communication
 *
 * Order module uses User and Inventory modules
 * WITHOUT direct imports or coupling.
 */
class OrderService extends AbstractService
{
    public function createOrder(array $data): array
    {
        // Option A: Facade
        $user = Module::call('User@UserService', 'findOrFail', [$data['user_id']]);

        // Option B: helper — terser, NestJS-like
        $product = module('Inventory@InventoryService')->findProduct($data['product_id']);

        // Option C: get instance, call multiple methods
        $inv = module('Inventory@InventoryService');
        $inv->reserve($data['product_id'], $data['quantity']);

        return compact('user', 'product');
    }
}
