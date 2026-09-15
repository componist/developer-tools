<?php

declare(strict_types=1);

namespace Componist\DeveloperTools\Tests\Feature;

use App\Models\User;
use Componist\DeveloperTools\Livewire\Database\Tools;
use Componist\DeveloperTools\Tests\TestCase;
use Livewire\Livewire;

class DeveloperToolsFeatureTest extends TestCase
{
    public function test_dev_stub_requires_authentication(): void
    {
        $this->get(route('componist.developer-tools.dev.notification'))->assertRedirect();
    }

    public function test_debug_ui_requires_authentication(): void
    {
        $this->get(route('componist.developer-tools.index'))->assertRedirect();
    }

    public function test_non_admin_is_forbidden_on_debug_ui(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->get(route('componist.developer-tools.index'))
            ->assertForbidden();
    }

    public function test_admin_can_open_debug_index(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('componist.developer-tools.index'))
            ->assertOk();
    }

    public function test_non_admin_cannot_use_database_tools(): void
    {
        $user = $this->verifiedUser();

        Livewire::actingAs($user)
            ->test(Tools::class)
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function verifiedUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ], $overrides));
    }

    private function adminUser(): User
    {
        $admin = $this->verifiedUser([
            'name' => 'Dev Tools Admin',
            'email' => 'devtools-admin@example.com',
        ]);

        config(['developer-tools.admin_user_ids' => [$admin->id]]);

        return $admin;
    }
}
