<?php
//print_r($data);
//include "header.php";
?>
<script type="text/javascript" language="javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.js";?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>

<script>
    $(document).ready(function() {
        $('#defDataTable2').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
            "aoColumns": [
                {"sWidth": "50px;","sClass": "center"},
                {"sWidth": "150px;","sClass": "center"},
                {"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"}
				
				],
            "sPaginationType": "full_numbers"});
    });
</script>
<form action="<?php echo site_url();?>/report/exportReportStockMovementToPDF" method="post" target="_blank">
<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
	<thead>
		<tr>
			<th><?php echo 'Date/วันแจ้งเข้าคลัง';?></th>
			<th><?php echo $this->conv->tis620_to_utf8('PO.NO');?></th>
			<th><?php echo $this->conv->tis620_to_utf8('Supplier');?></th>
			<th><?php echo $this->conv->tis620_to_utf8('Item');?></th>
			<th><?php echo $this->conv->tis620_to_utf8('Product Name');?></th>
			<th><?php echo $this->conv->tis620_to_utf8('Qty.PO.');?></th>
			<th><?php echo $this->conv->tis620_to_utf8('Arrival Date');?></th>
			<th><?php echo $this->conv->tis620_to_utf8('Receiving Date/Informed');?></th>
			<th>Qty. Receiv</th>
			<th><?php echo $this->conv->tis620_to_utf8('Remark');?></th>
		</tr>
	</thead>
	<tbody>
		<?php
	
			foreach($data as $key=>$value){
				$i=$key+1;
		?>
			<tr>
				<td ><?php echo $value->Estimate_Action_Date;?></td>
				<td><?php echo $value->Doc_Refer_Int;?></td>
				<td><?php echo $value->supplier;?></td>
				<td><?php echo $value->Product_Code;?></td>
				<td><?php echo $value->Product_NameEN;?></td>
				<td><?php echo $value->Reserv_Qty;?></td>
				<td><?php echo $value->Actual_Action_Date;?></td>
				<td><?php echo $value->Confirm_Qty;?></td>
				<td><?php echo $value->Pending_Date;?></td>
				<td><?php echo $value->Remark;?></td>
			</tr>
		<?php 
			}
		?>
		
	</tbody>
</table>
<div align="center" style="margin:10px auto">
	
</div>
</form>