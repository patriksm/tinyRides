<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$flash = $this->getMessage();
$user  = $user ?? [];
$cars  = $cars ?? [];

$token = (string)($_token ?? $this->csrfToken());

$name    = (string)($user['name'] ?? '');
$email   = (string)($user['email'] ?? '');
$phone   = (string)($user['phone'] ?? '');
$address = (string)($user['address'] ?? '');
$created = (string)($user['created_at'] ?? '');

$listingsCars = [];
$rentedCars   = [];

if (is_array($cars)) {
    foreach ($cars as $car) {
        $status = strtolower(trim((string)($car['status'] ?? 'active')));
        if ($status === 'rent') $status = 'rented';

        $car['status'] = $status;

        if ($status === 'rented') $rentedCars[] = $car;
        else $listingsCars[] = $car;
    }
}
?>

<section class="bg-[var(--main-bg)] profile-section">
    <div class="container pt-10 sm:pt-20 w-full max-w-[1000px] mx-auto px-4 sm:px-0">

        <?php if (!empty($flash)): ?>
            <div class="mb-4 p-3 rounded-xl border text-sm
                <?= ($flash['type'] ?? '') === 'success'
                    ? 'border-green-300 bg-green-50 text-green-700'
                    : 'border-red-300 bg-red-50 text-red-700' ?>">
                <?= htmlspecialchars((string)($flash['text'] ?? '')) ?>
            </div>
        <?php endif; ?>

        <!-- user info -->
        <div class="user-info mx-auto bg-white rounded-xl sm:mx-7">
            <div class="info-header border flex flex-col sm:flex-row sm:items-start justify-between gap-4 py-5 px-4 sm:px-10 rounded-xl">
                <div class="user-text min-w-0">
                    <h1 class="text-xl sm:text-2xl font-semibold break-words">
                        <?= htmlspecialchars($name !== '' ? $name : 'User') ?>
                    </h1>

                    <?php if ($created !== ''): ?>
                        <p class="text-sm sm:text-base text-[var(--body-pf)]">
                            <?= t('profile.member_since') ?>
                            <?= htmlspecialchars(date('F Y', strtotime($created))) ?>
                        </p>
                    <?php endif; ?>

                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-5 mt-4 sm:mt-5">
                        <div class="email flex items-center gap-2 min-w-0">
                            <i class="fa-regular fa-envelope text-[var(--body-pf)]"></i>
                            <small class="text-sm sm:text-base text-[var(--body-pf)] break-all"><?= htmlspecialchars($email) ?></small>
                        </div>

                        <?php if ($phone !== ''): ?>
                            <div class="phone flex items-center gap-2">
                                <i class="fa-solid fa-phone text-[var(--body-pf)]"></i>
                                <small class="text-sm sm:text-base text-[var(--body-pf)]"><?= htmlspecialchars($phone) ?></small>
                            </div>
                        <?php endif; ?>

                        <?php if ($address !== ''): ?>
                            <div class="location flex items-center gap-2 min-w-0">
                                <i class="fa-solid fa-location-dot text-[var(--body-pf)]"></i>
                                <small class="text-sm sm:text-base text-[var(--body-pf)] break-words"><?= htmlspecialchars($address) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="<?= rtrim(BASE_URL, '/') ?>/auth/logout"
                    class="border h-10 w-full sm:w-[110px] rounded-xl bg-[var(--main-bg)] hover:bg-[var(--btn-hover)] hover:text-white transition-all flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-arrow-right-from-bracket text-[var(--body-pf)] hover:text-white"></i>
                    <?= t('nav.logout') ?>
                </a>
            </div>
        </div>

        <!-- user menu -->
        <div class="users-menu">
            <div class="w-full max-w-[540px] mx-auto mt-6 sm:mt-10 rounded-xl border bg-[#f1eee7] p-1 overflow-x-auto">
                <div class="flex min-w-max gap-1">
                    <div data-tab="listings" class="tab-btn rounded-xl px-4 py-2 bg-[var(--main-bg)] hover:cursor-pointer text-sm sm:text-base flex items-center justify-center gap-2 whitespace-nowrap min-w-[150px]">
                        <i class="fa-solid fa-box-open"></i>
                        <?= t('profile.tabs.listings') ?>
                    </div>
                    <div data-tab="rentals" class="tab-btn rounded-xl px-4 py-2 hover:cursor-pointer text-sm sm:text-base flex items-center justify-center gap-2 whitespace-nowrap min-w-[150px]">
                        <i class="fa-solid fa-star"></i>
                        <?= t('profile.tabs.rentals') ?>
                    </div>
                    <div data-tab="settings" class="tab-btn rounded-xl px-4 py-2 hover:cursor-pointer text-sm sm:text-base flex items-center justify-center gap-2 whitespace-nowrap min-w-[170px]">
                        <i class="fa-solid fa-gear"></i>
                        <?= t('profile.tabs.settings') ?>
                    </div>
                </div>
            </div>

            <div class="min-h-[700px] sm:min-h-[900px] sm:mx-7">

                <!-- =========================
                     LISTINGS (active+inactive)
                     ========================= -->
                <div class="my-listing-section" data-panel="listings">
                    <div class="flex items-center justify-between gap-3 mt-6 sm:mt-10">
                        <h1 class="text-lg sm:text-xl font-semibold"><?= t('profile.listings.title') ?></h1>
                        <a href="<?= rtrim(BASE_URL, '/') ?>/car/create"
                            class="border py-2 sm:py-1 px-4 sm:px-7 rounded-xl bg-[var(--main-bg)] text-black hover:bg-[var(--btn-hover)] hover:text-white shrink-0">
                            <?= t('profile.listings.add_new') ?>
                        </a>
                    </div>

                    <!-- Empty state -->
                    <div id="listingsEmpty"
                        class="<?= !empty($listingsCars) ? 'hidden' : '' ?> border p-6 my-6 sm:my-10 bg-white rounded-xl text-[var(--body-pf)]">
                        <?= t('profile.listings.empty') ?>
                    </div>

                    <!-- Listings list wrapper -->
                    <div id="listingsList" class="mt-4 space-y-4 <?= empty($listingsCars) ? 'hidden' : '' ?>">
                        <?php if (!empty($listingsCars)): ?>
                            <?php foreach ($listingsCars as $car): ?>
                                <?php
                                $uuid  = trim((string)($car['uuid'] ?? ''));
                                if ($uuid === '') continue;

                                $status = (string)($car['status'] ?? 'active');
                                $postExtraCls = '';
                                if ($status === 'inactive') $postExtraCls = ' bg-slate-50';

                                $statusLabel = t('status.' . $status);
                                $statusBadgeClass = $status === 'active'
                                    ? 'bg-[var(--btn-bg)]'
                                    : ($status === 'inactive' ? 'bg-red-600' : 'bg-orange-500');
                                ?>
                                <div class="posts border flex items-center justify-between gap-3 p-4 my-4 sm:my-5 bg-white rounded-xl<?= $postExtraCls ?>"
                                    data-car-id="<?= htmlspecialchars($uuid) ?>"
                                    data-status="<?= htmlspecialchars($status) ?>">

                                    <div class="post-text min-w-0 flex-1">
                                        <h3 class="text-base flex flex-wrap items-center gap-2 break-words">
                                            <?= htmlspecialchars(trim(($car['brand'] ?? '') . ' ' . ($car['model'] ?? ''))) ?>

                                            <span class="status-badge border px-2 py-0.5 rounded-xl text-white text-sm <?= $statusBadgeClass ?>">
                                                <?= htmlspecialchars($statusLabel) ?>
                                            </span>
                                        </h3>
                                        <small class="text-sm text-[var(--body-pf)]">
                                            <?= htmlspecialchars((string)($car['price_per_day'] ?? '')) ?>/day
                                        </small>
                                    </div>

                                    <div class="relative shrink-0 ml-2">
                                        <button type="button"
                                            class="post-menu-btn border rounded-xl w-10 h-10 flex items-center justify-center bg-[var(--main-bg)] hover:bg-[var(--btn-hover)] transition"
                                            aria-haspopup="true"
                                            aria-expanded="false">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>

                                        <div class="post-menu hidden absolute right-0 mt-2 w-52 bg-white border rounded-xl shadow-lg z-50 max-sm:w-[180px]">
                                            <button type="button"
                                                class="menu-item w-full text-left px-4 py-2 hover:bg-gray-50"
                                                data-action="edit">
                                                <i class="fa-regular fa-pen-to-square mr-2"></i> <?= t('actions.edit') ?>
                                            </button>

                                            <div class="relative">
                                                <div class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center justify-between cursor-pointer select-none"
                                                    data-status-trigger>
                                                    <span class="max-sm:text-[15px] whitespace-nowrap"><i class="fa-solid fa-arrows-rotate mr-2"></i><?= t('actions.change_status') ?></span>
                                                    <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                                                </div>

                                                <div class="hidden absolute top-1/2 -translate-y-1/2 right-full mr-1 w-44 bg-white border rounded-xl shadow-lg z-[9999] max-sm:w-[170px]"
                                                    data-status-submenu></div>
                                            </div>

                                            <button type="button"
                                                class="menu-item w-full text-left px-4 py-2 hover:bg-red-50 text-red-600"
                                                data-action="delete">
                                                <i class="fa-regular fa-trash-can mr-2"></i> <?= t('actions.delete') ?>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- =========================
                     RENTALS (rented)
                     ========================= -->
                <div class="my-rentals-section hidden mt-6 sm:mt-10" data-panel="rentals">
                    <h1 class="text-lg sm:text-xl font-semibold mt-4"><?= t('profile.rentals.title') ?></h1>

                    <?php if (empty($rentedCars)): ?>
                        <div id="rentalsEmpty" class="border p-6 mt-4 bg-white rounded-xl text-[var(--body-pf)]">
                            <?= t('profile.rentals.empty') ?>
                        </div>
                        <div id="rentalsList" class="mt-4 space-y-4"></div>
                    <?php else: ?>
                        <div id="rentalsEmpty" class="hidden border p-6 mt-4 bg-white rounded-xl text-[var(--body-pf)]">
                            <?= t('profile.rentals.empty') ?>
                        </div>

                        <div id="rentalsList" class="mt-4 space-y-4">
                            <?php foreach ($rentedCars as $car): ?>
                                <?php
                                $uuid  = trim((string)($car['uuid'] ?? ''));
                                if ($uuid === '') continue;
                                ?>
                                <div class="posts border flex items-center justify-between gap-3 p-4 my-4 sm:my-5 bg-white rounded-xl rented-post"
                                    data-car-id="<?= htmlspecialchars($uuid) ?>"
                                    data-status="rented">

                                    <div class="post-text min-w-0 flex-1">
                                        <h3 class="text-base flex flex-wrap items-center gap-2 break-words">
                                            <?= htmlspecialchars(trim(($car['brand'] ?? '') . ' ' . ($car['model'] ?? ''))) ?>

                                            <span class="status-badge border px-2 py-0.5 rounded-xl text-white text-sm bg-orange-500">
                                                <?= t('status.rented') ?>
                                            </span>
                                        </h3>
                                        <small class="text-sm text-[var(--body-pf)]">
                                            <?= htmlspecialchars((string)($car['price_per_day'] ?? '')) ?>/day
                                        </small>
                                    </div>

                                    <div class="relative shrink-0 ml-2">
                                        <button type="button"
                                            class="post-menu-btn border rounded-xl w-10 h-10 flex items-center justify-center bg-[var(--main-bg)] hover:bg-[var(--btn-hover)] transition"
                                            aria-haspopup="true"
                                            aria-expanded="false">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>

                                        <div class="post-menu hidden absolute right-0 mt-2 w-52 bg-white border rounded-xl shadow-lg z-50">
                                            <button type="button"
                                                class="menu-item w-full text-left px-4 py-2 hover:bg-gray-50"
                                                data-action="edit">
                                                <i class="fa-regular fa-pen-to-square mr-2"></i> <?= t('actions.edit') ?>
                                            </button>

                                            <div class="relative">
                                                <div class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center justify-between cursor-pointer select-none"
                                                    data-status-trigger>
                                                    <span><i class="fa-solid fa-arrows-rotate mr-2"></i> <?= t('actions.change_status') ?></span>
                                                    <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                                                </div>

                                                <div class="hidden absolute top-1/2 -translate-y-1/2 right-full mr-1 w-44 bg-white border rounded-xl shadow-lg z-[9999]"
                                                    data-status-submenu></div>
                                            </div>

                                            <button type="button"
                                                class="menu-item w-full text-left px-4 py-2 hover:bg-red-50 text-red-600"
                                                data-action="delete">
                                                <i class="fa-regular fa-trash-can mr-2"></i> <?= t('actions.delete') ?>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- =========================
                     SETTINGS
                     ========================= -->
                <div class="my-settings-section mt-6 sm:mt-10 hidden" data-panel="settings">

                    <!-- Account Settings -->
                    <div class="border bg-white rounded-2xl p-4 sm:p-8 shadow-sm">
                        <div class="mb-6">
                            <h2 class="text-lg sm:text-xl font-semibold"><?= t('profile.settings.account_settings') ?></h2>
                            <p class="text-[var(--body-pf)] text-sm mt-1"><?= t('profile.settings.update_info') ?></p>
                        </div>

                        <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/user/updateProfile" class="space-y-6">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($token) ?>">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                                <div>
                                    <label class="block text-sm font-medium mb-2"><?= t('profile.settings.full_name') ?></label>
                                    <input type="text" name="name"
                                        placeholder="<?= t('profile.settings.full_name.placeholder') ?>"
                                        value="<?= htmlspecialchars($name) ?>"
                                        class="w-full border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-200"
                                        required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2"><?= t('profile.settings.email') ?></label>
                                    <input type="email" name="email"
                                        placeholder="<?= t('profile.settings.email.placeholder') ?>"
                                        value="<?= htmlspecialchars($email) ?>"
                                        class="w-full border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-200"
                                        required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2"><?= t('profile.settings.phone') ?></label>
                                    <input type="text" name="phone"
                                        value="<?= htmlspecialchars($phone) ?>"
                                        class="w-full border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-200"
                                        placeholder="<?= t('profile.settings.phone.placeholder') ?>">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2"><?= t('profile.settings.location') ?></label>
                                    <input type="text" name="address"
                                        value="<?= htmlspecialchars($address) ?>"
                                        class="w-full border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-200"
                                        placeholder="<?= t('profile.settings.location.placeholder') ?>">
                                </div>
                            </div>
                            <button type="submit"
                                class="border rounded-xl px-6 py-2 bg-[var(--btn-bg)] text-white hover:bg-[var(--btn-hover)] transition w-full sm:w-auto">
                                <?= t('profile.settings.save_changes') ?>
                            </button>
                        </form>
                    </div>

                    <!-- Password -->
                    <div class="border bg-white rounded-2xl p-4 sm:p-8 shadow-sm mt-6 sm:mt-8">
                        <div class="mb-6">
                            <h2 class="text-lg sm:text-xl font-semibold"><?= t('profile.settings.change_password') ?></h2>
                            <p class="text-[var(--body-pf)] text-sm mt-1"><?= t('profile.settings.password_secure') ?></p>
                        </div>

                        <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/user/changePassword" class="space-y-6">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($token) ?>">

                            <div>
                                <label class="block text-sm font-medium mb-2"><?= t('profile.settings.current_password') ?></label>
                                <input type="password" name="current_password"
                                    class="w-full border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-200"
                                    autocomplete="current-password" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2"><?= t('profile.settings.new_password') ?></label>
                                <input type="password" name="new_password"
                                    class="w-full border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-200"
                                    autocomplete="new-password" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2"><?= t('profile.settings.confirm_password') ?></label>
                                <input type="password" name="confirm_password"
                                    class="w-full border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-200"
                                    autocomplete="new-password" required>
                            </div>

                            <button type="submit"
                                class="border rounded-xl px-6 py-2 bg-[var(--btn-bg)] text-white hover:bg-[var(--btn-hover)] transition w-full sm:w-auto">
                                <?= t('profile.settings.update_password') ?>
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>

    </div>
