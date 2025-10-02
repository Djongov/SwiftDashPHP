<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3002');
echo json_encode(['test' => 'working', 'time' => date('Y-m-d H:i:s')]);