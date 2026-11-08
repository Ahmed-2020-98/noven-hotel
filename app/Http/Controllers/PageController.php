<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\GalleryImage;
use App\Models\RoomType;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'rooms' => RoomType::active()->with('amenities')->get(),
            'services' => Service::active()->take(6)->get(),
            'gallery' => GalleryImage::active()->take(8)->get(),
            'faqs' => Faq::active()->get(),
        ]);
    }

    public function about(): View
    {
        return view('pages.about', [
            'about' => Setting::text('about'),
            'policies' => Setting::text('policies'),
            'services' => Service::active()->take(4)->get(),
        ]);
    }

    public function services(): View
    {
        return view('pages.services', ['services' => Service::active()->get(), 'faqs' => Faq::active()->get()]);
    }

    public function gallery(): View
    {
        return view('pages.gallery', ['images' => GalleryImage::active()->get()]);
    }

    public function privacy(): View
    {
        return view('pages.legal', ['title' => __('site.nav.privacy'), 'content' => Setting::text('privacy')]);
    }

    public function terms(): View
    {
        return view('pages.legal', ['title' => __('site.nav.terms'), 'content' => Setting::text('terms')]);
    }
}
