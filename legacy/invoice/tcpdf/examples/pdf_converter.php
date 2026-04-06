<?php  
  require("../../../../connect/db_con.php") ;
if (isset($_GET['file_count_no'])) {$file_count_no = $_GET['file_count_no'];	}
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
require_once('tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('');
$pdf->SetTitle('within Earth');
$pdf->SetSubject('');
$pdf->SetKeywords('');
define ('K_PATH_IMAGES', '/images/');
$pdf->SetHeaderData("tcpdf_logo.jpg", 60, "", "  ");

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

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', '', 9);

// add a page
$pdf->AddPage();
//$isComing='hi thisi s coming test';
//$supplier_name_came='Texi hub';

// geting date from datebase
//$html3 =  'Krishna Kumar Jha'.$isComing ;
//$html2 =  'Murari Kumar Jha Kumar Jha'.$isComing ;

		 $resultsHeader = mysql_query("SELECT * FROM  file_entry WHERE  file_count_no ='$file_count_no' AND supplier_name='$supplier_name_came' ORDER BY  file_id  DESC LIMIT 1");
		$rowysHeader = mysql_fetch_array($resultsHeader);
		$agent_name = $rowysHeader['agent_name']; 
		$supplier_name=$rowysHeader['supplier_name'];
		$file_no=$rowysHeader['file_no'];
		$name=$rowysHeader['last_name'].' '.$rowysHeader['first_name'];
		$pax_mobile=$rowysHeader['pax_mobile'];
		
		$html=  '<table width="400" border="1" cellpadding="5">
		<tr><td colspan="2" align="center"><h2>Transfer Booking Request</h2></td></tr>
		<tr><td><h3>Pax Name :</h3></td><td>'.$name.'</td></tr>
		<tr><td><h3>Supplier Name:</h3></td><td>'.$supplier_name.'</td></tr>
		<tr><td ><h3>File No :</h3></td><td>'.$file_no.'</td></tr>
		<tr><td><h3>Pax Mobile Ref:</h3></td><td>'.$pax_mobile.'</td></tr>
		</table>';

	$pdf->writeHTML($html, true, 0, true, 0);
$query_select = "SELECT * FROM  file_entry WHERE   file_count_no ='$file_count_no' AND supplier_name='$supplier_name_came'";
		$Num=1;
				$result_select = mysql_query($query_select) or die(mysql_error());
				$rows = array();
				while($row = mysql_fetch_array($result_select))
    			$rows[] = $row;
	
			foreach($rows as $row){
			
			$file_id=$row['file_id'];

		// HEADING FOR THE BOOKING	 START
			

			
					// HEADING FOR THE BOOKING	 END
					
										// SERVICES DETAILS  START

				$service_type=$row['service_type']; $from_location=$row['from_location']; $from_country=$row['from_country'];	
				$from_zone=$row['from_zone'];	$to_location=$row['to_location']; $to_zone=$row['to_zone']; $service=$row['service'];
				$service_date=$row['service_date'];	$vehicle_type= $row["vehicle_type"];	$adults= $row['adults']; $children= $row['children'];	$no_of_pax= $row['no_of_pax'];
				$flight_time = $row['flight_time'];		$flight_no = $row['flight_no'];	$remarks= $row['remarks'];
				
				$flight_time_part=explode(':', $flight_time); $flight_time_joint=$flight_time_part[0].':'.$flight_time_part[1];
						if ($from_zone==''){ $pickupDisplay= $from_location;} else{ $pickupDisplay= $from_zone;}
							if ($to_zone==''){ $DropoffDisplay= $to_location;} else{ $DropoffDisplay= $to_zone;}
						
				$html4=  '<table  border="1" cellpadding="5">
				<tr><td colspan="4" align="left"><h4>Service '.$Num++.'</h4></td></tr>
				<tr><td><h3>Service Name :</h3></td><td colspan="2">'.$service.'</td><td align="left"></td><td></td></tr>
				<tr><td><h3>Pick up from:</h3></td><td>'.$pickupDisplay.'</td><td align="left"><h3>Drop off :</h3></td><td>'.$DropoffDisplay.'</td></tr>
				<tr><td ><h3>Service Date :</h3></td><td colspan="3">'.$service_date.'</td></tr>
				<tr><td><h3>Vechile Type:</h3></td><td>'.$vehicle_type.'</td><td align="left"><h3>No of Pax:</h3></td><td>'.$no_of_pax.'</td></tr>
				<tr><td><h3>Fligh No:</h3></td><td>'.$flight_no.'</td><td align="left"><h3>Flight Time:</h3></td><td>'.$flight_time_joint. '</td></tr>
				<tr><td ><h3>Remark:</h3></td><td colspan="4">'.$remarks.'</td></tr>
				</table>';
													// SERVICES DETAILS  START
														
														//WRITING IN PDF START
			
				$pdf->writeHTML($html4, true, 0, true, 0);
				
																		//WRITING IN PDF END
						}
					

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
//$pdf->Output($file_count_no.'example_021.pdf', 'I');
$pdf->Output('../../pdf/'.$file_count_no.'/services_'.$stamp.'-'.$file_no.'_'.$supplier_name_came.'.pdf', 'F');

$attachment_name='../../pdf/'.$file_count_no.'/services_'.$stamp.'-'.$file_no.'_'.$supplier_name_came.'.pdf';

if(strlen($file_count_no) < 1 ) {echo " File Name not correct . <br />"; }
else{$sql="INSERT INTO  file_attachment_services (file_attachment_type, file_count_no, supplier_ajent_name, attachment_name) 	VALUES ('Services', '$file_count_no','$supplier_name_came', '$attachment_name')";	 }

if (!mysql_query($sql,$connection)){  echo "error".mysql_error(). "<br/>"; }
 else { /* echo "Data is already saved";  echo '<script type="text/javascript"><!-- window.location = "file_booking_preview.php?entry=new"//--></script>';*/}


//============================================================+
// END OF FILE
//============================================================+

?>

