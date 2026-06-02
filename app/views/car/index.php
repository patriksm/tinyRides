<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php

$gSearch     = (string)($_GET['search'] ?? '');
$gLocation   = (string)($_GET['location'] ?? '');
$gCategoryId = (string)($_GET['category_id'] ?? '');
$gAge        = (string)($_GET['age_category'] ?? '');
$gMinPrice   = (string)($_GET['min_price'] ?? '');
$gMaxPrice   = (string)($_GET['max_price'] ?? '');
$gCondition  = (string)($_GET['condition_status'] ?? '');

$categories = [
    ['id' => '1', 'label' => t('category.rc_cars')],
    ['id' => '2', 'label' => t('category.ride_on_cars')],
    ['id' => '3', 'label' => t('category.balance_bikes')],
    ['id' => '4', 'label' => t('category.mini_tractors')],
    ['id' => '5', 'label' => t('category.push_cars')],
    ['id' => '6', 'label' => t('category.electric_scooters')],
];

$categoryMap = [
    '1' => t('category.rc_cars'),
    '2' => t('category.ride_on_cars'),
    '3' => t('category.balance_bikes'),
    '4' => t('category.mini_tractors'),
    '5' => t('category.push_cars'),
    '6' => t('category.electric_scooters'),
];

$ages = [
    ['v' => '0-2', 'l' => t('age.0_2')],
    ['v' => '2-5', 'l' => t('age.2_5')],
    ['v' => '5-8', 'l' => t('age.5_8')],
    ['v' => '8+',  'l' => t('age.8_plus')],
];

$ageMap = [
    '0-2' => t('age.0_2'),
    '2-5' => t('age.2_5'),
    '5-8' => t('age.5_8'),
    '8+'  => t('age.8_plus'),
];

$conditions = [
    'new' => t('condition.new'),
    'excellent' => t('condition.excellent'),
    'good' => t('condition.good'),
    'used' => t('condition.used'),
];

$perUnits = [
    'day'  => t('create.per.day'),
    'hour' => t('create.per.hour'),
    'week' => t('create.per.week'),
];
?>

