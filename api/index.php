<?php


require_once '../config/jwt_helper.php';
require_once '../config/config.php';

define('ALGORITMO', 'HS512'); // Algoritmo de codificación/firma
define('SECRET_KEY', 'AS..-.DJKLds·ak$dl%Ll!3kj12l3k1sa4_ÑÑ312ñ12LK3Jj4DK5A6LS7JDLK¿?asDqiwUEASDL,NMQWIEUIO'); //String largo y "complicado"

if (!isset($_GET['accion'])) {
    outputError();
}


$metodo = strtolower($_SERVER['REQUEST_METHOD']);
$accion = explode('/', strtolower($_GET['accion']));
$funcionNombre = $metodo . ucfirst($accion[0]);
$parametros = array_slice($accion, 1);
if (count($parametros) >0 && $metodo == 'get') {
    $funcionNombre = $funcionNombre.'ConParametros';
}
if (function_exists($funcionNombre)) {
    call_user_func_array ($funcionNombre, $parametros);
} else {
    outputError(400);
}


function outputJson($data, $codigo = 200)
{
    header('', true, $codigo);
    header('Content-type: application/json');
    print json_encode($data);
}

function outputError($codigo = 500, $mensaje=false)
{
    switch ($codigo) {
        case 400:
            header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad request", true, 400);
            break;
        case 404:
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
            break;
        case 401:
            header($_SERVER["SERVER_PROTOCOL"] . " 401 Unauthorized", true, 401);
            break;
        default:
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error", true, 500);
            break;
    }
    if ($mensaje!==false) {
        print json_encode(['error'=>$mensaje]);
    }
    die;
}

function conectarBD()
{
    $link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBBASE);
    if ($link === false) {
        print "Falló la conexión: " . mysqli_connect_error();
        outputError(500);
    }
    return $link;
}

function requireLogin () {
    $authHeader = getallheaders();
    try
    {
        list($jwt) = @sscanf( $authHeader['Authorization'], 'Bearer %s');
        $datos = JWT::decode($jwt, SECRET_KEY, ALGORITMO);
        $datos = [
            'id' => $datos->id,
            'expira' => $datos->expira,
        ];
        if (time() > $datos['expira']) {
            postLogout();
            throw new Exception("Token expirado", 1);
        }
        $link = conectarBD();
        $resultado = mysqli_query($link, "SELECT 1 FROM tokens WHERE token = '$jwt'");
        if (!($resultado && mysqli_num_rows($resultado)==1)) {
            throw new Exception("Token inválido", 1);
        }
        mysqli_close($link);
    } catch(Exception $e) {
        outputError(401);
    }
    return $datos;
}

function postLogin() {
    $loginData = json_decode(file_get_contents("php://input"), true);
    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');

    $email = mysqli_real_escape_string($link, $loginData['email']);
    $clave = mysqli_real_escape_string($link, $loginData['clave']);

    $sql = "SELECT id, idPerfil, nombre FROM usuarios us INNER JOIN usuarioperfil up ON us.id = up.idUsuario WHERE us.email='$email' AND us.password='$clave' AND us.estatus = 1";
    $resultado = mysqli_query($link, $sql);
    if($resultado && mysqli_num_rows($resultado)==1) {
        $res = mysqli_fetch_assoc($resultado);
        $data = [
            'expira'    => time() + 3600,
            'id'        => $res['id']+0,
        ];
        $jwt = JWT::encode($data, SECRET_KEY, ALGORITMO);
        if (mysqli_query($link, "INSERT INTO tokens (token) VALUES ('$jwt')")) {
            outputJson(['jwt' => $jwt, 'idusuario' => $res['id']+0, 'idperfil' => $res['idPerfil']+0, 'nombre' => $res['nombre']]);
            die;
        }
    }
    outputError(401);
}

function postLogout() {
    $link = conectarBD();
    $authHeader = getallheaders();
    list($jwt) = @sscanf( $authHeader['Authorization'], 'Bearer %s');
    if (!mysqli_query($link, "DELETE FROM tokens WHERE token = '$jwt'")) {
        throw new Exception("Token inválido", 1);
    }
    mysqli_close($link);
}

