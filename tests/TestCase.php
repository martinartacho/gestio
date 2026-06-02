<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Els tests no vénen d'un navegador real — CSRF és una protecció de browser, no de lògica de negoci
        // Laravel 11: CSRF és PreventRequestForgery, no ValidateCsrfToken
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
    }
}
