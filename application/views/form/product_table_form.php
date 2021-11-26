<?php
//print_r($data);
//include "header.php";
?>
<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->

<script>
    $(document).ready(function() {
        var oTable=$('#defDataTable2').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
            "aoColumns": [
                {"sWidth": "50px;","sClass": "center"},
                {"sWidth": "150px;","sClass": "left"},
                {"sWidth": "","sClass": "left"}
				],
            "sPaginationType": "full_numbers"
		});

		$('#check_all').click(function(){
			var selected = new Array();
			$(oTable.fnGetNodes()).find(':checkbox').each(function () {
				$this = $(this);
				$this.attr('checked', 'checked');
				selected.push($this.val());
			});
					// convert to a string
					//var mystring = selected.join();
					//alert(mystring);
		});
		$('#uncheck_all').click(function(){
			//$('input[id^=code-]').attr('checked',false);
			var selected = new Array();
			$(oTable.fnGetNodes()).find(':checkbox').each(function () {
				$this = $(this);
				$this.attr('checked', false);
				selected.push($this.val());
			});
		});
		
		$('#formPtoL').submit(function(){
			var sData = $('input', oTable.fnGetNodes()).serialize();
			//alert( "you select : \n\n"+sData );
			
			var data_form = $("#formPtoL").serialize();
			//alert( " your form "+data_form);
			var message = "";			
			$.post("<?php echo site_url(); ?>"+"/putaway/saveProductByLocation",data_form+'&'+sData,function(data){ 
					//alert(data.status);
					switch (data.status){
						case 'C001':  message = "Save Product To Location Complete";     break;
						case 'C002':  message = "This zone not have location";  break;
						case 'C000':  message = "Please select Product";  break;

					}
					alert(message);
					url = "<?php echo site_url(); ?>/"+data.url;
					redirect(url)

			},"json");
			
			return false;
		});
	});
</script>
<div style="margin:10px auto;">
	<input type="button" name="check_all" id="check_all" value="Select All" class="btn success" data-toggle="modal" role="button" />
	<input type="button" name="uncheck_all" id="uncheck_all" value="Unselect All" class="btn success" data-toggle="modal" role="button" />
</div>
<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
	<thead>
		<tr>
			<th>Select All</th>
			<th><?php echo _lang('product_code'); ?></th>
			<th><?php echo _lang('product_name'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$i=1;
		foreach($data as $value){		
		?>
		<tr>
			<td><input type="checkbox" name="product_code[]" id="code-<?php echo $i;?>" value="<?php echo $value['code'];?>" /></td>
			<td><?php echo $value['code'];?></td>
			<td><?php echo $this->encode->tis620_to_utf8($value['name']);?></td>
		</tr>
		<?php
			$i++;
		}
		?>
	</tbody>
</table>