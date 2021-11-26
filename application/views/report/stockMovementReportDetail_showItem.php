<?php
//print_r($data);
//include "header.php";
?>
<!--<script type="text/javascript" language="javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.js";?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->

<script>
    $(document).ready(function() {
	
	  oTable = $('#defDataTable2').dataTable({
            "aaSortingFixed": [[0, 'asc']],
            "aaSorting": [[1, 'asc']],
            "sPaginationType": "full_numbers",
            "bJQueryUI": true,
            "bSort": false,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
        });
		
});
function exportFile(file_type){
	if(file_type=='EXCEL'){
		$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportExcelMovement_showItem")
	}else{
		$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportReportStockMovementToPDF_showItem")
	}
	$("#form_report").submit();
 }
 function show_inven_report(product_name){
    $('#product_name').val(product_name);
//    console.log(product_name);
//    return false;
    $("#form_link_inven").submit();
 }

</script>

<style>
    td.group{
        background:#BEBEBE;
        text-align: left;
    }
    td.sum_group{
        background:#E8E8E8;
        text-align: right;
    }
    tr.reject_row{
        color:red;
    }
</style>

<form method="post" target="_blank" id="form_link_inven" action ='<?php echo site_url(); ?>/report/inventory'>
    <input type="hidden" name="product_name" id="product_name" >
    <input type="hidden" name="show_auto" value="Y">
    <input type="hidden" name="product_id" value="<?php echo $form_value['product_id'];?>">
    <input type="hidden" name="tdate" value="<?php echo $form_value['tdate'];?>">
    <input type="hidden" name="renter_id" value="<?php echo $form_value['renter_id'];?>">
    <input type="hidden" name="product_code" >
</form>

