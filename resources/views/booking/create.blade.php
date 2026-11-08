@php
    $nightsLabels = collect(range(1, 31))->mapWithKeys(fn ($n) => [$n => trans_choice('site.booking.nights', $n, ['count' => $n])]);
@endphp
<x-layout :title="__('site.booking.title')">
    <x-page-header :title="__('site.booking.title')" :subtitle="__('site.booking.subtitle')" />

    <section class="container-x py-12">
        <form method="post" action="{{ route('booking.store') }}" novalidate
              x-data="bookingForm({
                  defaults: @js($defaults),
                  quoteUrl: @js(route('booking.quote')),
                  csrf: @js(csrf_token()),
                  locale: @js($locale),
                  today: @js(today()->toDateString()),
                  nightsLabels: (n) => (@js($nightsLabels))[n] ?? n,
              })"
              class="grid gap-8 lg:grid-cols-[1fr_380px]">
            @csrf
            <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

            <div class="space-y-8">
                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                        <ul class="list-disc space-y-1 ps-5">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Stay --}}
                <fieldset class="rounded-2xl border border-green-line p-6">
                    <legend class="px-2 text-xl font-semibold text-green">{{ __('site.booking.stay') }}</legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="sm:col-span-2">
                            <span class="label">{{ __('site.booking.room_type') }}</span>
                            <select name="room_type_id" x-model="form.room_type_id" class="field" required>
                                <option value="">{{ __('site.booking.choose_room') }}</option>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->tr('name') }} — {{ __('site.common.from') }} {{ number_format((float) $room->base_price) }} {{ __('site.common.sar') }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span class="label">{{ __('site.common.check_in') }}</span>
                            <input type="date" name="check_in" x-model="form.check_in" :min="@js(today()->toDateString())" class="field" required>
                        </label>
                        <label>
                            <span class="label">{{ __('site.common.check_out') }}</span>
                            <input type="date" name="check_out" x-model="form.check_out" :min="minCheckout()" class="field" required>
                        </label>
                        <div class="grid grid-cols-3 gap-3 sm:col-span-2">
                            <label>
                                <span class="label">{{ __('site.common.rooms_count') }}</span>
                                <select name="rooms" x-model.number="form.rooms" class="field">
                                    @for ($i = 1; $i <= $maxRooms; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </label>
                            <label>
                                <span class="label">{{ __('site.common.adults') }}</span>
                                <input type="number" name="adults" x-model.number="form.adults" min="1" max="30" class="field" required>
                            </label>
                            <label>
                                <span class="label">{{ __('site.common.children') }}</span>
                                <input type="number" name="children" x-model.number="form.children" min="0" max="20" class="field">
                            </label>
                        </div>
                    </div>
                </fieldset>

                {{-- Guest --}}
                <fieldset class="rounded-2xl border border-green-line p-6">
                    <legend class="px-2 text-xl font-semibold text-green">{{ __('site.booking.guest') }}</legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="sm:col-span-2">
                            <span class="label">{{ __('site.booking.name') }}</span>
                            <input type="text" name="guest_name" value="{{ old('guest_name') }}" class="field" autocomplete="name" required maxlength="120">
                        </label>
                        <label>
                            <span class="label">{{ __('site.booking.phone') }}</span>
                            <input type="tel" name="guest_phone" value="{{ old('guest_phone') }}" class="field" dir="ltr" autocomplete="tel" placeholder="05xxxxxxxx" required>
                        </label>
                        <label>
                            <span class="label">{{ __('site.booking.email') }} <span class="text-muted">{{ __('site.booking.optional') }}</span></span>
                            <input type="email" name="guest_email" value="{{ old('guest_email') }}" class="field" dir="ltr" autocomplete="email">
                        </label>
                        <label>
                            <span class="label">{{ __('site.booking.nationality') }} <span class="text-muted">{{ __('site.booking.optional') }}</span></span>
                            <input type="text" name="guest_nationality" value="{{ old('guest_nationality') }}" class="field" maxlength="60">
                        </label>
                        <label class="sm:col-span-2">
                            <span class="label">{{ __('site.booking.notes') }} <span class="text-muted">{{ __('site.booking.optional') }}</span></span>
                            <textarea name="notes" rows="3" class="field" maxlength="1000">{{ old('notes') }}</textarea>
                        </label>
                    </div>
                </fieldset>
            </div>

            {{-- Summary --}}
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl bg-green-deep p-6 text-white">
                    <h2 class="text-2xl text-gold">{{ __('site.booking.summary') }}</h2>

                    <p x-show="!quote && !error && !loading" class="mt-4 text-sm text-white/70">{{ __('site.booking.pick_dates') }}</p>
                    <p x-show="loading" x-cloak class="mt-4 text-sm text-white/70">…</p>
                    <p x-show="error" x-cloak x-text="error" class="mt-4 rounded-lg bg-red-500/15 p-3 text-sm text-red-100"></p>

                    <template x-if="quote && !loading">
                        <div class="mt-4 space-y-3 text-sm">
                            <p :class="quote.available ? 'text-gold-soft' : 'text-red-200'" class="font-semibold"
                               x-text="quote.available ? @js(__('site.booking.available', ['count' => '__N__'])).replace('__N__', quote.units_left) : @js(__('site.booking.unavailable'))"></p>
                            <div class="flex justify-between border-t border-white/15 pt-3">
                                <span><span x-text="nightsLabel"></span> <span x-show="quote.rooms > 1" x-text="@js(__('site.booking.rooms_x', ['count' => '__N__'])).replace('__N__', quote.rooms)"></span></span>
                                <span><span x-text="money(quote.subtotal)"></span> {{ __('site.common.sar') }}</span>
                            </div>
                            <div class="flex justify-between text-white/75">
                                <span x-text="@js(__('site.booking.vat', ['rate' => '__R__'])).replace('__R__', quote.vat_rate)"></span>
                                <span><span x-text="money(quote.vat_amount)"></span> {{ __('site.common.sar') }}</span>
                            </div>
                            <div class="flex items-end justify-between border-t border-white/15 pt-3">
                                <span class="font-semibold">{{ __('site.booking.total') }}</span>
                                <span class="text-2xl font-semibold text-gold"><span x-text="money(quote.total)"></span> <span class="text-sm">{{ __('site.common.sar') }}</span></span>
                            </div>
                        </div>
                    </template>

                    <button type="submit" class="btn-gold mt-6 w-full" :disabled="loading || (quote && !quote.available)">{{ __('site.booking.submit') }}</button>
                    <p class="mt-3 text-xs leading-5 text-white/60">{{ __('site.booking.estimate_note') }}</p>
                    <p class="mt-2 text-xs text-white/60">{{ __('site.booking.agree') }} <a href="{{ route('terms') }}" class="underline hover:text-gold">{{ __('site.nav.terms') }}</a>.</p>
                </div>
            </aside>
        </form>
    </section>
</x-layout>
