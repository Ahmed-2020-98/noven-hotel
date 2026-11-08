<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Filament\Concerns\RestrictedToRoles;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Website texts, policies, social links and booking rules — stored in the settings table.
 *
 * @property-read Schema $form
 */
class ManageSettings extends Page
{
    use RestrictedToRoles;

    /** Keys only administrators can change. */
    private const BOOKING_KEYS = ['vat_rate', 'weekend_days', 'pending_hold_hours', 'max_rooms_per_booking', 'max_nights', 'notification_emails'];

    protected string $view = 'filament-panels::pages.page';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'نصوص الموقع والإعدادات';

    protected static ?string $title = 'نصوص الموقع والإعدادات';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Editor];
    }

    public function mount(): void
    {
        $values = [];
        foreach ($this->keys() as $key) {
            $values[$key] = Setting::read($key);
        }
        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        $isAdmin = auth()->user()?->isAdmin() ?? false;

        return $schema->statePath('data')->components([
            Tabs::make()->persistTabInQueryString()->tabs([
                Tab::make('الصفحة الرئيسية')->icon(Heroicon::OutlinedHome)->columns(2)->schema([
                    TextInput::make('hero_title_ar')->label('العنوان الرئيسي (عربي)')->placeholder(__('site.home.hero_title', locale: 'ar')),
                    TextInput::make('hero_title_en')->label('Hero title (English)')->placeholder(__('site.home.hero_title', locale: 'en')),
                    TextInput::make('hero_subtitle_ar')->label('النص التعريفي (عربي)')->placeholder(__('site.home.hero_subtitle', locale: 'ar')),
                    TextInput::make('hero_subtitle_en')->label('Hero subtitle (English)')->placeholder(__('site.home.hero_subtitle', locale: 'en')),
                    FileUpload::make('hero_image')->label('صورة الواجهة')->image()->disk('public')->directory('site')
                        ->maxSize(8192)->columnSpanFull()->helperText('صورة عرضية عالية الجودة (1920×1080 تقريبًا).'),
                ]),
                Tab::make('من نحن والسياسات')->icon(Heroicon::OutlinedInformationCircle)->columns(2)->schema([
                    RichEditor::make('about_ar')->label('من نحن (عربي)'),
                    RichEditor::make('about_en')->label('About (English)'),
                    RichEditor::make('policies_ar')->label('سياسات الإقامة (عربي)'),
                    RichEditor::make('policies_en')->label('Stay policies (English)'),
                    RichEditor::make('privacy_ar')->label('سياسة الخصوصية (عربي)'),
                    RichEditor::make('privacy_en')->label('Privacy policy (English)'),
                    RichEditor::make('terms_ar')->label('الشروط والأحكام (عربي)'),
                    RichEditor::make('terms_en')->label('Terms (English)'),
                ]),
                Tab::make('السوشيال ميديا')->icon(Heroicon::OutlinedGlobeAlt)->columns(2)->schema([
                    TextInput::make('social_instagram')->label('Instagram')->url(),
                    TextInput::make('social_x')->label('X (Twitter)')->url(),
                    TextInput::make('social_snapchat')->label('Snapchat')->url(),
                    TextInput::make('social_tiktok')->label('TikTok')->url(),
                ]),
                Tab::make('قواعد الحجز')->icon(Heroicon::OutlinedCalendarDays)->columns(3)->visible($isAdmin)->schema([
                    TextInput::make('vat_rate')->label('نسبة الضريبة')->numeric()->minValue(0)->maxValue(100)->suffix('%')->required(),
                    TextInput::make('pending_hold_hours')->label('مدة حجز الطلب المعلّق')->numeric()->minValue(1)->suffix('ساعة')->required()
                        ->helperText('بعدها يُلغى الطلب تلقائيًا إن لم يُؤكد.'),
                    TextInput::make('max_rooms_per_booking')->label('أقصى عدد غرف في الطلب')->numeric()->minValue(1)->required(),
                    TextInput::make('max_nights')->label('أقصى عدد ليالي')->numeric()->minValue(1)->required(),
                    CheckboxList::make('weekend_days')->label('ليالي الويكند (سعر الويكند)')->columns(4)->columnSpan(2)
                        ->options([0 => 'الأحد', 1 => 'الإثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت']),
                    TagsInput::make('notification_emails')->label('إيميلات استلام الطلبات')->columnSpanFull()
                        ->placeholder('reservations@...')->nestedRecursiveRules(['email'])
                        ->helperText('اتركها فارغة لاستخدام بريد الفندق.'),
                ]),
            ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([Action::make('save')->label('حفظ')->submit('save')->keyBindings(['mod+s'])]),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (! auth()->user()?->isAdmin()) {
            $data = array_diff_key($data, array_flip(self::BOOKING_KEYS));
        }
        if (isset($data['weekend_days'])) {
            $data['weekend_days'] = array_map('intval', $data['weekend_days']);
        }

        Setting::put($data);

        Notification::make()->title('تم الحفظ')->success()->send();
    }

    /** @return list<string> */
    private function keys(): array
    {
        $localized = ['hero_title', 'hero_subtitle', 'about', 'policies', 'privacy', 'terms'];

        return [
            ...collect($localized)->flatMap(fn (string $k) => ["{$k}_ar", "{$k}_en"])->all(),
            'hero_image', 'social_instagram', 'social_x', 'social_snapchat', 'social_tiktok',
            ...self::BOOKING_KEYS,
        ];
    }
}
