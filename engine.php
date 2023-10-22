<?php
include_once('conf.inc');
include_once('function.inc');
session_start();
ini_set(' session.save_path','/dev/shm');
$_SESSION['time']     = time();
//
// set the default timezone to use.
date_default_timezone_set('UTC');
// Prints something like: Monday 8th of August 2005 03:12:46 PM
$now=date('l jS \of F Y h:i:s A');
$ww=date('WY');

//$dbf="db/home.sqlite";
//NOTE: backup sql
if(!file_exists($dbf)){
    include_once('init_sqlite.php');
}else{
    $db = new SQLite3($dbf);
}
$dbm = new SQLite3(':memory:');
$backup = new SQLite3("$dbf$ww");
$db->backup($backup);
$backup->close();
$db->close();

$html = file_get_contents('base.html');

if($_SESSION['query_error']){
    $report=$_SESSION['query_error'];
    unset($_SESSION['query_error']);
}

if($_GET['subproc']){
    $html=str_replace("%subproc%",$_GET['subproc'],$html);
}

switch($_GET['subproc']){

case "link":
    $body= file_get_contents('html/link.html');
    $lid=q("select item_id,product.product as item_name from items join product on product.p_id = items.item_name");
    $item_list=("<select name=item>");
    foreach($lid as $row){
        //NOTE: fix this silly
        $fix_id=$row['item_id'];
        $fix=q("select items.count as total,(select sum(links.count) from links where item=$fix_id) as alloc  from items where item_id=$fix_id;");
        if($fix[0]['total']>$fix[0]['alloc'] || !$fix[0]['alloc']){
            $item_list.="<option value=$row[item_id]>$row[item_name]</option>";
        }
    }
    $item_list.="</select>";

    $lid=q("select str_id,str_name from storage");
    $location=("<select name=storage onchange=\"javascript: dynamicdropdown(this.options[this.selectedIndex].value)\">");
        $location.="<option>selection storage</option>";
    foreach($lid as $row){
        $location.="<option value=$row[str_id]>$row[str_name]</option>";
    }
    $location.="</select>";

    $zone='
                        <script type="text/javascript" language="JavaScript">
                                document.write(\'<select name="zone" id="zone"><option value="">Select status</option></select>\')
                        </script>
                        <noscript>
                                <select id="zone" name="zone">
                                        <option value="open">OPEN</option>
                                        <option value="delivered">DELIVERED</option>
                                </select>
                        </noscript>
';

    // dynamic selections
    //
    $lid=q("select storage_id as sid,zone_id as zid,str_name as storage,zone_name as zone from storage join zone on storage_id = str_id");
    $arr=array();
    $dyselect="";
    foreach($lid as $row){
        $arr[$row['sid']][$row['zid']]=$row['zone'];
    }
    foreach($arr as $key => $row){
        $dyselect.='case "'.$key.'" :';
        $c=0;
        foreach($row as $skey => $ar){
            $dyselect.='document.getElementById("zone").options['.$c.']=new Option("'.$ar.'","'.$skey.'");';
            $c=++$c;
        }
        $dyselect.='break;';
    }

    $body=str_replace("%item_list%",$item_list,$body);
    $body=str_replace("%location%",$location,$body);
    $body=str_replace("%zone%",$zone,$body);
    $html=str_replace("%action%","proc.php",$html);
    $html=str_replace("%dselect%","$dyselect",$html);
    $html=str_replace("%method%","GET",$html);
    break;

case "record":
    $body= file_get_contents('html/record.html');
    $html=str_replace("%action%","proc.php",$html);
    $html=str_replace("%method%","GET",$html);
    break;

case "storage":
    $body= file_get_contents('html/storage.html');
    $html=str_replace("%action%","proc.php",$html);
    $html=str_replace("%method%","GET",$html);
    break;
case "update":
    $id=$_GET['item'];
    $link_id=$_GET['link'];
    $c=q("select p_id,product from items join product on items.item_name = product.p_id where item_id=$id");
    $product=$c[0]['product'];
    $pid=$c[0]['p_id'];
    $body= file_get_contents('html/update.html');
    $body=str_replace("%p_id%","$pid",$body);
    $body=str_replace("%link_id%","$link_id",$body);
    $body=str_replace("%item_id%","$id",$body);
    $body=str_replace("%product%","$product",$body);
    $html=str_replace("%action%","proc.php",$html);
    $html=str_replace("%method%","GET",$html);
    // $html=str_replace("home","update",$html);
    break;
default:
    $html=str_replace("%action%","proc.php",$html);
    $html=str_replace("%method%","GET",$html);
    $id=$_GET['item'];
    $link_id=$_GET['link'];
    if($_GET['proc'] == "del_record")
    {
        // check count before delete
        $c=q("select count from items where item_id=".$id);
        $count=$c[0]['count'];
        if($count > 1){
            q("update items set count = count - 1 where item_id = $id limit 1;");
            q("update links set count = count - 1 where item = $id and link_id = $link_id limit 1;");
        }else{
            q("delete from items where item_id=".$id);
            q("delete from links where link_id=".$link_id);
        }
    } elseif($_GET['proc'] == "add")
    {
        q("update items set count = count + 1 where item_id = $id limit 1;");
        q("update links set count = count + 1 where item = $id and link_id = $link_id limit 1;");
    } elseif($_GET['proc'] == "update")
    {
        debugc("empty update call");
        break;
    }

    if($_SESSION['search']){
        $sprod=$_SESSION['search'];
        $search="where product like '%$sprod%'";
        unset($_SESSION['search']);
        $report="searched $sprod";
    }



    $body="
<table>
<tr><th>Item</th><th>Count</th><th>Location</th><th>Zone</th><th>Expire</th><th>Date</th><th>Update</th><th>Add</th><th>Remove</th></tr>";
    $list=q("select item_id,product,links.count as count,str_name as storage,zone_name as zone, items.expire as expire,date, link_id from links join items on items.item_id = links.item join 
        storage  on links.storage = storage.str_id join 
        zone on links.zone = zone.zone_id join 
        product on product.p_id = items.item_name $search order by date desc;");
    foreach($list as $row){
        $body.="
<tr>
<td> ".$row['product']." </td>
<td> ".$row['count']." </td>
<td> ".$row['storage']." </td>
<td> ".$row['zone']." </td>
<td> ".$row['expire']." </td>
<td> ".$row['date']." </td>
<td> <a href=\"?subproc=update&item=".$row['item_id']."&link=".$row['link_id']."\">update</a></td>
<td> <a href=\"?proc=add&item=".$row['item_id']."&link=".$row['link_id']."\">add</a></td>
<td> <a href=\"?proc=del_record&item=".$row['item_id']."&link=".$row['link_id']."\">remove</a></td>
</tr>
";
    }
    $body.="</table>";
    break;
}

$dbm->close();


// process and pass
// $array=array(1,2,3);
// index.php?value=".serialize($arr)
// another page
// process it
// $arr = unserialize($_GET['value']);
?>
