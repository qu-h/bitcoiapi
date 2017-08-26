<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['url_suffix'] = '.html';
// $config['theme_url'] = "//".$_SERVER['HTTP_HOST']."/themes/";
$config['language']	= 'vietnamese';
$config['enable_hooks'] = true;
$config['csrf_protection'] = FALSE;
$config['csrf_token_name'] = 'csrf_test_name';
$config['csrf_cookie_name'] = 'csrf_cookie_name';
$config['csrf_expire'] = 7200;
$config['csrf_regenerate'] = TRUE;
$config['csrf_exclude_uris'] = array();

$config['subclass_prefix'] = 'REST_';

