<?php /** @var Lokalise_Model_ProviderNotice $model */ ?>
<div class="notice notice-warning">
    <p>
        <?php _e("No localization plugin is enabled to fully utilize Lokalise Companion Plugin.<br />" .
        "At least one of following plugins need to be enabled:", 'lokalise'); ?><br />
        <?php foreach ($model->getProviders() as $provider) : ?>
            <?php echo $provider; ?><br />
        <?php endforeach; ?>
    </p>
</div>
