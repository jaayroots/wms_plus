<script>
    $(document).ready(function() {
        $('#defDataTable2').dataTable({
        	 /*"fnDrawCallback": function ( oSettings ) {
                 if ( oSettings.aiDisplay.length == 0 )
                 {
                     return;
                 }
                  
                 var nTrs = $('#defDataTable2 tbody tr');
                 var iColspan = nTrs[0].getElementsByTagName('td').length;
                 var sLastGroup = "";
                 for ( var i=0 ; i<nTrs.length ; i++ )
                 {
                     var iDisplayIndex = oSettings._iDisplayStart + i;
                     var sGroup = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[1];
                     if ( sGroup != sLastGroup )
                     {
                         var nGroup = document.createElement( 'tr' );
                         var nCell = document.createElement( 'td' );
                         nCell.colSpan = iColspan;
                         nCell.className = "group";
                         nCell.innerHTML = sGroup;
                         nGroup.appendChild( nCell );
                         nTrs[i].parentNode.insertBefore( nGroup, nTrs[i] );
                         sLastGroup = sGroup;
                     }
                 }
             },*/         
            "bJQueryUI": true,
            "bSort": false, // Edit By Akkarapol, 25/09/2013, เปลี่ยนจากที่ให้ bSort = True ให้เป็น false เนื่องจากไม่ต้องการให้มัน Auto Sort เพราะจะทำให้การเรียงข้อมูลผิด
            "bAutoWidth": false,
            "sPaginationType": "full_numbers"
                    });
	});

  function exportFile(file_type){
		if(file_type=='EXCEL'){
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportGRNToExcel")
		}else{
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportGRNToPDF")
		}
		$("#form_report").submit();
  }

</script>
<style>
    tr.reject_row{
        color:red;
    }

