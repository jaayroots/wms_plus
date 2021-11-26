<?php
header("Content-Type: application/vnd.ms-excel");
header('Content-Disposition: attachment; filename="'.$file_name.'.xls"');# �������
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
if(count($header) > 0 ){
	foreach ($header as $head) {
		echo "<td><b><font face=\"Tahoma\" size=2>".$head."</font></b></td>";
	}
    //p($body);
	if(count($body)>0){
		foreach($body as $aColumns){

			//echo "<tr>";
	
						$qty = (is_numeric(str_replace(",","",$aColumns->Dispatch_Qty)) ? 'x:num="'.$aColumns->Dispatch_Qty.'"' : '');
                        $td_pallet_code = '';
                        if($this->config->item('build_pallet')):
                            $td_pallet_code = '<td><font face=\"Tahoma\" size=2>'.$aColumns->Pallet_Code.'</font></td>';
                        endif;
			echo  '<tr>
						<td style="mso-number-format:\'Short Date\';"><font face=\"Tahoma\" size=2>'.$aColumns->Dispatch_Date.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Document_No.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Doc_Refer_Ext.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Doc_Refer_Int.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Code.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$this->conv->tis620_to_utf8($aColumns->Product_Name).'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Lot.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.date("d/m/Y", strtotime($aColumns->Product_Mfd)).'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Serial.'</font></td>
						<td align="right" '.$qty.' ><font face=\"Tahoma\" size=2>'.$aColumns->Dispatch_Qty.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Unit_Value.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->From_sup.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Destination_Code.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$this->conv->tis620_to_utf8($aColumns->Destination_Name).'</font></td>'
						.$td_pallet_code.
						'<td><font face=\"Tahoma\" size=2>'.$aColumns->Remark.'</font></td>
					</tr>
					';
			//echo "</tr>";
		}
	}
}
?>
</tr>
