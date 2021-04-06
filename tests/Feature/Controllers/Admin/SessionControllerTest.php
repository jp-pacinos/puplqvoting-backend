<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\Session;
use Illuminate\Support\Arr;
use Tests\Concerns\InteractsWithAdmin;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @covers App\Models\Session
 */
final class SessionControllerTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithAdmin;

    protected $route = 'api/r/sessions';

    protected function setUp(): void
    {
        parent::setUp();

        Session::factory()
            ->state(['id' => 1, 'name' => 'ElectionTesting'])
            ->create();
    }

    public function testControllerIndex()
    {
        $this->actingAsAdmin();

        $response = $this->getJson($this->route);

        $response
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->hasAll([
                        'current_page',
                        'data',
                        'first_page_url',
                        'from',
                        'next_page_url',
                        'path',
                        'per_page',
                        'prev_page_url',
                        'to',
                        'total',
                    ])
                    ->has('data', 1, fn (AssertableJson $json) => $json
                        ->where('name', 'ElectionTesting')
                        ->hasAll([
                            'id',
                            'name',
                            'description',
                            'year',
                            'active',
                            'registration',
                            'verification_type',
                            'started_at',
                            'registration_at',
                            'completed_at',
                            'cancelled_at',
                        ])->etc())
            );
    }

    public function testControllerStore()
    {
        $this->actingAsAdmin();

        $response = $this->postJson($this->route, [
            'name' => 'ElectionTestingStore',
            'year' => \date('Y'),
            'registration' => '0',
            'verification_type' => Arr::random(Session::$VERIFICATION_TYPES),
            'description' => 'Example testing description',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['message', 'session'])
            ->assertJsonPath('session.name', 'ElectionTestingStore');
    }

    public function testControllerStoreButNoDataSentToStore()
    {
        $this->actingAsAdmin();

        $response = $this->postJson($this->route);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name', 'year', 'registration', 'verification_type',
            ]);
    }

    public function testControllerShow()
    {
        $this->actingAsAdmin();

        $response = $this->getJson($this->route . '/1');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'year',
                'active',
                'registration',
                'verification_type',
                'started_at',
                'registration_at',
                'completed_at',
                'cancelled_at',
            ])
            ->assertJsonPath('name', 'ElectionTesting');
    }

    public function testControllerShowButRecordNotFound()
    {
        $this->actingAsAdmin();

        $response = $this->getJson($this->route . '/12345');

        $response->assertNotFound();
    }

    public function testControllerDestroy()
    {
        $this->actingAsAdmin();

        $response = $this->deleteJson($this->route . '/1', [
            'confirmation' => 'ElectionTesting',
        ]);

        $response->assertOk()->assertJsonStructure(['message', 'success'])->assertJsonPath('success', true);
    }

    public function testControllerDestroyButConfirmationIsRequired()
    {
        $this->actingAsAdmin();

        $response = $this->deleteJson($this->route . '/1');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['confirmation']);
    }
}
