<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$_SERVER['SERVER_NAME']='localhost';$_SERVER['HTTP_HOST']='localhost';$_SERVER['DOCUMENT_ROOT']='C:/xampp/htdocs';
session_save_path(__DIR__.'/sessions');
require __DIR__.'/../../xadmin/config/db.php';
$_SESSION['admin_id']=1;$_SESSION['admin_username']='verification';
session_destroy();
if(($argv[1]??'')==='export'){
 $_SERVER['REQUEST_METHOD']='POST';$_POST=['category_id'=>'14','product_type'=>'simple'];
 include __DIR__.'/../../xadmin/modules/catalogue/tools/export_process.php';
}else{
 $_SERVER['REQUEST_METHOD']='GET';$_GET=['category_id'=>'14'];
 include __DIR__.'/../../xadmin/api/webapi/products.php';
}
