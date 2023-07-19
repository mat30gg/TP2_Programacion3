<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Factory\AppFactory;
use Slim\Psr7\Cookies;
use Slim\Routing\RouteCollectorProxy;
use Slim\Psr7\Response as ResponseMW;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/MiddleWare/ValidacionesEmpleado.php';
require_once __DIR__ . '/MiddleWare/ValidacionesProducto.php';
require_once __DIR__ . '/MiddleWare/ValidacionesPedido.php';
require_once __DIR__ . '/MiddleWare/ValidacionesFormulario.php';
require_once __DIR__ . '/MiddleWare/ValidacionesLogeo.php';
require_once __DIR__ . '/MiddleWare/ValidacionesPermisos.php';
require_once __DIR__ . '/MiddleWare/ValidacionesProcesarPedido.php';
require_once __DIR__ . '/MiddleWare/MWManipulacionMesas.php';
require_once __DIR__ . '/MiddleWare/MWEncuestas.php';

include_once "Clases/Logs.php";

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes

$app->map( ['GET', 'POST'], "[/]", function (Request $request, Response $response) {
    $response->getBody()->write( "<h1>Bienvenido a la comanda</h1>" );
    return $response->withHeader( 'Content-Type', 'text/html' );
});

$app->group("/menusocio", function(RouteCollectorProxy $group) {
    $group->delete("/cerrarmesa/{codigomesa}", function(Request $request, Response $response) {
        $codigoMesa = $request->getHeader('codigoMesa')[0];
        $mesa = Mesa::ObtenerPorCodigo($codigoMesa);
        $mesa->setEstado(Mesa::CERRADA);

        $response->getBody()->write(json_encode($mesa));
        return $response;
    })
    ->add( function(Request $request, Handler $handler) {
        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $codigoMesa = $route->getArgument("codigomesa");
        $request = $request->withAddedHeader( 'codigoMesa', $codigoMesa );
        $response = $handler->handle( $request );
        return $response;
    });

    $group->get("/listar/{objeto}", function (Request $request, Response $response) {
        include_once "Clases/Utilidades.php";
    
        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $claseObjeto;
    
        $nombreObjeto = $route->getArgument('objeto');
        switch($nombreObjeto){
            case "empleados":
                include_once "Clases/Empleado.php";
                $claseObjeto = Empleado::class;
                break;
            case "productos":
                include_once "Clases/Producto.php";
                $claseObjeto = Producto::class;
                break;
            case "mesas":
                include_once "Clases/Mesa.php";
                $claseObjeto = Mesa::class;
                break;
            case "pedidos":
                include_once "Clases/Pedido.php";
                $claseObjeto = Pedido::class;
                break;
            default:
                break;
        }
    
        if(isset($claseObjeto)){
            $response->getBody()->write( Utilidades::Listar( $claseObjeto::ObtenerListado() ));
        }
    
        return $response;
    });

    $group->group( '/alta', function (RouteCollectorProxy $group2) {
        $group2->map(["GET", "POST"], "/", function (Request $request, Response $response, array $args) {
            echo "Bienvenido al alta seleccione que dar de alta [el usuario selecciona de una serie de botones]";
            
            return $response;
        });
        
        $group2->post("/empleado", function (Request $request, Response $response) {
            include_once "Clases/Empleado.php";
            include_once "Clases/Usuario.php";
            include_once "Clases/Puesto.php";
    
            $requestBody = $request->getParsedBody();
            $nEmpleado = Empleado::Alta( $requestBody['nombre'], $requestBody['email'], Puesto::IdPuestoPorNombre($requestBody['puesto']), date_format( date_create(), 'Y-m-d') );
            Usuario::Alta( $nEmpleado, $requestBody['clave'] );
    
            $response->getBody()->write( json_encode( $nEmpleado ) );

            Logs::CrearLog( "Alta empleado ".$requestBody['email'] );
            return $response->withHeader( 'Content-Type', 'application/json' );
        })
        ->add( \ValidacionesEmpleado::class . ':ValidarQueNoExiste' )
        ->add( \ValidacionesEmpleado::class . ':ValidarDatos' );
    
        $group2->post("/producto", function (Request $request, Response $response) {
            include_once "Clases/Producto.php";
            include_once "Clases/Puesto.php";
    
            $requestBody = $request->getParsedBody();
            $nId = Producto::UltimoId()+1;
            $nProducto = new Producto( $requestBody['nombre'], Puesto::IdPuestoPorNombre( $requestBody['puestoasignado'] ), $nId, true, $requestBody['precio']);
            // $nProducto = Producto::Alta($requestBody['nombre'], Puesto::IdPuestoPorNombre( $requestBody['puestoasignado'] ), $requestBody['precio'] );
            Producto::Alta($nProducto);
            $response->getBody()->write( json_encode($nProducto) );
    
            Logs::CrearLog("Alta producto ".$requestBody['nombre'] );
            return $response->withHeader( 'Content-Type', 'application/json' );
        })
        ->add( \ValidacionesProducto::class . ':ValidarQueNoExiste' )
        ->add( \ValidacionesProducto::class . ':ValidarDatos' );
        
    })
    ->add( \ValidacionesFormulario::class . ':ValidarCamposLlenos');

    $group->group( '/baja', function (RouteCollectorProxy $group2) {

        $group2->post('/empleado/{id_empleado}', function(Request $request, Response $response) {
            include_once "Clases/Empleado.php";

            $id_empleado = $request->getHeader("id_empleado")[0];

            Empleado::Baja($id_empleado);
            Logs::CrearLog("Baja empleado");
            return $response;
        })
        ->add( \ValidacionesEmpleado::class . ':ValidarBaja')
        ->add( function($request, $handler) {
            $routeContext = RouteContext::fromRequest( $request );
            $route = $routeContext->getRoute();
            $id_empleado = $route->getArgument("id_empleado");
            $request = $request->withAddedHeader("id_empleado", $id_empleado);
            $response = $handler->handle($request);
            return $response;
        });

        $group2->delete('/borrarempleado/{id_empleado}', function(Request $request, Response $response) {
            include_once "Clases/Empleado.php";

            $id_empleado = $request->getHeader("id_empleado")[0];
            Empleado::BorrarEmpleado( $id_empleado );
            Usuario::BorrarUsuario($id_empleado);
            $response->getBody()->write( "Se borro el empleado ID:".$id_empleado );

            Logs::CrearLog("Borro empleado" );
            return $response;
        })
        ->add( \ValidacionesEmpleado::class . ':ValidarQueExiste')
        ->add( \ValidacionesEmpleado::class . ':ValidarBaja')
        ->add( function($request, $handler) {
            $routeContext = RouteContext::fromRequest( $request );
            $route = $routeContext->getRoute();
            $id_empleado = $route->getArgument("id_empleado");
            $request = $request->withAddedHeader("id_empleado", $id_empleado);
            $response = $handler->handle($request);
            return $response;
        });
    });

    $group->post( "/leerarchivocsv/{tipodato}", function( Request $request, Response $response ) {
        include_once "Clases/Producto.php";

        $tDato = $request->getHeader("tipodato")[0];
        $archivoCsv = $request->getUploadedFiles()['archivocsv'];

        $filePath = $archivoCsv->getFilePath();
        $stream = fopen( $filePath, "r");
        $arrayValores = [];
        while( !feof($stream) ){
            if($elemento = fgetcsv($stream)){
                if( $tDato == "productos" ) {
                    $nObj = new Producto( $elemento[0] ,$elemento[1] ,$elemento[2] ,$elemento[3] ,$elemento[4] );
                    Producto::Alta($nObj);
                }elseif( $tDato == "empleados" ){
                    // $nObj = new Empleado( $elemento[0] ,$elemento[1] ,$elemento[2] ,$elemento[3] ,$elemento[4], $elemento[5] );
                    // Empleado::Alta( $nObj->nombre, $nObj->email, $nObj->id_puesto, $nObj->fechaRegistro );
                }
                $arrayValores[] = $nObj;
            }
        }
        
        fclose($stream);
        $response->getBody()->write( json_encode($arrayValores));
        return $response;
    })
    ->add( function(Request $request, Handler $handler) {
        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $dato = $route->getArgument("tipodato");
        $request = $request->withAddedHeader( 'tipodato', $dato );
        $response = $handler->handle( $request );
        return $response;
    });

    $group->get("/descargararchivocsv/{tipodato}", function( Request $request, Response $response ) {
        include_once "Clases/Utilidades.php";

        $tDato = $request->getHeader("tipodato")[0];

        switch($tDato){
            case "empleados":
                include_once "Clases/Empleado.php";
                $claseObjeto = Empleado::class;
                break;
            case "productos":
                include_once "Clases/Producto.php";
                $claseObjeto = Producto::class;
                break;
            default:
                break;
        }

        if(isset($claseObjeto)){
            $listado = $claseObjeto::ObtenerListado();
            foreach($listado as $objeto){
                $response->getBody()->write( Utilidades::FormatearEnCSV( $objeto ) );
            }
            $response = $response->withHeader("Content-Disposition", "inline; filename=".$claseObjeto.".csv");
        }

        return $response->withHeader("Content-Type", "text/csv");
    })
    ->add( function(Request $request, Handler $handler) {
        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $dato = $route->getArgument("tipodato");
        $request = $request->withAddedHeader( 'tipodato', $dato );
        $response = $handler->handle( $request );
        return $response;
    });

    $group->get("/mejorescomentarios", function( Request $request, Response $response ) {
        include_once "Clases/Encuesta.php";
        include_once "Clases/Utilidades.php";
        $arrayComentarios = Encuesta::ObtenerMejoresComentarios();
        $response->getBody()->write( "Los mejores comentarios:\n" );
        $response->getBody()->write( Utilidades::Listar($arrayComentarios) );
        return $response;
    });

    $group->get("/mesamasusada", function( Request $request, Response $response ) {
        include_once "Clases/Mesa.php";
        include_once "Clases/Estadisticas.php";

        $mesaMasUsada = Stats::MesaMasUsada();
        $response->getBody()->write( "La mesa mas usada:\n" );
        $response->getBody()->write( json_encode($mesaMasUsada) );
        return $response;
    });

    $group->get("/pedidostardios", function( Request $request, Response $response ) {
        include_once "Clases/Pedido.php";
        include_once "Clases/Utilidades.php";
        $pedidosTardios = Pedido::PedidosTardios();
        $response->getBody()->write( "Los pedidos que no fueron entregados en el tiempo estipulado:\n" );
        $response->getBody()->write( Utilidades::Listar($pedidosTardios) );
        return $response;
    });

    $group->get("/logoempresa", function( Request $request, Response $response ) {

        $dirLogo = __DIR__ . "\\fotos\\logorestaurante\\";
        $nombreLogo = "logo1.jpeg";
        $dirArchivo = $dirLogo . $nombreLogo;
        $img = file_get_contents($dirArchivo);
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->Image( '@'.$img, 0, 0, 200);
        $pdf->Output("logoempresa.pdf", "I");
        return $response;

    });

    $group->get("/cantidadoperaciones/sector", function( Request $request, Response $response ) {
        $queryParams = $request->getQueryParams();
        $ascendente = $queryParams['ascendente'] ?? null;
        $fechadesde = $queryParams['fechadesde'] ?? null;
        $fechahasta = $queryParams['fechahasta'] ?? null;

        $arrayOperaciones = Logs::CantidadOperacionesPorSector($ascendente, $fechadesde, $fechahasta);
        $response->getBody()->write( "Operaciones de sectores:\n" );
        foreach( $arrayOperaciones as $sector ){
            $stringSector = ":". str_pad( $sector['nombre'],10 ,"." ) . " = " . $sector['cantidad_incidencias']. "\n";
            $response->getBody()->write( $stringSector );
        }
        return $response;
    });

    $group->get("/cantidadoperaciones/empleados", function( Request $request, Response $response ) {
        $queryParams = $request->getQueryParams();
        $ascendente = $queryParams['ascendente'] ?? null;
        $fechadesde = $queryParams['fechadesde'] ?? null;
        $fechahasta = $queryParams['fechahasta'] ?? null;

        $arrayOperaciones = Logs::CantidadOperacionesDeEmpleadosPorSector($ascendente, $fechadesde, $fechahasta);
        $response->getBody()->write( "Operaciones de empleados por sectores:\n" );
        foreach( $arrayOperaciones as $empleado ){
            $stringEmpleado = ":". str_pad( $empleado['nombre'],10 ,"." ) . " PUESTO ".$empleado['puesto']. " = " . $empleado['cantidad_incidencias']. "\n";
            $response->getBody()->write( $stringEmpleado );
        }
        return $response;
    });

    $group->get("/cantidadoperaciones/empleados/{id_empleado}", function( Request $request, Response $response ) {
        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $id_empleado = $route->getArgument("id_empleado");
        $empleado = Logs::CantidadOperacionesDeEmpleado($id_empleado);
        $stringEmpleado = ":NOMBRE: ". $empleado->nombre. ". PUESTO ".$empleado->puesto. ". CANTIDAD OPERACIONES = " . $empleado->cantidad_incidencias. "\n";
        $response->getBody()->write( $stringEmpleado );
        return $response;
    });

    $group->get("/registros/ingresosalsistema/{id_empleado}", function( Request $request, Response $response ) {
        $queryParams = $request->getQueryParams();
        $fechadesde = $queryParams['fechadesde'] ?? null;
        $fechahasta = $queryParams['fechahasta'] ?? null;

        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $id_empleado = $route->getArgument("id_empleado");

        $logEmpleado = Logs::CantidadIngresosAlSistema($id_empleado, $fechadesde, $fechahasta);
        $stringEmpleado = ":NOMBRE: ". $logEmpleado->nombre. ". CANTIDAD DE INGRESOS AL SISTEMA = " . $logEmpleado->cantidad_logins. "\n";
        $response->getBody()->write($stringEmpleado);
        return $response;
    });

    $group->get("/registros/productos/popularidad", function( Request $request, Response $response ) {
        $queryParams = $request->getQueryParams();
        $ascendente = $queryParams['ascendente'] ?? null;
        $fechadesde = $queryParams['fechadesde'] ?? null;
        $fechahasta = $queryParams['fechahasta'] ?? null;

        $stringProductos = "Productos ordenado por ventas:\n";
        $productosOrdenados = Logs::ObtenerProductosOrdenadoPorPopularidad($ascendente, $fechadesde, $fechahasta);
        foreach( $productosOrdenados as $prod ){
            $stringProductos.="[".$prod->id_producto."] ".$prod->nombre." Cantidad vendido: ".$prod->cantidad_vendido."\n";
        }
        $response->getBody()->write($stringProductos);
        return $response;
    });

    $group->get("/registros/mesas/facturacion", function( Request $request, Response $response ) {
        $queryParams = $request->getQueryParams();
        $ascendente = $queryParams['ascendente'] ?? null;
        $fechadesde = $queryParams['fechadesde'] ?? null;
        $fechahasta = $queryParams['fechahasta'] ?? null;

        $mesasString = "Mesas ordenadas por factura:\n";
        $mesasOrdenadas = Logs::ObtenerMesasPorFactura($ascendente, $fechadesde, $fechahasta);
        foreach( $mesasOrdenadas as $mesa ){
            $mesasString.="[".$mesa->codigoMesa."] Cantidad facturada: ".$mesa->precio_facturado."\n";
        }
        $response->getBody()->write($mesasString);
        return $response;
    });

    $group->get("/registros/mesas/{codigomesa}/facturacion", function( Request $request, Response $response ) {
        $queryParams = $request->getQueryParams();
        $fechadesde = $queryParams['fechadesde'] ?? null;
        $fechahasta = $queryParams['fechahasta'] ?? null;

        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $codigoMesa = $route->getArgument("codigomesa");

        $mesaString = "Cuanto facturo la mesa ". str_pad( $codigoMesa, 5, "0", STR_PAD_LEFT );
        if( $fechadesde ){
            $mesaString .= " desde la fecha ".$fechadesde;
        }
        if( $fechahasta ){
            $mesaString .= " hasta la fecha ".$fechahasta;
        }
        $mesaString .= ":\n";
        $factMesa = Logs::ObtenerFacturacionMesa($codigoMesa, $fechadesde, $fechahasta);
        $mesaString.="[".$factMesa->codigoMesa."] Cantidad facturada: $".($factMesa->precio_facturado+0)."\n";

        $response->getBody()->write($mesaString);
        return $response;
    });
})
->add( \ValidacionesPermisos::class . ':UsuarioAdministrador');

