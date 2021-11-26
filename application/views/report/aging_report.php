<?php
 $column_name=array_keys($range);
 $group_col=count($column_name);
?>

<script>
    $(document).ready(function() {
       //#2236 แก้ไขกรณีที่ next page ทำงานไม่ถูกต้อง เนื่องจากเดิมไม่ได้ใส่ส่วนนี้ เลยทำให้แสดงผิดพลาด
       //#DATE:2013-09-05
       //#BY:POR
        $('#tbreport').dataTable({
			"bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "sPaginationType": "full_numbers",
            
            //ADD BY POR 2013-11-18 sort ตัวเลขให้ถูกต้อง (CREDIT BY AKK)
            "sSortDataType": "dom-text", 
            "sType": "numeric-comma"
            //END ADD
            
//Comment BY POR 2013-10-08 แก้ไขให้แสดงแบ่งเพจถูกต้อง 			
//                ,"aoColumns": [
//                {"sWidth": "50px;","sClass": "center"},
//                {"sWidth": "110px;","sClass": "center"},
//                {"sWidth": "450px;","sClass": "left"},
//				{},
//                                {},
//				{},
//                                {},
//                                {},
//                                {},
//                                {},
//				{}
//				]
//END COMMENT
        });
    });

	function exportFile(file_type){
		if(file_type=='EXCEL'){
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportAgingToExcel")
		}else{
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportAgingPdf")
		}
		$("#form_report").submit();
    }
</script>
<?php
if(count($data)==0){
	echo 'no result'; //เปลี่ยนเป็นภาษาอังกฤษ เนื่องจากใช้ภาษาไทยแล้วมีปัญหา #ISSUE 2236  by kik | 2012-09-02
}
else{
?>
<form  method="post" action="" target="_blank" id="form_report">
<input type="hidden" name="renter_id" id="renter_id" value="<?php echo $search['renter_id'];?>">
<input type="hidden" name="warehouse_id" id="warehouse_id" value="<?php echo $search['warehouse_id'];?>">
<input type="hidden" name="category_id" id="category_id" value="<?php echo $search['category_id'];?>">
<input type="hidden" name="product_id" id="product_id" value="<?php echo $search['product_id'];?>">
<input type="hidden" name="as_date" id="as_date" value="<?php echo $search['as_date'];?>">
<input type="hidden" name="period" id="period" value="<?php echo $search['period'];?>">
<input type="hidden" name="step" id="step" value="<?php echo $search['step'];?>">
<input type="hidden" name="by" id="by" value="<?php echo $search_by;?>">
<table id="tbreport"  cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
	<thead>
		<!--
		<tr>
			<th rowspan="2" width="70" class="border-top">No.</th>
			<th rowspan="2" width="100" class="border-top">Product Code</th>
			<th rowspan="2" width="200" class="border-top">Product Name</th>
			<?php
			  $column_name=array_keys($range);
			  $group_col=count($column_name);
			?>
			 <th  colspan="<?php echo $group_col;?>" class="border-top">Remain Days</th>
			 <th rowspan="2" class="border-top">Total</th>
		</tr>
		-->
		<tr>
		
			<th  class="border-top"><?php echo _lang('no');?></th>
                        <th  width="100" class="border-top"><?php echo _lang('product_code');?></th>
			<th  width="200" class="border-top"><?php echo _lang('product_name');?></th>
			<?php
			$sum_cols=array();
				$j=1;
			  foreach($column_name as $col){
				  if(!array_key_exists($j, $sum_cols)){
						$sum_cols[$j]=0;
					}
			?>
			 <th class="line-bottom"><?php echo $col;?> <?php echo $search_by;?></th>
			<?php 
					$j++;
			  }
			?>
			<th class="border-top" >Total</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i=1;
		
		$sum_all=0;
                
		foreach($data as $cols):
                    
                    $html_data = "";
                    $html_data .= "<tr><td width=\"50\">" . $i . "</td>";	
                    $html_data .= "<td >" . $cols->Product_Code . "</td>";
                    $html_data .= "<td style=\"text-align: left;\" width=\"250\">" . $cols->Product_NameEN . "</td>";
                    
                    if(($i%2)!=0){
			$bg=' style="background:#D3D6FF;text-align: right;"';
                    }
                    else{
                        $bg=' style="background:#EAEBFF;text-align: right;"';
                    }

                    $sum_row=0;
                    $name='counts';
                    for($j=1;$j<=$group_col;$j++):
					
                            $sum_row+=$cols->{$name."_".$j};

                            $sum_cols[$j]+=$cols->{$name."_".$j};
                            
                            $value_cols=$cols->{$name."_".$j}!=0?set_number_format($cols->{$name."_".$j}):''; //ADD BY POR 2014-03-19 เพิ่มให้ตรวจสอบว่า ถ้ามีค่าเท่ากับ 0 ไม่ต้องแสดงค่า
                            $html_data .= "<td style=\"text-align: right;\">" . $value_cols . "</td>";
                     endfor;
                     
                    $html_data .= "<td style=\"text-align: right;\">" . set_number_format($sum_row) . "</td>";
                    $html_data .= "</tr>";
                    $sum_all+=$sum_row;
                    
                    
                    if ($sum_row != 0) : //ให้แสดงเฉพาะ balance >0
                        $i++;
                       echo $html_data;
                    endif;

                endforeach;

	    ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="3">Total</th>
				<?php
				foreach($sum_cols as $sc){
				?>
				<th style="text-align: right;"><?php echo set_number_format($sc);?></th>
				<?php 
				}
				?>
				<th style="text-align: right;"><?php echo set_number_format($sum_all);?></th>
			</tr>
		</tfoot>
</table>
<!--COMMENT BY POR 2013-11-05 ยกเลิกการใช้ปุ่มแสดง report หน้านี้ แต่ไป ให้ไปแสดงใน workflow_template แทน
<div align="center" style="margin-top:10px;">
        <input type="button" value="Export To PDF" class="button orange" onClick="exportFile('PDF')"  />
         &emsp;&emsp;
        <input type="button" value="Export To Excel" class="button orange" onClick="exportFile('EXCEL')" />
</div>
-->
</form>
<?php } ?>