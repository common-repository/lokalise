<?php /** @var Lokalise_Model_Dashboard $model */ ?>
<div class="wrap lokalise-dashboard">
    <h1><?php _e('Lokalise settings', 'lokalise'); ?></h1>

    <table class="form-table">
        <tbody>
        <tr>
            <th>
                <?php _e('Authorization secret', 'lokalise'); ?>
                <span class="option-about"><?php _e('This value is used to authorize Lokalise in your site.', 'lokalise'); ?></span>
            </th>
            <td>
                <?php if ($model->getSecret() !== null) : ?>
                    <code class="auth-secret"><?php echo $model->getSecret(); ?></code><br />
                <?php endif; ?>
                <a href="<?php echo $model->get('generate_url'); ?>"><?php _e('Generate secret', 'lokalise'); ?></a>
            </td>
        </tr>
        </tbody>
    </table>
</div>
