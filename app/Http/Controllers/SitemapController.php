<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\RoomType;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $pages = ['home', 'rooms.index', 'services', 'gallery', 'about', 'contact', 'booking.create'];
        $urls = [];

        foreach (SetLocale::LOCALES as $locale) {
            foreach ($pages as $page) {
                $urls[] = route($page, ['locale' => $locale]);
            }
            foreach (RoomType::active()->pluck('slug') as $slug) {
                $urls[] = route('rooms.show', ['locale' => $locale, 'roomType' => $slug]);
            }
        }

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
