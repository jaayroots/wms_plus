<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>

    var master_product_status = {};
    var master_product_sub_status = "";
    var master_product_unit = "";
    var master_container_size = "";
    var allVals = new Array();
    var dispatch_type; //add for ISSUE#3334 : by kik : 20140220
    var config_pallet = "<?php echo ($config_pallet == 1 ? true : false)?>"; // add by kik for ISSUE 3333 : 20140220
    var oTable = null;
    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
    var allVals = new Array();
    var form_name = "form_ch_status";
    var separator = "<?php echo SEPARATOR; ?>";

    var set_suggest = '<?php echo $set_suggest; ?>'; //ADD BY POR  2014-06-10 รับค่า setting suggest_locate

    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = ' Change Product Status'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = '#showProductTable'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/flow/flowChangeStatusList"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';

    var ci_prod_code = 1;
    var ci_prod_status_val = 3;
    var ci_prod_sub_status_val = 4;
    var ci_lot = 5;
    var ci_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
     var ci_actual_name = 9;
    var ci_show_actual_location = 9;
    var ci_reserv_qty = 10;
    var ci_confirm_qty = 11;

    //Add By por 2014-01-09 เพิ่มตัวแปร ราคาต่อหน่วย, หน่วย, ราคารวม
    var ci_price_per_unit = 13;
    var ci_unit_price = 14;
    var ci_all_price = 15;
    var statusprice = '<?php echo $statusprice;?>';
    //END Add

    var ci_pallet_code = 16;        //add by kik for ISSUE 3334 : 2014-02-20
    var ci_remark = 17;

    //Define Hidden Field Datatable
    var ci_prod_id = 19;
    var ci_prod_status = 20;
    var ci_prod_sub_status = 21;
    var ci_unit_id = 22;
    var ci_item_id = 23;
    var ci_suggest_loc = 24;
    var ci_actual_loc = 25;
    var ci_old_loc = 26;
    var ci_inbound_id = 27;
    var ci_unit_price_id = 28;
    var ci_dp_type_pallet = 29;     //add by kik for ISSUE 3334 : 2014-02-17
    
