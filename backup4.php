<?php
@set_time_limit(0);
const ADMIN_URL='https://supportindeed.com/phpMyAdmin4/';
const USERNAME='';
const DB_NAME='';
const PASSWORD='';
const BACKUPS_DIR = 'd:/backups/';

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_URL, ADMIN_URL);

$result = curl_exec ($ch);
if ( !preg_match('/\"token\" value=\"([a-f0-9]+)/i', $result, $matches)) {
    die("Token not found");
}

$token =  $matches[1];
if (strpos($result, 'login_form') === FALSE){
    die("login_form not found");
}

$post = array(
    'pma_username'=> USERNAME,
    'pma_password' => PASSWORD,
    'lang' => 'en',
    'server' => 1,
    'token' => $token
);

$tmpfname = dirname(__FILE__).'/cookie.txt';
//curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfname);
curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfname);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec ($ch);
if ( !preg_match('/token=([a-f0-9]+)\'/i', $result, $matches)) {
    die("Token not found" . $result);
}

$token = $matches[1];

$post = array(
    'token' => $token,
    'export_type' => 'server',
    'export_method' => 'quick',
    'what' => 'sql',
    'quick_or_custom' => 'quick',
    'template_id' => '',
    'output_format' => 'sendit',
    'filename_template' => '@SERVER@',
    'remember_template' =>'on',
    'charset_of_file' => 'utf-8',
    'charset' => 'utf-8',
    'compression' => 'none',
    'db_select[]' => DB_NAME,
    'codegen_format' => '0',
    'maxsize' => '',
    'codegen_structure_or_data' => 'data',
    'sql_include_comments' => 'something',
    'sql_header_comment' => '',
    'sql_compatibility' => 'NONE',
    'sql_structure_or_data' => 'structure_and_data',
    'sql_create_table' => 'something',
    'sql_auto_increment' => 'something',
    'sql_create_view' => 'something',
    'sql_procedure_function' => 'something',
    'sql_create_trigger' => 'something',
    'sql_backquotes' => 'something',
    'sql_type' => 'INSERT',
    'sql_insert_syntax' => 'both',
    'sql_max_query_size' => '50000',
    'sql_hex_for_binary' => 'something',
    'sql_utc_time' => 'something',
);
curl_setopt($ch, CURLOPT_URL, ADMIN_URL .'export.php');
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query ($post));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec ($ch);
if (strpos($result, '-- phpMyAdmin SQL Dump') === FALSE) {
    echo $result;
    die("It seems to be that backup process failed");
}

$outputFileName = BACKUPS_DIR.'zenden_ws_backup_'.date('Y_m_d').'_time_'.date('H_i_s').'.sql';
file_put_contents($outputFileName, $result);