function getFiguritas(){
	$datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];

	$link = conectarBD();
	mysqli_set_charset($link, 'utf8mb4_general_ci');
	$sql = "SELECT id, nombre, puesto, descripcion, fechanacimiento, altura, imagen, detalle, idEquipo FROM figuritas";
	$resultado = mysqli_query($link, $sql);
	if($resultado === false){
		print mysqli_error($link);
		outputError(500);
	}
	$ret = [];
	while($fila = mysqli_fetch_assoc($resultado)){
		settype($fila['id'], 'integer');
		settype($fila['idEquipo'], 'integer');
		$ret[] = $fila;
	}
	mysqli_free_result($resultado);
	mysqli_close($link);
	outputJson($ret);
}

function getFiguritasConParametros($id)
{
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];

    settype($id, 'integer'); // setea parámetro
    $link = conectarBD();   // conecta a BBDD
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $sql = "SELECT * FROM figuritas WHERE id=$id"; // la query
    $resultado = mysqli_query($link, $sql); // almacena query en resultado
    if ($resultado === false) { // cheque si el resultado es exactamente iigual a false
        outputError(500);
        die;
    }
    if (mysqli_num_rows($resultado) == 0) { // chequea si no devolvió ninguna fila
        outputError(404);
    }

    $fila = mysqli_fetch_assoc($resultado); // almacena el resultado en variable fila
    settype($fila['id'], 'integer');    // setea el campo id a entero
    settype($fila['idEquipo'], 'integer');  // setea el campo idEquipo a entero
    mysqli_free_result($resultado);     // quita el resultado de la consulta
    mysqli_close($link);        // cierra conexión con BBDD
    outputJson($fila);      // envía a frontend
}
function getFiguritasrandom(){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];

    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $sql = "SELECT * FROM figuritas ORDER BY RAND() LIMIT 1";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false) {
        outputError(500);
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }

    $fila = mysqli_fetch_assoc($resultado);
    settype($fila['id'], 'integer');
    settype($fila['idEquipo'], 'integer');
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($fila);
}


function getFiguritasporusuarios(){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];
    settype($idUsuario, 'integer');

    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $sql = "SELECT fi.*, uf.cantidad AS cantidad FROM figuritas fi INNER JOIN usuariofigurita uf ON fi.id = uf.idFigurita WHERE uf.idUsuario=$idUsuario GROUP BY fi.id ORDER BY fi.id ASC";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        outputError(500);
        die;
    }
    if(mysqli_num_rows($resultado) == 0){
        // puede ocurrir que no tenga figuritas todavía
    }

    $ret = [];
    while($fila = mysqli_fetch_assoc($resultado)){
        settype($fila['id'], 'integer');
        settype($fila['idEquipo'], 'integer');
        settype($fila['cantidad'], 'integer');
        $ret[] = $fila;
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);

}

function getEquipos(){
	$datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];

	$link = conectarBD();
	mysqli_set_charset($link, 'utf8mb4_general_ci');
	$sql = "SELECT id, nombre, descripcion FROM equipos";
	$resultado = mysqli_query($link, $sql);
	if($resultado === false){
		print mysqli_error($link);
		outputError(500);
	}
	$ret = [];
	while($fila = mysqli_fetch_assoc($resultado)){
		settype($fila['id'], 'integer');
		$ret[] = $fila;
	}
	mysqli_free_result($resultado);
	mysqli_close($link);
	outputJson($ret);
}

function getEquiposConParametros($id)
{
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];
    settype($id,'integer');
    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $sql = "SELECT * FROM equipos WHERE id=$id";
    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500);
        die;
    }
    if (mysqli_num_rows($resultado) == 0) {
        outputError(404);
    }
    
    $fila = mysqli_fetch_assoc($resultado);
    settype($fila['id'], 'integer'); // convertir campo id de texto (json) a integer

    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($fila);
}

function getUsuarios(){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];
    settype($id, 'integer');

    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $sql = "SELECT * FROM usuarios";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    $ret = [];
    while($fila = mysqli_fetch_assoc($resultado)){
        settype($fila['id'], 'integer');
        settype($fila['estatus'], 'integer');
        $ret[] = $fila;
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);
}

