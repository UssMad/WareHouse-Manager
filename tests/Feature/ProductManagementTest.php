<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_access_products(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_an_owner_can_create_a_product_and_its_initial_stock_movement(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['user_id' => $user->id, 'name' => 'Électronique']);

        $response = $this->actingAs($user)->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Scanner code-barres',
            'reference' => 'SCN-001',
            'description' => 'Scanner sans fil',
            'unit_price' => 549.90,
            'stock_quantity' => 12,
            'alert_threshold' => 3,
        ]);

        $product = Product::firstOrFail();
        $response->assertRedirect(route('products.show', $product));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'user_id' => $user->id, 'stock_quantity' => 12]);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'entry', 'quantity' => 12]);
    }

    public function test_a_user_cannot_view_another_users_product(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::create(['user_id' => $owner->id, 'name' => 'Matériel']);
        $product = Product::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'name' => 'Palette bois',
            'reference' => 'PAL-001',
            'unit_price' => 35,
            'stock_quantity' => 8,
            'alert_threshold' => 2,
        ]);

        $this->actingAs($otherUser)->get(route('products.show', $product))->assertForbidden();
    }

    public function test_a_stock_entry_updates_the_current_quantity(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['user_id' => $user->id, 'name' => 'Fournitures']);
        $product = Product::create([
            'user_id' => $user->id, 'category_id' => $category->id, 'name' => 'Carton',
            'reference' => 'CRT-001', 'unit_price' => 4.5, 'stock_quantity' => 10, 'alert_threshold' => 2,
        ]);

        $this->actingAs($user)->post(route('products.movements.store', $product), [
            'type' => 'entry', 'quantity' => 6, 'supplier' => 'Atlas Distribution', 'occurred_at' => now()->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 16]);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'entry', 'quantity' => 6]);
    }

    public function test_a_stock_exit_cannot_exceed_the_available_quantity(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['user_id' => $user->id, 'name' => 'Fournitures']);
        $product = Product::create([
            'user_id' => $user->id, 'category_id' => $category->id, 'name' => 'Carton',
            'reference' => 'CRT-001', 'unit_price' => 4.5, 'stock_quantity' => 2, 'alert_threshold' => 1,
        ]);
        $client = Client::create([
            'user_id' => $user->id, 'reference' => 'CLI-001', 'client_type' => 'company',
            'name' => 'Client test', 'email' => 'client@example.test',
        ]);

        $this->actingAs($user)->from(route('products.show', $product))->post(route('products.movements.store', $product), [
            'type' => 'exit', 'client_id' => $client->id, 'quantity' => 3, 'reason' => 'Commande client', 'occurred_at' => now()->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('products.show', $product))->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 2]);
    }

    public function test_a_blacklisted_client_cannot_receive_a_stock_exit(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['user_id' => $user->id, 'name' => 'Fournitures']);
        $product = Product::create([
            'user_id' => $user->id, 'category_id' => $category->id, 'name' => 'Carton',
            'reference' => 'CRT-002', 'unit_price' => 4.5, 'stock_quantity' => 10, 'alert_threshold' => 2,
        ]);
        $client = Client::create([
            'user_id' => $user->id, 'reference' => 'BLK-001', 'client_type' => 'individual',
            'name' => 'Client bloqué', 'phone' => '0600000000', 'is_blacklisted' => true,
        ]);

        $this->actingAs($user)->from(route('products.show', $product))->post(route('products.movements.store', $product), [
            'type' => 'exit', 'client_id' => $client->id, 'quantity' => 1, 'occurred_at' => now()->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('products.show', $product))->assertSessionHasErrors('client_id');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 10]);
    }

    public function test_product_page_lists_active_clients_for_a_stock_operation(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['user_id' => $user->id, 'name' => 'Fournitures']);
        $product = Product::create([
            'user_id' => $user->id, 'category_id' => $category->id, 'name' => 'Carton',
            'reference' => 'CRT-003', 'unit_price' => 4.5, 'stock_quantity' => 10, 'alert_threshold' => 2,
        ]);
        Client::create([
            'user_id' => $user->id, 'reference' => 'CLI-ACT', 'client_type' => 'company',
            'name' => 'Client actif', 'email' => 'active@example.test',
        ]);
        Client::create([
            'user_id' => $user->id, 'reference' => 'CLI-BLK', 'client_type' => 'company',
            'name' => 'Client blacklisté', 'email' => 'blocked@example.test', 'is_blacklisted' => true,
        ]);

        $this->actingAs($user)->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('CLI-ACT')
            ->assertDontSee('CLI-BLK')
            ->assertSee(route('clients.index'));
    }

    public function test_an_owner_can_export_only_its_inventory_data_to_excel(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownerCategory = Category::create(['user_id' => $owner->id, 'name' => 'Informatique']);
        $otherCategory = Category::create(['user_id' => $otherUser->id, 'name' => 'Privé']);
        $product = Product::create([
            'user_id' => $owner->id, 'category_id' => $ownerCategory->id, 'name' => 'Clavier',
            'reference' => 'CLV-001', 'unit_price' => 180, 'stock_quantity' => 8, 'alert_threshold' => 2,
        ]);
        Product::create([
            'user_id' => $otherUser->id, 'category_id' => $otherCategory->id, 'name' => 'Produit privé',
            'reference' => 'PRV-001', 'unit_price' => 10, 'stock_quantity' => 1, 'alert_threshold' => 1,
        ]);
        StockMovement::create([
            'user_id' => $owner->id, 'product_id' => $product->id, 'type' => 'entry',
            'quantity' => 8, 'reason' => 'Stock initial', 'occurred_at' => now(),
        ]);

        $response = $this->actingAs($owner)->get(route('exports.inventory'));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('StockManager_export_', (string) $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('PK', $response->streamedContent());
    }
}
