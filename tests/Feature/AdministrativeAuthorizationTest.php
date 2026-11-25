<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdministrativeAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('t', 32)),
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
            'session.driver' => 'array',
            'security.admin_user_ids' => [],
        ]);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2014_10_12_000000_create_users_table.php',
            'database/migrations/2021_05_05_110916_create_countries_table.php',
            'database/migrations/2021_05_05_110926_create_states_table.php',
            'database/migrations/2021_05_05_110935_create_cities_table.php',
            'database/migrations/2021_05_05_110946_create_departments_table.php',
            'database/migrations/2021_05_05_111902_create_employees_table.php',
        ]])->assertExitCode(0);
    }

    private function user(): User
    {
        $unique = uniqid();
        return User::create([
            'username' => $unique, 'first_name' => 'Test', 'last_name' => 'Operator',
            'email' => $unique.'@example.test', 'password' => Hash::make('old-password-123'),
        ]);
    }

    private function passwordBody(string $current = 'old-password-123'): array
    {
        return ['current_password' => $current, 'password' => 'replacement-password-456', 'password_confirmation' => 'replacement-password-456'];
    }

    public function test_anonymous_management_employee_and_password_routes_are_blocked(): void
    {
        $user = $this->user();
        $this->getJson('/users')->assertUnauthorized();
        $this->postJson('/countries', ['country_code' => 'ET', 'name' => 'Injected'])->assertUnauthorized();
        $this->getJson('/api/employees')->assertUnauthorized();
        $this->postJson('/api/employees', [])->assertUnauthorized();
        $this->postJson('/users/'.$user->id.'/change-password', $this->passwordBody())->assertUnauthorized();
        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
    }

    public function test_ordinary_accounts_cannot_manage_records_or_change_another_password(): void
    {
        $actor = $this->user();
        $target = $this->user();
        $this->actingAs($actor);
        $this->getJson('/users')->assertForbidden();
        $this->postJson('/countries', ['country_code' => 'ET', 'name' => 'Injected'])->assertForbidden();
        $this->getJson('/api/employees')->assertForbidden();
        $this->postJson('/api/employees', [])->assertForbidden();
        $this->postJson('/users/'.$target->id.'/change-password', $this->passwordBody())->assertForbidden();
        $this->assertTrue(Hash::check('old-password-123', $target->fresh()->password));
    }

    public function test_self_service_requires_the_current_password(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $this->postJson('/users/'.$user->id.'/change-password', $this->passwordBody('incorrect'))->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
        $this->post('/users/'.$user->id.'/change-password', $this->passwordBody())->assertRedirect();
        $this->assertTrue(Hash::check('replacement-password-456', $user->fresh()->password));
    }

    public function test_approved_admin_can_manage_records_and_reset_using_their_own_password(): void
    {
        $admin = $this->user();
        $target = $this->user();
        $target->update(['password' => Hash::make('target-private-password')]);
        config(['security.admin_user_ids' => [(string) $admin->id]]);
        $this->actingAs($admin);
        $this->post('/countries', ['country_code' => 'ET', 'name' => 'Ethiopia'])->assertRedirect();
        $this->assertDatabaseHas('countries', ['country_code' => 'ET', 'name' => 'Ethiopia']);
        $this->getJson('/api/employees')->assertOk();
        $this->postJson('/users/'.$target->id.'/change-password', $this->passwordBody('target-private-password'))->assertUnprocessable();
        $this->post('/users/'.$target->id.'/change-password', $this->passwordBody())->assertRedirect();
        $this->assertTrue(Hash::check('replacement-password-456', $target->fresh()->password));
    }

    public function test_session_authenticated_api_mutations_still_require_csrf(): void
    {
        $admin = $this->user();
        config(['security.admin_user_ids' => [(string) $admin->id]]);
        $this->actingAs($admin);
        // Laravel normally bypasses CSRF in tests; exercise the production check.
        $this->app->instance('env', 'production');
        $this->postJson('/api/employees', [])->assertStatus(419);
    }
}
