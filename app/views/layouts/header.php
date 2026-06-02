<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../../config/config.php';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Tiny Rides') ?></title>

    <link rel="stylesheet" href="<?= CSS_URL ?>/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<body>

    <!-- HEADER -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm border-b border-slate-200">
        <div class="mx-auto max-w-[1250px] px-4">
            <div class="h-[56px] flex items-center justify-between">

                <!-- Logo -->
                <a href="<?= BASE_URL ?>" class="flex items-center gap-2">
                    <div class="border p-2 rounded-3xl bg-[var(--btn-bg)]">
                        <i class="fa-solid fa-car-side text-slate-50"></i>
                    </div>
                    <h3 class="font-bold text-xl text-[var(--nav-text)]">TinyRides</h3>
                </a>

                <!-- Desktop nav -->
                <nav class="hidden md:flex items-center gap-2">
                    <a href="<?= BASE_URL ?>"
                        class="flex items-center gap-2 py-2 px-4 rounded-2xl hover:bg-[var(--btn-hover)] hover:text-white transition">
                        <i class="fa-regular fa-house"></i>
                        <span class="text- [var(--nav-text)]"><?= t('nav.home') ?></span>
                    </a>

                    <a href="<?= BASE_URL ?>/car/index"
                        class="flex items-center gap-2 py-2 px-4 rounded-2xl hover:bg-[var(--btn-hover)] hover:text-white transition">
                        <i class="fa-solid fa-car-side"></i>
                        <span class="text-white[var(--nav-text)]"><?= t('nav.items') ?></span>
                    </a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_URL ?>/car/create"
                            class="flex items-center gap-2 py-2 px-4 rounded-2xl hover:bg-[var(--btn-hover)]  transition">
                            <i class="fa-solid fa-plus"></i>
                            <span class="text-[var(--nav-text)]"><?= t('nav.add_post') ?></span>
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Desktop right -->
                <div class="hidden md:flex items-center gap-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_URL ?>/user/profile"
                            class="flex items-center gap-2 py-2 px-4 rounded-2xl hover:bg-[var(--btn-hover)]  transition">
                            <i class="fa-regular fa-user"></i>
                            <span class="text-[var(--nav-text)]"><?= t('nav.profile') ?></span>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/auth/login"
                            class="border rounded-2xl bg-[var(--main-bg)] px-4 py-2 hover:bg-[var(--btn-hover)] hover:text-white  transition">
                            <?= t('nav.login') ?>
                        </a>
                        <a href="<?= BASE_URL ?>/auth/register"
                            class="rounded-2xl px-4 py-2 bg-[var(--btn-bg)] text-white hover:bg-[var(--green-btn-hover)] transition">
                            <?= t('nav.register') ?>
                        </a>
                    <?php endif; ?>

                    <div class="relative">
                        <button
                            type="button"
                            id="langToggle"
                            class="flex items-center gap-2 border rounded-2xl px-3 py-2 bg-white hover:bg-[var(--btn-hover)] hover:text-white transition">
                            <i class="fa-solid fa-globe"></i>
                            <span><?= strtoupper($currentLang) ?></span>
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>

                        <div
                            id="langDropdown"
                            class="hidden absolute right-0 mt-2 w-44 bg-white border rounded-2xl shadow-lg p-2 z-50">
                            <a href="<?= BASE_URL ?>?lang=en"
                               class="block px-3 py-2 rounded-xl hover:bg-[var(--btn-hover)] hover:text-white <?= $currentLang === 'en' ? 'font-bold bg-[var(--btn-hover)] text-white' : '' ?>">
                                English
                            </a>

                            <a href="<?= BASE_URL ?>?lang=uz"
                                class="block px-3 py-2 rounded-xl hover:bg-[var(--btn-hover)] hover:text-white <?= $currentLang === 'uz' ? 'font-bold bg-[var(--btn-hover)] text-white' : '' ?>">
                                O'zbek
                            </a>

                            <a href="<?= BASE_URL ?>?lang=ru"
                                class="block px-3 py-2 rounded-xl hover:bg-[var(--btn-hover)] hover:text-white <?= $currentLang === 'ru' ? 'font-bold bg-[var(--btn-hover)] text-white' : '' ?>">
                                Русский
                            </a>

                            <a href="<?= BASE_URL ?>?lang=lv"
                                class="block px-3 py-2 rounded-xl hover:bg-[var(--btn-hover)] hover:text-white <?= $currentLang === 'lv' ? 'font-bold bg-[var(--btn-hover)] text-white' : '' ?>">
                                Latviešu
                            </a>

                            <a href="<?= BASE_URL ?>?lang=bn"
                               class="block px-3 py-2 rounded-xl hover:bg-[var(--btn-hover)] hover:text-white <?= $currentLang === 'bn' ? 'font-bold bg-[var(--btn-hover)] text-white' : '' ?>">
                                বাংলা
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mobile hamburger -->
                <button type="button"
                    id="navToggleBtn"
                    class="md:hidden w-11 h-11 rounded-2xl border bg-white hover:bg-[var(--btn-hover)] transition flex items-center justify-center"
                    aria-expanded="false"
                    aria-controls="mobileNav">
                    <i id="navIconBars" class="fa-solid fa-bars text-xl text-[var(--nav-text)]"></i>
                    <i id="navIconX" class="fa-solid fa-xmark text-xl text-[var(--nav-text)] hidden"></i>
                </button>
            </div>
        </div>

        <!-- Mobile dropdown -->
        <div id="mobileNav"
            class="md:hidden overflow-hidden max-h-0 opacity-0 pointer-events-none transition-all duration-300 ease-out">
            <div class="mx-auto max-w-[1250px] px-4 pb-4">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3">

                    <nav class="flex flex-col gap-1">
                        <a href="<?= BASE_URL ?>"
                            class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-[var(--btn-hover)] transition">
                            <i class="fa-regular fa-house"></i>
                            <span class="text-[var(--nav-text)]"><?= t('nav.home') ?></span>
                        </a>

                        <a href="<?= BASE_URL ?>/car/index"
                            class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-[var(--btn-hover)] transition">
                            <i class="fa-solid fa-car-side"></i>
                            <span class="text-[var(--nav-text)]"><?= t('nav.items') ?></span>
                        </a>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/car/create"
                                class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-[var(--btn-hover)] transition">
                                <i class="fa-solid fa-plus"></i>
                                <span class="text-[var(--nav-text)]"><?= t('nav.add_post') ?></span>
                            </a>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/user/profile"
                                class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-[var(--btn-hover)] transition">
                                <i class="fa-regular fa-user"></i>
                                <span class="text-[var(--nav-text)]"><?= t('nav.profile') ?></span>
                            </a>
                        <?php endif; ?>

                        <!-- MOBILE LANGUAGE DROPDOWN -->
                        <div class="px-1 py-1">
                            <button
                                type="button"
                                id="mobileLangToggle"
                                class="w-full flex items-center justify-between gap-3 px-3 py-3 rounded-xl border hover:bg-[var(--btn-hover)] transition">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-globe"></i>
                                    <span class="text-[var(--nav-text)]"><?= strtoupper($currentLang) ?></span>
                                </div>
                                <i id="mobileLangChevron" class="fa-solid fa-chevron-down text-xs transition-transform duration-200"></i>
                            </button>

                            <div id="mobileLangDropdown" class="hidden mt-2 border rounded-2xl overflow-hidden bg-white">
                                <a href="<?= BASE_URL ?>?lang=uz"
                                    class="block px-4 py-3 hover:bg-[var(--btn-hover)] <?= $currentLang === 'uz' ? 'font-bold bg-[var(--btn-hover)]' : '' ?>">
                                    O'zbek
                                </a>

                                <a href="<?= BASE_URL ?>?lang=en"
                                    class="block px-4 py-3 hover:bg-[var(--btn-hover)] <?= $currentLang === 'en' ? 'font-bold bg-[var(--btn-hover)]' : '' ?>">
                                    English
                                </a>

                                <a href="<?= BASE_URL ?>?lang=ru"
                                    class="block px-4 py-3 hover:bg-[var(--btn-hover)] <?= $currentLang === 'ru' ? 'font-bold bg-[var(--btn-hover)]' : '' ?>">
                                    Русский
                                </a>

                                <a href="<?= BASE_URL ?>?lang=lv"
                                    class="block px-4 py-3 hover:bg-[var(--btn-hover)] <?= $currentLang === 'lv' ? 'font-bold bg-[var(--btn-hover)]' : '' ?>">
                                    Latviešu
                                </a>
                            </div>
                        </div>
                    </nav>

                    <hr class="my-3">

                    <div class="flex gap-3">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/auth/login"
                                class="w-1/2 text-center border rounded-2xl bg-[var(--main-bg)] px-4 py-2 hover:bg-[var(--btn-hover)] transition">
                                <?= t('nav.login') ?>
                            </a>
                            <a href="<?= BASE_URL ?>/auth/register"
                                class="w-1/2 text-center rounded-2xl px-4 py-2 bg-[var(--btn-bg)] text-white hover:bg-[var(--green-btn-hover)] transition">
                                <?= t('nav.register') ?>
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/auth/logout"
                                class="w-full text-center border rounded-2xl bg-[var(--main-bg)] px-4 py-2 hover:bg-[var(--btn-hover)] transition">
                                <?= t('nav.logout') ?>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </header>

    <div class="h-[56px]"></div>