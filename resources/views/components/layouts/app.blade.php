<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' | '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen font-sans antialiased bg-base-200">
    {{-- The navbar with `sticky` and `full-width` --}}
    <x-nav sticky full-width>
        <x-slot:brand>
            {{-- Drawer toggle for "main-drawer" --}}
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>

            {{-- Brand --}}
            <x-app-brand class="px-5" />
        </x-slot:brand>

        {{-- Right side actions --}}
        <x-slot:actions>
            {{-- User Profile Dropdown --}}
            @if($user = auth()->user())
            <x-dropdown>
                <x-slot:trigger>
                    <x-button label="{{ $user->name }}" icon="o-user" class="btn-ghost btn-sm" />
                </x-slot:trigger>

                <x-menu-item title="Mon Profil" icon="o-user" link="/users/{{ $user->id }}/edit" />
                <x-menu-separator />
                <x-menu-item title="Déconnexion" icon="o-power" link="/logout" />
            </x-dropdown>
            @endif
        </x-slot:actions>
    </x-nav>
    {{-- MAIN --}}
    <x-main with-nav full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- MENU --}}
            <x-menu activate-by-route>
                {{-- User Removed from here --}}


                <x-menu-item title="Traitement Unitaire" icon="o-camera" link="/student" />
                <x-menu-sub title="Gestion par Lot" icon="o-queue-list">
                    <x-menu-item title="1. Années Scolaires" icon="o-calendar" link="/annees-scolaires" />
                    <x-menu-item title="2. Établissements" icon="o-building-library" link="/etablissements" />
                    <x-menu-item title="3. Classes" icon="o-users" link="/classes" />
                </x-menu-sub>

                <x-menu-separator />

                <x-menu-item title="Corrections" icon="o-clipboard-document-check" link="/corrections" />
                <x-menu-item title="À propos" icon="o-information-circle" link="/about" />
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}

        </x-slot:content>
    </x-main>

    <footer class="w-full flex flex-row justify-between items-center px-6 py-3 bg-base-200 text-base-content shadow-inner">
        <div class="flex items-center gap-2">
            <x-icon name="o-camera" class="w-5 h-5" />
            <p class="text-sm">Copyright © {{ date('Y') }} <strong>Venus App</strong></p>
        </div>

        {{-- Live Clock --}}
        <div class="hidden md:block font-mono text-sm" id="live-clock">
            {{-- Javascript will populate this --}}
        </div>

        <div class="flex items-center gap-4">
            {{-- WhatsApp --}}
            <a href="https://wa.me/237697449003" target="_blank" class="hover:text-green-500 transition-colors" title="WhatsApp">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                </svg>
            </a>

            {{-- Gmail --}}
            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=teguiasimo@gmail.com" target="_blank" class="hover:text-red-500 transition-colors" title="Envoyer un email">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z" />
                </svg>
            </a>

            {{-- LinkedIn --}}
            <a href="https://www.linkedin.com/in/teguia-simo-yannick-franck-65a03b155" target="_blank" class="hover:text-blue-600 transition-colors" title="LinkedIn">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                </svg>
            </a>

            {{-- Facebook --}}
            <a href="https://www.facebook.com/profile.php?id=61585313233163" target="_blank" class="hover:text-blue-500 transition-colors" title="Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                </svg>
            </a>
        </div>
    </footer>

    {{-- Universal Live Clock Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateClock() {
                const now = new Date();
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                const formattedTime = now.toLocaleDateString('fr-FR', options);
                // Capitalize first letter
                const finalString = formattedTime.charAt(0).toUpperCase() + formattedTime.slice(1);

                const clockElement = document.getElementById('live-clock');
                if (clockElement) {
                    clockElement.innerText = finalString;
                }
            }
            setInterval(updateClock, 1000);
            updateClock(); // Initial call
        });
    </script>

    {{-- TOAST area --}}
    <x-toast />
</body>

</html>