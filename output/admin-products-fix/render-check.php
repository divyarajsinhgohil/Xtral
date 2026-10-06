<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['DOCUMENT_ROOT']='C:/xampp/htdocs';
session_save_path(__DIR__.'/sessions');
require __DIR__.'/../../xadmin/config/db.php';
$_SESSION['admin_id']=1;
$_SESSION['admin_username']='local-verification';
$_SESSION['admin_name']='Local Verification';
$page=($argv[1]??'list')==='edit'?'edit':'list';
if($page==='edit') $_GET['id']=(int)($argv[2]??664);
session_destroy();
ob_start();
include __DIR__.'/../../xadmin/modules/catalogue/products/'.$page.'.php';
$html=ob_get_clean();
if(strpos($html,$page==='edit'?'Edit Product':'Catalogue Products')===false) throw new RuntimeException('Page did not render');
if(preg_match('/Fatal error|Warning:|Uncaught /',$html)) throw new RuntimeException('PHP error in rendered page');
echo 'PASS: Full '.$page.' page rendered ('.strlen($html).' bytes)'.PHP_EOL;
