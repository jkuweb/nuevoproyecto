<?php
class Collector {
    function __construct() {
        $this->coleccion = [];
    }

    function get($clase) {
        $tabla = strtolower($clase);
        $sql = "SELECT {$tabla}_id FROM {$tabla}";
        $datos = [];
        $array = consultar($sql, $datos);
        if(!empty($array)) {
            foreach($array as $arrayAsoc){
                $obj = new $clase();
                $propiedad = "{$tabla}_id";
                $obj->$propiedad = $arrayAsoc["{$tabla}_id"];
                $obj->select();
                $this->coleccion[] = $obj;
            } 
        }
    }    
}