function postUsuarios()
{
    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $dato = json_decode(file_get_contents('php://input'), true);
    
    $nombre = mysqli_real_escape_string($link, $dato['nombre']);
    $username = mysqli_real_escape_string($link, $dato['username']);
    $email = mysqli_real_escape_string($link, $dato['email']);
    $clave = mysqli_real_escape_string($link, $dato['clave']);
    $date = date("Y-m-d"); // con esto funcionó la fecha

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        outputError(400, 'El e-mail ingresado es incorrecto');
    }

    if ($clave=='') {
        outputError(400, 'La clave no puede estar vacía');
    }
    
    $sql = "SELECT 1 FROM usuarios WHERE email='$email' LIMIT 1";
    $resultado = mysqli_query($link, $sql);
    if (mysqli_num_rows($resultado)==1) {
        outputError(400, 'El e-mail ingresado ya está registrado');
    }

    $sql = "INSERT INTO usuarios (nombre, email, username, password, estatus, fechaRegistro) VALUES ('$nombre','$email', '$username','$clave', 1, '$date')";

    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500);
        die;
    } else {
        $idUsuario = mysqli_insert_id($link);
        $sql = "INSERT INTO usuarioperfil (idUsuario, idPerfil) VALUES ($idUsuario, 3)";
    }
    $resultado2 = mysqli_query($link, $sql);
    if($resultado2 === false){
        outputError(500);
        die;
    }

    $ret = [
        'id' => mysqli_insert_id($link)
    ];
    mysqli_free_result($resultado);
    mysqli_free_result($resultado2);
    mysqli_close($link);
    outputJson($ret, 201);
}

function patchUsuarios(){
    $datosUsuario = requireLogin();
    
    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $dato = json_decode(file_get_contents('php://input'), true);

    $idUsuario = mysqli_real_escape_string($link, $dato['id']);
    settype($idUsuario, 'integer');
    $estatus = mysqli_real_escape_string($link, $dato['estatus']);
    settype($estatus, 'integer');
    $sql = "UPDATE usuarios SET estatus=$estatus WHERE id=$idUsuario";
    $resultado = mysqli_query($link, $sql);
    if(resultado === false){
        outputError(500);
    }
    $ret = [];
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret, 201);

}

function deleteUsuarios($id){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];

    settype($id, 'integer');

    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $sql = "DELETE FROM usuariofigurita WHERE idUsuario=$id";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    $sql = "DELETE FROM usuarioperfil WHERE idUsuario=$id";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    $sql = "DELETE FROM usuarios WHERE id=$id";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson([], 202);

}

function postEquipos(){
	$datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];

    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $dato = json_decode(file_get_contents('php://input'), true);

    $nombre = mysqli_real_escape_string($link, $dato['nombre']);
    $descripcion = mysqli_real_escape_string($link, $dato['descripcion']);
    $sql = "INSERT INTO equipos (nombre, descripcion) VALUES ('$nombre','$descripcion')";

    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500);
        die;
    }

    $ret = [
    	'id' => mysqli_insert_id($link)
    ];
    mysqli_close($link);
    outputJson($ret, 201);
}

function patchEquipos($id){
	$datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];

    $id+= 0;
    $link = conectarBD();

    $dato = json_decode(file_get_contents('php://input'), true);
    $nombre = mysqli_real_escape_string($link, $dato['nombre']);
    $descripcion = mysqli_real_escape_string($link, $dato['descripcion']);
    $sql = "UPDATE equipos SET nombre = '$nombre', descripcion = '$descripcion' WHERE id=$id";

    $resultado = mysqli_query($link, $sql);
    if ($resultado === false) {
        outputError(500);
        die;
    }

    $ret = [];

    mysqli_close($link);
    outputJson($ret, 201);

}

function deleteEquipos($id){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];
    settype($id, 'integer');

    $link = conectarBD();
    $sql = "DELETE equipos, figuritas, usuariofigurita FROM equipos INNER JOIN figuritas ON figuritas.idEquipo = equipos.id INNER JOIN usuariofigurita ON usuariofigurita.idFigurita = figuritas.id WHERE equipos.id=$id";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        outputError(500);
    }
    if(mysqli_affected_rows($link)==0){
        $sql = "DELETE equipos, figuritas FROM equipos INNER JOIN figuritas ON figuritas.idEquipo = equipos.id WHERE equipos.id=$id";
        $resultado = mysqli_query($link, $sql);
        if($resultado === false){
            outputError(500);
        }
        if(mysqli_affected_rows($link)==0){
            $sql = "DELETE FROM equipos WHERE equipos.id=$id";
            $resultado = mysqli_query($link, $sql);
            if($resultado === false){
                print mysqli_error($link);
                outputError(500);
            }
            if(mysqli_affected_rows($link)==0){
                print mysqli_error($link);
                outputError(404);
            }
        }
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson([], 202);
}

