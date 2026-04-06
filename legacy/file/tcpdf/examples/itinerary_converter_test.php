<?php  
session_start();
//echo '<img src="../../../setup/agent_logo/10/10logo.jpg" />';

  require("../../../../connect/db_con.php") ;
if (isset($_GET['file_count_no'])) {$file_count_no = $_GET['file_count_no'];}

$file_count_no=870;

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

$pdf->SetHeaderData($agent_logo_name, 60, "", "  ");

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

$pdf->SetFont('aefurat', '', 18);

$pdf->setRTL(false);

$pdf->SetFontSize(8);
// add a page
$pdf->AddPage();
//$isComing='hi thisi s coming test';
//$supplier_name_came='Texi hub';
			 
    //<------------Get the Arabit labels -------------------------->
     

			$side_sqls="SELECT * FROM   arebic_lebels WHERE arebic_lebels_id  = '1'";
			$side_results=mysql_query($side_sqls);
			$side_row=mysql_fetch_array($side_results);
			 $ITINERARY =$side_row['ITINERARY'];  $ITINERARY_fills =$side_row['ITINERARY_fills']; $Client_Name=$side_row['Client_Name']; $Client_Name_fills=$side_row['Client_Name_fills'];
			 $Ref_No=$side_row['Ref_No']; $Ref_No_fills=$side_row['Ref_No_fills']; $Arrival=$side_row['Arrival']; $Arrival_fills=$side_row['Arrival_fills']; $Departure=$side_row['Departure'];
			 $Departure_fills=$side_row['Departure_fills'];	 $Transfers=$side_row['Transfers'];	 $Transfers_fills=$side_row['Transfers_fills'];	 $Drop_Off_Point=$side_row['Drop_Off_Point'];
			 $Drop_Off_Point_fills=$side_row['Drop_Off_Point_fills']; $Service_Name=$side_row['Service_Name']; $Service_Name_fills=$side_row['Service_Name_fills'];
			  $Pick_Up_Point=$side_row['Pick_Up_Point']; $Pick_Up_Point_fills=$side_row['Pick_Up_Point_fills']; $Tours=$side_row['Tours']; $Tours_fills=$side_row['Tours_fills'];
 

 

$textHeader=  '<table width="250"  >
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
			$arrival_sqls="SELECT * FROM   file_entry WHERE service_type='Arrival' AND file_count_no = '$file_count_no' ORDER BY file_id DESC LIMIT 1";
			$arrival_results=mysql_query($arrival_sqls);
			$arrival_row=mysql_fetch_array($arrival_results);
			$service_typeArrival =$arrival_row['service_type'];
			$title=$arrival_row['title']; 	$last_name=$arrival_row['last_name']; 	$first_name=$arrival_row['first_name'];
			$serviceArrivalEng=$arrival_row['service'];
			
			
			$client_name= $title .' '.$last_name.' '.$first_name;
			$flight_timeArrivalSQL=$arrival_row['flight_time'];
			$flight_timeArrival = substr($flight_timeArrivalSQL,0,5); 
			
			


$now=$flight_timeArrivalSQL; $dateCon = DateTime::createFromFormat('H:i:s', $now);
$date1Strat = "01:00:00"; $date1End = "05:59:00"; $date1from = DateTime::createFromFormat('H:i:s', $date1Strat); $date1to = DateTime::createFromFormat('H:i:s', $date1End);
$date2Strat = "06:00:00"; $date2End = "11:59:00";$date2from = DateTime::createFromFormat('H:i:s', $date2Strat);$date2to = DateTime::createFromFormat('H:i:s', $date2End);
$date3Strat = "12:00:00"; $date3End = "14:59:00"; $date3from = DateTime::createFromFormat('H:i:s', $date3Strat);$date3to = DateTime::createFromFormat('H:i:s', $date3End);
$date4Strat = "15:00:00"; $date4End = "17:59:00"; $date4from = DateTime::createFromFormat('H:i:s', $date4Strat); $date4to = DateTime::createFromFormat('H:i:s', $date4End);
$date5Strat = "18:00:00"; $date5End = "23:59:00"; $date5from = DateTime::createFromFormat('H:i:s', $date5Strat);$date5to = DateTime::createFromFormat('H:i:s', $date5End);
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
			
			$departure_sqls="SELECT * FROM   file_entry WHERE service_type='Departure' AND file_count_no = '$file_count_no' ORDER BY file_id ASC LIMIT 1";
			$departure_results=mysql_query($departure_sqls);
			$departure_row=mysql_fetch_array($departure_results);
			$service_typeDeparture =$departure_row['service_type'];
			
			
			
			$flight_timeDepartureSQL=$departure_row['flight_time'];
			$flight_timeDeparture = substr($flight_timeDepartureSQL,0,5); 
			
			$now=$flight_timeDepartureSQL; $dateCon = DateTime::createFromFormat('H:i:s', $now);
