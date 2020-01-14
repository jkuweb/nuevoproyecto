<?php
require_once 'core/europio_code.php';


class Usuario {

    function __construct() {
        $this->usuario_id = 0;
        $this->denominacion = "";
        $this->nivel = 0;
    }

    function insert() {
        $sql = "INSERT INTO usuario (
            usuario_id, 
            denominacion, 
            nivel
            ) 
            VALUES (?, ?, ?)";
        $datos = [
            $this->usuario_id,
            $this->denominacion,
            $this->nivel
        ];
        $this->usuario_id = consultar($sql, $datos);
    } 

    function select() {
        $sql = "SELECT denominacion FROM usuario WHERE usuario_id = ?";
        $dato = [$this->usuario_id];
        $resultado = consultar($sql, $dato)[0];
        $this->denominacion = $resultado['denominacion'];
    }

    function delete() {
        $sql = "DELETE FROM usuario WHERE usuario_id = ?";
        $dato = [$this->usuario_id];
        consultar($sql, $dato);
    }

}


class UsuarioView {

    function agregar($errores) {
        $template = file_get_contents('static/template_no_login.html');
	    $usuario_html = PATH_MODULO_USUARIO . "/agregar_usuario.html";	
        // usuario
        $usuario_form = Template::extract($usuario_html, 'FORM');
		$ok_usuario = ((@$_POST['usuario']) && (!@$errores['usuario_existe']));
		$dic = [
			"{denominacion}" => @$_POST['denominacion'],
			"{ok_denominacion}" => (@$_POST['denominacion']) ? "ok" : "",
			"{error_denominacion}" => @$errores['denominacion_vacio'],
			"{usuario}" => @$_POST['usuario'],
			"{ok_usuario}"=> ($ok_usuario) ? "ok" : '',
			"{error_usuario}"=> @$errores['usuario_vacio'],
			"{usuario_existe}"=> @$errores['usuario_existe'],
		];
        $usuario_render = str_replace(
            array_keys($dic), 
            array_values($dic), 
            $usuario_form
        );

        $errores_pass = "";
		$li = "\u{2022}";
        if($errores['password']) {
            $inicio = "El password :\n";
			$errores_pass .= "{$inicio}{$li} ";
			$errores_pass .= implode("\n{$li} ", $errores['password']);
			$errores_pass = nl2br($errores_pass);
        }
        $render_errores_pas = str_replace(
            '{errores}', 
            $errores_pass, 
            $usuario_render
        );

        $final = str_replace('<!--HTML-->', $render_errores_pas, $template);
        print $final; 
    }

    function logear($error) {
        $template = file_get_contents('static/template_no_login.html');
        $usuario_html = PATH_MODULO_USUARIO . "/logear_usuario.html";
        $logear_form = file_get_contents($usuario_html); 
        $final = str_replace("{error}", $error, $logear_form);
        $final = str_replace('<!--HTML-->', $final, $template);
        print $final; 
    }

    function listar($usuarios) {
        $template = file_get_contents('static/template.html');
        $usuario_html = PATH_MODULO_USUARIO . "/listar_usuarios.html";

        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
        // usuarios 
        $table = file_get_contents($usuario_html);
        $row = Template::extract($usuario_html, 'ROW');
        $render = "";
        foreach($usuarios as $usuario) {
            $dic = [
                "{denominacion}" => $usuario->denominacion,
                "{usuario_id}" => $usuario->usuario_id,
                "{nivel}" => $usuario->nivel
            ]; 
            $render .= str_replace(array_keys($dic), array_values($dic), $row);
        }
        $final = str_replace($row, $render, $table);
        $final = str_replace('<!--HTML-->', $final, $template);
        print $final;
    }

}


class UsuarioController {

    function __construct() {
        $this->model = new Usuario();
        $this->view = new UsuarioView();
    }

    function agregar($errores=[]) {
        $this->view->agregar($errores);
    }

    function __call($m, $a) { header("Location: /dashboard/home"); }

