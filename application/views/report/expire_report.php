<script>
    $(document).ready(function() {
        $('#tbreport').dataTable({
			"bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
			"sPaginationType": "full_numbers",
			// Add By Ball
	        /*"bProcessing": true,
	        "bServerSide": true,
	        "sAjaxSource": "<?php echo site_url(); ?>/report/showInventoryReport",
	        "fnServerData": function ( sSource, aoData, fnCallback ) {
				aoData.push(
					{ "name": "as_date", "value": $('#as_date').val() },
					{ "name": "renter_id", "value": $('#renter_id').val() },
					{ "name": "status_id", "value": $('#status_id').val() },
					{ "name": "product_id", "value": $('#product_id').val() }
				);
				$.getJSON( sSource, aoData, function (json) { 
					fnCallback(json);
				} );
			},*/
			// End By Ball	
			 "aoColumns": [
                {"sWidth": "50px;","sClass": "center"},
                {"sWidth": "110px;","sClass": "center"},
                {"sWidth": "450px;","sClass": "left"},
				{},
				{},
				{},// #defect 336 เพิ่ม column เพื่อแสดง Lot/Serial | by kik
				{}
				]});
	});

	function exportFile(file_type){
		if(file_type=='EXCEL'){
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportExpiredToExcel")
		}else{
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportExpiredPdf")
		}
		$("#form_report").submit();
    }
</script>
<form  method="post" action="" target="_blank" id="form_report">
<input type="hidden" name="renter_id" id="renter_id" value="<?php echo $search['renter_id'];?>">
<input type="hidden" name="warehouse_id" id="warehouse_id" value="<?php echo $search['warehouse_id'];?>">
<input type="hidden" name="category_id" id="category_id" value="<?php echo $search['category_id'];?>">
<input type="hidden" name="product_id" id="product_id" value="<?php echo $search['product_id'];?>">
<input type="hidden" name="as_date" id="as_date" value="<?php echo $search['as_date'];?>">
<input type="hidden" name="remain_day" id="remain_day" value="<?php echo $search['remain_day'];?>">
<table id="tbreport"  cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
	<thead>
		<tr>
			<th><?php echo _lang('no');?></th>
                        <th width="100"><?php echo _lang('product_code');?></th>
			<th width="500"><?php echo _lang('product_name');?></th>
			<th><?php echo _lang('lot');?>/<?php echo _lang('serial');?></th><!--#defect 336 เพิ่ม column เพื่อแสดง Lot/Serial-->
			<th><?php echo _lang('expired_date');?></th>
			<th><?php echo _lang('remain_days');?></th>
			<th><?php echo _lang('qty');?></th>
		</tr>		
	</thead>
	<tbody>
		<?php
		$i=1;
		$sum_cols=array();
		$sum_all=0;
		foreach($data as $cols){
		?>
		<tr>
			<td ><?php echo $i;?></td>
			<td ><?php echo $cols->Product_Code;?></td>
			<td style='text-align: left' ><?php echo $cols->Product_NameEN;?></td>
			<td><?=(($cols->Product_Lot != " ")?$cols->Product_Lot:"-")?>/<?=(($cols->Product_Serial != " ")?$cols->Product_Serial:"-")?></td><!--#defect 336 เพิ่ม column เพื่อแสดง Lot/Serial  | by kik-->
			<td><?php echo $cols->Product_Exp;?></td>
			<td style='text-align: right'><?php echo $cols->Remain_day;?></td>
			<td style='text-align: right'><?php echo number_format($cols->Balance);?></td>
		</tr>
	    <?php
			$sum_all+=@$cols->Balance;
			$i++;
		}
	    ?>
		</tbody>
		<!--
		<tfoot>
			<tr>
				<th colspan="3">Total</th>
				<?php
				foreach($sum_cols as $sc){
				?>
				<th><?php echo $sc;?></th>
				<?php 
				}
				?>
				<th><?php echo $sum_all;?></th>
			</tr>
		</tfoot>
		-->
                
                <!-- show total qty : by kik : 31-10-2013-->
                    <tfoot>
                             <tr>
                                    <th colspan="6" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                                    <th class ='ui-state-default indent'  style='text-align: right;'><?php echo number_format(@$sum_all);?></th>
                                    
                             </tr>
                    </tfoot>
                <!-- end show total qty : by kik : 31-10-2013-->
        
        
</table>
<!--COMMENT BY POR 2013-11-05 ยกเลิกการใช้ปุ่มแสดง report หน้านี้ แต่ไป ให้ไปแสดงใน workflow_template แทน
        <div align="center" style="margin-top:10px;">
                <input type="button" value="Export To PDF" class="button orange" onClick="exportFile('PDF')"  />
                 &emsp;&emsp;
                <input type="button" value="Export To Excel" class="button orange" onClick="exportFile('EXCEL')" />
        </div>
-->
</form>