</section>

<!-- Delete Modal -->
<div id="delete-modal" class="hidden fixed inset-0 z-[999]">
    <div class="delete-modal-backdrop absolute inset-0 bg-black/40"></div>

    <div class="relative mx-auto mt-24 sm:mt-40 w-[92%] max-w-[420px] bg-white rounded-2xl p-5 shadow-xl">
        <h2 class="text-lg font-semibold"><?= t('post.delete') ?></h2>
        <p class="text-sm text-[var(--body-pf)] mt-2">
            <?= t('post.undone') ?>
        </p>

        <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
            <button type="button" id="delete-cancel"
                class="border rounded-xl px-4 py-2 sm:py-1 bg-[var(--main-bg)] hover:bg-[var(--btn-hover)] transition w-full sm:w-auto">
                <?= t('create.cancel') ?>
            </button>

            <form id="delete-form" method="POST" action="" class="w-full sm:w-auto">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($token) ?>">
                <button type="submit"
                    class="border rounded-xl px-4 py-2 sm:py-1 bg-red-600 text-white hover:bg-red-700 transition w-full sm:w-auto">
                    <?= t('post_delete') ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    window.BASE_URL = "<?= rtrim(BASE_URL, '/') ?>";
    window.CSRF_TOKEN = "<?= htmlspecialchars($token) ?>";
    window.PROFILE_I18N = {
        statusActive: "<?= htmlspecialchars(t('status.active')) ?>",
        statusInactive: "<?= htmlspecialchars(t('status.inactive')) ?>",
        statusRented: "<?= htmlspecialchars(t('status.rented')) ?>",
        setInactive: "<?= htmlspecialchars(t('status.set_inactive')) ?>",
        setRented: "<?= htmlspecialchars(t('status.set_rented')) ?>"
    };
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>