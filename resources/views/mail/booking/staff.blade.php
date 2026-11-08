@php($b = $booking)
<x-mail::message>
# طلب حجز جديد من الموقع

<x-mail::table>
| | |
|:--|:--|
| رقم الطلب | **{{ $b->reference }}** |
| الضيف | {{ $b->guest_name }} |
| الجوال | {{ $b->guest_phone }} |
| البريد | {{ $b->guest_email ?: '—' }} |
| الغرفة | {{ $b->roomType->name_ar }} × {{ $b->rooms }} |
| الإقامة | {{ $b->check_in->format('Y-m-d') }} ← {{ $b->check_out->format('Y-m-d') }} ({{ $b->nights }} ليلة) |
| الضيوف | {{ $b->adults }} بالغ، {{ $b->children }} طفل |
| الإجمالي التقديري | **{{ number_format((float) $b->total, 2) }} ر.س** |
| ملاحظات | {{ $b->notes ?: '—' }} |
</x-mail::table>

الطلب محجوز مؤقتًا حتى {{ $b->expires_at?->format('Y-m-d H:i') }} — أكّده من لوحة التحكم.

<x-mail::button :url="route('filament.admin.resources.bookings.view', $b)">
فتح الطلب في لوحة التحكم
</x-mail::button>
</x-mail::message>
