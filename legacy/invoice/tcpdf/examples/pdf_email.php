<?php  
  require("../../../../connect/db_con.php") ;
if (isset($_GET['file_count_no'])) {$file_count_no = $_GET['file_count_no'];	}



// geting date from datebase
//$html3 =  'Krishna Kumar Jha'.$isComing ;
//$html2 =  'Murari Kumar Jha Kumar Jha'.$isComing ;
// $entry_state_result_conut = mysql_num_rows(mysql_query("SELECT Distinct  file_count_no  FROM file_entry WHERE service_date BETWEEN '$from_date' AND '$to_date' ORDER BY service_date DESC"));

$query_select = "SELECT Distinct supplier_name FROM  file_entry WHERE   file_count_no ='$file_count_no'";
	
				$result_select = mysql_query($query_select) or die(mysql_error());
				$rows = array();
				while($row = mysql_fetch_array($result_select))
    			$rows[] = $row;

			foreach($rows as $row){
			$supplier_name_came=$row['supplier_name'];

			
			include("pdf_converter.php");

						}
	
echo '<script type="text/javascript">
window.location = "../../send_email.php?file_count_no='.$file_count_no.'" ;
</script>';

?>

