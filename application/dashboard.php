<?php
require_once 'pedido.php';


class DashboardView {
    
    function home($pedidos) {
        $template = file_get_contents('static/template.html');
        $sesion = $_SESSION['denominacion'] ?? "Invitado";  
        $template = str_replace("{denominacion_usuario}", $sesion, $template); 
		$dashboard = file_get_contents('static/dashboard.html');
        // ventas
        $ventas_totales = PedidoDataHelper::get_precio_total_pedidos();
        $render_ventas = str_replace("{ventas_totales}", $ventas_totales, $dashboard);
        
        // cliente
        $clientes_html = Template::extraer('static/dashboard.html', 'clientes');
        $clientes = ClienteDataHelper::get_ultimos_clientes();
        $render_cliente = "";
        if(!empty($clientes)){
            foreach($clientes as $cliente) {
                $dic = [
                    "{clientex_id}" => $cliente['cliente_id'],
                    "{clientex_denominacion}" => $cliente['denominacion']
                ];
                $render_cliente .= str_replace(array_keys($dic), array_values($dic), $clientes_html);
            }
        }

        $render_clientes = str_replace($clientes_html, $render_cliente, $render_ventas);

        // producto
        $productos_html = Template::extraer('static/dashboard.html', 'productos');
        $productos = ProductoDataHelper::get_ultimos_productos_agregados();
        $render_producto = "";
        foreach($productos as $producto) {
            $dic = [
                "{producto_id}" => $producto->producto_id,
                "{producto_denominacion}" => $producto->denominacion,
                "{producto_precio}" => $producto->precio
            ]; 
            $render_producto .= str_replace(array_keys($dic), array_values($dic), $productos_html);
        }
        $render_productos = str_replace($productos_html, $render_producto, $render_clientes);
        // ordenes recientes
		$pedidos_html = Template::extraer('static/dashboard.html', 'pedidos');
		$pedidos_render = "";
		foreach($pedidos as $index => $pedido) {
            $denominacion_cliente = ClienteDataHelper::get_denominacion($pedido->cliente);
            $precio_pedido = PedidoDataHelper::get_precio_total_pedido($pedido->pedido_id);
			$dic = [
				"{indice}" => ++$index,	
				"{pedido_id}" => $pedido->pedido_id,	
				"{pedido_fecha}" => $pedido->fecha,	
				"{cliente_id}" => $pedido->cliente,	
				"{cliente_denominacion}" => $denominacion_cliente,	
				"{pedido_precio}" => $precio_pedido,
				"{bgc_estado}" => ($pedido->estado)? BGC_ESTADO_PEDIDO[$pedido->estado] : "#ff00001a",
				"{color_estado}" => ($pedido->estado)? COLOR_ESTADO_PEDIDO[$pedido->estado] : "red",
				"{pedido_estado}" => ESTADO_PEDIDO[$pedido->estado]	
			];
			$pedidos_render .= str_replace(array_keys($dic), array_values($dic), $pedidos_html);
		}

		$render_pedidos = str_replace($pedidos_html, $pedidos_render, $render_productos);
		// pedidos pendientes
		$pedidos_pendientes_html = Template::extraer('static/dashboard.html', 'pedidos_pendientes');
		$pedidos_pendientes_render = "";
		$pedidos_pendientes = PedidoDataHelper::get_pedidos_en_espera();
		foreach($pedidos_pendientes as $pedido) {
            $denominacion_cliente = ClienteDataHelper::get_denominacion($pedido->cliente);
			$dic = [
				"{pedido_id}" => $pedido->pedido_id,	
				"{pedido_fecha}" => $pedido->fecha,	
				"{cliente_id}" => $pedido->cliente,	
				"{cliente_denominacion}" => $denominacion_cliente,	
				"{bgc_estado}" => (!$pedido->estado)? BGC_ESTADO_PEDIDO[$pedido->estado] : "inherit",
				"{color_estado}" => (!$pedido->estado)? COLOR_ESTADO_PEDIDO[$pedido->estado] : "inherit",
				"{pedido_estado}" => ESTADO_PEDIDO[$pedido->estado]	
			];
			$pedidos_pendientes_render .= str_replace(array_keys($dic), array_values($dic), $pedidos_pendientes_html);
		}
		$render_pedidos_pendientes = str_replace($pedidos_pendientes_html, $pedidos_pendientes_render, $render_pedidos);

        $devoluciones = PedidoDataHelper::get_valor_devoluciones();
        $dic = [
            "{ventas}" => $ventas_totales - $devoluciones , 
            "{devoluciones}" => $devoluciones 
        ];    
        $final = str_replace(array_keys($dic), array_values($dic), $render_pedidos_pendientes);

		$final = str_replace('<!--HTML-->', $final, $template);
        print $final;
    }

}


class DashboardController {

    function __construct() {
        $this->view = new DashboardView();
    }

    function home() {
        UsuarioHelper::check();
        $pedidos = PedidoDataHelper::get_ultimos_pedidos();
        $this->view->home($pedidos);
    }
}
?>
