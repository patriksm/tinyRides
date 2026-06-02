<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$flash = $this->getMessage();
$oldName  = (string)($_GET['name'] ?? '');
$oldEmail = (string)($_GET['email'] ?? '');
$token = (string)($_token ?? ($this->csrfToken() ?? ''));
?>

<div class="wrapper bg-[var(--main-bg)]">
    <div class="container flex justify-center">
        <form action="<?= BASE_URL ?>/auth/signup" method="POST" class="mt-20 bg-white p-5 w-[450px] border rounded-xl">

            <!-- Flash message -->
            <?php if (!empty($flash)): ?>
                <div class="mb-4 p-3 rounded-xl border text-sm
          <?= ($flash['type'] ?? '') === 'success' ? 'border-green-300 bg-green-50 text-green-700' : 'border-red-300 bg-red-50 text-red-700' ?>">
                    <?= $flash['text'] ?? '' ?>
                </div>
            <?php endif; ?>

            <!-- CSRF -->
            <input type="hidden" name="_token" value="<?= htmlspecialchars($token) ?>">

            <div class="signup-text text-center p-5">
                <div class="logo">
                    <i class="fa-solid fa-car-side border hidden"></i>
                </div>
                <h1 class="text-2xl font-bold"><?= t('auth.register.title') ?></h1>
                <p class="text-sm text-[var(--body-pf)] my-2"><?= t('auth.register.subtitle') ?></p>
            </div>

            <label for="name"><?= t('auth.register.name') ?></label>
            <div class="border p-2 rounded-xl mb-3 mt-1">
                <i class="fa-regular fa-user text-[var(--body-pf)]"></i>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($oldName) ?>"
                    placeholder="<?= t('auth.register.name_placeholder') ?>"
                    required
                    class="focus:outline-none">
            </div>

            <label for="email"><?= t('auth.register.email') ?></label>
            <div class="border p-2 rounded-xl mb-3 mt-1">
                <i class="fa-regular fa-envelope text-[var(--body-pf)]"></i>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="<?= htmlspecialchars($oldEmail) ?>"
                    placeholder="<?= t('auth.register.email_placeholder') ?>"
                    required
                    class="focus:outline-none">
            </div>

            <label for="phone"><?= t('auth.register.phone') ?></label>
            <div class="border p-2 rounded-xl mb-3 mt-1">
                <i class="fa-solid fa-phone text-[var(--body-pf)]"></i>
                <input type="text"
                    name="phone"
                    id="phone"
                    placeholder="<?= t('auth.register.phone_placeholder') ?>"
                    required
                    class="focus:outline-none"
                    value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>">
            </div>


            <label for="password"><?= t('auth.register.password') ?></label>
            <div class="border p-2 rounded-xl mb-3 mt-1">
                <i class="fa-solid fa-lock text-[var(--body-pf)]"></i>
                <input
                    type="password"
                    required
                    name="password"
                    id="password"
                    placeholder="<?= t('auth.register.password_placeholder') ?>"
                    class="focus:outline-none">
            </div>

            <label for="confirm_password"><?= t('auth.register.confirm_password') ?></label>
            <div class="border p-2 rounded-xl mb-3 mt-1">
                <i class="fa-solid fa-lock text-[var(--body-pf)]"></i>
                <input
                    type="password"
                    required
                    name="confirm_password"
                    id="confirm_password"
                    placeholder="<?= t('auth.register.confirm_password_placeholder') ?>"
                    class="focus:outline-none">
            </div>

            <button
                type="submit"
                class="bg-[var(--btn-bg)] text-center border rounded-xl text-white w-full p-1 my-3 font-normal hover:bg-[var(--green-btn-hover)]">
                <?= t('auth.register.button') ?>
            </button>

            <p class="text-center text-[var(--body-pf)]">
                <?= t('auth.register.has_account') ?>
                <a href="<?= BASE_URL ?>/auth/login" class="text-[var(--btn-bg)] hover:underline"><?= t('auth.register.signin') ?></a>
            </p>

        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>