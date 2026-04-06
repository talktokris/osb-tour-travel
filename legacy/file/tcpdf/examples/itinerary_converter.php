<?php  
session_start();
//echo '<img src="../../../setup/agent_logo/10/10logo.jpg" />';

  require("../../../../connect/db_con.php") ;
if (isset($_GET['file_count_no'])) {$file_count_no = $_GET['file_count_no'];}

//$file_count_no=870;

		 $resultsHeader = mysql_query("SELECT * FROM  file_entry WHERE  file_count_no ='$file_count_no'  ORDER BY  file_id  DESC LIMIT 1");
		$rowysHeader = mysql_fetch_array($resultsHeader);
		$agent_name = $rowysHeader['agent_name']; 
		$file_no=$rowysHeader['file_no'];
		$name=$rowysHeader['last_name'].' '.$rowysHeader['first_name'];
		$pax_mobile=$rowysHeader['pax_mobile'];
		$no_of_pax=$rowysHeader['no_of_pax'];
		$date=$rowysHeader['date'];
		$ref_no=$rowysHeader['ref_no'];
		$remarks=$rowysHeader['remarks'];
		
		// find Agent Details Start
		$resultsAgent = mysql_query("SELECT * FROM  agent WHERE  agent_name ='$agent_name'");
		$rowysAgent = mysql_fetch_array($resultsAgent);
		$agent_name = $rowysAgent['agent_name']; 
		$agent_country = $rowysAgent['agent_country']; 
		$agent_city = $rowysAgent['agent_city']; 
		$agent_email = $rowysAgent['agent_email']; 
		$agent_contact_no = $rowysAgent['agent_contact_no']; 
		
		$agent_logo_name= $rowysAgent['agent_logo_name']; 
		$agent_id= $rowysAgent['agent_id']; 
		$image_locaton='../../setup/agent_logo/'.$agent_id.'/';


	
//============================================================+
// File name   : example_021.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 021 for TCPDF class
//               WriteHTML text flow
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Wri teHTML text flow.
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include2.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('');
$pdf->SetTitle('within Earth');
$pdf->SetSubject('');
$pdf->SetKeywords('');
//define ('K_PATH_IMAGES', '../../../setup/agent_logo/'.$agent_id.'/');

$pdf->SetHeaderData($agent_logo_name, 180, "", "  ");

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 021', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

$lg = Array();
$lg['a_meta_charset'] = 'UTF-8';
$lg['a_meta_dir'] = 'rtl';
$lg['a_meta_language'] = 'fa';
$lg['w_page'] = 'page';

// ---------------------------------------------------------
$pdf->SetFont('helvetica', '', 9);

$pdf->SetFont('dejavusans', '', 18);

$pdf->setRTL(false);

$pdf->SetFontSize(8);
// add a page
$pdf->AddPage();
//$isComing='hi thisi s coming test';
//$supplier_name_came='Texi hub';
date_default_timezone_set("Asia/Kuala_Lumpur"); 			 
    //<------------Get the Arabit labels -------------------------->
     

			$side_sqls="SELECT * FROM   arebic_lebels WHERE arebic_lebels_id  = '1'";
			$side_results=mysql_query($side_sqls);
			$side_row=mysql_fetch_array($side_results);
			 $ITINERARY =$side_row['ITINERARY'];  $ITINERARY_fills =$side_row['ITINERARY_fills']; $Client_Name=$side_row['Client_Name']; $Client_Name_fills=$side_row['Client_Name_fills'];
			 $Ref_No=$side_row['Ref_No']; $Ref_No_fills=$side_row['Ref_No_fills']; $Arrival=$side_row['Arrival']; $Arrival_fills=$side_row['Arrival_fills']; $Departure=$side_row['Departure'];
			 $Departure_fills=$side_row['Departure_fills'];	 $Transfers=$side_row['Transfers'];	 $Transfers_fills=$side_row['Transfers_fills'];	 $Drop_Off_Point=$side_row['Drop_Off_Point'];
			 $Drop_Off_Point_fills=$side_row['Drop_Off_Point_fills']; $Service_Name=$side_row['Service_Name']; $Service_Name_fills=$side_row['Service_Name_fills'];
			  $Pick_Up_Point=$side_row['Pick_Up_Point']; $Pick_Up_Point_fills=$side_row['Pick_Up_Point_fills']; $Tours=$side_row['Tours']; $Tours_fills=$side_row['Tours_fills'];
