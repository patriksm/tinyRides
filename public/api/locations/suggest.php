<?php
header('Content-Type: application/json; charset=utf-8');

$type = strtolower(trim($_GET['type'] ?? 'city'));
$q = trim($_GET['q'] ?? '');
$city = trim($_GET['city'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// Nominatim (OpenStreetMap) endpoint
function nominatim_get(array $params): array
{
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query($params);

    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: ToyRent/1.0 (amity.muzaffar@gmail.com)\r\n"
        ]
    ];

    $res = @file_get_contents($url, false, stream_context_create($opts));
    if ($res === false) return [];

    $data = json_decode($res, true);
    return is_array($data) ? $data : [];
}

if ($type === 'street') {
    if ($city === '') {
        echo json_encode([]);
        exit;
    }

    $search = $q . ', ' . $city;

    $data = nominatim_get([
        'q' => $search,
        'format' => 'jsonv2',
        'addressdetails' => 1,
        'limit' => 8,
    ]);

    $out = [];

    foreach ($data as $item) {
        $addr = $item['address'] ?? [];

        $road = $addr['road'] ?? ($addr['pedestrian'] ?? '');
        $house = $addr['house_number'] ?? '';
        if ($road === '') continue;

        $street = trim(($house !== '' ? $house . ' ' : '') . $road);

        $labelParts = [];
        $labelParts[] = $street;
        if (!empty($addr['suburb'])) $labelParts[] = $addr['suburb'];
        $labelParts[] = $city;

        $out[] = [
            'street' => $street,
            'label'  => implode(', ', $labelParts),
            'lat'    => $item['lat'] ?? null,
            'lon'    => $item['lon'] ?? null,
        ];
    }

    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

// default: city
$data = nominatim_get([
    'q' => $q,
    'format' => 'jsonv2',
    'addressdetails' => 1,
    'limit' => 8,
]);

$out = [];
foreach ($data as $item) {
    $addr = $item['address'] ?? [];

    $cityName =
        $addr['city'] ??
        $addr['town'] ??
        $addr['village'] ??
        $addr['municipality'] ??
        $addr['county'] ??
        '';

    if ($cityName === '') continue;

    $label = $item['display_name'] ?? $cityName;

    $out[] = [
        'city'  => $cityName,
        'label' => $label,
        'lat'   => $item['lat'] ?? null,
        'lon'   => $item['lon'] ?? null,
    ];
}

$seen = [];
$uniq = [];
foreach ($out as $r) {
    $k = mb_strtolower($r['city']);
    if (isset($seen[$k])) continue;
    $seen[$k] = true;
    $uniq[] = $r;
}

echo json_encode($uniq, JSON_UNESCAPED_UNICODE);