    function guardar() {
        // <?phpclassJoseba;
        // ECODG60ECODCECODG63ECODCphpclassJosebaECODG59ECODC
        $encode = EuropioCode::encode($_POST['password']);  # Solo para seguridad
        // &#60;&#63;phpclassJoseba&#59;
        $decode = EuropioCode::decode($encode);    # Para saber si hay caracteres extraños
        // phpclassjoseba
        $purge  = EuropioCode::purge($decode);
        // phpclassJoseba
        $clean  = EuropioCode::clean($decode);     # Para validar: min, MAY, números
        // VALIDAR  PASSWORD
        /*
        $errores = [];
        // 1 - longitud > 8
        if(!(strlen($_POST['password']) >= 8)) {
            $errores['password'][] = "Debe contener como mínimo 8 caracteres";
        } 
        // 2 - al menos una min
        if(!preg_match("/[a-z]/", $clean)) {
            $errores['password'][] =  "Debe contener al menos una minúscula";
        }
        // 2 - al menos una MAY
        if(!preg_match("/[A-Z]/", $clean)) {
            $errores['password'][] = "Debe contener al menos una Mayuscula";
        }
        // 3-  al menos un caracter extraño
        if(!(strpos($decode, "&#") !== false)) {
            $errores['password'][] = "Debe contener al menos un caracter extraño";
        }
        // 4 - al menos un número
        if(!preg_match("/[0-9]/", $clean)) {
            $errores['password'][] = "Debe contener al menos un número";
        }
 
        if(!strlen($_POST['denominacion']) >= 1) {
            $errores['denominacion_vacio'] = "El nombre es requerido";
        }

        if(!strlen($_POST['usuario']) >= 1) {
            $errores['usuario_vacio'] = "El usuario es requerido";
        }

        // NOMBRE DE USUARIO YA EXISTE ( el nombre de usuario haseado lo usamos como ID) 
        $usuario_existe = UsuarioHelper::verificar(hash("crc32", $_POST['usuario']));
        if(!empty($usuario_existe['denominacion'])) {
            $errores['usuario_existe'] = "Usuario registrado, prueba con otro";
        }

        if($errores || !empty($errores['password'])) exit($this->agregar($errores));
        */
        $this->model->usuario_id = hash("crc32", $_POST['usuario']);
        $this->model->denominacion = $_POST['denominacion'];
        $this->model->insert();


        // CREDENCIAL
        $sha1 = hash("sha256", $_POST['usuario']);
        $sha2 = hash("crc32", $_POST['password']);
        $reverse = strrev($_POST['usuario']);
        $salt = hash("fnv132", $reverse);
        $credencial = md5($sha1 . $salt . $sha2);
        $ruta = PATH_CREDENCIALES."/.{$credencial}";
        file_put_contents($ruta, ""); 
        header("Location: /dashboard/home");
    }

    function autenticar() {
        // CREDENCIAL
        $sha1 = hash("sha256", $_POST['usuario']);
        $sha2 = hash("crc32", $_POST['password']);
        $reverse = strrev($_POST['usuario']);
        $salt = hash("fnv132", $reverse);
        $credencial = md5($sha1 . $salt . $sha2);

        $ruta = PATH_CREDENCIALES."/.{$credencial}";
        
        $error = "";
        if(!file_exists($ruta)) {
            $error = "Usuario y/o contraseña incorrectas";
        } 
        
        $usuario_exite = UsuarioHelper::verificar(hash("crc32", $_POST['usuario']));
        if(!$usuario_exite) {
            $error = "Usuario y/o contraseña incorrectas";
        }
        
        if($error) exit($this->logear($error));

        $this->model->usuario_id = hash("crc32", $_POST['usuario']);
        $this->model->select();

        $_SESSION['usuario_id'] = $this->model->usuario_id;
        $_SESSION['denominacion'] = $this->model->denominacion;
        $_SESSION['auth'] = true;

        header("Location: /dashboard/home");
    }

    function logear($error="") {
        $this->view->logear($error);
    }

    function logout() {
        UsuarioHelper::check();
        session_destroy();
        $_SESSION['usuario_id'] = "";
        $_SESSION['denominacion'] = "";
        $_SESSION['auth'] = false;

        header("Location: /usuario/logear");
    }

    function listar($usuarios) {
        UsuarioHelper::check();
        $colector = new Collector();
        $colector->get("Usuario");
        $usuarios = $colector->coleccion;
        $this->view->listar($usuarios);
    } 

    function eliminar($id="") {
        UsuarioHelper::check();
        $this->model->usuario_id =  (string) $id;
        $this->model->delete();
        
        header("Location: /usuario/listar");
    }

}


class UsuarioHelper {

    static function check() {
       if(!@$_SESSION['auth']) exit(header("Location: /usuario/logear"));
    } 

    static function verificar($usuario_id) {
        $sql = "SELECT denominacion FROM usuario WHERE usuario_id = ?";
        $dato = [$usuario_id];
        return  consultar($sql, $dato)[0];
    }

}
?>
