<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>
    //$('#myModal').modal('toggle');
    $(document).ready(function() {
        
        /**
        * Search Product Code By AutoComplete
        */
       $("#product").autocomplete({
           minLength: 0,
           search: function( event, ui ) {
               $('#highlight_productCode').attr("placeholder",'');
           },
           source: function( request, response ) {        
             $.ajax({
                 url: "<?php echo site_url(); ?>/report/ajax_show_product_list",
                 dataType: "json",
                 type:'post',
                 data: {
                   text_search: $('#product').val()
                 },
                 success: function( val, data ) {
                     if(val != null){
                        var flag_set_product_id = true;
                        response( $.map( val, function( item ) {
                           if(flag_set_product_id){
                            $('#product_id').val(item.product_id);
                            flag_set_product_id = false;
                           }
                         return {
                           label: item.product_code + ' ' + item.product_name,
                           value: item.product_id
                         }
                       }));
                     }
                 },
             });
           },
           open: function( event, ui ) {
               var auto_h = $(window).innerHeight()-$('#table_of_productCode').position().top-50;
               $('.ui-autocomplete').css('max-height',auto_h);
           },
           focus: function( event, ui ) {
               $('#highlight_productCode').attr("placeholder", ui.item.label);
               return false;
           },
           select: function( event, ui ) {
               $('#highlight_productCode').attr("placeholder",'');
               $('#product_id').val( ui.item.value );
               $('#product').val( ui.item.label );
             return false;
           },
           close: function(){
               $('#highlight_productCode').attr("placeholder",'');
           }
       });
        
        // Add By Akkarapol, 24/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 24/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

		
        //initProductTable();

        $( "#as_date" ).datepicker({
		  defaultDate: "+1d",
		  changeMonth: true,
		  numberOfMonths: 1,
		  onClose: function( selectedDate ) {
		  }
		}).keypress(function(event) {event.preventDefault();}).on('changeDate', function(ev){
			//$('#sDate1').text($('#datepicker').data('date'));
			//$('#as_date').datepicker('hide');
		}).bind("cut copy paste",function(e) {
			  e.preventDefault();
		  });
		
                $('#frmPreDispatch').submit(function(){
                    find_data();
                    return false;
		});
                
		$('#search').click(function(){
                    find_data();
		});

		$('#clear').click(function(){
			$('#product').val('');
			$('#as_date').val('');
			$('#warehouse_id').val('');
			$('#category_id').val('');
			$('#period').val('');
			$('#step').val('');
			$('#product_id').val('');
			$('#report').html('Please click search');
                        
                        //ADD BY POR 2013-11-05 กำหนดให้ซ่อนปุ่ม print report 
                        $("#pdfshow").hide();
                        $("#excelshow").hide();
                        //END ADD
		});
		
		$("#product").click(function(){
			$('#product').val('');
			$('#product_id').val('');
                        $('#highlight_productCode').attr("placeholder",'');
		});
		
		$("#period").keyup(function () { 
			this.value = this.value.replace(/[^0-9\.]/g,'');
		});

		$("#step").keyup(function () { 
			this.value = this.value.replace(/[^0-9\.]/g,'');
		});
    });

    function find_data(){
        
        var statusisValidateForm = validateForm();
        if(statusisValidateForm === true  ){
            var as_date=$('#as_date').val();
            var renter_id=$('#renter_id').val();
            var warehouse_id=$('#warehouse_id').val();
            var category_id=$('#category_id').val();
            var product_id=$('#product_id').val();
            var period=$('#period').val();
            var step=$('#step').val();
            var by=$('#by').val();

            if(product_id=="" && as_date=="" && warehouse_id=="" && category_id=="" && by=="" && product_id=="" && period=="" && step==""){
                alert('Please select "Warehouse" or "<?php echo _lang('product_category') ?>" or "<?php echo _lang('product_code') ?>" or "As Date" or "Period" or "Step"');
                $('#report').html('Please select "Warehouse" or "<?php echo _lang('product_category') ?>" or "<?php echo _lang('product_code') ?>" or "As Date" or "Period" or "Step"');
            }
            else{
                //step=parseInt(step);
                if(by=="DAY" && step.length==1){						
                                alert('Please input Remain step more than 9 days');
                                $('#report').html('Please input Remain step more than 9 days');
                //BY POR 2013-10-08 เพิ่มเติมเงื่อนไข กรณีไม่ได้เลือก DAY จะต้องกรอก Remain Step > 0 
                //Start
                }else if(by!="DAY" && step==0){
                    alert('Please input Remain step more than 0');
                    $('#report').html('Please input Remain step more than 0');  
                }
                //END
                else{
                        $('#report').html('<img src="<?php echo  base_url()?>/images/ajax-loader.gif" />');

                        $.ajax({
                                type: 'post',
                                //--#Comment 2013-08-26 #ISSUE NO:2236/DEFECT 328
                                //--# Start --
                                //url: '<?php echo site_url();?>/report/showAgingReport', // in here you should put your query 
                                //--# End Comment 2013-08-26 #ISSUE NO:2236/DEFECT 328 --


                                //--#ISSUE NO:2236/DEFECT 328
                                //--#DATE:2013-08-26
                                //--#BY:POR
                                //-- START--
                                url: '<?php echo site_url();?>/report/showAgingReportReceiveApp', // in here you should put your query 
                                //-- END #ISSUE NO:2236/DEFECT 328--

                                data: 'renter_id=' + renter_id + '&warehouse_id='+warehouse_id+'&category_id='+category_id+'&product_id='
                                          +product_id+'&as_date='+as_date+'&period='+period+'&step='+step+'&by='+by, 
                                success: function(data)
                                {
                                        //alert(data);

                                        $("#report").html(data);

                                        //--#Comment 2013-09-05 BY POR#2236 นำออกเนื่องจากใช้ Datatable ถ้ายังคงไว้ จะไม่สามารถใช้ datatable ได้
                                        //--# Start --
                                        /*
                                        inittable();
                                        */
                                        //--# END--

                                        //ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
                                        $("#pdfshow").show();
                                        $("#excelshow").show();
                                        //END ADD
                                }
                        });
                }
            }
        } // close if validate ok
        else{
            alert("Please Check Your Require Information (Red label).");
            return false;
        }  
    }

	function inittable(){
		var oTable= $('#tbreport').dataTable({
			"bJQueryUI": true,
            "bSort": false,
            //"bAutoWidth": false,
			"sPaginationType": "full_numbers",
			//"sScrollY": "900px",
			"sScrollX": "100%",
			//"sScrollXInner": "200%",
			"bScrollCollapse": true,

	   });

		new FixedColumns( oTable, {
               "iLeftColumns": 3
               	,"sLeftWidth": 'relative'
				,"iLeftWidth": 40
				//,"iRightColumns": 1
				//,"iRightWidth": 7
				//,"sRightWidth": 'relative'
				
		});
	}

	function validateForm() {        
        //validate engine 
            var status;
            $("form").each(function () {
                $(this).validate();
                $(this).valid();
                status = $(this).valid();
            });
            return status;            
       //end of validate engine
    }

	function lookup(inputVar,text_search) {
		if(text_search.length != 0) {
			$.post("<?php echo site_url();?>/report/showProductList", {text_search:text_search}, function(val,data){
				if(data.length >0) {
					$('#suggestions').show();
					$('#autoSuggestionsList').html(val,data);	
					//$("#receive_phone").attr("disabled", "disabled");				
				}
			});
		}
	}
	function fill(id,code,name){
			$('#product').val(code+' '+name);
			$('#product_id').val(id);
			setTimeout("$('#suggestions').hide();", 500);
	}   
