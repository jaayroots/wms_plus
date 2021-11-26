<?php // Create by Ton! 20130521 ?>
<SCRIPT>

    $(document).ready(function(){  
        $('#xfile').val('');
        $("input[type=submit]").attr("disabled", "disabled");
    });
    $(document).ready(function() {
        $( "#frm_date" ).datepicker({
		  defaultDate: "+1d",
		  changeMonth: true,
		  numberOfMonths: 1,
		  onClose: function( selectedDate ) {
			$( "#to_date" ).datepicker( "option", "minDate", selectedDate );
		  }
		}).on('changeDate', function(ev){
			//$('#frm_date').datepicker('hide');
		}).keypress(function(event) {event.preventDefault();}).bind("cut copy paste",function(e) {
			  e.preventDefault();
		  });

		$( "#to_date" ).datepicker({onRender: function(date) {
			var edate=$('#frm_date').val();
			from = edate.split("/");
			f = new Date(from[2], from[1] - 1, from[0]);;
			return date.valueOf() < f.valueOf() ? 'disabled' : '';
        }}).on('changeDate', function(ev){
			//$('#to_date').datepicker('hide');
		}).keypress(function(event) {event.preventDefault();}).bind("cut copy paste",function(e) {
			  e.preventDefault();
		  });

        $('#defDataTable2').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
				/*
            "aoColumns": [
                {"sWidth": "50px;","sClass": "center"},
                {"sWidth": "150px;","sClass": "center"},
                {"sWidth": "","sClass": "left"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"},
				{"sWidth": "","sClass": "center"}

				],
				*/
            "sPaginationType": "full_numbers"});

			$('#submit').click(function(){
			var fdate=$('#frm_date').val();
			var tdate=$('#to_date').val();
			
			if(fdate==""){
				alert('Please select  "Search Date"');
				$('#report').html('Please  "Search Date"');
                return false;
			}

           
			// else{
			// */
				$('#report').html('<img src="<?php echo  base_url()?>/images/ajax-loader.gif" />');

				$.ajax({
					type: 'post',
					url: '<?php echo site_url();?>/import_inventory_movement/upload', // in here you should put your query
					data: 'fdate='+fdate+'&tdate='+tdate, // here you pass your id via ajax
					success: function(data)
					{
						//alert(data);
						$("#report").html(data);

						//ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
						$("#pdfshow").show();
						$("#excelshow").show();
						//END ADD

					}
				});
			//}
		});

		$('#clear').click(function(){

			$('#frm_date').val('');
			$('#to_date').val('');

			$('#report').html('Please click search');

			//ADD BY POR 2013-11-05 กำหนดให้ซ่อนปุ่ม print report
            $("#pdfshow").hide();
            $("#excelshow").hide();
            //END ADD
		});
    });

    function checkfile(sender) {
        var validExts=new Array(".xlsx", ".xls", ".csv");
        var fileExt=sender.value;
        fileExt=fileExt.substring(fileExt.lastIndexOf('.'));
        if (validExts.indexOf(fileExt) < 0) {
            alert("Invalid file selected, valid files are of " + validExts.toString() + " types.");
            $("input[type=submit]").attr("disabled", "disabled");
            return false;
        }
        else $("input[type=submit]").removeAttr("disabled"); return true;
    }
    
    function download_template(){
        window.open('<?php echo site_url('import_inventory_movement/load_template'); ?>', '_blank');
    }

    
</SCRIPT>

<HTML>
    <HEAD>
        <TITLE> import_Inventory_movement </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmImportPreReceive" NAME="frmImportPreReceive" METHOD='post' ENCTYPE="multipart/form-data" ACTION="<?php echo site_url()?>/import_inventory_movement/upload">
            <TABLE  border="1">
                <TR>
                        <td>Search Date :</td>
                        <td><input type="text" placeholder="Date Format" id="frm_date" name="frm_date" value="<?php echo date("d/m/Y");?>" ></td>
                        <td>To :</td>
                        <td><input type="text" placeholder="Date Format" id="to_date" name="to_date" value="<?php echo date("d/m/Y");?>" ></td>
<!--						<td>
						 <input type="button" name="search" value="Search" id="search" class="button dark_blue" />
						<input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" /> 
						</td>
						<td></td>
						<td></td>-->
                </tr>

                <TR>
                    <TD>Select file for import : </TD>
                    <TD><input type="file" name="xfile" id="xfile" onchange="checkfile(this);"/></TD>
                    <TD></TD>
                    <TD><INPUT TYPE="submit" CLASS="button dark_blue" VALUE="SUBMIT" ONCLICK="" ID="submit">
                   <INPUT TYPE="button" CLASS="button dark_blue" VALUE="Load Import Template" ONCLICK="javascript:download_template();" ID="load_template"></TD>
                </TR>   
                <div id="response"></div>
                <ul id="image-list"></ul>
            </TABLE>
        </FORM>
    </BODY>
</HTML>

