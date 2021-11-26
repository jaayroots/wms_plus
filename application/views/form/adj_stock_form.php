<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var oTable = null;
    var allVals;
    var dispatch_type;
    var statusprice = "<?php echo $price_per_unit; ?>"; //add by kik :
    var config_pallet = "<?php echo ($config_pallet == 1 ? true : false)?>"; // add by kik for ISSUE 3333 : 20140220
    var separator = "<?php echo SEPARATOR; ?>"; // Add By Akkarapol, 22/01/2013, Add Separator for use in Page

    // add var for validation data
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Stock Adjustment ';
    var data_table_id_class = '#showProductTable';
    var redirect_after_save = site_url + "/adjust_stock"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';
    // end add var for validation data


    var ci_prod_code = 1;
    var ci_product_status = 3;
    var ci_product_lot = 5;
    var ci_product_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_suggest_location_id = 9;
    var ci_reserv_qty = 10;
    var ci_confirm_qty = 11;
    var ci_unit_Value = 12;
    var ci_inbound_id = 16;
    var ci_product_sub_status = 17;
    var ci_item_id = 18;
    var ci_unit_Id = 19;

    // add by kik : 2014-01-14
    var ci_price_per_unit = 13;
    var ci_unit_price = 14;
    var ci_all_price = 15;
    var ci_unit_price_id = 20;
    //end add by kik : 2014-01-14

    //add by kik for ISSUE 3333 : 2014-02-12
    var ci_pallet_code = 21;
    var ci_dp_type_pallet = 22;
    //end add by kik for ISSUE 3333 : 2014-02-12

    var ci_list = [
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_product_status', value: ci_product_status},
        {name: 'ci_product_lot', value: ci_product_lot},
        {name: 'ci_product_serial', value: ci_product_serial},
        {name: 'ci_mfd', value: ci_mfd},
        {name: 'ci_exp', value: ci_exp},
        {name: 'ci_suggest_location_id', value: ci_suggest_location_id},
        {name: 'ci_reserv_qty', value: ci_reserv_qty},
        {name: 'ci_confirm_qty', value: ci_confirm_qty},
        {name: 'ci_unit_Value', value: ci_unit_Value},
        {name: 'ci_price_per_unit', value: ci_price_per_unit},      // add by kik : 2014-01-15
        {name: 'ci_unit_price', value: ci_unit_price},              // add by kik : 2014-01-15
        {name: 'ci_all_price', value: ci_all_price},                // add by kik : 2014-01-15
        {name: 'ci_inbound_id', value: ci_inbound_id},
        {name: 'ci_product_sub_status', value: ci_product_sub_status},
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_unit_Id', value: ci_unit_Id},
        {name: 'ci_unit_price_id', value: ci_unit_price_id},         // add by kik : 2014-01-15
        {name: 'ci_pallet_code', value: ci_pallet_code} ,            // add by kik : 2014-02-19
        {name: 'ci_dp_type_pallet', value: ci_dp_type_pallet}        // add by kik : 2014-02-12
    ]

    var item_in_lists = [];

    $(document).ready(function() {

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


        // Add By Akkarapol, 19/12/2013, เพิ่ม $('#showProductTable tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย
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
        // END Add By Akkarapol, 19/12/2013, เพิ่ม $('#showProductTable tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย

        // Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง


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



        $("#est_relocate_date").datepicker({
            startDate: '-0m'
        }).on('changeDate', function(ev) {
            //$('#sDate1').text($('#datepicker').data('date'));
            //$('#est_relocate_date').datepicker('hide');
        });

        $("#relocate_date").datepicker();
        getSelectLocationOption();

        $('#search_submit').click(function() {

            //Add code for fix defect : 219 :  add by kik : 2013-12-16
            if (allVals.length == 0) {
                alert("No Product was selected ! Please select a product or click cancle button to exit.");
                return false;
            }
            //end Add code for fix defect : 219 :  add by kik : 2013-12-16

            var dataSet = {
                post_val: allVals,
                dp_type_pallet_val : dispatch_type
            }

            $.each(allVals, function(idx, value){
                if ($.inArray( value, item_in_lists ) == -1) {
                    item_in_lists.push(value);
                }
            });

            $.post('<?php echo site_url() . "/adjust_stock/showSelectedProduct" ?>', dataSet, function(data) {
                //var pstatus="NORMAL";
                var Unit_Id = "";
                var actual_location = "";
                var adj_qty = "";
                var remark = "";
                var allprice="";
                var price=set_number_format(0);

                //+' <a ONCLICK="showProduct('+item.Location_Id+')"><?php echo img("css/images/icons/view.png"); ?></a>'
                $.each(data.locations, function(i, item) {
                    var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
                    //add by kik : 20140115
                    if(item.Price_Per_Unit != ""){
                        price=item.Price_Per_Unit;
                    }
                    //end add by kik : 20140115

                     //add by kik : 20140212
                    if(item.DP_Type_Pallet == "FULL"){
                        adj_qty = item.Est_Balance_Qty;
//                        del = "DEL";
                         del = "<a ONCLICK=\"removePalletItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>"; // add by kik : 20140219
                    }
                    //end add by kik : 20140212

                    allprice = adj_qty*item.Price_Per_Unit;




                    var a = $('#showProductTable').dataTable().fnAddData([
                         recordsTotal    //add for edit No. to running number : by kik : 20140226
                                , item.Product_Code
                                , item.Product_NameEN
                                , item.Product_Status
                                , item.Sub_Status_Value  //add by kik : 04-098-2013
                                , item.Product_Lot
                                , item.Product_Serial
                                , item.Product_Mfd //EDIT BY POR 2013-12-12 สลับที่ให้ mfd exp มาไว้หน้า location code
                                , item.Product_Exp //EDIT BY POR 2013-12-12 สลับที่ให้ mfd exp มาไว้หน้า location code
                                , item.Location_Code
                                , item.Est_Balance_Qty
                                , set_number_format(adj_qty)
                                , item.Unit_Value
                                , price                     //add by kik : 20140115
                                , item.Unit_Price_value     //add by kik : 20140115
                                , set_number_format(allprice)                  //add by kik : 20140115
                                , item.Inbound_Id
                                , item.Sub_Status_Code
                                , "Item_Id"              // add by kik : 11-10-2013
                                , item.Unit_Id
                                , item.Unit_Price_Id        //add by kik : 20140115
                                , item.Pallet_Code          // add by kik : 20140219
                                , item.DP_Type_Pallet       //add by kik : 20140212
                                , del]
                            );


                    $('#showProductTable').dataTable().fnSetColumnVis(ci_inbound_id, false); //edit ci_inbound_id by kik : 04-09-2013
                    $('#showProductTable').dataTable().fnSetColumnVis(ci_product_sub_status, false);// add index ci_product_sub_status by kik : 04-09-2013
                    $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);// add index ci_item_id by kik : 11-10-2013
                    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_Id, false);
                    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);// add index ci_unit_price_id by kik : 15-01-2014
                    $('#showProductTable').dataTable().fnSetColumnVis(ci_dp_type_pallet, false);// add index ci_dp_type_pallet by kik : 2014-02-12
                    //
                    // add by kik : 2013-11-13
                    var new_td_item = $('td:eq(1)', $('#showProductTable tr:last'));
                    new_td_item.addClass("td_click");
                    new_td_item.attr('onclick', 'showProductEstInbound(' + '"' + item.Product_Code + '"' + ',' + item.Inbound_Id + ')');
                    // end add by kik


                    if(item.DP_Type_Pallet == "FULL"){
                        var dp_type_full = $('td:eq('+ci_confirm_qty+')', $('#showProductTable tr:last'));
                        dp_type_full.addClass("readonly");
                    }

                    recordsTotal++;     //add for edit No. to running number : by kik : 20140226
                });

                calculate_qty();
                initProductTable();
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

    function getSelectLocationOption() {
        $.post('<?php echo site_url(); ?>/reLocation/genLocationSelectOption', function(data) {
            $('#location_list').val(data);
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

            $('#boxDetail .modal-body #defDataTable').dataTable({
                "bJQueryUI": true,
                "bSort": false,
                "oSearch": {},
                "bRetrieve": true,
                "bDestroy": true,
                "sPaginationType": "full_numbers"
            });
            //tmp=data;
            $('#boxDetail').modal('show');

        }, "json");

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




    function initProductTable() {

        $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
        	"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
				//console.log(nRow);
        	},
            "aoColumnDefs": [
                {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
                {"sWidth": "20%", "sClass": "left_text", "aTargets": [2]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [3]}, // Edit by Ton! 20131001
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [4]}, //Edit by Ton! 20131001
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [5]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [6]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [7]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [8]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [9]},
                {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [10]},
                {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [11]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [12]},
                {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [13]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [14]},
                {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [15]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [16]},
            ]
        }).makeEditable({
            sUpdateURL: '<?php echo site_url() . "/reLocation/editDataTable"; ?>'
                    , "aoColumns": [
                null
                        , null
                        , null
                        , null
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
                    loadfirst: true, // Add By Akkarapol, 15/10/2013, เพิ่ม loadfirst เพื่อให้ช่อง Adjust Qty นี้มี textbox ขึ้นมาตั้งแต่โหลด Product
                    "event": 'click', // Add By Akkarapol, 15/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Adjust Qty นี้ โดยการ คลิกเพียง คลิกเดียว
                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 28-10-2013
                        calculate_qty();
                    }
                }
                , null
                , null
                , null
                , null
                        , null
                        , null
                        , {"bSearchable": false}
            ]
        });
        //add by kik : 20140113
        if(statusprice!=true){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
        }
        //end add by kik : 20140113

        //add by kik : 20140113
        if(config_pallet!=true){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_pallet_code, false);
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
        calculate_qty();
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
        data = data.join(separator); // Add By Akkarapol, 28/01/2014, set delimiter from ',' to separator in variable data
        $('#showProductTable').dataTable().fnDeleteRow(index);
        var f = document.getElementById("form_receive");
        var prodDelItem = document.createElement("input");
        prodDelItem.setAttribute('type', "hidden");
        prodDelItem.setAttribute('name', "prod_del_list[]");
        prodDelItem.setAttribute('value', data);
        f.appendChild(prodDelItem);
        calculate_qty();
    }



    function postRequestAction(module, sub_module, action_value, next_state, elm) {
        global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');

        $("input[name='prod_list[]']").remove();


        //alert('module='+module+'/ sub='+sub_module+'/ actvion='+action_value+'/ '+next_state);
        var statusisValidateForm = validateForm();

        if (statusisValidateForm === true) {

//================================= Check Validation Form==================================
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

            var inputs = [];
            $('#showProductTable tr td input').each(function() {
                var other = $(this).val();
                //alert('other = '+other);
                //$(this).parent().html('0');
                //initProductTable();
                inputs.push(other);
            });

            if (inputs.length != 0) {
                alert('Please fill all information');
                return false;
            }

            var cTable = $('#showProductTable').dataTable().fnGetNodes();
            var cells = [];
//			var cells2=[];
            for (var i = 0; i < cTable.length; i++)
            {
                // Get HTML of 3rd column (for example)
                var qty = $(cTable[i]).find("td:eq("+ci_confirm_qty+")").html();

                if (qty == 'Edit...' || qty == "" || qty == 0 || qty == "NaN") {
                    cells.push($(cTable[i]).find("td:eq("+ci_confirm_qty+")").html());
                }

                if (qty < 0) {
                    alert("Negative Adjust Qty is not allow");
                    return false;
                }
            }
            var all_qty = cells.length;

            if (all_qty != 0) {
                alert('Please fill all Adjust Qty and not equal 0!');   //edit by kik : 14-10-2013
                return false;
            }
//--------------------------------- end Check Validation Form ---------------------------

//================================= Start data_form =======================================
                var backupForm = document.getElementById('form_receive').innerHTML;
                var f = document.getElementById("form_receive");
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

                var oTable = $('#showProductTable').dataTable().fnGetData();
                for (i in oTable) {

                    oTable[i][ci_reserv_qty] = oTable[i][ci_reserv_qty].replace(/\,/g, ''); //EDIT BY POR 2013-12-12 เปลี่ยนตำแหน่งเนื่องจากมีการขยับ mfd exp มาไว้หน้า location code
                    oTable[i][ci_confirm_qty] = oTable[i][ci_confirm_qty].replace(/\,/g, ''); //EDIT BY POR 2013-12-12 เปลี่ยนตำแหน่งเนื่องจากมีการขยับ mfd exp มาไว้หน้า location code

                    var prod_data = oTable[i].join(separator);

                    var prodItem = document.createElement("input");
                    prodItem.setAttribute('type', "hidden");
                    prodItem.setAttribute('name', "prod_list[]");
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

            global_data_form = $("#form_receive").serialize();
//---------------------------------- End data_form --------------------------------------


//====================== Start Validate data and Save data ==============================

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
//-----------------------End Validate data and Save data------------------------------------

        }
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
			window.onbeforeunload = null;
            url = "<?php echo site_url(); ?>/adjust_stock";
            redirect(url)
        }
    }

    function validateForm() {
        //validate engine
        var status;
        $("form").each(function() {
            $(this).validate();
            $(this).valid();
            status = $(this).valid();
        });
        return status;
        //end of validate engine
    }

    // Add function calculate_qty : by kik : 29-10-2013
    function calculate_qty() {
        var rowData = $('#showProductTable').dataTable().fnGetData();
        var rowData2 = $('#showProductTable').dataTable();        //add by kik : 20140113

        var num_row = rowData.length;
        var sum_cf_qty = 0;
        var sum_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมดต่อหน่วย
        var sum_all_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมด
        for (i in rowData) {
            var tmp_qty = 0;
            var tmp_price = 0;//ราคาต่อหน่วย
            var all_price = 0; //ราค่าทั้งหมดต่อหนึ่งรายการ
            //tmp_qty=parseFloat(rowData[i][9].replace(/\,/g,'')); //Edit by por 2013-12-02 แก้ไขจาก parseInt เป็น parseFloat และตัด comma ออกเพื่อให้สามารถคำนวณได้
            tmp_qty = parseFloat(rowData[i][ci_confirm_qty].replace(/\,/g, '')); //Edit by por 2013-12-12 เปลี่ยนตำแหน่ง เนื่องจากย้าย mfd exp มาไว้หน้า location code จึงทำให้ตำแหน่งเปลี่ยน
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
    #myModal,
    #boxDetail{
        width: 1170px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -600px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
        /*z-index:9999*/


    }

</style>

<div class="well">
    <form id="form_receive" method=post action="" class="">
        <input type="hidden" name="location_list" id="location_list" />
        <fieldset>
            <legend>&nbsp;Order&nbsp;</legend>
            <?php
            $act_re = '';
            if (isset($flow_id)) {
                echo form_hidden('flow_id', $flow_id);
                $act_re = 'class="required" ';
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
            //if(!isset($doc_relocate)){$doc_relocate = ""; }
            if (!isset($est_action_date)) {
                $est_action_date = "";
            }
            if (!isset($act_action_date)) {
                $act_action_date = "";
            }
            if (!isset($process_type)) {
                $process_type = $data_form['process_type'];
            }
            if (!isset($remark)) {
                $remark = "";
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
            <?php echo form_hidden('user_id', $user_id); ?>
            <?php echo form_hidden('owner_id', $owner_id); ?>
            <?php echo form_hidden('process_type', $process_type); ?>

            <table width="98%">
                <tr>
                    <td align="right">Renter : </td>
                    <td align="left">
                        <?php echo form_dropdown('renter_id', $renter_list, $renter_id, 'class="required" '); ?>
                    </td>
                    <td align="right">Document No : </td>
                    <td align="left">
                        <input type="text" placeholder="Auto Generate" id="adjust_no" name="adjust_no" disabled value="<?php echo $document_no; ?>" />
                    </td>
                    <td align="right"></td>
                    <td align="left"></td>
                </tr>

                <tr>
                    <td align="right" >Remark : </td>
                    <td align="left" >
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo $remark; ?></TEXTAREA>
			</td>
			<td align="right" valign='top'>Adjust Type : </td>
			<td align="left" valign='top'>{dispatch_type_select}
                        <br>
                            <!--//add for ISSUE 3312 : by kik : 20140120-->
                            <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font></td>
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
	<br>
	<fieldset>
	<legend>&nbsp;Order Detail&nbsp;</legend>

	  <table width="100%" align="center"> <!--Add align and change width by kik : 04-09-2013-->
            <?php
            if ($present_state == 0) {
                ?>
                            <tr>
                                <td>
                                    <table style="width:100%; margin:0px auto;">
                            <?php $this->load->view('element_filtergetDetail'); ?>
                                    </table>
                                </td>
                            </tr>

            <?php } ?>



		  <tr align="center" >
			<td align="center" id="showDataTable" >
				<table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
					<thead>
                            <?php
                            $str_header = "";
                            foreach ($show_column as $column) {
                                if ($column == "Inbound" ||
                                        $column == "Item_Id" ||
                                        $column == "Sub_Status_Code" ||
                                        $column == "Unit_Id" ||
                                        $column == "Price/Unit ID" ||
                                        $column == "DP_Type_Pallet" ) {
                                    //echo 'if ';
                                    $str_header .= "<th style='display:none;'>" . $column . "</th>";
                                } else {
                                    if ($column == "Delete") {
                                        if (!isset($flow_id)) {
                                            $str_header .= "<th   style='text-align:center;'>" . $column . "</th>";
                                        }
                                    } else if ($column == "Est. Balance Qty") {
                                        $str_header .= "<th  style='text-align:center;'>" . $column . "</th>";
                                    } else if ($column == "Adjust Qty") {
                                        $str_header .= "<th  style='text-align:center;'>" . $column . "</th>";
                                    } else {
                                        $str_header .= "<th>" . $column . "</th>";
                                    }
                                }
                            }
                            ?>
						<tr><?php echo $str_header; ?></tr>
					</thead>
                                        <tbody>
                            <?php
                            $sum_Receive_Qty = 0;   //add by kik : 29-10-2013
                            $sumPriceUnit = 0;      //add by kik : 14-01-2014 ราคารวมทั้งหมดต่อหน่วย
                            $sumPrice = 0;          //add by kik : 14-01-2014 ราคารวมทั้งหมด
                            if (isset($order_detail)) {
//							p($order_detail);
                                $str_body = "";
                                $i = 1;

                                foreach ($order_detail as $order_column) {
                                    //p($order_column);
                                    //$order_id = $order_column->Order_Id;
                                    $all_price = ($order_column->Reserv_Qty)*($order_column->Price_Per_Unit);
                                    $class_DP_Type_Palet = (!empty($order_column->DP_Type_Pallet) && $order_column->DP_Type_Pallet=="FULL")?"readonly":"";
                                    $str_body .= "<tr>";
                                    $str_body .= "<td>" . $i . "</td>";
                                    //add class td_click and ONCLICK for show Product Est. balance Detail modal : by kik : 06-11-2013
                                    $str_body .= "<td class='td_click' align='center'   ONCLICK='showProductEstInbound(\"{$order_column->Product_Code}\",{$order_column->Inbound_Item_Id})'>" . $order_column->Product_Code . "</td>";

//								$str_body .= "<td>".$order_column->Product_Code."</td>";
                                    //
                                    //  Edit By Akkarapol, 19/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้
                                    //  $str_body .= "<td style='text-align:left'>" . $order_column->Product_NameEN . "</td>";
//                                    $str_body .= "<td title='" . htmlspecialchars($order_column->Product_NameEN, ENT_QUOTES) . "' >" . $order_column->Product_NameEN . "</td>";
                                    $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Product_NameEN) . "\">" . $order_column->Product_NameEN . "</td>"; // Edit by Ton! 20140114
                                    // END Edit By Akkarapol, 19/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้

                                    $str_body .= "<td>" . $order_column->Product_Status . "</td>";
                                    $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>"; //add by kik : 04-098-2013
                                    $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                    //EDIT BY POR 2013-12-12 สลับที่ให้ mfd exp มาไว้หน้า location code
                                    $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                    //END EDIT
                                    $str_body .= "<td>" . $order_column->Location_Code . "</td>";
                                    $str_body .= "<td style='text-align:right'>" . set_number_format($order_column->Est_Balance_Qty) . "</td>";      // edit by kik : 11-10-2013
                                    $str_body .= "<td style='text-align:right' class='".$class_DP_Type_Palet."'>" . set_number_format($order_column->Reserv_Qty) . "</td>";           // edit by kik : 11-10-2013
                                    $str_body .= "<td>" . $order_column->Unit_Value . "</td>"; // Edit By Akkarapol, 10/01/2014, เปลี่ยนจากการเอา Unit_Id มาแสดงให้เป็นการใช้ Unit_Value แสดง
                                    $str_body .= "<td>" . set_number_format($order_column->Price_Per_Unit) . "</td>";
                                    $str_body .= "<td>" . $order_column->Unit_Price_value . "</td>";
                                    $str_body .= "<td>" . set_number_format($all_price) . "</td>";
                                    $str_body .= "<td  style=\"display:none;\">" . $order_column->Inbound_Item_Id . "</td>";
                                    $str_body .= "<td  style=\"display:none;\">" . $order_column->Sub_Status_Code . "</td>";
                                    $str_body .= "<td  style=\"display:none;\">" . $order_column->detail_item_id . "</td>";    // add by kik : 11-10-2013
                                    $str_body .= "<td  style=\"display:none;\">" . $order_column->Unit_Id . "</td>";    // add by kik : 11-10-2013
                                    $str_body .= "<td  style=\"display:none;\">" . $order_column->Unit_Price_Id . "</td>";    // add by kik : 11-10-2013
                                    $str_body .= "<td>" . @$order_column->Pallet_Code . "</td>";
                                    $str_body .= "<td  style=\"display:none;\">" . $order_column->DP_Type_Pallet . "</td>";    // add by kik : 11-10-2013
                                    if (!isset($flow_id)) {
                                        $str_body .= "<td><a ONCLICK=\"deleteItem(this)\" >" . img("css/images/icons/del.png") . "</a></td>";
                                    }
                                    $str_body .= "</tr>";
                                    $i++;
                                    $sum_Receive_Qty+=$order_column->Reserv_Qty;        //add by kik    :   29-10-2013
                                    $sumPriceUnit+=$order_column->Price_Per_Unit;       //add by kik    :   20140114
                                    $sumPrice+=$all_price;                              //add by kik    :   20140114
                                }
                                echo $str_body;
                            }
                            ?>
					</tbody>

                                          <!-- show total qty : by kik : 28-10-2013-->
                                        <tfoot>
                                           <tr>
                                                <th colspan='11' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                                <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Receive_Qty); ?></span></th>

                                                <?php if($price_per_unit):?>
                                                <th></th>
                                                <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                                <th></th>
                                                <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                                <?php endif;?>

                                                <?php if(!$price_per_unit || !isset($flow_id)):?>
                                                <th colspan='15' class ='ui-state-default indent' ></th>
                                                <?php endif;?>
                                                 <?php if($config_pallet==1 && isset($flow_id)):?>
                                                <th colspan='1' class ='ui-state-default indent' ></th>
                                                <?php endif;?>
                                            </tr>
                                        </tfoot>
                                        <!-- end show total qty : by kik : 28-10-2013-->


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
    <div class="modal-body"></div>
    <div class="modal-footer">
        <div style="float:left;">
        </div>
        <div style="float:right;">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
    </div>
</div>

<!--call element Product Est. balance Detail modal : add by kik : 06-11-2013-->
<?php $this->load->view('element_showEstBalance'); ?>

<?php $this->load->view('element_modal_message_alert'); ?>