$Pick_up_Time_arabic =$side_row['city_one_fills'];	
$City_arebic=$side_row['city_two_fills'];
$Service_Name_arabic=$side_row['city_three_fills'];
$Date_arabic=$side_row['city_four_fills'];	
$city_five_fills=$side_row['city_five_fills'];

 

$textHeader=  '<table width="400"  >
<tr><td  colspan="2"><h2>'.$agent_name.'</h2></td></tr>
<tr><td><strong>Country : </strong>'.$agent_country.'</td><td><strong>    City : </strong>'.$agent_city.'</td></tr>
<tr><td><strong>Tel No : </strong>'.$agent_contact_no .'</td><td><strong>Email : </strong>'.$agent_email.'</td></tr>
</table>';
$pdf->writeHTML($textHeader, true, 0, true, 0);
$html4Itenheader=  '<table >
<tr><td align="center"><h1> ITINERARY &nbsp; ( '.$ITINERARY_fills.' ) </h1></td></tr>
</table>';	
$pdf->writeHTML($html4Itenheader, true, 0, true, 0);

   			$time_sqls="SELECT * FROM   time_format WHERE  	time_format_id  = '1'";
			$time_results=mysql_query($time_sqls);
			$time_row=mysql_fetch_array($time_results);
			 $aa_fills =$time_row['aa_fills'];
			 $bb_fills =$time_row['bb_fills'];
			 $cc_fills=$time_row['cc_fills'];
			 $dd_fills=$time_row['dd_fills'];
			 $ee_fills=$time_row['ee_fills'];
			// echo $aa_fills.' | '.$bb_fills.' | '.$cc_fills.' | '.$dd_fills.' | '.$ee_fills;
			 

// <----------------------- getting Arrival ---------->
			$arrival_sqls="SELECT * FROM   file_entry WHERE file_count_no = '$file_count_no' ORDER BY service_date ASC LIMIT 1";
			$arrival_results=mysql_query($arrival_sqls);
			$arrival_row=mysql_fetch_array($arrival_results);
			$service_typeArrival =$arrival_row['service_type'];
			$title=$arrival_row['title']; 	$last_name=$arrival_row['last_name']; 	$first_name=$arrival_row['first_name'];
			$serviceArrivalEng=$arrival_row['service'];
			
			
			$client_name= $title .' '.$last_name.' '.$first_name;
			$flight_timeArrivalSQL=$arrival_row['pickup_time'];
			$flight_timeArrival = substr($flight_timeArrivalSQL,0,5); 
			
			


$now=$flight_timeArrivalSQL;

	$dateCon = strtotime($now);
$date1Strat = "01:00:00"; $date1End = "05:59:00"; $date1from = strtotime($date1Strat); $date1to = strtotime($date1End);
$date2Strat = "06:00:00"; $date2End = "11:59:00"; $date2from = strtotime($date2Strat); $date2to = strtotime($date2End);
$date3Strat = "12:00:00"; $date3End = "14:59:00"; $date3from = strtotime($date3Strat); $date3to= strtotime($date3End); 
$date4Strat = "15:00:00"; $date4End = "17:59:00"; $date4from = strtotime($date4Strat); $date4to = strtotime($date4End);
$date5Strat = "18:00:00"; $date5End = "23:59:00"; $date5from = strtotime($date5Strat); $date5to = strtotime($date5End);
if ($dateCon >= $date1from && $dateCon <= $date1to){ $arabic_timeArrival=$aa_fills;   } elseif ($dateCon >= $date2from && $dateCon <= $date2to){ $arabic_timeArrival=$bb_fills;   }
elseif ($dateCon >= $date3from && $dateCon <= $date3to){ $arabic_timeArrival=$cc_fills;   } elseif ($dateCon >= $date4from && $dateCon <= $date4to){ $arabic_timeArrival=$dd_fills;   }
elseif ($dateCon >= $date5from && $dateCon <= $date5to){ $arabic_timeArrival=$ee_fills;   }

		
//			<------------ Get the service name in  arabic ------------------->

   			$ServiceArrival_sqls="SELECT * FROM   service WHERE  service_name_english  = '$serviceArrivalEng'";
			$ServiceArrival_results=mysql_query($ServiceArrival_sqls);
			$ServiceArrival_row=mysql_fetch_array($ServiceArrival_results);
			$service_name_arabicServiceArrival =$ServiceArrival_row['service_name_arabic'];
			$to_locaionServiceArrival =$ServiceArrival_row['to_locaion'];	
            $from_locaionServiceArrival =$ServiceArrival_row['from_locaion'];
