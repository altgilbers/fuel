<?php 
header('content-type: text/plain; charset: utf-8');
include('./sql_connect.php');
print "google.visualization.Query.setResponse({";
print "version:'0.1',";
print "status:'ok',";
if (isset($_REQUEST['tqx']))
   print $_REQUEST['tqx'].",";
else
   print "reqId:'0',";	
print "table:{";
print "cols:[";
$line="{id:'A',label:'time',type:'datetime'},";
$line=$line."{id:'B',label:'miles',type:'number'},";
$line=$line."{id:'C',label:'gallons',type:'number'},";
$line=$line."{id:'D',label:'cost',type:'number'},";
$line=$line."{id:'E',label:'lat',type:'number'},";
$line=$line."{id:'F',label:'lng',type:'number'},";
$line=$line."{id:'G',label:'accuracy',type:'number'}";
print $line;
print "],";
print "rows:[";

if (isset($_REQUEST['vehicle_id']) && $_REQUEST['vehicle_id']!="")
   $query = "SELECT * FROM `fillups` where vehicle_id=".$_REQUEST['vehicle_id']." order by `miles` LIMIT 1000 OFFSET 0";
else
   $query = "SELECT * FROM `fillups` order by `time` LIMIT 1000 OFFSET 0";
$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());
$count=mysql_num_rows($result);
$i=1;
if ($count > 0) 
{ 
while($row = mysql_fetch_row($result)) 
{ 
	$dt=getdate(strtotime($row[0]));		
	$line="{c:[";
	$line=$line."{v:new Date(".$dt[year].",".($dt[mon]-1).",".$dt[mday].",".$dt[hours].",".$dt[minutes].",".$dt[seconds]."),f:'".$row[0]."'},";
	$line=$line."{v:".$row[1].",f:'".$row[1]."'},";
	$line=$line."{v:".$row[2].",f:'".$row[2]."'},";
	$line=$line."{v:".$row[3].",f:'".$row[3]."'},";
	$line=$line."{v:".$row[4].",f:'".$row[4]."'},";
	$line=$line."{v:".$row[5].",f:'".$row[5]."'},";
	$line=$line."{v:".$row[6].",f:'".$row[6]."'}";
	$line=$line."]}";
	if ($i<$count)
		$line=$line.",";
	print $line;
	$i++;
	} 
}
print "]";
print "}";
print "}";
print ");";


?>


