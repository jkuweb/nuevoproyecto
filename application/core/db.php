<?php

 function consultar($sql, $datos=array()) {
     # Conectar
     $conexion = "mysql:host=". DB_HOST .";dbname=". DB_NAME .";charset=utf8";
     $opciones = array(PDO::ATTR_PERSISTENT=>true);
     $conn = new PDO($conexion, DB_USER, DB_PASS, $opciones);

     # Preparar consulta
     $query = $conn->prepare($sql);

     # Enlazar variables a la consulta preparada
     foreach($datos as $i=>$dato) $query->bindParam($i+1, $datos[$i]);

     # Ejecutar consulta
     $query->execute();

     # Obtener resultados
     $id_ingresado = $conn->lastInsertId();
     $registros_leidos = $query->fetchAll(PDO::FETCH_ASSOC);

     # Retornar resultados
     return ($registros_leidos) ? $registros_leidos : $id_ingresado;
 }

 ?>
