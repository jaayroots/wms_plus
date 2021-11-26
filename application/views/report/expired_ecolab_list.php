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
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/expiredToExcel_ecolab")
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
<!-- <input type="hidden" name="renter_id" id="renter_id" value="<?php echo $search['renter_id'];?>">
<input type="hidden" name="warehouse_id" id="warehouse_id" value="<?php echo $search['warehouse_id'];?>">
<input type="hidden" name="category_id" id="category_id" value="<?php echo $search['category_id'];?>"> -->
<input type="hidden" name="product_id" id="product_id" value="<?php echo $search['product_id'];?>">
<input type="hidden" name="product_status" id="product_status" value="<?php echo $search['product_status'];?>">
<!-- <input type="hidden" name="as_date" id="as_date" value="<?php echo $search['as_date'];?>">
<input type="hidden" name="period" id="period" value="<?php echo $search['period'];?>">
<input type="hidden" name="step" id="step" value="<?php echo $search['step'];?>"> -->
<!-- <input type="hidden" name="by" id="by" value="<?php echo $search_by;?>"> -->
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
			<th  width="100" class="border-top"><?php echo _lang('Receipt Date');?></th>
			<th  width="100" class="border-top"><?php echo _lang('aging');?></th>
			<th  width="100" class="border-top"><?php echo _lang('Receipt_No');?></th>
			<th  width="100" class="border-top"><?php echo _lang('product_code');?></th>
			<th  width="100" class="border-top"><?php echo _lang('Part_Name');?></th>
			<th  width="100" class="border-top"><?php echo _lang('lot');?></th>
			<th  width="100" class="border-top"><?php echo _lang('Remain_Shelf_life');?></th>
			<th  width="100" class="border-top"><?php echo _lang('Remain_Product_life');?></th>
			<th  width="100" class="border-top"><?php echo _lang('MFD_DATE');?></th>
			<th  width="100" class="border-top"><?php echo _lang('expired_date');?></th>
			<th  width="100" class="border-top"><?php echo _lang('shelf_life');?></th>
			<th  width="200" class="border-top"><?php echo _lang('Life');?></th>
			<th  width="200" class="border-top"><?php echo _lang('invoice_no');?></th>
			<th  width="200" class="border-top"><?php echo _lang('Begin_Qty');?></th>
			<th  width="200" class="border-top"><?php echo _lang('qty');?></th>
			<th  width="200" class="border-top"><?php echo _lang('Location_Alias');?></th>
			<th  width="200" class="border-top"><?php echo _lang('SubInventoryName');?></th>
			<th  width="200" class="border-top"><?php echo _lang('Product Life Status');?></th>

		</tr>
	
	</thead>
	<tbody>
	<?php foreach ($data as $key => $list) {

		$no = $key+1;
		echo '<tr>';
		echo '<td >'.$no.'</td>';
		echo '<td >'.$list->Receive_Date.'</td>';
		echo '<td >'.$list->Aging.'</td>';
		echo '<td >'.$list->Pallet_Code.'</td>';
		echo '<td >'.$list->Product_Code.'</td>';
		echo '<td >'.$list->Product_NameEN.'</td>';
		echo '<td >'.$list->Product_Lot.'</td>';
		echo '<td >'.$list->Remain_Shelf_life.'</td>';
		echo '<td >'.$list->Remain_Product_life.'</td>';
		echo '<td >'.$list->MFD_Date.'</td>';
		echo '<td >'.$list->EXP_DATE.'</td>';
		echo '<td >'.$list->Shelf_life.'</td>';
		echo '<td >'.$list->Life.'</td>';
		echo '<td >'.$list->INVOICENO.'</td>';
		echo '<td >'.$list->BeginQty.'</td>';
		echo '<td >'.$list->BalQty.'</td>';
		echo '<td >'.$list->Location_Alias.'</td>';
		echo '<td >'.$list->SubInventoryName.'</td>';
		echo '<td >'.$list->Product_Life_Status.'</td>';
		echo '</tr>';
	}?>


		<?php
		
	    ?>
		</tbody>
	
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