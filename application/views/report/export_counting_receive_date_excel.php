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
<tr>
<?php
//p($body);exit;
if(count($header) > 0 ){
	foreach ($header as $head) {
		echo "<td><b><font face=\"Tahoma\" size=2>".$head."</font></b></td>";
	}
//    p($body);
	if(count($body)>0){
		foreach($body as $rows){
			
            $Balance_Qty   = (is_numeric(str_replace(",","",$rows->Balance_Qty)) ? 'x:num="'.$rows->Balance_Qty.'"' : '');
			echo  '<tr>

                            <td><font face=\"Tahoma\" size=2>'.$rows->Receive_Date.'</font></td>
                            <td><font face=\"Tahoma\" size=2>'.$rows->Doc_Refer_Ext.'</font></td>
                            <td><font face=\"Tahoma\" size=2>'.$rows->Product_Code.'</font></td>
                            <td><font face=\"Tahoma\" size=2>'.$rows->Product_NameEN.'</font></td>
                            <td><font face=\"Tahoma\" size=2>'.$rows->Product_Lot.'</font></td>
                            <td><font face=\"Tahoma\" size=2>'.$rows->Product_Mfd.'</font></td>
                            <td><font face=\"Tahoma\" size=2>'.$rows->Product_Exp.'</font></td>
                            <td><font face=\"Tahoma\" size=2>'.$rows->Pallet_Code.'</font></td>
                            <td><font face=\"Tahoma\" size=2>'.$rows->Location_Code.'</font></td>
                            <td '.$Balance_Qty.'>'.$rows->Balance_Qty.'</td>
                            <td><font face=\"Tahoma\" size=2>'.$rows->uom.'</font></td>

		
					</tr>';
			//echo "</tr>";
		}
		
	}
}
?>
</tr>


