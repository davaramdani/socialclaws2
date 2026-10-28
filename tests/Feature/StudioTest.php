<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\CartItem;
use App\Models\Gallery;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudioTest extends TestCase
{
    use RefreshDatabase;

    private function customer(string $email = 'nail@example.test'): User
    {
        return User::create(['full_name' => 'Naila Putri', 'email' => $email, 'address' => 'Jakarta', 'password' => 'SecretPass123']);
    }

    private function service(int $price = 150000): Service
    {
        return Service::create(['name' => 'Signature Manicure', 'description' => 'Perawatan kuku dengan gel polish.', 'price' => $price, 'image' => 'images/nail-art.jpg', 'duration_minutes' => 60, 'category' => 'signature']);
    }

    private function bookingData(string $type = 'cart'): array
    {
        return ['checkout_type' => $type, 'customer_name' => 'Naila Putri', 'customer_address' => 'Jakarta Selatan', 'phone' => '081211729515', 'appointment_date' => now()->addDay()->toDateString(), 'appointment_time' => '10:00'];
    }

    public function test_public_catalog_pages_render_seeded_data(): void
    {
        $this->seed();
        foreach (['/', '/services', '/gallery', '/reviews', '/login', '/register', '/admin/login'] as $uri) {
            $this->get($uri)->assertOk()->assertSee('SocialClaws');
        }
        $this->get('/services?q=Signature')->assertOk()->assertSee('Signature Manicure')->assertDontSee('Gel Extension');
        $this->get('/gallery?category=minimal')->assertOk()->assertSee('Soft &amp; Sculpted', false)->assertDontSee('Cherry on Top');
    }

    public function test_migration_adopts_existing_tables_without_replacing_records(): void
    {
        $user = $this->customer();
        $service = $this->service();
        $migration = require database_path('migrations/2026_10_28_000001_create_socialclaws_tables.php');
        $migration->up();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => $user->email]);
        $this->assertDatabaseHas('services', ['id' => $service->id, 'price' => 150000]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_logs_in_and_hashes_password(): void
    {
        $this->post('/register', ['full_name' => 'Naila Putri', 'email' => 'naila@example.test', 'address' => 'Jakarta', 'password' => 'SecretPass123', 'password_confirmation' => 'SecretPass123'])->assertRedirect(route('home'));
        $user = User::where('email', 'naila@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('SecretPass123', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_legacy_bcrypt_credentials_still_log_in(): void
    {
        $user = $this->customer();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'SecretPass123'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_guests_and_regular_customers_cannot_access_admin(): void
    {
        $this->get('/cart')->assertRedirect(route('login'));
        $this->post('/cart', ['item_type' => 'service', 'item_id' => 1])->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->actingAs($this->customer())->get('/admin/services')->assertRedirect(route('admin.login'));
        $this->delete('/admin/users/1')->assertRedirect(route('admin.login'));
    }

    public function test_cart_merges_quantities_and_enforces_ownership_and_limits(): void
    {
        $service = $this->service();
        $owner = $this->customer();
        $other = $this->customer('other@example.test');
        $this->actingAs($owner)->post('/cart', ['item_type' => 'service', 'item_id' => $service->id, 'quantity' => 2])->assertSessionHasNoErrors();
        $this->post('/cart', ['item_type' => 'service', 'item_id' => $service->id])->assertSessionHasNoErrors();
        $item = CartItem::firstOrFail();
        $this->assertSame(3, $item->quantity);
        $this->assertDatabaseCount('cart', 1);
        $this->post('/cart', ['item_type' => 'service', 'item_id' => $service->id, 'quantity' => 20])->assertSessionHasErrors('quantity');
        $this->actingAs($other)->patch('/cart/'.$item->id, ['quantity' => 5])->assertNotFound();
        $this->delete('/cart/'.$item->id)->assertNotFound();
        $this->assertSame(3, $item->fresh()->quantity);
        $this->actingAs($owner)->patch('/cart/'.$item->id, ['quantity' => 0])->assertSessionHasErrors('quantity');
        $this->patch('/cart/'.$item->id, ['quantity' => 2])->assertSessionHasNoErrors();
        $this->get('/cart')->assertOk()->assertSee('Signature Manicure');
        $this->delete('/cart/'.$item->id)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('cart', 0);
    }

    public function test_invalid_cart_item_type_is_rejected(): void
    {
        $this->actingAs($this->customer())->post('/cart', ['item_type' => 'users', 'item_id' => 1])->assertSessionHasErrors('item_type');
        $this->assertDatabaseCount('cart', 0);
    }

    public function test_cart_booking_recalculates_prices_and_clears_cart(): void
    {
        $user = $this->customer();
        $service = $this->service(175000);
        CartItem::create(['user_id' => $user->id, 'item_id' => $service->id, 'item_type' => 'service', 'quantity' => 2]);
        $this->actingAs($user)->get('/checkout')->assertOk()->assertSee('Signature Manicure');
        $response = $this->post('/bookings', [...$this->bookingData(), 'total_price' => 1, 'booking_details' => 'forged details', 'user_id' => 999]);
        $booking = Booking::firstOrFail();
        $response->assertRedirect(route('booking.success', $booking));
        $this->assertSame(350000, $booking->total_price);
        $this->assertSame($user->id, $booking->user_id);
        $this->assertStringContainsString('Signature Manicure', $booking->booking_details);
        $this->assertStringNotContainsString('forged', $booking->booking_details);
        $this->assertSame('pending', $booking->status);
        $this->assertDatabaseCount('cart', 0);
        $this->get(route('booking.success', $booking))->assertOk()->assertSee('https://wa.me/', false);
        $this->get('/bookings')->assertOk()->assertSee($booking->reference);
    }

    public function test_direct_gallery_booking_keeps_existing_cart(): void
    {
        $user = $this->customer();
        $service = $this->service();
        CartItem::create(['user_id' => $user->id, 'item_id' => $service->id, 'item_type' => 'service', 'quantity' => 1]);
        $design = Gallery::create(['name' => 'Cherry Red', 'description' => 'Detail cherry.', 'image' => 'images/nail-art.jpg', 'price' => 225000, 'duration_minutes' => 90, 'category' => 'nail art']);
        $this->actingAs($user)->get('/checkout?direct_checkout=true&item_type=gallery&item_id='.$design->id)->assertOk()->assertSee('Cherry Red');
        $this->post('/bookings', [...$this->bookingData('direct'), 'item_type' => 'gallery', 'item_id' => $design->id])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', ['user_id' => $user->id, 'total_price' => 225000]);
        $this->assertDatabaseCount('cart', 1);
    }

    public function test_empty_and_past_bookings_are_rejected(): void
    {
        $this->actingAs($this->customer())->post('/bookings', $this->bookingData())->assertSessionHasErrors('checkout_type');
        $this->post('/bookings', [...$this->bookingData(), 'appointment_date' => now()->subDay()->toDateString()])->assertSessionHasErrors('appointment_date');
        $this->post('/bookings', [...$this->bookingData(), 'appointment_time' => '03:00'])->assertSessionHasErrors('appointment_time');
        $this->post('/bookings', [...$this->bookingData(), 'phone' => '--------'])->assertSessionHasErrors('phone');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_confirmation_is_scoped_to_its_owner(): void
    {
        $owner = $this->customer();
        $booking = Booking::create(['user_id' => $owner->id, 'customer_name' => $owner->full_name, 'customer_address' => 'Jakarta', 'booking_details' => 'Manicure', 'total_price' => 150000, 'status' => 'pending']);
        $this->actingAs($this->customer('other@example.test'))->get(route('booking.success', $booking))->assertNotFound();
        $this->get('/bookings')->assertOk()->assertDontSee($booking->reference);
    }

    public function test_review_validates_rating_and_prevents_duplicate_reviews(): void
    {
        $this->actingAs($this->customer())->post('/reviews', ['rating' => 6, 'review_text' => 'Hasilnya cantik dan rapi.'])->assertSessionHasErrors('rating');
        $this->post('/reviews', ['rating' => 5, 'review_text' => 'Hasilnya cantik dan rapi.'])->assertRedirect(route('reviews'));
        $this->assertDatabaseHas('reviews', ['user_name' => 'Naila Putri', 'rating' => 5]);
        $this->post('/reviews', ['rating' => 4, 'review_text' => 'Ulasan kedua tidak boleh.'])->assertSessionHasErrors('review_text');
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_admin_login_and_catalog_management(): void
    {
        $admin = Admin::create(['username' => 'studio-test', 'password' => 'SecretPass123']);
        $this->post('/admin/login', ['username' => $admin->username, 'password' => 'SecretPass123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->get('/admin')->assertOk();
        foreach (['services', 'gallery', 'users', 'bookings', 'reviews'] as $resource) {
            $this->get('/admin/'.$resource)->assertOk();
        }
        $this->post('/admin/services', ['name' => 'Test Manicure', 'description' => 'Layanan untuk pengujian.', 'price' => 100000, 'duration_minutes' => 60, 'category' => 'signature'])->assertRedirect(route('admin.index', 'services'));
        $service = Service::firstOrFail();
        $this->put('/admin/services/'.$service->id, ['name' => 'Updated Manicure', 'description' => 'Detail layanan diperbarui.', 'price' => 125000, 'duration_minutes' => 90, 'category' => 'signature'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'Updated Manicure', 'price' => 125000]);
        $this->delete('/admin/services/'.$service->id)->assertRedirect(route('admin.index', 'services'));
        $this->assertDatabaseCount('services', 0);
    }

    public function test_admin_status_change_and_delete_keep_cart_consistent(): void
    {
        $admin = Admin::create(['username' => 'studio-test', 'password' => 'SecretPass123']);
        $user = $this->customer();
        $service = $this->service();
        CartItem::create(['user_id' => $user->id, 'item_id' => $service->id, 'item_type' => 'service', 'quantity' => 1]);
        $booking = Booking::create(['user_id' => $user->id, 'customer_name' => $user->full_name, 'customer_address' => 'Jakarta', 'booking_details' => 'Manicure', 'total_price' => 150000, 'status' => 'pending']);
        $this->actingAs($admin, 'admin')->put('/admin/bookings/'.$booking->id, ['status' => 'completed'])->assertSessionHasNoErrors();
        $this->assertSame('completed', $booking->fresh()->status);
        $this->delete('/admin/services/'.$service->id)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('cart', 0);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'total_price' => 150000]);
    }

    public function test_legacy_urls_redirect_to_laravel_preserving_checkout_query(): void
    {
        $this->get('/services.php')->assertRedirect('/services');
        $this->get('/checkout.php?direct_checkout=true&item_id=2&item_type=service')->assertRedirect('/checkout?direct_checkout=true&item_id=2&item_type=service');
        $this->get('/admin/manage_services.php')->assertRedirect('/admin/services');
    }
}