//    var ci_actual_name = 29;
//    var ci_show_actual_location = 29;
    
    var ci_list = [
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_lot', value: ci_lot},
        {name: 'ci_serial', value: ci_serial},
        {name: 'ci_mfd', value: ci_mfd},
        {name: 'ci_exp', value: ci_exp},
        {name: 'ci_reserv_qty', value: ci_reserv_qty},
        {name: 'ci_confirm_qty', value: ci_confirm_qty},
        {name: 'ci_remark', value: ci_remark},
        {name: 'ci_prod_id', value: ci_prod_id},
        {name: 'ci_prod_status', value: ci_prod_status},
        {name: 'ci_unit_id', value: ci_unit_id},
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_suggest_loc', value: ci_suggest_loc},
        {name: 'ci_actual_loc', value: ci_actual_loc},
        {name: 'ci_actual_name', value: ci_actual_name},
        {name: 'ci_old_loc', value: ci_old_loc},
        {name: 'ci_prod_sub_status', value: ci_prod_sub_status},
        {name: 'ci_inbound_id', value: ci_inbound_id},
        {name: 'ci_prod_status_val', value: ci_prod_status_val},
        {name: 'ci_prod_sub_status_val', value: ci_prod_sub_status_val},


        //Add By por 2014-01-15 เพิ่มตัวแปร ราคาต่อหน่วย, หน่วย, ราคารวม
        {name: 'ci_price_per_unit', value: ci_price_per_unit},
        {name: 'ci_unit_price', value: ci_unit_price},
        {name: 'ci_all_price', value: ci_all_price},
        {name: 'ci_unit_price_id', value: ci_unit_price_id},
        //END Add

        {name: 'ci_pallet_code', value: ci_pallet_code},    //add by kik for ISSUE 3334 : 2014-02-20
        {name: 'ci_dp_type_pallet', value: ci_dp_type_pallet}    //add by kik for ISSUE 3334 : 2014-02-17
    ]

    $(document).ready(function() {
       

        $.post('<?php echo site_url() . "/pre_receive/getProductStatus"; ?>', {"is_pending" : "Y"}, function(data) {
            $.extend(master_product_status, JSON.parse(data));
        });

        $.post('<?php echo site_url() . "/pre_receive/getProductStatus"; ?>', function(data) {
            $.extend(master_product_status, JSON.parse(data));
        });

        $.post('<?php echo site_url() . "/pre_receive/getSubStatus"; ?>', function(data) {
            master_product_sub_status = data;
        });

        $.post('<?php echo site_url() . "/pre_receive/getProductUnit"; ?>', function(data) {
            master_product_unit = data;
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

        // Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        // Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="assigned_id"]').change(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        // add by kik (08-10-2013)
        // start script filter getDetail
        // script use togeter element_filtergetDetail
        $('#getBtn').click(function() {
            if ($('#productCode').val() == "") {
                alert("Please fill <?php echo _lang('product_code') ?>");
                $('#productCode').focus();
                return false;
            } else {
                showModalTable();

                // Edit By Akkarapol, 21/01/2014,set dataTable bind search when enter key
                $('#modal_data_table_filter label input')
                .unbind('keypress keyup')
                .bind('keypress keyup', function(e){
                    if (e.keyCode == 13){
                      oTable.fnFilter($(this).val());
                    }
                });
                // Edit By Akkarapol, 21/01/2014,set dataTable bind search when enter key

            }
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
        // end script filter getDetail



        //$("#est_relocate_date").datepicker();

        $("#est_action_date").datepicker({
            //autoclose: true,
            //startDate: '0d',
            //format: "dd/mm/yyyy",
            onRender: function(date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }
        }).keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
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


         /////////////////-add 
        /////////////////-add 
        function bindDataTD() {
            $('.td_actual_location').click(function() {
       

//                var input = $('<input>').attr('class', 'actual_location').appendTo($(this));
                var input = $('<input>').attr('width', '100').appendTo($(this));
//                $('.actual_location').css('width','30px');
            });
        }

// Suggestion
        suggestLocation();
        suggesLocationClick();
// Add By Akkarapol, 08/10/2013, เพิ่ม script เมื่อตอน click ที่ list ของ location จาก div ที่ได้เตรียมไว้ ว่าให้นำค่าเข้าไปที่ td และอัพเดทเข้าที่ dataTable ด้วย พร้อมทั้ง hide div ตัวนั้นและซ่อน input ออกไปพร้อมกับเซ็ท label ของ td ให้เป็น location Code
        function suggesLocationClick(selector, parent_selector){
            $('.actual_location_id').on('click', function(e)
            {
//                console.log($(this).data('id'));
                flag = false;
                var elm = $(selector);
                $(selector).attr('value', $(this).text()).removeClass('required');
                // START Create Temp [draft version]
//				var input = '<input class="'+elm.attr('class')+'" placeholder="'+elm.attr('placeholder')+'" value="'+$(this).text()+'" data-status="'+elm.data('status')+'" data-sub_status="'+elm.data('sub_status')+'" data-category="'+elm.data('category')+'">';
                var input = $(this).text();
                var oTable = $('#showProductTable').dataTable(); // get datatables
                var indexOfRow = oTable.fnGetPosition($(parent_selector).closest('tr').get(0)); // find rows index
                oTable.fnUpdate(input, indexOfRow, ci_show_actual_location); // update new data to datatables
                oTable.fnUpdate($(this).data('id'), indexOfRow, ci_actual_loc); // update new data to datatables
                suggestLocation(); // bind event again
                // END
                setTimeout(function() {
                    $('#suggestion_div').hide();
                }, 200);
            });
        }
        // END Add By Akkarapol, 08/10/2013, เพิ่ม script เมื่อตอน click ที่ list ของ location จาก div ที่ได้เตรียมไว้ ว่าให้นำค่าเข้าไปที่ td และอัพเดทเข้าที่ dataTable ด้วย พร้อมทั้ง hide div ตัวนั้นและซ่อน input ออกไปพร้อมกับเซ็ท label ของ td ให้เป็น location Code

        // Add By Akkarapol, 08/10/2013, เพิ่ม script เมื่อ keyup หรือ focus ที่ textbox ให้ทำการ get ค่าที่เซ็ทไว้ใน td ตัวแม่ มาเข้า ajax เพื่อคืนค่า location ที่ถูกต้อง พร้อมทั้งนำค่า return นั้นมา append list ของ location เพื่อให้เป็น autocomplete ต่อไป
        
        // END Add By Akkarapol, 08/10/2013, เพิ่ม script เมื่อ keyup หรือ focus ที่ textbox ให้ทำการ get ค่าที่เซ็ทไว้ใน td ตัวแม่ มาเข้า ajax เพื่อคืนค่า location ที่ถูกต้อง พร้อมทั้งนำค่า return นั้นมา append list ของ location เพื่อให้เป็น autocomplete ต่อไป
// END

        // Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        // Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="assigned_id"]').change(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        

//
//        });
        call_from_add();
        
        
        /////////////////-add 
        /////////////////-add 
        
        setTimeout(function(){
            initProductTable();
        }, 1000);


        $.validator.addMethod("document", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9._-]+$/i.test(value);
        }, "Document Format is invalid.");

//        $("#" + form_name + " :input").not("[name=showProductTable_length],[name=remark],[name=assigned_id]").attr("disabled", true);


//Define button Search onClick
        $('#search_submit').click(function() {
            //Add code for fix defect : 219 :  add by kik : 2013-12-16
            if (allVals.length == 0) {
                alert("No Product was selected ! Please select a product or click cancle button to exit.");
                return false;
            }
            //end Add code for fix defect : 219 :  add by kik : 2013-12-16

            var dataSet = {
                post_val: allVals,
                dp_type_pallet_val : dispatch_type  // add for ISSUE 3334 : by kik : 20140220
            }
            $.post('<?php echo site_url() . "/product_status/showProduct" ?>', dataSet, function(data) {
                var remark = "";
                var qty = "";

                var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";

                $.each(data.product, function(i, item) {

                    //add for ISSUE3334 :  by kik : 20140217
                    if(item.DP_Type_Pallet == "FULL"){
                        qty = item.Est_Balance_Qty;
//                        del = "DEL";
                        del = "<a ONCLICK=\"removePalletItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>"; // add by kik : 20140219
                    }
                    //end add by kik : 20140212

                    $('#showProductTable').dataTable().fnAddData([
                            recordsTotal
                                , item.Product_Code
                                , item.Product_NameEN
                                , item.Prod_Status_Value
                                , item.Prod_Sub_Status_Value
                                , item.Product_Lot
                                , item.Product_Serial
                                , item.Product_Mfd
                                , item.Product_Exp
//                                , set_number_format(item.Est_Balance_Qty)       //add by kik : 11-10-2013
                                , item.Location_Code
                                // ,""
                                , item.Est_Balance_Qty
//                                                        ,item.Current_Balance_Qty //comment by kik : 11-10-2013
                                , qty
                                , item.Unit_Value
                                //ADD BY POR 2014-01-16 เพิ่มให้แสดงราคาต่อหน่วย หน่วยของราคา และจำนวนเงินรวม
                                , item.Price_Per_Unit
                                , item.unit_price
                                , item.All_Price
                                //END ADD
                                , item.Pallet_Code //add by kik : 20140220
                                , ""
                                , del
                                , item.Product_Id
                                , item.Prod_Status
                                , item.Prod_Sub_Status
                                , item.Unit_Id
                                , "new"

                                // Comment By Akkarapol, 29/10/2013, คอมเม้นต์ทิ้ง เพราะว่า ของเก่าเนี่ย มันใส่ค่ากันไว้ผิด ไม่ตรงกับ Index ที่เซ็ตกันไว้ มันเลยทำให้ค่า Actual Location ที่ต้องเอาไปใช้เป็น Old Location Id ในขั้นตอนของการ openAction แล้วทำการบันทึกค่า Old Location มันผิด
//                                , ""
//                                , ""
//                                , item.Suggest_Location_Id
                                // END Comment By Akkarapol, 29/10/2013, คอมเม้นต์ทิ้ง เพราะว่า ของเก่าเนี่ย มันใส่ค่ากันไว้ผิด ไม่ตรงกับ Index ที่เซ็ตกันไว้ มันเลยทำให้ค่า Actual Location ที่ต้องเอาไปใช้เป็น Old Location Id ในขั้นตอนของการ openAction แล้วทำการบันทึกค่า Old Location มันผิด


                                // Add By Akkarapol, 29/10/2013, เพิ่มข้อมูลของ Suggest Location, Actual Location, Old Location ให้มันถูกต้อง ไม่งั้นค่าที่เอาไปใช้ต่อไปมันก็ผิดอีก ถ้ายังไม่ใส่ค่าไว้มันก็ผิดอยู่วันยันค่ำ
                                , item.Suggest_Location_Id
                                , item.Actual_Location_Id
                                , item.Old_Location_Id
                                // END Add By Akkarapol, 29/10/2013, เพิ่มข้อมูลของ Suggest Location, Actual Location, Old Location ให้มันถูกต้อง ไม่งั้นค่าที่เอาไปใช้ต่อไปมันก็ผิดอีก ถ้ายังไม่ใส่ค่าไว้มันก็ผิดอยู่วันยันค่ำ

                                , item.Inbound_Id
                                , item.Unit_Price_Id //ADd BY POR 2014-01-16 เพิ่มให้ส่ง unit price id ไปด้วย
                                , item.DP_Type_Pallet       //add by kik : 20140212

                    ]
                            );
                    // add by kik : 2013-11-13
                    var new_td_item = $('td:eq(1)', $('#showProductTable tr:last'));
                    new_td_item.addClass("td_click");
                    new_td_item.attr('onclick', 'showProductEstInbound(' + '"' + item.Product_Code + '"' + ',' + item.Inbound_Id + ')');
                    // new_td_item.attr('style', 'color: green;');
                    // end add by kik
                    
                     var new_td_item = $('td:eq(9)', $('#showProductTable tr:last'));
                    new_td_item.addClass("td_actual_location");
//                    new_td_item.attr('onclick', 'call_from_add(this)');

                    
                    // add for ISSUE 3334 : by kik : 2013-11-17
                    if(item.DP_Type_Pallet == "FULL"){
                        var dp_type_full = $('td:eq('+ci_confirm_qty+')', $('#showProductTable tr:last'));
                        dp_type_full.addClass("readonly");
                    }
                    // end add for ISSUE 3334 : by kik : 2013-11-17

                    recordsTotal++;     //add for edit No. to running number : by kik : 20140226
                });
//                $('#showProductTable').dataTable().fnDraw();
//                calculate_qty();
//                suggestLocation();
//                 setTimeout(function(){
                    suggestLocation();
                    initProductTable();
                    call_from_add();
//                }, 1000);
//                initProductTable();
//                 $('#showProductTable').dataTable().fnDraw();
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
    });

    //Define Dialog Model
    $('#myModal').modal('toggle').css({
        'width': function() {
            return ($(document).width() * .9) + 'px';   // make width 90% of screen
        },
        'margin-left': function() {
            return -($(this).width() / 2);    // center model
        }
    });

    function initProductTable() {
        var oTable = $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "aoColumnDefs": [
                {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
                {"sWidth": "20%", "sClass": "left_text", "aTargets": [2]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [3]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [4]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [5]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [6]},
                {"sWidth": "5%", "sClass": "indent obj_mfg", "aTargets": [7]},
                {"sWidth": "5%", "sClass": "center obj_exp", "aTargets": [8]},
                  {"sWidth": "7%", "sClass": "td_actual_location", "aTargets": [9]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [10]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [11]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [12]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [13]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [14]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [15]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [16]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [17]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [18]},
              
//                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [9]},
//                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [10]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [11]},
//                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [12]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [13]},
//                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [14]},
//                {"sWidth": "5%", "sClass": "left_text", "aTargets": [15]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [16]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [17]},
//                {"sWidth": "7%", "sClass": "td_actual_location center", "aTargets": [29]},
            ]
        }).makeEditable({
            sUpdateURL: function(value, settings) {
                return value;
            }
            , "aoColumns": [
                null
                        , null
                        , null
                        , {

                    data: JSON.stringify(master_product_status),
                    event: 'click',
                    type: 'select',
                    onblur: 'submit',
                    select_filter_active : true,
                    select_filter_by : "PENDING",
                    sUpdateURL: function(value, settings) {
                        var oTable = $('#showProductTable').dataTable();
                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                        oTable.fnUpdate(value, rowIndex, ci_prod_status);

                        //start add for ISSUE 3334 : by kik : 20140220
                        var datas = oTable.fnGetData(rowIndex);
                        if(datas[ci_dp_type_pallet] == "FULL"){
                            update_pallet_status(rowIndex,value,datas[ci_pallet_code]);
                        }

                    return value;
                    },
                }
                , {
                    data : master_product_sub_status,
                    event: 'click',
                    type: 'select',
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {
                    var oTable = $('#showProductTable').dataTable();
                         var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                         oTable.fnUpdate(value, rowIndex, ci_prod_sub_status);
                         return value;
                    }
                }
				, {
					onblur: 'submit',
                    event: 'click'
				}
				, {
					onblur: 'submit',
                    event: 'click'
				}
                , {
                    onblur: 'submit',
                    type: 'datepicker',
                    cssclass: 'date',
                    event: 'click focusin',
                    is_required: false,
                    loadfirst: true,
                }
                , {
                    onblur: 'submit',
                    type: 'datepicker',
                    cssclass: 'date',
                    event: 'click focusin',
                    loadfirst: true,
                }
                        , null
                        , null
                        , {
                    sSortDataType: "dom-text",
                    sType: "numeric",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    cssclass: "required number",
                    loadfirst: true, // Add By Akkarapol, 15/10/2013, เพิ่ม loadfirst เพื่อให้ช่อง Change Qty นี้มี textbox ขึ้นมาตั้งแต่โหลด Product
                    "event" : 'click', // Add By Akkarapol, 15/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Change Qty นี้ โดยการ คลิกเพียง คลิกเดียว
                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 04-11-2013
                        calculate_qty();
                    }
                }
                , null
                 , {
                    onblur: 'submit',
                    "event": 'click', // Add By Akkarapol, 15/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว
                }
                , null
                , null
                , null
                , {
                    onblur: 'submit',
                    "event": 'click', // Add By Akkarapol, 15/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว
                }
                , null
//                , null
            ]
        });

        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_suggest_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_actual_loc, false);
        //$('#showProductTable').dataTable().fnSetColumnVis(ci_confirm_qty,false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_old_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_inbound_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_dp_type_pallet, false); // add for ISSUE3334 : by kik : 20140217

        if(statusprice!=true){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
        }

        //add by kik : 20140113
        if(config_pallet!=true){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_pallet_code, false);
        }
        //end add by kik : 20140113

        $('#showProductTable tbody tr td[title]').hover(function() {

            // Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา
            var chk_title = $(this).attr('title');
            var chk_innerHTML = this.innerHTML;
            if(chk_title != chk_innerHTML){
                $(this).show_tooltip();
            }
            // END Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา

        }, function() {
            $(this).hide_tooltip();
        });
        new FixedColumns(oTable, {
            "iLeftColumns": 0,
            "sLeftWidth": 'relative',
            "iLeftWidth": 0
        });
    }
    
            function suggestLocation(){
            $('.actual_location').on('keyup focus', function(e)
            {
            //    console.log();
                var _this = $(this);
                var _parent = $(this).parent('td');
                $('#suggestion_div').css({top: ($(this).offset().top + 30), left: $(this).offset().left}).show();
   
                var data = $(this).parent('td').data();
                $.post("<?php echo site_url('/reLocation/getLocationAll'); ?>", {criteria: $(this).val()}, function(response)
                {
 
                    $('#suggestionLocationList').html(''); // clear old list
                    $.each(response, function(index, value)
                    {
                        var lists = $('<li>').attr('class', 'actual_location_id').attr('data-id', value.Location_Code).html(value.Location_Code);
                        $('#suggestionLocationList').append(lists);
                    });
                    suggesLocationClick(_this, _parent);
                });
//                $('.actual_location').focus();
            }).blur(function() {
                var _this = this;
                var _parent = $(this).parent('td');

                setTimeout(function() {
                    //ADD BY POR 2013-12-16 เพิ่มให้ตรวจสอบ location ว่ามีจริงหรือไม่ (ล้อมาจากเมนู Relocation(locoation))
                    $.get("<?php echo site_url(); ?>" + "/location/check_exist_location", {location_code: $(_this).val()}, function(data)
                    {
                        if (data == "null")
                        {
                               alert('Sorry!!, This location not found, Please select another location.');

                            flag = false;
                            $(_this).attr('value', $(_this).val()).removeClass('required');
                            var input = $(_this).val();
                            var oTable = $('#showProductTable').dataTable(); // get datatables
                            var indexOfRow = oTable.fnGetPosition($(_parent).closest('tr').get(0)); // find rows index
                            oTable.fnUpdate('', indexOfRow, ci_show_actual_location); // update new data to datatables
                            oTable.fnUpdate('', indexOfRow, ci_actual_loc); // update new data to datatables

                            var datas = $('#showProductTable').dataTable().fnGetData(indexOfRow);
                            if(datas[ci_dp_type_pallet] == "FULL"){
                                update_pallet_actualLoc(indexOfRow,input,data,datas[ci_pallet_code]);
                            }

                            $(_this).val('');
                        }else{
                            //ADD BY POR 2013-12-16 เพิ่มให้ update ค่าใน datatable ด้วยกรณีมีการคีย์ข้อมูล
                            flag = false;
                            $(_this).attr('value', $(_this).val()).removeClass('required');
                            var input = $(_this).val();
                            var oTable = $('#showProductTable').dataTable(); // get datatables
                            var indexOfRow = oTable.fnGetPosition($(_parent).closest('tr').get(0)); // find rows index
                            oTable.fnUpdate(input, indexOfRow, ci_show_actual_location); // update new data to datatables
                            oTable.fnUpdate(data, indexOfRow, ci_actual_loc); // update new data to datatables
                            //END ADD

                            //start add for ISSUE 3334 : by kik : 20140221
                            var datas = $('#showProductTable').dataTable().fnGetData(indexOfRow);
                            if(datas[ci_dp_type_pallet] == "FULL"){
                                update_pallet_actualLoc(indexOfRow,input,data,datas[ci_pallet_code]);
                            }
                            //end add for ISSUE 3334 : by kik : 20140221
                        }
                    });
                    if ($(_this).val() != "") {
                        
                        $.get("<?php echo site_url(); ?>" + "/product_status/get_location", {location_code: $(_this).val()}, function(result)
                        {
                            console.log(result);
                            if (result != "null")  
                            {
    // console.log(result);
                            // console.log($(_this).val());
                            // return
                            alert('Sorry!!, Can not choose location : ' + $(_this).val() + ', Please select another location.'); 
                            // alert('Sorry!!, Can not choose this location, Please select another location.'); 
                    
                            $(_this).attr('value', $(_this).val()).removeClass('required');
                            var input ="";
                            var oTable = $('#showProductTable').dataTable();
                            var indexOfRow = oTable.fnGetPosition($(_parent).closest('tr').get(0)); 
                            oTable.fnUpdate(input, indexOfRow, ci_show_actual_location); 
                            oTable.fnUpdate('', indexOfRow, ci_actual_loc); 
                            } 
                            else {
                                $(_this).data('', $(_this).val());
                            }
                          });
                    }
                    //END ADD
                    $('#suggestion_div').hide();
                }, 200);
            });
    }
    
     function suggesLocationClick(selector, parent_selector){
            $('.actual_location_id').on('click', function(e)
            {
//                console.log($(this).data('id'));
                flag = false;
                var elm = $(selector);
                $(selector).attr('value', $(this).text()).removeClass('required');
                // START Create Temp [draft version]
//				var input = '<input class="'+elm.attr('class')+'" placeholder="'+elm.attr('placeholder')+'" value="'+$(this).text()+'" data-status="'+elm.data('status')+'" data-sub_status="'+elm.data('sub_status')+'" data-category="'+elm.data('category')+'">';
                var input = $(this).text();
                var oTable = $('#showProductTable').dataTable(); // get datatables
                var indexOfRow = oTable.fnGetPosition($(parent_selector).closest('tr').get(0)); // find rows index
                oTable.fnUpdate(input, indexOfRow, ci_show_actual_location); // update new data to datatables
                oTable.fnUpdate($(this).data('id'), indexOfRow, ci_actual_loc); // update new data to datatables
                suggestLocation(); // bind event again
                // END
                setTimeout(function() {
                    $('#suggestion_div').hide();
                }, 200);
            });
        }
    
    function call_from_add() {
//                console.log(this_n);
          $('td.td_actual_location').click(function() {
//            console.log($(this));
//            console.log('s');
//            return;
                if (!flag)  {
                if ($(this).text().length == 0)
                {
                    var input = $('<input>').attr('class', 'actual_location').appendTo($(this));
                    //$(this).html(input);
                    suggestLocation();
                    input.focus();
                }
                else
                {
                    var temp = $(this).text();
                    $(this).html('');
                    var input = $('<input>').attr({'class': 'actual_location', 'data-val': temp}).prop('value', temp).appendTo($(this));
                    suggestLocation();
//                    input.attr('data-val',temp);
                    input.focus();
                }
                flag = true;

            }
            });
            


    }
        

    function update_pallet_status(row_index,value,pallet_code){

        var oTable = $('#showProductTable').dataTable();
        var rowData = $('#showProductTable').dataTable().fnGetData();
        var txt_value = $("#productStatus_select option[value='"+value+"']").text();

        for (var i=0; i < rowData.length ; i++){

            if(pallet_code == rowData[i][ci_pallet_code] && row_index!=i){

                oTable.fnUpdate(value, i, ci_prod_status);
                oTable.fnUpdate(txt_value, i, ci_prod_status_val);
            }
        }

    }

    function postRequestAction(module, sub_module, action_value, next_state, elm) {
        global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');

        var statusisValidateForm = validateForm();
        if (statusisValidateForm === true) {

   //================================= Check Validation Form ==================================
            //in case select product status = 'BORROW' must input Document Reference : ADD BY POR 2014-12-09
            if($('#showProductTable').val()==''){
                if($('#document_ref').val()==""){
                    alert('Please Input Document Reference');
                    return false;
                }
            }
            var rowData = $('#showProductTable').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Order Detail");
                return false;
            }

            for (i in rowData) {

                reserv_qty = rowData[i][ci_reserv_qty].replace(/\,/g,''); //Edit by POR 2013-12-02 เพิ่ม replace(/\,/g,'') เพื่อให้ตัด comma ออก
                confirm_qty = rowData[i][ci_confirm_qty].replace(/\,/g,''); //Edit by POR 2013-12-02 เพิ่ม replace(/\,/g,'') เพื่อให้ตัด comma ออก
                if ((confirm_qty == "") || (confirm_qty <= 0)) {
                    alert('Please fill all Receive Qty and Negative Value is not allow');
                    return false;
                }
                
                //##Begin in case select product status = 'BORROW' must input Document Reference : ADD BY POR 2014-12-09
                status_borrow = rowData[i][ci_prod_status_val]; //status select
                if(status_borrow=='Borrow'){
                    if($('input[name="document_ref"]').val()==""){
                        alert('Please Input Document Reference');
                        return false;
                    }
                }
                //##End 
                

            /**=================================================================
             * start comment : 20140306 : by kik
             * ส่วนนี้ไว้ก่อน เพราะจากการตรวจสอบฟังก์ชั่นที่เรียก Product status list จะกำหนดไม่ให้เอา status PENDING ขึ้นมาแสดงผลอยู่แล้ว
             * เพราะฉะนั้นโค้ดส่วนนี้จึงไม่จำเป็นต้องตรวจสอบอีกรอบ
             * และที่สำคัญ Hardcode เอาไว้ ควรจะเขียน query เรียกจาก database มาตรวจสอบมากกว่า
             */
//                prod_status = rowData[i][ci_prod_status];
//                if ("PENDING" == prod_status) {
//                    alert("Product Status 'Pending' not Allow !!");
//                    return false;
//                }
              /**
               * end comment : 20140306 : by kik
              ------------------------------------------------------------------ */
            var chk_actual_loc = rowData[i][ci_actual_loc];
            var chk_show_actual_location = rowData[i][ci_show_actual_location];
            var chk_remark = rowData[i][ci_remark];

              if (chk_actual_loc != "" || chk_show_actual_location != "") {
                    if(set_suggest==true){
                        // console.log(set_suggest)

                        
                        // if (chk_actual_loc == 0) {
                        //     alert("Please select destination location.");
                        //     return false;
                        // }
                        // if (chk_suggest_loc != chk_actual_loc) {
                        // if (chk_actual_loc) {
                        //     if (chk_remark == "") {
                        //         alert('Please Check Your Information Remark');
                        //         var oTable = $('#showProductTable').dataTable(); // get datatables
                        //         var cTable = oTable.fnGetNodes();
                        //         $(cTable[i]).find("td:eq("+ci_remark+")").html('');
                        //         var input = $('<input>').attr('class', 'txt_remark required').appendTo($(cTable[i]).find("td:eq("+ci_remark+")"));
                        //         return false;
                        //     }
                            
                        // }
                    }
                } else {
                    alert('Please Select Location');
                    return false;
                }
            }

//------------------------ End check validate Form ---------------------------------

//============================ Start data_form ====================================

            $("#" + form_name + " :input").not("[name=showProductTable_length]").attr("disabled", false);
                var f = document.getElementById(form_name);
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

                $('.class_prod_list').remove();  // Add By Akkarapol, 04/11/2013, เพิ่มโค๊ดในการลบ prodItem ทั้งหมดที่เคยได้ append เข้าไปใน form เพราะไม่งั้นมันจะเกิดอาการ duplicate ซึ่งส่งผลเสียให้กับระบบแน่ๆ เลยทำการ ลบก่อน เผื่อเกิดเหตุการณ์ที่ไม่คาดฝันเกิดขึ้น เช่น ajax รันไปไม่ทำงาน หรือจะ return กลับมาแล้วไม่ได้ redirect แล้วดันไปกดปุ่ม submit อีกครั้ง อะไรแบบนี้ จึงทำขึ้นมาป้องกันก่อนเป็นดีที่สุด

                var oTable = $('#showProductTable').dataTable().fnGetData();

                for (i in oTable) {

                    //ADD BY POR 2013-12-02 เอา comma ออกก่อนส่งค่าไปบันทึก
                    oTable[i][9] = oTable[i][9].replace(/\,/g,'');
                    oTable[i][10] = oTable[i][10].replace(/\,/g,'');
                    //END ADD

                    var prod_data = oTable[i].join(separator);
                    var prodItem = document.createElement("input");
                    prodItem.setAttribute('type', "hidden");
                    prodItem.setAttribute('name', "prod_list[]");
                    prodItem.setAttribute('class', "class_prod_list"); // Add By Akkarapol, 04/11/2013, เพิ่ม class ที่ชื่อ 'class_prod_list' ให้กับ prodItem ที่จะถูก append เพื่อที่ว่า จะได้เรียกถึงตัวของ prodItem นี้ได้ในภายหลัง
                    prodItem.setAttribute('value', prod_data);
                    f.appendChild(prodItem);
                }

                $.each(ci_list, function(i, obj) {
                    var ci_item = document.createElement("input");
                    ci_item.setAttribute('type', "hidden");
                    ci_item.setAttribute('name', obj.name);
                    ci_item.setAttribute('value', obj.value);
                    f.appendChild(ci_item);
                });

                global_data_form = $("#" + form_name).serialize();

//---------------------------------- End data_form ---------------------------------

//======================== Start check validate Data ===============================

                if(global_sub_module != 'rejectAction' && global_sub_module !='rejectAndReturnAction'){
                    validation_data();
                }else{
                    var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
                    $('#div_for_alert_message').html(mess);
                    $('#div_for_modal_message').modal('show').css({
                        'margin-left': function() {
                                        return ($(window).width() - $(this).width()) / 2;
                                }
                        });
                }

//            if (confirm("Are you sure to action " + action_value + "?")) {
//
//                var message = "";
//
//                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
//                    switch (data.status) {
//                        case 'C001':
//                            message = "Open Job Change Status Complete";
//                            break;
//                        case 'C002':
//                            message = "Confirm Change Status  Complete";
//                            break;
//                        case 'C003':
//                            message = "Approve Change Status  Complete";
//                            break;
//                        case 'C004':        // add by kik : 08-01-2013
//                            window.onbeforeunload = null;
//                            message = "Reject Change Status Complete";
//                            break;
//                        case 'E001':
//                            message = "Save Change Status  Incomplete";
//                            break;
//                        case 'E002':
//                            message = "Sorry, Estimate balance is less than you need.";
//                            alert(message);
//                            return false;
//                            break; // Add By Akkarapol, 03/10/2013, เพิ่มเพื่อรอรับค่า return หากเป็น E002 จะให้เข้า case ตามนี้
//                    }
//                    alert(message);
//                    url = "<?php echo site_url(); ?>/flow/flowChangeStatusList";
//                    redirect(url);
//                }, "json");
//            }
        } else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
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
                if(data == rowData[i][ci_pallet_code] && rowData[i][ci_dp_type_pallet] == 'FULL'){
                     $('#showProductTable').dataTable().fnDeleteRow(i);
                }
            }

            calculate_qty();
        }

    }


    function deleteItem(obj) {
        var index = $(obj).closest("table tr").index();
        var data = $('#showProductTable').dataTable().fnGetData(index);
        data = data.join(separator); // Add By Akkarapol, 28/01/2014, set delimiter from ',' to separator in variable data
        $('#showProductTable').dataTable().fnDeleteRow(index);
        var f = document.getElementById(form_name);
        var prodDelItem = document.createElement("input");
        prodDelItem.setAttribute('type', "hidden");
        prodDelItem.setAttribute('name', "prod_del_list[]");
        prodDelItem.setAttribute('value', data);
        f.appendChild(prodDelItem);
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
			window.onbeforeunload = null;
            url = "<?php echo site_url(); ?>/flow/flowChangeStatusList";
            redirect(url);
        }
    }

    function validateForm() {
        $("form").validate({
            rules: {
                est_action_date: {required: true}
            }
        });
        return $("form").valid();
    }

    // Add function calculate_qty : by kik : 28-10-2013
    function calculate_qty() {

        var rowData = $('#showProductTable').dataTable().fnGetData();
        var rowData2 = $('#showProductTable').dataTable();
        var num_row = rowData.length;
        var sum_cf_qty = 0;
        var sum_price = 0; //ADD BY POR 2014-01-16 ราคาทั้งหมดต่อหน่วย
        var sum_all_price = 0; //ADD BY POR 2014-01-16 ราคาทั้งหมด
        for (i in rowData) {
            var tmp_qty = 0;
            var tmp_price = 0;//ราคาต่อหน่วย
            var all_price = 0; //ราค่าทั้งหมดต่อหนึ่งรายการ

            tmp_qty = parseFloat(rowData[i][ci_confirm_qty].replace(/\,/g,'')); //EDIT BY POR 2013-12-02 แก้ไขให้นำ comma ออก และแปลงเป็น parseFloat

            if (!($.isNumeric(tmp_qty))) {
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

            rowData2.fnUpdate(set_number_format(all_price), parseInt(i), ci_all_price); //update ราคารวมทั้งหมดใน datatable
            //END ADD

            sum_cf_qty = sum_cf_qty + tmp_qty;
        }

        $('#sum_recieve_qty').html(set_number_format(sum_cf_qty));
        $('#sum_price_unit').html(set_number_format(sum_price)); //ADD BY POR 2014-01-16 เพิ่มให้แสดงราคารวมทั้งหมดต่อหน่วย
        $('#sum_all_price').html(set_number_format(sum_all_price)); //ADD BY POR 2014-01-16 เพิ่มให้แสดงราคารวมทั้งหมด

    }
    // end function calculate_qty : by kik : 28-10-2013
    function reInitProductTable() {
        var oTable = $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "aoColumnDefs": [
                {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
                {"sWidth": "20%", "sClass": "left_text", "aTargets": [2]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [3]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [4]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [5]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [6]},
                {"sWidth": "5%", "sClass": "indent obj_mfg", "aTargets": [7]},
                {"sWidth": "5%", "sClass": "center obj_exp", "aTargets": [8]},
                {"sWidth": "7%", "sClass": "td_actual_location", "aTargets": [9]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [10]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [11]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [12]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [13]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [14]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [15]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [16]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [17]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [18]},
              
//                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [9]},
//                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [10]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [11]},
//                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [12]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [13]},
//                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [14]},
//                {"sWidth": "5%", "sClass": "left_text", "aTargets": [15]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [16]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [17]},
//                {"sWidth": "7%", "sClass": "td_actual_location center", "aTargets": [29]},
            ]
        }).makeEditable({
            sUpdateURL: function(value, settings) {
                return value;
            }
            , "aoColumns": [
                null
                        , null
                        , null
                        , {

                    data: JSON.stringify(master_product_status),
                    event: 'click',
                    type: 'select',
                    onblur: 'submit',
                    select_filter_active : true,
                    select_filter_by : "PENDING",
                    sUpdateURL: function(value, settings) {
                        var oTable = $('#showProductTable').dataTable();
                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                        oTable.fnUpdate(value, rowIndex, ci_prod_status);

                        //start add for ISSUE 3334 : by kik : 20140220
                        var datas = oTable.fnGetData(rowIndex);
                        if(datas[ci_dp_type_pallet] == "FULL"){
                            update_pallet_status(rowIndex,value,datas[ci_pallet_code]);
                        }

                    return value;
                    },
                }
                , {
                    data : master_product_sub_status,
                    event: 'click',
                    type: 'select',
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {
                    var oTable = $('#showProductTable').dataTable();
                         var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                         oTable.fnUpdate(value, rowIndex, ci_prod_sub_status);
                         return value;
                    }
                }
				, {
					onblur: 'submit',
                    event: 'click'
				}
				, {
					onblur: 'submit',
                    event: 'click'
				}
                , {
                    onblur: 'submit',
                    type: 'datepicker',
                    cssclass: 'date',
                    event: 'click focusin',
                    is_required: false,
                    loadfirst: true,
                }
                , {
                    onblur: 'submit',
                    type: 'datepicker',
                    cssclass: 'date',
                    event: 'click focusin',
                    loadfirst: true,
                }
                        , null
                        , null
                        , {
                    sSortDataType: "dom-text",
                    sType: "numeric",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    cssclass: "required number",
                    loadfirst: true, // Add By Akkarapol, 15/10/2013, เพิ่ม loadfirst เพื่อให้ช่อง Change Qty นี้มี textbox ขึ้นมาตั้งแต่โหลด Product
                    "event" : 'click', // Add By Akkarapol, 15/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Change Qty นี้ โดยการ คลิกเพียง คลิกเดียว
                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 04-11-2013
                        calculate_qty();
                    }
                }
                , null
                 , {
                    onblur: 'submit',
                    "event": 'click', // Add By Akkarapol, 15/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว
                }
                , null
                , null
                , null
                , {
                    onblur: 'submit',
                    "event": 'click', // Add By Akkarapol, 15/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว
                }
                , null
//                , null
            ]
        });

        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_suggest_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_actual_loc, false);
        //$('#showProductTable').dataTable().fnSetColumnVis(ci_confirm_qty,false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_old_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_inbound_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_dp_type_pallet, false); // add for ISSUE3334 : by kik : 20140217

        if(statusprice!=true){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
        }

        //add by kik : 20140113
        if(config_pallet!=true){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_pallet_code, false);
        }
        //end add by kik : 20140113

        $('#showProductTable tbody tr td[title]').hover(function() {

            // Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา
            var chk_title = $(this).attr('title');
            var chk_innerHTML = this.innerHTML;
            if(chk_title != chk_innerHTML){
                $(this).show_tooltip();
            }
            // END Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา

        }
        
        , function() {
            $(this).hide_tooltip();
        });
        new FixedColumns(oTable, {
            "iLeftColumns": 0,
            "sLeftWidth": 'relative',
            "iLeftWidth": 0
        });
    }
    
