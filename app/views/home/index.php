<?php require_once __DIR__ . '/../layouts/header.php'; ?>


<!-- HERO SECTION START -->
<div class="hero-bg w-full">
    <div class="container px-4 sm:px-0">
        <section class="flex justify-center items-center flex-col py-10 sm:py-16 lg:py-20">
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-bold my-2 text-[var(--nav-text)] text-center leading-tight">
                <?= t('home.hero.title.line1') ?><br class="hidden sm:block">
                <span class="text-[var(--btn-bg)]"><?= t('home.hero.title.line2') ?></span>
            </h1>

            <p class="text-[var(--body-pf)] text-sm sm:text-lg mb-6 text-center max-w-4xl">
                <?= t('home.hero.description') ?>
            </p>
        </section>
    </div>
</div>
<!-- HERO SECTION END -->


<!-- CATEGORY SECTION START -->
<section class="bg-[var(--main-bg)]">
    <div class="container px-4 sm:px-0 py-10 sm:py-14">
        <div class="text-center">
            <h1 class="font-bold text-3xl sm:text-4xl"><?= t('home.categories.title') ?></h1>
            <p class="text-base text-[var(--body-pf)] mt-2"><?= t('home.categories.description') ?></p>
        </div>

        <!-- FIX: max-width + center -->
        <div class="max-w-[1200px] mx-auto mt-10">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-5">
                <a href="<?= BASE_URL ?>/car/index"
                    class="border rounded-2xl border-[var(--border-color)] bg-white px-4 py-6 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-[#E8F6EC] flex items-center justify-center mb-4">
                        <i class="fa-solid fa-gamepad text-2xl text-[var(--btn-bg)]"></i>
                    </div>
                    <h2 class="text-[15px] font-semibold leading-tight"><?= t('category.rc_cars') ?></h2>
                </a>

                <a href="<?= BASE_URL ?>/car/index"
                    class="border rounded-2xl border-[var(--border-color)] bg-white px-4 py-6 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-[#FFF0E8] flex items-center justify-center mb-4">
                        <i class="fa-solid fa-car-side text-2xl text-[#FF8A4C]"></i>
                    </div>
                    <h2 class="text-[15px] font-semibold leading-tight"><?= t('category.ride_on_cars') ?></h2>
                </a>

                <a href="<?= BASE_URL ?>/car/index"
                    class="border rounded-2xl border-[var(--border-color)] bg-white px-4 py-6 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-[#E9FBFF] flex items-center justify-center mb-4">
                        <i class="fa-solid fa-person-biking text-2xl text-[#06B6D4]"></i>
                    </div>
                    <h2 class="text-[15px] font-semibold leading-tight"><?= t('category.balance_bikes') ?></h2>
                </a>

                <a href="<?= BASE_URL ?>/car/index"
                    class="border rounded-2xl border-[var(--border-color)] bg-white px-4 py-6 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-[#F9E8F9] flex items-center justify-center mb-4">
                        <i class="fa-solid fa-tractor text-2xl text-[#D946EF]"></i>
                    </div>
                    <h2 class="text-[15px] font-semibold leading-tight"><?= t('category.mini_tractors') ?></h2>
                </a>

                <a href="<?= BASE_URL ?>/car/index"
                    class="border rounded-2xl border-[var(--border-color)] bg-white px-4 py-6 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-[#F0EDFF] flex items-center justify-center mb-4">
                        <i class="fa-solid fa-shoe-prints text-2xl text-[#7C6CF6]"></i>
                    </div>
                    <h2 class="text-[15px] font-semibold leading-tight"><?= t('category.push_cars') ?></h2>
                </a>

                <a href="<?= BASE_URL ?>/car/index"
                    class="border rounded-2xl border-[var(--border-color)] bg-white px-4 py-6 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-[#E8F6EC] flex items-center justify-center mb-4">
                        <i class="fa-solid fa-bolt text-2xl text-[var(--btn-bg)]"></i>
                    </div>
                    <h2 class="text-[15px] font-semibold leading-tight"><?= t('category.electric_scooters') ?></h2>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- CATEGORY SECTION END -->