$date1Strat = "01:00:00"; $date1End = "05:59:00"; $date1from = DateTime::createFromFormat('H:i:s', $date1Strat); $date1to = DateTime::createFromFormat('H:i:s', $date1End);
$date2Strat = "06:00:00"; $date2End = "11:59:00";$date2from = DateTime::createFromFormat('H:i:s', $date2Strat);$date2to = DateTime::createFromFormat('H:i:s', $date2End);
$date3Strat = "12:00:00"; $date3End = "14:59:00"; $date3from = DateTime::createFromFormat('H:i:s', $date3Strat);$date3to = DateTime::createFromFormat('H:i:s', $date3End);
$date4Strat = "15:00:00"; $date4End = "17:59:00"; $date4from = DateTime::createFromFormat('H:i:s', $date4Strat); $date4to = DateTime::createFromFormat('H:i:s', $date4End);
$date5Strat = "18:00:00"; $date5End = "23:59:00"; $date5from = DateTime::createFromFormat('H:i:s', $date5Strat);$date5to = DateTime::createFromFormat('H:i:s', $date5End);
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
    <td  align="center" colspan="2">'. $ref_noAll.'</td>
  </tr>
  <tr>
    <td width="84" align="center">'. $service_dateArrival.'</td>
    <td width="84" align="center">'.$flight_timeArrival.'</td>
    <td width="84" align="center">'.$flight_noArrival.'</td>
    <td width="174" align="center">'.$service_typeArrival.' : '. $Arrival_fills.'</td>
  
  </tr>
  <tr>
    <td align="center">'. $service_dateDeparture.'</td>
    <td align="center">'.$flight_timeDeparture.'</td>
    <td align="center">'.$flight_noDeparture.'</td>
    <td align="center" width="174">'. $service_typeDeparture.' : '. $Departure_fills.'</td>

  </tr>
</table>
<table  height="136" border="1" cellpadding="5" cellspacing="0">
  <tr>
    <td colspan="4" align="center"><h2>'. $Transfers_fills .'</h2></td>
  </tr>
  <tr>
    <td width="116"  align="center">'.$arabic_timeArrival . '  ' . date("g:i", strtotime($flight_timeArrival)).'</td>
    <td width="128"  align="center">'. $Location_name_arabicLocationArrival.'</td>
    <td width="271" align="center">'.$service_name_arabicServiceArrival.'</td>
    <td width="84" align="center"  width="123">'.$service_dateArrival.'</td>
  </tr>
  <tr>
    <td align="center">'.$arabic_timeDeparture. '  ' .date("g:i", strtotime($flight_timeDeparture)).'</td>
    <td align="center">'.$Location_name_arabicLocationDeparture.'</td>
    <td align="center">'.$service_name_arabicServiceDeparture.'</td>
    <td align="center"  width="123" >'.$service_dateDeparture.'</td>
  </tr>
</table>'; 

$loop=1;

