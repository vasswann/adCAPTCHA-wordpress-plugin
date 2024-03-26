<?php

namespace AdCaptcha\Settings\Plugins;

class Plugins {
     
    public function render_Plugins_settings() {
        $plugins = array(
            array(
                'label' => 'Wordpress',
                'logo' => 'wordpress_logo.png',
                'logo_width' => '200px',
                'logo_height' => '100px',
                'options' => array('Login', 'Register', 'Comments', 'Forgot Password')
            ),
            array(
                'label' => 'Woocommerce',
                'logo' => 'woocommerce_logo.png',
                'logo_width' => '250px',
                'logo_height' => '100px',
                'options' => array('Login', 'Register', 'Forgot Password')
            ),
            array(
                'label' => 'ContactForm7',
                'logo' => 'contactForm7_logo.png',
                'logo_width' => '250px',
                'logo_height' => '50px',
                'options' => array('Forms')
            ),
            array(
                'label' => 'Mailchimp',
                'logo' => 'mailchimp_logo.png',
                'logo_width' => '250px',
                'logo_height' => '50px',
                'options' => array('Forms')
            ),
        );

        $selected_plugins = get_option('adcaptcha_selected_plugins') ? get_option('adcaptcha_selected_plugins') : array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
            if (!isset($nonce) || !wp_verify_nonce($nonce, 'adcaptcha_form_action')) {
                die('Invalid nonce');
            }
            $checked_ids = isset($_POST['selected_plugins']) && is_array($_POST['selected_plugins']) 
            ? array_map('sanitize_text_field', wp_unslash($_POST['selected_plugins'])) 
            : array();
            $selected_plugins = $checked_ids;
            update_option('adcaptcha_selected_plugins', $checked_ids);
        }

        ?>
                <div class="plugins-container">
                    <h1>Manage Plugins</h1>
                    <p>adCAPTCHA is compatible with the following plugins:</p>
                    <form method="post" class="plugin-form">
                        <?php
                            foreach ($plugins as $plugin) {
                                printf('<img class="plugin_logo" src="%s" height="%s" width="%s" />', esc_url(untrailingslashit(plugin_dir_url(dirname(dirname(__FILE__)))) . '/assets/' . $plugin['logo']), $plugin['logo_height'], $plugin['logo_width']);
                                foreach ($plugin['options'] as $option) {
                                    $optionId = $plugin['label'] . '_' . str_replace(' ', '', $option);
                                    $checked = in_array($optionId, $selected_plugins, true) ? 'checked' : '';
                                    ?>
                                        <div class="checkbox-container">
                                            <input type="checkbox" id="<?php echo $optionId; ?>" name="selected_plugins[<?php echo $optionId; ?>]" value="<?php echo $optionId; ?>" <?php echo $checked; ?>>
                                            <label class="checkbox-label" for="<?php echo $option; ?>"><?php echo ucfirst($option); ?></label><br>
                                        </div>
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