function postFiguritas(){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];

    $link = conectarBD();
    $dato = json_decode(file_get_contents('php://input'), true);
    $nombre = mysqli_real_escape_string($link, $dato['nombre']);
    $puesto = mysqli_real_escape_string($link, $dato['puesto']);
    $descripcion = mysqli_real_escape_string($link, $dato['descripcion']);
    $fdn = isset($dato['fechanacimiento']) ? ("'" . mysqli_real_escape_string($link, substr($dato['fechanacimiento'], 0, 10)) . "'") : 'NULL';
    if($fdn != 'NULL') {
        list($anio, $mes, $dia) = explode('-', str_replace("'", "", $fdn));
        if(!checkdate($mes+0, $dia+0, $anio+0)) {
            outputError(400);
        }
    }
    $altura = mysqli_real_escape_string($link, $dato['altura']);
    $imagen = mysqli_real_escape_string($link, $dato['imagen']);
    $detalle = mysqli_real_escape_string($link, $dato['detalle']);
    $idEquipo = mysqli_real_escape_string($link, $dato['idEquipo']);
    settype($idEquipo, 'integer');

    $sql = "INSERT INTO figuritas (nombre, puesto, descripcion, fechanacimiento, altura, imagen, detalle, idEquipo) VALUES ('$nombre', '$puesto', '$descripcion', $fdn, '$altura', '$imagen', '$detalle', $idEquipo)";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    $ret = [
        'id' => mysqli_insert_id($link)
    ];
    mysqli_close($link);
    outputJson($ret, 201);

}

function postFiguritasusuarios(){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];
    settype($idUsuario, 'integer');

    $link = conectarBD();
    $dato = json_decode(file_get_contents('php://input'), true);
    $idFigurita = mysqli_real_escape_string($link, $dato['id']);
    settype($idFigurita, 'integer');
    $cantidad = mysqli_real_escape_string($link, $dato['cantidad']);
    settype($cantidad, 'integer');
    $sql = "SELECT * FROM usuariofigurita WHERE idUsuario = $idUsuario AND idFigurita = $idFigurita";
    $resultado = mysqli_query($link, $sql);
    if($resultado && mysqli_num_rows($resultado)==1){
        $sql = "UPDATE usuariofigurita SET cantidad = cantidad + $cantidad WHERE idUsuario = $idUsuario AND idFigurita = $idFigurita";
        $resultado = mysqli_query($link, $sql);
        if($resultado === false){
            print mysqli_error($link);
            outputError(500);
        }
    } else {

        $sql = "INSERT INTO usuariofigurita (idUsuario, idFigurita, cantidad) VALUES ($idUsuario, $idFigurita, $cantidad)";
        $resultado = mysqli_query($link, $sql);
        if($resultado === false){
            print mysqli_error($link);
            outputError(500);
        }
        $ret = [
            'idUsuario' => $idUsuario, 'idFigurita' => $idFigurita
        ];
    }

    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret, 201);
}
   
function getFiguritasajenas(){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];
    settype($idUsuario, 'integer');

    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $sql = "SELECT fi.*, uf.idUsuario AS idUsuario FROM figuritas fi INNER JOIN usuariofigurita uf ON fi.id = uf.idFigurita WHERE uf.idUsuario <> $idUsuario AND uf.cantidad > 1 AND uf.idFigurita NOT IN (SELECT idFigurita FROM usuariofigurita WHERE idUsuario = $idUsuario)";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    if(mysqli_num_rows($resultado)==0){
        
    }
    $ret = [];
    while($fila = mysqli_fetch_assoc($resultado)){
        settype($fila['id'], 'integer');
        settype($fila['idEquipo'], 'integer');
        settype($fila['idUsuario'], 'integer');
        $ret[] = $fila;
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);    

}

