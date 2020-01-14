<?php
require_once 'categoria.php';
require_once 'usuario.php';
require_once 'common/view.php';


class Producto {

    function __construct() {
        $this->producto_id = 0;
        $this->denominacion = '';
        $this->precio = 0.0;
    }

    function insert() {
        $sql = "INSERT INTO producto (producto_id, denominacion, precio) VALUES (?, ?, ?)";
        $datos = array_values(get_object_vars($this));         
        $this->producto_id = @consultar($sql, $datos);
    }

    function select() {
        $sql = "SELECT denominacion, precio FROM producto WHERE producto_id = ?";
        $datos = [$this->producto_id];
        $resultados = @consultar($sql, $datos)[0];        
        $this->denominacion  = $resultados['denominacion'];
        $this->precio = $resultados['precio'];

    }

    function update() {
        $sql = "UPDATE producto SET denominacion = ?, precio = ? WHERE producto_id = ? ";
        $datos = [
            $this->denominacion,
            $this->precio,
            $this->producto_id
        ];
        consultar($sql, $datos);
    }

    function delete() {
        $sql = "DELETE FROM producto WHERE producto_id = ?";
        $datos = [$this->producto_id];
        consultar($sql, $datos);
    }
}


class ProductoView extends CommonView {

    function agregar($categoria, $errores=[]) {        
        extract($_POST);
        $template = $this->get_rendered_template(); 
        $archivo = PATH_MODULO_PRODUCTO . "/producto_agregar.html";
        $ok_denominacion = Template::get_check_symbol(
            @$denominacion, 
            @$errores['denominacion']
        );
        $ok_precio = Template::get_check_symbol(@$precio, @$errores['precio']);
        $dict = [
            "{denominacion}" => @$denominacion,
            "{precio}" => @$precio,
            "{ok_denominacion}" => $ok_denominacion,
            "{ok_precio}" => $ok_precio,
            "{error_denominacion}" => @$errores['denominacion'],
            "{error_precio}" => @$errores['precio'],
            "{error_imagen}" => @$errores['type'],
            "{fallo}" => @$errores['fallo'],

        ];  
        $contenido = Template::render_dict($archivo, $dict);
        $contenido = $this->render_categorias(
            $categoria, $archivo, $contenido, 'categorias');
        $contenido = $this->render_categorias(
            $categoria, $archivo, $contenido, 'sup_categorias');

        print $this->render($contenido, $template);
    }
     
    function ver($categoria) {
        $template = $this->get_rendered_template();
        $archivo = PATH_MODULO_PRODUCTO . "/producto_ver.html";
        $contenido = $this->render_producto($categoria, $archivo);
        $contenido = $this->render_categoria($categoria, $contenido);
        $contenido = $this->render_imagenes($categoria, $contenido, $archivo);
        print $this->render($contenido, $template);
    }

    function editar($categoria, $producto_categoria, $errores=[]) {
        extract($_POST);
        $producto = $producto_categoria->producto_collection[0];
        
        $template = $this->get_rendered_template();
        $archivo = PATH_MODULO_PRODUCTO . "/producto_editar.html";
        $dict = [
            "{denominacion}" => Template::get_rendered_value(@$denominacion, $producto, 'denominacion'),
            "{precio}" => Template::get_rendered_value(@$precio, $producto, 'precio'),
            "{producto_id}" => $producto->producto_id,
            "{categoria_actual}" => $producto_categoria->denominacion,

            "{ok_denominacion}" => Template::get_check_symbol(@$denominacion, @$errores['denominacion']), 
            "{ok_precio}" => Template::get_check_symbol(@$precio, @$errores['precio']), 

            "{error_denominacion}" => @$errores["denominacion"],
            "{error_precio}" => @$errores["precio"],
            "{error_imagen}" => @$errores['type']
        ];  
        $contenido = Template::render_dict($archivo, $dict);
        $contenido = $this->render_categorias($categoria, $archivo, $contenido, 'categorias');
        //$contenido = $this->render_imagenes($producto, $contenido, $archivo); 
        print $this->render($contenido, $template);
    }

    function listar($productos) {
        $template = $this->get_rendered_template();
        $archivo = PATH_MODULO_PRODUCTO . "/producto_listar.html";
        $contenido = $this->render_productos($productos, $archivo);
        print $this->render($contenido, $template);
    }

    function render_producto($categoria, $archivo) {
        $base = file_get_contents($archivo);
        $html= Template::extract($archivo, 'producto');
        
        foreach($categoria->producto_collection as $producto) {
            $dict = [
                "{denominacion}" =>  $producto->denominacion,
                "{precio}" =>  $producto->precio,
                "{producto_id}" => $producto->producto_id,
            ];  
        }
        $contenido = str_replace(array_keys($dict), array_values($dict), $html);
        $contenido = str_replace($html, $contenido, $base);
        return $contenido;
    }
     
