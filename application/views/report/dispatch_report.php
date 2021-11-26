<script>
    
    var built_pallet = '<?php echo $this->config->item('build_pallet'); ?>';
    var ci_pallet_id = 11;  
    
$(document).ready(function() {

    $('#defDataTable2 tbody tr td[title]').hover(function(){
    	var chk_title = $(this).attr('title');
		if (chk_title.length > 1) {
        	$(this).show_tooltip();		
		}
    }, function(){
    	$(this).hide_tooltip();
    });
        
	oTable = $('#defDataTable2').dataTable({
		"fnDrawCallback": function ( oSettings ) {
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
				var sGroup = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[0];
				if ( sGroup != sLastGroup )
				{
					var nGroup = document.createElement( 'tr' );
					var nCell = document.createElement( 'td' );
					nCell.colSpan = iColspan;
					nCell.className = "group left_text";
					nCell.innerHTML = sGroup;
					nGroup.appendChild( nCell );
					nTrs[i].parentNode.insertBefore( nGroup, nTrs[i] );
					sLastGroup = sGroup;
				}
			}
		},
		"aoColumnDefs": [
			{ "bVisible": false, "aTargets": [ 0 ] }
		],
		"aaSortingFixed": [[ 0, 'asc' ]],
		"aaSorting": [[ 1, 'asc' ]],
		"sPaginationType": "full_numbers",
		"bJQueryUI": true,
        "bSort": true,
        "bAutoWidth": false,
        "oSearch": {"sSearch": ""},
		//"sDom": 'lfr<"giveHeight"t>ip'
	});
        
        if(!built_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
            $('#defDataTable2').dataTable().fnSetColumnVis(ci_pallet_id, false);
        }
} );

function exportFile(file_type){
		if(file_type=='EXCEL'){
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportDispatchToExcel")
		}else{
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportDispatchToPDF")
		}
		$("#form_report").submit();
  }
</script>
<style>
td.group{
	background:#E6F1F6;
}
</style>
<form  method="post" target="_blank" id="form_report">
<input type="hidden" name="fdate" value="<?php echo $search['fdate'];?>" />
<input type="hidden" name="tdate" value="<?php echo $search['tdate'];?>" />
<input type="hidden" name="doc_type" value="<?php echo $search['doc_type'];?>" />
<input type="hidden" name="doc_value" value="<?php echo $search['doc_value'];?>" />
<input type="hidden" name="type_dp_date_val" value="<?php echo $search['type_dp_date_val'];?>" /><!-- //add for select type of dispatch date between system dispatch date and real dispatch date : by kik : 20141209-->
<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
	<thead>
		<tr>
                    <th><?php echo _lang('dispatch_date'); ?></th>
                    <th><?php echo _lang('document_no'); ?></th>
                    <th><?php echo _lang('document_ext'); ?></th>
		    		<th><?php echo _lang('doc_refer_int'); ?></th>
                    <th><?php echo _lang('product_code'); ?></th>
                    <th><?php echo _lang('product_name'); ?></th>
                    <th><?php echo _lang('lot'); ?></th>
					<th><?php echo _lang('Mfd_Date'); ?></th>
                    <th><?php echo _lang('serial'); ?></th>
                    <th><?php echo _lang('qty'); ?></th>
		    		<th><?php echo _lang('CBM'); ?></th>
                    <th><?php echo _lang('unit'); ?></th>
                    <th><?php echo _lang('from'); ?></th>
                    <th><?php echo _lang('to'); ?></th>
                    <th><?php echo _lang('Description'); ?></th>
                    <th><?php echo _lang('pallet_code'); ?></th>
                    <th><?php echo _lang('remark'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
                        
                        $sum_all=0;
			foreach($data as $key => $value){
			?>
			<tr>
				<td><?php echo $value->Dispatch_Date;?></td>
				<td class="popup" title="<?php echo $value->Doc_Remark?>" style="cursor: pointer;"><?php echo $value->Document_No;?></td>
				<td><?php echo $value->Doc_Refer_Ext;?></td>
				<td><?php echo $value->Doc_Refer_Int;?></td>
				<td><?php echo $value->Product_Code;?></td>
				<td style="text-align:left;"><?php echo $value->Product_Name;?></td>
				<td><?php echo $value->Product_Lot;?></td>
				<td><?php echo  date("d/m/Y", strtotime($value->Product_Mfd));?></td>
				<td><?php echo $value->Product_Serial;?></td>
                                <td style="text-align:right;"><?php echo set_number_format($value->Dispatch_Qty);?></td>
				<td style="text-align:right;"><?php echo set_number_format($value->CBM);?></td>
                                <!--Edit By Akkarapol, 13/01/2013, เปลี่ยนจากการนำ Unit_Id มาแสดงที่หน้าจอ เป็นแสดงด้วย Unit_Value แทน-->
                                <!--<td><?php //echo $value->Unit_Id; ?></td>-->
                                <td><?php echo $value->Unit_Value; ?></td>
                    
				<td style="text-align:left;"><?php echo $value->From_sup;?></td>
				<td style="text-align:left;"><?php echo $value->Destination_Code;?></td>
				<td style="text-align:left;"><?php echo $value->Destination_Name;?></td>
				<td style="text-align:left;"><?php echo $value->Pallet_Code;?></td>
				<td style="text-align:left;"><?php echo $value->Remark;?></td>
			</tr>
			<?php
                        
                        $sum_all+=@$value->Dispatch_Qty;
					//} // close loop detail
				//} // close loop doc no
			}
		?>
	</tbody>
        
                <!-- show total qty : by kik : 31-10-2013-->
                    <tfoot>
                             <tr>
                                    <th colspan="9" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                                    <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all);?></th>
				    <th ></th>
                                    <th colspan="7" ></th>
                                    
                             </tr>
                    </tfoot>
                <!-- end show total qty : by kik : 31-10-2013-->
        
        
</table>
</form>
<div class="modal fade" id="modal_note" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body"></div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