<form method="post" target="_blank" id="form_report" >
<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">

	<thead>
		<tr>
			<th><?php echo _lang('no')?></th>
			<th><?php echo _lang('date')?></th>
			<th><?php echo _lang('number_received')?></th>
			<th><?php echo _lang('reference_received')?></th>
			<th><?php echo _lang('number_dispatch')?></th>
			<th><?php echo _lang('reference_dispatch')?></th>
			<th><?php echo _lang('receive_qty')?></th>
			<th><?php echo _lang('dispatch_qty')?></th>
			<th><?php echo _lang('serial')?>/<?php echo _lang('lot')?></th>
			<th><?php echo _lang('product_mfd')?></th>
			<th><?php echo _lang('product_exp')?></th>
			<th><?php echo _lang('from_to')?></th>
			<th><?php echo _lang('location')?></th>
                
                        <?php if($this->config->item('build_pallet')): ?>
                            <th><?php echo _lang('pallet_code') ?></th>
                        <?php endif; ?>
                
			<th><?php echo _lang('balance')?></th>
		</tr>
	</thead>
	<tbody>
		<?php
                $sumBalanceQty = 0;    //add $sumReceiveQty variable for calculate total Balance qty : by kik : 31-10-2013
                $sumStartBalanceQty = 0;    //add by kik : 2013-11-25

                $sumReceive_not_reject = 0;
                $sumReceive_reject = 0;
                $sumReceive_all = 0;

                $sumDispatch_not_reject = 0;
                $sumDispatch_reject = 0;
                $sumDispatch_all = 0;
                
                $have_reject = FALSE;
                  
                  
                if(!empty($datas['no_onhand'])){ ?>
                    <font size="3" color="red"><?php echo $datas['no_onhand'];?></font><br>
               <? }else if (!empty($datas)) {
                   
		  $i=1;
//                  $sumReceiveQty = 0;     //add $sumReservQty variable for calculate total Receive qty : by kik : 31-10-2013
//                  $sumDispatchQty = 0;    //add $sumReceiveQty variable for calculate total Dispatch qty : by kik : 31-10-2013
                  
                  
                            foreach($datas as $key=>$value){ 
                                $tr_reject_class =""; 
                                
                                 if($value['r_qty'] <=0 && $value['Is_reject']=='Y'):
                                    $tr_reject_class = 'class = "reject_row" ';
                                    $have_reject = TRUE;
                                 endif;?>
                    
				<tr <?php echo $tr_reject_class;?> >
                                        
                                        <td ><?php echo $i;?></td>
					<td><?php echo $value['receive_date'];?></td>
					<td>&nbsp;&nbsp;<?php echo $value['receive_doc_no'];?></td>
					<td><?php echo $value['receive_refer_ext'];?></td>
					<td><?php echo $value['pay_doc_no'];?></td>
					<td><?php echo $value['pay_refer_ext'];?></td>
					<td style='text-align: right'><?php echo (set_number_format($value['r_qty']) == 0)?'':set_number_format($value['r_qty']);?></td>
					<td style='text-align: right'><?php echo (set_number_format($value['p_qty']) == 0)?'':set_number_format($value['p_qty']);?></td>
					<td><?php echo $value['Product_SerLot'];?></td>
					<td><?php echo $value['Product_Mfd'];?></td>
					<td><?php echo $value['Product_Exp'];?></td>
					<td><?php echo $value['branch'];?></td>
					<td><?php echo $value['Location_Code'];?></td>
                                
                                        <?php if($this->config->item('build_pallet')): ?>
                                            <td><?php echo $value['Pallet_Code']; ?></td>
                                        <?php endif; ?>
                                    
					<td style='text-align: right'><?php echo set_number_format($value['Balance_Qty']);?></td>
				</tr>
                    <?php 
                    
                                    //------------ SUM QTY Reject only ------------//
                                    if($value['Is_reject']=='Y' && $value['r_qty'] >= 0):
                                        $have_reject = TRUE;
                                        $sumReceive_reject+=@$value['r_qty'];     
                                        $sumDispatch_reject+=@$value['p_qty'];   
                                        
                                    elseif($value['Is_reject']=='N'):
                                    //------------ SUM QTY NOT Reject only ------------//
                                        $sumReceive_not_reject+=@$value['r_qty'];     
                                        $sumDispatch_not_reject+=@$value['p_qty'];     
                                        
                                    endif;
                                    
                                    //------------ SUM QTY ALL (reject + not reject) ------------//
                                    if($value['r_qty'] >= 0):
                                        $sumReceive_all+=@$value['r_qty'];     
                                        $sumDispatch_all+=@$value['p_qty'];    
                                    endif;

                                    $i++;

//                                    $sumReceiveQty+=@$value['r_qty'];     // Add $sumReceiveQty for calculate total qty : by kik : 31-10-2013
//                                    $sumDispatchQty+=@$value['p_qty'];     // Add $sumDispatchQty for calculate total qty : by kik : 31-10-2013
                        
                                }?>
                                
                       <?
                                    $sumStartBalanceQty+=@$value['start_balance'];
                                    $sumBalanceQty+=@$value['Balance_Qty'];     // Add $sumBalanceQty for calculate total qty : by kik : 31-10-2013
		}
                
		?>
		
	</tbody>
        
         <!-- show total qty : by kik : 31-10-2013-->
                    <tfoot>
                                <?php 
                                $colspan = 5;
                                if($this->config->item('build_pallet')):
                                    $colspan = 6;
                                endif; 
                                ?>
                             <tr>
                                    <th colspan="6" class ='ui-state-default indent'  style='text-align: center;'><b>Total Incoming Balance : <?php echo set_number_format(@$sumStartBalanceQty)?></b></th>
                                    <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format(@$sumReceive_not_reject);?></th>
                                    <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format(@$sumDispatch_not_reject);?></th>
                                    <th colspan="<?php echo $colspan; ?>" class ='ui-state-default indent' ></th>
                                    <th class ='ui-state-default indent'  style='text-align: right; cursor: pointer;' onclick='show_inven_report("<?php echo iconv("UTF-8","TIS-620//IGNORE",$form_value['product']);?>")'><u><?php echo set_number_format(@$sumBalanceQty);?></u></th>
                                   
                             </tr>
                             <!--Reject Area show Total - ในส่วนนี้จะแสดงผลเฉพาะ SKU ที่มีรายการ reject เกิดขึ้นเท่านั้น-->
                             <?php if($have_reject): ?>
                                 <tr  bgcolor="#EEEED1" >
                                    <td colspan="6"   style='text-align: center;'><b>Reject</b></td>
                                    <td style='text-align: right; border: 1px solid #D0D0D0;'><?php echo set_number_format(@$sumReceive_reject);?></td>
                                    <td style='text-align: right; border: 1px solid #D0D0D0;'><?php echo set_number_format(@$sumDispatch_reject);?></td>
                                    <td colspan="<?php echo $colspan; ?>"  ></td>
                                    <td style='text-align: right; cursor: pointer;'></td>
                                   
                                </tr>
                                <tr  bgcolor="#CDCDB4">
                                       <td colspan="6"  style='text-align: center;'><b>All Total</b><font style="font-size:10px;">(Include Reject)</font></td>
                                       <td style='text-align: right; border: 1px solid #D0D0D0;'><?php echo set_number_format(@$sumReceive_all);?></td>
                                       <td style='text-align: right; border: 1px solid #D0D0D0;'><?php echo set_number_format(@$sumDispatch_all);?></td>
                                       <td colspan="<?php echo $colspan; ?>" ></td>
                                       <td style='text-align: right; cursor: pointer;'></td>

                                </tr>
                             <?php endif; ?>
                            <!-- end Reject Area show Total - ในส่วนนี้จะแสดงผลเฉพาะ SKU ที่มีรายการ reject เกิดขึ้นเท่านั้น-->
                             
                    </tfoot>
        <!-- end show total qty : by kik : 31-10-2013-->
        
        
</table>
<div align="center" style="margin:10px auto">
    <input type="hidden" name="product_name" value="<?php echo $form_value['product'];?>">
	<input type="hidden" name="product_id2" value="<?php echo $form_value['product_id'];?>">
	<input type="hidden" name="fdate" value="<?php echo $form_value['fdate'];?>">
	<input type="hidden" name="tdate" value="<?php echo $form_value['tdate'];?>">
	<input type="hidden" name="renter_id" value="<?php echo $form_value['renter_id'];?>">
        <!--ADD BY POR 2013-10-28 เพิ่มเงือนไขให้ค้นหาด้วย document-->
        <input type="hidden" name="doc_type" value="<?php echo $form_value['doc_type'];?>">
        <input type="hidden" name="doc_value" value="<?php echo $form_value['doc_value'];?>">
        <!--END ADD-->
        

</div>
</form>