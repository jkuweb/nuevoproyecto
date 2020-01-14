<?php

class Template {

    static function extract($archivo, $tag) {        
        $form = file_get_contents($archivo);        
        $regex = "/<!--$tag-->(.)*<!--$tag-->/s";
        preg_match($regex, $form, $coincidencias); 
        return  $coincidencias[0];        
    }

    static function extraer($archivo, $tag) {
        $fichero = file_get_contents($archivo);
        $regex = "/<!--$tag-->/s";
        return preg_split($regex, $fichero)[1];
    }

    // prueba realizada para el refactoring , en este caso pasamos un
    // string como parámetro 
    static function ex($html, $tag) {        
        $regex = "/<!--$tag-->(.)*<!--$tag-->/s";
        preg_match($regex, $html, $coincidencias); 
        return  $coincidencias[0];        
    }

    // prueba realizada para el refactoring , en este caso pasamos un
    // string como parámetro 
    static function ext($html, $tag) {
        $regex = "/<!--$tag-->/s";
        return preg_split($regex, $html)[1];
    }

    static function get_rendered_value($value, $object, $property) {
        return ($value) ? $value : $object->$property;
    }
   
    static function get_check_symbol($field_value, $error_message) {
        $has_field_value = ($field_value);
        $has_not_error = (!$error_message);
        $is_correct_value = ($has_field_value && $has_not_error);
        return ($is_correct_value) ? "&#10004;" : "";
    }


    static function render_dict($path, $dict) {
        $base = file_get_contents($path);
        $base = str_replace(array_keys($dict), array_values($dict), $base);
        return $base;
    }
     
}
?>
