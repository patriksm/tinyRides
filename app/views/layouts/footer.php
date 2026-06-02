</main>

<!-- Footer -->
<footer class="">
    <div class="container">
        <div class="footer-text flex flex-col items-center justify-center mt-10">
            <div class="footer-logo flex items-center">
                <div class="logo mr-2">
                    <i class="fa-solid fa-car-side py-4 px-6 border flex items-center justify-center rounded-2xl text-slate-50 bg-[var(--btn-bg)]"></i>
                </div>
                <h2 class="font-bold text-xl">TinyRides</h2>
            </div>
            <p class="text-center text-[var(--body-pf)] mt-5"><?= t('footer.description') ?></p>
            <hr class="text-xl w-[80vw] h-auto my-2">
            <p class="text-center text-[var(--body-pf)] text-sm"><?= t('footer.copyright') ?></p>
        </div>
    </div>
</footer>
<script src="<?= BASE_URL ?>/public/js/app.js"></script>
<script>
    window.BASE_URL = "<?= rtrim(BASE_URL, '/') ?>";
    window.CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>";
</script>
</body>

</html>