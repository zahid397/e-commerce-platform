<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_handles_a_new_empty_database(): void
    {
        $this->withoutVite()->get('/')->assertOk()->assertSee('No products yet');
    }
}
