<?php
require_once 'producto.php';


class Categoria {

    function __construct() {
        $this->categoria_id = 0;
        $this->denominacion = "";
        //$this->categoria_collection = []; // propiedad colectora independiente
        //$this->producto_collection = []; // propiedad colectora independiente
    }

    function insert() {
        $sql = "INSERT INTO categoria (denominacion) VALUES (?)";
        $dato = [$this->denominacion];
        $this->categoria_id = consultar($sql, $dato);
    }

    function select() {
        $sql = "SELECT denominacion FROM categoria WHERE categoria_id = ?";
        $dato = [$this->categoria_id];
        $resultado = consultar($sql, $dato)[0];
        $this->denominacion = $resultado['denominacion'];

        if(isset($this->categoria_collection)) {
            $cl = new CategoriaCategoria($this);
            $cl->get();
        }
       
        if(isset($this->producto_collection)) {
            $cl = new ProductoCategoria($this);
            $cl->get();
        }
        
    }

    function update() {
        $sql = "UPDATE categoria SET denominacion = ? WHERE categoria_id = ?";
        $datos = [
            $this->denominacion,
            $this->categoria_id
        ];
        consultar($sql, $datos);
    }

    function delete() {
        $sql = "DELETE FROM categoria WHERE categoria_id = ?";
        $dato = [$this->categoria_id];
        consultar($sql, $dato);
    }

}


class CategoriaView {

    function agregar($categoria,$error){
        extract($_POST);
        $template = file_get_contents('static/template.html');
        $categoria_html = PATH_MODULO_CATEGORIA. "/categoria_agregar.html";

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        // categoria
        $html = file_get_contents($categoria_html);
        $render_errores = str_replace(
            "{error_denominacion}",
            @$error['denominacion'], 
            $html
        );
        // sup_categorias
        $categoria_html = Template::extract($categoria_html, 'sup_categorias');
        CategoriaHelper::tabular_categorias($categoria, $categoria_html);
        $render_categorias = CategoriaHelper::$render_categorias;
        $final = str_replace($categoria_html, $render_categorias, $render_errores);
        $final = str_replace('<!--HTML-->', $final, $template);
        print $final;
    } 

    function ver($categoria) {
        $template = file_get_contents('static/template.html');
        $categoria_html = PATH_MODULO_CATEGORIA. "/categoria_ver.html"; 

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        // categoria
        $html = file_get_contents($categoria_html);
        $dic = [
            "{categoria_id}" => $categoria->categoria_id,
            "{denominacion}" => $categoria->denominacion,
            "{categoria_denominacion}" => $categoria->denominacion
        ];
        $render_categoria = str_replace(
            array_keys($dic), 
            array_values($dic), 
            $html
        ); 
        // sup_categorias
        $sup_categorias_html = Template::extract($categoria_html, 'sup_categorias');
        CategoriaHelper::tabular_categorias($categoria, $sup_categorias_html);
        $render = CategoriaHelper::$render_categorias;
        $dic = [
            "{categoria_raiz}" 
        ];
        $render_sup_categorias = str_replace(
            $sup_categorias_html, 
            $render, 
            $render_categoria
        );
        // producto
        $producto_html = Template::extract($categoria_html, 'productos');
        $render_productos = "";
        foreach($categoria->producto_collection as $producto) {
            $dic = [
                "{producto_id}" => $producto->producto_id,
                "{producto_denominacion}" => $producto->denominacion,
                "{producto_precio}" => $producto->precio,
            ];
            $render_productos .= str_replace(
                array_keys($dic), 
                array_values($dic), 
                $producto_html
            );
        }
        $final = str_replace(
            $producto_html, 
            $render_productos, 
            $render_sup_categorias
        );
        $final = str_replace('<!--HTML-->' , $final, $template);
        print $final;
    }

    function editar($categoria, $sup_categoria, $errores) {
        extract($_POST);
        $template = file_get_contents('static/template.html');
        $categoria_form = PATH_MODULO_CATEGORIA . "/categoria_editar.html";

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        // categoria 
        $html = file_get_contents($categoria_form);
        $categoria_html = Template::extract($categoria_form, 'categoria');
        $dic = [
            "{denominacion}" => (@$denominacion) ?? $categoria->denominacion,
            "{categoria_id}" => $categoria->categoria_id,
            "{error_denominacion}" => @$errores['denominacion'],
        ];
        $render_categoria = str_replace(
            array_keys($dic), 
            array_values($dic), 
            $categoria_html
        );
        $render_categoria = str_replace(
            $categoria_html, 
            $render_categoria, 
            $html
        );
        // sup_categoria
        $categoria_html = Template::extract($categoria_form, 'sup_categorias');
        CategoriaHelper::tabular_categorias($sup_categoria, $categoria_html);
        $render =  CategoriaHelper::$render_categorias;

        $final = str_replace($categoria_html, $render, $render_categoria);
        $final = str_replace('<!--HTML-->', $final, $template);
        print $final;
    }

