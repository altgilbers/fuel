<html>
<head>
	<script type="text/javascript" src="./js/jquery-1.5.1.min.js"></script>
	<script type="text/javascript" src="./js/jquery-ui-1.8.13.custom.min.js"></script>
	<link rel="stylesheet" type="text/css" href="./css/fuel2.css" />    
	<link rel="stylesheet" type="text/css" href="./css/ui-lightness/jquery-ui-1.8.13.custom.css" />    
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
    <script type="text/javascript">
    
$(document).ready(function(){
  $(function() {
	$( ".datepicker" ).datepicker();
	});
});
        var vechicle_id;
	var map;
	var fillup_data,filtered_fillup_data;
	var data_gallons,chart_gallons;
	var data_price,chart_price;
	var data_coords,data_mileage;
	var scatter_chart, mileageXY_chart;
	var table;
	var markers=new Array();
	var marker;
	var circle = new google.maps.Circle({
          map: map,
          radius: 3000 // 3000 km
        });

	google.load('visualization', '1', {'packages':['corechart', 'table']});
	google.setOnLoadCallback(init);

	function init() {
          map_initialize();
          requestData();
	}

        function requestData()
           { 
               vehicle_id=document.getElementById('vehicle_id').value;
		var query = new google.visualization.Query('http://www.altgilbers.com/fuel/datasource.php?vehicle_id='+vehicle_id);
		query.setTimeout(5);
		query.send(processData);
          }
	function map_initialize() {
		var myOptions = {
				zoom: 12,
				center: new google.maps.LatLng(42.25, -71.25),
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
		map = new google.maps.Map(document.getElementById("map"), myOptions);
	}

	function processData(response) {
		if (response.isError()) {
			alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
			return;
		}

  		fillup_data = response.getDataTable();
		createDataViews();
		updateTotals();
		createCharts();
		drawCharts();
	}
	
	function createDataViews()
	{
 		minDate=new Date(2007, 1, 1);
		maxDate=new Date(2039, 1, 1);
  		filtered_fillup_data = new google.visualization.DataView(fillup_data);
  		if (document.getElementById('start').value.length > 5)
  		{
  			s=new Date();
			s=Date.parse(document.getElementById('start').value);
			minDate=s;
  		}
  		if (document.getElementById('end').value.length > 5)
  		{
  			e=new Date();
			e=Date.parse(document.getElementById('end').value);
			maxDate=e;
  		}

  		filtered_fillup_data.setRows(filtered_fillup_data.getFilteredRows([{column: 0, minValue: minDate, maxValue: maxDate}]));
                if (filtered_fillup_data.getNumberOfRows()<1)
                {
                   alert("no rows...  adjust parameters");
                   return;
                }
		data_gallons = new google.visualization.DataView(filtered_fillup_data);
		data_gallons.setColumns([0,2]);
		data_price = new google.visualization.DataView(filtered_fillup_data);
		data_price.setColumns([0,3]);

		data_miles = new google.visualization.DataTable();
		data_miles.addColumn('datetime', 'date');
                data_miles.addColumn('number', 'miles');
                data_miles.addRows(filtered_fillup_data.getNumberOfRows());
                data_miles.setValue(0,0,filtered_fillup_data.getValue(0,0));
                data_miles.setValue(0,1,400);
                for (x=1; x<filtered_fillup_data.getNumberOfRows(); x++)
                {
                        tempdate=filtered_fillup_data.getValue(x,0);
                        data_miles.setValue(x,0,filtered_fillup_data.getValue(x,0));
                        data_miles.setValue(x,1,(filtered_fillup_data.getValue(x,1)-filtered_fillup_data.getValue(x-1,1)));
                }



		data_mileage = new google.visualization.DataTable();
		data_mileage.addColumn('datetime', 'date');
                data_mileage.addColumn('number', 'mileage');
                data_mileage.addRows(filtered_fillup_data.getNumberOfRows());

		data_mileage.setValue(0,0,filtered_fillup_data.getValue(0,0));
		data_mileage.setValue(0,1,25.0);
                for (x=1; x<filtered_fillup_data.getNumberOfRows(); x++)
		{
			tempdate=filtered_fillup_data.getValue(x,0);
			data_mileage.setValue(x,0,filtered_fillup_data.getValue(x,0));
			data_mileage.setValue(x,1,(filtered_fillup_data.getValue(x,1)-filtered_fillup_data.getValue(x-1,1))/filtered_fillup_data.getValue(x,2));
		}	
	}
		
	function updateTotals()
	{
		total_dollars=0.0;
		total_gallons=0.0;
		n=filtered_fillup_data.getNumberOfRows();
        for (x=0; x<n; x++)
        {
        	total_dollars+=filtered_fillup_data.getValue(x,2)*filtered_fillup_data.getValue(x,3);
        	total_gallons+=filtered_fillup_data.getValue(x,2);
        }
        total_miles=filtered_fillup_data.getValue(n-1,1)-filtered_fillup_data.getValue(0,1);
		document.getElementById('gallons').innerHTML=total_gallons.toFixed(3);
		document.getElementById('dollars').innerHTML=total_dollars.toFixed(2);
		document.getElementById('miles').innerHTML=total_miles;
		document.getElementById('c02').innerHTML=(total_gallons*8.788).toFixed(2);
		
	}

	function createCharts()
	{
		chart_gallons = new google.visualization.LineChart(document.getElementById('chart_gallons'));
		google.visualization.events.addListener(chart_gallons, 'select', gallonsChartSelectHandler);
		google.visualization.events.addListener(chart_gallons, 'onmouseover', chartOnmouseoverHandler);

		chart_miles = new google.visualization.LineChart(document.getElementById('chart_miles'));
		google.visualization.events.addListener(chart_miles, 'select', milesChartSelectHandler);
		google.visualization.events.addListener(chart_miles, 'onmouseover', chartOnmouseoverHandler);

		table = new google.visualization.Table(document.getElementById('table_div'));
		google.visualization.events.addListener(table, 'select', tableSelectHandler);
		table.draw(filtered_fillup_data, {showRowNumber: true});

		chart_price = new google.visualization.LineChart(document.getElementById('chart_price'));
		google.visualization.events.addListener(chart_price, 'select', priceChartSelectHandler);
		google.visualization.events.addListener(chart_price, 'onmouseover', chartOnmouseoverHandler);
			
		chart_mileage = new google.visualization.LineChart(document.getElementById('chart_mileage'));
		google.visualization.events.addListener(chart_mileage, 'select', mileageChartSelectHandler);
		google.visualization.events.addListener(chart_mileage, 'onmouseover', chartOnmouseoverHandler);
				
		scatter_chart = new google.visualization.ScatterChart(document.getElementById('scatter_div'));
		google.visualization.events.addListener(scatter_chart, 'onmouseover', chartOnmouseoverHandler);
		mileageXY_chart = new google.visualization.ScatterChart(document.getElementById('mileageXY_div'));	
		google.visualization.events.addListener(mileageXY_chart, 'onmouseover', chartOnmouseoverHandler);
	}
	
	function drawCharts()
	{
	loadMapData();
	chart_gallons.draw(data_gallons,
		{
		title: 'Gallons Purchased',
		vAxis: {minValue: 10.0, maxValue: 17.0,viewWindow: {min: 12.5, max:17.5} },
		showRowNumber: 'false'
		});
	chart_miles.draw(data_miles,
		{
		title: 'Miles Driven',
		vAxis: {minValue: 300.0, maxValue: 650.0, viewWindow: {min: 300, max:650}},
		showRowNumber: 'false'
		});
	chart_price.draw(data_price,
		{
		title: 'Cost Per Gallon',
		vAxis: {minValue: 2.0, maxValue: 4.5,viewWindow: {min: 1.0, max:4.5}}
		});
        mileageXY_chart.draw(data_mileage,
        	{
		title: 'date vs. mileage',
		hAxis: {title: 'date'},
		vAxis: {title: 'mileage', minValue: 20, maxValue: 45},
		legend: 'none'
		});		
        scatter_chart.draw(data_price,
       		{
                title: 'date vs. price',
                hAxis: {title: 'date'},
                vAxis: {title: 'price', minValue: 1.5, maxValue: 4.0,viewWindow: {min: 1.5, max:4.0}},
                legend: 'none'
                });
	chart_mileage.draw(data_mileage,
		{
		title: 'mileage',
		vAxis: {minValue: 23.0, maxValue: 29.0,viewWindow: {min: 20, max:45}}
		});
	}

	function loadMapData(){
		for (i=0; i<markers.length; i++)
		{
			markers[i].setMap(null);
		}
		s=90.0;
		n=-90.0;
		w=180.0;
		e=-180.0;
		for (x=0; x<filtered_fillup_data.getNumberOfRows(); x++)
		{
			if (filtered_fillup_data.getValue(x,4)<s)
				s=filtered_fillup_data.getValue(x,4);
			if (filtered_fillup_data.getValue(x,4)>n)
				n=filtered_fillup_data.getValue(x,4);
			if (filtered_fillup_data.getValue(x,5)<w)
				w=filtered_fillup_data.getValue(x,5);
			if (filtered_fillup_data.getValue(x,5)>e)
				e=filtered_fillup_data.getValue(x,5);
			createMapMarkers(x);
		}
	  var southWest = new google.maps.LatLng(s,w);
  	var northEast = new google.maps.LatLng(n,e);
  	var bounds = new google.maps.LatLngBounds(southWest,northEast);
  	map.fitBounds(bounds);
	}

	function createMapMarkers(x){

		marker = new google.maps.Marker({
				position: new google.maps.LatLng(filtered_fillup_data.getValue(x,4),filtered_fillup_data.getValue(x,5)),
				title: filtered_fillup_data.getValue(x,0)+": "+filtered_fillup_data.getValue(x,2)+" gallons @"+filtered_fillup_data.getValue(x,3),
				icon: "http://google-maps-icons.googlecode.com/files/gazstation.png"
				});
		google.maps.event.addListener(marker, 'click', function(){markerClickHandler(x);});
		marker.setMap(map);
		markers[x]=marker;
	}
	
	function markerClickHandler(x)
	{
	tableSelectRow(x);       
    circle.setRadius(filtered_fillup_data.getValue(x,6));
    circle.bindTo('center', markers[x], 'position');
	circle.setMap(map);
	}
	


	function gallonsChartSelectHandler() {
		mySelection=chart_gallons.getSelection();
		table.setSelection([{row:mySelection[0].row,column:null}]);
		map.setCenter(new google.maps.LatLng(filtered_fillup_data.getValue(mySelection[0].row,4), filtered_fillup_data.getValue(mySelection[0].row,5)));
	}
	function milesChartSelectHandler() {		
		mySelection=chart_miles.getSelection();
		table.setSelection([{row:mySelection[0].row,column:null}]);
		map.setCenter(new google.maps.LatLng(filtered_fillup_data.getValue(mySelection[0].row,4), filtered_fillup_data.getValue(mySelection[0].row,5)));
	}
	function priceChartSelectHandler() {		
		mySelection=chart_price.getSelection();
		table.setSelection([{row:mySelection[0].row,column:null}]);
		map.setCenter(new google.maps.LatLng(filtered_fillup_data.getValue(mySelection[0].row,4), filtered_fillup_data.getValue(mySelection[0].row,5)));
	}
	function mileageChartSelectHandler() {		
		mySelection=chart_mileage.getSelection();
		table.setSelection([{row:mySelection[0].row,column:null}]);
		map.setCenter(new google.maps.LatLng(filtered_fillup_data.getValue(mySelection[0].row,4), filtered_fillup_data.getValue(mySelection[0].row,5)));
	}
	function chartOnmouseoverHandler(e){
		tableSelectRow(e.row);
	}
	function tableSelectHandler() {		
		mySelection=table.getSelection();
		map.setCenter(new google.maps.LatLng(filtered_fillup_data.getValue(mySelection[0].row,4), filtered_fillup_data.getValue(mySelection[0].row,5)));
	}
	function tableSelectRow(r){
		table.setSelection([{row:r,column:null}]);
		document.getElementById('table_div').firstChild.firstChild.scrollTop = r*21-(document.getElementById('table_div').offsetHeight)/2
	}
	function setDateFilter(){
	setDateFiltersFromFormFields();	
	}

	function setDateFiltersFromFormFields()
	{
		createDataViews();
		updateTotals();
		createCharts();
		drawCharts();
	}
</script>
</head>


  <body>
  	<p>I Buy Gas 2.0</p>
	<div id="choose_vehicle">
	  <select name="vehicle_id" id="vehicle_id">
	    <option value="1">Ranger</option>
	    <option value="2">Civic</option>
	    <option value="3" selected="true">Echo</option>
	    <option value="4" selected="true">Impreza</option>
	    <option value=""></option>
	  </select>
	  <input type="button" value="reset vehicle" onClick="requestData()">
	</div>
  	<div id="choose_date">Start: <input type="text" id="start" class="datepicker">End: <input type="text" id="end" class="datepicker"><input type="button" value="Filter Dates" onClick="setDateFilter();"></div>
  	<div id="totals">Totals:  <span id="dollars">???</span> dollars to drive <span id="miles">???</span> miles, burning <span id="gallons">???</span> gallons, belching <span id="c02"></span>kg of carbon dioxide</div>
  	<div class="row">
    <div id="map" class="fuel_chart"><img id="ajax-loader" src="./ajax-loader.gif" /></div>
    <div class="fuel_chart" id="table_div" style="height: 350px"><img id="ajax-loader" src="./ajax-loader.gif" /></div>
 	</div>
 	<div class="row">
    <div class="fuel_chart" id="chart_mileage"><img id="ajax-loader" src="./ajax-loader.gif" /></div>
    <div class="fuel_chart" id="chart_price"><img id="ajax-loader" src="./ajax-loader.gif" /></div>
	</div>
  	<div class="row">
    <div class="fuel_chart" id="chart_gallons"><img id="ajax-loader" src="./ajax-loader.gif" /></div>
    <div class="fuel_chart" id="chart_miles"><img id="ajax-loader" src="./ajax-loader.gif" /></div>
	</div>
  	<div class="row">
	<div class="fuel_chart" id="mileageXY_div"><img id="ajax-loader" src="./ajax-loader.gif" /></div>
    <div class="fuel_chart" id="scatter_div"><img id="ajax-loader" src="./ajax-loader.gif" /></div>
	</div>
	<script>
  </body>
</html>
