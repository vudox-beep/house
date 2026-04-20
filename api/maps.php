<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;
if (!$data) $data = $_GET;

$action = $data['action'] ?? '';

if (empty($action)) {
    echo json_encode(['status' => 'error', 'message' => 'Action is required']);
    exit;
}

if ($action === 'get_api_key') {
    // Optionally secure this endpoint if needed
    echo json_encode([
        'status' => 'success',
        'api_key' => GOOGLE_MAPS_API_KEY
    ]);
    exit;
}

if ($action === 'geocode') {
    // Convert Lat/Lng to Address (Reverse Geocoding)
    $lat = $data['lat'] ?? '';
    $lng = $data['lng'] ?? '';

    if (empty($lat) || empty($lng)) {
        echo json_encode(['status' => 'error', 'message' => 'Latitude and Longitude are required']);
        exit;
    }

    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lng}&key=" . GOOGLE_MAPS_API_KEY . "&result_type=street_address|premise|route|intersection|political|neighborhood";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Add SSL fix for local testing or strict servers
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(['status' => 'error', 'message' => 'cURL error: ' . $curl_error]);
        exit;
    }

    $json = json_decode($response, true);

    if (isset($json['status']) && $json['status'] === 'OK') {
        echo json_encode([
            'status' => 'success',
            'address' => $json['results'][0]['formatted_address'],
            'raw_data' => $json['results'][0]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to geocode location', 'google_status' => $json['status'] ?? 'UNKNOWN', 'raw' => $json]);
    }
    exit;
}

if ($action === 'autocomplete') {
    // Autocomplete text search for places
    $input = $data['input'] ?? '';
    $country = $data['country'] ?? 'zm'; // Default to Zambia

    if (empty($input)) {
        echo json_encode(['status' => 'error', 'message' => 'Search input is required']);
        exit;
    }

    $input = urlencode($input);
    $url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input={$input}&components=country:{$country}&key=" . GOOGLE_MAPS_API_KEY;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(['status' => 'error', 'message' => 'cURL error: ' . $curl_error]);
        exit;
    }

    $json = json_decode($response, true);

    if (isset($json['status']) && $json['status'] === 'OK') {
        $places = [];
        foreach ($json['predictions'] as $pred) {
            $places[] = [
                'place_id' => $pred['place_id'],
                'description' => $pred['description']
            ];
        }
        echo json_encode(['status' => 'success', 'predictions' => $places]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch predictions', 'google_status' => $json['status'] ?? 'UNKNOWN', 'raw' => $json]);
    }
    exit;
}

if ($action === 'place_details') {
    // Get Lat/Lng from a specific Place ID
    $place_id = $data['place_id'] ?? '';

    if (empty($place_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Place ID is required']);
        exit;
    }

    $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&fields=geometry,formatted_address&key=" . GOOGLE_MAPS_API_KEY;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(['status' => 'error', 'message' => 'cURL error: ' . $curl_error]);
        exit;
    }

    $json = json_decode($response, true);

    if (isset($json['status']) && $json['status'] === 'OK') {
        $location = $json['result']['geometry']['location'];
        echo json_encode([
            'status' => 'success',
            'address' => $json['result']['formatted_address'],
            'lat' => $location['lat'],
            'lng' => $location['lng']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch place details', 'google_status' => $json['status'] ?? 'UNKNOWN', 'raw' => $json]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