    function listar($categorias) {
        $template = file_get_contents('static/template.html');
        $categoria_list = PATH_MODULO_CATEGORIA . "/categoria_listar.html";

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        // categoria
        $categoria_html = file_get_contents($categoria_list);
        $fila = Template::extract($categoria_list, 'fila');
        $render = "";
        foreach($categorias as $categoria) {
            $dic = [
                "{categoria_id}" => $categoria->categoria_id,
                "{denominacion}" => $categoria->denominacion
            ];
            $render .= str_replace(array_keys($dic), array_values($dic), $fila);
        }
        $final = str_replace($fila, $render, $categoria_html);
        $final = str_replace('<!--HTML-->', $final, $template);
        print $final;
    }
}

class CategoriaController {

    function __construct() {
        $this->model = new Categoria();
        $this->view = new CategoriaView();
    }

    function __call($m, $a) { $this->listar(); }

    function agregar($error=[]) {
        UsuarioHelper::check();
        $this->model->categoria_id = 1; // Es el ID de la raiz 
        $this->model->categoria_collection = [];
        $this->model->select();

        $this->view->agregar($this->model, $error);
    }

    function guardar() {
        UsuarioHelper::check();
        extract($_POST);
        
        $error = [];
        if(!$denominacion) $error['denominacion'] = ERR_CATEGORY_INPUT_IS_EMPY;

        if($error) exit($this->agregar($error));

        $this->model->denominacion = ucwords($denominacion);
        $this->model->insert();
        $categoria = new Categoria();
        $categoria->categoria_id = $categoria_id;
        $categoria->select();
        $categoria->categoria_collection[] = $this->model;
        $cl = new CategoriaCategoria($categoria);
        $cl->save();

        header("Location: /categoria/ver/{$this->model->categoria_id}");
    }

    function ver($id=0) {
        UsuarioHelper::check();
        $this->model->categoria_id = (int) $id;
        $this->model->categoria_collection = []; 
        $this->model->producto_collection = []; 
        $this->model->select();
        $this->view->ver($this->model);
    }

    function editar($id=0, $errores=[]) {
        UsuarioHelper::check();
        $this->model->categoria_id = (int) $id;
        $this->model->categoria_collection = []; 
        $this->model->select();
        
        $sup_categoria = new Categoria();
        $sup_categoria->categoria_id = 1;
        $sup_categoria->categoria_collection = [];
        $sup_categoria->select();

        $this->view->editar($this->model, $sup_categoria, $errores);
    }

    function actualizar() {
        UsuarioHelper::check();
        extract($_POST);
        $errores = [];
        if(!$denominacion) $errores['denominacion'] = ERR_CATEGORY_INPUT_IS_EMPY;

        if($errores) exit($this->editar($categoria_id,$errores));


        $this->model->categoria_id = $categoria_id;
        $this->model->denominacion = $denominacion;
        $this->model->update();

        $categoria = new Categoria();
        $categoria->categoria_id = $sup_categoria_id;
        $categoria->select();
        $categoria->categoria_collection[] = $this->model;
        $categoriacategoria = new categoriacategoria($categoria);
        $categoriacategoria->actualizar();

        header("Location: /categoria/ver/{$this->model->categoria_id}");
    }

    function eliminar($id=0) {
        UsuarioHelper::check();
        $this->model->categoria_id = (int) $id;
        $this->model->delete();

        header("Location: /categoria/listar");
    }

    function listar() {
        UsuarioHelper::check();
        $colector = new Collector();
        $colector->get("Categoria");
        $categorias = $colector->coleccion;
        $this->view->listar($categorias);
    }

}


class CategoriaCategoria {

    function __construct($categoria) {
        $this->compuesto = $categoria;
        $this->compositor = $categoria->categoria_collection;
    }

