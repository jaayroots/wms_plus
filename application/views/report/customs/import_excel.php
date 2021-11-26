<?php
header("Content-Type: application/vnd.ms-excel");
header('Content-Disposition: attachment; filename="'.$file_name.'.xls"');
header("Pragma: no-cache");
header("Expires: 0");
set_time_limit(5000);
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
<HTML>
<HEAD>
<meta http-equiv="Content-type" content="text/html;charset=tis-620" />
</HEAD>
<BODY>
<TABLE  x:str BORDER="1">
<?php
list($fdate, $fmonth, $fyear) = explode("/", $from_date);
list($tdate, $tmonth, $tyear) = explode("/", $to_date);

echo "
    <tr><td colspan=9><b>"._lang("cus_renter_name")." : </b>".$renter_name."</td></tr>
    <tr><td colspan=\"9\"><b>"._lang("cus_customs_entry")." : </b>".$customs_entry."</td></tr>
    <tr><td colspan=\"9\"><b>"._lang("cus_ior")." : </b>".$ior."</td></tr>
    <tr><td colspan=\"9\"><b>"._lang("cus_invoice")." : </b>".$invoice."</td></tr>
    <tr><td colspan=\"9\"><b>"._lang("cus_from_date")." : </b>" . $fdate . " " . nameMonthENG($fmonth) . " " . ($fyear + 543) .  "&nbsp;&nbsp;&nbsp;&nbsp;<b>"._lang("cus_to_date")." : </b>" . $tdate . " " . nameMonthENG($tmonth) . " " . ($tyear + 543) .  "</td></tr>
";

        
if(count($header) > 0 ){
        echo "<tr>";
	foreach ($header as $head) {
		echo "<td><b><font face=\"Tahoma\" size=2>".$head."</font></b></td>";
	}
        echo "</tr>";

	if(count($body)>0){
		foreach($body as $aColumns){
               
                    foreach ($aColumns as $key => $value) {
                        ?>
                        <tr>
                            <td><?php echo $value["Doc_Refer_CE"];?></td>
                            <td><?php echo $value["Vendor_Name"];?></td>
                            <td><?php echo $value["Invoice_No"]; ?></td>
                            <td><?php echo $value["Receive_Date"]; ?></td>
                            <td><?php echo $value["Product_Code"]; ?></td>
                            <td align="left"><?php echo $value["Product_NameEN"]; ?></td>
                            <td align="right"><?php echo set_number_format($value["Receive_Qty"]); ?></td>
                            <td><?php echo $value["unit"]; ?></td>
                            <td align="right"><?php echo set_number_format($value["price"])." ".$value["unit_price"]; ?></td> 
                        </tr>
                        <?php
                     }
                            			
		}
	}
}
?>
