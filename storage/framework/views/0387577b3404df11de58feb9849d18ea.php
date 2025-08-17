<?php
    $prevUrl = $prevUrl ?? url()->previous();
    $nextLabel = $nextLabel ?? 'Suivant';
    $nextFormId = $nextFormId ?? null; // if provided, submit that form
    $isFinal = $isFinal ?? false;
?>

<div class="flex justify-between items-center mt-12 pt-6">
    <div class="w-full max-w-4xl mx-auto flex justify-between items-center gap-4 mt-4">
    <a href="<?php echo e($prevUrl); ?>" class="btn btn-ghost">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
        Retour
    </a>

    <?php if($nextFormId): ?>
        <button type="submit" form="<?php echo e($nextFormId); ?>" class="btn btn-primary">
            <?php echo e($isFinal ? 'Finaliser' : $nextLabel); ?>

            <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
        </button>
    <?php else: ?>
        <button class="btn btn-primary" onclick="document.querySelector('form')?.submit()">
            <?php echo e($isFinal ? 'Finaliser' : $nextLabel); ?>

            <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
        </button>
    <?php endif; ?>
    </div>
</div>


<?php /**PATH /Users/laminebarro/agent-O/resources/views/components/onboarding/footer.blade.php ENDPATH**/ ?>