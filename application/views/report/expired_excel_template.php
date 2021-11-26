<!--//            --#ISSUE 2237 / #Defect 300/330/336 
    //            --#DATE:2012-09-02
    //            --#BY:KIK
    //            --#ปัญหา:1. แสดงวันหมดอายุของ product ไม่ถูกต้อง 2.report จะต้องแสดงข้อมูลตั้งแต่ receive approve 3.ต้องแสดง Lot/Serial
    //            --#สาเหตุ:เขียน sql เพื่อ query ข้อมูลได้ไม่สมบูรณ์ และการแสดงผลผิดพลาด
    //            --#วิธีการแก้:เขียน sql ใหม่ทั้งหมด โดยดึงโครงสร้างมาจากของเดิม เพื่อให้ใช้งานได้อย่างถูกต้อง และแสดงผลให้ถูกต้อง

    //            -- START New Code ISSUE 2237 / #Defect 300/330/336-->


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
    
    <tr bgcolor="#CCCCCC">
        <?php
            if(count($header) > 0 ){

                $j=1;
                foreach ($header as $head) {		
                        echo "<td><b><font face=\"Tahoma\" size=2>".$head."</font></b></td>";
                        $j++;
                }
            }
        ?>
    </tr>
    
<?php
if(count($body) > 0):
	$i=1;
	foreach($body as $key => $row): ?>
    
		<?  foreach($row as $cols):  ?>
		<tr>
                        <td width="40"><font face=\"Tahoma\" size=2><?php echo $i;?></font></td>	
                        <td width="140"><font face=\"Tahoma\" size=2><?php echo $cols['Product_Code'];?></font></td>
			<td><font face=\"Tahoma\" size=2><?php echo $cols['Product_NameEN'];?></font></td>
			<td><font face=\"Tahoma\" size=2><?=(($cols['Product_Lot'] != " ")?$cols['Product_Lot']:"-")?>/<?=(($cols['Product_Serial'] != " ")?$cols['Product_Serial']:"-")?></font></td>
                        <td><font face=\"Tahoma\" size=2><?php echo $cols['Warehouse'];?></font></td>
			<td><font face=\"Tahoma\" size=2><?php echo $cols['Zone'];?></font></td>
			<td><font face=\"Tahoma\" size=2><?php echo $cols['Location_Code'];?></font></td>
			<td style='mso-number-format:"Short Date"'><font face=\"Tahoma\" size=2><?php echo $cols['Product_Exp'];?></font></td>
			<td align="right"><font face=\"Tahoma\" size=2><?php echo $cols['Remain_day'];?></font></td>
			<td><font face=\"Tahoma\" size=2><?php echo $cols['Pallet_Code'];?></font></td>
                        <td  align="right"><font face=\"Tahoma\" size=2><?php echo set_number_format($cols['Balance']);?></font></td>
		</tr>
    
                <?  $i++; endforeach;	
	endforeach;
endif;
?>

</TABLE>
</BODY>
</HTML>
    
    
<!--    //           -- END New Code Defect ISSUE 2237  / #Defect 300/330/336
        //           -- START Old Comment Code ISSUE 2237 / #Defect 300/330/336-->


<?/*php
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
<tr bgcolor="#CCCCCC">
<?php
if(count($header) > 0 ){
	
	$j=1;
	foreach ($header as $head) {		
		echo "<td><b><font face=\"Tahoma\" size=2>".$head."</font></b></td>";
		$j++;
	}
	//echo '<td><b><font face=\"Tahoma\" size=2>Total</font></b></td>';
   /*
	if(count($body)>0){
		foreach($body as $aColumns){
			echo "<tr>";
			foreach($aColumns as $value){
				echo "<td><font face=\"Tahoma\" size=2>".$value."</font></td>";
			}
			echo "</tr>";
		}
	}
	
}
?>
<!--</tr>-->
<?php/*
if(count($body) > 0){
	
	foreach($body as $key => $row){
		
		?>
<!--    #Comment 2013-08-21 Defect ID : 336 , Ak , Should be show raw data for Privot table only. (คอมเม้นต์ทิ้งเพราะไม่ต้องการให้มันแสดงข้อมูลที่ทำมา Merge กัน ต้องการให้แสดงเหมือนในหน้าเว็บ)
    #Start -->
<!--		<tr>
			<td colspan="10" align="center" bgcolor="#EAEAEA" height="30"><font face=\"Tahoma\" size=2><?php echo $key;?></font></td>
		</tr>-->
    <!--# End Comment 2013-08-21 #ISSUE NO/#BUG NO ---->
		<?php
		foreach($row as $cols){	
		?>
		<tr>
			<td width="140"><font face=\"Tahoma\" size=2><?php echo $cols['Product_Code'];?></font></td>
			<td ><font face=\"Tahoma\" size=2><?php echo $cols['Product_NameEN'];?></font></td>
			<!-- <td align="left" width="250"><?php echo $this->conv->tis620_to_utf8($cols->Product_NameEN);?></td> -->
			<td><font face=\"Tahoma\" size=2><?php echo $cols['Product_Lot'];?></font></td>
			<td><font face=\"Tahoma\" size=2><?php echo $cols['Product_Serial'];?></font></td>
			<td><font face=\"Tahoma\" size=2><?php echo $cols['Warehouse'];?></font></td>
			<td><font face=\"Tahoma\" size=2><?php echo $cols['Zone'];?></font></td>
			<td><font face=\"Tahoma\" size=2><?php echo $cols['Location_Code'];?></font></td>
			<td><font face=\"Tahoma\" size=2><?php echo $cols['Product_Exp'];?></font></td>
			<td align="right"><font face=\"Tahoma\" size=2><?php echo $cols['Remain_day'];?></font></td>
			<td  align="right"><font face=\"Tahoma\" size=2><?php echo $cols['Balance'];?></font></td>
		</tr>
	    <?php
		}	
	}
}
?>

</TABLE>
</BODY>
</HTML>
    
    
    */?>

<!--    //            -- END Old Comment Code Defect ISSUE 2237 / #Defect 300/330/336-->