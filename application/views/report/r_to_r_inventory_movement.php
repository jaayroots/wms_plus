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
// p($body); exit;
#Load config (by kik : 20140708)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        
if(count($header) > 0 ){
	foreach ($header as $head) {
		echo '<td><b>'.$head.'</b></td>';
	}

	if(count($body)>0){         
		foreach($body as $key => $rows){     
			// $excel_num = set_number_format($rows['ECO_IN']   
			// p($excel_num)   ; exit;
			$eco_in = (is_numeric(str_replace(",","",$rows['ECO_IN'])) ? 'x:num="'.$rows['ECO_IN'].'"' : '');
			$jwd_in = (is_numeric(str_replace(",","",$rows['JWD_IN'])) ? 'x:num="'.$rows['JWD_IN'].'"' : '');
			$diff_in = (is_numeric(str_replace(",","",$rows['Diff_IN'])) ? 'x:num="'.$rows['Diff_IN'].'"' : '');
			$eco_out = (is_numeric(str_replace(",","",$rows['ECO_OUT'])) ? 'x:num="'.$rows['ECO_OUT'].'"' : '');
			$jwd_out = (is_numeric(str_replace(",","",$rows['JWD_OUT'])) ? 'x:num="'.$rows['JWD_OUT'].'"' : '');
			$diff_out = (is_numeric(str_replace(",","",$rows['Diff_OUT'])) ? 'x:num="'.$rows['Diff_OUT'].'"' : '');		
			$sumdiff = (is_numeric(str_replace(",","",$rows['SumDiff'])) ? 'x:num="'.$rows['SumDiff'].'"' : '');	
			$DAYENDSEQ = (is_numeric(str_replace(",","",$rows['DAYENDSEQ'])) ? 'x:num="'.$rows['DAYENDSEQ'].'"' : '');			
					
			echo  '<tr>
						<td>'.$rows['FirstOfTRANSDATE'].'</td>
						<td>'.$rows['FirstOfECL-TYPE'].'</td>
						<td>'.$rows['DOC-MAIN'].'</td>
						<td>'.$rows['ITEMNO'].'</td>
						<td '.$eco_in.'>'.$rows['ECO_IN'].'</td>
						<td '.$jwd_in.'>'.$rows['JWD_IN'].'</td>
                        <td '.$diff_in.' >'.(float)$rows['Diff_IN'].'</td>
						<td '.$eco_out.'>'.$rows['ECO_OUT'].'</td>
						<td '.$jwd_out.'>'.$rows['JWD_OUT'].'</td>
                        <td '.$diff_out.' >'.(float)$rows['Diff_OUT'].'</td>
						<td '.$sumdiff.' >'.(float)$rows['SumDiff'].'</td>
						<td '.$DAYENDSEQ.' >'.(float)$rows['DAYENDSEQ'].'</td>
					</tr>
                                        ';
                                        
		}
	}
}
?>
</tr>