// <----------------------- getting Location Name Arabic ---------->
			   			$LocationArrival_sqls="SELECT * FROM   location WHERE  location_name  = '$from_locaionServiceArrival'";
			$LocationArrival_results=mysql_query($LocationArrival_sqls);
			$LocationArrival_row=mysql_fetch_array($LocationArrival_results);
			$Location_name_arabicLocationArrival =$LocationArrival_row['location_name_arb'];
			
			
				$flight_noArrival=$arrival_row['flight_no']; 
				
					$pickup_timeArrival=$arrival_row['pickup_time']; 	
			$pickup_fromArrival=$arrival_row['pickup_from']; 	$drop_offArrival=$arrival_row['drop_off'];
			$ref_noAll=$arrival_row['ref_no'];
			$service_dateArrival=$arrival_row['service_date'];
			$service_dateArrivalSQL=$arrival_row['service_date'];
			$service_dateArrival=date('d-M-Y',strtotime($service_dateArrivalSQL));
			
// <----------------------- getting Departure ---------->
			
			$departure_sqls="SELECT * FROM   file_entry WHERE file_count_no = '$file_count_no' ORDER BY service_date DESC LIMIT 1";
			$departure_results=mysql_query($departure_sqls);
			$departure_row=mysql_fetch_array($departure_results);
			$service_typeDeparture =$departure_row['service_type'];
			
			
			
			$flight_timeDepartureSQL=$departure_row['pickup_time'];
			$flight_timeDeparture = substr($flight_timeDepartureSQL,0,5); 
			
			$nowde=$flight_timeDepartureSQL; 
			//$dateCon = DateTime::createFromFormat('H:i:s', $now);
			
			$dateCon = strtotime($nowde);
$date1Strat = "01:00:00"; $date1End = "05:59:00"; $date1from = strtotime($date1Strat); $date1to = strtotime($date1End);
$date2Strat = "06:00:00"; $date2End = "11:59:00"; $date2from = strtotime($date2Strat); $date2to = strtotime($date2End);
$date3Strat = "12:00:00"; $date3End = "14:59:00"; $date3from = strtotime($date3Strat); $date3to= strtotime($date3End); 
$date4Strat = "15:00:00"; $date4End = "17:59:00"; $date4from = strtotime($date4Strat); $date4to = strtotime($date4End);
$date5Strat = "18:00:00"; $date5End = "23:59:00"; $date5from = strtotime($date5Strat); $date5to = strtotime($date5End);
if ($dateCon >= $date1from && $dateCon <= $date1to){ $arabic_timeDeparture=$aa_fills;   } elseif ($dateCon >= $date2from && $dateCon <= $date2to){ $arabic_timeDeparture=$bb_fills;   }
elseif ($dateCon >= $date3from && $dateCon <= $date3to){ $arabic_timeDeparture=$cc_fills;   } elseif ($dateCon >= $date4from && $dateCon <= $date4to){ $arabic_timeDeparture=$dd_fills;   }
elseif ($dateCon >= $date5from && $dateCon <= $date5to){ $arabic_timeDeparture=$ee_fills;   }


			$flight_noDeparture=$departure_row['flight_no']; 	$pickup_timeDeparture=$departure_row['pickup_time']; 	
			$pickup_fromDeparture=$departure_row['pickup_from']; 	$drop_offDeparture=$departure_row['drop_off'];		
			
			$service_dateDepartureSQL=$departure_row['service_date'];
			$service_dateDeparture=date('d-M-Y',strtotime($service_dateDepartureSQL));
			
			$serviceDepartureEng=$departure_row['service'];
