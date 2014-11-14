<?php include('./sql_connect.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
	<link rel="stylesheet" type="text/css" href="./css/fuel.css" />    
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=320"> 
    <title>Fuel</title>
    	<script type="text/javascript">

	function PD(n, totalDigits) 
    { 
        n = n.toString(); 
        var pd = ''; 
        if (totalDigits > n.length) 
        { 
            for (i=0; i < (totalDigits-n.length); i++) 
            { 
                pd += '0'; 
            } 
        } 
        return pd + n.toString(); 
    } 

	function findLocation()
	{
	  navigator.geolocation.getCurrentPosition(foundLocation, noLocation);
	}
	function foundLocation(position)
	{
	  var lat = position.coords.latitude;
	  var long = position.coords.longitude;	  
	  var acc = position.coords.accuracy;
  	  document.data_entry.elements["lat"].value=lat;
	  document.data_entry.elements["long"].value=long;
	  document.data_entry.elements["accuracy"].value=acc;
	}
	function noLocation()
	{
	  alert('Could not find location');
	}
	function prePopulate()
	{
		findLocation();
		var now=new Date();
		document.data_entry.elements["date"].value=now.getFullYear()+"-"+PD(now.getMonth()+1,2)+"-"+PD(now.getDate(),2);
		document.data_entry.elements["time"].value=PD(now.getHours(),2)+":"+PD(now.getMinutes(),2)+":"+PD(now.getSeconds(),2);
	}
	</script>
</head>

<body onload=prePopulate()>
	<?php
	if ($_POST[buttonSubmit]!=true)
{
echo "<form name=\"data_entry\" method=\"post\" action=\"enter.php\">";
echo "<table id=\"data_entry\">";
echo "<tr><td>Vehicle:</td><td><select name=\"vehicle\">";
echo "<option value=\"1\">Ranger</option>";
echo "<option value=\"2\">Civic</option>";
echo "<option value=\"3\">Echo</option>";
echo "<option value=\"4\" selected=\"true\">Impreza</option>";
echo "</select></td>";
echo "<tr><td>Date:</td><td><input name=\"date\" type=\"text\" id=\"date\" value=\"$_POST[date]\"></td></tr>";
echo "<tr><td>Time:</td><td><input name=\"time\" type=\"text\" id=\"time\" value=\"$_POST[time]\"></td></tr>";
echo "<tr><td>Price:</td><td><input name=\"price\" type=\"text\" id=\"price\" value=\"$_POST[price]\"></td></tr>";
echo "<tr><td>Gallons:</td><td><input name=\"gallons\" type=\"text\" id=\"gallons\" value=\"$_POST[gallons]\"></td></tr>";
echo "<tr><td>Miles:</td><td><input name=\"miles\" type=\"text\" id=\"miles\" value=\"$_POST[miles]\"></td></tr>";
echo "<tr><td>Lat:</td><td><input name=\"lat\" type=\"text\" id=\"lat\" value=\"$_POST[lat]\"></td></tr>";
echo "<tr><td>Long:</td><td><input name=\"long\" type=\"text\" id=\"long\" value=\"$_POST[long]\"></td></tr>";
echo "<tr><td>Acc:</td><td><input name=\"accuracy\" type=\"text\" id=\"accuracy\" value=\"$_POST[accuracy]\"></td></tr>";
echo "<input name=\"buttonSubmit\" type=\"hidden\"  value=\"true\">";
echo "<tr><td></td><td><input id=\"registerButton\" type=\"submit\" name=\"Submit\" value=\"Enter\"></td></tr>";
echo "</table>";
echo "</form>"; ?>
<input name="refresh" type="submit"  value="refresh" onClick="prePopulate()">
<div id="accuracy"></div>



<?php
}
else
{
echo "Thanks!!";	
}

if ($_POST[buttonSubmit]==true)
{
	$datetime=$_POST[date]." ".$_POST[time];
	$query="INSERT INTO fillups (time, miles, gallons, cost, lat, lng, accuracy, vehicle_id) VALUES ('$datetime', '$_POST[miles]', '$_POST[gallons]', '$_POST[price]', '$_POST[lat]', '$_POST[long]', '$_POST[accuracy]', '$_POST[vehicle]')";
	$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error()); 	
}
?>

</body>
</html>