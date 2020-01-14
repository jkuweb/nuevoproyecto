<?php
require_once 'domicilio.php';
require_once 'datodecontacto.php';
require_once 'pedido.php';
require_once 'usuario.php';
require_once 'core/template.php';
require_once 'common/view.php';


class Cliente {

    function __construct() {    
        $this->cliente_id = 0;
        $this->denominacion = "";
        $this->nif = "";
        $this->domicilio = new Domicilio(); 
        $this->datodecontacto_collection = [];
        $this->pedido_collection = [];
    }

    function insert() {
        $sql = "INSERT INTO     cliente
                                (denominacion, nif, domicilio) 
                VALUES          (?, ?, ?)";
        $datos = [
            $this->denominacion,
            $this->nif,
            $this->domicilio->domicilio_id
        ];
        $this->cliente_id = consultar($sql, $datos);
    }
    
    function select() {
        $sql = "SELECT  denominacion, nif, domicilio 
                FROM    cliente 
                WHERE   cliente_id = ?";
        $datos = [$this->cliente_id];
        $resultado = consultar($sql, $datos)[0];

        $this->denominacion = $resultado['denominacion'];
        $this->nif = $resultado['nif'];
        $this->domicilio->domicilio_id = $resultado['domicilio'];
        $this->domicilio->select();

        $datodecontactos = DatoDeContacto::get_datodecontacto($this->cliente_id);
        foreach($datodecontactos as $array) {
            $datodecontacto = new DatoDeContacto();
            $datodecontacto->datodecontacto_id = $array['datodecontacto_id'];
            $datodecontacto->select();
            $this->datodecontacto_collection[] = $datodecontacto;
        } 

        $pedidos = PedidoDataHelper::get_pedido($this->cliente_id);
        if(!empty($pedidos)) {  # FIXME 
            foreach($pedidos as $array) {
                $pedido = new Pedido();
                $pedido->pedido_id = $array['pedido_id'];
                $pedido->select();
                $this->pedido_collection[] = $pedido;
            }
        }
    }

    function update() {
        $sql = "UPDATE  cliente 
                SET     denominacion = ?, nif = ?, domicilio = ? 
                WHERE   cliente_id = ?";
        $datos = [
            $this->denominacion,
            $this->nif,
            $this->domicilio->domicilio_id,
            $this->cliente_id
        ];
        consultar($sql, $datos);
    }

    function delete() {
        $sql = "DELETE FROM cliente 
                WHERE       cliente_id = ?";
        $datos = [$this->cliente_id];
        consultar($sql, $datos);
    }

}


class ClienteView extends CommonView{

    function agregar($error=[]) {
        extract($_POST);
        $template = $this->get_rendered_template();
        $dict = [
            "{denominacion_usuario}" => $_SESSION['denominacion'] ?? "Invitado",
            "{denominacion}" => @$denominacion,
            "{nif}"=> @$nif,
            "{calle}"=> @$calle,
            "{numero}" => @$numero,
            "{planta}" => @$planta,
            "{puerta}" => @$puerta,
            "{ciudad}" => @$ciudad,
            "{email}" => @$email,
            "{movil}" => @$movil,

            "{ok_nif}"=> Template::get_check_symbol(@$nif, @$error['nif']),
            "{ok_calle}"=> Template::get_check_symbol(@$calle, @$error['calle']),
            "{ok_numero}"=> Template::get_check_symbol(@$numero, @$error['numero']),
            "{ok_planta}"=> Template::get_check_symbol(@$planta, @$error['planta']),
            "{ok_puerta}"=> Template::get_check_symbol(@$puerta, @$error['puerta']),
            "{ok_email}"=> Template::get_check_symbol(@$email, @$error['email']),
            "{ok_ciudad}"=> Template::get_check_symbol(@$ciudad, ''),
            "{ok_movil}"=> Template::get_check_symbol(@$movil, ''),
            "{ok_denominacion}"=> Template::get_check_symbol(@$denominacion, ''),

            "{error_denominacion}" => @$error['denominacion'],
            "{error_nif}" => @$error['nif'], 
            "{error_calle}" => @$error['calle'],
            "{error_numero}" => @$error['numero'],
            "{error_planta}" => @$error['planta'],
            "{error_puerta}" => @$error['puerta'],
            "{error_ciudad}" => @$error['ciudad'],
            "{error_email}" => @$error['email'],
            "{error_movil}" => @$error['movil'],
            "{error_imagen}" => @$error['mime']
        ];

        $file_cliente = PATH_MODULO_CLIENTE . '/cliente_agregar_form.html';
        $form = Template::render_dict($file_cliente, $dict);
        print $this->render($form, $template);
    }

