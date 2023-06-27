<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Psr7\Response as ResponseMW;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/MiddleWare/ValidacionesEmpleado.php';
require_once __DIR__ . '/MiddleWare/ValidacionesProducto.php';
require_once __DIR__ . '/MiddleWare/ValidacionesPedido.php';

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

$app->group("/listar", function (RouteCollectorProxy $group) {
    $group->get("/empleados", function(Request $request, Response $response) {
        include_once "Clases/Empleado.php";
        include_once "Clases/Utilidades.php";

        $response->getBody()->write( Utilidades::Listar( Empleado::ObtenerListado() ) );
    
        return $response;
    });

    $group->get("/productos", function(Request $request, Response $response) {
        include_once "Clases/Producto.php";
        include_once "Clases/Utilidades.php";

        $response->getBody()->write( Utilidades::Listar( Producto::ObtenerListado() ) );

        return $response;
    });

    $group->get("/mesas", function( Request $request, Response $response ){
        include_once "Clases/Mesa.php";
        include_once "Clases/Utilidades.php";

        $response->getBody()->write( Utilidades::Listar( Mesa::ObtenerListado() ) );
        
        return $response;
    });

    $group->get("/pedidos", function( Request $request, Response $response ){
        include_once "Clases/Pedido.php";
        include_once "Clases/Utilidades.php";

        $response->getBody()->write( Utilidades::Listar( Pedido::ObtenerListado() ) );
        
        return $response;
    });
});


$app->group('/alta', function (RouteCollectorProxy $group) {
    $group->map(["GET", "POST"], "/", function (Request $request, Response $response, array $args) {
        echo "Bienvenido al alta seleccione que dar de alta [el usuario selecciona de una serie de botones]";
        
        return $response;
    });
    
    $group->post("/empleado", function (Request $request, Response $response) {
        include_once "Clases/Empleado.php";

        $requestBody = $request->getParsedBody();
        $nEmpleado = Empleado::Alta( $requestBody['nombre'], $requestBody['email'], $requestBody['puesto'], date_format( date_create(), 'Y-m-d') );

        //hacer alta usuario y terminar login
        $response->getBody()->write( json_encode( $nEmpleado ) );
        return $response->withHeader( 'Content-Type', 'application/json' );
    })
    ->add( \ValidacionesEmpleado::class . ':ValidarQueNoExiste' )
    ->add( \ValidacionesEmpleado::class . ':ValidarDatos' )
    ->add( \ValidacionesEmpleado::class . ':ValidarRegistro' );

    $group->post("/producto", function (Request $request, Response $response) {
        include_once "Clases/Producto.php";

        $requestBody = $request->getParsedBody();
        $nProducto = new Producto($requestBody['nombre'], $requestBody['tipo'] );
        if( Producto::Alta( $nProducto ) ) {
            $response->getBody()->write( json_encode( ['mensaje' => 'Producto dado de alta'] ) );
        } else {
            $response->getBody()->write( json_encode( ['mensaje' => 'El producto ya existe'] ) );
        }

        return $response->withHeader( 'Content-Type', 'application/json' );
    })
    ->add( \ValidacionesProducto::class . ':ValidarDatos' )
    ->add( \ValidacionesProducto::class . ':ValidarIngresoDatos' );

});

$app->group( "/crear", function(RouteCollectorProxy $group) {
    $group->map(["GET", "POST"], "[/]", function (Request $request, Response $response, array $args) {
        echo "Seleccione que crear [el usuario selecciona de una serie de botones]";
        
        return $response;
    });

    $group->post( "/mesa", function( Request $request, Response $response ){
        include_once "Clases/Mesa.php";
    
        $response->getBody()->write( json_encode( Mesa::Alta() ) );
        return $response->withHeader( 'Content-Type', 'application/json');
    });
    
    $group->post( "/pedido", function( Request $request, Response $response) {
        include_once "Clases/Pedido.php";
    
        $cuerpoRequest = $request->getParsedBody();
        $nPedido = Pedido::Alta( $cuerpoRequest['codigoMesa'], $cuerpoRequest['encargo'], $cuerpoRequest['minutosEstimados']);
        $response->getBody()->write( json_encode( $nPedido ) );
        return $response->withHeader( 'Content-Type', 'application/json' );
    })
    ->add( ValidacionesPedido::class . ":ValidarMesa" )
    ->add( ValidacionesPedido::class . ":ValidarDatos" );
});

$app->post('/login', function( Request $request, Response $response ){
    include_once "Clases/Usuario.php";

    $cuerpoRequest = $request->getParsedBody();
    $usuarioLogueado = Usuario::LoguearUsuario( $cuerpoRequest['email'], $cuerpoRequest['clave'] );
    $response->getBody()->write( json_encode($usuarioLogueado) );

    return $response;
});


$app->run();
