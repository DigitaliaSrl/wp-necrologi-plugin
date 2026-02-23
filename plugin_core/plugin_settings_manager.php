<?php
/*
namespace Digitalia;

class PluginSettingsWrapper {

    private $option_group;
    private $option_name;
    private $settings_page;
    private $fields = [];

    public function __construct($option_group, $option_name, $settings_page) {
        $this->option_group = $option_group;
        $this->option_name = $option_name;
        $this->settings_page = $settings_page;
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_field($id, $label, $type = 'text', $args = []) {
        $this->fields[] = compact('id', 'label', 'type', 'args');
    }

    public function register_settings() {
        register_setting($this->option_group, $this->option_name);

        add_settings_section(
            "{$this->option_name}_section",
            '',
            null,
            $this->settings_page
        );

        foreach ($this->fields as $field) {
            add_settings_field(
                $field['id'],
                $field['label'],
                [$this, 'render_field'],
                $this->settings_page,
                "{$this->option_name}_section",
                $field
            );
        }
    }

    public function render_field($field) {
        $options = get_option($this->option_name);
        $value = $options[$field['id']] ?? '';
        $id = $field['id'];
        $name = "{$this->option_name}[$id]";
        $args = $field['args'];

        switch ($field['type']) {
            case 'text':
                echo "<input type='text' id='$id' name='$name' value='" . esc_attr($value) . "' class='regular-text'>";
                break;

            case 'textarea':
                echo "<textarea id='$id' name='$name' rows='5' cols='50'>" . esc_textarea($value) . "</textarea>";
                break;

            case 'color':
                echo "<input type='text' class='color-picker' id='$id' name='$name' value='" . esc_attr($value) . "'>";
                break;

            case 'image':
                echo "<input type='text' id='$id' name='$name' value='" . esc_attr($value) . "' class='regular-text'>";
                echo " <button class='button select-image' data-target='$id'>Seleziona immagine</button>";
                break;

            case 'select':
                echo "<select id='$id' name='$name'>";
                foreach ($args['options'] ?? [] as $key => $label) {
                    $selected = selected($value, $key, false);
                    echo "<option value='" . esc_attr($key) . "' $selected>$label</option>";
                }
                echo "</select>";
                break;
        }
    }

    public function enqueue_scripts() {
        add_action('admin_enqueue_scripts', function($hook) {
            if ($hook !== $this->settings_page) return;
            wp_enqueue_media();
            wp_enqueue_script('plugin-settings-wrapper', plugin_dir_url(__FILE__) . 'settings-wrapper.js', ['jquery'], null, true);
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        });
    }
}


*/