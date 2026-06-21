<?php 

#Database Connection , no need to implement in all files
# Means panggil je file ni dalam semua file yang guna DB

#ini test untuk version control serta guna GIT PUSH , PULL


$VPS_HOST = {{secrets.VPS_HOST}}
$VPS_USERNAME_DB = {{secrets.VPS_USERNAME_DB}}
$VPS_PASS_DB = {{secrets.VPS_PASS_DB}}
$VPS_NAME_DB = {{secrets.VPS_NAME_DB}}


$conn = mysqli_connect( $VPS_HOST, $VPS_USERNAME_DB , $VPS_PASS_DB , $VPS_NAME_DB );
