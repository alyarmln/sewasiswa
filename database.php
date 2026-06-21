<?php

// $VPS_HOST = getenv('VPS_HOST');
// $VPS_USERNAME_DB = getenv('VPS_USERNAME_DB');
// $VPS_PASS_DB = getenv('VPS_PASS_DB');
// $VPS_NAME_DB = getenv('VPS_NAME_DB');

// // $API_KEY = getenv('GERMINI_API');

// $conn = mysqli_connect(
//     $VPS_HOST,
//     $VPS_USERNAME_DB,
//     $VPS_PASS_DB,
//     $VPS_NAME_DB
// );

$VPS_HOST = $_ENV['VPS_HOST'] ?? getenv('VPS_HOST');
$VPS_USERNAME_DB = $_ENV['VPS_USERNAME_DB'] ?? getenv('VPS_USERNAME_DB');
$VPS_PASS_DB = $_ENV['VPS_PASS_DB'] ?? getenv('VPS_PASS_DB');
$VPS_NAME_DB = $_ENV['VPS_NAME_DB'] ?? getenv('VPS_NAME_DB');

$conn = mysqli_connect(
    $VPS_HOST,
    $VPS_USERNAME_DB,
    $VPS_PASS_DB,
    $VPS_NAME_DB
);

if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}