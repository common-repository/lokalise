<?php
/** @var Lokalise_Model_Authorization_Request $model */
wp_register_style("lokalise_auth", LOKALISE_URL . 'public/authorization.css');
wp_enqueue_style('lokalise_auth');
get_header();
?>
<div class="lokalise-authorization-form">
    <div class="lokalise-logo-wrapper">
        <img src="<?php echo plugin_dir_url(LOKALISE_FILE) . 'public/lokalise-logo.png'; ?>" alt="Lokalise logo" />
    </div>
    <?php if ($model->isValid()) : ?>
    <h3><?php _e('Lokalise is asking your permission to', 'lokalise'); ?></h3>
    <ul>
        <li><?php _e('Create, read and update posts and pages', 'lokalise'); ?></li>
    </ul>
    <div class="lokalise-action-wrapper">
        <a href="?redirect_uri=<?=$model->redirectUri?>&choice=reject" class="button button-secondary">
            <?php _e('Reject', 'lokalise'); ?>
        </a>
        <a href="?redirect_uri=<?=$model->redirectUri?>&choice=accept" class="button button-primary">
            <?php _e('Accept', 'lokalise'); ?>
        </a>
    </div>
    <?php else : ?>
    <h3><?php _e('Authorization can not be performed.', 'lokalise'); ?></h3>
    <h4><?php _e('Resolve errors and try again.', 'lokalise'); ?></h4>
    <br />
    <a href="<?php echo $model->getReturnUrl(); ?>"><?php _e('Return to Lokalise', 'lokalise'); ?></a>
    <?php endif; ?>
    <div class="lokalise-signed-in-user">
        <?php printf(__('Signed in as <b>%s</b>.', 'lokalise'), wp_get_current_user()->display_name); ?>
        <a href="<?php echo $model->getSignInUrl(); ?>"><?php _e('Switch account', 'lokalise'); ?></a>
    </div>
</div>
<?php
get_footer();
?>
