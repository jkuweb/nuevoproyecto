<?php

class Helper {

    static function sanear(&$value, $name='') {
        $names = ['password', 'pass', 'passwd', 'clave', 'contrasenia', 'contraseña'];
        if(!in_array($name, $names)) {
            $value = trim($value);
            $value = htmlentities(strip_tags($value), ENT_QUOTES);
        }
    }

    static function get_check_symbol($field_value, $error_message) {
        $has_field_value = ($field_value);
        $has_not_error = (!$error_message);
        $is_correct_value = ($has_field_value && $has_not_error);
        return ($is_correct_value) ? "&#10004;" : "";
    }

    static function render($content) {
        $template = file_get_contents('static/template.html');
        $template = str_replace('<!--HTML-->', $content, $template);
        return $template;
    }

    static function render_dict($path, $dict) {
        $base = file_get_contents($path);
        $base = str_replace(array_keys($dict), array_values($dict), $base);
        return $base;
    }
}

?>