    function ver($cliente) {
        $template = file_get_contents('static/template.html');
        $file_cliente = PATH_MODULO_CLIENTE . '/cliente_ver.html';

        $username = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $username, $template); 
        // cliente
        $html = file_get_contents($file_cliente);
        $dic = [
            "{cliente_id}" => $cliente->cliente_id,
            "{denominacion}" => $cliente->denominacion,
            "{nif}" => $cliente->nif,
            "{calle}" => $cliente->domicilio->calle,
            "{numero}" => $cliente->domicilio->numero,
            "{planta}" => $cliente->domicilio->planta,
            "{puerta}" => $cliente->domicilio->puerta,
            "{ciudad}" => $cliente->domicilio->ciudad,
        ];
        $render_cliente = str_replace(
            array_keys($dic), 
            array_values($dic), 
            $html
        );
        // dato de contacto 
        $datodecontacto_html = Template::extract(
            $file_cliente, 
            'datosdecontacto'
        );
        $datodecontacto_render = "";
        foreach($cliente->datodecontacto_collection as $datodecontacto) {
            $dic = [
                "{denominacion_contacto}" => $datodecontacto->denominacion,
                "{valor}" => $datodecontacto->valor
            ];
            $datodecontacto_render .= str_replace(
                array_keys($dic), 
                array_values($dic), 
                $datodecontacto_html
            );
        }
        $datodecontacto_render = str_replace(
            $datodecontacto_html, 
            $datodecontacto_render, 
            $render_cliente
        );
        // pedido
        $pedido_html = Template::extract($file_cliente, 'pedido');
        $pedido_render = "";
        foreach($cliente->pedido_collection as $pedido) {
            $dic = [
               "{pedido_id}" => $pedido->pedido_id, 
               "{fecha}" => $pedido->fecha, 
               "{estado}" => ESTADO_PEDIDO[$pedido->estado]
           ];
            $pedido_render .= str_replace(
                array_keys($dic), 
                array_values($dic), 
                $pedido_html
            );
        }
        $pedido_render = str_replace(
            $pedido_html, 
            $pedido_render, 
            $datodecontacto_render
        );
        $final = str_replace('<!--HTML-->', $pedido_render, $template);
        print $final;
    }

    function editar($cliente, $error) {
        $template = file_get_contents('static/template.html');
        $file_cliente = PATH_MODULO_CLIENTE . '/cliente_editar.html';

        $username = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $username, $template); 
        // imagen
        $form = file_get_contents($file_cliente);
        $file_exist = file_exists(PATH_IMAGES . "/cliente/{$cliente->cliente_id}");
        $imagen_html = Template::extraer($file_cliente, 'imagen');
        $dic = [
            "{recurso}" => ($file_exist) ? "eliminarImagen" : "imagen",
            "{imagen}" => ($file_exist) ? $cliente->cliente_id :"default.png",
            "{accion}" => ($file_exist) ? "Eliminar": "Ver"
        ];
        $imagen_render = str_replace(array_keys($dic), array_values($dic), $form);
        // cliente 
        $dic = [
            "{cliente_id}" => $cliente->cliente_id,
            "{domicilio_id}" => $cliente->domicilio->domicilio_id,
            "{denominacion}" => $cliente->denominacion,
            "{error_denominacion}" => @$error['error_denominacion'],
            "{nif}"=> @$cliente->nif,
            "{error_nif}" => @$error['nif'], 
            "{calle}"=> @$cliente->domicilio->calle,
            "{error_calle}" => @$error['error_calle'],
            "{numero}" => @$cliente->domicilio->numero,
            "{error_numero}" => @$error['error_numero'],
            "{planta}" => @$cliente->domicilio->planta,
            "{error_planta}" => @$error['error_planta'],
            "{puerta}" => @$cliente->domicilio->puerta,
            "{error_puerta}" => @$error['error_puerta'],
            "{ciudad}" => @$cliente->domicilio->ciudad,
            "{error_ciudad}" => @$error['error_ciudad'],
            "{error_email}" => @$error['email'],
            "{error_movil}" => @$error['error_movil'],
            "{error_imagen}" => @$error['error_mime']
        ];
        $cliente_render = str_replace(
            array_keys($dic), 
            array_values($dic), 
            $imagen_render
        );
        // dato de contacto
        $datodecontacto_html = Template::extract($file_cliente, 'contacto');
        $render_contacto = "";
        foreach($cliente->datodecontacto_collection as $contacto) {
            $dic = [
                "{denominacion_contacto}" => $contacto->denominacion,
                "{valor}" => $contacto->valor,
                "{contacto_id}" => $contacto->datodecontacto_id
            ];
            $render_contacto .= str_replace(
                array_keys($dic), 
                array_values($dic), 
                $datodecontacto_html
            );
        }
        $render_contacto = str_replace(
            $datodecontacto_html, 
            $render_contacto, 
            $cliente_render
        );
        $final = str_replace('<!--HTML-->', $render_contacto, $template);
        print $final;
    }

    function listar($clientes) {
        $template = file_get_contents('static/template.html');
        $file_cliente = PATH_MODULO_CLIENTE . '/cliente_listar.html';

        $username = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $username, $template); 
        // clientes
        $tabla = file_get_contents($file_cliente);
        $fila = Template::extract($file_cliente, 'fila');
        $render = "";
        foreach($clientes as $cliente) {
            $dic = [
                "{cliente_id}" => $cliente->cliente_id,
                "{denominacion}" => $cliente->denominacion,
                "{nif}" => $cliente->nif,
                "{domicilio_id}" => $cliente->domicilio->domicilio_id,
                "{calle}" => $cliente->domicilio->calle,
                "{numero}" => $cliente->domicilio->numero,
                "{planta}" => $cliente->domicilio->planta,
                "{puerta}" => $cliente->domicilio->puerta,
                "{ciudad}" => $cliente->domicilio->ciudad
            ];

            $render .= str_replace(array_keys($dic), array_values($dic), $fila);
        }
        $tabla_final = str_replace($fila, $render, $tabla);
        $final = str_replace('<!--HTML-->', $tabla_final, $template);
        print $final;
    }
    
}


