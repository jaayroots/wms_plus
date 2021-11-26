<script>

    $(document).ready(function() {
        $('#defDataTable2').dataTable({"bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
			"sPaginationType": "full_numbers"});
            initProductTable();

	});
    function initProductTable() {
$('#defDataTable2').dataTable({
    "bJQueryUI": true,
    "bAutoWidth": false,
    "bSort": false,
    "bRetrieve": true,
    "bDestroy": true,
    "sPaginationType": "full_numbers",
    "sDom": '<"H"lfr>t<"F"ip>',
    "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
    },
    "aoColumnDefs": [
        {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
        {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
        {"sWidth": "20%", "sClass": "left_text", "aTargets": [2]},
        {"sWidth": "7%", "sClass": "left_text", "aTargets": [3]}, // Edit by Ton! 20131001
    ]
}).makeEditable({

            "aoColumns": [
                  null
                , null
                , null
                ,{ 
                 sSortDataType: "dom-text",
                 onblur:"submit",
                 event: 'click',
                 sUpdateURL: "<?php echo site_url() . "/change_doc_awb/editDataTable"; ?>",
                 fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 24-10-2013
                
                    }
         
                }
        , null
       
    ]
}); 

$('#submit').click(function(){
			var fdate=$('#txtDocument_No').val();
            console.log(fdate);
            });

}


function save(save){

    var rowData2 = $('#defDataTable2').dataTable().fnGetData();
 
	console.log(save); 
    if(save){
    setTimeout(function(){ 
    if (confirm("Save this data?") == true) {

        
     $.post("<?php echo site_url(); ?>/change_doc_awb/edit_documentAWB", {id: save, data : rowData2}, function(result) {
                if (result == true) {
                    message = "Save DocumentAWB Complete";
                        alert(message);
                        // window.location = "<?php echo site_url() ?>/change_doc_awb/index";
                }else{
                    message = "Not Save This DocumentAWB";
                        alert(message);
                }

            }, "json");
        }
    }, 1000);
}

}

  
</script>
<form  method="post" target="_blank" id="form_report">
<input type="hidden" name="fdate" value="<?php echo $search['fdate'];?>" />
<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
	<thead>
            <tr>
                <th style="display:none;"></th>
                <th ><?php echo _lang('Document_No'); ?></th>
                <th><?php echo _lang('Doc_Refer_Ext'); ?></th>
                <th><?php echo _lang('Doc_Refer_AWB'); ?></th>
        
          
            </tr>
	</thead>
	<tbody>
		<?php
			foreach($data as $key=>$value){
      
		?>
			<tr>
                <td  style="display:none;" ><?php echo $value->Order_id;?></td>
			    <td  id="txtDocument_No" ><?php echo $value->Document_No;?></td>
                <td id="txtDoc_Refer_Ext"><?php echo $value->Doc_Refer_Ext;?></td>
                <td id="txtDoc_Refer_AWB"><?php echo $value->Doc_Refer_AWB;?></td>
			</tr>
                        
		<?php 	}	?>
	</tbody>
          
</table>

<div align="center" style="margin-top:10px;">
	<input type="button" value="Save Change DocumentAWB" class="button orange"  onClick="save('save')"  />
	 &emsp;&emsp;
</div>

</form>