    function save() {
        $sql = "INSERT INTO categoriacategoria (compuesto, compositor) VALUES (?, ?)";
        foreach($this->compositor as $categoria) {
            $datos = [
                $this->compuesto->categoria_id,
                $categoria->categoria_id
            ];

        }
        consultar($sql, $datos);
    }

    function get() {
        $sql = "SELECT compositor FROM categoriacategoria WHERE compuesto = ?";
        $dato = [$this->compuesto->categoria_id];
        $resultados = consultar($sql , $dato);
		if(!empty($resultados)) {
		   foreach($resultados as $arrayAsoc) {
				$categoria = new Categoria();
				$categoria->categoria_id = $arrayAsoc['compositor'];
				$categoria->categoria_collection = [];
				$categoria->select();
				$this->compuesto->categoria_collection[] = $categoria; 
			}
		}
    }

    function actualizar() {
        $sql = "UPDATE categoriacategoria SET compuesto = ? WHERE compositor = ?";
        foreach($this->compositor as $categoria) {
            $datos = [
                $this->compuesto->categoria_id,
                $categoria->categoria_id
            ];
        }
        consultar($sql, $datos);  
    }

}


class ProductoCategoria {

    function __construct(Categoria $categoria) {
        $this->productocategoria_id = 0;
        $this->compuesto = $categoria;
        $this->compositor = @$categoria->producto_collection;
        $this->fm = 1;
    }

    function save() {
        $this->destroy();
        $sql = "INSERT INTO productocategoria (compuesto, compositor, fm) VALUES ";
        $conjuntos = [];
        foreach($this->compositor as $compositor) {
            $conjuntos[] = "(
                {$this->compuesto->categoria_id},
                {$compositor->producto_id},
                {$this->fm})";    
        };
        $sql .= implode(",", $conjuntos);
        consultar($sql, []);
    }

    function get() {
        $sql = "SELECT compositor, fm FROM productocategoria WHERE compuesto = ?";
        $dato = [$this->compuesto->categoria_id];
        $resultados = consultar($sql, $dato);
        $resultados = (!is_array($resultados)) ? [] : $resultados;
        foreach($resultados as $array_asoc) {
            $producto = new Producto();
            $producto->producto_id = $array_asoc['compositor'];
            $producto->select();
            $this->compuesto->producto_collection[] = $producto; 
            $producto->fm = $array_asoc['fm']; 
        }
    }  

    function get_relacion($producto_id) {
        $sql = "SELECT compuesto, fm FROM productocategoria WHERE compositor = ?";
        $dato = [$producto_id];
        $resultado = consultar($sql, $dato)[0];

        $this->compuesto->categoria_id = $resultado["compuesto"];
        $this->compuesto->select();
        $this->compuesto->producto_collection = [];

        $producto = new Producto();
        $producto->producto_id = $producto_id;
        $producto->select();
        $this->compuesto->producto_collection[] = $producto;
        $producto->fm = $resultado["fm"];

    }

    function destroy() {
        $sql = "DELETE FROM productocategoria WHERE compuesto = ?";
        $dato = [$this->compuesto->categoria_id];
        consultar($sql, $dato);
    }


/*    static function get_categoria_denominacion_del_producto($producto_id) {
        $sql = "SELECT compuesto FROM productocategoria WHERE compositor = ?";
        $dato = [$producto_id];
        $resultado = consultar($sql, $dato)[0];

        $categoria = new Categoria();
        $categoria->categoria_id = $resultado['compuesto'];
        $categoria->select();
        return $categoria->denominacion;
    }

    static function get_categoria_del_producto($producto_id) {
        $sql = "SELECT compuesto FROM productocategoria WHERE compositor = ?";
        $dato = [$producto_id];
        return consultar($sql, $dato)[0];
    }
 */
}


class CategoriaHelper {

    static public $render_categorias = "";

    static function tabular_categorias($categoria, $html, $nivel=0) {
        $dic = [
            "{margin}" => $nivel - 1,
            "{guion}" => str_repeat("&#45;", $nivel),
            "{espacio}" => str_repeat("&nbsp", $nivel),
            "{categoria_id}" => $categoria->categoria_id,
            "{a_denominacion}" => $categoria->denominacion 
        ];
        CategoriaHelper::$render_categorias .= str_replace(
            array_keys($dic), 
            array_values($dic), 
            $html
        );
        $nivel++;
        foreach($categoria->categoria_collection as $a_categoria) {
            CategoriaHelper::tabular_categorias($a_categoria, $html, $nivel);
        }
    }


}
?>