</style>
<form action="" method="post" target="_blank" id="form_report">
<input type="hidden" name="fdate" value="<?php echo $search['fdate'];?>" />
<input type="hidden" name="tdate" value="<?php echo $search['tdate'];?>" />
<table id="defDataTable2" class="display dataTable" cellspacing="2" cellpadding="0" border="0" aria-describedby="defDataTable_info">
	<thead>
		<tr>
                    <th><?php echo _lang('asn_date'); ?></th>
			<th><?php echo _lang('po_no'); ?></th>
            <th><?php echo _lang('document_no'); ?></th>
            <th><?php echo _lang('doc_refer_int'); ?></th>
			<th align="center"><?php echo _lang('supplier'); ?></th>
			<th><?php echo _lang('product_code'); ?></th>
			<th><?php echo _lang('product_name'); ?></th>
			<th><?php echo _lang('po_qty'); ?></th>
			<th><?php echo _lang('arrival_date'); ?></th>
			<th><?php echo _lang('receive_date_informed'); ?></th>
			<th><?php echo _lang('receive_qty'); ?></th>
			<th><?php echo _lang('remark'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php   
                        $sumReservQty_all = 0;     //add $sumReservQty variable for calculate total reserv qty : by kik : 31-10-2013
                        $sumReceiveQty_all = 0;    
                        $sumReservQty_not_reject = 0;     
                        $sumReceiveQty_not_reject = 0;    
                        $sumReservQty_reject = 0;     
                        $sumReceiveQty_reject = 0;    
                        $have_reject = FALSE;
			// p($data);exit();
                        foreach($data as $key=>$value){
				$i=$key+1; 
                                $color_diff="";
                                if($value['reserv_quan']!=$value['receive_quantity']):
                                    $color_diff=";color:red;font-weight:bold;";
                                endif;
                                
                                ?>
                                
                                
                                <tr><!--Fix Error empty variable and new all query : kik-->
                                        <td ><?php echo $value['0']->Estimate_Action_Date;?></td>
                                        <td><?php echo $value['0']->Doc_Refer_Ext;?></td>
                                        <td><?php echo $value['0']->Document_No;?></td>
                                        <td><?php echo $value['0']->Doc_Refer_Int;?></td>
                                        <td><?php echo $value['0']->supplier;?></td>
                                        <td><?php echo $value['0']->Product_Code;?></td>
                                        <td style='text-align: left'><?php echo $value['0']->Product_NameEN;?></td>
                                        <td style='text-align: right<?php echo $color_diff;?>'><?php echo set_number_format($value['reserv_quan']);?></td>
                                        <td><?php echo @$value['0']->Actual_Action_Date;?></td>
                                        <td><?php echo @$value['0']->Pending_Date;?></td>
                                        <td style='text-align: right<?php echo $color_diff;?>'><?php echo set_number_format($value['receive_quantity']);?></td>
                                        <td><?php echo @$value['remark_data'];?></td>
                                </tr>
                        
                                <? if($value['0']->Is_reject == 'Y'): 
                                    $have_reject = TRUE;
                                    ?>
                                    <tr class="reject_row"><!--Fix Error empty variable and new all query : kik-->
                                            <td ><?php echo $value['0']->Estimate_Action_Date;?></td>
                                            <td><?php echo $value['0']->Doc_Refer_Ext;?></td>
                                            <td><?php echo $value['0']->Document_No;?></td>
                                            <td><?php echo $value['0']->Doc_Refer_Int;?></td>
                                            <td><?php echo $value['0']->supplier;?></td>
                                            <td><?php echo $value['0']->Product_Code;?></td>
                                            <td style='text-align: left'><?php echo $value['0']->Product_NameEN;?></td>
                                            <td style='text-align: right<?php echo $color_diff;?>'><?php echo set_number_format(-$value['reserv_quan']);?></td>
                                            <td><?php echo @$value['0']->Actual_Action_Date;?></td>
                                            <td><?php echo @$value['0']->Pending_Date;?></td>
                                            <td style='text-align: right<?php echo $color_diff;?>'><?php echo set_number_format($value['receive_quantity']);?></td>
                                            <td><?php echo (@$value['Remark'] == " " || @$value['Remark'] == "" || empty($value['Remark']))?"Reject":@$value['Remark']; ?></td>
                                    </tr>
                                    
                                <? 
                                    $sumReservQty_reject += @$value['reserv_quan'];    
                                    $sumReceiveQty_reject += @$value['receive_quantity'];    
                                else : 
                                    $sumReservQty_not_reject += @$value['reserv_quan'];    
                                    $sumReceiveQty_not_reject += @$value['receive_quantity'];    
                                    
                                endif; ?>
			
		<?php 
                            $sumReservQty_all+=@$value['reserv_quan'];     // Add $sumQty for calculate total qty : by kik : 31-10-2013
                            $sumReceiveQty_all+=@$value['receive_quantity'];     // Add $sumQty for calculate total qty : by kik : 31-10-2013
			}
		?>
		
	</tbody>
        
        <!-- show total qty : by kik : 31-10-2013-->
                    <tfoot>
                             <tr>
                                    <th colspan="7" class ='ui-state-default indent'  style='text-align: left;'><b>Total</b> <?php if($have_reject): ?> <font style="font-size:10px;">(Exclude Reject)</font><?php endif;?></th>
                                    <th class ='ui-state-default indent'  style='text-align: right;'><span  id="sum_all_qty"><?php echo set_number_format(@$sumReservQty_not_reject);?></span></th>
                                    <th colspan="2" class ='ui-state-default indent' ></th>
                                    <th class ='ui-state-default indent'  style='text-align: right;'><span  id="sum_all_qty"><?php echo set_number_format(@$sumReceiveQty_not_reject);?></span></th>
                                    <th class ='ui-state-default indent' ></th>
                             </tr>
                    <!--Reject Area show Total - ในส่วนนี้จะแสดงผลเฉพาะ SKU ที่มีรายการ reject เกิดขึ้นเท่านั้น-->
                    <?php if($have_reject): ?>
                             <tr  bgcolor="#EEEED1">
                                    <td colspan="7"  style='text-align: left;'><b>Reject</b></td>
                                    <td  style='text-align: right; border: 1px solid #D0D0D0;'><span  id="sum_all_qty"><?php echo set_number_format(@$sumReservQty_reject);?></span></td>
                                    <td colspan="2" ></td>
                                    <td style='text-align: right; border: 1px solid #D0D0D0;'><span  id="sum_all_qty"><?php echo set_number_format(@$sumReceiveQty_reject);?></span></td>
                                    <td  ></td>
                             </tr>
                             <tr  bgcolor="#CDCDB4">
                                    <td colspan="7" style='text-align: left;'><b>All Total</b></td>
                                    <td  style='text-align: right; border: 1px solid #D0D0D0;'><span  id="sum_all_qty"><?php echo set_number_format(@$sumReservQty_all);?></span></td>
                                    <td colspan="2" ></td>
                                    <td  style='text-align: right; border: 1px solid #D0D0D0;'><span  id="sum_all_qty"><?php echo set_number_format(@$sumReceiveQty_all);?></span></td>
                                    <td ></td>
                             </tr>
                  <?php endif; ?>
                  <!-- end Reject Area show Total - ในส่วนนี้จะแสดงผลเฉพาะ SKU ที่มีรายการ reject เกิดขึ้นเท่านั้น-->
                    </tfoot>
        <!-- end show total qty : by kik : 31-10-2013-->
                            
</table>
<!--COMMENT BY POR 2013-11-05 ยกเลิกการใช้ปุ่มแสดง report หน้านี้ แต่ไป ให้ไปแสดงใน workflow_template แทน
<div align="center" style="margin-top:10px;">
	<input type="button" value="Export To PDF" class="button orange" onClick="exportFile('PDF')"  />
	 &emsp;&emsp;
	<input type="button" value="Export To Excel" class="button orange" onClick="exportFile('EXCEL')" />
</div>-->
</form>