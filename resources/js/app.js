import Alpine from 'alpinejs';

/**
 * Booking form: fetches a live availability + price quote whenever the stay changes.
 */
Alpine.data('bookingForm', (config) => ({
    form: { ...config.defaults },
    quote: null,
    loading: false,
    error: null,
    timer: null,

    init() {
        this.$watch('form', () => this.schedule(), { deep: true });
        this.schedule();
    },

    get nightsLabel() {
        return this.quote ? config.nightsLabels(this.quote.nights) : '';
    },

    minCheckout() {
        if (!this.form.check_in) return config.today;
        const d = new Date(this.form.check_in + 'T00:00:00');
        d.setDate(d.getDate() + 1);
        return d.toISOString().slice(0, 10);
    },

    schedule() {
        if (this.form.check_in && this.form.check_out && this.form.check_out <= this.form.check_in) {
            this.form.check_out = this.minCheckout();
        }
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.fetchQuote(), 300);
    },

    async fetchQuote() {
        this.error = null;
        if (!this.form.room_type_id || !this.form.check_in || !this.form.check_out) {
            this.quote = null;
            return;
        }
        this.loading = true;
        try {
            const res = await fetch(config.quoteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': config.csrf,
                },
                body: JSON.stringify(this.form),
            });
            const data = await res.json();
            if (res.ok) {
                this.quote = data;
            } else {
                this.quote = null;
                this.error = Object.values(data.errors ?? {})[0]?.[0] ?? data.message;
            }
        } catch (e) {
            this.quote = null;
        } finally {
            this.loading = false;
        }
    },

    money(value) {
        return new Intl.NumberFormat(config.locale === 'ar' ? 'ar-SA-u-nu-latn' : 'en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }).format(value);
    },
}));

window.Alpine = Alpine;
Alpine.start();
