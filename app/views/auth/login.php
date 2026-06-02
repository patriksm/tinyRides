<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$flash = $this->getMessage();
$oldEmail = (string)($_GET['email'] ?? '');
$token = (string)($_token ?? ($this->csrfToken() ?? ''));
?>

<div class="wrapper bg-[var(--main-bg)] min-h-[600px]">
    <div class="container flex justify-center">
        <form action="<?= BASE_URL ?>/auth/authenticate" method="POST" class="mt-20 bg-white p-5 w-[450px] border rounded-xl">

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
                <h1 class="text-2xl font-bold"><?= t('auth.login.title') ?></h1>
                <p class="text-sm text-[var(--body-pf)] my-2"><?= t('auth.login.subtitle') ?></p>
            </div>

            <label for="email"><?= t('auth.login.email') ?></label>
            <div class="border p-2 rounded-xl mb-3 mt-1">
                <i class="fa-regular fa-envelope text-[var(--body-pf)]"></i>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="<?= htmlspecialchars($oldEmail) ?>"
                    placeholder="<?= t('auth.login.email_placeholder') ?>"
                    class="focus:outline-none">
            </div>

            <label for="password"><?= t('auth.login.password') ?></label>
            <div class="border p-2 rounded-xl mb-3 mt-1">
                <i class="fa-solid fa-lock text-[var(--body-pf)]"></i>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="<?= t('auth.login.password_placeholder') ?>"
                    class="focus:outline-none">
            </div>

            <button
                type="submit"
                class="bg-[var(--btn-bg)] text-center border rounded-xl text-white w-full p-1 my-3 font-normal hover:bg-[var(--green-btn-hover)]">
                <?= t('auth.login.button') ?>
            </button>

            <p class="text-center text-[var(--body-pf)]">
                <?= t('auth.login.no_account') ?>
                <a href="<?= BASE_URL ?>/auth/register" class="text-[var(--btn-bg)] hover:underline"><?= t('auth.login.signup') ?></a>
            </p>

        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>