<script>
    $(document).ready(function() {
        $('#defDataTable2').dataTable({"bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
			"sPaginationType": "full_numbers"});
	});

	function exportFile(file_type){
		if(file_type=='EXCEL'){
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/counting_report/export_counting_excel")
		}else{
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/counting_report/export_counting_pdf")
		}
		$("#form_report").submit();
  }
</script>
<form  method="post" target="_blank" id="form_report">
<input type="hidden" name="fdate" value="<?php echo $search['fdate'];?>" />
<input type="hidden" name="tdate" value="<?php echo $search['tdate'];?>" />
<input type="hidden" name="product_id" value="<?php echo $search['product_id'];?>" />
<input type="hidden" name="lot" value="<?php echo $search['lot'];?>" />
<input type="hidden" name="count_type" value="<?php echo $search['count_type'];?>" />
<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
	<thead>
		<tr>
			<th><?php echo _lang('no'); ?></th>
			<th><?php echo _lang('product_code'); ?></th>
			<th><?php echo _lang('product_name'); ?></th>
			<th><?php echo _lang('system'); ?></th>
			<th><?php echo _lang('physical'); ?></th>
			<th><?php echo _lang('diff'); ?></th>
			<th><?php echo _lang('variant'); ?></th>
			<th><?php echo _lang('count_by'); ?></th>
		</tr>
	</thead>
	<tbody>

		<?php
		$sum_all_qty1=0;
                $sum_all_qty2=0;
                $sum_all_qty3=0;
                $sum_all_qty4=0;
                $sum_all_qty_diff=0;
			foreach($data as $key=>$value){
				$i=$key+1;
				if($value->Reserv_Qty && $value->Confirm_Qty) :
					$variat = (($value->Reserv_Qty-$value->Confirm_Qty) / (float) $value->Reserv_Qty) * 100;
				else:
					$variat = 0;
				endif;
		?>
			<tr style="text-align:center;">
				<td><?php echo $i;?></td>
				<td><?php echo $value->Product_Code;?></td>
				<td style="text-align:left;"><?php echo $value->Product_NameEN;?></td>
				<!--<td style="text-align:right;"><?php echo set_number_format($value->Actual_Qty);?></td>-->
				<td style="text-align:right;"><?php echo set_number_format($value->Reserv_Qty);?></td>
				<td style="text-align:right;"><?php echo set_number_format($value->Confirm_Qty);?></td>
				<td style="text-align:right;"><?php echo set_number_format(abs($value->Confirm_Qty-$value->Reserv_Qty));?></td>
				<td style="text-align:right;"><?php echo set_number_format($variat);?></td>
				<td style="text-align:left;"><?php echo $value->Count_By;?></td>
			</tr>
		<?php 
                $sum_all_qty1+=$value->Actual_Qty;
                $sum_all_qty2+=$value->Reserv_Qty;
                $sum_all_qty3+=$value->Confirm_Qty;
                $sum_all_qty4+=$variat;
                $sum_all_qty_diff+=abs($value->Confirm_Qty-$value->Reserv_Qty);
			}
			
		?>

	</tbody>
        
        <!-- show total qty : by kik : 31-10-2013-->
            <tfoot>
                     <tr>
                            <th colspan="3" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                            
                            <!--<th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_qty1);?></th>-->
                            
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_qty2);?></th>
                            
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_qty3);?></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_qty_diff);?></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_qty4);?></th>
                            <th ></th>
                            <!--<th colspan="5" ></th>-->

                     </tr>
            </tfoot>
        <!-- end show total qty : by kik : 31-10-2013-->
        
        
</table>
<!--COMMENT BY POR 2013-11-05 เธขเธเน€เธฅเธดเธเธเธฒเธฃเนเธเนเธเธธเนเธกเนเธชเธ”เธ report เธซเธเนเธฒเธเธตเน เนเธ•เนเนเธ เนเธซเนเนเธเนเธชเธ”เธเนเธ workflow_template เนเธ—เธ
<div align="center" style="margin-top:10px;">
	<input type="button" value="Export To PDF" class="button orange" onClick="exportFile('PDF')"  />
	 &emsp;&emsp;
	<input type="button" value="Export To Excel" class="button orange" onClick="exportFile('EXCEL')" />
</div>
-->
</form>