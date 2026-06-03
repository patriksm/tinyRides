<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
// =====================================================
// OLD + ERRORS (from store())
// =====================================================
$old  = $_SESSION['old'] ?? [];
$errs = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

function oldv(array $old, string $key, string $default = ''): string
{
    return isset($old[$key]) ? (string)$old[$key] : $default;
}
function err(array $errs, string $key): string
{
    return isset($errs[$key]) ? (string)$errs[$key] : '';
}
function inputCls(string $base, string $errMsg): string
{
    return $base
        . ' focus:outline-none focus:ring-2 '
        . ($errMsg !== ''
            ? ' border-red-500 ring-1 ring-red-200 focus:ring-red-300'
            : ' border-gray-300 focus:ring-[var(--btn-bg)]');
}

// old values (DB city/street/lat/lon)
$oldCity   = oldv($old, 'city');
$oldStreet = oldv($old, 'street');
$oldLat    = oldv($old, 'latitude');
$oldLon    = oldv($old, 'longitude');

$categories = [
    ['id' => '1', 'label' => t('category.rc_cars')],
    ['id' => '2', 'label' => t('category.ride_on_cars')],
    ['id' => '3', 'label' => t('category.balance_bikes')],
    ['id' => '4', 'label' => t('category.mini_tractors')],
    ['id' => '5', 'label' => t('category.push_cars')],
    ['id' => '6', 'label' => t('category.electric_scooters')],
];

$perUnits = [
    ['id' => 'day', 'label' => t('create.per.day')],
    ['id' => 'hour', 'label' => t('create.per.hour')],
    ['id' => 'week', 'label' => t('create.per.week')],
];

$ageRanges = [
    ['id' => '0-2', 'label' => t('age.0_2')],
    ['id' => '2-5', 'label' => t('age.2_5')],
    ['id' => '5-8', 'label' => t('age.5_8')],
    ['id' => '8+', 'label' => t('age.8_plus')],
];

$conditions = [
    ['id' => 'new', 'label' => t('condition.new')],
    ['id' => 'excellent', 'label' => t('condition.excellent')],
    ['id' => 'good', 'label' => t('condition.good')],
    ['id' => 'used', 'label' => t('condition.used')],
];
?>

