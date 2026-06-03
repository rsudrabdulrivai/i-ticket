<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<script>
    (function() {
        const getTheme = () => {
            const stored = localStorage.getItem('flux.theme');
            if (stored === 'dark' || stored === 'light') return stored;
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };
        const theme = getTheme();
        document.cookie = "theme=" + theme + ";path=/;max-age=31536000;SameSite=Lax";
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>
@fluxAppearance

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'iTicket') : config('app.name', 'iTicket') }}
</title>

<link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
<link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

@vite(['resources/css/app.css', 'resources/js/app.js'])