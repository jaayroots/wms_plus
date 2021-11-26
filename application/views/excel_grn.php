<?php
header("Content-Type: application/vnd.ms-excel");
header('Content-Disposition: attachment; filename="'.$file_name.'.xls"');# �������
header("Pragma: no-cache");
header("Expires: 0");
set_time_limit(5000);
// function checkIsAValidDate($myDateString){
//     $myDateString = str_replace('/', '-', $myDateString);
//     return (bool)strtotime($myDateString);
// }
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

	if(count($body)>0){
		foreach($body as $key => $value){ 
                    $i = 1;

					
			echo "<tr>";
//                        p($aColumns);
	echo "<td style='mso-number-format:\"Short Date\";'><font face=\"Tahoma\" size=2>".$value['0']->Estimate_Action_Date."</font></td>";
	echo "<td><font face=\"Tahoma\" size=2>".$value['0']->Doc_Refer_Ext."</font></td>";
	echo "<td><font face=\"Tahoma\" size=2>".$value['0']->Document_No."</font></td>";
	echo "<td><font face=\"Tahoma\" size=2>".$value['0']->Doc_Refer_Int."</font></td>";
	echo "<td><font face=\"Tahoma\" size=2>".$value['0']->supplier."</font></td>";
	echo "<td><font face=\"Tahoma\" size=2>".$value['0']->Product_Code."</font></td>";
	echo "<td><font face=\"Tahoma\" size=2>".$value['0']->Product_NameEN."</font></td>";
	echo "<td align=\"right\"><font face=\"Tahoma\" size=2>".set_number_format($value['reserv_quan'])."</font></td>";
	echo "<td style='mso-number-format:\"Short Date\";'><font face=\"Tahoma\" size=2>".@$value['0']->Actual_Action_Date."</font></td>";
	echo "<td style='mso-number-format:\"Short Date\";'><font face=\"Tahoma\" size=2>".@$value['0']->Pending_Date."</font></td>";
	echo "<td align=\"right\"><font face=\"Tahoma\" size=2>".set_number_format(@$value['receive_quantity'])."</font></td>";
	echo "<td><font face=\"Tahoma\" size=2>".@$value['remark_data']."</font></td>"; // Original echo "<td><font face=\"Tahoma\" size=2>".($value['0']->Activity_Code == "RECEIVE" ? @$value['remark_data'] : "")."</font></td>";                    
/*			foreach($aColumns as  $value){
                            $i++;
                            // check condition show data : kik
                            // key 1-7 show data for approve step prerecive  (in step putaway)
                            // ker 8-11 show data for approve strp recive (in step putaway)
                            if($i<12){
                                echo "<td><font face=\"Tahoma\" size=2>".$value."</font></td>";
                            }
			}*/
			echo "</tr>";
		}
	}
}
?>
</tr>