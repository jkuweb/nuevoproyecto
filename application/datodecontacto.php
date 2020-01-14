<?php

class DatoDeContacto {
    
    function __construct() {
        $this->datodecontacto_id = 0;
        $this->denominacion = "";
        $this->valor = "";
        $this->cliente = 0; // propiedad de dependencia 
    }

    static function get_datodecontacto($cliente_id) {
        $sql = "SELECT datodecontacto_id FROM datodecontacto WHERE cliente = ?";
        $datos = [$cliente_id];
        return consultar($sql, $datos); 
    }

    function insert() {
        $sql = "INSERT INTO datodecontacto (denominacion, valor, cliente)
            VALUES (?, ?, ?)";
        $datos = [
            $this->denominacion,
            $this->valor,
            $this->cliente
        ];
        // Dotar al objeto de una indentidad propia.
        $this->datodecontacto_id = @consultar($sql, $datos);
     }

    function select() {
        $sql = "SELECT denominacion, valor, cliente
            FROM datodecontacto
            WHERE datodecontacto_id = ?";
        $datos = [
            $this->datodecontacto_id
        ];
        $resultados = @consultar($sql, $datos)[0];
        $this->denominacion = $resultados['denominacion'];
        $this->valor = $resultados['valor'];
        $this->cliente = $resultados['cliente'];
    }

    function update() {
        $sql = "UPDATE datodecontacto
            SET denominacion = ?, valor = ?, cliente = ? 
            WHERE datodecontacto_id = ?";
        $datos = [
            $this->denominacion,
            $this->valor,
            $this->cliente,
            $this->datodecontacto_id
        ];
        consultar($sql, $datos);
    }

    function delete() {
        $sql = "DELETE FROM datodecontacto WHERE datodecontacto_id = ?";
        $datos = [$this->datodecontacto_id];
        consultar($sql, $datos);
    }

}


class DatoDeContactoController {
    
    function __construct(){
        $this->model = new DatoDeContacto();
    }

    function guardar($cliente) {
        extract($_POST);
        $contacto = new DatoDeContacto();
        $contacto->denominacion = 'E-mail';
        $contacto->valor = $email;
        $contacto->cliente = $cliente->cliente_id; 
        $contacto->insert();
        $cliente->datodecontacto_collection[] = $contacto;
    
        $contacto = new DatoDeContacto();
        $contacto->denominacion = 'teléfono móvil';
        $contacto->valor = $movil;
        $contacto->cliente = $cliente->cliente_id; 
        $contacto->insert();
        $cliente->datodecontacto_collection[] = $contacto;
    }

    function actualizar($cliente) {
        extract($_POST);
        foreach($contacto_id as $i => $id) {
            $contacto = new DatoDeContacto();
            $contacto->datodecontacto_id = $id;
            $contacto->select();
            $contacto->valor = $valor[$i];
            $contacto->update();
        }
    }

}
?>
