<?php
/**
 * Standardized staff page header.
 *
 * Expected variables (set before require):
 * - $pageTitle (string, required)
 * - $pageBreadcrumbText (string, optional)
 * - $pageBreadcrumbHtml (string, optional, overrides text)
 * - $pageActionHtml (string, optional)
 */
?>
<div class="main-content-header">
    <div class="main-topic">
        <h1><?php echo htmlspecialchars($pageTitle ?? 'Page'); ?></h1>
        <div class="page-header-actions">
            <?php if (!empty($pageActionHtml)): ?>
                <?php echo $pageActionHtml; ?>
            <?php else: ?>
                <span class="empty-action" aria-hidden="true"></span>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!empty($pageBreadcrumbHtml)): ?>
        <p class="MC-p"><?php echo $pageBreadcrumbHtml; ?></p>
    <?php elseif (!empty($pageBreadcrumbText)): ?>
        <p class="MC-p"><?php echo htmlspecialchars($pageBreadcrumbText); ?></p>
    <?php endif; ?>
</div>
