<?php  
session_start();

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

$textHeader=  '<table>
<tr><td><h3>OSB Blobal Services Sdn Bhd</h3></td></tr>
<tr><td>Suite B-09-04 ,Block B, Megan Avenue 2 - Jalan Yap Kwan Seng,50450</td></tr>
<tr><td>Kuala Lumpur 50450 Malaysia</td></tr>
<tr><td>Tel : +603 2166 3969, Fax : +603 2166 0418</td></tr>
<tr><td>E-Mail : sales@ossbtrf.com Website : malaysia.onlinewe.net</td></tr>
</table>
';

$pdf->writeHTML($textHeader, true, 0, true, 0);

$file_count_no=866;

		 $resultsHeader = mysql_query("SELECT * FROM  file_entry WHERE  file_count_no ='$file_count_no'  ORDER BY  file_id  DESC LIMIT 1");
		$rowysHeader = mysql_fetch_array($resultsHeader);
		$agent_name = $rowysHeader['agent_name']; 
		$file_no=$rowysHeader['file_no'];
		$name=$rowysHeader['last_name'].' '.$rowysHeader['first_name'];
		$pax_mobile=$rowysHeader['pax_mobile'];
		$no_of_pax=$rowysHeader['no_of_pax'];
		$date=$rowysHeader['date'];
		$ref_no=$rowysHeader['ref_no'];
		
		// find Agent Details Start
		$resultsAgent = mysql_query("SELECT * FROM  agent WHERE  agent_name ='$agent_name'");
		$rowysAgent = mysql_fetch_array($resultsAgent);
		$agent_name = $rowysAgent['agent_name']; 
		$agent_country = $rowysAgent['agent_country']; 
		$agent_city = $rowysAgent['agent_city']; 
		$agent_email = $rowysAgent['agent_email']; 
		$agent_contact_no = $rowysAgent['agent_contact_no']; 
	

			//Find Agent Details End
		
		$html=  '<table  border="0"  cellpadding="5">
		
		<tr><td><strong>INVOICE NO. :</strong></td><td> WEA'.$file_count_no.'</td><td><strong>DATE OF ISSUED :</strong></td><td>'.$date.'</td></tr>
		<tr><td><strong>TAX ID NO. :</strong></td><td></td><td><strong>ISSUED BY :</strong></td><td>'.$_SESSION['myusername'].'</td></tr>
		<tr><td><strong>ORDERED BY. :</strong></td><td></td><td><strong>FILE NO. :</strong></td><td>'.$file_no.'</td></tr>
		<tr><td><strong>O/S Ref. :</strong></td><td>'.$ref_no.'</td><td colspan="2"></td></tr>

		</table>';

	$pdf->writeHTML($html, true, 0, true, 0);
	
	$middleHtml='
	<table>
	<tr><td width="400">
		<table cellpadding="5" style=" border-collapse:collapse; border: 1px solid black; width:500px;" >
	<tr><td width="130"><h3>COMPANY :</h3></td><td><h2>'.$agent_name.'</h2></td></tr>
	<tr><td></td><td> <strong>Country : </strong>'.$agent_country.'<strong> City : </strong>'.$agent_city .'</td></tr>
	<tr><td></td><td><strong> Tel No. : </strong>'.$agent_contact_no.'<strong> Email : </strong>'.$agent_email .'</td></tr>
	<tr><td><h3>Guest NAME :</h3> </td><td>'.$name.'</td></tr>
	</table>
	
	</td><td width="80" valign="middle"><strong>No of Pax :</strong></td><td width="20"  valign="middle">'.$no_of_pax.'</td>
	
	</tr></table>';
	
	
	$html4header=  '<table width="500"  border="1" cellpadding="5" >

<tr><td width="500"><strong>Description</strong></td><td width="50"><strong>Qty</strong></td><td width="50"><strong>Price</strong></td><td width="70"><strong>Amount</strong></td></tr>';	


	
	
