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
			if($aColumns->Reserv_Qty!="" && $aColumns->Confirm_Qty!=""){
				//$variat=ceil((($aColumns->Reserv_Qty - $aColumns->Confirm_Qty)/$aColumns->Reserv_Qty)*100); COMMENT BY POR 2013-11-04 
				$variat=(($aColumns->Reserv_Qty-$aColumns->Confirm_Qty)/ (int) $aColumns->Reserv_Qty)*100; //ADD BY POR 2013-11-04 
			}
			else{
				$variat=0;
			}
// Comment By Akkarapol, 19/11/2013, - �����鹵� ��ǹ�ʴ��������ѹ��� �͡���Ш���� ��÷Ѵ Actual_Qty �͡ ���ͧ�ҡ����ͧ�ʴ�����
/* 
			echo  '<tr align="center">
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Actual_Action_Date.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Code.'</font></td>
						<td align="left"><font face=\"Tahoma\" size=2>'.$aColumns->Product_NameEN.'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.number_format((int) $aColumns->Actual_Qty).'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.number_format((int) $aColumns->Reserv_Qty).'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.number_format((int) $aColumns->Confirm_Qty).'</font></td>
						<td align="left"><font face=\"Tahoma\" size=2>'.$aColumns->Count_By.'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.number_format($variat,2,".",",").'</font></td>
					</tr>
					';
*/
echo  '<tr align="center">
						<td style="mso-number-format:\'Short Date\';"><font face=\"Tahoma\" size=2>'.$aColumns->Actual_Action_Date.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Code.'</font></td>
						<td align="left"><font face=\"Tahoma\" size=2>'.$aColumns->Product_NameEN.'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.set_number_format($aColumns->Reserv_Qty).'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.set_number_format($aColumns->Confirm_Qty).'</font></td>
						<td align="left"><font face=\"Tahoma\" size=2>'.$aColumns->Count_By.'</font></td>
						<td align="right"><font face=\"Tahoma\" size=2>'.set_number_format($variat).'</font></td>
					</tr>
					';
			//echo "</tr>";
		}
	}
}
?>
</tr>