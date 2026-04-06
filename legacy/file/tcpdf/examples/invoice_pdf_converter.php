<?php  
session_start();

@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

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
$pdf->SetHeaderData("/Applications/XAMPP/xamppfiles/htdocs/projects/withinearth/withinearth_new_travel/new_app_travel/images/within_earth.png", 60, "", "  ");

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
$osbLogo = '/Applications/XAMPP/xamppfiles/htdocs/projects/withinearth/withinearth_new_travel/new_app_travel/images/within_earth.png';
if (is_file($osbLogo)) {
    $pdf->Image($osbLogo, 14, 16, 70, 0, 'PNG');
}
//$isComing='hi thisi s coming test';
//$supplier_name_came='Texi hub';

// geting date from datebase
//$html3 =  'Krishna Kumar Jha'.$isComing ;
//$html2 =  'Murari Kumar Jha Kumar Jha'.$isComing ;







//$file_count_no=866;

		 $resultsHeader = mysql_query("SELECT * FROM  file_entry WHERE  file_count_no ='$file_count_no'  ORDER BY  file_id  DESC LIMIT 1");
		$rowysHeader = mysql_fetch_array($resultsHeader);
		$agent_name = $rowysHeader['agent_name']; 
		$file_no=$rowysHeader['file_no'];
		$name=$rowysHeader['last_name'].' '.$rowysHeader['first_name'].' '.$rowysHeader['title'];
		$pax_mobile=$rowysHeader['pax_mobile'];
		$no_of_pax=$rowysHeader['no_of_pax'];
		//$date=$rowysHeader['date'];
		$date= date('d-m-Y', strtotime($rowysHeader['date']));
		$ref_no=$rowysHeader['ref_no'];
		
		// find Agent Details Start
		$resultsAgent = mysql_query("SELECT * FROM  agent WHERE  agent_name ='$agent_name'");
		$rowysAgent = mysql_fetch_array($resultsAgent);
		$agent_name = $rowysAgent['agent_name']; 
		$agent_country = $rowysAgent['agent_country']; 
		$agent_city = $rowysAgent['agent_city']; 
		$agent_email = $rowysAgent['agent_email']; 
		$agent_contact_no = $rowysAgent['agent_contact_no']; 
		
		 $resultsInvoice = mysql_query("SELECT * FROM  invoices WHERE  file_count_no ='$file_count_no' AND agent_supplier_name ='$agent_name'  ORDER BY  Invoices_id  DESC LIMIT 1");
		$rowysInvoice = mysql_fetch_array($resultsInvoice);
		$Invoices_id = $rowysInvoice['Invoices_id']; 
		$total_price= $rowysInvoice['total_price']; 
		$paid_amount= $rowysInvoice['paid_amount']; 
		$balance_amounts= $rowysInvoice['balance_amount'];
			if($paid_amount>1){$balance_amount=$balance_amounts;} else {$balance_amount=$total_price;} 
		
		$textHeader=  '<table><tr><td>
		<table>
<tr><td><h3>OSB Blobal Services Sdn Bhd</h3></td></tr>
<tr><td>Suite B-09-04 ,Block B, Megan Avenue 2 - Jalan Yap Kwan Seng,50450</td></tr>
<tr><td>Kuala Lumpur 50450 Malaysia</td></tr>
<tr><td>Tel : +603 2166 3969, Fax : +603 2166 0418</td></tr>
<tr><td>E-Mail : sales@ossbtrf.com Website : malaysia.onlinewe.net</td></tr>
</table>

</td><td width="300" align="right"><h1>'.$paid_status.'</h1></td></tr></table>
';
		
$pdf->writeHTML($textHeader, true, 0, true, 0);	

			//Find Agent Details End
		
		$html=  '<table  border="0"  cellpadding="5">
		
		<tr><td><strong>INVOICE NO. :</strong></td><td> '.$Invoices_id.'</td><td><strong>DATE OF ISSUED :</strong></td><td>'.$date.'</td></tr>
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
	<tr><td><h3>GUEST NAME :</h3> </td><td>'.$name.'</td></tr>
	</table>
	
	</td><td width="80" valign="middle"><strong></strong></td><td width="20"  valign="middle"></td>
	
	</tr></table>';
	
	
	$html4header=  '<table width="500"  border="1" cellpadding="5" >

<tr><td width="460"><strong>Description</strong></td><td width="70"><strong>Qty</strong></td><td width="70"><strong>Price</strong></td><td width="70"><strong>Amount</strong></td></tr>';
	


	
	
