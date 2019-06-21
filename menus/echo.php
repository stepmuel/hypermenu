<?php

$body = file_get_contents("php://input");
$body = json_decode($body);
echo $body->echo;
