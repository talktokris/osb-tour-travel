<?php  
session_start();

require("../../../../connect/db_con.php") ;
if (isset($_GET['statement_agent'])) {$tour_came = $_GET['statement_agent'];
$tour_came_part=explode('|', $tour_came);


$search_word=$tour_came_part[0]; // $b=$search_word_part[1];
$from_date=$tour_came_part[1];
$to_date=$tour_came_part[2];

$from_date_came=$from_date;
$from_date_part=explode('-',$from_date_came);
$from_date_dis=$from_date_part[2].'-'.$from_date_part[1].'-'.$from_date_part[0];


$to_date_came=$to_date;
$to_date_part=explode('-',$to_date_came);
$to_date_dis=$to_date_part[2].'-'.$to_date_part[1].'-'.$to_date_part[0];

	}

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
$pdf->SetHeaderData(__DIR__ . "/images/within_earth.png", 60, "", "  ");

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
$pdf->SetFont('helvetica', '', 7);

// add a page
//$pdf->AddPage();

$pdf->AddPage('L', 'A4');
$osbLogo = __DIR__ . '/images/within_earth.png';
if (is_file($osbLogo)) {
	$pdf->Image($osbLogo, 10, 10, 45, 0, 'PNG');
}
//$isComing='hi thisi s coming test';
//$supplier_name_came='Texi hub';

// geting date from datebase
//$html3 =  'Krishna Kumar Jha'.$isComing ;
//$html2 =  'Murari Kumar Jha Kumar Jha'.$isComing ;
		

//////////Paf Start Karma ///////////////

/*$header_table='<table>
<tr><td colspan="2" align="center"><h2>Statement Agent Report.</h2> </td></tr>
<tr><td width="60"><strong>Agent Name  </strong></td><td>: '.$search_word.'</td></tr>
<tr><td><strong>From Date  </strong></td><td>: '.$from_date.'</td></tr>
<tr><td><strong>To Date  </strong></td><td>: '.$to_date.'</td></tr>
</table>';*/
///////////////////
$resultsHeaderadd = mysql_query("SELECT * FROM  agent WHERE agent_name = '$search_word' ORDER BY agent_id DESC LIMIT 1");
		$resultsHeaderadd = mysql_fetch_array($resultsHeaderadd);

		$agent_address 	= $resultsHeaderadd['agent_address'];

$header_table='<table>
<table width="945" border="1" bgcolor="#cccccc">
  <tr>
    <td  align="center"><h2>Statement Agent Report</h2> '.$from_date_dis.' to '.$to_date_dis.'</td>
  </tr>
  <tr>
    <td width="70"><strong>Agent Name </strong></td>
    <td width="875">  '.$search_word.'</td>
  </tr>
  <tr>
    <td><strong>Address </strong></td>
    <td>  '.$agent_address.'</td>
  </tr>
  <tr>
    <td><strong>Currency </strong></td>
    <td> Ringgit Malaysia</td>
  </tr>
</table>';
///////////////////

  $total=1;



$pdf->writeHTML($header_table, true, 0, true, 0);
$Num=1;


// Define $color=1


$rowHeader= '<table  border="1"  cellpadding="4"><tr bgcolor="#cccccc"><td>S.N</td><td>Invoice No.</td><td>Issue Date</td><td width= "50">Due Date</td><td>Service Date</td><td width= "75">Guest Name</td><td width= "100">Description</td><td width= "30">Qty</td><td width= "50">Price</td><td>Item Amount</td><td>Total Invoice</td><td width= "50">Paid</td><td>Balance</td><td>Acc. Status</td><td>Status</td><td>UserCreate</td></tr>';