</script>
<style>
    #myModal {
        width: 1024px; /* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -512px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
	#report{
		margin:5px;
		text-align:center;
	}
	.suggestionsBox {
		position: absolute;
		width: 200px;
		background-color: #f2f2f2;
		-moz-border-radius: 2px;
		-webkit-border-radius: 2px;
		border: 1px solid #333;
		color:#333;
		/*margin-top: 10px;*/
		margin-top:1px;
		margin-right: 0px;
		margin-bottom: 0px;
		margin-left: 0px;
		padding-right: 2px;
		padding-left: 2px;
		font-size:11px;	
		z-index:100;
   }
	
	.suggestionList {
		margin: 0px;
		padding-top: 0px;
		padding-right: 2px;
		padding-bottom: 2px;
		padding-left: 2px;
		height:200px;
	   overflow:scroll;
	}
	
	.suggestionList ul {
		list-style:none;
	}
	
	.suggestionList li {
		margin: 0px 0px 3px 0px;
		padding: 3px;
		cursor: pointer;
		list-style-type: none;
		/*background-color: #E8E8E8;*/
		color:#000000;
	}
	
	.suggestionList li:hover {
		background-color: #659CD8;
	}
</style>
<TR class="content" style='height:100%' valign="top">
    <TD>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmPreDispatch" name="frmPreDispatch" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>
		    
                <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
					<tr>
						<td>Renter</td>
						<td><?php echo form_dropdown('renter_id', $renter_list,$renter_id,'id="renter_id" class="required" ');  ?></td>
						<td>Warehouse</td>
						<td><?php echo form_dropdown('warehouse_id', $warehouse_list,'','id="warehouse_id" ');  ?></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
                    <tr>
						<td>Product Category</td>
						<td><?php echo form_dropdown('category_id', $category_list,'','id="category_id"');  ?></td>
                        <td valign="top">
                            <?php echo _lang('product_code') ?>
                        </td>
                        <td>
                            <table id="table_of_productCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; padding: 0px; width: 210px;">
                                <div style="position: relative;">
                                    <?php echo form_input("product", "", "id='product' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 210px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                    <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 210px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?> 
                                </div>
                            </table>
<!--                            <input type="text"  id="product" name="product" autocomplete="off" onkeyup="lookup('product',this.value);"  onblur="fill('','','');"; />
							<div class="suggestionsBox" id="suggestions" style="display:none;">
								<div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
							 </div>-->
							<input type="hidden" id="product_id" name="product_id" />
                        </td>
						<td>As of Date</td>
						<td> <input type="text" placeholder="Date Format" id="as_date" name="as_date" value="<?php echo date("d/m/Y"); ?>" class="required" ></td>
						<td></td>
					</tr>
					<tr>
                        <td>Remain By
                        </td>
                        <td>
                           <select name="by" id="by">
								<option value="DAY">Day</option>
								<option value="MONTH">Month</option>
								<option value="YEAR">Year</option>
						   </select>
                        </td>
                        <td>Remain Step
                        </td>
                        <td>
                             <input type="text" placeholder="Integer" id="step" name="step" >
                        </td>
						<td><!--Remain Period--></td>
						<td><!--<input type="text" placeholder="Integer" id="period" name="period" >--></td>
						<td >
                                                <input type="submit" style="display: none;">
						<input type="button" name="search" value="Search" id="search" class="button dark_blue" />
						<input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
						</td>
                    </tr>
                    
                </table>
				
            </fieldset>
            
            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
        </form>    
        
		   <fieldset>
		        <legend>Search Result </legend>
				<div id="report" style="margin:10px">
					Please click search
				</div>
		   </fieldset>
    </TD>
</TR>