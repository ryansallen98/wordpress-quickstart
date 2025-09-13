<div x-data="themeToggle()" x-init="init()" class="inline-flex items-center">
    <button type="button" x-on:click="toggle()" x-bind:aria-pressed="theme === 'dark'"
        x-bind:aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
        class="btn btn-ghost btn-icon">

        <x-heroicon-s-sun x-cloak x-show="theme === 'light'" class="size-5" />
        <x-heroicon-s-moon x-cloak x-show="theme === 'dark'" class="size-5" />

        <span class="sr-only">(theme toggle)</span>
    </button>
</div>

@pushOnce('scripts')
<script>
    console.log('Footer scripts pushed');
    function themeToggle() {
        const COOKIE_NAME = 'theme';
        const COOKIE_MAX_AGE = 60 * 60 * 24 * 365; // 1 year

        function readCookie(name) {
            return document.cookie.split('; ').reduce((acc, part) => {
                const [k, v] = part.split('=');
                return k === name ? decodeURIComponent(v) : acc;
            }, null);
        }

        function writeCookie(name, value, opts = {}) {
            const { maxAge = COOKIE_MAX_AGE, path = '/', sameSite = 'Lax' } = opts;
            let cookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)}; Max-Age=${maxAge}; Path=${path}; SameSite=${sameSite}`;
            // Add Secure automatically on HTTPS
            try { if (location.protocol === 'https:') cookie += '; Secure'; } catch (e) { }
            document.cookie = cookie;
        }

        function applyTheme(t) {
            const root = document.documentElement;
            root.classList.remove('light', 'dark');
            root.classList.add(t);
        }

        return {
            theme: 'light',
            init() {
                const html = document.documentElement;
                this.theme = html.classList.contains('dark') ? 'dark' : 'light';
            },
            toggle() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                applyTheme(this.theme);
                writeCookie(COOKIE_NAME, this.theme);
            },
        };
    }
</script>
@endpushOnce