    function render_productos($productos, $archivo) {
        $base = file_get_contents($archivo); 
        $html = Template::extract($archivo, 'productos');
        $pila = "";
        foreach($productos as $producto){
            $dict = [
                "{producto_id}" => $producto->producto_id,
                "{denominacion}" => $producto->denominacion,
                "{precio}" => $producto->precio
            ];  
            $pila .= str_replace(array_keys($dict), array_values($dict), $html);           
        }        
        $contenido = str_replace($html, $pila, $base);
        return $contenido;
    }

    function render_categoria($categoria, $contenido) {
        $contenido = str_replace("{categoria}", $categoria->denominacion, $contenido);
        return $contenido;
    }

    function render_categorias($categoria, $archivo, $contenido, $tag) {
        $html_categoria = Template::extract($archivo, $tag);    
        CategoriaHelper::$render_categorias = "";
        CategoriaHelper::tabular_categorias($categoria, $html_categoria);
        $contenido = str_replace(
            $html_categoria, 
            CategoriaHelper::$render_categorias, 
            $contenido
        );
        return $contenido;
    }

    function render_imagenes($categoria, $contenido, $archivo) {
        $html = Template::extract($archivo, 'imagenes');
        foreach($categoria->producto_collection as $producto) {
            $imagenes = scandir(PATH_IMAGES."/producto/{$producto->producto_id}/"); 
        }
        unset($imagenes[0], $imagenes[1]); 
        $pila = "";
        foreach($imagenes as $imagen) {
            $dic = [
                "{id}" => $producto->producto_id,
                "{nombre_imagen}" => $imagen
            ];
            $pila .= str_replace(array_keys($dic), array_values($dic), $html);
        }
        $contenido = str_replace($html, $pila, $contenido);
        return $contenido;
    }

}


class ProductoController {

    function __construct() {
        $this->model = new Producto();
        $this->view = new ProductoView();
    }

    function __call($m, $a) { $this->listar(); }

    function agregar($errores=[]) {
        UsuarioHelper::check();
        $categoria = new Categoria();
        $categoria->categoria_id = 1; 
        $categoria->categoria_collection = [];
        $categoria->select();
        $this->view->agregar($categoria, $errores);
    }

    function guardar() {
        UsuarioHelper::check();
        extract($_POST);     
        extract($_FILES);     
        
        $precio = filter_var($precio, FILTER_VALIDATE_FLOAT,  FILTER_FLAG_ALLOW_THOUSAND);    
        settype($categoria_id, 'int');
        settype($compuesta_categoria_id, 'int');

        $errores = [];

        if(!($precio > 1)) $errores['precio'] = sprintf(ERR_PRODUCT_PRICE_LT, $precio);
        if(!($precio <= 9999.99)) $errores['precio'] = sprintf(ERR_PRODUCT_PRICE_TOO_HIGHT, $precio);
        if($precio == "") $errores['precio'] = ERR_PRODUCT_PRICE_IS_EMPTY; 
        //if($compositora_denominacion == "") $errores['fallo'] = ERR_NOT_OBJECT_EXIST;
        if(!$categoria_id && !$compositora_denominacion) $errores['fallo'] = ERR_NOT_OBJECT_EXIST;
        if(!$categoria_id && !$compuesta_categoria_id) $errores['fallo'] = ERR_NOT_OBJECT_EXIST;
        // TODO validar que denominacion no sea núlo
        if(!$denominacion) $errores['denominacion'] = ERR_PRODUCT_DENOMINACION_IS_EMPTY;
        // Validar el tipo MIME de las imágenes
        if($imagen['type']) {
            foreach($imagen['type'] as $type) {
                if(!in_array($type, MIMES_PERMITIDOS)) {
                    $errores['type'] = ERR_IMAGE_TYPE;
                }      
            }
        }

        if($errores) exit($this->agregar($errores));

        # Modificar las propiedades del modelo
        $this->model->denominacion = $denominacion;
        $this->model->precio = $precio;
        # Guardar el nuevo objeto
        $this->model->insert();

        // GUARDAR UNA CATEGORÍA YA EXISTENTE  
        if($categoria_id) {
            $categoria = new Categoria();
            $categoria->categoria_id = (int) $categoria_id;
            $categoria->select();
            $categoria->producto_collection[] = $this->model;

            $productocategoria = new ProductoCategoria($categoria);
            $productocategoria->save();
        }
        // GUARDAR UNA NUEVA CATEGORÍA  	
        if($compuesta_categoria_id) {
            $compositora_categoria = new Categoria();
            $compositora_categoria->denominacion =  $compositora_denominacion;
            $compositora_categoria->producto_collection[] = $this->model;
            $compositora_categoria->insert();
            $productocategoria = new ProductoCategoria($compositora_categoria);
            $productocategoria->save();

            $categoria_compuesta = new Categoria();
            $categoria_compuesta->categoria_id = (int) $compuesta_categoria_id;
            $categoria_compuesta->select();
            $categoria_compuesta->categoria_collection[] = $compositora_categoria;
            $cl = new CategoriaCategoria($categoria_compuesta);
            $cl->save();
        }
        if(!($imagen['size'] === 0)){
            $destino = PATH_IMAGES."/producto/{$this->model->producto_id}";
            $permisos = mkdir($destino);

            foreach($_FILES['imagen']['name'] as $i => $valor) {
                $destino = PATH_IMAGES."/producto/{$this->model->producto_id}/{$valor}";
                $tmp_name = $_FILES['imagen']['tmp_name'][$i];
                move_uploaded_file($tmp_name, $destino);
            }        
        }    
        # Redirigir al usuario al recurso ver
        header("Location: /producto/ver/{$this->model->producto_id}");        
    }

