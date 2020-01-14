<?php


class CommonView {
    
    function get_rendered_template() {
        $template = file_get_contents('static/template.html');
        $username = $this->get_username();
        $template = str_replace('{denominacion_usuario}', $username, $template);
        return $template;
    }

    function get_username() {
        $username = $_SESSION['denominacion'] ?? 'Invitado';
        return $username;
    }   

    function render($content, $template) {
        $template = str_replace('<!--HTML-->', $content, $template);
        return $template;
    }

}


?>
