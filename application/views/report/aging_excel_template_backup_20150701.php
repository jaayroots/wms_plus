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
<tr bgcolor="#CCCCCC" height="35">
<?php
if(count($header) > 0 ){
	echo '<td><b><font face=\"Tahoma\" size=2>'._lang('no').'</font></b></td>
            <td><b><font face=\"Tahoma\" size=2>'._lang('product_code').'</font></b></td>
            <td><b><font face=\"Tahoma\" size=2>'._lang('product_name').'</font></b></td>
            <td><b><font face=\"Tahoma\" size=2>'._lang('product_status').'</font></b></td>
            <td><b><font face=\"Tahoma\" size=2>'._lang('lot').'</font></b></td>
            <td><b><font face=\"Tahoma\" size=2>'._lang('serial').'</font></b></td>
            <td><b><font face=\"Tahoma\" size=2>'._lang('warehouse').'</font></b></td>
            <td><b><font face=\"Tahoma\" size=2>'._lang('zone').'</font></b></td>
            <td><b><font face=\"Tahoma\" size=2>'._lang('location').'</font></b></td>';
        
	$sum_cols=array();
	$j=1;
	foreach ($header as $head) {
		if(!array_key_exists($j, $sum_cols)){
			$sum_cols[$j]=0;
		}
		echo "<td><b><font face=\"Tahoma\" size=2>".$head." ".$search_by."</font></b></td>";
		$j++;
	}
}
?>
</tr>
<?php
if(count($body) > 0){
	$group_col=count($header);
	//p($group_col);
		$i=1;
	//p($body);
		$sum_all=0;
		foreach($body as $key => $row):                        
			if(($i%2)!=0):
				$bg=' style="background:#D3D6FF;"';
			else:
				$bg=' style="background:#EAEBFF;"';
			endif;
                        
			$gc=7+$group_col;
		 
			foreach($row as $cols):
                            $html_data = "";
                            $html_data .= "<tr>";
                            $html_data .= "<td width=\"50\"><font face=\"Tahoma\" size=2>$i</font></td>";
                            $html_data .= "<td ><font face=\"Tahoma\" size=2>$cols[Product_Code]</font></td>";
                            $html_data .= "<td align=\"left\" width=\"250\"><font face=\"Tahoma\" size=2>$cols[Product_NameEN]</font></td>";
                            $html_data .= "<td align=\"left\" ><font face=\"Tahoma\" size=2>$cols[Product_Status]</font></td>";
                            $html_data .= "<td align=\"left\" ><font face=\"Tahoma\" size=2>$cols[Product_Lot]</font></td>";
                            $html_data .= "<td align=\"left\" ><font face=\"Tahoma\" size=2>$cols[Product_Serial]</font></td>";
                            $html_data .= "<td align=\"left\" ><font face=\"Tahoma\" size=2>$cols[Warehouse]</font></td>";
                            $html_data .= "<td align=\"left\" ><font face=\"Tahoma\" size=2>$cols[Zone]</font></td>";
                            $html_data .= "<td align=\"left\" ><font face=\"Tahoma\" size=2>$cols[Location_Code]</font></td>";
                           
                            $sum_row=0;
                            $name='count';
                            for($j=1;$j<=$group_col;$j++):
                                $sum_row+=$cols["count_".$j];
                                $html_data .= "<td  align=\"right\"><font face=\"Tahoma\" size=2>".set_number_format($cols["count_".$j])."</font></td>";
                            endfor;
                            
                            $html_data .= "</tr>";

                            $sum_all+=$sum_row;
                            $i++;
			endforeach;
                        
                        if ($sum_row != 0) : //ให้แสดงเฉพาะ balance >0
                            echo $html_data;
                        endif;
                        
                endforeach;
	 
}
?>

</TABLE>
</BODY>
</HTML>