</script>
<style>
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
    /* END Add by Akkarapol, 01/10/2013, เธ—เธณ autocomplete เน�เธ�เธชเน�เธงเธ�เธ�เธญเธ� Product Code เน�เธฅเธฐเน€เธกเธทเน�เธญเธ�เธฅเธดเธ�เธ—เธตเน� list product เธ”เธฑเธ�เธ�เธฅเน�เธฒเธง เธ�เน�เธ�เธฐเน�เธซเน� auto input to datatable*/

    #suggestion_div {
        width: 220px;
        height: 170px;
        display: none;
        z-index: 2000;
        position: absolute;
        background-color: #FFF;
        overflow-y:hidden;
    }

    #myModal{
        width: 1170px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -600px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */

    }


/*    .tooltip {
        left: 140px !important;
        width: 200px !important;
    }*/
</style>
<div class="well">
    <form id="form_ch_status" method=post action="">
        <?php
        if (!isset($owner_id)) {
            $owner_id = "";
        }
        if (!isset($est_action_date)) {
            $est_action_date = date("d/m/Y");
        }
        if (!isset($process_type)) {
            $process_type = $data_form['process_type'];
        }
        if (!isset($document_no)) {
            $document_no = "";
        }
        if (!isset($action_date)) {
            $action_date = "";
        }
        if (!isset($remark)) {
            $remark = "";
        }
        if (!isset($assigned_id)) {
            $assigned_id = "";
        }
        ?>
        <?php
        if (isset($flow_id)) {
            echo form_hidden('flow_id', $flow_id);
        }
        ?>
        <?php
        if (isset($order_id)) {
            echo form_hidden('order_id', $order_id);
        }

         if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
                $is_urgent = false;
            } else {
                $is_urgent = true;
            }
         
         if (!isset($document_ref)) {  //ADD BY POR 2014-11-17
            $document_ref = "";
        }
        ?>
        <?php echo form_hidden('process_id', $process_id); ?>