$loop_header ='
<table   border="1" cellpadding="1" cellspacing="0">
  <tr>
    <td colspan="4" align="center" height="30"><h2>'. $Tours_fills.'</h2></td>
  </tr>';
 $query_select = "SELECT * FROM  file_entry WHERE   file_count_no ='$file_count_no' AND (service_type='Overland' OR service_type='Tour')";
		$Num=1;
		$result_select = mysql_query($query_select) ;
		$rows = array();
		while($row = mysql_fetch_array($result_select))
    	$rows[] = $row;
	
			foreach($rows as $row){
			
		
		
			
	
$date1Strat = "01:00:00"; $date1End = "05:59:00"; $date1from = DateTime::createFromFormat('H:i:s', $date1Strat); $date1to = DateTime::createFromFormat('H:i:s', $date1End);
$date2Strat = "06:00:00"; $date2End = "11:59:00";$date2from = DateTime::createFromFormat('H:i:s', $date2Strat);$date2to = DateTime::createFromFormat('H:i:s', $date2End);
$date3Strat = "12:00:00"; $date3End = "14:59:00"; $date3from = DateTime::createFromFormat('H:i:s', $date3Strat);$date3to = DateTime::createFromFormat('H:i:s', $date3End);
$date4Strat = "15:00:00"; $date4End = "17:59:00"; $date4from = DateTime::createFromFormat('H:i:s', $date4Strat); $date4to = DateTime::createFromFormat('H:i:s', $date4End);
$date5Strat = "18:00:00"; $date5End = "23:59:00"; $date5from = DateTime::createFromFormat('H:i:s', $date5Strat);$date5to = DateTime::createFromFormat('H:i:s', $date5End);
if ($dateCon >= $date1from && $dateCon <= $date1to){ $arabic_timeloop=$aa_fills;   } elseif ($dateCon >= $date2from && $dateCon <= $date2to){ $arabic_timeloop=$bb_fills;   }
elseif ($dateCon >= $date3from && $dateCon <= $date3to){ $arabic_timeloop=$cc_fills;   } elseif ($dateCon >= $date4from && $dateCon <= $date4to){ $arabic_timeloop=$dd_fills;   }
elseif ($dateCon >= $date5from && $dateCon <= $date5to){ $arabic_timeloop=$ee_fills;   }


			$flight_noloop=$row['flight_no']; 	$pickup_timeloopSQL=$row['pickup_time']; 	
			$pickup_fromloop=$row['pickup_from']; 	$drop_offloop=$row['drop_off'];		
			
					$now=$pickup_timeloopSQL; $dateCon = DateTime::createFromFormat('H:i:s', $now);
				$flight_timeloopSQL=$row['flight_time'];
			$flight_timeloop = substr($flight_timeloopSQL,0,5); 
			
			$service_dateloopSQL=$row['service_date'];
			$service_dateloop=date('d-M-Y',strtotime($service_dateloopSQL));
			
			$serviceloopEng=$row['service'];
// <----------------------- getting Service Name Arabic ---------->
   			$Serviceloop_sqls="SELECT * FROM   service WHERE  service_name_english  = '$serviceloopEng'";
			$Serviceloop_results=mysql_query($Serviceloop_sqls);
			$Serviceloop_row=mysql_fetch_array($Serviceloop_results);
			$service_name_arabicServiceloop =$Serviceloop_row['service_name_arabic'];
			$to_locaionServiceloop =$Serviceloop_row['to_locaion'];	
            $from_locaionServiceloop =$Serviceloop_row['from_locaion'];
// <----------------------- getting Location Name Arabic ---------->
			$Locationloop_sqls="SELECT * FROM   location WHERE  location_name  = '$from_locaionServiceloop'";
			$Locationloop_results=mysql_query($Locationloop_sqls);
			$Locationloop_row=mysql_fetch_array($Locationloop_results);
			$Location_name_arabicLocationloop =$Locationloop_row['location_name_arb'];
			
			
  
$loop_body_part ='  <tr>
    <td width="116"  height="38" align="center" >'. $arabic_timeloop. '  ' .date("g:i", strtotime($flight_timeloop)) .'</td>
    <td width="128" align="center">'.$Location_name_arabicLocationloop.'</td>
    <td width="271" align="center">'.$service_name_arabicServiceloop.'</td>
    <td  width="123" align="center">'. $service_dateloop.'</td>
  </tr>';
  if($loop==1){$loop_body=$loop_body_part;} else {$loop_body=$loop_body.$loop_body_part;}
  $loop++;
   }
$loop_footer ='</table>'; 

$loop_table=$loop_header.$loop_body .$loop_footer;


