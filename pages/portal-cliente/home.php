<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <div class="clearfix">
            <div class="float-start">
                <?php if (!empty($office['logo'])): ?>
                <img src="<?= $office['logo']; ?>" class="mb-2" alt="logo" style="height: 30px; max-width: 150px; object-fit: contain;">
                <?php endif; ?>
                <h5 class="mb-0 caption fw-semibold"><?= $office['name']; ?></h5>
            </div>
        </div>
    </div>
</div>