$query_select = "SELECT * FROM  file_entry WHERE   file_count_no ='$file_count_no'";
		$Num=1;
				$result_select = mysql_query($query_select) ;
				$rows = array();
				while($row = mysql_fetch_array($result_select))
    			$rows[] = $row;
	
			foreach($rows as $row){
			
			$file_id=$row['file_id'];


				$service_type=$row['service_type']; $from_location=$row['from_location']; $from_country=$row['from_country'];	
				$from_zone=$row['from_zone'];	$to_location=$row['to_location']; $to_zone=$row['to_zone'];
				 $service=$row['service'];
				 
		$resultsServices = mysql_query("SELECT * FROM  service WHERE  service_name_english ='$service'");
		$rowysServices = mysql_fetch_array($resultsServices);
		$buying_price = $rowysServices['buying_price']; 
		$selling_price = $rowysServices['selling_price']; 

				 
				
				$service_date=$row['service_date'];	$vehicle_type= $row["vehicle_type"];	$adults= $row['adults']; $children= $row['children'];	$no_of_pax= $row['no_of_pax'];
				$flight_time = $row['flight_time'];		$flight_no = $row['flight_no'];	$no_of_vachileFoud=$row['no_of_vachile'];
				
				$flight_time_part=explode(':', $flight_time); $flight_time_joint=$flight_time_part[0].':'.$flight_time_part[1];
						if ($from_zone=''){ $pickupDisplay= $from_location;} else{ $pickupDisplay= $from_zone;}
							if ($to_zone=''){ $DropoffDisplay= $to_location;} else{ $DropoffDisplay= $to_zone;}
				if($no_of_vachileFoud==''){$no_of_vachile=1;} else {$no_of_vachile=$no_of_vachileFoud;}
						$selling_price_total=$selling_price*$no_of_vachile; 
						
if($Num==1){$selling_price_total_final=$selling_price_total;} else {$selling_price_total_final=$selling_price_total+$selling_price_total_final;}
						
							if($no_of_vachileFoud==0){$no_of_vachile=1;} else {$no_of_vachile=$no_of_vachileFoud;}
				
$html4bodyPart=  '

<tr><td width="500">'.$service_date.' '.$service.'.'.$vehicle_type.'</td><td width="50">'.$no_of_vachile.'</td><td width="50">'.$selling_price.'</td><td width="70"> USD '.$selling_price_total.'</td></tr>';
							
			if($Num==1){$html4body=$html4bodyPart;} else {$html4body=$html4body.$html4bodyPart;}						

										//WRITING IN PDF START
			
$Num++;
				
																		//WRITING IN PDF END
						}
				$html4footer='</table>';	
				
				$html4total='<table>
				<tr><td width="600" align="right"><strong>TOTAL	USD</strong></td><td align="right" width="50">'. $selling_price_total_final.'</td></tr>
				<tr><td align="right"><strong>PAYMENT RECEIVABLE	USD</strong></td><td  align="right" width="50"> 0</td></tr>
				<tr><td align="right"><strong>OUT STANDING BALANCE	USD</strong></td><td  align="right" width="50">'.$selling_price_total_final.'</td></tr>
				</table>';
				
				$html4Account='<table>
				<tr><td colspan="2">Please refer to above of your term of payment and settle your payment to our bank details below</td></tr>
<tr><td width="80"><strong>A/C Name</strong></td><td>: WITHIN EARTH HOLIDAYS SDN.BHD</td></tr>
<tr><td><strong>Bank Name</strong></td><td>: HSBC BANK</td></tr>
<tr><td><strong>Branch</strong></td><td>: Kuala Lumpur</td></tr>
<tr><td><strong>Account</strong></td><td>: 003 014776 021</td></tr>
<tr><td><strong>Address</strong></td><td>: Kuala Lumpur Malaysia</td></tr>
<tr><td><strong>Swift Code</strong></td><td>: HMABMYKL</td></tr>
				</table>';
				
$html4pageFooter='<table>
				<tr><td colspan="2">Upon banking in the payment, kindly fax us the bank-in-slip for our accounting purposes to avoid unnecessary disputes.
This is a computer generated document, no signature is required.
 </td></tr>
<tr><td width="80"><strong>Handle By : </strong></td><td>: '.$_SESSION['myusername'].'</td></tr>

				</table>';
				
$joint_html=$html4header.$html4body.$html4footer;

			


$pdf->writeHTML($middleHtml, true, 0, true, 0);

$pdf->writeHTML($joint_html, true, 0, true, 0);

$pdf->writeHTML($html4total, true, 0, true, 0);

$pdf->writeHTML($html4Account, true, 0, true, 0); 

$pdf->writeHTML($html4pageFooter, true, 0, true, 0); 
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

