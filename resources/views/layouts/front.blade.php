<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="author" content="text/html" />

    <meta name="theme-color" content="#00A47D">
    <meta name="msapplication-navbutton-color" content="#00A47D">
    <meta name="apple-mobile-web-app-status-bar-style" content="#00A47D">

    <title>Zedville - Financial Skills for Your Future</title>
    <link sizes='57x57' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='114x114' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='72x72' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='144x144' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='60x60' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='120x120' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='76x76' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='152x152' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='180x180' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='192x192' href='{{ asset('asset/front/images/favicon.png') }}' rel='icon' type='image/png'>
    <link sizes='160x160' href='{{ asset('asset/front/images/favicon.png') }}' rel='icon' type='image/png'>
    <link sizes='96x96' href='{{ asset('asset/front/images/favicon.png') }}' rel='icon' type='image/png'>
    <link sizes='16x16' href='{{ asset('asset/front/images/favicon.png') }}' rel='icon' type='image/png'>
    <link sizes='32x32' href='{{ asset('asset/front/images/favicon.png') }}' rel='icon' type='image/png'>
    <!-- Tailwind CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.4/dist/tailwind.min.css" rel="stylesheet">

    <!-- Tailwind Typography plugin via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/@tailwindcss/typography@0.4.1/dist/typography.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet">

    <link href="{{ asset('asset/front/css/theme_plugin.css') }}?ver={{ rand(111, 999) }}" rel="stylesheet" />
    <link href="{{ asset('asset/front/css/theme_style.css') }}?ver={{ rand(111, 999) }}" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            content: [
                "./index.html",
                "./src/**/*.{js,ts,jsx,tsx}",
            ],
            theme: {
                extend: {
                    colors: {
                        themegreen: '#00A47D',
                        themeyellow: '#FFF5D4',
                    },
                },
            },
        };
    </script>
</head>