<?php echo form_hidden('present_state', $present_state); ?>
<?php echo form_hidden('user_id', $user_id); ?>
<?php echo form_hidden('process_type', $process_type); ?>
<?php echo form_hidden('owner_id', $owner_id); ?>
        <input type="hidden" name="token" value="<?php echo $token; ?>" />
        <fieldset class="well">
            <legend>&nbsp;&nbsp;<b>Change Product Status</b>&nbsp;&nbsp;</legend>
            <table width="98%">
                <tr>
                    <td align="right">Est. Action Date</td>
                    <td align="left">
<?php echo form_input('est_action_date', $est_action_date, 'id="est_action_date" placeholder="Estimate Re-Location Date" class="required"  '); ?>
                    </td>
                    <td align="right">Action Date</td>
                    <td align="left"><?php echo form_input('action_date', $action_date, 'id="action_date" placeholder="Putaway Date"  readonly="readyonly" '); ?></td>
                    <td align="right"></td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="right">Document No.</td>
                    <td align="left"><?php echo form_input('document_no', $document_no, 'placeholder="Auto Generate Document" disabled style="text-transform: uppercase"'); ?></td>
                    <td align="right">Worker Name</td>
                    <td align="left"><?php echo form_dropdown('assigned_id', $assign_list, $assigned_id, ""); ?></td>
                    <td align="right"></td>
                    <td align="left"></td>
                </tr>
                <!--ให้สามารถกรอกเลขที่คำร้อง เพื่อให้รองรับ customs report : ADD BY POR 2014-11-14-->
                <tr>
                    <td align="right">Document Reference</td>
                    <td align="left" colspan="4"><?php echo form_input('document_ref', $document_ref, 'placeholder="' . DOCUMENT_REF . '" style="text-transform: uppercase"'); ?></td>
                </tr>
                <!--end Document Reference -->
                <tr valign="center">
                    <td align="right">Remark</td>
                    <td align="left" colspan="2">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:80%" placeholder="Remark..."><?php echo trim($remark); ?></TEXTAREA>
			</td>

			<td align="left">
                        <!--//add for ISSUE 3312 : by kik : 20140120-->
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                        </td>
                        <td align="left"></td>
			<td align="left"></td>
		  </tr>
	  </table>
	</fieldset>
	</form>
	<fieldset class="well">
	<legend>&nbsp;&nbsp;<b>Product Detail</b>&nbsp;&nbsp;</legend>
	  <table width="100%">

                <tr>
                        <td>
                            <table style="width:100%; margin:0px auto;">
                                <?php $this->load->view('element_filtergetDetail'); ?>
                            </table>
                        </td>
                    </tr>

		  <tr align="center" >
			<td align="center" id="showDataTable" >
				<table width ="100%" align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
					<thead>
                            <?php
                            $show_column = array(
                                _lang('no'),
                                _lang('product_code'),
                                _lang('product_name'),
                                _lang('product_status'),
                                _lang('product_sub_status'),
                                _lang('lot'),
                                _lang('serial'),
                                _lang('product_mfd'),
                                _lang('product_exp'),
                                _lang('location_code'), 
                                _lang('est_balance_qty'),
                                _lang('change_qty'),
                                _lang('unit'),
                                _lang('price_per_unit'),
                                _lang('unit_price'),
                                _lang('all_price'),
                                _lang('pallet_code'),
                                _lang('remark'),
                                _lang('del'),
                                "Product_Id",
                                "Product_Status",
                                "Product_Sub_Status",
                                "Unit_Id",
                                "Item_Id",
                                "Suggest_Location_Id",
                                "Actual_Location_Id",
                                "Old_Location_Id",
                                "Inbound_Id",
                                "Unit Price ID",
                                "DP_Type_Pallet",    // add for ISSUE3334 : by kik : 20140217
//                                _lang('location_code'),    // add for ISSUE3334 : by kik : 20140217
                            );
                            $str_header = "";
                            foreach ($show_column as $index => $column) {
                                $str_header .= "<th>" . $column . "</th>";
                            }
                            ?>

						<tr><?php echo $str_header; ?></tr>
					</thead>
                                        <tbody>
                            <?php
                            $sum_Confirm_Qty = 0;   //add by kik : 04-11-2013
                            $sumPriceUnit = 0; //ADD BY POR 2014-01-16 ราคารวมทั้งหมดต่อหน่วย
                            $sumPrice = 0; //ADD BY POR 2014-01-16 ราคารวมทั้งหมด
                            if (isset($order_detail)) {
                                $str_body = "";
                                $j = 1;
                                // p($order_detail);exit;
                                foreach ($order_detail as $order_column) {
                                    
                                    $estimate_balance = getCalculateAllowcate($order_column->Receive_Qty,$order_column->Dispatch_Qty,$order_column->Adjust_Qty,$order_column->PD_Reserv_Qty);
                                    $class_DP_Type_Palet = (!empty($order_column->DP_Type_Pallet) && $order_column->DP_Type_Pallet=="FULL")?"readonly":""; // add for ISSUE3334 : by kik : 20140217
//                                    $str_body .= "<tr title='" . $order_column->Full_Product_Name . "' >";
                                    $str_body .= "<tr>"; // Edit by Ton! 20131127
                                    $str_body .= "<td>" . $j . "</td>";
                                    //add class td_click and ONCLICK for show Product Est. balance Detail modal : by kik : 06-11-2013
                                    $str_body .= "<td class='td_click' align='center'  ONCLICK='showProductEstInbound(\"{$order_column->Product_Code}\",{$order_column->Inbound_Item_Id})'>" . $order_column->Product_Code . "</td>";
//                                    $str_body .= "<td title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">" . $order_column->Product_Name . "</td>";
                                    $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                    $str_body .= "<td>" . $order_column->Status_Value . "</td>";
                                    $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                     $str_body .= "<td class='td_actual_location'>" . $order_column->Actual_Location . "</td>";
                                    $str_body .= "<td>" . set_number_format($estimate_balance) . "</td>";     #add by kik : 11-10-2013
//                                     $str_body .= "<td>" . $order_column->Est_Balance_Qty . "</td>";     #add by kik : 11-10-2013

                                    if ("" == $order_column->Confirm_Qty) {
                                        $order_column->Confirm_Qty = $order_column->Reserv_Qty;
                                    }
                                    $str_body .= "<td class='".$class_DP_Type_Palet."'>" . set_number_format($order_column->Confirm_Qty) . "</td>";
                                    $str_body .= "<td>" . $order_column->Unit_Value . "</td>";

                                    //ADD BY POR 2014-01-15 เพิ่ม column เกี่ยวกับ price
                                        $str_body .= "<td>" . set_number_format($order_column->Price_Per_Unit) . "</td>";
                                        $str_body .= "<td>" . $order_column->unit_price . "</td>";
                                        $str_body .= "<td>" . set_number_format($order_column->All_Price) . "</td>";
                                    //END ADD

                                    $str_body .= "<td>" . $order_column->Pallet_Code . "</td>";
                                    $str_body .= "<td>" . $order_column->Remark . "</td>";
                                    $str_body .= "<td><a ONCLICK=\"deleteItem(this)\" >" . img("css/images/icons/del.png") . "</a></td>";
                                    $str_body .= "<td>" . $order_column->Product_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Status_Code . "</td>";
                                    $str_body .= "<td>" . $order_column->Sub_Status_Code . "</td>";
                                    $str_body .= "<td>" . $order_column->Unit_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Item_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Suggest_Location_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Actual_Location_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Old_Location_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Inbound_Item_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Unit_Price_Id . "</td>"; //ADD BY POR 2014-01-16 เพิ่ม ID ของ unit price
                                    $str_body .= "<td>" . $order_column->DP_Type_Pallet . "</td>"; // add for ISSUE3334 : by kik : 20140217
//                                    $str_body .= "<td class='td_actual_location'>" . $order_column->Old_Location . "</td>";
                                    $str_body .= "</tr>";
                                    $j++;
                                    $sum_Confirm_Qty+=$order_column->Confirm_Qty;        //add by kik    :   04-11-2013

                                    //ADD BY POR 2014-01-13 รวมราคาต่อหน่วยและราคารวม
                                    $sumPriceUnit+=$order_column->Price_Per_Unit;
                                    $sumPrice+=$order_column->All_Price;
                                    //END ADD
                                }
                                echo $str_body;
                            }
                            ?>
					</tbody>

                                        </tbody>
<!--                                          show total qty : by kik : 29-10-2013
-->                                                <tfoot>
                                                   <tr>
                                                         <th colspan='11' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                                         <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Confirm_Qty); ?></span></th>
                                                         <th colspan='20' class ='ui-state-default indent' ></th>
<!--                                                           <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                                       <th></th>
                                                        <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>-->
                                                         <!--<th colspan='13' class ='ui-state-default indent' ></th>-->
                                                    </tr>
                                                </tfoot><!--
                                         end show total qty : by kik : 29-10-2013-->
				</table>
			</td>
		  </tr>
	  </table>
	</fieldset>
</div>
<div id="suggestion_div">
	<div class="suggestionList" id="suggestionLocationList"></div>
</div>
<!--call element element show product get Detail modal : add by kik : 16-12-2013-->

<?php $this->load->view('element_showgetDetail'); ?>


<!--call element Product Est. balance Detail modal : add by kik : 06-11-2013-->
<?php $this->load->view('element_showEstBalance'); ?>

<!--call element Show modal message alert modal : add by kik : 06-03-2014-->
<?php $this->load->view('element_modal_message_alert'); ?>
