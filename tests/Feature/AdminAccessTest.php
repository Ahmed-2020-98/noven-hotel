<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Bookings\Pages\ViewBooking;
use App\Mail\GuestBookingMail;
use App\Models\Booking;
use App\Models\User;
use Database\Seeders\HotelContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HotelContentSeeder::class);
    }

    private function user(UserRole $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** @return array<string, array{UserRole, string, int}> */
    public static function access(): array
    {
        return [
            'admin dashboard' => [UserRole::Admin, '/admin', 200],
            'admin users' => [UserRole::Admin, '/admin/users', 200],
            'admin settings' => [UserRole::Admin, '/admin/manage-settings', 200],
            'admin rooms' => [UserRole::Admin, '/admin/room-types', 200],
            'admin calendar' => [UserRole::Admin, '/admin/occupancy-calendar', 200],
            'reception bookings' => [UserRole::Reception, '/admin/bookings', 200],
            'reception calendar' => [UserRole::Reception, '/admin/occupancy-calendar', 200],
            'reception cannot manage users' => [UserRole::Reception, '/admin/users', 403],
            'reception cannot edit site texts' => [UserRole::Reception, '/admin/manage-settings', 403],
            'editor gallery' => [UserRole::Editor, '/admin/gallery-images', 200],
            'editor settings' => [UserRole::Editor, '/admin/manage-settings', 200],
            'editor cannot see bookings' => [UserRole::Editor, '/admin/bookings', 403],
        ];
    }

    #[DataProvider('access')]
    public function test_roles_see_only_their_sections(UserRole $role, string $url, int $status): void
    {
        $this->actingAs($this->user($role))->get($url)->assertStatus($status);
    }

    public function test_inactive_user_cannot_enter_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin, 'is_active' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_booking_pages_render_for_reception(): void
    {
        $booking = Booking::factory()->pending()->create();
        $user = $this->user(UserRole::Reception);

        $this->actingAs($user)->get(BookingResource::getUrl('view', ['record' => $booking]))->assertOk()->assertSee($booking->reference);
        $this->actingAs($user)->get(BookingResource::getUrl('edit', ['record' => $booking]))->assertOk();
        $this->actingAs($user)->get(BookingResource::getUrl('create'))->assertOk();
    }

    public function test_confirming_a_booking_logs_and_emails_the_guest(): void
    {
        Mail::fake();
        $booking = Booking::factory()->pending()->create(['guest_email' => 'guest@example.com']);
        $this->actingAs($this->user(UserRole::Reception));

        Livewire::test(ViewBooking::class, ['record' => $booking->getRouteKey()])
            ->callAction('to_confirmed');

        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
        $this->assertNull($booking->fresh()->expires_at);
        $this->assertDatabaseHas('booking_status_logs', ['booking_id' => $booking->id, 'to_status' => 'confirmed']);
        Mail::assertSent(GuestBookingMail::class, fn ($mail) => $mail->kind === 'confirmed');
    }
}
