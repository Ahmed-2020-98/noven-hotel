<?php

namespace Tests\Feature;

use App\Models\RoomType;
use Database\Seeders\HotelContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HotelContentSeeder::class);
        $this->withoutVite();
    }

    /** @return array<string, array{string, string}> */
    public static function pages(): array
    {
        $cases = [];
        foreach (['ar', 'en'] as $locale) {
            foreach (['', '/about', '/services', '/gallery', '/rooms', '/booking', '/contact', '/privacy', '/terms'] as $path) {
                $cases["{$locale}{$path}"] = [$locale, $path];
            }
        }

        return $cases;
    }

    #[DataProvider('pages')]
    public function test_public_pages_render_in_both_languages(string $locale, string $path): void
    {
        $this->get("/{$locale}{$path}")
            ->assertOk()
            ->assertSee('lang="'.$locale.'"', false)
            ->assertSee($locale === 'ar' ? 'dir="rtl"' : 'dir="ltr"', false);
    }

    public function test_root_redirects_to_a_locale(): void
    {
        $this->get('/', ['Accept-Language' => 'ar-SA,ar;q=0.9'])->assertRedirect('/ar');
        $this->get('/', ['Accept-Language' => 'en-US'])->assertRedirect('/en');
    }

    public function test_unknown_locale_is_not_found(): void
    {
        $this->get('/fr/rooms')->assertNotFound();
    }

    public function test_room_page_shows_localized_name_and_language_switch(): void
    {
        $room = RoomType::where('slug', 'king-suite')->firstOrFail();

        $this->get('/en/rooms/king-suite')->assertOk()->assertSee('King Suite')->assertSee(url('/ar/rooms/king-suite'), false);
        $this->get('/ar/rooms/king-suite')->assertOk()->assertSee($room->name_ar);
    }

    public function test_inactive_room_is_hidden(): void
    {
        RoomType::where('slug', 'king-suite')->update(['is_active' => false]);

        $this->get('/ar/rooms/king-suite')->assertNotFound();
    }

    public function test_contact_form_stores_message_and_rejects_bots(): void
    {
        $this->post('/ar/contact', ['name' => 'Ali', 'phone' => '0501234567', 'message' => 'Hello'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('contact_messages', ['name' => 'Ali']);

        $this->post('/ar/contact', ['name' => 'Bot', 'phone' => '0501234567', 'message' => 'x', 'website' => 'spam'])
            ->assertSessionHasErrors('website');
    }

    public function test_sitemap_lists_both_languages(): void
    {
        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(url('/en/rooms/king-suite'), false)
            ->assertSee(url('/ar/booking'), false);
    }
}
