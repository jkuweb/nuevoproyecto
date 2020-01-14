<?php
require_once 'producto.php';
require_once 'cliente.php';
require_once 'common/view.php';


class Pedido {

    function __construct() {
        $this->pedido_id = 0;
        $this->estado = 0; 
        $this->fecha = "";
        $this->cliente = 0;
        $this->producto_collection = []; 
    }

    function insert() {
        $sql = "INSERT INTO pedido (estado, fecha, cliente) VALUES (?, ?, ?)";
        $datos = [
            $this->estado,
            $this->fecha,
            $this->cliente
        ];
        $this->pedido_id = consultar($sql, $datos);
    }

    function select() {
        $sql = "SELECT estado, fecha, cliente FROM pedido WHERE pedido_id = ?";
        $datos = [$this->pedido_id];
        $resultados = @consultar($sql, $datos)[0];

        $this->estado = $resultados['estado'];
        $this->fecha = $resultados['fecha'];
        $this->cliente = $resultados['cliente']; 

        $pp = new ProductoPedido($this);
        $pp->get();
    }

    function update() {
        $sql = "UPDATE pedido SET estado = ?, fecha = ? WHERE pedido_id = ?";
        $datos = [
            $this->estado,
            $this->fecha,
            $this->pedido_id
        ];
        consultar($sql, $datos);
    }

    function delete() {
        $sql = "DELETE FROM pedido WHERE pedido_id = ?";
        $datos = [$this->pedido_id];
        consultar($sql, $datos);    
    }

}


class PedidoView extends CommonView {
    
    function agregar($clientes, $productos, $errores) {
        $template = $this->get_rendered_template();
        $file_pedido = PATH_MODULO_PEDIDO . "/pedido_agregar.html";
        $contenido = $this->render_clientes($clientes, $file_pedido);
        $contenido = $this->render_productos($productos, $contenido);
        $contenido = $this->render_errores($errores, $contenido);
        print $this->render($contenido, $template); 
    }
     
    function render_clientes($clientes, $file_pedido) {
        $base = file_get_contents($file_pedido);
        $html = Template::extraer($file_pedido, 'clientes');
        $contenido = "";
        foreach($clientes as $cliente) {
		$dict = [
                "{denominacion_usuario}" => $_SESSION['denominacion'] ?? "Invitado",
                "{cliente_id}" => $cliente->cliente_id,
                "{denominacion}" => $cliente->denominacion
            ];
            $contenido .= str_replace(array_keys($dict), array_values($dict), $html);
        }
        $contenido = str_replace($html, $contenido, $base);
        return $contenido;
    }

    function render_productos($productos, $contenido) {
        $html = Template::ex($contenido, 'productos');
        $pila = "";
        foreach($productos as $producto) {
            $dict = [
                "{producto_id}" => $producto->producto_id,
                "{denominacion}" => $producto->denominacion
            ];
            $pila .= str_replace(array_keys($dict), array_values($dict), $html);
        }
	
        $pila = str_replace($html, $pila, $contenido);
        return $pila;
    }

    function render_errores($errores, $contenido) {
        $mensaje = "";
        $li = "\u{2022}";
        if($errores) {
            $mensaje = "Ups! Algo salio mal:{$li}\n";
            $mensaje .= implode("\n{$li} ", $errores);
            $mensaje = nl2br($mensaje);
        }
        $contenido = str_replace('{errores}', $mensaje, $contenido);
        return $contenido;
    } 


    function ver($pedido) {
        $template = file_get_contents('static/template.html');
        $pedido_html = PATH_MODULO_PEDIDO . "/pedido_ver.html";

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        //pedido
        $tabla = file_get_contents($pedido_html);
        $dic = [
            "{pedido_id}" => $pedido->pedido_id,
            "{fecha}" => $pedido->fecha,
            "{estado}" => ESTADO_PEDIDO[$pedido->estado],
            "{cliente_id}" => $pedido->cliente
        ];
        $render_pedido = str_replace(
            array_keys($dic), 
            array_values($dic), 
            $tabla
        );
	
        $lista_productos = Template::extract($pedido_html, 'producto');
        $render_producto = "";
        $total = 0;
        foreach($pedido->producto_collection as $producto) {
            $dic = [
                "{producto_id}" => $producto->producto_id,
                "{denominacion}" => $producto->denominacion,
                "{cantidad}" => $producto->fm,
                "{precio}" => $producto->precio,
                "{total_producto}" => $producto->precio * $producto->fm,
            ];
            $total += $producto->precio * $producto->fm;
            $render_producto .= str_replace(
                array_keys($dic), 
                array_values($dic), 
                $lista_productos
            );
        }
        $render_producto = str_replace(
            $lista_productos, 
            $render_producto, 
            $render_pedido
        );

        $total_precio = Template::extract($pedido_html, 'precio_total');
        $final = str_replace("{total}", $total, $render_producto);
        $final = str_replace('<!--HTML-->', $final, $template);
        print $final;
    }

