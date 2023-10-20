<?php

// DB
include_once('conf.inc');
include_once('function.inc');

session_start();

debugc("loaded proc.php");

switch($_GET['proc']){
case "storage":
    $zone=$_GET['zone'];
    $name=$_GET['name'];
    if(!$name){
        $_SESSION['query_error']="ERROR: where is your storage space name";
        break;
    }
    q("INSERT INTO storage(str_name) VALUES('$name')");
    $lid=q("select seq from sqlite_sequence where name = 'storage'");
    $id=($lid[0]['seq']);
    if($zone){
        debugc('in zone.');
        $zone_a = explode(',',$zone);
        foreach($zone_a as $value){
            $zz = explode(':',$value);
            if(count($zz) > 1){
                for($i=0;$i<=$zz[1];$i++){
                    $label="$name,$zz[0]-$i";
                    q("INSERT INTO zone(zone_name,storage_id) VALUES('$label',$id)");
                    $zone_added=1;
                }
            }elseif (preg_match('/^\d+$/',$zz[0])) {
                for($i=0;$i<=$zz[0];$i++){
                    $label="$name,$i";
                    q("INSERT INTO zone(zone_name,storage_id) VALUES('$label',$id)");
                    $zone_added=1;
                }
            }
        }
    }else{
        debugc('add none zone.');
        q("INSERT INTO zone(zone_name,storage_id) VALUES('none',$id)");
    }
    if(!$_SESSION['query_error']){
        $_SESSION['query_error']="successfully added storage space";
    }
    break;

case "del_record":
    $item=$_GET['item'];
    q("delete from items where item_id=$item");
    if(!$_SESSION['query_error']){
        $_SESSION['query_error']="delete $item";
    }
    break;
case "record":
    $product=strtolower($_GET['item']);
    $count=$_GET['count'];
    $source=$_GET['source'];
    $expire=$_GET['expire'];

    if( "" == trim($expire)){
        $expire = $def_expire;
    }

    if(!$product){
        $_SESSION['query_error']="ERROR: where is your product name";
        break;
    }
    $q=q("select p_id from product where product='$product'");
    $name_id=$q[0]['p_id'];
    if(!$name_id){
        //NOTE: product table should NOT maintain count
        q("INSERT INTO product(product,count) VALUES('$product','$count')");
        $seq=q("select seq from sqlite_sequence where name = 'product'");
        $name_id=($seq[0]['seq']);
    }else{
        q("update product set count = count + $count where p_id = $name_id;");
    }
    debugc("step in record.");
    q("INSERT INTO items(item_name,expire,count) VALUES('$name_id','$expire','$count')");
    if(!$_SESSION['query_error']){
        $_SESSION['query_error']="successfully added item/product";
    }
    break;
case "update":
    $product=strtolower($_GET['product']);
    $id=$_GET['p_id'];
    $item=$_GET['item'];
    $link=$_GET['link'];
    $unlink=$_GET['unlink'];
    $count=$_GET['count'];
    debugc("step in proc for update");
    debugc("data: $id - $link - $unlink - $count");
    q("update product set product='$product' where p_id=$id limit 1");
    if($_GET['unlink']){
        q("delete from links where item=$unlink");
    }
    if($count){
        q("update links set count=$count where link_id=$link limit 1");
        q("update items set count=$count where item_id=$item limit 1");
    }
    break;
case "link":
    $item=$_GET['item'];
    $storage=$_GET['storage'];
    $zone=$_GET['zone'];
    $count=$_GET['count'];
    $expire=$_GET['expire'];
    $detail=$_GET['detail'];
    $tag=$_GET['tag'];


    if( "" == trim($expire)){
        $expire = $def_expire;
    }

    //
    if(!$zone){
        debugc("add none for storage_id $storage");
        $lid=q("select zone_id from zone where storage_id=$storage");
        $zone=($lid[0]['zone_id']);
    }
    q("INSERT INTO links(item,storage,zone,expire,detail,count) VALUES('$item','$storage','$zone','$expire','$detail','$count')");
    if(!$_SESSION['query_error']){
        $_SESSION['query_error']="successfully link item/product to storage";
    }
    break;

default:
    $search=$_GET['search'];
    if($search){
        $_SESSION['search']="$search";
    }
    debugc("stuck in default:".$_GET['proc']);
    break;
}
//);
//$db->close();
header('Location: ' . $_SERVER['HTTP_REFERER']);
?>
