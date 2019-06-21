<?php

$actions = (object) [
  'items' => [],
];

$body = file_get_contents("php://input");
$bodyBytes = strlen($body);
$actions->items []= (object) [
  'label' => "Show Request Body",
  'detail' => "{$bodyBytes} bytes",
  'file' => echoRequest($body),
];

$more = [];
$more['Method'] = $_SERVER['REQUEST_METHOD'];

$menu = (object) [
  'title' => 'Info',
  'groups' => [
    arrayToGroup($more),
    $actions,
    arrayToGroup(getallheaders(), 'Headers'),
  ],
];

function arrayToGroup($arr, $header = null) {
  foreach ($arr as $k => $v) {
    $items []= (object) [
      'label' => $k,
      'detail' => $v
    ];
  }
  return (object) [
    "header" => $header,
    "items" => $items,
  ];
}

function echoRequest($content) {
  $request = (object) ['url' => 'echo.php'];
  $request->body = (object) ['echo' => $content];
  return $request;
}

function requestObj($url) {
  return (object) ['request' => (object) ['url' => $url]];
}

echo json_encode($menu, JSON_PRETTY_PRINT);
