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
	       var	flag_set_product_id = true;
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
            $('#product').val( ui.item.label ); //โชว์อะไรในช่อง
            $('#product_id').val(ui.item.value); //ค่าที่ต้องการ assign
          return false;
        }
    });	
        //initProductTable();

        $( "#frm_date" ).datepicker({
		  defaultDate: "+1d",
		  changeMonth: true,
		  numberOfMonths: 1,
		  onClose: function( selectedDate ) {
			$( "#to_date" ).datepicker( "option", "minDate", selectedDate );
		  }
		}).on('changeDate', function(ev){
			//$('#sDate1').text($('#datepicker').data('date'));
			//$('#frm_date').datepicker('hide');
		});
		$( "#to_date" ).datepicker({
		  defaultDate: "+1d",
		  changeMonth: true,
		  numberOfMonths: 1,
		  onClose: function( selectedDate ) {
			$( "#frm_date" ).datepicker( "option", "maxDate", selectedDate );
		  }
		}).on('changeDate', function(ev){
			//$('#sDate1').text($('#datepicker').data('date'));
			//$('#to_date').datepicker('hide');
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
			$('#frm_date').val('');
			$('#to_date').val('');
			$('#product_id').val('');
			$('#doc_value').val('');
			$('#report').html('Please click search');
			
			//ADD BY POR 2013-11-05 กำหนดให้ซ่อนปุ่ม print report 
            $("#pdfshow").hide();
            $("#excelshow").hide();
            //END ADD
		});
		
		$("#product").click(function(){
			$('#product').val('');
			$('#product_id').val('');
		});
    });


        function find_data(){

            //alert('search');
            var product=$('#product').val();
            var fdate=$('#frm_date').val();
            var tdate=$('#to_date').val();
            var product_id=$('#product_id').val();
            var activity=$('#activity').val();
            var doc_type=$('#doc_type').val();
            var doc_value=$('#doc_value').val();

            //--#ISSUE001/BUG001#1
            //--#DATE:2013-08-29
            //--#BY:POR
            //-- START--
            var txtby;
            if(activity=='Putaway'){
                txtby='Putaway By';
            }else if(activity=='Picking'){
                txtby='Picking By';
            }else if(activity=='Relocation'){
                txtby='Relocation By';
            }
            //--END
            if(product=="" && fdate=="" && tdate=="" && activity==""){
                    alert('Please select "Product" or "Activity" or "Start Date" or "To Date"');
                    $('#report').html('Please select "Product" or "Activity" or "Start Date" or "To Date"');
            }
            else{

                    $('#report').html('<img src="<?php echo  base_url()?>/images/ajax-loader.gif" />');

                    $.ajax({
                            type: 'post',
                            url: '<?php echo site_url();?>/report/searchPTLocation', // in here you should put your query 
                            //--#Comment 2013-08-29 #ISSUE NO/#BUG NO & Description
                            //--# Start --
                            /* data: 'product=' + product + '&product_id='+product_id+'&fdate='+fdate+'&tdate='+tdate+"&activity="+activity+"&doc_type="+doc_type+"&doc_value="+doc_value,*/ // here you pass your id via ajax 
                            //-- #End --

                            //--#Add value txtby
                            //--#DATE:2013-08-29
                            //--#BY:POR
                            //-- START --
                            data: 'product=' + product + '&product_id='+product_id+'&fdate='+fdate+'&tdate='+tdate+"&activity="+activity+"&doc_type="+doc_type+"&doc_value="+doc_value+"&txtby="+txtby, // here you pass your id via ajax
                            //-- End --
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
            }
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
            <!--<fieldset style="margin:0px auto;">
                <legend>Transfer Order</legend>
		    -->
                <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    <tr>
                        <td valign="top">
                            <?php echo _lang('product_code') ?> :
                        </td>
                        <td>
<!--                            <input type="text"  id="product" name="product" autocomplete="off" onkeyup="lookup('product',this.value);"  onblur="fill('','','');"; />
							<div class="suggestionsBox" id="suggestions" style="display:none;">
								<div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
							 </div>-->
                            <table id="table_of_productCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; padding: 0px; width: 290px;">
                                <div style="position: relative;">
                                                <?php echo form_input("product", "", "id='product' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 290px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                    <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 290px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?> 
                                        </div>
                            </table>
							<input type="hidden" id="product_id" name="product_code" />
                        </td>
						<td>Activity</td>
						<td>
						  <select name="activity" id="activity">
							<option value="Putaway">Putaway</option>
							<option value="Picking">Picking</option>
							<option value="Relocation">Relocation</option>
						  </select>
						</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
				    </tr>
					</tr>
                        <td>
                          From Date :
                        </td>
                        <td>
                            <input type="text" placeholder="Date Format" id="frm_date" name="frm_date" >
                        </td>
                        <td>
                            To Date :
                        </td>
                        <td>
                             <input type="text" placeholder="Date Format" id="to_date" name="to_date" >
                        </td>
						<td colspan="3">
						<input type="submit" style="display: none;" />
						<input type="button" name="search" value="Search" id="search" class="button dark_blue" />
						<input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
						</td>
						<td></td>
                    </tr>
                    <tr>
						<td>Document Field
                        </td>
                        <td>
                            <select name="doc_type" id="doc_type">
								<option value="Document_No"><?php echo _lang('document_no') ?></option>
								<option value="Doc_Refer_Ext"><?php echo _lang('doc_refer_ext') ?></option>
								<option value="Doc_Refer_Int"><?php echo _lang('doc_refer_int') ?></option>
								<option value="Doc_Refer_Inv"><?php echo _lang('doc_refer_inv') ?></option>
								<option value="Doc_Refer_CE"><?php echo _lang('doc_refer_ce') ?></option>
								<option value="Doc_Refer_BL"><?php echo _lang('doc_refer_bl') ?></option>
							</select>
							<input id="doc_value" class="input-small" type="text" placeholder="VALUE" value="" name="doc_value">
                        </td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
                </table>
				
            <!--</fieldset>-->
            
            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
        </form>    
        
		<fieldset>
			<legend>Search Result</legend>
				<div id="report" style="margin:10px">
					Please click search
				</div>
		</fieldset>
    </TD>
</TR>