<!-- HOW IT WORKS START -->
<section class="bg-[var(--main-bg)]">
    <div class="container px-4 sm:px-0 py-10 sm:py-12">
        <div class="text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-[var(--nav-text)]"><?= t('home.how_it_works.title') ?></h2>
            <p class="text-base text-[var(--body-pf)] mt-2 max-w-3xl mx-auto">
                <?= t('home.how_it_works.description') ?>
            </p>
        </div>

        <!-- FIX: max-width + center -->
        <div class="max-w-[1200px] mx-auto mt-10 relative">
            <!-- connecting line -->
            <div class="hidden lg:block absolute left-8 right-8 top-10 h-[2px] bg-[var(--border-color)]"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">
                <!-- 1 -->
                <a href="javascript:void(0)" class="text-center">
                    <div class="relative mx-auto w-20 h-20 rounded-full bg-[#E8F6EC] flex items-center justify-center">
                        <i class="fa-solid fa-magnifying-glass text-2xl text-[var(--btn-bg)]"></i>
                        <div class="absolute -top-2 -right-2 w-9 h-9 rounded-full bg-[var(--btn-bg)] text-white flex items-center justify-center text-sm font-semibold">1</div>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold"><?= t('home.how_it_works.step1.title') ?></h3>
                    <p class="mt-2 text-sm text-[var(--body-pf)] max-w-xs mx-auto">
                        <?= t('home.how_it_works.step1.description') ?>
                    </p>
                </a>

                <!-- 2 -->
                <a href="javascript:void(0)" class="text-center">
                    <div class="relative mx-auto w-20 h-20 rounded-full bg-[#E8F6EC] flex items-center justify-center">
                        <i class="fa-regular fa-comment-dots text-2xl text-[var(--btn-bg)]"></i>
                        <div class="absolute -top-2 -right-2 w-9 h-9 rounded-full bg-[var(--btn-bg)] text-white flex items-center justify-center text-sm font-semibold">2</div>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold"><?= t('home.how_it_works.step2.title') ?></h3>
                    <p class="mt-2 text-sm text-[var(--body-pf)] max-w-xs mx-auto">
                        <?= t('home.how_it_works.step2.description') ?>
                    </p>
                </a>

                <!-- 3 -->
                <a href="javascript:void(0)" class="text-center">
                    <div class="relative mx-auto w-20 h-20 rounded-full bg-[#E8F6EC] flex items-center justify-center">
                        <i class="fa-regular fa-face-smile text-2xl text-[var(--btn-bg)]"></i>
                        <div class="absolute -top-2 -right-2 w-9 h-9 rounded-full bg-[var(--btn-bg)] text-white flex items-center justify-center text-sm font-semibold">3</div>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold"><?= t('home.how_it_works.step3.title') ?></h3>
                    <p class="mt-2 text-sm text-[var(--body-pf)] max-w-xs mx-auto">
                        <?= t('home.how_it_works.step3.description') ?>
                    </p>
                </a>

                <!-- 4 -->
                <a href="javascript:void(0)" class="text-center">
                    <div class="relative mx-auto w-20 h-20 rounded-full bg-[#E8F6EC] flex items-center justify-center">
                        <i class="fa-regular fa-calendar-days text-2xl text-[var(--btn-bg)]"></i>
                        <div class="absolute -top-2 -right-2 w-9 h-9 rounded-full bg-[var(--btn-bg)] text-white flex items-center justify-center text-sm font-semibold">4</div>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold"><?= t('home.how_it_works.step4.title') ?></h3>
                    <p class="mt-2 text-sm text-[var(--body-pf)] max-w-xs mx-auto">
                        <?= t('home.how_it_works.step4.description') ?>
                    </p>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- HOW IT WORKS END -->


<!-- RECOMMEND SECTION START-->
<section class="bg-[var(--btn-bg)]">
    <div class="container px-4 sm:px-0 py-12 sm:py-16">
        <div class="recommend-text text-center">
            <h1 class="text-white text-3xl sm:text-4xl font-bold"><?= t('home.cta.title') ?></h1>
            <p class="text-[#ffffffcc] text-base sm:text-lg my-5 max-w-4xl mx-auto">
                <?= t('home.cta.description') ?>
            </p>

            <a href="<?= BASE_URL ?>/car/index">
                <button class="py-3 px-6 rounded-xl bg-[#f3e2c7] text-[#1c222b] hover:bg-[#f1ae84]">
                    <i class="fa-solid fa-arrow-right-long ml-2 text-[#1c222b]"></i>
                    <?= t('home.cta.button') ?>
                    <i class="fa-solid fa-arrow-right-long ml-2 text-[#1c222b]"></i>
                </button>
            </a>

        </div>
    </div>
</section>
<!-- RECOMMEND SECTION END -->

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>