$app->group( '/menuempleado', function(RouteCollectorProxy $group) {
    $group->get( '/listarpendientes', function(Request $request, Response $response, array $args) {
        include_once "Clases/Pedido.php";
        include_once "Clases/DatosUsuarioLogueado.php";
        include_once "Clases/Utilidades.php";


        $id_puesto = DatoUsuario::IDPuesto();
        $stringEncargos = "Pedidos pendientes de ".Puesto::ObtenerNombreDePuestoID($id_puesto).":\n";
        
        $listaEncargos = Pedido::ObtenerListadoDePuesto( $id_puesto );
        

        $listaEncargos = array_filter( $listaEncargos, function( $ped ){
            return ($ped->estado != Pedido::SERVIDO );
        });
        $stringEncargos .= Utilidades::Listar($listaEncargos);
        $response->getBody()->write( $stringEncargos );
        return $response;
    });

    $group->get("/prepararpedido/{numeroPedido}", function(Request $request, Response $response) {
        include_once "Clases/DatosUsuarioLogueado.php";
        include_once "Clases/Pedido.php";

        $queryRequest = $request->getQueryParams();
        $minutosEstimados = $queryRequest['minutos'] ?? 0;

        $numPed = $request->getHeader('numPed')[0];
        $pedido = Pedido::ObtenerPorNumPed( $numPed );
        $pedido = $pedido->PrepararPedido( $minutosEstimados );
        
        $response->getBody()->write( "El pedido N° ".$pedido->numPed." esta en preparacion." );
        if( $minutosEstimados != 0){
            $response->getBody()->write(  " Hora estimada de finalizacion: ".$pedido->horaEstimada);
        }

        Logs::CrearLog("Preparar pedido", $numPed );
        return $response;
    })
    ->add( \ValidacionesProcesarPedidos::class . ':ValidarQuePedidoCorrespondaAUsuario')
    ->add( \ValidacionesProcesarPedidos::class . ':ValidarPedidoPendiente')
    ->add( \ValidacionesProcesarPedidos::class . ':ValidarPedidoExiste')
    ->add( function (Request $request, $handler){ 
        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $numPed = $route->getArgument("numeroPedido");
        $request = $request->withAddedHeader( 'numPed', $numPed );
        $response = $handler->handle( $request );
        return $response;
     });

    $group->get( '/pedidolisto/{numeroPedido}', function(Request $request, Response $response) {
        include_once "Clases/Pedido.php";

        $numPed = $request->getHeader('numPed')[0];
        $pedido = Pedido::ObtenerPorNumPed($numPed);
        $pedido->PedidoListo();

        $response->getBody()->write( 
        "El pedido N° ".$numPed." esta listo
        La hora estimada de finalizacion fue [".$pedido->horaEstimada."] y su hora de preparacion fue [".$pedido->horaFinal."]" );

        Logs::CrearLog("Pedido listo", $numPed);
        return $response;
    })
    ->add( \ValidacionesProcesarPedidos::class . ':ValidarQuePedidoCorrespondaAUsuario')
    ->add( \ValidacionesProcesarPedidos::class . ':ValidarPedidoEnPreparacion')
    ->add( \ValidacionesProcesarPedidos::class . ':ValidarPedidoExiste')
    ->add( function (Request $request, $handler){ 
        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $numPed = $route->getArgument("numeroPedido");
        $request = $request->withAddedHeader( 'numPed', $numPed );
        $response = $handler->handle( $request );
        return $response;
    });


})
->add( \ValidacionesPermisos::class . ':UsuarioStandard' );

$app->group( '/menumozo', function( RouteCollectorProxy $group) {

    $group->post("/crear/mesa", function(Request $request, Response $response) {
        include_once "Clases/Mesa.php";
    
        $fotomesa = null;
        if( !empty($_FILES["fotomesa"]["name"]) ){
            $fotomesa = $_FILES["fotomesa"];
        }

        $response->getBody()->write( json_encode( Mesa::Alta($fotomesa) ));

        Logs::CrearLog("Creo mesa");
        return $response->withHeader( 'Content-Type', 'application/json');
    });

    $group->post("/agregarpedido/{codigomesa}", function(Request $request, Response $response) {
        include_once "Clases/Pedido.php";
    
        $cuerpoRequest = $request->getParsedBody();
        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $codigoMesa = $route->getArgument("codigomesa");
        $encargos = $cuerpoRequest['encargos'];

        $arr = [];
        foreach( $encargos as $encargo ){
            $pedido = Pedido::Alta( $codigoMesa, $encargo );
            Logs::CrearLog("Tomar pedido", $pedido->numPed );
            $arr[] = $pedido;
        }
        $response->getBody()->write( json_encode( $arr ) );

        
        return $response->withHeader( 'Content-Type', 'application/json' );
    })
    ->add( \ValidacionesPedido::class . ":ValidarDatos" )
    ->add( \ValidacionesPedido::class . ":ValidarMesa" );

    $group->map(['GET', 'POST'], "/servirpedido/{codigomesa}/{numeropedido}", function(Request $request, Response $response){

        include_once "Clases/Mesa.php";
        include_once "Clases/Pedido.php";

        $codigoMesa = $request->getHeader( 'codigoMesa' )[0];
        $numPed = $request->getHeader( 'numPed' )[0];

        $pedido = Pedido::ObtenerPorNumPed($numPed);
        $mesa = Mesa::ObtenerPorCodigo( $codigoMesa );
        $mesa->setEstado( Mesa::CLIENTE_COMIENDO );
        $pedido->EntregarPedido();

        $response->getBody()->write( json_encode(["mesa" => $mesa, "pedido_servido" => $pedido], JSON_PRETTY_PRINT));

        Logs::CrearLog("Servir pedido", $pedido->numPed);
        return $response->withHeader("Content-Type", "application/json");
    })
    ->add( \ValidacionesProcesarPedidos::class . ':ValidarPedidoDeMesa')
    ->add( \ValidacionesProcesarPedidos::class . ':ValidarPedidoListo')
    ->add( \ValidacionesProcesarPedidos::class . ':ValidarPedidoExiste')
    ->add( \MWManipulacionMesas::class . ':ValidarMesaExiste' )
    ->add( function (Request $request, $handler){ 
        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $numPed = $route->getArgument("numeropedido");
        $codigoMesa = $route->getArgument("codigomesa");
        $request = $request->withAddedHeader( 'codigoMesa', $codigoMesa );
        $request = $request->withAddedHeader( 'numPed', $numPed );
        $response = $handler->handle( $request );
        return $response;
    });

    $group->map(['GET', 'POST'], "/cobrarmesa/{codigomesa}", function(Request $request, Response $response) {

        include_once "Clases/Pedido.php";

        $codigoMesa = $request->getHeader("codigoMesa")[0];
        $mesa = Mesa::ObtenerPorCodigo($codigoMesa);
        $listadoPedidosDeMesa = Pedido::ObtenerListadoDeMesa( $codigoMesa );
        $suma = 0;
        foreach( $listadoPedidosDeMesa as $pedido ){
            $prod = $pedido->getProducto();
            $response->getBody()->write( $prod.'' );
            $suma += $prod->precio;
            Logs::CrearLog("Cobro mesa", $pedido->numPed);
        }

        $response->getBody()->write( "TOTAL: $". number_format( $suma, 2 ));

        $mesa->setEstado( Mesa::CLIENTE_PAGANDO );
        
        return $response;
    })
    ->add( function (Request $request, $handler){ 
        $routeContext = RouteContext::fromRequest( $request );
        $route = $routeContext->getRoute();
        $codigoMesa = $route->getArgument("codigomesa");
        $request = $request->withAddedHeader( 'codigoMesa', $codigoMesa );
        $response = $handler->handle( $request );
        return $response;
    });
})
->add( \ValidacionesPermisos::class . ':UsuarioMozo');

$app->post("/altasocio", function( Request $request, Response $response ){
    include_once "Clases/Puesto.php";
    include_once "Clases/Empleado.php";
    include_once "Clases/Usuario.php";
    $requestBody = $request->getParsedBody();
    if( $requestBody["clavealtasocio"] == "5463"){
        $nSocio = Empleado::Alta( $requestBody['nombre'], $requestBody['email'], Puesto::IdPuestoPorNombre("socio"), date_format( date_create(), 'Y-m-d'));
        Usuario::Alta( $nSocio, $requestBody['clave'] );
        Logs::CrearLog("Alta socio");
    }
    return $response;
});


$app->post('/login', function( Request $request, Response $response ){
    include_once "Clases/Usuario.php";
    include_once "Clases/AutenticadorJWT.php";

    $cuerpoRequest = $request->getParsedBody();

    $usuario = Usuario::ObtenerPorEmail( $cuerpoRequest['email'] );
    if( $cuerpoRequest['clave'] == $usuario->clave ){

        $response->getBody()->write( "Iniciando sesion..." );
        $token = AutenticadorJWT::CrearToken( [ "id_empleado" => $usuario->id_empleado, "estado" => "Ok"]  );
        setcookie("jwt",$token,0,"/","",true,true);
        Logs::CrearLog("Log in", null, $usuario->id_empleado );
        
    } else {
        $response->getBody()->write( "Contraseña incorrecta" );
    }

    return $response;
})
->add( \ValidacionesLogueo::class . ':ValidarCredenciales')
->add( \ValidacionesFormulario::class . ':ValidarCamposLlenos');

$app->get('/logout', function( Request $request, Response $response ) {
    Logs::CrearLog("Log out");
    setcookie("jwt", "", -1);
    return $response;
})
->add( \ValidacionesPermisos::class . ':UsuarioStandard');

$app->get( "/verestado/{codigomesa}/{numeropedido}", function(Request $request, Response $response) {
    $numPed = $request->getHeader("numPed")[0];
    $pedido = Pedido::ObtenerPorNumPed($numPed);
    $response->getBody()->write( $pedido->__toString() );
    return $response;
})
->add( function (Request $request, $handler){ 
    $routeContext = RouteContext::fromRequest( $request );
    $route = $routeContext->getRoute();
    $numPed = $route->getArgument("numeropedido");
    $codigoMesa = $route->getArgument("codigomesa");
    $request = $request->withAddedHeader( 'codigoMesa', $codigoMesa );
    $request = $request->withAddedHeader( 'numPed', $numPed );
    $response = $handler->handle( $request );
    return $response;
});


$app->post( "/encuesta/{codigomesa}/{numeropedido}", function(Request $request, Response $response) {
    include_once "Clases/Encuesta.php";
    $requestBody = $request->getParsedBody();
    $codigoMesa = $request->getHeader("codigoMesa")[0];
    $encuesta = new Encuesta();

    $encuesta->codigoMesa = $codigoMesa;
    $encuesta->pMesa = $requestBody['puntuacion_mesa'];
    $encuesta->pRestaurante = $requestBody['puntuacion_restaurante'];
    $encuesta->pMozo = $requestBody['puntuacion_mozo'];
    $encuesta->pCocina = $requestBody['puntuacion_cocina'];
    $encuesta->comentarios = $requestBody['comentarios'];

    Encuesta::Alta($encuesta);
    $response->getBody()->write(json_encode($encuesta));

    return $response;
})
->add( \MWEncuestas::class . ':ValidarComentarios')
->add( \MWManipulacionMesas::class . ':ValidarMesaCerrada')
->add( \ValidacionesProcesarPedidos::class . ':ValidarPedidoDeMesa')
->add( \MWManipulacionMesas::class . ':ValidarMesaExiste')
->add( function(Request $request, Handler $handler) {
    $routeContext = RouteContext::fromRequest($request);
    $route = $routeContext->getRoute();
    $codigoMesa = $route->getArgument("codigomesa");
    $numPed = $route->getArgument("numeropedido");
    $request = $request->withAddedHeader("codigoMesa", $codigoMesa);
    $request = $request->withAddedHeader("numPed", $numPed);
    $response = $handler->handle($request);
    return $response;
});

$app->run();
