<?php
// phpmyadmin-backup-script
// Backup your MySQL database if you only have access to phpMyAdmin
// Tested on phpMyAdmin 3.4.3.1
@set_time_limit(0);
const ADMIN_URL='https://example.com/phpMyAdmin3/';
const USERNAME='';
const PASSWORD='';
const BACKUPS_DIR = '~/';
const FILENAME_PREFIX = 'backup';
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, ADMIN_URL);

$result = curl_exec ($ch);
if ( !preg_match('/\"token\" value=\"(.+?)\"/i', $result, $matches)) {
    die("Token not found");
}

$token =  $matches[1];
preg_match('/\"phpMyAdmin\" value=\"(.+?)\"/i', $result, $matches);
$phpMyAdmin = $matches[1];

$post = array(
    'pma_username'=> USERNAME,
    'pma_password' => PASSWORD,
    'lang' => 'ru',
    'server' => 1,
    'token' => $token,
);

$tmpfname = dirname(__FILE__).'/cookie.txt';
curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfname);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec ($ch);
if ( !preg_match('/token=(.+?)\'/i', $result, $matches)) {
    die("Token not found" . $result);
}

$token = $matches[1];

$post = array(
    'token' => $token,
    'export_type' => 'server',
    'what' => 'sql',
    'sql_structure_or_data' => 'structure_and_data',
    'export_method' => 'quick',
    'quick_or_custom' => 'quick',
    'output_format' => 'sendit',
    'filename_template' => '@SERVER@',
    'remember_template' =>'on',
    'charset_of_file' => 'utf-8',
    'compression' => 'none',
    'codegen_format' => '0',
);
curl_setopt($ch, CURLOPT_URL, ADMIN_URL .'export.php');
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query ($post));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec ($ch);

$outputFileName = BACKUPS_DIR.FILENAME_PREFIX.'_'date('Y_m_d').'_time_'.date('H_i_s').'.sql';
file_put_contents($outputFileName, $result);