function getFiguritaspropiasConParametros($idOtroUsuario){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];
    settype($idUsuario, 'integer');
    $idOtroUsuario+=0;

    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');

    $sql = "SELECT fi.*, uf.idUsuario AS idUsuario FROM figuritas fi INNER JOIN usuariofigurita uf ON fi.id = uf.idFigurita WHERE uf.idUsuario = $idUsuario and uf.cantidad > 1 and uf.idFigurita NOT IN (SELECT idFigurita FROM usuariofigurita WHERE idUsuario = $idOtroUsuario)";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    if(mysqli_num_rows($resultado)==0){
        
    }
    $ret = [];
    while($fila = mysqli_fetch_assoc($resultado)){
        settype($fila['id'], 'integer');
        settype($fila['idEquipo'], 'integer');
        settype($fila['idUsuario'], 'integer');
        $ret[] = $fila;
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson($ret);

}

function patchCanjefiguritas(){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];
    settype($idUsuario, 'integer');

    $link = conectarBD();
    mysqli_set_charset($link, 'utf8mb4_general_ci');
    $dato = json_decode(file_get_contents('php://input'), true);
    $idFiguPropia = mysqli_real_escape_string($link, $dato['figupropia']);
    settype($idFiguPropia, 'integer');
    $idFiguAjena = mysqli_real_escape_string($link, $dato['figuajena']);
    settype($idFiguAjena, 'integer');
    $idOtroUsuario = mysqli_real_escape_string($link, $dato['idajeno']);
    settype($idOtroUsuario, 'integer');

    $sql = "UPDATE usuariofigurita SET cantidad = cantidad - 1 WHERE idUsuario = $idUsuario AND idFigurita = $idFiguPropia";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    $sql = "UPDATE usuariofigurita SET cantidad = cantidad -1 WHERE idUsuario = $idOtroUsuario AND idFigurita = $idFiguAjena";
    $resultado2 = mysqli_query($link, $sql);
    if($resultado2 === false){
        print mysqli_error($link);
        outputError(500);
    }
    $sql = "INSERT INTO usuariofigurita (idUsuario, idFigurita, cantidad) VALUES ($idUsuario, $idFiguAjena, 1), ($idOtroUsuario, $idFiguPropia, 1)";
    $resultado3 = mysqli_query($link, $sql);
    if($resultado3 === false){
        print mysqli_error($link);
        outputError(500);
    }
    mysqli_free_result($resultado);
    mysqli_free_result($resultado2);
    mysqli_free_result($resultado3);
    mysqli_close($link);
    outputJson(['id' => $idFiguAjena], 201);

}

function patchFiguritas($id){
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];
    settype($id,'integer');

    $link = conectarBD();
    $dato = json_decode(file_get_contents('php://input'), true);
    $nombre = mysqli_real_escape_string($link, $dato['nombre']);
    $puesto = mysqli_real_escape_string($link, $dato['puesto']);
    $descripcion = mysqli_real_escape_string($link, $dato['descripcion']);
    $fdn = isset($dato['fechanacimiento']) ? ("'" . mysqli_real_escape_string($link, substr($dato['fechanacimiento'], 0, 10)) . "'") : 'NULL';
    if($fdn != 'NULL') {
        list($anio, $mes, $dia) = explode('-', str_replace("'", "", $fdn));
        if(!checkdate($mes+0, $dia+0, $anio+0)) {
            outputError(400);
        }
    }
    $altura = mysqli_real_escape_string($link, $dato['altura']);
    $imagen = mysqli_real_escape_string($link, $dato['imagen']);
    $detalle = mysqli_real_escape_string($link, $dato['detalle']);
    $idEquipo = mysqli_real_escape_string($link, $dato['idEquipo']);
    settype($idEquipo, 'integer');

    $sql = "UPDATE figuritas SET nombre = '$nombre', puesto = '$puesto', descripcion = '$descripcion', fechanacimiento = $fdn, altura = '$altura', imagen = '$imagen', detalle = '$detalle', idEquipo = $idEquipo WHERE id=$id";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson(['id' => $id], 201);


}

function deleteFiguritas($id){      // funcionando
    $datosUsuario = requireLogin();
    $idUsuario = $datosUsuario['id'];

    settype($id,'integer');
    $link = conectarBD();
    $sql = "DELETE FROM figuritas WHERE id=$id";
    $resultado = mysqli_query($link, $sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    if(mysqli_affected_rows($link)==0){
        outputError(404);
    }
    $sql = "DELETE FROM usuariofigurita WHERE idFigurita=$id";
    $resultado = mysqli_query($link,$sql);
    if($resultado === false){
        print mysqli_error($link);
        outputError(500);
    }
    mysqli_free_result($resultado);
    mysqli_close($link);
    outputJson([], 202);

}


?>