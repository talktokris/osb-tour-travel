<?php  
session_start();

require("../../../../connect/db_con.php") ;
if (isset($_GET['driver'])) {$tour_came = $_GET['driver'];
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
$pdf->SetFont('helvetica', '', 7);

// add a page
//$pdf->AddPage();

$pdf->AddPage('L', 'A4');
//$isComing='hi thisi s coming test';
//$supplier_name_came='Texi hub';

// geting date from datebase
//$html3 =  'Krishna Kumar Jha'.$isComing ;
//$html2 =  'Murari Kumar Jha Kumar Jha'.$isComing ;
		

//////////Paf Start Karma ///////////////

$query_select = "SELECT * FROM service_type";
		$Num=1;
				$result_select = mysql_query($query_select) ;
				$rows = array();
				while($row = mysql_fetch_array($result_select))
    			$rows[] = $row;
	
			foreach($rows as $row){

			$service_type=$row['service_type_name'];
$entry_state_result_conut = mysql_num_rows(mysql_query("SELECT * FROM   file_entry  WHERE driver_name = '$search_word'  AND service_type='$service_type' ORDER BY file_count_no DESC"));

if ($entry_state_result_conut>=1) { 
    $resultsHeader = mysql_query("SELECT  *  FROM file_entry WHERE  driver_name = '$search_word'  AND service_type='$service_type' ORDER BY file_id DESC LIMIT 1");
													$rowysHeader = mysql_fetch_array($resultsHeader);
													$from_city = $rowysHeader['from_city']; 
$loopNo=1;											$service_type = $rowysHeader['service_type'];
$header_table='<table>
<table width="960" border="1" cellpadding="5" >
  <tr>
    <td colspan="15" align="center"><h2>Transfer Report</h2> '.$from_date_dis.' to '.$to_date_dis.'</td>
  </tr>
  <tr>
    <td colspan="15" align="left"><h2>'.$service_type.'</h2> </td>
  </tr>
  <tr>
     <td width="87" align="center">Supplier Name </td>
    <td width="108" align="center">Agent Name</td>
    <td width="50"align="center">File No</td>
    <td width="90"align="center">Client Name</td>
    <td width="57"align="center">Service Date</td>
	<td width="135"align="center">Service Name</td> 
    <td width="35"align="center">Flight Details</td>
    <td width="46"align="center">Flight Time</td>
    <td width="48"align="center">Pick Up Time</td>
    <td width="40"align="center">Pick up</td>
    <td width="51"align="center">Drop off</td>
    <td width="42"align="center">Vehicle Type</td>
    <td width="56"align="center"v>Driver Name</td>
    <td width="61"align="center">PAX SIM NO</td>
    <td width="54"align="center">Tour type&nbsp;&nbsp; Sic/Private</td>
    
  </tr>';
   $entry_state_result =  mysql_query("SELECT  *  FROM file_entry WHERE  driver_name  = '$search_word' AND service_type='$service_type' AND service_date BETWEEN '$from_date' AND '$to_date' ORDER BY service_date DESC");
   
   while($rows=mysql_fetch_array($entry_state_result)){
   $loopNo++;
   $supplier_name=$rows['supplier_name'];$agent_name=$rows['agent_name'];$file_no=$rows['file_no'];
   $first_name=$rows['first_name']." ".$rows['last_name']; $service_date= date('d-m-Y', strtotime($rows['service_date']));
   $service=$rows['service']; $flight_no=$rows['flight_no']; $flight_time=$rows['flight_time'];
   $pickup_time=$rows['pickup_time']; $from_zone=$rows['from_zone']; $to_zone=$rows['to_zone']; $vehicle_type=   $rows['vehicle_type'];$driver_name=$rows['driver_name'];$pax_mobile=$rows['pax_mobile'];$service_cat=$rows['service_cat'];


$lo_from=$rows['from_location']; $lo_to=$rows['to_location'];
$zo_from=$rows['from_zone']; $zo_to=$rows['to_zone'];

if($zo_from==''){$from_location=$lo_from;}else{$from_location=$lo_from;}
if($zo_to==''){$to_location=$zo_from;}else{$to_location=$zo_to;}


 $body_part_table=' <tr>
    <td> '.$supplier_name.'</td>
    <td> '.$agent_name.'</td>
    <td> '.$file_no.'</td>
    <td>'.$first_name.'</td>
    
    <td>'.$service_date.'</td>
    <td>'.$service.'</td>
    <td>'.$flight_no.'</td>
    <td>'.$flight_time.'</td>
    <td>'.$pickup_time.'</td>
    <td>'.$from_location.'</td>
    <td>'.$to_location.'</td>
    <td>'.$vehicle_type.'</td>
    <td>'.$driver_name.'</td>
    <td>'.$pax_mobile.'</td>
	 <td>'.$service_cat.'</td>
  </tr>';
 if ($loopNo===1){$body_table=$body_part_table;} else {$body_table=$body_table.$body_part_table ;}
}

$loopNo=1;
$footer_table='</table>';

////////testtt/////////////////

///////////////////

$joint_table_html=$header_table.$body_table.$footer_table;



$pdf->writeHTML($joint_table_html, true, 0, true, 0);
//$Num=1;
$body_table='';
}

}
// Define $color=1





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

//============================================================+
// END OF FILE
//============================================================+

?>

