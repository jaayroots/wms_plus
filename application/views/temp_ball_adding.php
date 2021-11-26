
<form class="well form-inline" method="POST" action="" id="frmReceive" name="frmReceive">
   <fieldset style="margin:0px auto;">
      <legend>Add List Criteria </legend>
      <table cellpadding="1" cellspacing="1" style="width:98%; margin:0px auto;">
         <tbody>
            <tr>
               <td>Location Code
               </td>
               <td>
                  <!-- <input id="location_m" type="text" value="" > -->

                  <table id="table_of_locationCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; padding: 0px; width: 210px;">
                                <div style="position: relative;">
                                    <?php echo form_input("locaton_code", "", "id='locaton_code' placeholder='"._lang('Locaton Code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 210px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                    <?php echo form_input("highlight_locationCode", "", "id='highlight_locationCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 210px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?> 
                                </div>
                            </table>
							<input type="hidden" id="location_id" name="location_id" />
                            
               </td>
               <td>Product Code
               </td>
               <td>
               <table id="table_of_productCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; padding: 0px; width: 210px;">
                                <div style="position: relative;">
                                    <?php echo form_input("product", "", "id='product' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 210px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                    <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 210px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?> 
                                </div>
                            </table>
							<input type="hidden" id="product_id" name="product_id" />

               </td>
               <td></td>
               <td></td>
               <td>
               </td>
            </tr>
            <tr>
               <td>Re Order Point
               </td>
               <td>
               <input id="reorder_point_m" type="number" value="" onblur="validation_re_order_max()" >
                </td>
               <td>Max
               </td>
               <td>
               <input id="max_m" type="number" value="" onblur="validation_re_order_max()">
                </td>
               <td></td>
               <td></td>
               <td>
                  <input type="button" name="Add" value="Add" id="Add" onclick="add_list()" class="button dark_blue">
                  <input type="button" name="clear" value="Clear" id="clear "onclick="clear_input()" class="button orange">
               </td>
            </tr>
         </tbody>
      </table>
   </fieldset>
   <input type="hidden" name="queryText" id="queryText" value="">
   <input type="hidden" name="search_param" id="search_param" value="">
</form>
<script>

$(document).ready(function() {
        
        /**
        * Search Product Code By AutoComplete
        */

        $("#locaton_code").autocomplete({
           minLength: 0,
           search: function( event, ui ) {
               $('#highlight_productCode').attr("placeholder",'');
           },
           source: function( request, response ) {        
             $.ajax({
                 url: "<?php echo site_url(); ?>/replenishment_/get_location",
                 dataType: "json",
                 type:'post',
                 data: {
                   text_search: $('#locaton_code').val()
                 },
                 success: function( val, data ) {
   
                     if(val != null){
                        var flag_set_product_id = true;
                        response( $.map( val, function( item ) {
                           if(flag_set_product_id){
                            $('#location_id').val(item.Location_Id);
                            flag_set_product_id = false;
                           }
                         return {
                           label: item.Location_Code,
                           value: item.Location_Id
                         }
                       }));
                     }
                 },
             });
           },
           open: function( event, ui ) {
               var auto_h = $(window).innerHeight()-$('#table_of_locationCode').position().top-50;
               $('.ui-autocomplete').css('max-height',auto_h);
           },
           open: function( event, ui ) {
               var auto_h = $(window).innerHeight()-$('#table_of_locationCode').position().top-50;
               $('.ui-autocomplete').css('max-height',auto_h);
           },
           focus: function( event, ui ) {
               $('#highlight_locationCode').attr("placeholder", ui.item.label);
               return false;
           },
         //   focus: function( event, ui ) {
         //       $('#highlight_productCode').attr("placeholder", ui.item.label);
         //       return false;
         //   },
         //   select: function( event, ui ) {
         //       $('#highlight_productCode').attr("placeholder",'');
         //       $('#product_id').val( ui.item.value );
         //       $('#product').val( ui.item.label );
         //     return false;
         //   },
           select: function( event, ui ) {
               $('#highlight_locationCode').attr("placeholder",'');
               $('#locaton_id').val( ui.item.value );
               $('#locaton_code').val( ui.item.label );
             return false;
           },
         //   close: function(){
         //       $('#highlight_productCode').attr("placeholder",'');
         //   }
           close: function(){
               $('#highlight_locationCode').attr("placeholder",'');
           }
       });


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
           open: function( event, ui ) {
               var auto_h = $(window).innerHeight()-$('#table_of_locationCode').position().top-50;
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

    function validation_re_order_max(){
       let reorder_point_m = document.getElementById("reorder_point_m").value;
       let max_m = document.getElementById("max_m").value;
       if(parseInt(reorder_point_m) >= parseInt(max_m)){
        document.getElementById("max_m").value = "";
        document.getElementById("reorder_point_m").value = "";
        alert("Can't Re Order Point Over Max");
        return false;
       }
    }
   function clear_input() {
     document.getElementById("locaton_code").value = "";
     document.getElementById("product").value = "";
     document.getElementById("reorder_point_m").value = "";
     document.getElementById("max_m").value = "";
   }
   var count = 1;
   function add_list(){
       let product_code_add_id = document.getElementById("product_id").value;
       let location_add = document.getElementById("locaton_code").value;
       let product_code_add = document.getElementById("product").value;
       let product_code_post_id = document.getElementById("product_id").value;
       let reorder_point_add = document.getElementById("reorder_point_m").value;
       let max_add = document.getElementById("max_m").value;
       let location_product = [product_code_add_id, location_add]
      //  console.log(JSON.stringify(location_product));
       var data = new FormData();
         data.append("Recheck_location_product", JSON.stringify(location_product));

         var xhr = new XMLHttpRequest();
         xhr.withCredentials = true;

         xhr.addEventListener("readystatechange", function() {
            // console.log(await this.responseText);
            
         if(this.readyState === 4) {
            var res_check = JSON.parse(this.responseText);
            if(res_check.location_code == false ){
               alert("Location Code Not Found");
               document.getElementById("locaton_code").value = "";
               document.getElementById("locaton_code").focus();
               return
            }
            if(res_check.product_id == false ){
               alert("Product Code Not Found!");
               document.getElementById("product").value = "";
               document.getElementById("product").focus();
               return
            }
            if(res_check.location_code == true  && res_check.product_id == true){
               
            if (location_add == "" || product_code_add == "" || reorder_point_add == "" || max_add == "") {
                  alert("must be filled out");
                  return false
            }
            var parent = document.getElementById('adding_detail');
            var newChild = '<tr class="'+ class_tr +'" id="a'+count+'"> <td width="50" class="  sorting_1">' + count + '<input type="hidden" name="Location' + count + '" value="' + location_add + '" /></td> <td class="text-center-row">'+ location_add +'<input type="hidden" name="product_code' + count + '" value="' + product_code_post_id + '" /></td><td class="text-center-row">'+ product_code_add +'</td><input type="hidden" name="re_order_point' + count + '" value="' + reorder_point_add + '" /><td class="text-center-row">'+ reorder_point_add +'</td><td class="text-center-row">'+ max_add +'<input type="hidden" name="max' + count + '" value="' + max_add + '" /></td><td class="text-center-row" onclick="delete_list('+ count +')"><img src="<?php echo base_url()?>/css/images/icons/del.png" alt=""></td>';
            parent.insertAdjacentHTML('beforeend', newChild);
            count++;
            clear_input();
            }
         }
      });
            xhr.open("POST", "<?php echo base_url()?>/index.php/replenishment_/recheck_location_product");
            xhr.send(data);
           if(count % 2 == 0){
           var class_tr = "list_row_click odd";
           }
           else{
           var class_tr = "list_row_click even";
           }


   }
   function delete_list(delete_list){
        document.getElementById('a'+delete_list+'').remove();
        document.getElementById('a'+delete_list+'').innerHTML = "";
   }
   
       
</script>
<fieldset>
   <div id="report" style="margin:10px">
      <style> 
         table.dd tr:nth-child(odd) td.uom_qty{
         background-color: #ccc;
         }
         table.dd tr:nth-child(even) td.uom_qty{
         background-color: #EEEED1;
         }
         table.dd tr:nth-child(odd) td.uom_unit_prod{
         background-color: #ccc;
         }
         table.dd tr:nth-child(even) td.uom_unit_prod{
         background-color: #EEEED1;
         }
      </style>
               <form method="post" action="<?php echo base_url()?>index.php/replenishment_/get_list">

         <div id="tbreport_wrapper" class="dataTables_wrapper" role="grid">
            <div class="fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">
               <div id="tbreport_length" class="dataTables_length">
               </div>
               <!-- <div class="dataTables_filter" id="tbreport_filter"><label>Search: <input type="text" aria-controls="tbreport"></label></div> -->
            </div>
            <table id="tbreport" class="dd dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="tbreport_info">
               <thead>
                  <tr role="row">
                  <th class="uom_unit_prod border-top ui-state-default" style="width: 80px;" role="columnheader" tabindex="0" aria-controls="tbreport" rowspan="1" colspan="1" aria-label="Unit/Product: activate to sort column ascending">
                        <div class="DataTables_sort_wrapper unuse_sort">No<span ></span></div>
                     </th>
                  <th class="uom_unit_prod border-top ui-state-default" style="width: 80px;" role="columnheader" tabindex="0" aria-controls="tbreport" rowspan="1" colspan="1" aria-label="Unit/Product: activate to sort column ascending">
                        <div class="DataTables_sort_wrapper unuse_sort">Location Code<span ></span></div>
                     </th>                     <th class="uom_unit_prod border-top ui-state-default" style="width: 80px;" role="columnheader" tabindex="0" aria-controls="tbreport" rowspan="1" colspan="1" aria-label="Unit/Product: activate to sort column ascending">
                        <div class="DataTables_sort_wrapper unuse_sort">Product Code<span ></span></div>
                     </th>                     <th class="uom_unit_prod border-top ui-state-default" style="width: 80px;" role="columnheader" tabindex="0" aria-controls="tbreport" rowspan="1" colspan="1" aria-label="Unit/Product: activate to sort column ascending">
                        <div class="DataTables_sort_wrapper unuse_sort">Re Order Point<span ></span></div>
                     </th>                     <th class="uom_unit_prod border-top ui-state-default" style="width: 80px;" role="columnheader" tabindex="0" aria-controls="tbreport" rowspan="1" colspan="1" aria-label="Unit/Product: activate to sort column ascending">
                        <div class="DataTables_sort_wrapper unuse_sort">Max<span ></span></div>
                     </th>
                     </th>                     <th class="uom_unit_prod border-top ui-state-default" style="width: 80px;" role="columnheader" tabindex="0" aria-controls="tbreport" rowspan="1" colspan="1" aria-label="Unit/Product: activate to sort column ascending">
                        <div class="DataTables_sort_wrapper unuse_sort">Delete<span ></span></div>
                     </th>
 
                  </tr>
               </thead>
               <tfoot>
                  <tr>
                     <th colspan="6" class="ui-state-default" rowspan="1"></th>
                  </tr>
               </tfoot>
               <!-- <form method="post" action="<?php echo base_url()?>index.php/temp_ball/get_list"> -->
               <tbody id="adding_detail"role="alert" aria-live="polite" aria-relevant="all">

               
               </tbody>
            </table>
         </div>
         <div >

         <input type="submit" value="Submit" class="button dark_blue">
         <a href="<?php echo base_url()?>index.php/replenishment_/replenishment_adding"><input type="submit" value="Cancel" class="button orange"></a>
      </form>
   </div>
</fieldset>
</div>
</fieldset>

    
