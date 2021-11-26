<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<?php echo link_tag(base_url("css/themes/smoothness/custom_checkbox.css"));?>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/custom_checkbox.js") ?>"></script>
<script>
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Re-Location by product'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = '#showProductTable'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/replenishment"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ

    var oTable = null;
    var present_state = '<?php echo $present_state ?>';
    var separator = "<?php echo SEPARATOR; ?>"; // Add By Akkarapol, 22/01/2013, Add Separator for use in Page
    var allVals = new Array();
    var dispatch_type;
    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
    var statusprice = "<?php echo $price_per_unit; ?>"; //add by kik : 20140115

    var conf_pallet = '<?php echo ($conf_pallet) ? true : false; ?>';
    var ci_item_id          = 20;
    var ci_prod_code        = 1;
    var ci_prod_name        = 2;
    var ci_product_status   = 3;
    var ci_product_sub_status   = 4;
    var ci_product_lot      = 5;
    var ci_product_serial   = 6;
    var ci_est_balance      = 7;
    var ci_reserv_qty       = 8;
    var ci_old_loc_id       = 12;
    var ci_sug_loc_id       = 13;
    var ci_act_loc_id       = 14;
    var ci_remark           = 15;
    var ci_inbound_id       = 16;

//  add by kik : 2014-01-14
    var ci_price_per_unit = 9;
    var ci_unit_price = 10;
    var ci_all_price = 11;
    var ci_unit_price_id = 17;
    var ci_pallet_code = 18;
    var ci_dp_type_pallet = 19; //ADD BY POR 2014-02-19

    //ADD BY POR 2014-03-12 ส่งชื่อ location ไปด้วยเพื่อไปแสดงใน realtime pdf
    var ci_old_loc_name=21;
    var ci_sug_location_name = 22;
    var ci_act_loc_name=23;
    //END ADD

	var error_count = 0;
	var error_location = 0;

