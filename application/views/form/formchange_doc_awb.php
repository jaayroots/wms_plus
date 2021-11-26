<script>
    $(document).ready(function() {
        $( "#frm_date" ).submit({
		});

        $('#defDataTable2').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
            "sPaginationType": "full_numbers"});

			$('#search').click(function(){

			var fdate=$('#frm_date').val();
			var tdate=$('#to_date').val();
		$('#report').html('<img src="<?php echo  base_url()?>/images/ajax-loader.gif" />');

				$.ajax({
					type: 'post',
					url: '<?php echo site_url();?>/change_doc_awb/get_document', 
					data: 'fdate='+fdate+'&tdate='+tdate,
					success: function(data)
					{
						$("#report").html(data);
						$("#pdfshow").show();
						$("#excelshow").show();
					
					}
				});
			//}
		});

		$('#clear').click(function(){
			$('#frm_date').val('');
			$('#to_date').val('');
			$('#report').html('Please click search');
            $("#pdfshow").hide();
            $("#excelshow").hide();
            //END ADD
		});
    });

    
</script>
<style>

</style>
<TR class="content" style='height:100%' valign="top">
    <TD>
       <form action="<?php echo site_url();?>/change_doc_awb/" method="post" target="_blank" id="form_hide">
	   <div class="well">
	   <fieldset>
		  <legend>Search Document</legend>
		  <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    <tr>
                        <td>Document  :
                        </td>
                        <td>
                            <input type="text"  id="frm_date" name="frm_date"  >
                        </td>

						<td>
						<input type="button" name="search" value="Search" id="search" class="button dark_blue" />
						<input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
						</td>
						<td></td>
						<td></td>
                    </tr>

                </table>
	   </fieldset>
	   </div>
</form>
 <fieldset>
  <legend>Search Result</legend>
  <div style="margin:5px auto;width:98%;">
	<div id="report" style="margin:10px auto;text-align:center;">
			Please click search
	</div>
</div>
</fieldset>
    </TD>
</TR>
