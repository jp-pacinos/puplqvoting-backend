<?php

namespace Tests\Unit;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTheLandingPageWillRedirectToTheStudentApp()
    {
        $this->get('/')->assertRedirect(config('spa.student'));
    }
}
