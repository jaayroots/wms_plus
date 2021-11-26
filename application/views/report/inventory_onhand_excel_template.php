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
    // p($body); exit;
	if(count($body)>0){
        $sum_balance_all = 0;
		foreach($body as $key => $aColumns){

			//echo "<tr>";
                        $no = $key+1;
						$qty = (is_numeric(str_replace(",","",$aColumns->remain)) ? 'x:num="'.$aColumns->remain.'"' : '');
                        $td_pallet_code = '';
                        if($this->config->item('build_pallet')):
                            $td_pallet_code = '<td><font face=\"Tahoma\" size=2>'.$aColumns->Pallet_Code.'</font></td>';
                        endif;

                        $sum_qty_all+=$aColumns->remain;
                        
                        $sum = (is_numeric(str_replace(",","",$sum_qty_all)) ? 'x:num="'.$sum_qty_all.'"' : '');
		echo  '<tr>
                        <td><font face=\"Tahoma\" size=2>'.$no.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Doc_Refer_Ext.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Receive_Date.'</font></td>
                        <td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Code.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$this->conv->tis620_to_utf8($aColumns->Product_NameEN).'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Lot.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Mfd.'</font></td>
                        <td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Exp.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Status.'</font></td>
                        <td><font face=\"Tahoma\" size=2>'.$aColumns->Prod_Type.'</font></td>
                        <td><font face=\"Tahoma\" size=2>'.$aColumns->location.'</font></td>
						<td align="right" '.$qty.' ><font face=\"Tahoma\" size=2>'.$aColumns->remain.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Pallet_Code.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Remark.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Unit_Value.'</font></td>
                        <td><font face=\"Tahoma\" size=2>'.$aColumns->Doc_Refer_Int.'</font></td>
					</tr>
					';
			//echo "</tr>";
		}
        echo  '<tr>
        <td><font face=\"Tahoma\" size=2>Total</font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td align="right" '.$sum.' ><font face=\"Tahoma\" size=2>'.$sum_qty_all.'</font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
        <td><font face=\"Tahoma\" size=2></font></td>
    </tr>
    ';
	}
}
?>
</tr>
