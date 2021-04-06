<?php

namespace Tests\Concerns;

use App\Models\UserAdmin;

/**
 * @covers App\Models\Session
 */
trait InteractsWithAdmin
{
    /**
     * Set the currently logged in user for the application.
     *
     * @return $this
     */
    protected function actingAsAdmin()
    {
        return $this->actingAs(UserAdmin::factory()->create());
    }

    /**
     * Add Authorization to Headers using Admin credentials
     *
     * @return $this
     */
    protected function withAuthAdmin()
    {
        $token = UserAdmin::factory()->create()->createToken('admin-token', ['user:admin'])->plainTextToken;

        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }
}