class ClienteController {
    
    function __construct() {
        $this->model = new Cliente();
        $this->view = new ClienteView();
    }

    function __call($m, $a) { $this->listar(); }

    function agregar($error=[]) {
        UsuarioHelper::check();
        $this->view->agregar($error);
    }
    
    function guardar() {        
        UsuarioHelper::check();
        extract($_POST);
        extract($_FILES);
        settype($numero, 'int');
        settype($planta , 'int');

        // validar formato NIF 
        $regex = '/^[0-9]{8,8}[A-Za-z]$/s'; // 12345678z
        $a = preg_match($regex, $nif, $coincidencias);

        // validar EMAIL
        $email = filter_var($email, FILTER_VALIDATE_EMAIL); 
        
        $error = [];
        if(!$coincidencias) $error['nif'] = ERR_NIF_FORMAT;                                                            
        if(!$email) $error['email'] =  ERR_EMAIL;                                                                     
        if(!$denominacion) $error['denominacion'] = ERR_DENOMINACION_EMPTY;
        if(!$calle) $error['calle'] = ERR_CALLE_EMPTY; 
        if(!($numero > 0)) $error['numero'] = ERR_NUMERO_EMPTY;
        if(!($planta > 0)) $error['planta'] = ERR_PLANTA_EMPTY;
        if(!$puerta) $error['puerta'] = ERR_PUERTA_EMPTY;
        if(!$ciudad) $error['ciudad'] = ERR_CIUDAD_EMPTY;
        if(!$movil) $error['movil'] = ERR_TELEFONO_EMPTY;

        // Validar el tipo MIME de las imágenes
        if(!in_array($imagen['type'], MIMES_PERMITIDOS) && $imagen['type']) {
             $error['mime'] = ERR_IMAGE_TYPE;
        }

        if($error) exit($this->agregar($error));
        $nif = $coincidencias[0];
        
        // 1. guardar Compositor
        $this->model->domicilio->calle = $calle;
        $this->model->domicilio->numero = $numero;
        $this->model->domicilio->planta = $planta;
        $this->model->domicilio->puerta = $puerta;
        $this->model->domicilio->ciudad = $ciudad;
        $this->model->domicilio->insert();

        // 2. guardar objeto Compuesto
        $this->model->denominacion = $denominacion;
        $this->model->nif = $nif;
        $this->model->insert();

        // 3. guardar objeto Dependiente DatoDeContacto
        $dc = new DatoDeContactoController();
        $dc->guardar($this->model);

        $destino = PATH_IMAGES."/cliente/{$this->model->cliente_id}";
        move_uploaded_file($_FILES['imagen']['tmp_name'], $destino);
        
        header("Location: /cliente/ver/{$this->model->cliente_id}");
    }

    function ver($id=0) {
        UsuarioHelper::check();
        $this->model->cliente_id = (int) $id;
        $this->model->select();       
        $this->view->ver($this->model);
     }

     function editar($id=0, $error=[]) {
        UsuarioHelper::check();
        $this->model->cliente_id = (int) $id;
        $this->model->select();        
        $this->view->editar($this->model, $error);
     }

