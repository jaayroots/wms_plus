<script>
    $(document).ready(function() {
        $('#defDataTable2').dataTable({"bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
			"sPaginationType": "full_numbers"});
	});
	  

</script>
<form  method="post" target="_blank" id="form_report">
<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">


    <thead>
            <tr>
                                            <th><?php echo _lang('No.'); ?></th>
                                            <th><?php echo _lang('product_code'); ?></th>
                                            <th><?php echo _lang('product_name'); ?></th>
                                            <th><?php echo _lang('Pallet Code'); ?></th>
                                            <th><?php echo _lang('Location'); ?></th>
                                            <th><?php echo _lang('Stock Material Mfd.'); ?></th>
                                            <th><?php echo _lang('Dispatch Material Mfd.'); ?></th> 
                                            <th><?php echo _lang('Dispatch Date'); ?></th>
                                            <th><?php echo _lang('Balance Qty.'); ?></th>
                                             <th><?php echo _lang('UOM'); ?></th>
            </tr>
	</thead>
	<tbody>
		<?php
				
                        $sumReceiveQty = 0;     //add $sumReservQty variable for calculate total Receive qty : by kik : 31-10-2013
                        $sumDispatchQty = 0;    //add $sumReceiveQty variable for calculate total Dispatch qty : by kik : 31-10-2013
                        
			foreach($listCounting as $key=>$value){
				$i=$key+1;
		?>
			<tr>
                    <td style="text-align:center;width:50px;"><?php echo ($low + ($key + 1)) ?></td>
                    <td style="text-align:left;width:100px;"><?php echo $value->Product_Code ?></td>
                    <td style="text-align:left;width:200px;"><?php echo $value->Product_NameEN ?></td>
                    <td style="text-align:center;width:110px;"><?php echo $value->Pallet_Code ?></td>
                    <td style="text-align:left;width:80px;"><?php echo $value->Location_Code ?></td>
                    <td style="text-align:left;width:80px;"><?php echo $value->Product_Mfd ?></td>
                    <td style="text-align:left;width:80px;"><?php echo $value->Dispatch_Mfd ?></td>
                    <td style="text-align:left;width:80px;"><?php echo $value->Dispatch_Date ?></td>
                    <td style="text-align:right;width:80px;" ><?php echo $value->Balance_Qty ?></td>
                    <td style="text-align:left;width:80px;"><?php echo $value->uom ?></td>
			</tr>
                        
		<?php 
                    $sumReceiveQty+=@$value->Reserv_Qty;     // Add $sumReceiveQty for calculate total qty : by kik : 31-10-2013
                    $sumDispatchQty+=@$value->Confirm_Qty;     // Add $sumDispatchQty for calculate total qty : by kik : 31-10-2013
                    
                            
			}
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