    function editar($pedido) {
        $template = file_get_contents('static/template.html');
        $pedido_html = PATH_MODULO_PEDIDO . "/pedido_editar.html";

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        // pedido
        $tabla = file_get_contents($pedido_html);
        $dic = [
            "{pedido_id}" => $pedido->pedido_id,
            "{fecha}" => $pedido->fecha,
            "{estado}" => ESTADO_PEDIDO[$pedido->estado],
            "{cliente_id}" => $pedido->cliente
        ];
        $render_pedido = str_replace(
            array_keys($dic), 
            array_values($dic), 
            $tabla
        );
        $estados_html = Template::extract($pedido_html, 'estados');
        $render_estados = "";
        foreach(ESTADO_PEDIDO as $codigo_estado => $descripcion_estado) {
            $dic = [
            "{codigo_estado}" => $codigo_estado,
            "{descripcion_estado}" => $descripcion_estado    
            ];
            $render_estados .= str_replace(
                array_keys($dic), 
                array_values($dic), 
                $estados_html
            );
        }
        $final = str_replace($estados_html, $render_estados, $render_pedido);
        $final = str_replace('<!--HTML-->', $final, $template);
        print $final;
    }

    function listar($pedidos) {
        $template = file_get_contents('static/template.html');
        $pedido_html = PATH_MODULO_PEDIDO . "/pedido_listar.html";

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 

        $tabla = file_get_contents($pedido_html);
        $fila = Template::extract($pedido_html, 'fila');
        $render = "";
        foreach($pedidos as $pedido) {
            $dic = [
                "{pedido_id}" => $pedido->pedido_id,
                "{fecha}" => $pedido->fecha,
                "{estado}" => ESTADO_PEDIDO[$pedido->estado],
                "{cliente_id}" => $pedido->cliente
            ];
            $render .= str_replace(
                array_keys($dic), 
                array_values($dic), 
                $fila
            );
        }
        $render_tabla = str_replace($fila, $render, $tabla);
        $final = str_replace('<!--HTML-->', $render_tabla, $template);
        print $final;
    }
}


class PedidoController {

    function __construct() {
        $this->model = new Pedido();
        $this->view = new PedidoView();
    }

    function __call($m, $a) { $this->listar(); }
    
    function agregar($errores=[]) {
        UsuarioHelper::check();
        $coleccion = new Collector();
        $coleccion->get("Cliente");
        $clientes = $coleccion->coleccion;
        $coleccion = new Collector();
        $coleccion->get("Producto");
        $productos = $coleccion->coleccion;
        $this->view->agregar($clientes, $productos, $errores);
    }

    function guardar() {
        UsuarioHelper::check();
        extract($_POST); 
        // SANEAR
        settype($cliente_id, 'int');
        foreach($cantidad as $i => $a_cantidad) {
            settype($a_cantidad, 'int');
            $cantidad[$i] = $a_cantidad;
        } 
        
        foreach($producto_id as $i => $a_producto_id) {
            settype($a_producto_id, 'int');
            $producto_id[$i] = $a_producto_id;
        } 
        // VALIDACION
        $errores = [];

        if(!$cliente_id) $errores[] = "El cliente es requerido";
        foreach($producto_id as $id){
            if(!$id) $errores[] = "El producto es requerido";
        }

        foreach($cantidad as $numero){
            if(!$numero) $errores[] = "La cantidad es requerida";
        }

        $check_cliente = ClienteDataHelper::get_denominacion($cliente_id);
        if(!$check_cliente) $errores[] = "El cliente no existe";

        foreach($producto_id as $id) {
            $check_producto = ProductoDataHelper::get_producto_id($id);
            if(!$check_producto) $errores[] = "El producto no existe";
        }

        if($errores) exit($this->agregar($errores));
        $this->model->fecha = date("y-m-d");
        $this->model->estado = ESTADO_PEDIDO[0];
        $this->model->cliente = $cliente_id;
        $this->model->insert();

        foreach($producto_id as $i => $pid) {
            $producto = new Producto();
            $producto->producto_id = $pid;
            $producto->select();
            $producto->fm = $cantidad[$i];                 
            $this->model->producto_collection[] = $producto; 
        }

        $productopedido = new ProductoPedido($this->model);
        $productopedido->save();
        header("Location: /pedido/ver/{$this->model->pedido_id}");    
    }

    function ver($id=0) {
        UsuarioHelper::check();
        $this->model->pedido_id = (int) $id;
        $this->model->select();
        $this->view->ver($this->model);
    }

