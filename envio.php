
<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

ini_set('display_errors', 1);
// Ingresa tu clave de API de Google Maps
$API_KEY = 'AIzaSyCOy3KVVLCFYACs6UQMAUpNDuiGHmrnqxY';

$codigo_postal_origen = '25740';
$codigo_postal_destino = '25790';

$costo =22500;

$precio_milla=2.2;


// Construye la URL de la solicitud a la API de Google Maps Geocoding
$url_origen = "https://maps.googleapis.com/maps/api/geocode/json?address={$codigo_postal_origen}&key={$API_KEY}";
$url_destino = "https://maps.googleapis.com/maps/api/geocode/json?address={$codigo_postal_destino}&key={$API_KEY}";

// Realiza la solicitud a la API para el código postal de origen
$response_origen = file_get_contents($url_origen);
$data_origen = json_decode($response_origen, true);

// Realiza la solicitud a la API para el código postal de destino
$response_destino = file_get_contents($url_destino);
$data_destino = json_decode($response_destino, true);

// Verifica si las solicitudes fueron exitosas
if ($data_origen['status'] == 'OK' && $data_destino['status'] == 'OK') {
    // Obtiene los nombres de los códigos postales de origen y destino
    $nombre_origen = $data_origen['results'][0]['formatted_address'];
    $nombre_destino = $data_destino['results'][0]['formatted_address'];

    // Construye la URL de la solicitud a la API de Google Maps Directions
    $url_directions = "https://maps.googleapis.com/maps/api/directions/json?origin=" . urlencode($nombre_origen) . "&destination=" . urlencode($nombre_destino) . "&mode=driving&key={$API_KEY}";

    // Realiza la solicitud a la API de Google Maps Directions
    $response_directions = file_get_contents($url_directions);
    $data_directions = json_decode($response_directions, true);
  
    // Verifica si la solicitud fue exitosa
    if ($data_directions['status'] == 'OK') {
        // Extrae la distancia en metros del resultado
        $distancia_metros = $data_directions['routes'][0]['legs'][0]['distance']['value'];

        // Convierte la distancia de metros a kilómetros
        $distancia_km = $distancia_metros / 1000;

        // Extrae el tiempo en segundos del resultado
        $tiempo_segundos = $data_directions['routes'][0]['legs'][0]['duration']['value'];

        // Convierte el tiempo de segundos a minutos
        $tiempo_minutos = $tiempo_segundos / 60;
        $precio_total = $costo+$distancia_km*$precio_milla;

        $coordenadas_origen = $data_origen['results'][0]['geometry']['location'];
        $coordenadas_destino = $data_destino['results'][0]['geometry']['location'];
    
        // Construye la URL de la solicitud a la API de Google Maps Directions
        $url_directions = "https://maps.googleapis.com/maps/api/directions/json?origin={$coordenadas_origen['lat']},{$coordenadas_origen['lng']}&destination={$coordenadas_destino['lat']},{$coordenadas_destino['lng']}&mode=driving&key={$API_KEY}";
    
    
        // Construye el JSON con la información solicitada
        $json_data = array(
            'origen' => $nombre_origen,
            'destino' => $nombre_destino,
            'distancia_km' => $distancia_km,
            'tiempo_minutos' => $tiempo_minutos,
            'precio_total' => $precio_total,
            'coordenadas_origen' => $coordenadas_origen,
            'coordenadas_destino' => $coordenadas_destino
        );

        // Establece el encabezado del contenido como JSON
        header('Content-Type: application/json');

        // Imprime el JSON resultante
        echo json_encode($json_data);
    } else {
        // Construye el JSON con el mensaje de error
        $json_error = array(
            'error' => 'No se pudo calcular la distancia en carro.',
            'status' => $data_directions['status']
        );

        // Establece el encabezado del contenido como JSON
        header('Content-Type: application/json');

        // Imprime el JSON de error
        echo json_encode($json_error);
    }
} else {
    // Construye el JSON con el mensaje de error
    $json_error = array(
        'error' => 'No se encontraron resultados para los códigos postales proporcionados.',
        'status_origen' => $data_origen['status'],
        'status_destino' => $data_destino['status']
    );

    // Establece el encabezado del contenido como JSON
    header('Content-Type: application/json');

    // Imprime el JSON de error
    echo json_encode($json_error);
}
?>