//  end add by kik : 2014-01-14
    var build_pallet = '<?php echo $this->config->item('build_pallet');?>'; //ADD BY POR เรียกสถานะ build_pallet ว่ามีการใช้หรือไม่


    var ci_list = [
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_prod_name', value: ci_prod_name},
        {name: 'ci_product_status', value: ci_product_status},
        {name: 'ci_product_sub_status', value: ci_product_sub_status},
        {name: 'ci_product_lot', value: ci_product_lot},
        {name: 'ci_product_serial', value: ci_product_serial},
        {name: 'ci_est_balance', value: ci_est_balance},
        {name: 'ci_reserv_qty', value: ci_reserv_qty},
        {name: 'ci_old_loc_id', value: ci_old_loc_id},
        {name: 'ci_sug_loc_id', value: ci_sug_loc_id},
        {name: 'ci_act_loc_id', value: ci_act_loc_id},
        {name: 'ci_price_per_unit', value: ci_price_per_unit},      // add by kik : 2014-01-15
        {name: 'ci_unit_price', value: ci_unit_price},              // add by kik : 2014-01-15
        {name: 'ci_all_price', value: ci_all_price},                // add by kik : 2014-01-15
        {name: 'ci_remark', value: ci_remark},
        {name: 'ci_inbound_id', value: ci_inbound_id},
        {name: 'ci_unit_price_id', value: ci_unit_price_id},         // add by kik : 2014-01-15
        {name: 'ci_pallet_code', value: ci_pallet_code},         // ADD BY POR 2014-02-19
        {name: 'ci_dp_type_pallet', value: ci_dp_type_pallet} ,       // ADD BY POR 2014-02-19

        //ADD BY POR 2014-03-12 ส่งชื่อที่ไม่มีรูปแว่นขยายติดไปด้วย
        {name: 'ci_old_loc_name', value: ci_old_loc_name},
        {name: 'ci_sug_location_name', value: ci_sug_location_name},
        {name: 'ci_act_loc_name', value: ci_act_loc_name}
        //END ADD
    ]


    var ci_prod_no = 0;
    var ci_prod_document = 1;
    var ci_prod_product_code = 2;
    var ci_prod_name = 3;
    //var ci_prod_status = 4;
    //var ci_prod_sub_status = 5;
    var ci_prod_rec_date = 6;
    var ci_prod_lot = 7;
    var ci_prod_sel = 8;
    var ci_prod_mfd = 9;
    var ci_prod_exp = 10;
    var ci_prod_pallet = 11;


        function show_hide_column(this_event){
            var count_colspan_before_reserve_qty = 0;
            var count_colspan_before_suggest_location = 0;
            var start_count_colspan_after_confirm_qty = false;
            this_event.parent().children('input').each(function(key, value){
                if($(this).attr('id')==='relocation_job_move_qty'){
                    if(count_colspan_before_reserve_qty == 0){
                        $('#colspan_before_move_qty').hide();
                    }else{
                        $('#colspan_before_move_qty').show();
                        $('#colspan_before_move_qty').attr('colspan',count_colspan_before_reserve_qty);
                    }
                }else if($(this).attr('id')==='relocation_job_location_from'){
                    start_count_colspan_after_confirm_qty = true;
                }

                if(start_count_colspan_after_confirm_qty){
                    if($(this).is(':checked')){
                        count_colspan_before_suggest_location = count_colspan_before_suggest_location+1;
                    }
                }

                if($(this).is(':checked')){
                    count_colspan_before_reserve_qty = count_colspan_before_reserve_qty+1;
                }
            });

            if(count_colspan_before_suggest_location == 0){
                $('#colspan_before_location_from').hide();
            }else{
                $('#colspan_before_location_from').show();
                $('#colspan_before_location_from').attr('colspan',count_colspan_before_suggest_location);
            }

            var tmp_column = this_event.attr('id').replace('relocation_job','rlc');
            if(this_event.is(':checked')){
                $('.'+tmp_column).show();
            }else{
                $('.'+tmp_column).hide();
            }
        }

    $(document).ready(function() {

        $( "#div_relocation_job" ).buttonset();

        // function for show/hide column
        $('#div_relocation_job input').on("change", function(event){
            show_hide_column($(this));
        });


        /**
        * Search Product Code By AutoComplete
        */
       $("#productCode").autocomplete({
           minLength: 0,
           search: function( event, ui ) {
               $('#highlight_productCode').attr("placeholder",'');
           },
           source: function( request, response ) {
             $.ajax({
                 url: "<?php echo site_url(); ?>/product_info/ajax_show_product_list",
                 dataType: "json",
                 type:'post',
                 data: {
                   text_search: $('#productCode').val()
                 },
                 success: function( val, data ) {
                     if(val != null){
                        response( $.map( val, function( item ) {
                         return {
                           label: item.product_code + ' ' + item.product_name,
                           value: item.product_code
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
               $('#productCode').val( ui.item.value );
             return false;
           },
           close: function(){
               $('#highlight_productCode').attr("placeholder",'');
           }
       });

        calculate_qty(); //ADD BY POR 2014-02-20 เรียกใช้ function เพื่อให้รองรับกรณี build pallet ในขั้นตอน confirm
        // Ball
        //$("#datepicker_test").datepicker();

        // Bind Data First
        actualLocation(); // bind event
        // add by kik (08-10-2013)
        // start script filter getDetail
        // script use togeter element_filtergetDetail
        $('#getBtn').click(function() {
            if ($('#productCode').val() == "") {
                alert("Please fill <?php echo _lang('product_code'); ?>");
                $('#productCode').focus();
                return false;
            }

            //ถ้ามีการเรียกใช้ build_pallet ให้ตรวจสอบเพิ่มเติม
//            if($('#productCode').val()=="" && $('#palletCode').val() =="" && build_pallet==1){
//                alert("Please fill Product Code or Pallet Code");
//                $('#productCode').focus();
//                return false;
//            } else {
                showModalTable();

                // Edit By Akkarapol, 22/01/2014,set dataTable bind search when enter key
                $('#modal_data_table_filter label input')
                .unbind('keypress keyup')
                .bind('keypress keyup', function(e){
                    if (e.keyCode == 13){
                      oTable.fnFilter($(this).val());
                    }
                });
                // Edit By Akkarapol, 22/01/2014,set dataTable bind search when enter key

            //}
        });

        //Product Mfd
        $("#productMfd").datepicker().keypress(function(event) {
        }).on('changeDate', function(ev) {
            //$('#productMfd').datepicker('hide');
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        //Product Exp
        $("#productExp").datepicker().keypress(function(event) {
        }).on('changeDate', function(ev) {
            //$('#productExp').datepicker('hide');
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $('#formProductCode').submit(function() {

            $('#getBtn').click();
            return false;

        });
        /**
         Bind click when search for all location
         */
        function actualLocation() {
            $('.actual_location').on('keyup focus', function(e) {

                var move_qty = parseFloat($(this).parent().parent().children('td.cell_move_qty').html());
                if (move_qty == 0 || isNaN(move_qty)) {
                    alert('Please fill move quantity first.');
                    $(':focus').blur();
                    return ;
                }

                var _this = this;
                $('#suggestion_div').css({top: ($(this).offset().top + 30), left: $(this).offset().left}).show();
                $.post("<?php echo site_url('/reLocation/getLocationAll'); ?>", {criteria: $(this).val()}, function(response)
                {
                    $('#suggestionLocationList').html(''); // clear old list
                    $.each(response, function(index, value)
                    {
                        var lists = $('<li>').attr('class', 'actual_location_id').attr('data-id', value.Location_Code).html(value.Location_Code);
                        $('#suggestionLocationList').append(lists);
                    });
                    actualLocationClick(_this);
                });
            }).blur(function() {
            	var _this = this;
                 setTimeout(function() {

                    if ($(_this).val() != "")
                    {
                        $.get("<?php echo site_url(); ?>" + "/location/check_exist_location", {location_code: $(_this).val()}, function(data)
                        {
                        //    alert(data);exit();
                            if (data == "null")  //EDIT BY POR 2014-06-12 แก้ไขให้เตือนว่า location ไม่ถูกต้องกรณีที่ไม่มีอยู่จริง
                            {
                                alert('Sorry!!, This actual location not found, Please select another location.');
                                $(_this).val($(_this).data('act_location'));
                                error_location = -1; // set -1 เพราะว่า พอคืนค่าแล้วก็ไม่มีปัญหาอะไร
                            } else {
                                error_count = 0;
                                error_location = 0;
                                $(_this).data('act_location', $(_this).val());
                            }
                        });
                    }
                    if ($(_this).val() != "") {
                        $.get("<?php echo site_url(); ?>" + "/replenishment/get_location", {location_code: $(_this).val()}, function(result)
                        {
                            console.log(result);  
                        // return;
                            if (result != "null")  
                            {
                            alert('Sorry!!, Can not choose this location, Please select another location.'); 
                            $(_this).val($(_this).data(''));
                                error_location = -1; 
                    
                            } 
                            else {
                                error_location = 0;
                                $(_this).data('act_location2', $(_this).val());
                             
                            }
                          });
                    }

                    $('#suggestion_div').hide();
                }, 200);
            });
        }

        function actualLocationClick(selector)
        {
            $('.actual_location_id').on('click', function(e)
            {
                var elm = $(selector);
                $(selector).attr('value', $(this).text()).removeClass('required');

                // START Create Temp [draft version]
                var input = '<input class="' + elm.attr('class') + '" placeholder="' + elm.attr('placeholder') + '" value="' + $(this).text() + '" data-act_location="'  + '" data-act_location2="' + $(this).text() + '" />';
                var oTable = $('#showProductTable').dataTable(); // get datatables
                var indexOfRow = oTable.fnGetPosition($(selector).closest('tr').get(0)); // find rows index
                oTable.fnUpdate(input, indexOfRow, ci_act_loc_id); // update new data to datatables
                actualLocation(); // re-bind event again
                // END

            });
        }

        function suggesLocationClick(selector)
        // console.log(selector); 
        {
            $('.suggest_location_id').on('click', function(e)
            {
                var elm = $(selector);
          
                $(selector).attr('value', $(this).text()).removeClass('required');

                // START Create Temp [draft version]
                var input = '<input class="' + elm.attr('class') + '" placeholder="' + elm.attr('placeholder') + '" value="' + $(this).text() + '" data-inbound_id="' + elm.data('inbound_id') + '" data-status="' + elm.data('status') + '" data-sub_status="' + elm.data('sub_status') + '" data-category="' + elm.data('category') + '">';
                // console.log(input); return;
                var oTable = $('#showProductTable').dataTable(); // get datatables
                var indexOfRow = oTable.fnGetPosition($(selector).closest('tr').get(0)); // find rows index
                oTable.fnUpdate(input, indexOfRow, ci_sug_loc_id); // update new data to datatables
                //   console.log(input); return;
                suggestLocation(); // bind event again
                // END

            });
        }

        function suggestLocation()
        {
            $('.suggest_location').on('keyup focus', function(e)
            {

//                var move_qty = parseInt($(this).parent().prev().prev().html());

                var move_qty = parseFloat($(this).parent().parent().children('td.cell_move_qty').html());

                if (move_qty == 0 || isNaN(move_qty)) {
                    alert('Please fill move quantity first.');
                    $(':focus').blur();
                    return ;
                }
                var _this = this;
                // console.log(_this); return;
                $('#suggestion_div').css({top: ($(this).offset().top + 30), left: $(this).offset().left}).show();
                $.post("<?php echo site_url('/reLocation/show_suggest_list_by_product'); ?>", {criteria: $(this).val(), inbound_id: $(this).data('inbound_id'), type: 1, qty: move_qty}, function(response)
                {
                          
                    $('#suggestionLocationList').html(''); // clear old list
                    $.each(response, function(index, value)
                    {
                        var lists = $('<li>').attr('class', 'suggest_location_id').attr('data-id', value.Id).html(value.Location_Code);
                        $('#suggestionLocationList').append(lists);
                    });
                    
                    suggesLocationClick(_this);
                });
            }).blur(function() {
				var _this = this;
                setTimeout(function() {
                    $('#suggestion_div').hide();
//      if ($(_this).val() != "")
                        {
                        $.get("<?php echo site_url(); ?>" + "/replenishment/get_location", {location_code: $(_this).val()}, function(result)
                        {
                            console.log(result); 
                        // exit();
                            if (result != "null")  
                            {
                                alert('Sorry!!, Can not choose this location, Please select another location.'); 
                                $(_this).val($(_this).data(''));
                                error_location = -1; 
                            } 
                            else {
                                error_count = 0;
                                error_location = 0;
                                $(_this).data('', $(_this).val());
                            }
                          });
                    }
                }, 200);
            });
        }
        // End Ball

        // Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง


        // Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="worker_id"]').change(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        //$("#est_relocate_date").datepicker();

        $("#est_relocate_date").datepicker({
        		//autoclose: true,
        		//startDate: '0d',
        		//format: "dd/mm/yyyy",
        		onRender: function(date) {
					return date.valueOf() < now.valueOf() ? 'disabled' : '';
				}
		}).keypress(function(event) {
				event.preventDefault();
        }).on('changeDate', function(ev) {
            //$('#sDate1').text($('#datepicker').data('date'));

			//if (ev.viewMode == "days") {
				////$('#est_relocate_date').datepicker('hide');
	            // Add By Akkarapol, 21/09/2013, เพิ่ม การเช็คว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
	            if ($(this).val() != '') {
	                $(this).removeClass('required');
	            } else {
	                $(this).addClass('required');
	            }
	            // Add By Akkarapol, 21/09/2013, เพิ่ม การเช็คว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
			//}

        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $("#relocate_date").datepicker({onRender: function(date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }}).keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
            //$('#relocate_date').datepicker('hide');

            // Add By Akkarapol, 21/09/2013, เพิ่ม การเช็คว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
            // Add By Akkarapol, 21/09/2013, เพิ่ม การเช็คว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        getSelectLocationOption();

        $('#select_all').click(function() {
            var cdata = $('#modal_data_table').dataTable();
//            allVals = new Array(); // Comment By Akkarapol, 29/11/2013, คอมเม้นต์ทิ้งเพราะถ้าเกิดเซ็ตให้ allVals มันเป็น new Array() แล้ว จะทำให้ ค่าที่เคยเลือกไว้หายไปด้วย แล้วจะได้ค่าที่ไม่ตรงตามความต้องการ จึงจำเป็นต้องปิดส่วนนี้ไป
            $(cdata.fnGetNodes()).find(':checkbox').each(function() {
                $this = $(this);
                $this.attr('checked', 'checked');
                allVals.push($this.val());
            });
            //alert('select all '+allVals);
        });

        $('#deselect_all').click(function() {
            var selected = new Array();
            var cdata = $('#modal_data_table').dataTable();
            $(cdata.fnGetNodes()).find(':checkbox').each(function() {
                $this = $(this);
                $this.attr('checked', false);
                selected.push($this.val());
                allVals.pop($this.val());
            });
            allVals = new Array();
        });

        $('#search_submit').click(function() {
            var dataSet = {
                post_val: allVals,
                dp_type_pallet_val : dispatch_type //ADD BY POR 2014-02-19 ส่งค่า dispatch_type ไปยัง datatable ด้วย
            }
            $.post('<?php echo site_url() . "/reLocationProduct/showProduct" ?>', dataSet, function(data) {
                //var pstatus="NORMAL";
                //console.log(data);
                var suggest_location = "";
                var actual_location = "";
                var move_qty = "";
                var remark = "";
                var allprice=0;
                var price=0;
                var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
                //+' <a ONCLICK="showProduct('+item.Location_Id+')"><?php echo img("css/images/icons/view.png"); ?></a>'
                $.each(data.locations, function(i, item) {
                    //add by kik : 20140115
                        if(item.Price_Per_Unit != ""){
                            price=item.Price_Per_Unit;
                        }
                    //end add by kik : 20140115
                    //ADD BY POR 20114-02-19 ตรวจสอบกรณี dispatch_type=FULL จะไม่สามารถแก้ไข qty ได้เนื่องจากเราจะ change ทั้ง pallet
                    if(item.DP_Type_Pallet == "FULL"){
                        move_qty = item.Balance_Qty; //ให้มีค่าเท่ากับ Balance_Qty เนื่องจากบังคับให้ยกไปทั้งก้อน
                        //del = "DEL";
                        del = "<a ONCLICK=\"removePalletItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>"; // ADD BY POR 2014-02-20 credit by kik
                    }

                    $('#showProductTable').dataTable().fnAddData([
                            recordsTotal
                                , item.Product_Code
                                , item.Product_NameEN
                                , item.Product_Status
                                , item.Product_Sub_Status //add by kik : 04-098-2013
                                , item.Product_Lot
                                , item.Product_Serial
                                , item.Balance_Qty
                                , set_number_format(move_qty)
                                , set_number_format(price)                     //add by kik : 20140115
                                , item.Unit_Price_value     //add by kik : 20140115
                                , set_number_format(allprice)                  //add by kik : 20140115
                                , item.Location_Code + ' <a href="javascript:;" ONCLICK="showProduct(\'\',\'' + item.Location_Code + '\')"><?php echo img("css/images/icons/view.png"); ?></a>'
                                , '<input type="text" class="required suggest_location" name="suggestion[]" placeholder="Search here.." value="" data-inbound_id="'+item.Inbound_Id+'" data-status="' + item.Product_Status + '" data-sub_status="' + item.Product_Sub_Status_Code + '" data-category="'+item.Product_Category_Id+'" />' //'<span>Click to edit</span>'
                                , '<input type=\"text\" class=\"required actual_location\" placeholder=\"Search here..\" value=\"\" />'
                                , remark
                                , item.Inbound_Id
                                , item.Unit_Price_Id        //add by kik : 20140115
                                , item.Pallet_Code //ADD BY POR 2014-02-19 เพิ่มให้แสดง Pallet_Code ด้วย
                                , item.DP_Type_Pallet       //ADD BY POR : 2014-02-19
                                , "new"
                                , item.Location_Code
                                , null
                                , actual_location
                                , del]
                            );
                    calculate_qty();
                      // add by kik : 2013-11-13
                    var new_td_item =  $('td:eq(1)', $('#showProductTable tr:last'));
                    new_td_item.addClass("td_click");
                    new_td_item.attr('onclick','showProductEstInbound('+'"'+item.Product_Code+'"'+','+item.Inbound_Id+')');
                    // end add by kik

                    var obj =  $('td:eq(7)', $('#showProductTable tr:last'));
                    obj.addClass("set_number_format");
                    var obj =  $('td:eq('+ci_reserv_qty+')', $('#showProductTable tr:last'));
                    obj.addClass("cell_move_qty");

                    if(item.DP_Type_Pallet == "FULL"){
                        var dp_type_full = $('td:eq('+ci_reserv_qty+')', $('#showProductTable tr:last'));
                        dp_type_full.addClass("readonly");
                    }

                    recordsTotal++;     //add for edit No. to running number : by kik : 20140226

                });
                initProductTable();
                suggestLocation();
                actualLocation();
            }, "json");
            $('.modal.in').modal('hide');
            allVals = new Array();

        });

        // Add By Ball
        // Prevent unwant redirect pages
        window.onbeforeunload = function() {
            return "You have not yet saved your work.Do you want to continue? Doing so, may cause loss of your work?";
        };

        // If found key F5 remove unload event!!
        $(document).bind('keydown keyup', function(e) {
            if (e.which === 116) {
                window.onbeforeunload = null;
            }
            if (e.which === 82 && e.ctrlKey) { // F5 with Ctrl
                window.onbeforeunload = null;
            }
        });
        // Add By Ball

        var recordsTotal = $('#showProductTable').dataTable().fnSettings().fnRecordsTotal() + 1;//add for edit No. to running number : by kik : 20140226


        $('#div_relocation_job').children('input').each(function(key, value){
            show_hide_column($(this));
        });

    });

    function getSelectLocationOption() {
        $.post('<?php echo site_url(); ?>/reLocation/genLocationSelectOption', function(data) {
            //$('#location_list').val(data);
            initProductTable();
        });
    }

    function showProduct(location_id, location_code) {
        var dataSet = {location_id: location_id, location_code: location_code};
        var tmp = '';
        $('#prdModalval').val(location_id);
        $.post('<?php echo site_url() . "/reLocation/showProductInLocation" ?>', dataSet, function(data) {
            $('#boxDetail #myModalLabel').html(location_code + ' Product Detail ');
            $("#boxDetail .modal-body").html(data);

            $('#defDataTable').dataTable({
                "bJQueryUI": true,
                "bAutoWidth": false,
                "bSort": false,
                "oSearch": {},
                "bRetrieve": true,
                "bDestroy": true,
                "sPaginationType": "full_numbers",
                "aoColumnDefs": [
                             {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                             {"sWidth": "7%", "sClass": "center", "aTargets": [1]},
                             {"sWidth": "5%", "sClass": "center", "aTargets": [2]},
                             {"sWidth": "20%", "sClass": "left_text", "aTargets": [3]},
                             {"sWidth": "7%", "sClass": "left_text", "aTargets": [4]},
                             {"sWidth": "7%", "sClass": "left_text", "aTargets": [5]},
                             {"sWidth": "7%", "sClass": "left_text", "aTargets": [6]},
                             {"sWidth": "7%", "sClass": "left_text", "aTargets": [7]},
                             {"sWidth": "7%", "sClass": "left_text", "aTargets": [8]},
                             {"sWidth": "7%", "sClass": "center", "aTargets": [9]},
                             {"sWidth": "7%", "sClass": "center", "aTargets": [10]},
                             {"sWidth": "7%", "sClass": "center", "aTargets": [11]},
                             {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [12]}
                ]
            });

            if(!conf_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
                $('#defDataTable').dataTable().fnSetColumnVis(ci_prod_pallet, false);
            }

            $('#boxDetail').modal('show');
        }, "html");


    }

    $('#myModal').modal('toggle').css({
        // make width 90% of screen
        'width': function() {
            return ($(document).width() * .9) + 'px';
        },
        // center model
        'margin-left': function() {
            return -($(this).width() / 2);
        }
    });
    function get_location(value,rowIndex,callback) {
    
    var rowData2 = $('#showProductTable').dataTable().fnGetData();
    $.get('<?php echo site_url() . "/replenishment/get_location" ?>', { location_code : value, prodid : rowData2[rowIndex][19]}, function(result) {
console.log(result);
    if (result != "null")  
                            {
                                alert('Sorry!!, Can not choose this location, Please select another location.'); 
                               var result =  "";
                                error_location = -1; 
                                callback(result)
                            } 
                            else {
                                error_count = 0;
                                error_location = 0;
                                var result =  value;
                                callback(result)
                               
                            }
                          });

}
    function initProductTable() {
        $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "aoColumnDefs": [
                {"sWidth": "7%", "sClass": "center", "aTargets": [0]},
                {"sWidth": "10%", "sClass": "center", "aTargets": [1]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [2]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [3]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [4]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [5]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [6]},
                {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [7]},
                {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [8]},
                {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [9]},
                {"sWidth": "10%", "sClass": "center", "aTargets": [10]},
                {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [11]},
                {"sWidth": "10%", "sClass": "center", "aTargets": [12]},
                {"sWidth": "10%", "sClass": "center", "aTargets": [13]},
                {"sWidth": "10%", "sClass": "center", "aTargets": [14]},
                {"sWidth": "10%", "sClass": "left_text", "aTargets": [15]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [16]},
            ]
        }).makeEditable({
                    sUpdateURL: function(value, settings) {
                        return value;
                    }
                    , "aoColumns": [
                        null
                        , null
                        , null
                        , null
                        , null
                        , null
                        , null
                        , null
                        , {
                            onblur: 'submit',
                            "cssclass": "required number",
                            event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Confirm นี้ โดยการ คลิกเพียง คลิกเดียว
                            fnOnCellUpdated: function(sStatus, sValue, settings){   // add fnOnCellUpdated for update total qty : by kik : 28-10-2013
                                calculate_qty();
                            }
                        }
                        //{price_per_unit_table} //COMMENT BY POR 2014-03-12
                        , null
                        , null
                        , null
                        , null
                        , { //remark
                            onblur: 'submit',
                            event: 'click',
                            sUpdateURL: function(value, settings) {
                                console.log(value); 
            var oTable = $('#showProductTable').dataTable();
            var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
            var getdata = get_location(value,rowIndex,function(data){
               console.log(data);
               oTable.fnUpdate(data, rowIndex, ci_sug_loc_id);
            });
            return value;
         }
           
    } // MFD
    , 
                        , null
                        , null
                        // , <?php echo $editable_suggestion?> // suggest location
                        //, <?php echo $editable_actual?> // actual location
                        , null
                        , null
                        , null
                        , null
                        , null
                        , null
                        , null
                        , null
        //                , null
        //                , null
        //                , null
            ]
        });

        //COMMENT BY POR 2014-06-10 เนื่องจากไม่ได้นำไปใช้ แค่ alert value ออกมา
        /*
        $("input[name='value']").each(function() {
            alert($(this).val());
        });
        */
        //

        $("#showProductTable tr td span").click(function() {
            //alert($(this).parent().index());
            var column = $(this).parent().index();
            var row = $(this).closest("tr")[0].rowIndex;

            if (column == ci_act_loc_id || column == ci_sug_loc_id) {
//                                    by kik
//                                if(column==9 || column==8){
                //alert('row='+row+' col = '+column);
                var value = $(this).text();
                //alert('value='+value);
                var p_code = $("#showProductTable tr:eq(" + row + ") td:eq("+ci_prod_code+")").text();
                var l_code = $("#showProductTable tr:eq(" + row + ") td:eq("+ci_reserv_qty+")").text();
                var p_status = $("#showProductTable tr:eq(" + row + ") td:eq("+ci_product_status+")").text();


                //$("#showProductTable tr:eq("+row+") td:eq("+column+")").html(new_data);
                //alert('code='+p_code);

                $('#showProductTable tr td select').each(function() {
                    var other = $(this).val();
                    //alert(' old val ='+other);
                    //showValue(row,cols,value)
                    if (other == "") {
                        $(this).parent().html('<span>Click to edit</span>');
                    }
                    else {
                        var new_value = '<span>' + other + '</span> <a href="javascript:;" ONCLICK="showProduct(\'\',\'' + other + '\')"><?php echo img("css/images/icons/view.png"); ?></a>';
                        $(this).parent().html(new_value);

                    }
                    initProductTable();
                });

                if (value == "Click to edit") {
                    var select_id = '';
                }
                else {
                    var select_id = value;
                }
                if (column == ci_act_loc_id) {
                    var path = 'getSgLocationAll';
                }
                else if (column == ci_sug_loc_id) {
                    var path = 'getSgProductLocation';
                }
//                                        by kik
//                                        if(column==9){
//						var path='getSgLocationAll';
//					}
//					else if(column==8){
//						var path='getSgProductLocation';
//					}
                $.post("<?php echo site_url(); ?>/reLocationProduct/" + path, {product_code: p_code, product_status: p_status, location_code: l_code, select_id: select_id}, function(data) {
                    if (data != "") {
                        //alert(data);
                        //$(this).text(data);
                        //

                        //#ISSUE 2190 Re-Location
//                                                        #DATE:2013-09-04
//                                                        #BY:KIK
//                                                        #เอาปุ่ม ok ออก
//
//                                                        #START New Comment Code #ISSUE 2190
//
                        var new_data = '<select name="suggest_location" id="suggest_location" onchange="showValue(' + row + ',' + column + ',this.value);" class="required" >' + data + '</select>';
                        $("#showProductTable tr:eq(" + row + ") td:eq(" + column + ")").html(new_data);

                        // #End New Comment Code #ISSUE 2190
                        // #=======================================================================================


                        // #Start Old Comment Code #ISSUE 2190
                        //
                        //							var new_data='<select name="suggest_location" id="suggest_location" onchange="showValue('+row+','+column+',this.value);" class="required" >'+data+'</select>'+
                        //							'<input type="button" name="go" value="OK" id="go" onClick="showValue('+row+','+column+',document.getElementById(\'suggest_location\').value);" />';
                        //							$("#showProductTable tr:eq("+row+") td:eq("+column+")").html(new_data);
                        // #=======================================================================================
                        // #End Old Comment Code #ISSUE 2190

                    }

                });
            }

        });
        $('#showProductTable').dataTable().fnSetColumnVis(ci_inbound_id, false);//Edit from 11 to 12 by kik : 04-098-2013
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);//Edit from 11 to 12 by kik : 04-098-2013
        $('#showProductTable').dataTable().fnSetColumnVis(ci_dp_type_pallet, false);// add index ci_dp_type_pallet BY POR 2014-02-19
        $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);//add for edit No. to running number : by kik : 20140226

        //ADD BY POR 2014-03-12 ซ่อน column ชื่อ location เนื่องจากแค่ส่งไปแสดงค่าในหน้า PDF แบบ realtime เฉยๆ แต่ไม่ได้แสดงในหน้า form
        $('#showProductTable').dataTable().fnSetColumnVis(ci_old_loc_name, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_sug_location_name, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_act_loc_name, false);

        if(build_pallet==false){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_pallet_code, false);// add index ci_pallet_code BY POR 2014-02-19
        }
        //add by kik : 20140113

        if(statusprice!=true){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
        }
        //end add by kik : 20140113

    }

    function showValue(row, cols, value) {
        if (value != "") {
            var new_value = '<span>' + value + '</span> <a href="javascript:;" ONCLICK="showProduct(\'\',\'' + value + '\')"><?php echo img("css/images/icons/view.png"); ?></a>';
        }
        else {
            var new_value = '<span>Click to edit<span>';
        }
        var cTable = $('#showProductTable').dataTable();
        var new_r = row - 1;
        cTable.fnUpdate(new_value, new_r, cols);
        //$(cTable[n]).find("td:eq("+row+")").html(new_value)
        $("#showProductTable tr:eq(" + row + ") td:eq(" + cols + ")").html(new_value);
        initProductTable();
    }

    function removeItem(obj) {
        var index = $(obj).closest("table tr").index();
        $('#showProductTable').dataTable().fnDeleteRow(index);
    }

    function removePalletItem(obj) {


        var index = $(obj).closest("table tr").index();

        var datas = $('#showProductTable').dataTable().fnGetData(index);
        var data = datas[ci_pallet_code];

        if (confirm("Do you want to delete all recode in pallet "+data+" ?")) {

            var rowData = $('#showProductTable').dataTable().fnGetData();
            var length_data = (rowData.length)-1;

            for (var i = length_data; i >= 0 ; i--){
                if(data == rowData[i][ci_pallet_code]  && rowData[i][ci_dp_type_pallet] == 'FULL'){
                     $('#showProductTable').dataTable().fnDeleteRow(i);
                }
            }

            calculate_qty();
        }

    }

    function deleteItem(obj) {
        var index = $(obj).closest("table tr").index();
        var data = $('#showProductTable').dataTable().fnGetData(index);
        $('#showProductTable').dataTable().fnDeleteRow(index);
        var f = document.getElementById("form_receive");
        var prodDelItem = document.createElement("input");
        prodDelItem.setAttribute('type', "hidden");
        prodDelItem.setAttribute('name', "prod_del_list[]");
        prodDelItem.setAttribute('value', data);
        f.appendChild(prodDelItem);
    }

    function postRequestAction(module, sub_module, action_value, next_state, elm) {
        global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');

        $("input[name='prod_list[]']").remove();

        //alert('module='+module+'/ sub='+sub_module+'/ actvion='+action_value+'/ '+next_state);

        if (sub_module == 'rejectAction' || sub_module == 'rejectAndReturnAction') {
             var statusisValidateForm = true;
        } else {
             var statusisValidateForm = validateForm();
        }

        if (statusisValidateForm === true) {


            var rowData = $('#showProductTable').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Detail");
                return false;
            }

            $('#showProductTable tr td select').each(function() {
                var other = $(this).val();
                $(this).parent().html('<span>Click to edit</span>');
                initProductTable();
            });

            $('#showProductTable tr td form').each(function() {
                var other = $(this).val();
                //$(this).parent().html('Click to edit');
                //alert(' no ');
                initProductTable();
            });


            var cTable = $('#showProductTable').dataTable().fnGetNodes();
            var cells = [];
            var cells2 = [];
            var cells_sug = [];
            var cells_act = [];
            var chkequalSugAct = "TRUE"; // add by kik : 05-09-2013

<?php if (isset($flow_id)) { ?>
                var qty_name = 'Confirm';
                var compare = 'Move';
<?php } else { ?>
                var qty_name = 'Move';
                var compare = 'Balance';
<?php } ?>

            // add by kik : 05-0-2013
            for (var i = 0; i < cTable.length; i++)
            {
                // Get HTML of 3rd column (for example)
                var balance_qty = $(cTable[i]).find("td:eq("+ci_est_balance+")").html();
                var qty = $(cTable[i]).find("td:eq("+ci_reserv_qty+")").html();

                //alert(qty);
                if (qty == 'Click to edit' || qty == '0') {
                    cells.push($(cTable[i]).find("td:eq("+ci_reserv_qty+")").html());
                    alert('Please input ' + qty_name + ' QTY.');
                    return false;
                }

                //if(qty.contain( "input" )){
                if (qty.indexOf("input") >= 0) {
                    alert('Please input ' + qty_name + ' QTY.');
                    return false;
                }

                //alert(balance_qty+' '+qty);
                //balance_qty = parseInt(balance_qty); //COMMENT BY POR 2013-12-02 เปลี่ยนเป็น float แทน และเอา comma ออก
                balance_qty = parseFloat(balance_qty.replace(/\,/g,'')); //EDIT BY POR 2013-12-02 เปลี่ยนเป็น float แทน และเอา comma ออก
                //qty = parseInt(qty);
                qty=parseFloat(qty.replace(/\,/g,''));//EDIT BY POR 2013-12-02 เปลี่ยนเป็น float แทน และเอา comma ออก
                if (qty < 0) {
                    alert('Negative ' + qty_name + ' QTY is not allow');
                    return false;
                }
                if (qty > balance_qty) {
                    alert("Please change " + qty_name + " QTY less than " + compare + " QTY");
                    return false;
                }


                if(statusprice!=true){
//                    alert('test');
                    var qty2 = $(cTable[i]).find("td:eq("+(ci_sug_loc_id-3)+")").html();
                    var qty2_txt = $(cTable[i]).find("td:eq("+(ci_sug_loc_id-3)+")").text();
                    var qty3 = $(cTable[i]).find("td:eq("+(ci_act_loc_id-3)+")").html();
                    var qty3_txt = $(cTable[i]).find("td:eq("+(ci_act_loc_id-3)+")").text();
                    var remark_txt = $(cTable[i]).find("td:eq("+(ci_remark-3)+")").text();
                }else{
                    var qty2 = $(cTable[i]).find("td:eq("+ci_sug_loc_id+")").html();
                    var qty2_txt = $(cTable[i]).find("td:eq("+ci_sug_loc_id+")").text();
                    var qty3 = $(cTable[i]).find("td:eq("+ci_act_loc_id+")").html();
                    var qty3_txt = $(cTable[i]).find("td:eq("+ci_act_loc_id+")").text();
                    var remark_txt = $(cTable[i]).find("td:eq("+ci_remark+")").text();
                }

//                alert(qty2);return false;
                if (qty2.indexOf("Click to edit") !== -1) {
                    alert('Please select Suggest Location');
                    return false;
                }

                if (qty3.indexOf("Click to edit") !== -1) {
                    cells_act.push($(cTable[i]).find("td:eq("+ci_act_loc_id+")").html());
                }

                // add by kik : 05-09-2013



                if ((qty2_txt != qty3_txt) && remark_txt == "Click to edit") {
                    chkequalSugAct = 'FALSE';
                }// end add by kik : 05-09-2013


            } // end  by kik : 05-0-2013

            var all_location = cells.length;
            if (all_location != 0) {
                alert('Please input ' + qty_name + ' QTY.');
                return false;
            }

            if (cells_sug.length != 0) {
                alert('Please select Suggest Location');
                return false;
            }



            <?php
            if ($present_state != 0) {
            ?>

                // add by kik : 05-09-2013
                if (cells_act.length != 0) {
                    alert('Please select Actual Location');
                    return false;
                } else if (chkequalSugAct == 'FALSE') {
                    alert('Please Check Your Information Remark');
                    return false;
                }

            <?php } ?>

            //return false;

            //if (confirm("Are you sure to action " + action_value + "?")) {

                var f = document.getElementById("form_receive");

                $('input[name="prod_list[]"]').remove();

                var actionType = document.createElement("input");
                actionType.setAttribute('type', "hidden");
                actionType.setAttribute('name', "action_type");
                actionType.setAttribute('value', action_value);
                f.appendChild(actionType);

                var toStateNo = document.createElement("input");
                toStateNo.setAttribute('type', "hidden");
                toStateNo.setAttribute('name', "next_state");
                toStateNo.setAttribute('value', next_state);
                f.appendChild(toStateNo);

                //add for set index in dataTable : by kik : 20140115
                $.each(ci_list, function(i, obj) {
                    var ci_item = document.createElement("input");
                    ci_item.setAttribute('type', "hidden");
                    ci_item.setAttribute('name', obj.name);
                    ci_item.setAttribute('value', obj.value);
                    f.appendChild(ci_item);
                });
                //end add for set index in dataTable : by kik : 20140115

                var oTable = $('#showProductTable').dataTable().fnGetData();

                var error_time = 0;

                var suggest = $(".suggest_location");

                var actual = $(".actual_location");

                for (i in oTable) {

                    var dataArray = [];

                    dataArray[ci_item_id] = oTable[i][ci_item_id];
                    dataArray[ci_prod_code] = oTable[i][ci_prod_code];
                    dataArray[ci_product_status] = oTable[i][ci_product_status];
                    dataArray[ci_product_sub_status] = oTable[i][ci_product_sub_status];
                    dataArray[ci_product_lot] = oTable[i][ci_product_lot];
                    dataArray[ci_product_serial] = oTable[i][ci_product_serial];
                    dataArray[ci_est_balance] = oTable[i][ci_est_balance].replace(/\,/g,''); //ADD BY POR 2013-12-02 (Credit by Ball) ให้ตัด comma ออกก่อนส่งค่า
                    dataArray[ci_reserv_qty] = oTable[i][ci_reserv_qty].replace(/\,/g,''); //ADD BY POR 2013-12-02 (Credit by Ball) ให้ตัด comma ออกก่อนส่งค่า

                    if (sub_module != 'rejectAction' && sub_module != 'rejectAndReturnAction') {    // add for reject document : by kik : 20131226
                        if (dataArray[ci_reserv_qty] == "" || parseFloat(dataArray[ci_reserv_qty]) == 0) {
                            alert('Please specific move QTY');
                            return false;
                        }
                    }


                    // START LOCATION FROM
                    if (present_state == 0 || present_state == 2) {
                        var location_from = oTable[i][ci_old_loc_id].split(" ");
                        dataArray[ci_old_loc_id] = location_from['0'];
                        if(location_from['0'] == "<a"){
                            dataArray[ci_old_loc_id] = "";
                        }
                    } else if (present_state == 1) {
                        var location_from = oTable[i][ci_old_loc_id].split(" ");
                        dataArray[ci_old_loc_id] = location_from['0'];
                    } else {
                        dataArray[ci_old_loc_id] = $(oTable[i][ci_old_loc_id]).val();
                    }
                    dataArray[8] = $.trim(dataArray[8]);
                    // END LOCATION FROM

					// START SUGGESTION
                    if (present_state == 0) {
                        dataArray[ci_sug_loc_id] = $(suggest[i]).val();
                    } else if (present_state == 1 || present_state == 2) {
                        var suggestion_to = oTable[i][ci_sug_loc_id].split(" ");
                        if (suggestion_to['0'] == "<a")
                        {
                            dataArray[ci_sug_loc_id] = "";
                        } else
                        {
                            dataArray[ci_sug_loc_id] = suggestion_to['0'];
                        }
                    } else {
                        alert('error on suggestion');
						return false;
                    }

                    dataArray[ci_sug_loc_id] = $.trim(dataArray[ci_sug_loc_id]);
					// END SUGGESTION

//                    if (present_state == 0) {
//                        dataArray[ci_act_loc_id] = oTable[i][ci_act_loc_id];
//                    } else if (present_state == 1) {
                    	dataArray[ci_act_loc_id] = $(actual[i]).val();
                        if (sub_module != 'rejectAction' && sub_module != 'rejectAndReturnAction' && sub_module != 'openRLProduct') {    // add for reject document : by kik : 20131226
                            if (dataArray[ci_act_loc_id] == "")
                            {
                                error_count += 1;
                                alert("Please specific actual location");
                                return false;
                            }
                        }

//                    } else if (present_state == 2) {
//                        dataArray[ci_act_loc_id] = oTable[i][ci_act_loc_id];
//                    } else {
//                        alert('error on actual location');
//						return false;
//                    }

                    dataArray[ci_remark] = oTable[i][ci_remark];
                    dataArray[ci_inbound_id] = oTable[i][ci_inbound_id];
                    dataArray[ci_price_per_unit] = oTable[i][ci_price_per_unit].replace(/\,/g,'');;
                    dataArray[ci_unit_price] = oTable[i][ci_unit_price];
                    dataArray[ci_all_price] = oTable[i][ci_all_price].replace(/\,/g,'');;
                    dataArray[ci_unit_price_id] = oTable[i][ci_unit_price_id];
                    dataArray[ci_dp_type_pallet] = oTable[i][ci_dp_type_pallet]; //ADD BY POR 2014-02-20 เพิ่มให้ส่ง dispatch type ด้วย

                    dataArray = dataArray.join(separator); // Add By Akkarapol, 22/01/2014, Set dataArray to join with SEPARATOR

//                    if (sub_module != 'rejectAction' && sub_module != 'rejectAndReturnAction') {    // add for reject document : by kik : 20131226
//                        var tmp = dataArray.split(separator);
//                        if ((tmp[ci_sug_loc_id] != tmp[ci_act_loc_id]) && tmp[ci_remark] == "" && present_state == 1)
//                        //if ((dataArray[ci_sug_loc_id] != dataArray[ci_act_loc_id]) && dataArray[ci_remark] == "" && present_state == 1)
//                        {
//                            alert('Please specific reason, because suggest location and actual location miss match.');
//                            return false;
//                        }
//                    }


    			var prodItem = document.createElement("input");
    	                prodItem.setAttribute('type', "hidden");
    	                prodItem.setAttribute('name', "prod_list[]");
    	                prodItem.setAttribute('value', dataArray);
    	                f.appendChild(prodItem);


              }

            //if (confirm("Are you sure to action " + action_value + "?")) {
	                //var data_form = $("#form_receive").serialize();
                        global_data_form = $("#form_receive").serialize();
	                var message = "";

                        /**
                         * not check error for reject and reject return
                         */
                            if (sub_module != 'rejectAction' && sub_module != 'rejectAndReturnAction') {    // add for reject document : by kik : 20131226
                                if (error_location > 0) {
                                        alert('Please check about actual location!');
                                        return false;
                                }
                                if (error_count > 0) {
                                       return false;
                                }

                            }

//                        if(global_sub_module != 'rejectAction' && global_sub_module !='rejectAndReturnAction'){
//                            validation_data();
//                        }else{
                            var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
                            $('#div_for_alert_message').html(mess);
                            $('#div_for_modal_message').modal('show').css({
                                'margin-left': function() {
                                    return ($(window).width() - $(this).width()) / 2;
                                }
                            });
//                        }

//	                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
//	                    switch (data.status) {
//	                        case 'C001':
//	                            message = "Save Re-Location Product Complete";
//	                            break;
//	                        case 'C002':
//	                            message = "Confirm Re-Location Product Complete";
//	                            break;
//	                        case 'C003':
//	                            message = "Approve Re-Location Product Complete";
//	                            break;
//                                case 'C004':        // add by kik : 26-12-2013
//                                    window.onbeforeunload = null;
//                                    message = "Reject Re-Location Product Complete";
//                                    break;
//                                case 'C005':        // add by kik : 26-12-2013
//                                    window.onbeforeunload = null;
//                                    message = "Reject and Return Re-Location Product Complete";
//                                    break;
//	                        case 'E001':
//	                            message = "Save Re-Location Product Incomplete";
//	                            break;
//	                    }
//	                    alert(message);
//	                    url = "<?php echo site_url(); ?>/reLocationProduct";
//	                    redirect(url)
//	                }, "json");
            //}
        }
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }


    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancel?")) {
        	window.onbeforeunload = null;
            url = "<?php echo site_url(); ?>/replenishment/flowReplenishment";
            redirect(url);
        }
    }

    function validateForm() {
        var status = 0;
        var count = 0;
        $("#form_receive").each(function() {
            $(this).validate();
            if ($(this).valid())
            {
				status++;
            }
            count++;
        });
        if (count == status)
        {
			return true;
        } else {
			return false;
        }
    }
    
   function ConfirmGenarate(){
       console.log('in_fn <?php echo $flow_id;?>');
       var flow_send = '<?php echo $flow_id ?>';
        $.ajax({
                 url: "<?php echo site_url(); ?>/replenishment/showAndGenSelectData",
                 dataType: "json",
                 type:'post',
                 data: {
                   flow_id: flow_send
                 },
                 success: function( val, data ) {
                     console.log(val);
                     console.log(data);
                     $('#showProductTable').dataTable().fnGetData; 
                        location.reload();    
//                     if(val != null){
//                        response( $.map( val, function( item ) {
//                         return {
//                           label: item.product_code + ' ' + item.product_name,
//                           value: item.product_code
//                         }
//                       }));
//                     }
                 },
             });
   }

   // Add By Akkarapol, 17/10/2013, เพิ่มฟังก์ชั่นสำหรับการ Print Relocation Job (Location,Product)
   function exportFile(file_type) {
        if (file_type == 'PDF') {
            //ADD BY POR 2014-03-11  เพิ่มให้ส่งค่าไปแบบ realtime
            var backupForm = document.getElementById('form_flow_id').innerHTML;
            var f = document.getElementById('form_flow_id');

            var oTable = $('#showProductTable').dataTable().fnGetData();

            // get actual location by class
            var actual_location = $(".actual_location");

            $("input[name='prod_list[]']").remove(); //ADD BY POR 2014-06-12 แก้ไขให้ clear เก่าก่อนที่จะส่งค่าใหม่ไป

            for (i in oTable) {

                //กรณี confirm
                if($(actual_location[i]).val()!==undefined){
                    // read actual_location by index row
                    oTable[i][ci_act_loc_name] = $(actual_location[i]).val();
                }
                var prod_data = oTable[i].join(separator);
                var prodItem = document.createElement("input");
                prodItem.setAttribute('type', "hidden");
                prodItem.setAttribute('name', "prod_list[]");
                prodItem.setAttribute('value', prod_data);
                f.appendChild(prodItem);

            }

            //Worker Name
            var worker_id = document.createElement("input");
            worker_id.setAttribute('type', "hidden");
            worker_id.setAttribute('name', "worker_id");
            worker_id.setAttribute('value', $('[name="worker_id"]').val());
            f.appendChild(worker_id);

            //Est.Re-Locate date
            var est_relocate_date = document.createElement("input");
            est_relocate_date.setAttribute('type', "hidden");
            est_relocate_date.setAttribute('name', "est_relocate_date");
            est_relocate_date.setAttribute('value', $('#est_relocate_date').val());
            f.appendChild(est_relocate_date);

            //Re-Locate date
            var relocate_date = document.createElement("input");
            relocate_date.setAttribute('type', "hidden");
            relocate_date.setAttribute('name', "relocate_date");
            relocate_date.setAttribute('value', $('#relocate_date').val());
            f.appendChild(relocate_date);

            //Re-Locate No
            var relocation_no = document.createElement("input");
            relocation_no.setAttribute('type', "hidden");
            relocation_no.setAttribute('name', "relocation_no");
            relocation_no.setAttribute('value', $('#relocation_no').val());
            f.appendChild(relocation_no);

            //key
            $.each(ci_list, function(i, obj) {
                var ci_item = document.createElement("input");
                ci_item.setAttribute('type', "hidden");
                ci_item.setAttribute('name', obj.name);
                ci_item.setAttribute('value', obj.value);
                f.appendChild(ci_item);
            });

            $("#form_flow_id").attr('action', "<?php echo site_url(); ?>" + "/report/exportReLocationPDF")
        }

        console.log($("#form_flow_id"));
        $("#form_flow_id").submit();
//        document.getElementById("form_flow_id").innerHTML = backupForm;
    }
   // END Add By Akkarapol, 17/10/2013, เพิ่มฟังก์ชั่นสำหรับการ Print Relocation Job (Location,Product)


   // Add function calculate_qty : by kik : 29-10-2013
    function calculate_qty(){
        var rowData     = $('#showProductTable').dataTable().fnGetData();
        var rowData2    = $('#showProductTable').dataTable();        //add by kik : 20140113
        var num_row = rowData.length;
            var sum_cf_qty = 0;
            var sum_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมดต่อหน่วย
            var sum_all_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมด
            for (i in rowData) {
                var tmp_qty = 0;
                var tmp_price = 0;//ราคาต่อหน่วย
                var all_price = 0; //ราค่าทั้งหมดต่อหนึ่งรายการ

                //+++++ADD BY POR 2013-11-29 แปลงตัวเลขให้อยู่ในรูปแบบคำนวณได้
                var str=rowData[i][ci_reserv_qty];
                rowData[i][ci_reserv_qty]=str.replace(/\,/g,'');

                tmp_qty=parseFloat(rowData[i][ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แก้ไขให้เป็น parseFloat เนื่องจาก qty เปลี่ยนเป็น float

                if(!($.isNumeric( tmp_qty ))){
                    tmp_qty = 0;
                }

                //+++++ADD BY POR 2014-01-09 เพิ่มการคำนวณราคา
                var str2 = rowData[i][ci_price_per_unit]; //ราคาต่อหน่วย
                rowData[i][ci_price_per_unit] = str2.replace(/\,/g, '');
                tmp_price = parseFloat(rowData[i][ci_price_per_unit]);
                if (!($.isNumeric(tmp_price))) {
                    tmp_price = 0;
                }

                //นำ qty มาคูณกับราคาต่อหน่วย เพื่อหาราคาทั้งหมดต่อหนึ่งรายการ
                all_price = tmp_price * tmp_qty;
                sum_price = sum_price + tmp_price; //รวมราคาทุกรายการต่อหน่วย
                sum_all_price = sum_all_price + all_price; //รวมราคาทั้งหมด

                rowData2.fnUpdate(set_number_format(all_price), parseFloat(i), ci_all_price); //update ราคารวมทั้งหมดใน datatable
                //END ADD

                sum_cf_qty = sum_cf_qty + tmp_qty;
            }

        $('#sum_cf_qty').html(set_number_format(sum_cf_qty));
        $('#sum_price_unit').html(set_number_format(sum_price)); //ADD BY POR 2014-01-09 เพิ่มให้แสดงราคารวมทั้งหมดต่อหน่วย
        $('#sum_all_price').html(set_number_format(sum_all_price)); //ADD BY POR 2014-01-09 เพิ่มให้แสดงราคารวมทั้งหมด
    }
    // end function calculate_qty : by kik : 29-10-2013
</script>
<style>
    /*#myModal,*/
/*    #boxDetail{
        width: 1170px;	 SET THE WIDTH OF THE MODAL
        margin: -250px 0 0 -600px;
        z-index:9999
    }*/

    #myModal,
    #boxDetail{
        width: 85%!important;	/* SET THE WIDTH OF THE MODAL */
        top:43%!important;
        margin-left: -42.5%!important;

    }

    /* Start Suggestion List */
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

    #suggestion_div {
        width: 140px;
        height: 170px;
        display: none;
        z-index: 2000;
        position: absolute;
        background-color: #FFF;
        overflow-y:hidden;
    }
    .suggest_location,
    .actual_location {
        width: 120px;
    }
    /* END Suggestion List */
</style>
<style>

    #myModal,
    #boxDetail{
        width: 1170px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -600px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
</style>
<div class="well">
    <!--<form id='form_flow_id' method='post' action="" target="_blank" > </form>-->
    <form id="form_receive" method=post action="" class="" target="_blank">
        <input type="hidden" name="location_list" id="location_list" />
        <fieldset class="well">
            <legend>&nbsp;Order&nbsp;</legend>
            <?php
            $act_re = '';
            if (isset($flow_id)) {
                echo form_hidden('flow_id', $flow_id);
                $act_re = 'class="required" ';
            } else {
                $act_re = ' disabled';
            }

            if (!isset($owner_id)) {
                $owner_id = "";
            }
            if (!isset($renter_id)) {
                $renter_id = "";
            }
            if (!isset($assigned_id)) {
                $assigned_id = "";
            }
            if (!isset($doc_type)) {
                $doc_type = "";
            }
            if (!isset($document_no)) {
                $document_no = "";
            }
            if (!isset($doc_relocate)) {
                $doc_relocate = "";
            }
            if (!isset($est_action_date)) {
                $est_action_date = date("d/m/Y");
            }
            if (!isset($act_action_date)) {
                $act_action_date = "";
            }
            if (!isset($process_type)) {
                $process_type = $data_form['process_type'];
            }

             if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
                $is_urgent = false;
            } else {
                $is_urgent = true;
            }

            //echo ' order id = '.$order_id;
            ?>
            <?php echo form_hidden('process_id', $process_id); ?>
            <?php echo form_hidden('present_state', $present_state); ?>
            <?php echo form_hidden('process_type', $process_type); ?>
            <?php echo form_hidden('user_id', $user_id); ?>
            <?php echo form_hidden('owner_id', $owner_id); ?>
            <?php echo form_hidden('renter_id', $renter_id); ?>

            <table width="98%">
                <tr>
                    <td align="right">Est.Replenishment date</td>
                    <td align="left">
                        <?php echo form_input('est_relocate_date', $est_action_date, 'id="est_relocate_date" placeholder="Est.Re-Locate date" class="required" '); ?>
                    </td>
                    <td align="right">Replenishment date</td>
                    <td align="left">
                        <?php echo form_input('relocate_date', $act_action_date, 'id="relocate_date" placeholder="Action Re-Locate date" ' . $act_re); ?>
                    </td>
                    <td align="right"></td>
                    <td align="left"></td>
                </tr>

                <tr valign='top'>
                    <td align="right">Replenishment No.</td>
                    <td align="left" >
                        <input type="text" placeholder="Auto Generate" id="relocation_no" name="relocation_no" disabled value="<?php echo $doc_relocate; ?>" />
                        <br>
                        <!--//add for ISSUE 3312 : by kik : 20140120-->
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font></td>
                    </td>
                    <td align="right">Worker Name</td>
                    <td align="left"><?php echo form_dropdown('worker_id', $worker_list, $worker_id, 'class="required" '); ?></td>
                    <td align="right"></td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="right"></td>
                    <td align="left"></td>
                    <td align="right"></td>
                    <td align="left"></td>
                    <td align="right"></td>
                    <td align="left"></td>
                </tr>

            </table>
        </fieldset>
        <input type="hidden" name="token" value="<?php echo $token?>" />
    </form>


    <form class="" method="POST" action="" id="form_flow_id" name="form_flow_id" target='_blank'>
        <?php if(@$this->settings['show_column_relocation_job'] and !empty($flow_id)):?>
            <fieldset class="well">
            <legend>Show Column Report</legend>
                <div id='div_relocation_job' style='text-align: center;font-size: 11px;margin-bottom: 5px;'>
                    <?php echo form_checkbox('relocation_job[no]', TRUE, array_key_exists('no', $show_column_relocation_job), 'id="relocation_job_no"'); echo form_label('No.', 'relocation_job_no'); ?>
                    <?php echo form_checkbox('relocation_job[product_code]', TRUE, array_key_exists('product_code', $show_column_relocation_job), 'id="relocation_job_product_code"'); echo form_label('Code', 'relocation_job_product_code'); ?>
                    <?php echo form_checkbox('relocation_job[product_name]', TRUE, array_key_exists('product_name', $show_column_relocation_job), 'id="relocation_job_product_name"'); echo form_label('Name', 'relocation_job_product_name'); ?>
                    <?php echo form_checkbox('relocation_job[product_status]', TRUE, array_key_exists('product_status', $show_column_relocation_job), 'id="relocation_job_product_status"'); echo form_label('Status', 'relocation_job_product_status'); ?>
                    <?php echo form_checkbox('relocation_job[product_sub_status]', TRUE, array_key_exists('product_sub_status', $show_column_relocation_job), 'id="relocation_job_product_sub_status"'); echo form_label('Sub Status', 'relocation_job_product_sub_status'); ?>
                    <?php echo form_checkbox('relocation_job[lot]', TRUE, array_key_exists('lot', $show_column_relocation_job), 'id="relocation_job_lot"'); echo form_label('Lot', 'relocation_job_lot'); ?>
                    <?php echo form_checkbox('relocation_job[serial]', TRUE, array_key_exists('serial', $show_column_relocation_job), 'id="relocation_job_serial"'); echo form_label('Serial', 'relocation_job_serial'); ?>
                    <?php echo form_checkbox('relocation_job[move_qty]', TRUE, array_key_exists('move_qty', $show_column_relocation_job), 'id="relocation_job_move_qty"'); echo form_label('Move QTY', 'relocation_job_move_qty'); ?>
                    <?php echo form_checkbox('relocation_job[confirm_qty]', TRUE, array_key_exists('confirm_qty', $show_column_relocation_job), 'id="relocation_job_confirm_qty"'); echo form_label('Confirm QTY', 'relocation_job_confirm_qty'); ?>

                    <?php
                        if($this->settings['price_per_unit'] == TRUE):
                            echo form_checkbox('relocation_job[price_per_unit]', TRUE, array_key_exists('price_per_unit', $show_column_relocation_job), 'id="relocation_job_price_per_unit"'); echo form_label('Price Per Unit', 'relocation_job_price_per_unit');
                            echo form_checkbox('relocation_job[unit_price]', TRUE, array_key_exists('unit_price', $show_column_relocation_job), 'id="relocation_job_unit_price"'); echo form_label('Unit Price', 'relocation_job_unit_price');
                            echo form_checkbox('relocation_job[all_price]', TRUE, array_key_exists('all_price', $show_column_relocation_job), 'id="relocation_job_all_price"'); echo form_label('All Price', 'relocation_job_all_price');
                        endif;
                    ?>

                    <?php echo form_checkbox('relocation_job[location_from]', TRUE, array_key_exists('location_from', $show_column_relocation_job), 'id="relocation_job_location_from"'); echo form_label('Location From', 'relocation_job_location_from'); ?>
                    <?php echo form_checkbox('relocation_job[suggest_location]', TRUE, array_key_exists('suggest_location', $show_column_relocation_job), 'id="relocation_job_suggest_location"'); echo form_label('Suggest Location', 'relocation_job_suggest_location'); ?>
                    <?php echo form_checkbox('relocation_job[actual_location]', TRUE, array_key_exists('actual_location', $show_column_relocation_job), 'id="relocation_job_actual_location"'); echo form_label('Actual Location', 'relocation_job_actual_location'); ?>

                    <?php
                        if($this->settings['build_pallet'] == TRUE):
                            echo form_checkbox('relocation_job[pallet_code]', TRUE, array_key_exists('pallet_code', $show_column_relocation_job), 'id="relocation_job_pallet_code"'); echo form_label('Pallet Code', 'relocation_job_pallet_code');
                        endif;
                    ?>

                    <?php echo form_checkbox('relocation_job[remark]', TRUE, array_key_exists('remark', $show_column_relocation_job), 'id="relocation_job_remark"'); echo form_label('Remark', 'relocation_job_remark'); ?>

                </div>
            </fieldset>
        <?php endif; ?>
        <?php echo form_hidden('flow_id', @$flow_id); ?>
        <?php echo form_hidden('showfooter', 'show'); ?>
    </form>

    <fieldset class="well">
        <legend>&nbsp;&nbsp;<b>Order Detail</b>&nbsp;&nbsp;</legend>
        <table width="100%" align="center"> <!--Add align and change width by kik : 04-09-2013-->
            <?php
            if ($present_state == 99999) {
                ?>

                <!--            #ISSUE 2190 Re-Location : Search Product by Status & Sub Status
                                #DATE:2013-09-04
                                #BY:KIK
                                #เพิ่มการค้นหาด้วย Status & Sub Status ในส่วนของ get product detail

                                #START New Comment Code #ISSUE 2190-->

        <!--		  <tr>
                                <td align="left">
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                        Product Status :  {productStatus_select}
                                        Product Sub Status :  {productSubStatus_select}
                                        Product Code : <?php echo form_input("product_code", "", "id='product_code' placeholder='Search By "._lang('product_code')."' "); ?>
                                    <a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn">Get Detail</a>
                                </td>
                          </tr>-->

                <!--  #add new code by kik : 14-10-2013 -->
                <tr>
                    <td>
                        <table style="width:100%; margin:0px auto;">
                            <?php $this->load->view('element_filtergetDetail'); ?>
                        </table>
                    </td>
                </tr>


                <!--             #End New Comment Code #ISSUE 2190
                                 #Start Old Comment Code #ISSUE 2190-->

        <!--		  <tr>
              <td align="right">Product Code</td>
              <td align="left"><?php //echo form_input("product_code" ,"" ,"id='product_code' placeholder='Search By Product Code' ");  ?>&nbsp;<a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn">Get Detail</a>
              </td>
              <td align="right"></td>
              <td align="left">&nbsp;</td>
              <td align="right">&nbsp;</td>
              <td align="left">&nbsp;</td>
        </tr>-->
                <!--            #End Old Comment Code #ISSUE 2233-->



                <?php
            }
            ?>
            <tr align="center" >
                <td align="center" id="showDataTable" >
                    <table width ="100%"align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
                        <thead>
                            <?php
                            $str_header = "";

                            foreach ($show_column as $key_column => $column) {
//                                if ($column == "Inbound" ) {
                                    if ($column == "Inbound" || $column == "Price/Unit ID" || $column == "DP_Type_Pallet") {
                                    //echo 'if ';
                                    $str_header .= "<th style=\"display:none;\">" . $column . "</th>";
                                } else if ($column == "Delete") {
                                    if ($present_state == 0) {
                                        $str_header .= "<th>" . $column . "</th>";
                                    }
                                }else if($column == "Est. Balance Qty"){
                                    $str_header .= "<th  style='text-align:center;'>" . $column . "</th>";
                                }else if($column == "Move Qty"){
                                    $str_header .= "<th  style='text-align:center;' class='rlc_{$key_column}'>" . $column . "</th>";
                                }else if($column == "Confirm Qty"){
                                    $str_header .= "<th  style='text-align:center;' class='rlc_{$key_column}'>" . $column . "</th>";
                                } else {

                                    $str_header .= "<th class='rlc_{$key_column}'>" . $column . "</th>";
                                }
                            }
                            ?>
                            <tr><?php echo $str_header; ?></tr>
                        </thead>
                        <tbody>
                        <?php
                        $sum_Receive_Qty = 0;   //add by kik : 29-10-2013
                        $sum_Cf_Qty = 0;        //add by kik : 29-10-2013
                        $sumPriceUnit = 0;      //add by kik : 14-01-2014 ราคารวมทั้งหมดต่อหน่วย
                        $sumPrice = 0;          //add by kik : 14-01-2014 ราคารวมทั้งหมด
                        if (isset($order_detail)) {
//                            p($order_detail);exit();
                            $str_body = "";
                            $i = 1;

                            foreach ($order_detail as $order_column) {
                                $all_price = $order_column['confirm_qty']*$order_column['Price_Per_Unit'];
                                $class_DP_Type_Palet = (!empty($order_column['DP_Type_Pallet']) && $order_column['DP_Type_Pallet']=="FULL")?"readonly":"";
                                if (!empty($order_column['act_location'])) :
//                                    $act_loc = $order_column['act_location'];
                                    $act_loc = "<input type=\"text\" class=\"required actual_location\" placeholder=\"Search here..\" value='".$order_column['act_location']."' data-act_location='{$order_column['act_location']}' />";
                                else :
//                                    if ($present_state == 1) :
                                    	$act_loc = "<input type=\"text\" class=\"required actual_location\" placeholder=\"Search here..\" value=\"\" data-act_location='' />";
//                                    else :
//                                    	$act_loc = "";
//                                    endif;
                                endif;

                                if (!empty($order_column['to_location'])) :
                                	$suggest_loc = $order_column['to_location'] . "<a href=\"javascript:;\" ONCLICK=\"showProduct('','" . $order_column['to_location'] . "')\">" . img("css/images/icons/view.png") . "</a>";
                                else :
                                	$suggest_loc = "";
                                endif;

                                //$confirm_qty = ($order_column['confirm_qty'] == "0") ? '' : $order_column['confirm_qty']; //COMMENT BY POR 2013-12-03 กรณีที่ confirm_qty=0 จะกำหนดค่าให้เป็น 0 ปกติ แทนการกำหนดให้เป็น ''
                                if($order_column['DP_Type_Pallet']=='FULL'):
                                    $confirm_qty = $order_column['reserv_qty']; //ADD BY POR 2014-02-20 ตรวจสอบว่าถ้าเป็น FULL ให้ qty เท่ากับค่าครั้งแรกที่ add มา
                                else:
                                    if($fast_set_confirm_relocation):
                                        $confirm_qty = $order_column['reserv_qty'];

                                        //ADD BY POR 2014-06-16 กรณีที่ confirm ไม่ตรงกับ reserv_qty ให้เอา confirm มาแสดง
                                        if(!empty($order_column['confirm_qty'])):
                                            $confirm_qty = $order_column['confirm_qty'];
                                        endif;
                                        //END ADD
                                    else:
                                        $confirm_qty = $order_column['confirm_qty']; //ADD NEW BY POR 2013-12-03 กำหนดให้ค่าเท่ากับที่ส่งมาเลย
                                    endif;

                                endif;


                                $str_body .= "<tr>";
                                $str_body .= "<td class='rlc_no'>" . $i . "</td>";
                                //add class td_click and ONCLICK for show Product Est. balance Detail modal : by kik : 06-11-2013
                                $str_body .= "<td class='td_click rlc_product_code' align='center' title='Click to display the running process is not finished.'  ONCLICK='showProductEstInbound(\"{$order_column['product_code']}\",{$order_column['inbound_id']})'>" . $order_column['product_code'] . "</td>";
                                $str_body .= "<td class='rlc_product_name'>" . $order_column['product_name'] . "</td>";
                                $str_body .= "<td class='rlc_product_status'>" . $order_column['product_status'] . "</td>";
                                $str_body .= "<td class='rlc_product_sub_status'>" . $order_column['Product_Sub_Status'] . "</td>"; //add by kik : 04-098-2013
                                $str_body .= "<td class='rlc_lot'>" . $order_column['product_lot'] . "</td>";
                                $str_body .= "<td class='rlc_serial'>" . $order_column['product_serial'] . "</td>";
                                $str_body .= "<td style='text-align: right;' class='rlc_move_qty'>" . set_number_format($order_column['reserv_qty']) . "</td>";

                                $str_body .= "<td style='text-align: right;' class='cell_move_qty set_number_format ".$class_DP_Type_Palet." rlc_confirm_qty'>" . set_number_format($confirm_qty) . "</td>";
                                $str_body .= "<td class='rlc_price_per_unit'>" . set_number_format($order_column['Price_Per_Unit']) . "</td>";
                                $str_body .= "<td class='rlc_unit_price'>" . $order_column['Unit_Price_value'] . "</td>";
                                $str_body .= "<td class='rlc_all_price'>" . set_number_format($all_price) . "</td>";
                                $str_body .= "<td class='rlc_location_from' data-from_location=$order_column[from_location]>" . $order_column['from_location'] . " <a href=\"javascript:;\" ONCLICK=\"showProduct('','" . $order_column['from_location'] . "')\">" . img("css/images/icons/view.png") . "</a></td>";
                                $str_body .= "<td class='rlc_suggest_location'>" . $order_column['to_location'] . " </td>";
                                $str_body .= "<td class='rlc_actual_location row_selected'>" . $act_loc . "</td>";
                                $str_body .= "<td class='rlc_remark'>" . $order_column['remark'] . "</td>";
                                $str_body .= "<td style=\"display:none;\">" . $order_column['inbound_id'] . "</td>";
                                $str_body .= "<td style=\"display:none;\">" . $order_column['Unit_Price_Id'] . "</td>"; // edit by kik (07-10-2013)
                                $str_body .= "<td style='text-align: center;' class='rlc_pallet_code'>" . @$order_column['Pallet_Code'] . "</td>";
                                $str_body .= "<td style=\"display:none;\">" . $order_column['DP_Type_Pallet'] . "</td>";    // ADD BY POR 2014-02-19
//                                $str_body .= "<td  style=\"display:none;\">" . $i . "</td>";    // ADD BY POR 2014-02-19 //comment by kik : 20140326 เพราะว่า parameter form ตัวนี้จะต้องเป็นค่า item_id
                                $str_body .= "<td style=\"display:none;\">" . $order_column['item_id'] . "</td>";    // add by kik : 20140326 ส่งค่า item_id เป็น parameter ไปยัง controller เพื่อ save ค่าได้ถูก item id

                                //ADD BY POR 2014-03-12 ต้องเก็บ Id ของ location เพิ่ม เนื่องจากต้องนำไปหาชื่อ location มาแสดงตอนแสดง pdf realtime
                                $str_body .= "<td style=\"display:none;\">" . $order_column['from_location'] . "</td>";
                                $str_body .= "<td style=\"display:none;\">" . $order_column['to_location'] . "</td>";
                                $str_body .= "<td style=\"display:none;\">" . $order_column['act_location'] . "</td>";

                                if ($present_state == 0) {
                                    $str_body .= "<td><a ONCLICK=\"deleteItem(this)\" >" . img("css/images/icons/del.png") . "</a></td>";
                                }
                                $str_body .= "</tr>";
                                $i++;
                                $sum_Receive_Qty+=$order_column['reserv_qty'];        //add by kik    :   29-10-2013
                                $sum_Cf_Qty+=$order_column['confirm_qty'];            //add by kik    :   29-10-2013
                                $sumPriceUnit+=$order_column['Price_Per_Unit'];       //add by kik    :   20140114
                                $sumPrice+=$all_price;                              //add by kik    :   20140114
                            }
                            echo $str_body;
                        }
                        ?>
                       </tbody>
                         <!-- show total qty : by kik : 29-10-2013-->
                        <tfoot>
                           <tr>

                               <? if (@$present_state == 0) { ?>

                                    <th colspan='8' id="colspan_before_move_qty" class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                    <th  class ='ui-state-default indent rlc_move_qty' style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Receive_Qty);?></span></th>
                                    <?php if($price_per_unit):?>
                                        <th class ='ui-state-default indent rlc_price_per_unit' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                        <th class="rlc_unit_price"></th>
                                        <th class ='ui-state-default indent rlc_all_price' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                     <?php endif;?>
                                    <th class ='ui-state-default indent' colspan="16" id="colspan_before_location_form"></th>
                               <? }else{ ?>

                                    <th colspan='7' id="colspan_before_move_qty" class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                    <th class ='ui-state-default indent rlc_move_qty'   style='text-align: right;'><span ><?php echo set_number_format($sum_Receive_Qty);?></span></th>
                                    <th class ='ui-state-default indent rlc_confirm_qty'   style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Cf_Qty);?></span></th>
                                    <?php if($price_per_unit):?>
                                        <th class ='ui-state-default indent rlc_price_per_unit' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                        <th class="rlc_unit_price"></th>
                                        <th class ='ui-state-default indent rlc_all_price' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                     <?php endif;?>
                                    <th class ='ui-state-default indent' colspan='11' id="colspan_before_location_from"></th>

                               <? }?>

                            </tr>
                        </tfoot>
                        <!-- end show total qty : by kik : 29-10-2013-->
                    </table>
                </td>
            </tr>
        </table>

    </fieldset>
</div>
<!-- Modal -->
<!--call element element show product get Detail modal : add by kik : 16-12-2013-->
<?php $this->load->view('element_showgetDetail'); ?>

<div style="min-height:500px;padding:5px 10px;" id="boxDetail" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">Product Detail</h3>

    </div>
    <div class="modal-body"> </div>
    <div class="modal-footer">
        <div style="float:left;">
        </div>
        <div style="float:right;">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
    </div>
</div>
<div id="suggestion_div">
    <div class="suggestionList" id="suggestionLocationList"></div>
</div>
<!--  <input type="text" id="datepicker_test"> -->

<!--call element Product Est. balance Detail modal : add by kik : 06-11-2013-->
<?php $this->load->view('element_showEstBalance'); ?>
<?php $this->load->view('element_modal_message_alert'); ?>
