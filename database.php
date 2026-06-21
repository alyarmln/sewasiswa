<?php

$env = parse_ini_file(__DIR__.'/.env');

if (!$env) {
    die("ENV file not found or empty");
}

$VPS_HOST = $env['VPS_HOST'];
$VPS_USERNAME_DB = $env['VPS_USERNAME_DB'];
$VPS_PASS_DB = $env['VPS_PASS_DB'];
$VPS_NAME_DB = $env['VPS_NAME_DB'];

$conn = mysqli_connect(
    $VPS_HOST,
    $VPS_USERNAME_DB,
    $VPS_PASS_DB,
    $VPS_NAME_DB
);

if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}