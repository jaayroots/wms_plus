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
			$color='';
                        
                        $td_pallet_code = '';
                        if($this->config->item('build_pallet')):
                            $td_pallet_code = '<td><font face=\"Tahoma\" size=2>'.$aColumns->Pallet_Code.'</font></td>';
                        endif;
                        
                                                //ADD BY POR 2014-06-18 เพิ่มเติม price per unit

	

                        $price_per_unit = '';
                        if($statusprice):
                            $price_per_unit = '
                                <td><font face=\"Tahoma\" size=2>'.set_number_format($aColumns->Price_Per_Unit).'</font></td>
                                <td><font face=\"Tahoma\" size=2>'.$aColumns->Unit_price.'</font></td>
                                <td><font face=\"Tahoma\" size=2>'.set_number_format($aColumns->All_price).'</font></td>
                                ';  
                        endif;   
                        //END ADD

                        
			if($aColumns->Suggest_Location_Id!=$aColumns->Actual_Location_Id){
						$color='color="#FF0000"';
					}
			echo  '<tr>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Act_Date.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Document_No.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Doc_Refer_Int.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Doc_Refer_Ext.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Code.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_NameEN.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Status.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Lot.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Product_Serial.'</font></td>
						<td style="text-align:right;"><font face=\"Tahoma\" size=2>'.set_number_format($aColumns->qty).'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Unit_Value.'</font></td>'
                                                .$price_per_unit.
						'<td style="text-align:center;"><font face=\"Tahoma\" size=2 '.$color.'>'.$aColumns->Suggest_Location_Id.'</font></td>
						<td style="text-align:center;"><font face=\"Tahoma\" size=2  '.$color.'>'.$aColumns->Actual_Location_Id.'</font></td>
						<td><font face=\"Tahoma\" size=2>'.$aColumns->Put_by.'</font></td>'
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