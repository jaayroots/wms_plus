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

<?php
if(count($body) > 0 ){
	//foreach ($header as $head) {
		//echo "<td><b><font face=\"Tahoma\" size=2>".$head."</font></b></td>";
		echo '<tr>
			<th rowspan="2" ><b><font face=\"Tahoma\" size=2>'._lang('product_code').'</font></b></th>
			<th rowspan="2"><b><font face=\"Tahoma\" size=2>'._lang('product_name').'</font></b></th>
			<th colspan="7"><b><font face=\"Tahoma\" size=2>'._lang('shelf_life').'</font></b></th>
			<th rowspan="2"><b><font face=\"Tahoma\" size=2>'._lang('total').'</font></b></th>
		</tr>
		<tr>
						<th><b><font face=\"Tahoma\" size=2>3M</font></b></th>
                        <th><b><font face=\"Tahoma\" size=2>6M</font></b></th>
                        <th><b><font face=\"Tahoma\" size=2>9M</font></b></th>
                        <th><b><font face=\"Tahoma\" size=2>1Y</font></b></th>
                        <th><b><font face=\"Tahoma\" size=2>1.5Y</font></b></th>
                        <th><b><font face=\"Tahoma\" size=2>2Y</font></b></th>
                        <th><b><font face=\"Tahoma\" size=2>>2Y</font></b></th>
		</tr>';
	//}
    //p($body);
	if(count($body)>0){
		foreach($body as $aColumns){
			//echo "<tr>";
			$color='';
			$aColumns->total = $aColumns->threeMonth+$aColumns->sixMonth+$aColumns->nineMonth+$aColumns->oneYear+$aColumns->oneHalfYear+$aColumns->twoYear+$aColumns->moreTwoYear;
                    
			echo  '<tr>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Code.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_NameEN.'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.set_number_format($aColumns->threeMonth).'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.  set_number_format($aColumns->sixMonth).'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.set_number_format($aColumns->nineMonth).'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.set_number_format($aColumns->oneYear).'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.set_number_format($aColumns->oneHalfYear).'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2 >'.set_number_format($aColumns->twoYear).'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2 >'.set_number_format($aColumns->moreTwoYear).'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.set_number_format($aColumns->total).'</font></td>
						
					</tr>
					';
			//echo "</tr>";
		}
	}
}
?>