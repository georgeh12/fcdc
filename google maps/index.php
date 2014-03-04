<?php
header('Content-type:text/html; charset=UTF-8');

$handle = fopen("settings/current.txt", "r");
$current = fgets( $handle );
fclose( $handle );

$handle = fopen("settings/numLines.txt", "r");
$numLines = fgets( $handle );
fclose( $handle );

$handle = fopen("settings/db_filename.txt", "r");
$db_filename = fgets( $handle );
fclose( $handle );
?>

<script type="text/javascript" src="js/json2.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	var current = <?php echo( $current ); ?>; //1
	var numLines = <?php echo( $numLines ); ?>; //1500
	var db_filename = '<?php echo( $db_filename ); ?>';
	var start = current;
	var end = current + numLines;
	var requests = 0;
	var MAX_REQUESTS = 2500; //Max is 2500, but this will prevent Google lock-outs
	var DATA_DOM = '#data';
	var ERROR_DOM = '#error';
	var ip;
	var ajax_requests = [];

	$.ajax({
		url: 'http://freegeoip.net/json/',
		success: function(data){
			ip = data['ip'];
			$('#ip').text(ip);
		}
	})

	$('#time').text( '<?php echo( date('Y/m/d H:i:s') ); ?>' );

	var ping = function(address,metadata) {
		if( requests < MAX_REQUESTS ) {
			requests++;
			var url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' + address + '&sensor=false';
			ajax_requests.push( url );
			$.ajax({
				url: url,
				cache: false,
				timeout: 30000,
				success: function(data) {
					if( data["status"] == 'OK' ) {
						var address_components = data["results"][0]["address_components"];
						var street_number = '';
						var route = '';
						var locality = '';
						var postal_code = '';
						for( i in address_components ){
							var type = address_components[i]["types"][0];
							var long_name = address_components[i]["long_name"];
							if( type == "street_number" ) {
								street_number = long_name;
							} else
							if( type == "route" ) {
								route = long_name;
							} else
							if( type == "locality" ) {
								locality = long_name;
							} else
							if( type == "postal_code" ) {
								postal_code = long_name;
							}
						}
						$(DATA_DOM).append( JSON.stringify(
							[
								data["results"][0]["formatted_address"],
								street_number,
								route,
								locality,
								postal_code,
								metadata
							] ) + '<br>\n' );
					} else
					if( data["status"] == 'OVER_QUERY_LIMIT' ){
						setTimeout( function(){ ping( address,metadata ); },2500 );
					} else
					if( data["status"] == 'ZERO_RESULTS' ){
						$(ERROR_DOM).append( JSON.stringify(
							[
								metadata
							] ) + '<br>\n' );
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert( 'Google Maps API Error: check log' );
					console.log(
						JSON.stringify(
							[
								{ 'jqXHR':jqXHR },
								{ 'textStatus':textStatus },
								{ 'errorThrown':errorThrown }
							] ) );
				}
			});
		}
	};

	var interval;
	
	var loop = function( lines ) {
		if( requests < MAX_REQUESTS && current < Math.min( end,lines.length ) ) {
			console.log( JSON.stringify(
					[
						{ 'requests':requests },
						{ 'current':current },
						{ 'lines[current]':lines[current] },
						{ 'end':end }
					] ) );
			var columns = lines[current].split(',');
			var address;
			if( db_filename == 'Street_File_Listing_By_Locality.csv' ) {
				address = columns[4] + ' ' + columns[2] + ' ' + columns[0] + ' ' + columns[1] + ' ' + columns[7];
			} else
			if( db_filename == 'list.csv' ) {
				address = columns[6] + ' ' + columns[7];
			} else
			if( db_filename == '2.csv' ) {
				address = columns[0] + ' ' + columns[1] + ' VA';
			}
			else {
				address = columns[0];
			}
			ping( address,columns );
			current++;
		}
		else {
			clearInterval( interval );
			console.log( 'geocode stopped before: ' + current );
			$.when(ajax_requests).then( function(){
				$.ajax({ 
					type: 'POST',
					url: 'save.php',
					data: { 'file':db_filename, 'ip':ip, 'start':start, 'end':current, 'data':$(DATA_DOM).text(), 'error':$(ERROR_DOM).text() },
					success: function(){ alert( 'done!' ); }
				});
				$('button').removeAttr('disabled');
			});
		}
	};
	
	

	$('#run').on('click', function(){
		$('button').attr('disabled', 'disabled');
		$.get( 'db/' + db_filename, function(data) {
			var lines = data.split('\n');
			var TIMEOUT = 250;
			interval = setInterval( function(){ loop( lines ); }, TIMEOUT );
		}, 'text' );
	});
});
</script>
<body>
	<button id="run">Run</button>
	<span id="ip" style='color:red;'></span>
	<span id="time" style='color:blue;'></span>
	<table>
		<tr>
			<td>
				<div id="data"></div>
			</td>
			<td>
				<div id="error"></div>
			</td>
		<tr>
	</table>
</body>