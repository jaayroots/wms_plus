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
			////$('#frm_date').datepicker('hide');
		}).keypress(function(event) {event.preventDefault();}).bind("cut copy paste",function(e) {
			  e.preventDefault();
		  });
		$( "#to_date" ).datepicker({onRender: function(date) {
			var edate=$('#frm_date').val();
			//var rdate=$('#receive_date').val();
			from = edate.split("/");
			f = new Date(from[2], from[1] - 1, from[0]);
			//alert(f);
			return date.valueOf() < f.valueOf() ? 'disabled' : '';
        }}).on('changeDate', function(ev){
			//$('#sDate1').text($('#datepicker').data('date'));
			//$('#to_date').datepicker('hide');
		}).keypress(function(event) {event.preventDefault();}).bind("cut copy paste",function(e) {
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
			$('#frm_date').val('');
			$('#to_date').val('');
			$('#product_id').val('');
			//$('#count_type').val('');
			$('#lot').val('');
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
		/*
		$("#product").keyup(function(){
			$.post('<?php echo site_url();?>/report/showProductList',{text_search:$(this).val()},function (data,val){
				$('#suggestions').show();
				$('#autoSuggestionsList').html(data,val);
			});
		});


		*/
    });


	function find_data(){

			//alert('search');
			var product=$('#product').val();
			var fdate=$('#frm_date').val();
			var tdate=$('#to_date').val();
			var product_id=$('#product_id').val();
			var lot=$('#lot').val();
			var count_type=$('#count_type').val();
			/*
			if(product=="" && fdate=="" && tdate==""){
				alert('Please select "Product" or "Start Date" or "To Date"');
				$('#report').html('Please select "Product" or "Start Date" or "To Date"');
			}
			else{
			*/
				$('#report').html('<img src="<?php echo  base_url()?>/images/ajax-loader.gif" />');
				//alert('ok');

				$.ajax({
					type: 'post',
					url: '<?php echo site_url();?>/counting_report/search_counting', // in here you should put your query
					data: 'product=' + product + '&product_id='+product_id+'&fdate='+fdate+'&tdate='+tdate+'&lot='+lot+'&count_type='+count_type, // here you pass your id via ajax
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
                <legend>Search Criteria</legend>
                <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    <tr>
                        <td valign="top">
                            <?php echo _lang('product_code'); ?> :
                        </td>
                        <td>
                            <!--<input type="text"  id="product" name="product" autocomplete="off" onkeyup="lookup('product',this.value);"  onblur="fill('','','');"; />
							<div class="suggestionsBox" id="suggestions" style="display:none;">
								<div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
							 </div>-->
							 <table id="table_of_productCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; padding: 0px; width: 470px;">
                                <div style="position: relative;">
                                    <?php echo form_input("product", "", "id='product' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 420px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                    <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='padding: 2px 6px; margin: 0px; height: auto; width: 420px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?>
                                </div>
                            </table>
							<input type="hidden" id="product_id" name="product_code" />
                        </td>
						<td>
							<?php echo _lang('lot') ?>
						</td>
						<td>
							<input type="text" placeholder="<?php echo _lang('lot') ?>" id="lot" name="lot" >
						</td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td>
							<?php echo _lang('counting_type') ?>:
						</td>
						<td>
							<select name="count_type" id="count_type">
								<option value="3" selected="selected">Daily</option>
								<option value="4">Criteria</option>
							</select>
						</td>
                        <td>
                           <?php echo _lang('start_date'); ?> :
                        </td>
                        <td>
                            <input type="text" placeholder="Date Format" id="frm_date" name="frm_date" value="<?php echo date("d/m/Y");?>" >
                        </td>
                        <td>
                           <?php echo _lang('to_date'); ?> :
                        </td>
                        <td>
                             <input type="text" placeholder="Date Format" id="to_date" name="to_date" value="<?php echo date("d/m/Y");?>" >
                        </td>

                    </tr>
                   <tr>
					 <td colspan="6" align="center">
                            				<input type="submit" style="display: none;">
						<input type="button" name="search" value="Search" id="search" class="button orange" />
						<input type="button" name="clear" value="Clear" id="clear" class="button orange" />
					 </td>
				   </tr>
                </table>

            <!--</fieldset>-->

            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
			</fieldset>
        </form>

		<fieldset>
			<legend>Search Result</legend>
				<div id="report" style="margin:10px">
					Please click search
				</div>
		</fieldset>

    </TD>
</TR>