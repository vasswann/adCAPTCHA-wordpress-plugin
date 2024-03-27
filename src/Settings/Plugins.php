<?php

namespace AdCaptcha\Settings\Plugins;

class Plugins {
     
    public function render_Plugins_settings() {
        $plugins = array(
            array(
                'label' => 'Wordpress',
                'logo' => 'wordpress_logo.png',
                'logo_width' => '200px',
                'logo_height' => '40px',
                'options' => array('Login', 'Register', 'Comments', 'Forgot Password')
            ),
            array(
                'label' => 'Woocommerce',
                'logo' => 'woocommerce_logo.png',
                'logo_width' => '200px',
                'logo_height' => '40px',
                'options' => array('Login', 'Register', 'Forgot Password')
            ),
            array(
                'label' => 'ContactForm7',
                'logo' => 'contactForm7_logo.png',
                'logo_width' => '200px',
                'logo_height' => '40px',
                'options' => array('Forms')
            ),
            array(
                'label' => 'Mailchimp',
                'logo' => 'mailchimp_logo.png',
                'logo_width' => '200px',
                'logo_height' => '40px',
                'options' => array('Forms')
            ),
        );

        $saved_setting = false;

        $selected_plugins = get_option('adcaptcha_selected_plugins') ? get_option('adcaptcha_selected_plugins') : array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
            if (!isset($nonce) || !wp_verify_nonce($nonce, 'adcaptcha_form_action')) {
                die('Invalid nonce');
            }
            $checked_ids = array_map('sanitize_text_field', wp_unslash($_POST['selected_plugins']));
            $selected_plugins = isset($checked_ids) && is_array($checked_ids) 
            ? $checked_ids
            : array();
            update_option('adcaptcha_selected_plugins', $selected_plugins);
            $saved_setting = true;
        }

        ?>
                <div class="plugins-container">
                    <h1>Manage Plugins</h1>
                    <p>adCAPTCHA is compatible with the following plugins:</p>
                    <?php if ($saved_setting === true) : ?>
                        <div style="background-color: #22C55E; color: #ffffff; padding: 10px; border-radius: 5px; margin: 10px 0; max-width: 450px; font-size: 14px;">
                            Settings saved. Captcha will be displayed in the selected plugins.
                        </div>
                    <?php endif; ?>
                    <form method="post" class="plugin-form">
                        <div class="plugins-layout">
                            <?php
                                foreach ($plugins as $plugin) {
                                    ?>
                                        <div class="plugin-container">
                                            <?php
                                            printf('<img class="plugin_logo" src="%s" height="%s" width="%s" />', esc_url(untrailingslashit(plugin_dir_url(dirname(dirname(__FILE__)))) . '/assets/' . $plugin['logo']), $plugin['logo_height'], $plugin['logo_width']);
                                            foreach ($plugin['options'] as $option) {
                                                $optionId = $plugin['label'] . '_' . str_replace(' ', '', $option);
                                                $checked = in_array($optionId, $selected_plugins, true) ? 'checked' : '';
                                                ?>
                                                <div class="checkbox-container">
                                                    <input type="checkbox" id="<?php echo $optionId; ?>" name="selected_plugins[<?php echo $optionId; ?>]" value="<?php echo $optionId; ?>" <?php echo $checked; ?>>
                                                    <label class="checkbox-label" for="<?php echo $option; ?>"><?php echo $option; ?></label><br>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    <?php
                                }
                            ?>
                        </div>
                        <?php wp_nonce_field('adcaptcha_form_action'); ?>
                        <button type="submit" class="save-button">Save</button>
                    </form>
                </div>
		<?php
    }
}
