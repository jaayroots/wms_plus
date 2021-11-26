<?php
//print_r($data);
//include "header.php";
?>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>

<script>
    $(document).ready(function() {
        //$('#defDataTable').dataTable({
        
       // if("defDataTable" == "defDataTableModal"){
             $('#defDataTable2').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
            "aoColumns": [
                {"sWidth": "100px;","sClass": "left"},
                {"sWidth": "","sClass": "left"},
                {"sWidth": "100px;","sClass": "center"}
			/*,
                {"sWidth": "40px;","sClass": "center"},
                {"sWidth": "50px;","sClass": "center"},
                {"sWidth": "60px;","sClass": "center"},
                {"sWidth": "40px;","sClass": "center"},
                {"sWidth": "50px;","sClass": "center"},
                {"sWidth": "60px;","sClass": "center"},
                {"sWidth": "20px;","sClass": "center"},
                {"sWidth": "50px","sClass": "right"}*/
				],
            "sPaginationType": "full_numbers"});
			/*
        }else{
        $('#defDataTable2').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            //"oSearch": {"sSearch": "{search_value}"},
            "aoColumns": [
				
                {"onblur": 'submit',"sWidth": "100px;","sClass": "left"},
                {"onblur": 'submit',"sWidth": "","sClass": "left"},
                {"onblur": 'submit',"sWidth": "100px;","sClass": "center"}
				
                {"onblur": 'submit',"sWidth": "40px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "50px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "60px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "40px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "50px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "60px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "20px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "50px","sClass": "right"}
				],
            "sPaginationType": "full_numbers"}).makeEditable({
            sUpdateURL: '<?php echo base_url() . "pre_dispatch/saveEditedRecord"; ?>'

        });
        //$('#showDataTable').dataTable()
       //$("#search_param").val("{search_value}");
       }
	   */
    });
</script>

<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
	<thead>
		<tr>
			<th>Location Id</th>
			<th>Location Code</th>
			<th>View</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($data as $value){ ?>
		<tr>
			<td><?php echo $value['location_id'];?></td>
			<td><?php echo $this->encode->tis620_to_utf8($value['location_code']);?></td>
			<td><a href="<?php echo site_url();?>/putaway/addProductByLocation?id=<?php echo $value['location_id'];?>">Add Product this Location</a></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
<!--

<style>
 input.btn2{
	 margin-bottom:0px;
}

</style>

<table cellpadding="2" cellspacing="0" border="0" class="display" id="ShowDataTableForInsert">
<thead>
	<tr class="header">
		<th>Select All <input type="checkbox" name="check_all" id="check_all" value="1"></th>
		<th>Location Id</th>
		<th>Location Code</th>
	</tr>
</thead>
<tbody role="alert" aria-live="polite" aria-relevant="all">

	<?php
	if(count($data)==0){
	?>
	<tr><td colspan="3">Not found</td></tr>
	<?php
	}
	else{
		$i=1;
	foreach($data as $value){
	?>
	<tr>
		<td><input type="checkbox" name="location_id[]" id="code-<?php echo $i;?>" value="<?php echo $value['location_id'];?>" /></td>
		<td><?php echo $value['location_id'];?></td>
		<td><?php echo $this->encode->tis620_to_utf8($value['location_code']);?></td>
	</tr>
	<?php 
		$i++;
		} 
	}
	?>
</tbody>
</table>
<div id="green" style="margin: auto;background:#E6F1F6;width:99%;border:none;">
</div>
-->