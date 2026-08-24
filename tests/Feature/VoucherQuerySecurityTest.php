<?php

namespace Tests\Feature;

use App\Models\Voucher;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VoucherQuerySecurityTest extends TestCase
{
    public function test_related_voucher_id_is_a_bound_query_parameter(): void
    {
        $query = Voucher::withoutGlobalScopes()->whereRelatedVoucherContains(42);

        $this->assertStringContainsString('find_in_set(?, related_voucher)', $query->toSql());
        $this->assertSame([42], $query->getBindings());
    }

    public function test_voucher_book_route_accepts_only_numeric_ids(): void
    {
        $route = Route::getRoutes()->getByName('vouchers.book');

        $this->assertNotNull($route);
        $this->assertSame('[0-9]+', $route->wheres['id']);
    }
}