// <----------------------- getting Service Name Arabic ---------->
   			$ServiceDeparture_sqls="SELECT * FROM   service WHERE  service_name_english  = '$serviceDepartureEng'";
			$ServiceDeparture_results=mysql_query($ServiceDeparture_sqls);
			$ServiceDeparture_row=mysql_fetch_array($ServiceDeparture_results);
			$service_name_arabicServiceDeparture =$ServiceDeparture_row['service_name_arabic'];
			$to_locaionServiceDeparture =$ServiceDeparture_row['to_locaion'];	
            $from_locaionServiceDeparture =$ServiceDeparture_row['from_locaion'];
// <----------------------- getting Location Name Arabic ---------->
			$LocationDeparture_sqls="SELECT * FROM   location WHERE  location_name  = '$from_locaionServiceDeparture'";
			$LocationDeparture_results=mysql_query($LocationDeparture_sqls);
			$LocationDeparture_row=mysql_fetch_array($LocationDeparture_results);
			$Location_name_arabicLocationDeparture =$LocationDeparture_row['location_name_arb'];
			
			
			
?>
<?php $html1= '
<table  height="84" border="1" cellpadding="5" cellspacing="0">
  <tr>
    <td colspan="4" rowspan="2" align="center">'. $client_name .' : '.$Client_Name_fills .'</td>
    <td align="center"  colspan="2">'. $Ref_No_fills.'</td>
  </tr>
  <tr>
    <td  align="center" colspan="2">'. $file_no.'</td>
  </tr>
</table>';

$pdf->writeHTML($html1, true, 0, true, 0);
$loop=1;

