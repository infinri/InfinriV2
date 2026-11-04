<?php

$infinri->response()->code(202);
$infinri->response()->header('Cache-Control', 'private');
$infinri->response()->type('text/plain');

echo 'This is a test: ' . uniqid();