$html2='
<table width="649" height="206" border="1" cellpadding="5" cellspacing="0">
  
  <tr>
    <td width="639" colspan="4"><table>
      <tr>
        <td width="459"><strong>Package inclusive of :- </strong>'.$remarks.'</td>
      </tr>
      <tr>
        <td>- Return Transfers Private in Destinations stated above</td>
      </tr>
      <tr>
        <td>- Accommodation with daily breakfasts EXCEPT ROOMS WITH NO BREAKFAST INCLUDED</td>
      </tr>
      <tr>
        <td>- Domestic Air Ticket </td>
      </tr>
      <tr>
        <td>- Complimentary SIM Card on First Arrival at Kuala Lumpur Airport.</td>
      </tr>
      <tr>
        <td>- 24/7 Customer Service Phone Desk (English /Arabic) Languages</td>
      </tr>
      <tr>
        <td>- Arabic Full Itinerary &amp; Complimentary Arabic Booklet Guide about Touristic Places.</td>
      </tr>
      <tr>
        <td>- Rate inclusive of service charge and Tax</td>
      </tr>
      <tr>
        <td>-<span style="color:red;"> Air Fare Rate subject to change and Addationl Charges Applied if Fare is Higher upon Booking</span></td>
      </tr>
    </table></td>
  </tr>
</table>';



				

				

				
$html4pageFooter='<table>
				<tr><td><strong>Package inclusive of :- </strong>'.$remarks.'</td></tr>
				<tr><td>- Return Transfers Private in Destinations stated above</td></tr>
				<tr><td>- Accommodation with daily breakfasts EXCEPT ROOMS WITH NO BREAKFAST INCLUDED</td></tr>
				<tr><td>- Domestic Air Ticket </td></tr>
				<tr><td>- Complimentary SIM Card on First Arrival at Kuala Lumpur Airport.</td></tr>
				<tr><td>- 24/7 Customer Service Phone Desk (English /Arabic) Languages</td></tr>
				<tr><td>- Arabic Full Itinerary & Complimentary Arabic Booklet Guide about Touristic Places.</td></tr>
				<tr><td>- Rate inclusive of service charge and Tax</td></tr>
				<tr><td>-<span style="color:red;"> Air Fare Rate subject to change and Addationl Charges Applied if Fare is Higher upon Booking</span></td></tr>

				</table>';
				


$pdf->writeHTML($html1, true, 0, true, 0);

$pdf->writeHTML($loop_table, true, 0, true, 0);


//$pdf->writeHTML($html4Account, true, 0, true, 0); 

$pdf->writeHTML($html2, true, 0, true, 0); 
// output the HTML content

//$pdf->writeHTML($html2, true, 0, true, 0);
//$pdf->writeHTML($html2, true, 0, true, 0);
//$pdf->writeHTML($html2, true, 0, true, 0);
$stamp = date("Y-m-d") ;
//Create Folder Start

if (!file_exists('../../pdf/'.$file_count_no)) {
    mkdir('../../pdf/'.$file_count_no, 0777, true);}
	
//Create Folder End
 
	//
// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------
date_default_timezone_set("Asia/Kuala_Lumpur"); 
//Close and output PDF document
$pdf->Output($file_count_no.'example_021.pdf', 'I');
//$pdf->Output('../../pdf/'.$file_count_no.'/services_'.$stamp.'-'.$file_no.'_'.$supplier_name_came.'.pdf', 'F');

$attachment_name='../../pdf/'.$file_count_no.'/services_'.$stamp.'-'.$file_no.'_'.$supplier_name_came.'.pdf';

if(strlen($file_count_no) < 1 ) {echo " File Name not correct . <br />"; }
else{$sql="INSERT INTO  file_attachment_services (file_attachment_type, file_count_no, supplier_ajent_name, attachment_name) 	VALUES ('Services', '$file_count_no','$supplier_name_came', '$attachment_name')";	 }

if (!mysql_query($sql,$connection)){  echo "error".mysql_error(). "<br/>"; }
 else { /* echo "Data is already saved";  echo '<script type="text/javascript"><!-- window.location = "file_booking_preview.php?entry=new"//--></script>';*/}


//============================================================+
// END OF FILE
//============================================================+

?>