    function editar($id=0) {
        UsuarioHelper::check();
        $this->model->pedido_id = (int) $id;
        $this->model->select();
        $this->view->editar($this->model);
    }

    function actualizar() {
        UsuarioHelper::check();
        extract($_POST);
        $this->model->pedido_id = $pedido_id;
        $this->model->fecha = date("y-m-d");
        $this->model->estado = $estado;
        $this->model->update();
        
        header("Location: /pedido/ver/{$this->model->pedido_id}"); 
    }

    function eliminar($id=0) {
        UsuarioHelper::check();
        $this->model->pedido_id = (int) $id;
        $this->model->delete();

        header("Location: /pedido/listar");
    }

    function listar() {
        UsuarioHelper::check();
        $coleccion = new Collector();
        $coleccion->get("Pedido");
        $pedidos = $coleccion->coleccion;
        $this->view->listar($pedidos);
    }
    
}

    
class ProductoPedido {
    
    function __construct(Pedido $pedido) {
        $this->productopedido_id = 0;
        $this->compuesto = $pedido;
        $this->compositor = $pedido->producto_collection; 
        $this->fm = 0;
    }

    function save() {
        $this->destroy();
        $sql = "INSERT INTO productopedido (compuesto, compositor, fm) VALUES ";
        $conjuntos = [];
        foreach($this->compositor as  $compositor) {
            $conjuntos[] = "(
                {$this->compuesto->pedido_id}, 
                {$compositor->producto_id}, 
                {$compositor->fm})";
        }
        $sql .= implode(", ", $conjuntos);
        consultar($sql, []); 
    }

    function get() {
        $sql = "SELECT compositor, fm FROM productopedido WHERE compuesto = ?";
        $datos = [$this->compuesto->pedido_id];
        $resultados = consultar($sql, $datos);
        $resultados = (!is_array($resultados)) ? [] : $resultados;
        foreach($resultados as $array_asoc) {
            $producto = new Producto();
            $producto->producto_id = $array_asoc['compositor'];
            $producto->select();
            $this->compuesto->producto_collection[] = $producto;
            $producto->fm = $array_asoc['fm'];
        }
    }

    function destroy() {
        $sql = "DELETE FROM productopedido WHERE compuesto = ?";
        $dato = [$this->compuesto->pedido_id];
        consultar($sql, $dato);
    }
}


class PedidoDataHelper {

    static function get_pedido($cliente_id) {
        $sql = "SELECT pedido_id FROM pedido WHERE cliente = ?";
        $datos = [$cliente_id];
        return consultar($sql, $datos);
    }
    
    static function get_pedidos_en_espera() {
        $sql = "SELECT pedido_id FROM pedido WHERE estado = ?";
        $datos = [ESTADO_PEDIDO[0]];
		$resultados = consultar($sql, $datos);
        $coleccion = [];
        if(!empty($resultados)) {
            foreach($resultados as $arrayasoc) {
                $pedido = new Pedido();
                $pedido->pedido_id = $arrayasoc['pedido_id'];
                $pedido->select();
                $coleccion[] = $pedido;
            }
        }
        return $coleccion;
    }

    static function get_ultimos_pedidos() {
	$sql = "SELECT pedido_id FROM pedido ORDER BY pedido_id DESC LIMIT 5";
	$datos = [];
	$resultados = consultar($sql, $datos);
        $coleccion = [];
        if(!empty($resultados)) {
            foreach($resultados as $arrayasoc) {
	        $pedido = new Pedido();
	        $pedido->pedido_id = $arrayasoc['pedido_id'];
	        $pedido->select();
	        $coleccion[] = $pedido;
            }
        }
        return $coleccion;
    }

    static function get_precio_total_pedido($pedido_id) {
        $pedido = new Pedido();
        $pedido->pedido_id = $pedido_id;
        $pedido->select();
        $precio_total = 0;
        foreach($pedido->producto_collection as $producto) {
           $precio_total +=  $producto->precio * $producto->fm;    
        }
        return $precio_total;
    }

    static function get_precio_total_pedidos() {
        $c_pedidos = new Collector();
        $c_pedidos->get("Pedido");
        $pedidos = $c_pedidos->coleccion;
        $precio_total = 0;
        foreach($pedidos as $pedido) {
		$precio_total += PedidoDataHelper::get_precio_total_pedido(
			$pedido->pedido_id);
        }
        return $precio_total;
    }
    
    static function get_valor_devoluciones() {
        $sql = "SELECT pedido_id FROM pedido WHERE estado = 4";
        $resultados = consultar($sql, $datos=[]);
        $precio_total = 0;
        if(!empty($resultados)) {
            foreach($resultados as $pedido_id) {
		$precio_total += PedidoDataHelper::get_precio_total_pedido(
	            $pedido_id['pedido_id']);
            }
        }
        return $precio_total; 
    }
}
?>
