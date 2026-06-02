<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
// safe helpers
$car = $car ?? [];
$title = trim(($car['brand'] ?? '') . ' ' . ($car['model'] ?? ''));
$title = $title !== '' ? $title : 'Item';

$condition = (string)($car['condition_status'] ?? 'excellent');
$categories = [
    '1' => t('category.rc_cars'),
    '2' => t('category.ride_on_cars'),
    '3' => t('category.balance_bikes'),
    '4' => t('category.mini_tractors'),
    '5' => t('category.push_cars'),
    '6' => t('category.electric_scooters'),
];

$categoryLabel = $categories[(string)($car['category_id'] ?? '')] ?? '';

$perUnits = [
    'day' => t('create.per.day'),
    'hour' => t('create.per.hour'),
    'week' => t('create.per.week'),
];

$perLabel = $perUnits[(string)($car['price_per'] ?? '')] ?? '';

// NEW address fields (create/edit)
$city   = (string)($car['city'] ?? '');
$street = (string)($car['street'] ?? '');
$lat    = $car['latitude'] ?? null;
$lon    = $car['longitude'] ?? null;

$locationText = trim($city . ($street !== '' ? ', ' . $street : ''));
if ($locationText === '') $locationText = (string)($car['location'] ?? '');

// numeric lat/lon
$lat = is_numeric((string)$lat) ? (float)$lat : null;
$lon = is_numeric((string)$lon) ? (float)$lon : null;

$price = $car['price_per_day'] ?? null;
$per = (string)($car['price_per'] ?? ($car['per'] ?? 'day'));
$deposit = $car['deposit'] ?? null;

$age = (string)($car['age_category'] ?? '');
$maxWeight = $car['max_weight_capacity'] ?? null;

$description = (string)($car['description'] ?? '');
$rentalTerms = (string)($car['rental_terms'] ?? '');

$ownerName  = (string)($car['owner_name'] ?? ($car['contact_name'] ?? ''));
$ownerPhone = (string)($car['contact_phone'] ?? '');
$ownerEmail = (string)($car['owner_email'] ?? ($car['contact_email'] ?? ''));

$rating = $car['rating'] ?? 4.9;
$reviewsCount = $car['reviews_count'] ?? 28;

$batteryLife = (string)($car['battery_life'] ?? '-');
$chargeTime  = (string)($car['charge_time'] ?? ($car['charging_time'] ?? '-'));
$maxSpeed    = (string)($car['max_speed'] ?? '-');

// photos
$photos = is_array($car['photos'] ?? null) ? $car['photos'] : [];
$mainPhoto = (string)($car['main_photo'] ?? '');


if ($mainPhoto === '' && !empty($photos)) {
    $mainPhoto = (string)($photos[0]['image_path'] ?? '');
}

if ($mainPhoto === '') {
    $mainPhoto = (string)($car['image'] ?? '');
}

$mainImg = $mainPhoto !== ''
    ? (BASE_URL . $mainPhoto)
    : (BASE_URL . '/public/uploads/images.jpeg');

$gallery = [];
foreach ($photos as $p) {
    $pPath = (string)($p['image_path'] ?? '');
    if ($pPath !== '') $gallery[] = BASE_URL . $pPath;
}
if (empty($gallery)) $gallery[] = $mainImg;

// helpers
$ownerInitials = '—';
if ($ownerName !== '') {
    $parts = preg_split('/\s+/', trim($ownerName));
    $i1 = strtoupper(substr($parts[0] ?? '', 0, 1));
    $i2 = strtoupper(substr($parts[1] ?? '', 0, 1));
    $ownerInitials = trim($i1 . $i2) !== '' ? trim($i1 . $i2) : strtoupper(substr($ownerName, 0, 1));
}
$depositValid = ($deposit !== null && $deposit !== '' && is_numeric((string)$deposit) && (float)$deposit > 0);


$hasMap = ($lat !== null && $lon !== null);
?>

