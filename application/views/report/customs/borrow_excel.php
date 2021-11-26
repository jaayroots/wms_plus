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
    <tr><td colspan=14><b>"._lang("cus_renter_name")." : </b>".$renter_name."</td></tr>
    <tr><td colspan=\"14\"><b>"._lang("cus_custom_doc_ref")." : </b>".$custom_doc_ref."</td></tr>
    <tr><td colspan=\"14\"><b>"._lang("cus_from_date")." : </b>" . $fdate . " " . nameMonthENG($fmonth) . " " . ($fyear + 543) .  "&nbsp;&nbsp;&nbsp;&nbsp;<b>"._lang("cus_to_date")." : </b>" . $tdate . " " . nameMonthENG($tmonth) . " " . ($tyear + 543) .  "</td></tr>
";

        
if(count($header) > 0 ){
        echo "<tr>";
	foreach ($header as $head) {
		echo "<td><b><font face=\"Tahoma\" size=2>".$head."</font></b></td>";
	}
        echo "</tr>";

        if(!empty($body)):
            foreach ($body as $key => $datas):             
                foreach ($datas as $keys => $value):
                    $confirm_qty = set_number_format($value["Confirm_Qty"]);
                    $all_price = set_number_format($value["all_price"]);
                    $remain_qty = set_number_format($value["remain_qty"]);
                    $remark = $value["Remark"];

                    if(empty($value["out_date"])):
                        $num="";
                        $confirm_qty = "";
                        $all_price = "";
                        $remain_qty = "";
                        $remark = "";
                    endif;

                    $import_Receive_Qty = set_number_format($value["import_Receive_Qty"]);
                    $import_All_Price = set_number_format($value["import_All_Price"]);
                    if(empty($value["Inbound_Id_His"])):
                        $import_Receive_Qty = "";
                        $import_All_Price = "";
                    endif;
                                    
                    ?>
                   <tr style="height: 30px;" valign="top">
                        <td><?php echo $value["out_date"]; ?></td>
                        <td align="left"><?php echo $value["Custom_Doc_Ref"]; ?></td>
                        <td><?php echo $value["Product_Code"]; ?></td>
                        <td align="left"><?php echo $value["Product_Name"]; ?></td>
                        <td align="right"><?php echo $confirm_qty; ?></td>
                        <td align="right"><?php echo $all_price; ?></td>

                        <td><?php echo $value["import_date"]; ?></td>
                        <td><?php echo $value["import_Product_Code"]; ?></td>
                        <td align="left" ><?php echo $value["import_Product_Name"]; ?></td>
                        <td align="right" ><?php echo $import_Receive_Qty; ?></td>
                        <td align="right" ><?php echo $import_All_Price; ?></td>     
                        <td align="right" ><?php echo $value["date_diff"]; ?></td>
                        <td valign="top" align="right" ><?php echo $remain_qty; ?></td>
                        <td align="left" valign="top" ><?php echo $remark; ?></td>
                    </tr>
                    <?php        			
		endforeach;
            endforeach;
	endif;
}
?>