$loop_header ='
<table   border="1" cellpadding="1" cellspacing="0">
  <tr>
    <td colspan="4" align="center" height="30"><h2>'. $Transfers_fills.'</h2></td>
  </tr><tr><td width="116"  height="25" align="center"><h3>'.$Pick_up_Time_arabic.'</h3></td><td  width="128" align="center"><h3>'.$City_arebic.'</h3></td ><td  width="315" align="center"><h3>'.$Service_Name_arabic.'</h3></td><td width="78" align="center"><h3>'.$Date_arabic.'</h3></td></tr>';
 $query_select = "SELECT * FROM  file_entry WHERE   file_count_no ='$file_count_no'  ORDER BY service_date, pickup_time ASC";
		$Num=1;
		$result_select = mysql_query($query_select) ;
		$rows = array();
		while($row = mysql_fetch_array($result_select))
    	$rows[] = $row;
	
			foreach($rows as $row){
			





			$flight_noloop=$row['flight_no']; 	$pickup_timeloopSQL=$row['pickup_time']; 	
			$pickup_fromloop=$row['pickup_from']; 	$drop_offloop=$row['drop_off'];	
			
			$from_locationloop=$row['from_location']; 	$to_locationloop=$row['to_location'];	
			$from_zoneloop=$row['from_zone']; 	$to_zoneloop=$row['to_zone'];		
			

			
					
			
			$flight_timeloopSQL=$row['pickup_time'];
			$flight_timeloop = substr($flight_timeloopSQL,0,5); 
			
			$service_dateloopSQL=$row['service_date'];
			$service_dateloop=date('d-M-Y',strtotime($service_dateloopSQL));
			
			$now=$flight_timeloopSQL;  $dateCon= strtotime($flight_timeloopSQL); //= DateTime::createFromFormat('H:i:s', $now);
			
					
$date1Strat = "01:00:00"; $date1End = "05:59:00"; $date1from = strtotime($date1Strat); $date1to = strtotime($date1End);
$date2Strat = "06:00:00"; $date2End = "11:59:00";$date2from = strtotime($date2Strat);$date2to = strtotime($date2End);
$date3Strat = "12:00:00"; $date3End = "14:59:00"; $date3from = strtotime($date3Strat); $date3to= strtotime($date3End); 
$date4Strat = "15:00:00"; $date4End = "17:59:00"; $date4from = strtotime($date4Strat); $date4to = strtotime($date4End);
$date5Strat = "18:00:00"; $date5End = "23:59:00"; $date5from = strtotime($date5Strat); $date5to = strtotime($date5End);

if ($dateCon >= $date1from && $dateCon <= $date1to){ $arabic_timeloop=$aa_fills;   } elseif ($dateCon >= $date2from && $dateCon <= $date2to){ $arabic_timeloop=$bb_fills;   }
elseif ($dateCon >= $date3from && $dateCon <= $date3to){ $arabic_timeloop=$cc_fills;   } elseif ($dateCon >= $date4from && $dateCon <= $date4to){ $arabic_timeloop=$dd_fills;   }
elseif ($dateCon >= $date5from && $dateCon <= $date5to){ $arabic_timeloop=$ee_fills;   }

			
			
			
			$serviceloopEng=$row['service'];
			$service_id=$row['service_id'];
// <----------------------- getting Service Name Arabic ---------->
   			$Serviceloop_sqls="SELECT * FROM   service WHERE  service_id  = '$service_id'  ORDER BY service_id DESC LIMIT 1";
			$Serviceloop_results=mysql_query($Serviceloop_sqls);
			$Serviceloop_row=mysql_fetch_array($Serviceloop_results);
			$service_name_arabicServiceloop =$Serviceloop_row['service_name_arabic'];
			$to_locaionServiceloop =$Serviceloop_row['to_locaion'];	
            $from_locaionServiceloop =$Serviceloop_row['from_locaion'];
			$from_city_service_en=$Serviceloop_row['from_city'];
			
			// <----------------------- getting From City Name Arabic ---------->
			$LocationFromCityServiceloop_sqls="SELECT * FROM   city WHERE  city_name  = '$from_city_service_en'  ORDER BY  	city_id DESC LIMIT 1";
			$LocationFromCityServiceloop_results=mysql_query($LocationFromCityServiceloop_sqls);
			$LocationFromCityServiceloop_row=mysql_fetch_array($LocationFromCityServiceloop_results);
			$LocationFromCityService_name_arabicLocationloop =$LocationFromCityServiceloop_row['city_shotform'];
			
// <----------------------- getting Location Name Arabic ---------->
			$LocationFromloop_sqls="SELECT * FROM   location WHERE  location_name  = '$from_locationloop'  ORDER BY location_id DESC LIMIT 1";
			$LocationFromloop_results=mysql_query($LocationFromloop_sqls);
			$LocationFromloop_row=mysql_fetch_array($LocationFromloop_results);
			$LocationFrom_name_arabicLocationloop =$LocationFromloop_row['location_name_arb'];
			
			
			$ZoneFromloop_sqls="SELECT * FROM   zone WHERE  zone_name  = '$from_zoneloop'  ORDER BY zone_id DESC LIMIT 1";
			$ZoneFromloop_results=mysql_query($ZoneFromloop_sqls);
			$ZoneFromloop_row=mysql_fetch_array($ZoneFromloop_results);
			$ZoneFrom_name_arabicLocationloop =$ZoneFromloop_row['zone_name_arabic'];
			
			
			$LocationToloop_sqls="SELECT * FROM   location WHERE  location_name  = '$to_locationloop'  ORDER BY location_id DESC LIMIT 1";
			$LocationToloop_results=mysql_query($LocationToloop_sqls);
			$LocationToloop_row=mysql_fetch_array($LocationToloop_results);
			$LocationTo_name_arabicLocationloop =$LocationToloop_row['location_name_arb'];
			
			
			$ZoneToloop_sqls="SELECT * FROM   zone WHERE  zone_name = '$to_zoneloop' ORDER BY zone_id DESC LIMIT 1";
			$ZoneToloop_results=mysql_query($ZoneToloop_sqls);
			$ZoneToloop_row=mysql_fetch_array($ZoneToloop_results);
			$ZoneTo_name_arabicLocationloop =$ZoneToloop_row['zone_name_arabic'];
			
			
			if($ZoneFrom_name_arabicLocationloop==''){ $from_loop=$LocationFrom_name_arabicLocationloop;} else {$from_loop=$ZoneFrom_name_arabicLocationloop; }
			if($ZoneTo_name_arabicLocationloop==''){ $to_loop=$LocationTo_name_arabicLocationloop;} else {$to_loop=$ZoneTo_name_arabicLocationloop; }
  
$loop_body_part ='  <tr>
    <td width="116"  height="38" align="center" ><table><tr><td align="right">'. $arabic_timeloop. ' </td><td align="center"> ' .date("g:i", strtotime($flight_timeloopSQL)) .'</td></tr></table></td>
    <td width="128" align="center">'.$LocationFromCityService_name_arabicLocationloop.'</td>
    <td width="315" align="center"><table><tr><td colspan="2">'.$service_name_arabicServiceloop.'</td></tr><tr><td>
	
  <table><tr><td width="100" align="right">'.$to_loop.'</td><td width="5">:</td><td  width="30"> '.$Drop_Off_Point_fills.'</td></tr></table>
</td><td>
<table><tr><td width="100"  align="right">'.$from_loop .'</td><td width="5">:</td><td width="30">'.$Pick_Up_Point_fills.'</td></tr></table></td></tr></table></td>
    <td  width="78" align="center">'. $service_dateloop.'</td>
  </tr>';
  if($loop==1){$loop_body=$loop_body_part;} else {$loop_body=$loop_body.$loop_body_part;}
  $loop++;
   }
