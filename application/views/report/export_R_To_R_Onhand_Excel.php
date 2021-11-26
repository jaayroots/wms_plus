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
    //p($body);
	if(count($body)>0){
		foreach($body as $rows){
			
			// $Item_No               = (is_numeric(str_replace(",","",$rows['Item No'])) ? 'x:num="'.$rows['Item No'].'"' : '');
			// $Item_Description      = (is_numeric(str_replace(",","",$rows['Item Description'])) ? 'x:num="'.$rows['Item Description'].'"' : '');
			// $Quantity_On_Hand      = (is_numeric(str_replace(",","",$rows['Quantity On Hand'])) ? 'x:num="'.$rows['Quantity On Hand'].'"' : '');
			// $Stocking_Unit         = (is_numeric(str_replace(",","",$rows['Stocking Unit'])) ? 'x:num="'.$rows['Stocking Unit'].'"' : '');
			$JWD_20                = (is_numeric(str_replace(",","",$rows['20-JWD'])) ? 'x:num="'.$rows['20-JWD'].'"' : '');
			$NEC_Bangpakong_88     = (is_numeric(str_replace(",","",$rows['88-NEC-Bangpakong'])) ? 'x:num="'.$rows['88-NEC-Bangpakong'].'"' : '');
			$REWORK_AND_ON_HOLD_92 = (is_numeric(str_replace(",","",$rows['92-REWORK AND ON HOLD'])) ? 'x:num="'.$rows['92-REWORK AND ON HOLD'].'"' : '');
			$OBSOLETE_99           = (is_numeric(str_replace(",","",$rows['99-OBSOLETE'])) ? 'x:num="'.$rows['99-OBSOLETE'].'"' : '');
			$LOC_94_ON_HAND        = (is_numeric(str_replace(",","",$rows['LOC_94_ON_HAND'])) ? 'x:num="'.$rows['LOC_94_ON_HAND'].'"' : '');
			$JWD                   = (is_numeric(str_replace(",","",$rows['JWD'])) ? 'x:num="'.$rows['JWD'].'"' : '');
			$Diff                  = (is_numeric(str_replace(",","",$rows['Diff'])) ? 'x:num="'.$rows['Diff'].'"' : '');

			// p($rows);exit;
		
			echo  '<tr>

						<td><font face=\"Tahoma\" size=2>'.$rows['Item No'].'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$rows['Item Description'].'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$rows['Quantity On Hand'].'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$rows['Stocking Unit'].'</font></td>
						<td '.$JWD_20.'>'.$rows['20-JWD'].'</td>
						<td '.$NEC_Bangpakong_88.'>'.$rows['88-NEC-Bangpakong'].'</td>
						<td '.$REWORK_AND_ON_HOLD_92.'>'.$rows['92-REWORK AND ON HOLD'].'</td>
						<td '.$OBSOLETE_99.'>'.$rows['99-OBSOLETE'].'</td>
						<td '.$LOC_94_ON_HAND.'>'.$rows['LOC_94_ON_HAND'].'</td>
						<td '.$JWD.'>'.$rows['JWD'].'</td>
						<td '.$Diff.'>'.$rows['Diff'].'</td>
					
		
					</tr>';
			//echo "</tr>";
		}
		//p($rows);exit;
	}
}
?>
</tr>