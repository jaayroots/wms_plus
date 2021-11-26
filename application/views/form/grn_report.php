<?php
ini_set('memory_limit', '2048M');
?>
<!--<script type="text/javascript" language="javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.js";?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->

<script>
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

			$('#search').click(function(){
			//alert('search');
			
			var fdate=$('#frm_date').val();
			var tdate=$('#to_date').val();
			/*
			if(fdate=="" && tdate==""){
				alert('Please select  "Start Date" or "To Date"');
				$('#report').html('Please  "Start Date" or "To Date"');
			}
			else{
			*/	
				$('#report').html('<img src="<?php echo  base_url()?>/images/ajax-loader.gif" />');

				$.ajax({
					type: 'post',
					url: '<?php echo site_url();?>/report/searchGRN', // in here you should put your query 
					data: 'fdate='+fdate+'&tdate='+tdate, // here you pass your id via ajax 
					success: function(data)
					{
						//alert(data);
						//console.log(data);
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
</script>
<style>
   
</style>
<TR class="content" style='height:100%' valign="top">
    <TD>
       <form action="<?php echo site_url();?>/report/exportGRNToPDF" method="post" target="_blank">
	   <div class="well">
	   <fieldset>
		  <legend>Search Criteria</legend>
		  <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    <tr>
                        <td>
                           Date Start : <!--change languages : kik-->
                           
                        </td>
                        <td>
                            <input type="text" placeholder="Date Format" id="frm_date" name="frm_date" value="<?php echo date("d/m/Y");?>" >
                        </td>
                        <td>
                            Date End :<!--change languages : kik-->
                        </td>
                        <td>
                             <input type="text" placeholder="Date Format" id="to_date" name="to_date" value="<?php echo date("d/m/Y");?>">
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
	   <fieldset>
		  <legend>Search Criteria</legend>
		  <div style="margin:5px auto;width:98%;">
			<div id="report" style="margin:10px auto;text-align:center;">
					Please click search
			</div>

		</div>
		</fieldset>

</form>
    </TD>
</TR>