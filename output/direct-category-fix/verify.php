<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['DOCUMENT_ROOT']='C:/xampp/htdocs';
$_SERVER['REQUEST_METHOD']='POST';
@mkdir(__DIR__.'/sessions');
session_save_path(__DIR__.'/sessions');
require __DIR__.'/../../xadmin/config/db.php';
require __DIR__.'/../../xadmin/includes/catalogue_product_hierarchy.php';
$_SESSION['admin_id']=1; $_SESSION['admin_username']='test';
$pdo=getDBConnection();
function verify($ok,$message){if(!$ok)throw new RuntimeException($message);}
// Each table shadows the real table only for this connection. No catalog rows are changed.
foreach(['catalogue_products','catalogue_categories','catalogue_sub_categories','catalogue_series','catalogue_product_images','catalogue_product_variants','catalogue_product_features','settings'] as $table){$ddl=$pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];$lines=explode("\n",$ddl);$lines=array_filter($lines,function($line){return strpos(ltrim($line),'CONSTRAINT ')!==0;});$ddl=implode("\n",$lines);$ddl=preg_replace('/,\n\)/',"\n)",$ddl);$ddl=preg_replace('/^CREATE TABLE/','CREATE TEMPORARY TABLE',$ddl);$pdo->exec($ddl);}
$pdo->exec("INSERT INTO catalogue_categories(id,name) VALUES(901,'HEALTH FAUCETS'),(902,'FAUCETS')");
$pdo->exec("INSERT INTO catalogue_series(id,category_id,name) VALUES(911,901,'HEALTH FAUCETS'),(912,902,'REAL SERIES')");
$pdo->exec("INSERT INTO catalogue_sub_categories(id,category_id,name) VALUES(921,902,'SUB')");
$cols=$pdo->query('SHOW COLUMNS FROM catalogue_products')->fetchAll(PDO::FETCH_COLUMN);
foreach(['category_id','sub_category_id'] as $col) if(in_array($col,$cols)) $pdo->exec("ALTER TABLE catalogue_products DROP COLUMN $col");
$pdo->exec('ALTER TABLE catalogue_products MODIFY series_id INT NOT NULL');
$pdo->exec("INSERT INTO catalogue_products(id,series_id,name,code,price,variant_type) VALUES(1,911,'POLO HEALTH FAUCET','HF-270022',1499,'none')");
$pdo->exec("INSERT INTO catalogue_product_images(product_id,image_url,is_primary) VALUES(1,'preserved.jpg',1)");
$mode=$argv[1]??'repair';
if($mode==='repair'){
 ensureCatalogueProductHierarchySchema($pdo); ensureCatalogueProductHierarchySchema($pdo);
 $cols=array_column($pdo->query('SHOW COLUMNS FROM catalogue_products')->fetchAll(),'Null','Field');
 verify(isset($cols['category_id'],$cols['sub_category_id'])&&$cols['series_id']==='YES','Migration failed');
 $before=$pdo->query('SELECT * FROM catalogue_products WHERE id=1')->fetch();
 $backup=null;$pdo->beginTransaction();
 $n=moveCataloguePlaceholderToCategory($pdo,911,function($data)use(&$backup){$backup=$data;});$pdo->commit();
 $after=$pdo->query('SELECT * FROM catalogue_products WHERE id=1')->fetch();
 verify($n===1&&$after['series_id']===null&&(int)$after['category_id']===901,'Repair did not create a true direct product');
 foreach(['id','name','code','price','specifications']as$key)verify($before[$key]===$after[$key],'Changed '.$key);
 verify((int)$pdo->query('SELECT COUNT(*) FROM catalogue_series WHERE id=911')->fetchColumn()===0,'Placeholder not removed');
 verify((int)$pdo->query('SELECT COUNT(*) FROM catalogue_series WHERE id=912')->fetchColumn()===1,'Real series changed');
 verify($pdo->query('SELECT image_url FROM catalogue_product_images WHERE product_id=1')->fetchColumn()==='preserved.jpg','Image lost');
 verify($backup['series']['id']==911&&count($backup['products'])===1,'Backup missing');
 [$cat,$sub,$series]=resolveCatalogueProductClassification($pdo,901,921,912);
 verify($cat===902&&$sub===null&&$series===912,'Series ownership incorrect');
 try{resolveCatalogueProductClassification($pdo,901,921,null);throw new RuntimeException('Invalid sub-category accepted');}catch(InvalidArgumentException $expected){}
 try{resolveCatalogueProductClassification($pdo,901,null,99999);throw new RuntimeException('Invalid series accepted');}catch(InvalidArgumentException $expected){}
 $pdo->exec("INSERT INTO catalogue_series(id,category_id,name) VALUES(913,901,'HEALTH FAUCETS')");
 $pdo->exec("UPDATE catalogue_products SET series_id=913 WHERE id=1");
 $pdo->beginTransaction();try{moveCataloguePlaceholderToCategory($pdo,913,function(){throw new RuntimeException('Backup failed');});throw new RuntimeException('Missing abort');}catch(RuntimeException $e){verify($e->getMessage()==='Backup failed','Wrong error');$pdo->rollBack();}
 verify((int)$pdo->query('SELECT series_id FROM catalogue_products WHERE id=1')->fetchColumn()===913,'Backup failure changed product');
 $pdo->exec("INSERT INTO catalogue_products(series_id,category_id,name,code) VALUES(NULL,901,'Duplicate','HF-270022')");
 $pdo->beginTransaction();try{moveCataloguePlaceholderToCategory($pdo,913,function(){});throw new RuntimeException('Duplicate accepted');}catch(RuntimeException $e){verify(strpos($e->getMessage(),'Duplicate product codes')===0,'Duplicate validation missing');$pdo->rollBack();}
 session_destroy();echo "PASS: legacy schema upgrade, idempotency, real direct storage, placeholder removal, backup, data preservation, invalid hierarchy, duplicate protection and rollback\n";exit;
}
$_POST=['category_id'=>901,'sub_category_id'=>'','series_id'=>'','name'=>'POLO HEALTH FAUCET','code'=>'HF-270022','price'=>'1499','display_order'=>'1','variant_type'=>'none','is_active'=>'1'];
if($mode==='create')$_POST['code']='HF-NEW';
if($mode==='edit')$_POST['id']=1;
if($mode==='duplicate'){
 ensureCatalogueProductHierarchySchema($pdo);
 $pdo->exec("UPDATE catalogue_products SET category_id=901,series_id=NULL WHERE id=1");
}
register_shutdown_function(function()use($pdo,$mode){
 try{
  if($mode==='duplicate'){verify(strpos($_SESSION['error']??'','already exists')!==false,'Duplicate code not rejected');verify((int)$pdo->query('SELECT COUNT(*) FROM catalogue_products')->fetchColumn()===1,'Duplicate inserted');}
  else{
   verify(!empty($_SESSION['success']), 'Save failed: '.($_SESSION['error']??'unknown'));
   $code=$mode==='create'?'HF-NEW':'HF-270022';$q=$pdo->prepare('SELECT * FROM catalogue_products WHERE code=?');$q->execute([$code]);$p=$q->fetch();
   verify($p&&$p['series_id']===null&&(int)$p['category_id']===901,'Save is not direct');
   verify((int)$pdo->query('SELECT COUNT(*) FROM catalogue_series')->fetchColumn()===2,'Save created a series');
   verify($pdo->query('SELECT image_url FROM catalogue_product_images WHERE product_id=1')->fetchColumn()==='preserved.jpg','Save lost image');
  }
  session_destroy();echo "PASS: actual $mode handler on legacy schema\n";
 }catch(Throwable $e){session_destroy();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
});
include __DIR__.'/../../xadmin/modules/catalogue/products/'.($mode==='edit'?'edit_process':'create_process').'.php';
