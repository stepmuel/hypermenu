<?php

$token = '106842b48d349a7f';
$basePath = '..';
$title = 'File System';

// {"request": {"url": "http://laptop.local/menu/fs.php", "stickyHeaders": {"Authorization": "Bearer 106842b48d349a7f"}}}

$headers = apache_request_headers();
// if ($headers['Authorization'] !== "Bearer {$token}") exit('unauthorized');

$path = isset($_GET['p']) ? $_GET['p'] : '';
if (strpos($path, '..') !== false) exit('error');

$fullPath = "{$basePath}{$path}";

if (is_file($fullPath)) {
  header('Content-Type: ' . mime_content_type($fullPath));
  header('Content-Disposition: filename="'.basename($fullPath).'"');
  readfile($fullPath);
  exit;
}

$folderGroup = (object) [
  "items" => [],
];

$fileGroup = (object) [
  "items" => [],
];

$menu = (object) [
  'groups' => [],
];

if ($path === '') {
  $menu->title = $title;
}

$invisible = isset($_GET['i']) && $_GET['i'] === '1';

foreach (scandir($fullPath) as $p) {
  if (!$invisible && substr($p, 0, 1) === '.') continue;
  if ($p === '.' || $p === '..') continue;
  $itemPath = "{$path}/{$p}";
  if (is_dir("{$basePath}{$itemPath}")) {
    $folderGroup->items []= (object) [
      'label' => basename($p),
      'menu' => requestObj(menuUrl($itemPath, $invisible)),
    ];
  } else {
    $fileGroup->items []= (object) [
      'label' => basename($p),
      'file' => (object) ['url' => "fs.php?p=" . urlencode($itemPath)],
    ];
  }
}

if (count($folderGroup->items) > 0) {
  $menu->groups []= $folderGroup;
}
if (count($fileGroup->items) > 0) {
  $menu->groups []= $fileGroup;
}

$showItem = (object) ['label' => "Show Invisible Files", 'action' => replaceActionObj(menuUrl($path, true))];
$hideItem = (object) ['label' => "Hide invisible Files", 'action' => replaceActionObj(menuUrl($path, false))];
$menu->groups []= (object) [
  'items' => [$invisible ? $hideItem : $showItem]
];

function requestObj($url) {
  return (object) ['request' => (object) ['url' => $url]];
}

function replaceActionObj($url) {
  return (object) ['replace' => requestObj($url)];
}

function menuUrl($path, $showInvisible = false) {
  $params = $showInvisible ? '&i=1' : '';
  return "fs.php?p=" . urlencode($path) . $params;
}

echo json_encode($menu);