    function ver($id = 0) {
        $categoria = new Categoria();
        $cl = new ProductoCategoria($categoria);
        $cl->get_relacion($id);
        $this->view->ver($categoria);
    }

    function editar($id=0, $errores=[]) {
        UsuarioHelper::check();
        $categorias = new Categoria();
        $categorias->categoria_id = 1;  
        $categorias->categoria_collection = [];
        $categorias->select();

        $categoria = new Categoria();
        $cl = new ProductoCategoria($categoria);
        $cl->get_relacion($id);

        $this->view->editar($categorias, $categoria);
    }

    function actualizar() {
        UsuarioHelper::check();
        extract($_POST);   
        extract($_FILES);   
        $precio = filter_var($precio, FILTER_VALIDATE_FLOAT,  FILTER_FLAG_ALLOW_THOUSAND);                                                                   

        //  validar que precio > 1
        $errores = [];
        if(!($precio > 1)) {
            $errores['precio'] = sprintf(ERR_PRODUCT_PRICE_LT, $precio);
        }   

        if(!($precio <= 9999.99)) {
            $errores['precio'] = sprintf(ERR_PRODUCT_PRICE_TOO_HIGHT, $precio);
        }    

        if($precio == "") {
            $errores['precio'] = ERR_PRODUCT_PRICE_IS_EMPTY;
        } 
        // validar que denominacion no sea núlo
        if($denominacion == "" ) {
            $errores['denominacion'] = ERR_PRODUCT_DENOMINACION_IS_EMPTY;
        }
        // Validar el tipo MIME de las imágenes
        if(!empty($imagen['type'])) {
            foreach($imagen['type'] as $type) {
                if(!in_array($type, MIMES_PERMITIDOS)) {
                    $errores['type'] = ERR_IMAGE_TYPE;
                }      
            }
        }

        if($errores) exit($this->editar($producto_id, $errores));

        $this->model->denominacion = $denominacion;
        $this->model->precio = $precio;
        $this->model->producto_id = $producto_id;
        $this->model->update();
        if($categoria_id) {
            $categoria = new Categoria();
            $categoria->categoria_id = $categoria_id; 
            $categoria->select();
            $categoria->producto_collection[] = $this->model;

            $producto_categoria = new ProductoCategoria($categoria);
            $producto_categoria->actualizar();
        }

        foreach($imagen['name'] as  $i => $imagen) {
            $origen = $_FILES['imagen']['tmp_name'][$i];
            $destino = PATH_IMAGES. "/producto/{$this->model->producto_id}/{$imagen}";
            move_uploaded_file($origen, $destino);
        } 

        header("Location: /producto/ver/{$this->model->producto_id}");
    }

    function eliminar($id = 0) {
        UsuarioHelper::check();
        $this->model->producto_id = (int) $id;
        $this->model->delete();
        header('Location: /producto/listar');
    }

    function listar() {   
        UsuarioHelper::check();
        $coleccion = new Collector();
        $coleccion->get("Producto");           
        $productos = $coleccion->coleccion;
        $this->view->listar($productos);
    }

    function imagen($id=0) {
        UsuarioHelper::check();
        $uri = rawurldecode($_SERVER['REQUEST_URI']);
        list(
            $null, 
            $modulo, 
            $recurso, 
            $producto_id, 
            $nombre_imagen
        ) = explode('/', $uri);
        $archivo = PATH_IMAGES."/producto/{$id}/$nombre_imagen" ;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $archivo);
        finfo_close($finfo);
        header("Content-type: $mime");
        readfile($archivo);
    }

    function eliminarImagen($id=0) {
        UsuarioHelper::check();
        $uri = rawurldecode($_SERVER['REQUEST_URI']);
        list(
            $null, 
            $modulo, 
            $recurso, 
            $producto_id, 
            $nombre_imagen
        ) = explode('/', $uri); 
        $fichero = PATH_IMAGES."/producto/{$id}/$nombre_imagen" ;
        unlink($fichero);
        header("Location: /producto/editar/{$id}");
    }

}


class ProductoDataHelper {

    static function get_ultimos_productos_agregados() {
        $sql = "SELECT producto_id, denominacion, precio FROM producto ORDER BY producto_id DESC LIMIT 5";
        $resultados = consultar($sql, $datos=[]);
        $coleccion = [];
        if(!empty($resultados)) {
            foreach($resultados as $arrayAsoc) {
                $producto = new Producto();
                $producto->producto_id = $arrayAsoc['producto_id'];
                $producto->select();
                $coleccion[] = $producto;
            }
        }
        return $coleccion;
    }

    static function get_producto_id($producto_id) {
        $sql = "SELECT producto_id FROM producto WHERE producto_id = ?";
        $dato = [$producto_id];
        return consultar($sql, $dato)[0];
    }

}

?>