<section class="min-h-screen bg-[#faf9f7]">
    <div class="mx-auto max-w-6xl px-4 max-sm:px-3 pt-24 sm:pt-28 pb-10 max-sm:pb-8">

        <!-- Breadcrumb -->
        <div class="mb-5 sm:mb-6 text-sm max-sm:text-[13px] text-slate-500 break-words">
            <?= t('detail.breadcrumb.home') ?> <span class="mx-1">/</span> <?= t('detail.breadcrumb.items') ?> <span class="mx-1">/</span>
            <span class="text-slate-700"><?= htmlspecialchars($title) ?></span>
        </div>

        <!-- TOP GRID -->
        <div class="grid grid-cols-12 gap-6 sm:gap-8">
            <!-- LEFT: Gallery -->
            <div class="col-span-12 lg:col-span-7">
                <div class="w-full">
                    <!-- MAIN IMAGE -->
                    <div class="rounded-[32px] overflow-hidden bg-[#f3efe6] border border-slate-200">
                        <img
                            id="mainDetailImage"
                            src="<?= htmlspecialchars($gallery[0] ?? '') ?>"
                            alt="main image"
                            class="w-full h-[280px] sm:h-[420px] object-cover cursor-pointer">
                    </div>

                    <!-- THUMBS -->
                    <div class="mt-4 sm:mt-6 flex gap-3 sm:gap-4 flex-wrap">
                        <?php
                        $thumbs = array_slice($gallery, 0, 5);
                        foreach ($thumbs as $idx => $g):
                        ?>
                            <button
                                type="button"
                                class="detail-thumb w-20 h-20 sm:w-24 sm:h-24 rounded-2xl border bg-[#efe7d8] flex items-center justify-center overflow-hidden <?= $idx === 0 ? 'ring-2 ring-green-600 border-transparent' : 'border-slate-200 hover:border-slate-300' ?>"
                                data-index="<?= (int)$idx ?>"
                                data-src="<?= htmlspecialchars($g) ?>">

                                <img src="<?= htmlspecialchars($g) ?>" class="w-full h-full object-cover" />
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- MODAL -->
                    <div id="galleryModal" class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/80 p-4">

                        <!-- CLOSE -->
                        <button id="galleryModalClose"
                            class="absolute top-4 right-4 text-white text-4xl">
                            &times;
                        </button>

                        <!-- PREV -->
                        <button id="galleryModalPrev"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/20 text-white text-3xl">
                            &#8249;
                        </button>

                        <!-- IMAGE -->
                        <img id="galleryModalImage"
                            src=""
                            class="max-w-full max-h-[90vh] rounded-2xl object-contain shadow-2xl">

                        <!-- NEXT -->
                        <button id="galleryModalNext"
                            class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/20 text-white text-3xl">
                            &#8250;
                        </button>
                    </div>

                </div>
            </div>

            <!-- RIGHT: Details -->
            <div class="col-span-12 lg:col-span-5">
                <div class="mb-3">
                    <span class="inline-flex items-center rounded-full bg-[#f3eadb] px-3 py-1 text-xs font-semibold text-slate-700">
                        <?= htmlspecialchars($categoryLabel) ?>
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">
                    <?= htmlspecialchars($title) ?>
                </h1>

                <div class="mt-3 flex flex-wrap items-center gap-x-5 sm:gap-x-6 gap-y-2 text-slate-600 text-sm sm:text-base">
                    <?php if ($locationText !== ''): ?>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot"></i>
                            <span><?= htmlspecialchars($locationText) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Price card -->
                <div class="mt-5 sm:mt-6 rounded-3xl max-sm:rounded-2xl bg-[#f3eadb] p-5 sm:p-6">
                    <div class="flex items-end gap-2">
                        <div class="text-4xl sm:text-5xl font-extrabold text-green-600 leading-none">
                            €<?= htmlspecialchars($price !== null ? (string)$price : '0') ?>
                        </div>
                        <div class="pb-1 sm:pb-2 text-base sm:text-lg text-slate-600">/<?= htmlspecialchars($perLabel) ?></div>
                    </div>

                    <?php if ($depositValid): ?>
                        <div class="mt-2 text-sm text-slate-600">
                            + €<?= htmlspecialchars((string)$deposit) ?> <?= t('create.security_deposit.help') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- small specs (2x2) -->
                <div class="mt-5 sm:mt-6 grid grid-cols-2 gap-3 sm:gap-4">
                    <div class="rounded-2xl bg-white/70 p-3 sm:p-4 ring-1 ring-black/5">
                        <div class="flex items-center gap-3">
                            <span class="h-9 w-9 rounded-2xl bg-green-50 flex items-center justify-center text-green-600">
                                <i class="fa-regular fa-clock"></i>
                            </span>
                            <div>
                                <div class="text-xs text-slate-500"><?= t('create.age_suitability') ?></div>
                                <div class="font-semibold text-slate-900 text-sm sm:text-base">
                                    <?= htmlspecialchars($age !== '' ? $age : '—') ?> <small><?= t('create.age') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white/70 p-3 sm:p-4 ring-1 ring-black/5">
                        <div class="flex items-center gap-3">
                            <span class="h-9 w-9 rounded-2xl bg-green-50 flex items-center justify-center text-green-600">
                                <i class="fa-solid fa-weight-hanging"></i>
                            </span>
                            <div>
                                <div class="text-xs text-slate-500"><?= t('create.max_weight_capacity') ?></div>
                                <div class="font-semibold text-slate-900 text-sm sm:text-base">
                                    <?= htmlspecialchars($maxWeight !== null && $maxWeight !== '' ? (string)$maxWeight . ' kg' : '—') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white/70 p-3 sm:p-4 ring-1 ring-black/5">
                        <div class="flex items-center gap-3">
                            <span class="h-9 w-9 rounded-2xl bg-green-50 flex items-center justify-center text-green-600">
                                <i class="fa-solid fa-battery-full"></i>
                            </span>
                            <div>
                                <div class="text-xs text-slate-500"><?= t('detail.battery') ?></div>
                                <div class="font-semibold text-slate-900 text-sm sm:text-base">
                                    <?= htmlspecialchars($batteryLife !== '' ? $batteryLife : '—') ?> <small>hours</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white/70 p-3 sm:p-4 ring-1 ring-black/5">
                        <div class="flex items-center gap-3">
                            <span class="h-9 w-9 rounded-2xl bg-green-50 flex items-center justify-center text-green-600">
                                <i class="fa-solid fa-gauge"></i>
                            </span>
                            <div>
                                <div class="text-xs text-slate-500"><?= t('create.max_speed') ?></div>
                                <div class="font-semibold text-slate-900 text-sm sm:text-base">
                                    <?= htmlspecialchars($maxSpeed !== '' ? $maxSpeed : '—') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- actions -->
                <div class="mt-5 sm:mt-6">
                    <button
                        id="seeLocationBtn"
                        type="button"
                        class="w-full rounded-2xl bg-green-600 py-3 text-base max-sm:text-[15px] font-semibold text-white hover:bg-green-700 <?= $hasMap ? '' : 'opacity-60 cursor-not-allowed' ?> "
                        <?= $hasMap ? '' : 'disabled' ?>>
                        <?= t('detail.button.see_location') ?>
                    </button>

                    <?php if (!$hasMap): ?>
                        <p class="mt-2 text-xs text-slate-500">
                            <?= t('detail.location.notavailable') ?>
                        </p>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- BOTTOM GRID -->
        <div class="my-8 sm:my-10 grid grid-cols-12 gap-6 sm:gap-8">
            <!-- Left column -->
            <div class="col-span-12 lg:col-span-8 space-y-6 sm:space-y-8">

                <!-- Description -->
                <div class="rounded-3xl max-sm:rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-black/5">
                    <h2 class="text-lg font-semibold text-slate-900"><?= t('create.description') ?></h2>
                    <p class="mt-4 text-slate-600 leading-relaxed text-sm sm:text-base">
                        <?= nl2br(htmlspecialchars($description !== '' ? $description : '—')) ?>
                    </p>
                </div>

                <!-- Technical Specifications -->
                <div class="rounded-3xl max-sm:rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-black/5">
                    <h2 class="text-lg font-semibold text-slate-900"><?= t('create.technical_details') ?></h2>

                    <div class="mt-5 sm:mt-6 grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-4">
                        <div class="rounded-2xl bg-slate-50 p-4 sm:p-5 text-center">
                            <div class="mx-auto mb-3 h-10 w-10 rounded-2xl bg-white ring-1 ring-black/5 flex items-center justify-center text-green-600">
                                <i class="fa-solid fa-battery-full"></i>
                            </div>
                            <div class="text-sm max-sm:text-[13px] text-slate-500"><?= t('detail.battery') ?></div>
                            <div class="mt-1 font-semibold text-slate-900 text-sm sm:text-base">
                                <?= htmlspecialchars($batteryLife !== '' ? $batteryLife : '—') ?>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4 sm:p-5 text-center">
                            <div class="mx-auto mb-3 h-10 w-10 rounded-2xl bg-white ring-1 ring-black/5 flex items-center justify-center text-green-600">
                                <i class="fa-regular fa-clock"></i>
                            </div>
                            <div class="text-sm max-sm:text-[13px] text-slate-500"><?= t('detail.charge') ?></div>
                            <div class="mt-1 font-semibold text-slate-900 text-sm sm:text-base">
                                <?= htmlspecialchars($chargeTime !== '' ? $chargeTime : '—') ?>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4 sm:p-5 text-center">
                            <div class="mx-auto mb-3 h-10 w-10 rounded-2xl bg-white ring-1 ring-black/5 flex items-center justify-center text-green-600">
                                <i class="fa-solid fa-gauge"></i>
                            </div>
                            <div class="text-sm max-sm:text-[13px] text-slate-500"><?= t('create.max_speed') ?></div>
                            <div class="mt-1 font-semibold text-slate-900 text-sm sm:text-base">
                                <?= htmlspecialchars($maxSpeed !== '' ? $maxSpeed : '—') ?>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4 sm:p-5 text-center">
                            <div class="mx-auto mb-3 h-10 w-10 rounded-2xl bg-white ring-1 ring-black/5 flex items-center justify-center text-green-600">
                                <i class="fa-solid fa-weight-hanging"></i>
                            </div>
                            <div class="text-sm max-sm:text-[13px] text-slate-500"><?= t('create.max_weight_capacity') ?></div>
                            <div class="mt-1 font-semibold text-slate-900 text-sm sm:text-base">
                                <?= htmlspecialchars($maxWeight !== null && $maxWeight !== '' ? (string)$maxWeight . ' kg' : '—') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rental Terms -->
                <div class="rounded-3xl max-sm:rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-black/5">
                    <h2 class="text-lg font-semibold text-slate-900"><?= t('create.rental_terms') ?></h2>

                    <p class="mt-4 text-slate-600 leading-relaxed text-sm sm:text-base">
                        <?= nl2br(htmlspecialchars($rentalTerms !== '' ? $rentalTerms : '—')) ?>
                    </p>

                    <?php if ($depositValid): ?>
                        <div class="mt-5 sm:mt-6 rounded-2xl bg-[#f8efe6] p-4 sm:p-5">
                            <div class="font-semibold text-slate-900">
                                <?= t('detail.deposit') ?>: €<?= htmlspecialchars((string)$deposit) ?>
                            </div>
                            <div class="mt-1 text-sm text-slate-600">
                                <?= t('detail.refund') ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Right column: Owner -->
            <div class="col-span-12 lg:col-span-4">
                <div class="rounded-3xl max-sm:rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-black/5">
                    <h2 class="text-lg font-semibold text-slate-900"><?= t('detail.owner.about') ?></h2>

                    <div class="mt-5 sm:mt-6 flex items-center gap-4">
                        <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-full bg-green-100 flex items-center justify-center font-bold text-green-700">
                            <?= htmlspecialchars($ownerInitials) ?>
                        </div>
                        <div>
                            <div class="text-base sm:text-lg font-semibold text-slate-900">
                                <?= htmlspecialchars($ownerName !== '' ? $ownerName : '—') ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 space-y-3 text-sm text-slate-600">
                        <?php if ($locationText !== ''): ?>
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-location-dot"></i>
                                <span class="break-words"><?= htmlspecialchars($locationText) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($ownerPhone !== ''): ?>
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-phone"></i>
                                <div class="font-semibold text-slate-900 hover:underline break-words">
                                    <?= htmlspecialchars($ownerPhone) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($ownerEmail !== ''): ?>
                            <div class="flex items-center gap-3">
                                <i class="fa-regular fa-envelope"></i>
                                <span class="break-words"><?= htmlspecialchars($ownerEmail) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Map Section -->
        <div id="mapSection"
            class="hidden rounded-3xl max-sm:rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-black/5">

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900">
                    <?= t('detail.map.pickup_location') ?>
                </h2>

                <button id="mapCloseBtn"
                    type="button"
                    class="text-sm text-red-600 hover:underline">
                    <?= t('detail.map.close') ?>
                </button>
            </div>

            <!-- Map container -->
            <div id="leafletMap"
                class="w-full max-sm:h-[280px] h-[360px] lg:h-[420px] rounded-2xl overflow-hidden border">
            </div>
        </div>

    </div>
</section>

<!-- DATA for external JS -->
<script>
    window.__carLocation = {
        lat: <?= $lat !== null ? json_encode($lat) : 'null' ?>,
        lon: <?= $lon !== null ? json_encode($lon) : 'null' ?>,
        label: <?= json_encode($locationText) ?>
    };
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>