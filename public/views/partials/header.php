<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="/public/styles/base.css">
    <link rel="stylesheet" type="text/css" href="/public/styles/<?= $pageStyle ?>.css">
    <?php if (!empty($extraStyles)): ?>
        <?php foreach ($extraStyles as $style): ?>
            <link rel="stylesheet" type="text/css" href="<?= $style ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <script src="/public/scripts/main.js" defer></script>
    <?php if (!empty($pageScript)): ?>
        <script src="/public/scripts/<?= $pageScript ?>.js" defer></script>
    <?php endif; ?>
    <title><?= $pageTitle ?? 'BudgetApp' ?></title>
</head>