<body>

    <!-- Header -->

    <header class="themeHeader">
        <div class="container mx-auto">
            <nav class="py-3.5" aria-label="Global">
                <div class="flex lg:flex-1">
                    <a href="{{ url('/') }}" class="-m-1.5 p-1.5">
                        <span class="sr-only">Zedville</span>
                        <img class="responsive" src="{{ asset('asset/front/images/logo.svg') }}" alt="" />
                    </a>
                </div>
                <div class="flex lg:hidden">
                    <button type="button"
                        class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700">
                        <span class="sr-only">Open main menu</span>
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                            aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
                <ul class="hidden lg:flex lg:gap-x-12 themenav">
                    <li class="navlits"><a href="#" class="navitems">About</a></li>
                    <li class="navlits"><a href="{{ route('how-it-work') }}" class="navitems">How It Works</a></li>
                    <li class="navlits"><a href="{{ route('faq') }}" class="navitems">FAQ</a></li>
                    <li class="navlits"><a href="{{ route('contact') }}" class="navitems">Contact</a></li>
                </ul>
                <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:gap-x-3 rightBtns">
                    <a href="{{ route('register') }}" class="secondaryBtn flex items-center">Register</a>
                    <a href="{{ route('login') }}" class="themeBtn flex items-center gap-x-1">Log in <span class="icon">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 9.5H15M15 9.5L9.72 5M15 9.5L9.72 14" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span></a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Header End -->

    <section class="themebodySection">

        @yield('content')

    </section>
    <footer class="themeFooter">
        <div class="mx-auto container">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-3">
                <div class="footerNavWidget py-6 lg:py-10">
                    <h4 class="mb-6 text-base font-semibold text-white mb-6 dark:text-white">Pages</h4>
                    <ul
                        class="text-sm/6 text-white flex flex-wrap gap-x-12 gap-y-2 dark:text-gray-400  font-normal fnavlists">
                        <li>
                            <a href="#">About us</a>
                        </li>
                        <li>
                            <a href="{{ route('how-it-work') }}">How It Works</a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}">Contact us</a>
                        </li>
                        <li>
                            <a href="#">Blogs</a>
                        </li>
                    </ul>
                </div>
                <div class="footerNavWidget py-6 lg:py-10 lg:pl-16">
                    <h4 class="mb-6 text-base font-semibold text-white mb-6 dark:text-white">Social</h4>
                    <ul
                        class="text-sm/6 text-white flex flex-wrap gap-x-14 gap-y-2 dark:text-gray-400 font-normal fnavlists">
                        <li>
                            <a href="#">Youtube</a>
                        </li>
                        <li>
                            <a href="#">Linkedin</a>
                        </li>
                    </ul>
                </div>
                <div class="footerNavWidget fwlocation py-6 lg:py-10 md:pl-16">
                    <h4 class="mb-6 text-base font-semibold text-white mb-6 dark:text-white">Contact</h4>
                    <div class="fwlocLists">
                        <div class=" mb-6">
                            <a href="mailto:info@example.com"
                                class="flex gap-x-2 items-center content text-sm text-white font-normal">
                                <span class="icon">
                                    <svg width="20" height="22" viewBox="0 0 20 22" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5.48047 7.77539L10.3192 10.356L15.1579 7.77539" stroke="currentColor"
                                            stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round" />
                                        <path
                                            d="M1.93164 16.0711V5.90332C1.93164 4.79875 2.82707 3.90332 3.93164 3.90332H16.5994C17.7039 3.90332 18.5994 4.79875 18.5994 5.90332V16.0711C18.5994 17.1756 17.7039 18.0711 16.5994 18.0711H3.93164C2.82707 18.0711 1.93164 17.1756 1.93164 16.0711Z"
                                            stroke="currentColor" stroke-width="0.8" />
                                    </svg>
                                </span>
                                <span class="txt">info@example.com</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footerBottom">
                <div class="py-6 md:flex md:items-center md:justify-between">
                    <span class="text-sm text-white dark:text-gray-300 sm:text-center">© 2025 Zed Ville. All rights
                        reserved.
                    </span>
                    <div class="text-sm sm:text-center flex gap-4">
                        <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                        <a href="{{ route('terms-conditions') }}">Terms & Conditions</a>
                        <span>Designed by <a href="#" target="_blank">Nexovah</a></span>
                    </div>
                </div>
            </div>
        </div>


    </footer>
    <main class="zv-main bg-white flex-1 max-h-full pt-5 pb-6 px-6 overflow-hidden overflow-y-auto">

        @if(session('success'))
            <div
                class="fixed inset-0 alert-box flex items-center justify-center z-50 transition transform duration-300 ease-out translate-y-0 opacity-100 backdrop-blur-sm">
                <div class="flex items-center w-full justify-between max-w-sm p-4 mb-2  bg-green-100 border  border-green-500 rounded-lg shadow-md"
                    role="alert">
                    <div class="flex items-start w-full">
                        <!-- ✅ Left icon -->
                        <div
                            class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-green-100 rounded-full mt-0.5 mr-3">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <!-- 🧾 Middle content -->
                        <div class="flex-1">
                            <h3 class="text-gray-900 font-semibold text-lg">Successful</h3>
                            <p class="text-gray-600 text-lg mt-0.5">
                                {{ session('success') }}
                            </p>
                        </div>
                        <!-- ❌ Right close button -->
                        <button onclick="this.closest('.alert-box').remove();" style="font-size:24px" type="button"
                            class="ml-3 text-gray-700 hover:text-gray-600  leading-none">
                            ×
                        </button>
                    </div>
                </div>
            </div>
        @endif
        </div>
    </main>

    <script src="{{ asset('asset/front/js/themePlugin.js') }}?ver={{ rand(111, 999) }}" type="text/javascript"></script>
    <script src="{{ asset('asset/front/js/themeScript.js') }}?ver={{ rand(111, 999) }}" type="text/javascript"></script>
    <!-- resources/views/layouts/app.blade.php -->
    <!-- other scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Select all Bootstrap or custom alerts
            const alerts = document.querySelectorAll('.alert-box');

            alerts.forEach(alert => {
                setTimeout(() => {
                    // Add fade-out animation if using Bootstrap
                    alert.classList.add('fade');
                    alert.classList.remove('show');

                    // Remove from DOM after transition
                    setTimeout(() => alert.remove(), 500);
                }, 5000); // 5 seconds
            });
        });
        document.addEventListener("DOMContentLoaded", function () {
            try {
                const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
                document.querySelectorAll('.timezone-field').forEach(el => {
                    if (!el.value) { // don't overwrite if already set
                        el.value = tz;
                    }
                });
            } catch (e) {
                // fallback to UTC silently
                document.querySelectorAll('.timezone-field').forEach(el => {
                    if (!el.value) el.value = 'UTC';
                });
            }
        });
    </script>

</body>

</html>