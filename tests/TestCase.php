<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Els tests no vénen d'un navegador real — CSRF és una protecció de browser, no de lògica de negoci
        // Laravel 11: CSRF és PreventRequestForgery, no ValidateCsrfToken
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);

        // Tenant per defecte de tots els tests (multi-tenant): les rutes
        // públiques i d'admin exigeixen /{tenant}/... a la URL, i les
        // factories hi enganxen tenant_id — cal que ja existeixi quan
        // RefreshDatabase deixa la BD buida a cada test.
        if (Schema::hasTable('tenants')) {
            if (! Tenant::where('slug', 'campus')->exists()) {
                Tenant::factory()->create(['slug' => 'campus', 'name' => 'Campus']);
            }

            // Perquè route('campus.lms.course', ...) etc. dins dels tests
            // (que no passen per ResolveWebTenant, ja que es criden abans de
            // fer la petició) omplin soles el paràmetre {tenant}.
            \Illuminate\Support\Facades\URL::defaults(['tenant' => 'campus']);
        }
    }
}
