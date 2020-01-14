<?php

class Domicilio {
    
    function __construct() {
        $this->domicilio_id = 0;
        $this->calle = "";
        $this->numero = 0;  
        $this->planta = 0; 
        $this->puerta = "";
        $this->ciudad = "";  
    }  
    
    function insert() {
        $sql = "INSERT INTO domicilio (domicilio_id, calle, numero, planta, puerta, ciudad) VALUES (?, ?, ?, ?, ?, ?)";
        $datos = array_values(get_object_vars($this));        
        $this->domicilio_id = @consultar($sql, $datos);
    }  

    function select() {
        $sql = "SELECT calle, numero, planta, puerta, ciudad FROM domicilio WHERE domicilio_id = ?";
        $dato = [$this->domicilio_id];
        $resultado = @consultar($sql, $dato)[0];

        $this->calle  = $resultado['calle'];
        $this->numero = $resultado['numero'];
        $this->planta = $resultado['planta'];
        $this->puerta = $resultado['puerta'];
        $this->ciudad = $resultado['ciudad'];
    }

    function update() {
        $sql = "UPDATE domicilio SET calle = ?, numero = ?, planta = ?, puerta = ?, ciudad = ? WHERE domicilio_id = ?";
        $datos = [
            $this->calle,
            $this->numero,
            $this->planta,
            $this->puerta,
            $this->ciudad,
            $this->domicilio_id
        ];
        consultar($sql, $datos);
    }

    function delete() {
        $sql = "DELETE FROM domicilio WHERE domicilio_id = ?";
        $dato = [$this->domicilio_id];
        consultar($sql, $dato);
    }

}


class DomicilioView {
   /* 
    function agregar() {
        $template = file_get_contents('static/template.html');         
        $domicilio_html = PATH_MODULO_DOMICILIO . "/domicilio_agregar.html";

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        // domicilio
        $html = file_get_contents($domicilio_html);  
        $final = str_replace('<!--HTML-->', $html, $template);
        print $final;  
    }
    
    function ver($domicilio) {
        $template = file_get_contents('static/template.html'); 
        $domicilio_html = PATH_MODULO_DOMICILIO . "/domicilio_ver.html";

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        // domicilio
        $html = file_get_contents($domicilio_html);
        $diccionario = [
            "{calle}" => $domicilio->calle,
            "{numero}" => $domicilio->numero,
            "{planta}" => $domicilio->planta,
            "{puerta}" => $domicilio->puerta,
            "{ciudad}" => $domicilio->ciudad,
            "{domicilio_id}" => $domicilio->domicilio_id
        ];  
        $html = str_replace(
            array_keys($diccionario), 
            array_values($diccionario), 
            $html
        );
        $final = str_replace('<!--HTML-->', $html, $template);
        print $final;  
    }

    function editar($domicilio) {
        $template = file_get_contents('static/template.html'); 
        $domicilio_html = PATH_MODULO_DOMICILIO . "/domicilio_editar.html";
        
        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        // domicilio
        $html = file_get_contents($domicilio_html);
        $diccionario = [
            "{calle}" => $domicilio->calle,
            "{numero}" => $domicilio->numero,
            "{planta}" => $domicilio->planta,
            "{puerta}" => $domicilio->puerta,
            "{ciudad}" => $domicilio->ciudad,
            "{domicilio_id}" => $domicilio->domicilio_id
        ];  
        $html = str_replace(
            array_keys($diccionario), 
            array_values($diccionario), 
            $html
        );
        $final = str_replace('<!--HTML-->', $html, $template);
        print $final;  
    }

    function listar($domicilios) {
        $template = file_get_contents('static/template.html');
        $domicilio_html = PATH_MODULO_DOMICILIO . "/domicilio_listar.html";

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        // domicilio
        $tabla = file_get_contents($domicilio_html); 
        $fila = Template::extract($domicilio_html, 'fila');
        $render = "";
        foreach($domicilios as $domicilio) {
            $diccionario = [
                "{calle}" => $domicilio->calle,
                "{numero}" => $domicilio->numero,
                "{planta}" => $domicilio->planta,
                "{puerta}" => $domicilio->puerta,
                "{ciudad}" => $domicilio->ciudad,
                "{domicilio_id}" => $domicilio->domicilio_id
            ]; 
            $render .= str_replace(
                array_keys($diccionario), 
                array_values($diccionario), 
                $fila
            );
        }
        $render_tabla = str_replace($fila, $render, $tabla);
        $final = str_replace('<!--HTML-->', $render_tabla, $template);
        print $final;         
    }
    */ 
}


class DomicilioController {

    function __construct() {
        $this->model = new Domicilio;
        $this->view  = new DomicilioView;
    }
    
    function agregar() {
        $this->view->agregar();
    }
    
    function guardar() {
        extract($_POST);
             
        // Saneamiento
        settype($planta, 'int');
        settype($numero, 'int');

        // Validación        
        $errores = [];
        if(!($planta >= 1 || $numero  >=1)) {
            $errores[] = "Error";
        }               
        
        if($calle == "" || $puerta == "" || $ciudad == "") {
            $errores[] = "El campo está vacío";
        }

        if($errores) exit(header("Location: /domicilio/agregar")); 
        
        $this->model->calle = $calle;
        $this->model->numero = $numero;
        $this->model->planta = $planta;
        $this->model->puerta = $puerta;
        $this->model->ciudad = $ciudad;
               
        $this->model->insert();  
        header("Location: /domicilio/ver/{$this->model->domicilio_id}"); 
    }

    function ver($id=0) {
        $this->model->domicilio_id = (int) $id;
        $this->model->select();

        $this->view->ver($this->model);
    }

    function editar($id=0) {        
        $this->model->domicilio_id = (int) $id;
        $this->model->select();
    
        $this->view->editar($this->model);        
    }

    function actualizar() {
        extract($_POST);
        
        $this->model->calle = $calle;
        $this->model->numero = $numero;
        $this->model->planta = $planta;
        $this->model->puerta = $puerta;
        $this->model->ciudad = $ciudad;
        $this->model->domicilio_id = $domicilio_id;
        $this->model->update();  
        
        header("Location: /domicilio/ver/{$this->model->domicilio_id}");  
    }

    function eliminar($id=0) {
        $this->model->domicilio_id = (int) $id;
        $this->model->delete();
        
        header("Location: /domicilio/listar");
    }

    function listar() {   
        $coleccion = new Collector();
        $coleccion->get("Domicilio"); 
        $domicilios = $coleccion->coleccion;   
        
        $this->view->listar($domicilios);
    }

}

?>
