<!-- Exibição de mensagem de sucesso ou erro -->
<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-<?= $_SESSION['msg']['alert']; ?> alert-dismissible fade show w-100 mt-3" role="alert">
        <?= $_SESSION['msg']['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">  
        </button>
    </div>
<?php unset($_SESSION['msg']); endif; ?>

<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
    </div>
</div>
<button type="button" class="btn btn-primary" id="liveToastBtn">Show live toast</button>