<section class="w-full pt-6 sm:pt-10 bg-[var(--main-bg)]">
    <div class="container bg-[var(--main-bg)]">
        <form
            id="carCreateForm"
            action="<?= rtrim(BASE_URL, '/') ?>/car/store"
            method="POST"
            enctype="multipart/form-data"
            class="w-full max-w-[710px] mx-auto mt-6 sm:mt-10 px-3 xs:px-4 sm:px-0">

            <!-- FORM ERRORS SUMMARY -->
            <?php if (!empty($errs)): ?>
                <div class="border border-red-300 bg-red-50 text-red-700 rounded-xl p-3 mb-6">
                    <p class="font-semibold mb-1"><?= t('validation.required') ?></p>
                    <ul class="list-disc ml-5 text-sm">
                        <?php foreach ($errs as $m): ?>
                            <li><?= htmlspecialchars((string)$m) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="add-text text-center mb-6 sm:mb-10">
                <h1 class="text-2xl sm:text-3xl font-bold"><?= t('create.page_title') ?></h1>
                <p class="text-sm sm:text-base text-[var(--body-pf)]">
                    <?= t('create.page_description') ?>
                </p>
            </div>

            <!-- basic info start -->
            <div class="basic-info border mb-6 sm:mb-10 bg-white p-4 sm:p-5 rounded-xl">
                <h1 class="text-base font-semibold"><?= t('create.basic_info') ?></h1>

                <!-- Model -->
                <div class="flex flex-col">
                    <label for="item_name" class="text-sm mt-3"><?= t('create.item_name') ?></label>
                    <?php $e = err($errs, 'model'); ?>
                    <input
                        id="item_name"
                        name="model"
                        type="text"
                        placeholder="<?= t('create.item_name.placeholder') ?>"
                        value="<?= htmlspecialchars(oldv($old, 'model')) ?>"
                        class="<?= htmlspecialchars(inputCls('w-full border px-3 py-2 rounded-xl mb-1 placeholder:text-sm', $e)) ?>">
                    <?php if ($e): ?>
                        <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                    <?php else: ?>
                        <small class="text-xs text-[var(--body-pf)]"><?= t('create.item_name.help') ?></small>
                    <?php endif; ?>
                </div>

                <!-- Brand -->
                <div class="flex flex-col mt-4">
                    <label for="brand" class="text-sm"><?= t('create.brand') ?></label>
                    <?php $e = err($errs, 'brand'); ?>
                    <input
                        id="brand"
                        name="brand"
                        type="text"
                        placeholder="<?= t('create.brand.placeholder') ?>"
                        value="<?= htmlspecialchars(oldv($old, 'brand')) ?>"
                        class="<?= htmlspecialchars(inputCls('w-full border px-3 py-2 rounded-xl mb-1 placeholder:text-sm', $e)) ?>">
                    <?php if ($e): ?>
                        <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Category -->
                <div class="flex flex-col mb-5">
                    <label for="category_id" class="text-sm mt-5"><?= t('create.category') ?></label>
                    <?php $e = err($errs, 'category_id');
                    $sel = oldv($old, 'category_id'); ?>
                    <select
                        name="category_id"
                        id="category_id"
                        class="<?= htmlspecialchars(inputCls('border w-full sm:w-[45%] px-3 py-2 rounded-xl hover:cursor-pointer text-[var(--body-pf)] mb-2', $e)) ?>">
                        <option value=""><?= t('create.category.placeholder') ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $sel === $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($e): ?>
                        <p class="text-sm text-red-600"><?= htmlspecialchars($e) ?></p>
                    <?php else: ?>
                        <small class="text-xs text-[var(--body-pf)]">
                            <?= t('create.category.help') ?>
                        </small>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="flex flex-col">
                    <label for="description" class="text-sm"><?= t('create.description') ?></label>
                    <textarea
                        name="description"
                        id="description"
                        placeholder="<?= t('create.description.placeholder') ?>"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 resize-none placeholder:text-sm focus:outline-none focus:ring-2 focus:ring-[var(--btn-bg)]"
                        rows="4"><?= htmlspecialchars(oldv($old, 'description')) ?></textarea>
                </div>
            </div>
            <!-- basic info end -->

            <!-- rental details start -->
            <div class="rental-details border mb-6 sm:mb-10 p-4 sm:p-5 bg-white rounded-xl">
                <h1 class="text-base font-semibold"><?= t('create.rental_details') ?></h1>

                <div class="rental-price flex flex-col sm:flex-row my-5 gap-4 sm:gap-8 lg:gap-20">
                    <!-- Price -->
                    <div class="flex flex-col w-full sm:w-auto">
                        <label for="price_per_day" class="text-sm"><?= t('create.rental_price') ?></label>
                        <?php $e = err($errs, 'price_per_day'); ?>
                        <input
                            id="price_per_day"
                            name="price_per_day"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            value="<?= htmlspecialchars(oldv($old, 'price_per_day')) ?>"
                            class="<?= htmlspecialchars(inputCls('w-full border px-3 py-2 rounded-xl', $e)) ?>">
                        <?php if ($e): ?>
                            <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                        <?php else: ?>
                            <small class="text-xs text-[var(--body-pf)] mt-1"><?= t('create.rental_price.help') ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Per -->
                    <div class="flex flex-col w-full sm:w-auto sm:mr-0 lg:mr-10">
                        <label for="per" class="text-sm"><?= t('create.per') ?></label>
                        <?php $e = err($errs, 'per');
                        $sel = oldv($old, 'per', 'day'); ?>
                        <select
                            name="per"
                            id="per"
                            class="<?= htmlspecialchars(inputCls('w-full border px-3 py-2 rounded-xl text-[var(--body-pf)]', $e)) ?>">
                            <?php foreach ($perUnits as $unit): ?>
                                <option value="<?= $unit['id'] ?>" <?= $sel === $unit['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($unit['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($e): ?>
                            <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- PICKUP ADDRESS GRID - CITY AND STREET -->
                <div class="pickup-address mt-4">
                    <h2 class="text-sm font-semibold mb-3"><?= t('create.pickup_address') ?? 'Pickup Address' ?></h2>

                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-6">
                        <!-- City Search -->
                        <div class="flex flex-col w-full relative">
                            <label for="citySearch" class="text-sm"><?= t('create.city') ?? 'City' ?></label>
                            <?php $e = err($errs, 'city'); ?>
                            <input
                                type="text"
                                id="citySearch"
                                name="city_search"
                                autocomplete="off"
                                placeholder="<?= t('create.city.placeholder') ?? 'Search city...' ?>"
                                value="<?= htmlspecialchars($oldCity) ?>"
                                class="<?= htmlspecialchars(inputCls('w-full border px-3 py-2 rounded-xl', $e)) ?>">

                            <!-- Hidden inputs for actual values -->
                            <input type="hidden" id="cityInput" name="city" value="<?= htmlspecialchars($oldCity) ?>">
                            <input type="hidden" id="latInput" name="latitude" value="<?= htmlspecialchars($oldLat) ?>">
                            <input type="hidden" id="lonInput" name="longitude" value="<?= htmlspecialchars($oldLon) ?>">

                            <!-- Suggestions dropdown -->
                            <div id="cityDropdown" class="suggestions hidden absolute top-full left-0 right-0 mt-1 border rounded-xl bg-white shadow-lg max-h-48 overflow-y-auto z-20">
                                <div id="cityList"></div>
                            </div>

                            <?php if ($e): ?>
                                <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Street Search -->
                        <div class="flex flex-col w-full relative">
                            <label for="streetSearch" class="text-sm"><?= t('create.street') ?? 'Street' ?></label>
                            <?php $e = err($errs, 'street'); ?>
                            <input
                                type="text"
                                id="streetSearch"
                                name="street_search"
                                autocomplete="off"
                                placeholder="<?= t('create.city.placeholder') ?>"
                                value="<?= htmlspecialchars($oldStreet) ?>"
                                class="<?= htmlspecialchars(inputCls('w-full border px-3 py-2 rounded-xl', $e)) ?>"
                                <?= empty($oldCity) ? 'disabled' : '' ?>>

                            <!-- Hidden input for actual value -->
                            <input type="hidden" id="streetInput" name="street" value="<?= htmlspecialchars($oldStreet) ?>">

                            <!-- Suggestions dropdown -->
                            <div id="streetDropdown" class="suggestions hidden absolute top-full left-0 right-0 mt-1 border rounded-xl bg-white shadow-lg max-h-48 overflow-y-auto z-20">
                                <div id="streetList"></div>
                            </div>

                            <?php if ($e): ?>
                                <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Age -->
                <div class="flex flex-col mt-5">
                    <label for="age_category" class="text-base"><?= t('create.age_suitability') ?></label>
                    <?php $e = err($errs, 'age_category');
                    $sel = oldv($old, 'age_category'); ?>
                    <select
                        name="age_category"
                        id="age_category"
                        class="<?= htmlspecialchars(inputCls('w-full sm:w-[45%] text-[var(--body-pf)] border rounded-xl px-3 py-2 mb-2 mt-1 text-sm', $e)) ?>">
                        <option value=""><?= t('create.age_suitability.placeholder') ?></option>
                        <?php foreach ($ageRanges as $age): ?>
                            <option value="<?= $age['id'] ?>" <?= $sel === $age['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($age['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($e): ?>
                        <p class="text-sm text-red-600"><?= htmlspecialchars($e) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Condition -->
                <div class="flex flex-col mt-3">
                    <label for="condition_status" class="text-base"><?= t('create.item_condition') ?></label>
                    <?php $e = err($errs, 'condition_status');
                    $sel = oldv($old, 'condition_status'); ?>
                    <select
                        name="condition_status"
                        id="condition_status"
                        class="<?= htmlspecialchars(inputCls('text-[var(--body-pf)] w-full sm:w-[45%] border px-3 py-2 text-base rounded-xl hover:cursor-pointer mt-1', $e)) ?>">
                        <option value=""><?= t('create.item_condition.placeholder') ?></option>
                        <?php foreach ($conditions as $condition): ?>
                            <option value="<?= $condition['id'] ?>" <?= $sel === $condition['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($condition['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($e): ?>
                        <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- technical details -->
            <div class="rental-details border mb-6 sm:mb-10 p-4 sm:p-5 bg-white rounded-xl">
                <h1 class="text-base font-semibold"><?= t('create.technical_details') ?></h1>

                <div id="electric-features" class="electric-features hidden">
                    <small class="text-xs sm:text-sm text-[var(--body-pf)]">
                        <?= t('create.electric_description') ?>
                    </small>

                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 lg:gap-10 mt-5">
                        <div class="w-full">
                            <label for="battery_life" class="text-sm"><?= t('create.battery_life') ?></label>
                            <input
                                id="battery_life"
                                name="battery_life"
                                type="number"
                                step="0.1"
                                min="0"
                                placeholder="<?= t('create.battery_life.placeholder') ?>"
                                value="<?= htmlspecialchars(oldv($old, 'battery_life')) ?>"
                                class="w-full border border-gray-300 placeholder:text-sm rounded-xl mt-1 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--btn-bg)]">
                        </div>

                        <div class="w-full">
                            <label for="charging_time" class="text-sm"><?= t('create.charging_time') ?></label>
                            <input
                                id="charging_time"
                                name="charging_time"
                                type="number"
                                step="0.1"
                                min="0"
                                placeholder="<?= t('create.charging_time.placeholder') ?>"
                                value="<?= htmlspecialchars(oldv($old, 'charging_time')) ?>"
                                class="w-full border border-gray-300 placeholder:text-sm rounded-xl mt-1 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--btn-bg)]">
                        </div>
                    </div>

                    <div class="flex flex-col mt-5">
                        <label for="max_speed" class="text-sm"><?= t('create.max_speed') ?></label>
                        <input
                            id="max_speed"
                            name="max_speed"
                            type="number"
                            step="0.1"
                            min="0"
                            placeholder="<?= t('create.max_speed.placeholder') ?>"
                            value="<?= htmlspecialchars(oldv($old, 'max_speed')) ?>"
                            class="w-full border border-gray-300 placeholder:text-sm rounded-xl mt-1 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--btn-bg)]">
                    </div>
                </div>

                <div class="mt-5 flex flex-col">
                    <label for="max_weight_capacity"><?= t('create.max_weight_capacity') ?></label>
                    <input
                        id="max_weight_capacity"
                        name="max_weight_capacity"
                        type="number"
                        step="1"
                        min="0"
                        placeholder="<?= t('create.max_weight_capacity.placeholder') ?>"
                        value="<?= htmlspecialchars(oldv($old, 'max_weight_capacity')) ?>"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 placeholder:text-sm mt-1 mb-2 focus:outline-none focus:ring-2 focus:ring-[var(--btn-bg)]">
                    <small class="text-[var(--body-pf)] text-xs mb-3">
                        <?= t('create.max_weight_capacity.help') ?>
                    </small>
                </div>

                <p class="disable-btn p-2 rounded-xl text-center text-sm text-[var(--body-pf)] bg-[#f1eee7]">
                    <?= t('create.non_electric_notice') ?>
                </p>
            </div>

            <!-- photos -->
            <div class="border mb-6 sm:mb-10 p-4 sm:p-5 bg-white rounded-xl">
                <h1 class="text-base font-semibold mb-2"><?= t('create.photos') ?></h1>
                <p class="text-sm text-gray-500 mb-4">
                    <?= t('create.photos.help') ?>
                </p>

                <label
                    for="photos"
                    class="w-28 h-28 sm:w-32 sm:h-32 flex flex-col items-center justify-center
               border-2 border-dashed border-gray-300 rounded-xl
               cursor-pointer hover:border-gray-400 transition">
                    <i class="fa-solid fa-arrow-up-from-bracket text-gray-500 text-xl mb-2"></i>
                    <span class="text-sm text-gray-600"><?= t('create.add_photo') ?></span>
                </label>

                <input
                    type="file"
                    name="photos[]"
                    id="photos"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    class="hidden">

                <small class="text-xs text-[var(--body-pf)] block mt-3">
                    <?= t('create.photos.supported') ?>
                </small>

                <div id="photo-previews" class="mt-4 flex flex-wrap gap-3"></div>
                <?php $e = err($errs, 'photos'); ?>
                <?php if ($e): ?>
                    <p class="text-sm text-red-600 mt-2"><?= htmlspecialchars($e) ?></p>
                <?php endif; ?>
            </div>

            <!-- contact information -->
            <div class="mb-6 sm:mb-10 p-4 sm:p-5 bg-white rounded-xl border">
                <h1 class="text-base font-semibold mb-4"><?= t('create.contact_information') ?></h1>

                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 lg:gap-20 mb-5">
                    <div class="flex flex-col w-full">
                        <label for="contact_name" class="text-sm"><?= t('create.contact_name') ?></label>
                        <?php $e = err($errs, 'contact_name'); ?>
                        <input
                            id="contact_name"
                            name="contact_name"
                            type="text"
                            placeholder="<?= t('create.contact_name.placeholder') ?>"
                            value="<?= htmlspecialchars(oldv($old, 'contact_name', (string)($_SESSION['user_name'] ?? ''))) ?>"
                            class="<?= htmlspecialchars(inputCls('w-full border border-gray-300 rounded-xl px-3 py-2 mt-1 placeholder:text-sm', $e)) ?>">
                        <?php if ($e): ?>
                            <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col w-full">
                        <label for="contact_phone" class="text-sm"><?= t('create.contact_phone') ?></label>
                        <?php $e = err($errs, 'contact_phone'); ?>
                        <input
                            id="contact_phone"
                            name="contact_phone"
                            type="text"
                            placeholder="<?= t('create.contact_phone.placeholder') ?>"
                            value="<?= htmlspecialchars(oldv($old, 'contact_phone')) ?>"
                            class="<?= htmlspecialchars(inputCls('w-full border border-gray-300 rounded-xl px-3 py-2 mt-1 placeholder:text-sm', $e)) ?>">
                        <?php if ($e): ?>
                            <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label for="contact_email" class="text-sm"><?= t('create.contact_email') ?></label>
                    <?php $e = err($errs, 'contact_email'); ?>
                    <input
                        id="contact_email"
                        name="contact_email"
                        type="email"
                        placeholder="<?= t('create.contact_email.placeholder') ?>"
                        value="<?= htmlspecialchars(oldv($old, 'contact_email', (string)($_SESSION['user_email'] ?? ''))) ?>"
                        class="<?= htmlspecialchars(inputCls('w-full border rounded-xl px-3 py-2 mt-1 placeholder:text-sm', $e)) ?>">
                    <?php if ($e): ?>
                        <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- rental terms -->
            <div class="mb-6 sm:mb-10 p-4 sm:p-5 bg-white rounded-xl border flex flex-col">
                <h1 class="text-base font-semibold mb-4"><?= t('create.rental_terms') ?></h1>

                <label for="rental_terms" class="text-sm mb-1"><?= t('create.additional_terms') ?></label>
                <textarea
                    name="rental_terms"
                    id="rental_terms"
                    placeholder="<?= t('create.additional_terms.placeholder') ?>"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 placeholder:text-sm resize-none h-[100px] mb-6 sm:mb-10 focus:outline-none focus:ring-2 focus:ring-[var(--btn-bg)]"><?= htmlspecialchars(oldv($old, 'rental_terms')) ?></textarea>

                <label for="deposit" class="text-sm"><?= t('create.security_deposit') ?></label>
                <?php $e = err($errs, 'deposit'); ?>
                <input
                    id="deposit"
                    name="deposit"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="<?= t('create.security_deposit.placeholder') ?>"
                    value="<?= htmlspecialchars(oldv($old, 'deposit')) ?>"
                    class="<?= htmlspecialchars(inputCls('w-full border rounded-xl placeholder:text-sm px-3 py-2 my-2', $e)) ?>">
                <?php if ($e): ?>
                    <p class="text-sm text-red-600 mt-1"><?= htmlspecialchars($e) ?></p>
                <?php endif; ?>

                <small class="text-[var(--body-pf)] text-xs"><?= t('create.security_deposit.help') ?></small>
            </div>

            <button
                type="submit"
                class="border rounded-xl text-center bg-[var(--btn-bg)] text-white w-full p-2 hover:bg-[var(--green-btn-hover)] mb-10">
                <?= t('€ List toy') ?>
            </button>
        </form>
    </div>
    <hr>
</section>

<!-- JS config: only path -->
<script>
    window.__citySuggestUrl =
        "<?= rtrim(BASE_URL, '/') ?>/public/api/locations/suggest.php?type=city";
    window.__streetSuggestUrl =
        "<?= rtrim(BASE_URL, '/') ?>/public/api/locations/suggest.php?type=street";
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>