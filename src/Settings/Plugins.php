<?php

namespace AdCaptcha\Settings\Plugins;

class Plugins {
     
    public function render_Plugins_settings() {
        $plugins = array(
            array(
                'label' => 'Wordpress',
                'logo' => 'wordpress_logo.png',
                'options' => array('login', 'register', 'comments')
            ),
            array(
                'label' => 'Woocommerce',
                'logo' => 'woocommerce_logo.png',
                'options' => array('login', 'register')
            ),
        );

        $selected_plugins = get_option('adcaptcha_selected_plugins') ? get_option('adcaptcha_selected_plugins') : array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
            if (!isset($nonce) || !wp_verify_nonce($nonce, 'adcaptcha_form_action')) {
                die('Invalid nonce');
            }
            $checked_ids = array_map('sanitize_text_field', wp_unslash($_POST['selected_plugins']));
            update_option('adcaptcha_selected_plugins', $checked_ids);
        }

        ?>
             <div>
                <h1>Manage Plugins</h1>
                <p>adCAPTCHA is compatible with the following plugins:</p>
                <?php
                    if (!empty($checked_ids)) {
                        echo '<p>Checked IDs: ' . implode(', ', $checked_ids) . '</p>';
                    }
                ?>
                <div class="integrating-description">
                    <form method="post" class="form">
                        <?php
                            foreach ($plugins as $plugin) {
                                printf('<img src="%s" class="logo"/>', esc_url(untrailingslashit(plugin_dir_url(dirname(dirname(__FILE__)))) . '/assets/' . $plugin['logo']));
                                foreach ($plugin['options'] as $option) {
                                    $optionId = $plugin['label'] . '_' . $option;
                                    $checked = in_array($optionId, $selected_plugins, true) ? 'checked' : '';
                                    ?>
                                    <input type="checkbox" id="<?php echo $optionId; ?>" name="selected_plugins[<?php echo $optionId; ?>]" value="<?php echo $optionId; ?>" <?php echo $checked; ?>>
                                    <label for="<?php echo $option; ?>"><?php echo ucfirst($option); ?></label><br>
                                    <?php
                                }
                            }
                        ?>
                        <?php wp_nonce_field('adcaptcha_form_action'); ?>
                        <button type="submit" class="save-button">Save</button>
                    </form>
                </div>
		<?php
    }
}
