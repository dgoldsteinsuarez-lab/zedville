<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="author" content="text/html" />

    <meta name="theme-color" content="#222">
    <meta name="msapplication-navbutton-color" content="#222">
    <meta name="apple-mobile-web-app-status-bar-style" content="#222">

    <title>Zedville - Admin</title>
    <link sizes='57x57' href='../images/favicon.png' rel='apple-touch-icon'>
    <link sizes='114x114' href='../images/favicon.png' rel='apple-touch-icon'>
    <link sizes='72x72' href='../images/favicon.png' rel='apple-touch-icon'>
    <link sizes='144x144' href='../images/favicon.png' rel='apple-touch-icon'>
    <link sizes='60x60' href='../images/favicon.png' rel='apple-touch-icon'>
    <link sizes='120x120' href='../images/favicon.png' rel='apple-touch-icon'>
    <link sizes='76x76' href='../images/favicon.png' rel='apple-touch-icon'>
    <link sizes='152x152' href='../images/favicon.png' rel='apple-touch-icon'>
    <link sizes='180x180' href='../images/favicon.png' rel='apple-touch-icon'>
    <link sizes='192x192' href='../images/favicon.png' rel='icon' type='image/png'>
    <link sizes='160x160' href='../images/favicon.png' rel='icon' type='image/png'>
    <link sizes='96x96' href='../images/favicon.png' rel='icon' type='image/png'>
    <link sizes='16x16' href='../images/favicon.png' rel='icon' type='image/png'>
    <link sizes='32x32' href='../images/favicon.png' rel='icon' type='image/png'>
    <!-- Tailwind CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.4/dist/tailwind.min.css" rel="stylesheet">

    <!-- Tailwind Typography plugin via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/@tailwindcss/typography@0.4.1/dist/typography.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link href="../css/theme_plugin.css?ver=<?php echo rand(111, 999) ?>" rel="stylesheet" />
    <link href="../css/theme_style.css?ver=<?php echo rand(111, 999) ?>" rel="stylesheet" />

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


<div class="userAdmin">
    <div class="flex h-screen overflow-y-hidden" x-data="setup()">
      <?php include 'sidebar.php'; ?>
    
      <div class="flex flex-col flex-1 h-full">
        <header class="flex-shrink-0">
            <div class="flex items-center justify-between px-2">
                <!-- Navbar left -->
                <div class="flex items-center space-x-3 relative">
                    <button @click="toggleSidbarMenu()" class="p-0 absolute left-[-22px] top-[20px] rounded-full w-6 h-6 hover:bg-themegreen text-[#667085] hover:text-white bg-[#F9F9F9] border border-color-[#E9EBF0] flex items-center justify-center transition duration-300 ease-in-out">
                        <svg class="w-4 h-4" :class="{ 'transform transition-transform -rotate-180': isSidebarOpen }" width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8.75 11L5.25 7.5L8.75 4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>
        <main class="bg-white flex-1 max-h-full py-10 px-8 overflow-hidden overflow-y-auto">
    