$query_select = "SELECT  *  FROM file_entry WHERE  agent_name  = '$search_word' AND service_date BETWEEN '$from_date' AND '$to_date' AND conform_status = 'Confirmed' ORDER BY service_date DESC";
		$Num=1;
				$result_select = mysql_query($query_select) ;
				$rows = array();
				while($row = mysql_fetch_array($result_select))
    			$rows[] = $row;
	
			foreach($rows as $row){
$file_count_no=$row['file_count_no'];
$resultsHeaderinv = mysql_query("SELECT * FROM  invoices WHERE agent_supplier_name = '$search_word' ORDER BY Invoices_id DESC LIMIT 1");
							$rowysHeaderinv = mysql_fetch_array($resultsHeaderinv);
													
												
							$Invoices_id 	= $rowysHeaderinv['Invoices_id'];
							//$file_count_no 	= $rowysHeadeinv['file_count_no'];
							$file_no 	= $rowysHeaderinv['file_no'];
							$ref_no 	= $rowysHeaderinv['ref_no'];
							$invoice_no 	= $rowysHeaderinv['invoice_no'];
							$invoice_type 	= $rowysHeaderinv['invoice_type'];
							$agent_supplier_name = $rowysHeaderinv['agent_supplier_name'];	
							$item 	= $rowysHeaderinv['item'];
							$buying_price 	= $rowysHeaderinv['buying_price'];
							$selling_price 	= $rowysHeaderinv['selling_price'];
							$total_price 	= $rowysHeaderinv['total_price'];
							//$invoice_create_date = $rowysHeaderinv['invoice_create_date'];
							$invoice_create_date= date('d-m-Y', strtotime($rowysHeaderinv['invoice_create_date']));	
							$paid_date 	= $rowysHeaderinv['paid_date'];
							$cheque_no 	= $rowysHeaderinv['cheque_no'];
							$paid_amount 	= $rowysHeaderinv['paid_amount'];
							$balance_amount 	= $rowysHeaderinv['balance_amount'];
							$paid_status = $rowysHeaderinv['paid_status'];
							
							//echo 'Invoice - '.$Invoices_id;
								
if ($balance_amount==0) { $paid_totatl_dispay= $total_price ;} else {  $paid_totatl_dispay=  $total_price-$balance_amount ;} 

if ($total==1){

$paid_totatl_dispay_count=$paid_totatl_dispay;
$total_price_count=$total_price;
$balance_amount_count=$balance_amount;

} else {
$paid_totatl_dispay_count=$paid_totatl_dispay_count+$paid_totatl_dispay; 
$total_price_count=$total_price_count+$total_price;
$balance_amount_count=$balance_amount_count+$balance_amount;

 }

   
$lo_from=$row['from_location']; $lo_to=$row['to_location'];
$zo_from=$row['from_zone']; $zo_to=$row['to_zone'];

if($zo_from==''){$from_location=$lo_from;}else{$from_location=$lo_from;}
if($zo_to==''){$to_location=$zo_from;}else{$to_location=$zo_to;}

$rowOnePart= "<tr height ='30'>

<td>".$Num++."</td><td>". $Invoices_id."</td><td>".$invoice_create_date."</td><td></td><td>".$row['service_date']."</td><td>".$row['first_name']. " " .$row['last_name']."</td><td>".$row['from_location']. " -" .$row['to_location']."</td><td>".$row['no_of_pax']."</td><td>".$selling_price."</td><td>".$total_price ."</td><td>".$total_price ."</td><td>".$paid_totatl_dispay ."</td><td>".$balance_amount ."</td><td>".$rowysHeaderinv['paid_status']."</td><td>".$row['conform_status']."</td><td>".$row['user_enter_by']."</td> 

</tr>";



	if($Num==1){$rowOne=$rowOnePart;} else {$rowOne=$rowOne.$rowOnePart;}	
	
	$total++;
}
$Num++;

$rowFooter= '<tr><td colspan="10" align = "right">Total</td><td>'.$total_price_count.'</td><td>'.$paid_totatl_dispay_count.'</td><td>'.$balance_amount_count.'</td><td></td><td></td><td></td></tr>
</table>';
$joint_html=$rowHeader.$rowOne.$rowFooter;
//$joint_html=$rowHeader.$rowOne.$rowFooter;

//5.close connect

//////////Paf End Karma ///////////////



$pdf->writeHTML($joint_html, true, 0, true, 0);


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