$query_select = "SELECT * FROM  file_entry WHERE   file_count_no ='$file_count_no' AND conform_status!='Cancel' ORDER BY service_date ASC";
		$Num=1;
				$result_select = mysql_query($query_select) ;
				$rows = array();
				while($row = mysql_fetch_array($result_select))
    			$rows[] = $row;
	
			foreach($rows as $row){
			
			$file_id=$row['file_id'];
$selling_price = $rowysServices['selling_price']; 

				$service_type=$row['service_type']; $from_location=$row['from_location']; $from_country=$row['from_country'];	
				$from_zone=$row['from_zone'];	$to_location=$row['to_location']; $to_zone=$row['to_zone'];
				 $service=$row['service'];
				  $service_id=$row['service_id'];
				   $service_selling_price=$row['selling_price'];
				   $adults=$row['adults'];
				   $children=$row['children'];
				   $no_of_pax=$row['no_of_pax'];
				   $service_cat=$row['service_cat'];	
				    
				  
				 
		$resultsServices = mysql_query("SELECT * FROM  service WHERE  service_id ='$service_id'");
		$rowysServices = mysql_fetch_array($resultsServices);
		$buying_price = $rowysServices['buying_price']; 
		$selling_price = $rowysServices['selling_price']; 
		$sic_children_price= $rowysServices['sic_children_price']; 

				 
				
				$service_date=$row['service_date'];	$vehicle_type= $row["vehicle_type"];	$adults= $row['adults']; $children= $row['children'];	$no_of_pax= $row['no_of_pax'];
				$flight_time = $row['flight_time'];		$flight_no = $row['flight_no'];	$no_of_vachileFoud=$row['no_of_vachile'];
				
				$flight_time_part=explode(':', $flight_time); $flight_time_joint=$flight_time_part[0].':'.$flight_time_part[1];
						if ($from_zone=''){ $pickupDisplay= $from_location;} else{ $pickupDisplay= $from_zone;}
							if ($to_zone=''){ $DropoffDisplay= $to_location;} else{ $DropoffDisplay= $to_zone;}
				if($no_of_vachileFoud==''){$no_of_vachile=1;} else {$no_of_vachile=$no_of_vachileFoud;}
						$selling_price_total=$selling_price*$no_of_vachile; 
						
if($Num==1){$selling_price_total_final=$selling_price_total;} else {$selling_price_total_final=$selling_price_total+$selling_price_total_final;}
						
							if($no_of_vachileFoud==0){$no_of_vachile=1;} else {$no_of_vachile=$no_of_vachileFoud;}
							
	if ($service_cat=="SIC"){ $qty_display= $adults.' + '.$children.' = '.$no_of_pax;} else { $qty_display= $no_of_pax;}
	
	if ($service_cat=="SIC"){ $price_display_show = 'AD '. $selling_price.'<br>CD '.$sic_children_price;}else { $price_display_show= $selling_price;}
				
$html4bodyPart=  '

<tr><td width="460">'.$service_date.' '.$service.'.'.$vehicle_type.'</td><td width="70">'.$qty_display.'</td><td width="70">'.$price_display_show.'</td><td width="70"> RM '.$service_selling_price.'</td></tr>';
							
			if($Num==1){$html4body=$html4bodyPart;} else {$html4body=$html4body.$html4bodyPart;}						

										//WRITING IN PDF START
			
$Num++;
				
																		//WRITING IN PDF END
						}
				$html4footer='</table>';	
				
				$html4total='<table>
				<tr><td width="600" align="right"><strong>TOTAL	MYR</strong></td><td align="right" width="50">'. $total_price.'</td></tr>
				<tr><td align="right"><strong>PAYMENT RECEIVABLE	MYR</strong></td><td  align="right" width="50"> '.$paid_amount.'</td></tr>
				<tr><td align="right"><strong>OUT STANDING BALANCE	MYR</strong></td><td  align="right" width="50">'.$balance_amount.'</td></tr>
				</table>';
				
				// Paid info Ta table
				$paidTableHeader='<table >
				<tr><td>One</td><td>Two</td><td>Three</td><td>Four</td><td>Five</td></tr>';
			/*
		$query_select = "SELECT * FROM  payment WHERE   file_count_no ='$file_count_no' AND agent_supplier_name='$agent_name'";
		$Pay=1;
				$result_select = mysql_query($query_select) ;
				$rows = array();
				while($row = mysql_fetch_array($result_select))
    			$rows[] = $row;
	
			foreach($rows as $row){
			
			$invoice_no=$row['invoice_no'];
			$paid_date=$row['paid_date'];
			$cheque_no=$row['cheque_no'];
			$paid_amount=$row['paid_amount'];

$paid_table_part='<tr><td>'.$invoice_no.'</td><td>'.$paid_date.'</td><td>'.$cheque_no.'</td><td>'.$paid_amount.'</td><td>  </td></tr>';
				
if($Pay==1){$paid_table_body=$paid_table_part;} else {$paid_table_body=$paid_table_body.$paid_table_part;}
				
				}
				
				$paidTableFooter='</table>';
				$paid_table_joint=$paidTableHeader.$paid_table_body.$paidTableFooter;
				*/

				// Paid info Ta table
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
$pdf->writeHTML($paid_table_joint, true, 0, true, 0);
$pdf->writeHTML($html4Account, true, 0, true, 0); 

$pdf->writeHTML($html4pageFooter, true, 0, true, 0); 
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
if (ob_get_length()) {
    @ob_clean();
}
$pdf->Output($file_count_no.'invoice.pdf', 'I');
//$pdf->Output('../../pdf/'.$file_count_no.'/services_'.$stamp.'-'.$file_no.'_'.$supplier_name_came.'.pdf', 'F');
/*
$attachment_name='../../pdf/'.$file_count_no.'/services_'.$stamp.'-'.$file_no.'_'.$supplier_name_came.'.pdf';

if(strlen($file_count_no) < 1 ) {echo " File Name not correct . <br />"; }
else{$sql="INSERT INTO  file_attachment_services (file_attachment_type, file_count_no, supplier_ajent_name, attachment_name) 	VALUES ('Services', '$file_count_no','$supplier_name_came', '$attachment_name')";	 }

if (!mysql_query($sql,$connection)){  echo "error".mysql_error(). "<br/>"; }
 else { /* echo "Data is already saved";  echo '<script type="text/javascript"><!-- window.location = "file_booking_preview.php?entry=new"//--></script>';/}

*/
//============================================================+
// END OF FILE
//============================================================+

?>

