<?php
    include 'dbconnect.php';
 

date_default_timezone_set('Asia/Calcutta');

	function getaddress($lat,$lng)
	{
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
		$json = @file_get_contents($url);
		$data=json_decode($json);
		$status = $data->status;
		if($status=="OK")
		{
			return $data->results[0]->formatted_address;
		}
		else
		{
			return false;
		}
	}
	
	$is_unreguser = 0;
	$unreguser_insert = ""; 
 
 
 
    $latitude       = isset($_GET['latitude']) ? $_GET['latitude'] : '0';
    $latitude       = (float)str_replace(",", ".", $latitude); // to handle European locale decimals
    $longitude      = isset($_GET['longitude']) ? $_GET['longitude'] : '0';
    $longitude      = (float)str_replace(",", ".", $longitude);    
    $speed          = isset($_GET['speed']) ? $_GET['speed'] : 0;
   // $direction      = isset($_GET['direction']) ? $_GET['direction'] : 0;
   // $distance       = isset($_GET['distance']) ? $_GET['distance'] : '0';
  //  $distance       = (float)str_replace(",", ".", $distance);
    $date           = isset($_GET['date']) ? $_GET['date'] : '0000-00-00 00:00:00';
    $date           = urldecode($date);
   // $locationmethod = isset($_GET['locationmethod']) ? $_GET['locationmethod'] : '';
 //   $locationmethod = urldecode($locationmethod);
    $username       = isset($_GET['username']) ? $_GET['username'] : 0;
   // $phonenumber    = isset($_GET['phonenumber']) ? $_GET['phonenumber'] : '';
  //  $sessionid      = isset($_GET['sessionid']) ? $_GET['sessionid'] : 0;
  //  $accuracy       = isset($_GET['accuracy']) ? $_GET['accuracy'] : 0;
    $extrainfo      = isset($_GET['extrainfo']) ? $_GET['extrainfo'] : '';
   // $eventtype      = isset($_GET['eventtype']) ? $_GET['eventtype'] : '';
   // $latitude       = 47.6273270;
//	$longitude      = -122.3256910;
//	$username = 9509877669;
    // doing some validation here
	
	
	$alt=0;
  $deg=0;
  $speed_kn=0;
  //$speed_km=0;
  $sattotal=0;
  $fixtype=3;
  $hash="0";
 
/* 
  // File Test Begin
  $myfile = fopen("newfile.txt", "a") or die("Unable to open file!");
$txt = $username."|".$date."|".$latitude."|".$longitude;
fwrite($myfile, $txt);
fwrite($myfile, "\r\n");
fclose($myfile);
  
  // File Test End
  */
  $query = "Select id from pt_unit where imei = '".$username."'";
//  echo $query;
 $result = mysql_connect("localhost","root","csl@jantvdb");
if (!$result)
{
  die('Could not connect: ' . mysql_error());
}
$result = mysql_select_db("gpstracker");

if (!$result)
{
  die('Could not select the database: ' . mysql_error());
}

$result = mysql_query($query);
  	
if($row = mysql_fetch_array($result))
{
	$username = $row[0];
	mysql_close($result);
}
else
{
//echo $row[0];	
	//mysql_close($result);
	$is_unreguser = 1;
//	exit('-1');
}


	
    if ($latitude == 0 && $longitude == 0) {
        exit('-1');
    }

	 $address = getaddress($latitude,$longitude);
	if($address)
	{
		$extrainfo =  $address;
	}
	else
	{
		$extrainfo =  "UNKNOWN";
	}
	//echo $extrainfo ;

	if(!$is_unreguser)
	{
		$params = array(':lat'        => $latitude,
						':lon'       => $longitude,
						':speed_km'           => $speed,
						':datetime'            => $date,
						':datetime_received'   => $date,
						':unit_id'        => $username,
						':raw_input'       => $extrainfo,
						':alt'       => $alt,
						':deg'       => $deg,
						':speed_kn'       => $speed_kn,
						':sattotal'       => $sattotal,
						':fixtype'       => $fixtype,
						':hash'       => $hash
					   
					);
	
	 
				$stmt = $pdo->prepare( $sqlFunctionCallMethod.'prcSavePTPosition(
							  :lat, 
							  :lon, 
							  :speed_km, 
							  :datetime, 
							  :datetime_received, 
							  :unit_id, 
							  :raw_input,
							  :alt,
							  :deg,
							  :speed_kn,
							  :sattotal,
							  :fixtype,
							  :hash);'
				 );
		$stmt->execute($params);
		$timestamp = $stmt->fetchColumn();
		
		echo $timestamp;    
	}
	else
	{
		$check_query = "Select * from pt_position_unreguser where user_entry = '".$username."'";		
		
		$unreguser_insert = "INSERT INTO `pt_position_unreguser`(`user_entry`, `datetime`, `datetime_received`, `lat`, `lon`, `alt`, `deg`, `speed_km`, `speed_kn`, `sattotal`, `fixtype`, `raw_input`, `hash`) VALUES ('".$username."','".$date."','".$date."',".$latitude.",".$longitude.",".$alt.",".$deg.",".$speed.",".$speed_kn.",".$sattotal.",".$fixtype.",'".$extrainfo."',".$hash.")";
		
		
		$result = mysql_connect("localhost","root","csl@jantvdb");  //csl@jantvdb
		if (!$result)
		{
		  die('Could not connect: ' . mysql_error());
		}
		$result = mysql_select_db("gpstracker");
		if (!$result)
		{
		  die('Could not select the database: ' . mysql_error());
		}
		

		$result_check = mysql_query($check_query);
		//$rowcnt = mysql_num_rows ($result_check );
		
		if($row_check = mysql_fetch_array($result_check))
		{
			exit(1);
		}
		else
		{
			$result = mysql_query($unreguser_insert);
		}
	}
?>