$loop_footer ='</table>'; 

$loop_table=$loop_header.$loop_body .$loop_footer;
$pdf->writeHTML($loop_table, true, 0, true, 0);
$pdf->SetFontSize(1);

$html2='
<table>
				<tr><td></td></tr>
		

				</table>';



				

				

				








//$pdf->writeHTML($html4Account, true, 0, true, 0); 

$pdf->writeHTML($html2, true, 0, true, 0); 
$pdf->AddPage();
//$pdf->writeHTML($city_five_fills, true, 0, true, 0); 
//$pdf->Image('images/itinerary_footer.jpg', 15, 140, 75, 113, 'JPG', 'http://www.tcpdf.org', '', true, 150, '', false, false, 0, false, false, false);
//$pdf->SetXY(110, 400);
$pdf->Image('images/itinerary_footer.jpg', '', '', 245, 320, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
// output the HTML content

//$pdf->writeHTML($html2, true, 0, true, 0);
//$pdf->writeHTML($html2, true, 0, true, 0);
//$pdf->writeHTML($html2, true, 0, true, 0);
$stamp = date("Y-m-d") ;
//Create Folder Start
/*
if (!file_exists('../../pdf/'.$file_count_no)) {
    mkdir('../../pdf/'.$file_count_no, 0777, true);}
	*/
//Create Folder End
 
	//
// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------
date_default_timezone_set("Asia/Kuala_Lumpur"); 
//Close and output PDF document
$pdf->Output($file_count_no.'example_021.pdf', 'I');
//$pdf->Output('../../pdf/'.$file_count_no.'/services_'.$stamp.'-'.$file_no.'_'.$supplier_name_came.'.pdf', 'F');
/*
$attachment_name='../../pdf/'.$file_count_no.'/services_'.$stamp.'-'.$file_no.'_'.$supplier_name_came.'.pdf';

if(strlen($file_count_no) < 1 ) {echo " File Name not correct . <br />"; }
else{$sql="INSERT INTO  file_attachment_services (file_attachment_type, file_count_no, supplier_ajent_name, attachment_name) 	VALUES ('Services', '$file_count_no','$supplier_name_came', '$attachment_name')";	 }

if (!mysql_query($sql,$connection)){  echo "error".mysql_error(). "<br/>"; }
 else { /* echo "Data is already saved";  echo '<script type="text/javascript"><!-- window.location = "file_booking_preview.php?entry=new"//--></script>';}

*/
//============================================================+
// END OF FILE
//============================================================+

?>

