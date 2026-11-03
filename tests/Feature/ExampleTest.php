<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_root_redirects_to_projects(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/projects');
    }
}