     function actualizar() {
        UsuarioHelper::check();
        extract($_POST);
        extract($_FILES);
        // SANEAR
        settype($numero, 'int');
        settype($planta , 'int');
        //VALIDAR

        // validar formato NIF 
        $regex = '/^[0-9]{8,8}[A-Za-z]$/s'; // 12345678z
        $a = preg_match($regex, $nif, $coincidencias);

        // validar EMAIL
        $email = $valor[0];
        $movil = $valor[1];
        $email = filter_var($email, FILTER_VALIDATE_EMAIL); 
        
        $error = [];
        if(!$coincidencias) $error['nif'] = ERR_NIF_FORMAT;                                                            
                                                                                                         
        if(!$email) $error['email'] =  ERR_EMAIL;                                                                     
        
        if(!$denominacion) $error['denominacion'] = ERR_DENOMINACION_EMPTY;
        
        if(!$calle) $error['calle'] = ERR_CALLE_EMPTY; 

        if(!($numero > 0)) $error['numero'] = ERR_NUMERO_EMPTY;

        if(!($planta > 0)) $error['planta'] = ERR_PLANTA_EMPTY;

        if(!$puerta) $error['puerta'] = ERR_PUERTA_EMPTY;

        if(!$ciudad) $error['ciudad'] = ERR_CIUDAD_EMPTY;

        if(!$movil) $error['movil'] = ERR_TELEFONO_EMPTY;

        // Validar el tipo MIME de las imágenes
        $mimes_permitidos = ['image/jpg', 'image/png', 'image/webp', 'image/jpeg']; 
        if(!in_array($imagen['type'], $mimes_permitidos) && $imagen['type']) {
             $error['mime'] = ERR_IMAGE_TYPE;
        }

        if($error) exit($this->editar($cliente_id, $error));
        $nif = $coincidencias[0];
         
        $this->model->domicilio->calle = $calle;
        $this->model->domicilio->numero = $numero;
        $this->model->domicilio->planta = $planta;
        $this->model->domicilio->puerta = $puerta;
        $this->model->domicilio->ciudad = $ciudad;
        $this->model->domicilio->domicilio_id = $domicilio_id;
        $this->model->domicilio->update();
 
        $this->model->denominacion = $denominacion;
        $this->model->nif = $nif;
        $this->model->cliente_id = $cliente_id;
        $this->model->update();

        $datodecontacto = new DatoDeContactoController();
        $datodecontacto->actualizar($this->model);

        $destino = PATH_IMAGES."/cliente/{$this->model->cliente_id}";
        move_uploaded_file($_FILES['imagen']['tmp_name'], $destino);
        header("Location:/cliente/ver/{$this->model->cliente_id}");
     }

     function eliminar($id=0) {
         UsuarioHelper::check();
         $this->model->cliente_id = (int) $id;
         $this->model->delete();

         header('Location: /cliente/listar');
     }

     function listar() {
        UsuarioHelper::check();
        $coleccion = new Collector();
        @$coleccion->get("Cliente");
        $clientes = $coleccion->coleccion;
        
        $this->view->listar($clientes);
     }

    function imagen($id=0) {
        UsuarioHelper::check();
        $uri = rawurldecode($_SERVER['REQUEST_URI']);
        list($null, $modulo, $recurso, $cliente_id) = explode('/', $uri);
        $archivo = PATH_IMAGES."/cliente/$cliente_id" ;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if(!file_exists($archivo)) {
            $archivo = PATH_IMAGES."/cliente/default.png";
        }
        $mime = finfo_file($finfo, $archivo);
        finfo_close($finfo);
        header("Content-type: $mime");
        readfile($archivo);
    }

    function eliminarImagen($id=0) {
        UsuarioHelper::check();
        $uri = rawurldecode($_SERVER['REQUEST_URI']);
        list($null, $modulo, $recurso, $cliente_id) = explode('/', $uri); 
        $fichero = PATH_IMAGES."/cliente/$cliente_id" ;
        unlink($fichero);
        header("Location: /cliente/editar/{$id}");
    }

}


class ClienteDataHelper {

    static function get_denominacion($cliente_id) {
        $sql = "SELECT denominacion FROM cliente WHERE cliente_id = ?";
        $dato = [$cliente_id];
        $resultado = consultar($sql, $dato)[0];
        return @$resultado['denominacion'];
    }

    static function get_ultimos_clientes() {
        $sql = "SELECT cliente_id, denominacion FROM cliente ORDER BY cliente_id DESC LIMIT 5"; 
        $datos = [];
        return consultar($sql, $datos);
    }

}

?>