<div class="w-full bg-[var(--main-bg)]">
    <section class="container max-w-[1250px] mx-auto flex flex-col md:flex-row items-start pt-20 px-4 md:px-0">

        <!-- LEFT: FILTER (Desktop) -->
        <div class="w-full md:w-[320px] md:shrink-0">
            <h1 class="text-3xl text-[var(--nav-text)] font-bold"><?= t('items.page_title') ?></h1>
            <p class="text-base text-[var(--body-pf)] mb-5"><?= t('items.page_description') ?></p>

            <!-- MOBILE: Filters button -->
            <button
                type="button"
                id="openFiltersBtn"
                class="md:hidden w-full rounded-xl border bg-white px-4 py-3 flex items-center justify-center gap-2 mb-4">
                <i class="fa-solid fa-sliders"></i>
                <?= t('filters') ?>
            </button>

            <!-- Desktop filter panel -->
            <div class="hidden md:block filter border w-full p-5 rounded-xl border-[var(--border-color)] bg-white">
                <form id="filtersForm" action="<?= BASE_URL ?>/car" method="GET" class="flex flex-col">

                    <!-- City Autocomplete -->
                    <div class="flex flex-col mt-2">
                        <label for="citySearch" class="text-base font-semibold"><?= t('filters.city') ?></label>

                        <div class="relative mt-2">
                            <input
                                id="citySearch"
                                type="text"
                                autocomplete="off"
                                placeholder="<?= t('filters.city.placeholder') ?>"
                                value="<?= htmlspecialchars($gLocation) ?>"
                                class="my-1 border p-2 rounded-xl text-[var(--nav-text)] focus:outline-none w-full" />

                            <input type="hidden" id="locationHidden" name="location" value="<?= htmlspecialchars($gLocation) ?>">

                            <div
                                id="cityDropdown"
                                class="absolute left-0 right-0 top-[52px] z-30 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                                <div id="cityList" class="max-h-64 overflow-auto"></div>
                            </div>
                        </div>

                        <small class="text-xs text-[var(--body-pf)] mt-1"><?= t('filters.city.help') ?></small>
                    </div>

                    <!-- Category -->
                    <div class="mt-5 filter-section">
                        <div class="filter-header flex justify-between items-center text-base font-semibold hover:cursor-pointer">
                            <h2><?= t('filters.category') ?></h2>
                            <i class="fa-solid fa-angle-up angle-icon transition-transform duration-300"></i>
                        </div>

                        <div class="filter-content my-2 ml-2 space-y-2">
                            <?php foreach ($categories as $cat):
                                $val = (string)$cat['id'];
                                $id  = 'cat-' . $val;
                            ?>
                                <div class="category-option flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="category_id"
                                        id="<?= htmlspecialchars($id) ?>"
                                        value="<?= htmlspecialchars($val) ?>"
                                        <?= $gCategoryId === $val ? 'checked' : '' ?>>
                                    <label for="<?= htmlspecialchars($id) ?>" class="text-[var(--body-pf)] text-sm">
                                        <?= htmlspecialchars($cat['label']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>

                            <div class="category-option flex items-center gap-2">
                                <input type="radio" name="category_id" id="cat-any" value="" <?= $gCategoryId === '' ? 'checked' : '' ?>>
                                <label for="cat-any" class="text-[var(--body-pf)] text-sm"><?= t('filters.any') ?></label>
                            </div>
                        </div>

                        <hr class="mt-5">
                    </div>

                    <!-- Age Suitability -->
                    <div class="mt-5 filter-section">
                        <div class="filter-header flex justify-between items-center text-base font-semibold hover:cursor-pointer">
                            <h2><?= t('filters.age_suitability') ?></h2>
                            <i class="fa-solid fa-angle-up angle-icon transition-transform duration-300"></i>
                        </div>

                        <div class="filter-content my-2 ml-2 space-y-2">
                            <?php foreach ($ages as $a):
                                $val = $a['v'];
                                $id  = 'age-' . str_replace(['+'], ['plus'], $val);
                            ?>
                                <div class="category-option flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="age_category"
                                        id="<?= htmlspecialchars($id) ?>"
                                        value="<?= htmlspecialchars($val) ?>"
                                        <?= $gAge === $val ? 'checked' : '' ?>>
                                    <label for="<?= htmlspecialchars($id) ?>" class="text-[var(--body-pf)] text-sm">
                                        <?= htmlspecialchars($a['l']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>

                            <div class="category-option flex items-center gap-2">
                                <input type="radio" name="age_category" id="age-any" value="" <?= $gAge === '' ? 'checked' : '' ?>>
                                <label for="age-any" class="text-[var(--body-pf)] text-sm"><?= t('filters.any') ?></label>
                            </div>
                        </div>

                        <hr class="mt-5">
                    </div>

                    <!-- Price range -->
                    <div class="price-range mt-5">
                        <label class="text-base font-semibold"><?= t('filters.price_range') ?></label>
                        <div class="flex gap-3 my-2">
                            <input
                                type="number"
                                placeholder="Min"
                                name="min_price"
                                value="<?= htmlspecialchars($gMinPrice) ?>"
                                class="border w-1/2 rounded-xl p-2"
                                min="0"
                                step="0.01">
                            <input
                                type="number"
                                placeholder="Max"
                                name="max_price"
                                value="<?= htmlspecialchars($gMaxPrice) ?>"
                                class="border w-1/2 rounded-xl p-2"
                                min="0"
                                step="0.01">
                        </div>
                    </div>

                    <!-- Condition -->
                    <div class="mt-5 filter-section">
                        <div class="filter-header flex justify-between items-center text-base font-semibold hover:cursor-pointer">
                            <h2><?= t('filters.condition') ?></h2>
                            <i class="fa-solid fa-angle-up angle-icon transition-transform duration-300"></i>
                        </div>

                        <div class="filter-content my-2 ml-2 space-y-2">
                            <?php foreach ($conditions as $val => $label):
                                $id = 'cond-' . $val;
                            ?>
                                <div class="category-option flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="condition_status"
                                        id="<?= htmlspecialchars($id) ?>"
                                        value="<?= htmlspecialchars($val) ?>"
                                        <?= $gCondition === $val ? 'checked' : '' ?>>
                                    <label for="<?= htmlspecialchars($id) ?>" class="text-[var(--body-pf)] text-sm">
                                        <?= htmlspecialchars($label) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>

                            <div class="category-option flex items-center gap-2">
                                <input type="radio" name="condition_status" id="cond-any" value="" <?= $gCondition === '' ? 'checked' : '' ?>>
                                <label for="cond-any" class="text-[var(--body-pf)] text-sm"><?= t('filters.any') ?></label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT: MAIN -->
        <div class="w-full md:flex-1 md:ml-10 mt-8 md:mt-0">
            <?php if (!empty($cars)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-10 md:mt-20">
                    <?php foreach ($cars as $car): ?>
                        <?php
                        $title = trim(($car['brand'] ?? '') . ' ' . ($car['model'] ?? ''));
                        $title = $title !== '' ? $title : 'Car';

                        $status   = (string)($car['status'] ?? 'active');
                        $isRented = ($status === 'rented');

                        $city   = (string)($car['city'] ?? '');
                        $ageCat = (string)($car['age_category'] ?? '');

                        $price = (string)($car['price_per_day'] ?? '');
                        $per   = (string)($car['price_per'] ?? 'day');
                        $owner = (string)($car['owner_name'] ?? '');

                        $imgPath = (string)($car['main_photo'] ?? '');
                        if ($imgPath === '') {
                            $imgPath = (string)($car['image'] ?? '');
                        }
                        $img = $imgPath !== '' ? (BASE_URL . $imgPath) : (BASE_URL . '/public/uploads/images.jpeg');

                        $conditionKey   = (string)($car['condition_status'] ?? '');
                        $conditionLabel = $conditions[$conditionKey] ?? '';

                        $categoryLabel = $categoryMap[(string)($car['category_id'] ?? '')]
                            ?? (string)($car['category_name'] ?? t('filters.category'));

                        $ageLabel = $ageMap[$ageCat] ?? $ageCat;
                        $perLabel = $perUnits[$per] ?? $per;

                        $battery = (string)($car['battery_life'] ?? '—');
                        $speed   = (string)($car['max_speed'] ?? '—');
                        $cap     = (string)($car['max_weight_capacity'] ?? '—');

                        $batteryTxt = ($battery !== '' && $battery !== '—') ? ($battery . ' hrs') : '—';
                        $speedTxt   = ($speed !== '' && $speed !== '—') ? ($speed . ' mph') : '—';
                        ?>
                        <div class="items border-[var(--border-color)] border rounded-xl bg-white overflow-hidden mt-6">
                            <div class="card-form">

                                <!-- IMAGE -->
                                <div class="relative">
                                    <?php if ($conditionLabel !== ''): ?>
                                        <small class="absolute py-0.5 px-3 rounded-xl mt-2 text-[10px] ml-2 text-[var(--nav-text)] bg-[#29e729] font-normal z-20">
                                            <?= htmlspecialchars($conditionLabel) ?>
                                        </small>
                                    <?php endif; ?>

                                    <img src="<?= htmlspecialchars($img) ?>" alt="car" class="mt-4 h-[160px] w-full object-cover">

                                    <?php if ($isRented): ?>
                                        <div class="absolute inset-0 bg-black/50 z-10"></div>

                                        <div class="absolute inset-0 flex items-start justify-center pt-6 z-20">
                                            <div class="bg-white rounded-2xl px-5 py-2 mt-5 shadow-lg">
                                                <div class="text-base font-semibold text-slate-900 leading-tight"><?= t('detail.unavailable') ?></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="p-4">
                                    <div class="card-category flex justify-between mb-2">
                                        <small class="text-xs border bg-[#f3e2c7] rounded-xl p-1 font-normal">
                                            <?= htmlspecialchars($categoryLabel) ?>
                                        </small>
                                        <div class="card-rate">
                                            <i class="fa-solid fa-star text-yellow-400"></i>
                                            4.9
                                        </div>
                                    </div>

                                    <div class="car-info">
                                        <h3 class="text-base font-semibold text-[var(--nav-text)] mb-2">
                                            <?= htmlspecialchars($title) ?>
                                        </h3>

                                        <p class="text-sm text-[var(--body-pf)] mb-1">
                                            <i class="fa-solid fa-location-dot"></i>
                                            <?= htmlspecialchars($city !== '' ? $city : '—') ?>
                                        </p>

                                        <p class="text-sm text-[var(--body-pf)] mb-2">
                                            <i class="fa-regular fa-clock"></i>
                                            <?= htmlspecialchars($ageLabel) ?> <?= t('detail.age_suitability') ?>
                                        </p>

                                        <hr>

                                        <div class="flex justify-between my-3">
                                            <div class="text-center">
                                                <i class="fa-solid fa-battery-full text-[var(--btn-bg)]"></i>
                                                <p class="text-xs text-[var(--body-pf)]"><?= t('detail.battery') ?></p>
                                                <small class="text-xs text-[var(--nav-text)] font-medium">
                                                    <?= htmlspecialchars($batteryTxt) ?>
                                                </small>
                                            </div>
                                            <div class="text-center">
                                                <i class="fa-solid fa-gauge-simple-high text-[var(--btn-bg)]"></i>
                                                <p class="text-xs text-[var(--body-pf)]"><?= t('create.max_speed') ?></p>
                                                <small class="text-xs text-[var(--nav-text)] font-medium">
                                                    <?= htmlspecialchars($speedTxt) ?>
                                                </small>
                                            </div>
                                            <div class="text-center">
                                                <i class="fa-solid fa-weight-hanging text-[var(--btn-bg)]"></i>
                                                <p class="text-xs text-[var(--body-pf)]"><?= t('create.max_weight_capacity') ?></p>
                                                <small class="text-xs text-[var(--nav-text)] font-medium">
                                                    <?= htmlspecialchars($cap) ?> (kg)
                                                </small>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="flex gap-5 my-4 items-center">
                                            <div class="owner-logo border p-2 rounded-2xl bg-[#f3e2c7]">
                                                <i class="fa-regular fa-user w-[20px] h-[16px] text-[var(--body-pf)]"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-sm"><?= htmlspecialchars($owner) ?></h3>
                                                <small class="text-xs text-[var(--body-pf)]"><?= t('detail.owner') ?></small>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="flex justify-between my-4 items-center">
                                            <div class="card-price text-lg text-[var(--btn-bg)] font-bold">
                                                <?= htmlspecialchars($price) ?><span class="text-sm text-[var(--body-pf)]">/<?= htmlspecialchars($perLabel) ?></span>
                                            </div>

                                            <?php if ($isRented): ?>
                                                <button
                                                    type="button"
                                                    class="bg-[#f1eee7] border py-1 px-5 text-slate-500 rounded-xl font-medium cursor-not-allowed"
                                                    disabled>
                                                    <?= t('detail.unavailable') ?>
                                                </button>
                                            <?php else: ?>
                                                <a href="<?= rtrim(BASE_URL, '/') ?>/car/detail/<?= htmlspecialchars((string)$car['uuid']) ?>">
                                                    <button class="bg-[var(--btn-bg)] border py-1 px-5 text-white rounded-xl hover:bg-[var(--green-btn-hover)] font-medium">
                                                        <?= t('detail.seemore') ?>
                                                    </button>
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-posts border rounded-xl mt-10 md:mt-20 bg-white min-h-[60vh] flex items-center justify-center px-4">
                    <div class="no-post__text text-center select-none">
                        <i class="fa-regular fa-face-frown text-5xl mb-5 text-[var(--body-pf)]"></i>
                        <h1 class="text-4xl md:text-5xl text-[var(--body-pf)]"><?= t('items.empty') ?></h1>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- MOBILE FILTER DRAWER -->
        <div id="filtersDrawer"
            class="fixed inset-0 z-[999] md:hidden opacity-0 pointer-events-none transition-opacity duration-300">
            <div id="filtersOverlay"
                class="absolute inset-0 bg-black/40 opacity-0 transition-opacity duration-300"></div>

            <aside id="filtersPanel"
                class="absolute left-0 top-0 h-full w-[85%] max-w-[360px]
                          bg-[var(--main-bg)] shadow-xl border-r flex flex-col
                          transform -translate-x-full transition-transform duration-300 ease-in-out">

                <div class="flex items-center justify-between p-4 border-b bg-white">
                    <h2 class="text-lg font-semibold"><?= t('filters') ?></h2>
                    <button type="button" id="closeFiltersBtn"
                        class="w-10 h-10 rounded-xl border bg-[var(--main-bg)] hover:bg-[var(--btn-hover)]">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-4 overflow-auto">
                    <form id="filtersFormMobile" action="<?= BASE_URL ?>/car" method="GET" class="flex flex-col">

                        <!-- City Autocomplete -->
                        <div class="flex flex-col mt-2">
                            <label for="citySearchMobile" class="text-base font-semibold"><?= t('filters.city') ?></label>

                            <div class="relative mt-2">
                                <input
                                    id="citySearchMobile"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="<?= t('filters.city.placeholder') ?>"
                                    value="<?= htmlspecialchars($gLocation) ?>"
                                    class="my-1 border p-2 rounded-xl text-[var(--nav-text)] focus:outline-none w-full bg-white" />

                                <input type="hidden" id="locationHiddenMobile" name="location" value="<?= htmlspecialchars($gLocation) ?>">

                                <div
                                    id="cityDropdownMobile"
                                    class="absolute left-0 right-0 top-[52px] z-30 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                                    <div id="cityListMobile" class="max-h-64 overflow-auto"></div>
                                </div>
                            </div>

                            <small class="text-xs text-[var(--body-pf)] mt-1"><?= t('filters.city.help') ?></small>
                        </div>

                        <!-- Category -->
                        <div class="mt-5 filter-section">
                            <div class="filter-header flex justify-between items-center text-base font-semibold hover:cursor-pointer">
                                <h2><?= t('filters.category') ?></h2>
                                <i class="fa-solid fa-angle-up angle-icon transition-transform duration-300"></i>
                            </div>

                            <div class="filter-content my-2 ml-2 space-y-2">
                                <?php foreach ($categories as $cat):
                                    $val = (string)$cat['id'];
                                    $id  = 'm-cat-' . $val;
                                ?>
                                    <div class="category-option flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="category_id"
                                            id="<?= htmlspecialchars($id) ?>"
                                            value="<?= htmlspecialchars($val) ?>"
                                            <?= $gCategoryId === $val ? 'checked' : '' ?>>
                                        <label for="<?= htmlspecialchars($id) ?>" class="text-[var(--body-pf)] text-sm">
                                            <?= htmlspecialchars($cat['label']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                                <div class="category-option flex items-center gap-2">
                                    <input type="radio" name="category_id" id="m-cat-any" value="" <?= $gCategoryId === '' ? 'checked' : '' ?>>
                                    <label for="m-cat-any" class="text-[var(--body-pf)] text-sm"><?= t('filters.any') ?></label>
                                </div>
                            </div>

                            <hr class="mt-5">
                        </div>

                        <!-- Age -->
                        <div class="mt-5 filter-section">
                            <div class="filter-header flex justify-between items-center text-base font-semibold hover:cursor-pointer">
                                <h2><?= t('filters.age_suitability') ?></h2>
                                <i class="fa-solid fa-angle-up angle-icon transition-transform duration-300"></i>
                            </div>

                            <div class="filter-content my-2 ml-2 space-y-2">
                                <?php foreach ($ages as $a):
                                    $val = $a['v'];
                                    $id  = 'm-age-' . str_replace(['+'], ['plus'], $val);
                                ?>
                                    <div class="category-option flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="age_category"
                                            id="<?= htmlspecialchars($id) ?>"
                                            value="<?= htmlspecialchars($val) ?>"
                                            <?= $gAge === $val ? 'checked' : '' ?>>
                                        <label for="<?= htmlspecialchars($id) ?>" class="text-[var(--body-pf)] text-sm">
                                            <?= htmlspecialchars($a['l']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                                <div class="category-option flex items-center gap-2">
                                    <input type="radio" name="age_category" id="m-age-any" value="" <?= $gAge === '' ? 'checked' : '' ?>>
                                    <label for="m-age-any" class="text-[var(--body-pf)] text-sm"><?= t('filters.any') ?></label>
                                </div>
                            </div>

                            <hr class="mt-5">
                        </div>

                        <!-- Price -->
                        <div class="price-range mt-5">
                            <label class="text-base font-semibold"><?= t('filters.price_range') ?></label>
                            <div class="flex gap-3 my-2">
                                <input
                                    type="number"
                                    placeholder="Min"
                                    name="min_price"
                                    value="<?= htmlspecialchars($gMinPrice) ?>"
                                    class="border w-1/2 rounded-xl p-2 bg-white"
                                    min="0"
                                    step="0.01">
                                <input
                                    type="number"
                                    placeholder="Max"
                                    name="max_price"
                                    value="<?= htmlspecialchars($gMaxPrice) ?>"
                                    class="border w-1/2 rounded-xl p-2 bg-white"
                                    min="0"
                                    step="0.01">
                            </div>
                        </div>

                        <!-- Condition -->
                        <div class="mt-5 filter-section">
                            <div class="filter-header flex justify-between items-center text-base font-semibold hover:cursor-pointer">
                                <h2><?= t('filters.condition') ?></h2>
                                <i class="fa-solid fa-angle-up angle-icon transition-transform duration-300"></i>
                            </div>

                            <div class="filter-content my-2 ml-2 space-y-2">
                                <?php foreach ($conditions as $val => $label):
                                    $id = 'm-cond-' . $val;
                                ?>
                                    <div class="category-option flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="condition_status"
                                            id="<?= htmlspecialchars($id) ?>"
                                            value="<?= htmlspecialchars($val) ?>"
                                            <?= $gCondition === $val ? 'checked' : '' ?>>
                                        <label for="<?= htmlspecialchars($id) ?>" class="text-[var(--body-pf)] text-sm">
                                            <?= htmlspecialchars($label) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                                <div class="category-option flex items-center gap-2">
                                    <input type="radio" name="condition_status" id="m-cond-any" value="" <?= $gCondition === '' ? 'checked' : '' ?>>
                                    <label for="m-cond-any" class="text-[var(--body-pf)] text-sm"><?= t('filters.any') ?></label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </aside>
        </div>

    </section>
</div>

<script>
    window.__citySuggestUrl =
        "<?= rtrim(BASE_URL, '/') ?>/public/api/locations/suggest.php?type=city";
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>