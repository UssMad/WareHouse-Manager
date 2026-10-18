<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_access_the_client_directory(): void
    {
        $this->get(route('clients.index'))->assertRedirect(route('login'));
    }

    public function test_an_owner_can_create_a_company_client_with_a_contact(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('clients.store'), [
            'reference' => 'CLI-001',
            'client_type' => 'company',
            'name' => 'Atlas Distribution',
            'contact_name' => 'Youssef El Amrani',
            'phone' => '0600000000',
            'is_blacklisted' => false,
        ]);

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'reference' => 'CLI-001',
            'name' => 'Atlas Distribution',
            'is_blacklisted' => false,
        ]);
    }

    public function test_a_client_needs_at_least_one_contact_method(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('clients.index'))->post(route('clients.store'), [
            'reference' => 'CLI-002',
            'client_type' => 'individual',
            'name' => 'Client sans contact',
        ])->assertRedirect(route('clients.index'))->assertSessionHasErrors(['phone', 'email']);
    }

    public function test_an_owner_cannot_update_another_users_client(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $client = Client::create([
            'user_id' => $owner->id, 'reference' => 'CLI-003', 'client_type' => 'company',
            'name' => 'Client privé', 'email' => 'private@example.test',
        ]);

        $this->actingAs($otherUser)->put(route('clients.update', $client), [
            'reference' => 'CLI-003', 'client_type' => 'company', 'name' => 'Modifié', 'email' => 'other@example.test',
        ])->assertForbidden();
    }
}
