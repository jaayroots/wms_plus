<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Putaway Pending'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = '#showProductTable'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/flow/flowPendingList"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';
    
    var flag = false;
    var form_name = "form_pending";
    var separator = "<?php echo SEPARATOR; ?>";
    var ci_prod_code = 1;
    var ci_prod_status_name = 3; //ADD BY POR 2013-12-17 เพิ่มคีย์ สำหรับไปแสดงใน PDF
    var ci_prod_sub_status_name = 4; //ADD BY POR 2013-12-17 เพิ่มคีย์ สำหรับไปแสดงใน PDF
    var ci_lot = 5;
    var ci_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_balance_qty = 9;
    var ci_confirm_qty = 10;
    var ci_unit_value = 11;
    
    //Add By por 2014-01-09 เพิ่มตัวแปร ราคาต่อหน่วย, หน่วย, ราคารวม
    var ci_price_per_unit = 12;
    var ci_unit_price = 13;
    var ci_all_price = 14;
    var statusprice = '<?php echo $statusprice;?>';
    //END Add
    
    var ci_sugest_loc_name=16; //Edit by por 2014-01-16 แก้ไขจากเลข 13 เป็น 16 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    
    //พบว่าคีย์ซ้ำกัน แต่มิได้เปลี่ยนเพราะเกรงว่าจะกระทบกับส่วนอื่น
    var ci_show_actual_location = 17; //Edit by por 2014-01-16 แก้ไขจากเลข 14 เป็น 17 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_actual_location_name = 17; //Edit by por 2014-01-16 แก้ไขจากเลข 14 เป็น 17 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    
    var ci_remark = 18; //Edit by por 2014-01-16 แก้ไขจากเลข 15 เป็น 18 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_prod_id = 19; //Edit by por 2014-01-16 แก้ไขจากเลข 16 เป็น 19 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_prod_status = 20; //Edit by por 2014-01-16 แก้ไขจากเลข 17 เป็น 20 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_prod_sub_status = 21; //Edit by por 2014-01-16 แก้ไขจากเลข 18 เป็น 21 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_unit_id = 22; //Edit by por 2014-01-16 แก้ไขจากเลข 19 เป็น 22 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_item_id = 23; //Edit by por 2014-01-16 แก้ไขจากเลข 20 เป็น 23 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_suggest_loc = 24; //Edit by por 2014-01-16 แก้ไขจากเลข 21 เป็น 24 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_actual_loc = 25; //Edit by por 2014-01-16 แก้ไขจากเลข 22 เป็น 25 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_old_loc = 26; //Edit by por 2014-01-16 แก้ไขจากเลข 23 เป็น 26 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_unit_price_id = 27;      //Edit by por 2014-01-17 เพิ่มคีย์ รหัส unit price
    //END

    var ci_list = [
        {name: 'ci_prod_id', value: ci_prod_id},
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_lot', value: ci_lot},
        {name: 'ci_serial', value: ci_serial},
        {name: 'ci_mfd', value: ci_mfd},
        {name: 'ci_exp', value: ci_exp},
        {name: 'ci_prod_status', value: ci_prod_status},
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_suggest_loc', value: ci_suggest_loc},
        {name: 'ci_actual_loc', value: ci_actual_loc},
        {name: 'ci_unit_id', value: ci_unit_id},
        {name: 'ci_unit_value', value: ci_unit_value},
        {name: 'ci_balance_qty', value: ci_balance_qty},
        {name: 'ci_confirm_qty', value: ci_confirm_qty},
        {name: 'ci_remark', value: ci_remark},
        {name: 'ci_old_loc', value: ci_old_loc},
        {name: 'ci_prod_status_name', value: ci_prod_status_name},
        {name: 'ci_prod_sub_status_name', value: ci_prod_sub_status_name},
        {name: 'ci_actual_location_name', value: ci_actual_location_name},   
        {name: 'ci_sugest_loc_name', value: ci_sugest_loc_name},
        //Add By por 2014-01-15 เพิ่มตัวแปร ราคาต่อหน่วย, หน่วย, ราคารวม
        {name: 'ci_price_per_unit', value: ci_price_per_unit},
        {name: 'ci_unit_price', value: ci_unit_price},
        {name: 'ci_all_price', value: ci_all_price},
        {name: 'ci_unit_price_id', value: ci_unit_price_id}
        //END Add
        
    ]

    $(document).ready(function() {
        // Add By Akkarapol, 18/12/2013, เพิ่ม $('#showProductTable tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย
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
        // END Add By Akkarapol, 18/12/2013, เพิ่ม $('#showProductTable tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย  



        // Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        // Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="assigned_id"]').change(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        initProductTable();
        $("#" + form_name + " :input").not("[name=showProductTable_length],[name=remark],[name=assigned_id]").attr("disabled", true);
        //Define Dialog Model 
        $("[name='is_urgent']").attr("disabled", false);

    });

    //Define Dialog Model 
    $('#myModal').modal('toggle').css({
        'width': function() {
            return ($(document).width() * .9) + 'px';   // make width 90% of screen
        },
        'margin-left': function() {
            return -($(this).width() / 2);				// center model
        }
    });


    function initProductTable() {
        var myDataTable = $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bStateSave": true,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "bScrollCollapse": false,
            "bPaginate": false,
            "sScrollX": "100%",
            "sScrollXInner": "100%",
            "sDom": '<"H"lfr>t<"F"ip>',
//            "aoColumnDefs": [
//				{"sWidth": "5%", "aTargets": [0]},
//				{"sWidth": "5%", "aTargets": [1]},
//				{"sWidth": "10%","aTargets": [2]},
//				{"sWidth": "5%", "aTargets": [3]},
//				{"sWidth": "5%", "aTargets": [4]},
//				{"sWidth": "5%", "aTargets": [5]},
//				{"sWidth": "5%", "aTargets": [6]},
//				{"sWidth": "5%", "aTargets": [7]},
//				{"sWidth": "5%", "aTargets": [8]},
//				{"sWidth": "5%", "aTargets": [9]},
//				{"sWidth": "5%", "aTargets": [10]},
//				{"sWidth": "5%", "aTargets": [11]},
//				{"sWidth": "5%", "aTargets": [12]},
//				{"sWidth": "5%", "aTargets": [13]},
//				{"sWidth": "5%", "sClass": "actual_loc_class", "aTargets": [14]},
//				{"sWidth": "5%", "aTargets": [15]}

            "aoColumnDefs": [
                {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
                {"sWidth": "12%", "sClass": "left_text", "aTargets": [2]},
                {"sWidth": "5%", "sClass": "left_text obj_status", "aTargets": [3]}, // Edit by Ton! 20131001
                {"sWidth": "5%", "sClass": "left_text obj_sub_status", "aTargets": [4]}, //Edit by Ton! 20131001
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [5]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [6]},
                {"sWidth": "5%", "sClass": "center obj_mfg", "aTargets": [7]},
                {"sWidth": "5%", "sClass": "center obj_exp", "aTargets": [8]},
                {"sWidth": "3%", "sClass": "right_text set_number_format", "aTargets": [9]},
                {"sWidth": "3%", "sClass": "right_text set_number_format", "aTargets": [10]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [11]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [12]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [13]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [14]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [15]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [16]},
                {"sWidth": "7%", "sClass": "center td_actual_location", "aTargets": [17]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [18]}
            ]
        }).makeEditable({
            sUpdateURL: function(value, settings) {
                return value;
            },
            "aoColumns": [
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
                        , {
                    sSortDataType: "dom-text",
                    sType: "numeric",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    cssclass: "required number",
                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
                        calculate_qty();
                    }
                }
                , null
                //ADD BY POR 2014-01-16 
                {priceperunit} //price/unit
                {unitofprice} //unit price
                , null //all price
                //END ADD
                        , null
                        , null
//                        , {
//                    loadurl: '<?php echo site_url() . "/pending/getSuggestLocRule"; ?>',
//                    loaddata: function(value, settings) {
//                        // AJAX Post Data
//                        var oTable = $('#showProductTable').dataTable( );
//                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
//                        var aData = oTable.fnGetData(rowIndex);
//                        var dataSet = {
//                            receive_type: $("#receive_type").val(),
//                            product_code: aData[ci_prod_code],
//                            product_status: aData[ci_prod_status],
//                            lot: aData[ci_lot],
//                            serial: aData[ci_serial],
//                            prod_mfd: "",
//                            prod_exp: ""
//                        };
//                        return dataSet;
//                    },
//                    loadtype: "POST",
//                    type: 'select',
//                    onblur: 'submit',
//                    sUpdateURL: function(value, settings) {
//                        console.log(settings);
//                        var oTable = $('#showProductTable').dataTable( );
//                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
//                        oTable.fnUpdate(value, rowIndex, ci_actual_loc);
//                        sNewCellDisplayValue = sNewCellDisplayValue + '<a ONCLICK="showProdInLoc(\'' + value + '\',\'' + sNewCellDisplayValue + '\')"><?php echo img("css/images/icons/view.png"); ?></a>';
//                        sNewCellValue = sNewCellDisplayValue;
//                        return value;
//                    }
//                }


                        // Add By Akkarapol, 01/10/2013, เปลี่ยน ActualLocation จาก DropdownList ไปเป็น Textbox
//                        , {
//                    sSortDataType: "dom-text",
//                    type: 'text',
//                    onblur: "submit",
//                    event: 'click',
//                    loadfirst: true, // Add By Akkarapol, 14/10/2013, เพิ่ม loadfirst เพื่อให้ช่อง Actual Location นี้มี textbox ขึ้นมาตั้งแต่โหลดหน้า
//
//                }
                        // END Add By Akkarapol, 01/10/2013, เปลี่ยน ActualLocation จาก DropdownList ไปเป็น Textbox

                        , null

                        , {
                    onblur: 'submit',
                    type: 'text',
                    event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว 
                }
                , null
            ]
        });

        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_id, false);
        //$('#showProductTable').dataTable().fnSetColumnVis(ci_unit_value, false); // Add By Akkarapol, 10/01/2014, เพิ่ม fnSetColumnVis(ci_unit_value, false); เพื่อให้ส่งค่า index ของ unit name ไปใช้ต่อในการ ทำ PDF
        $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_suggest_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_actual_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_old_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);
        
        //ADD BY POR 2014-01-13 เพิ่มเติมกรณีไม่ได้กำหนดให้แสดงราคา ก็ให้ซ่อน 3 รายการนี้ไว้
        if(statusprice!=true){
           $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
           $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
           $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
        }
        //END ADD
//        $('#showProductTable tbody tr[title]').tooltip({
//            "delay": 0,
//            "track": true,
//            "fade": 250,
//            "placement": 'top',
//        });
        new FixedColumns(myDataTable, {
            "iLeftColumns": 0,
            "sLeftWidth": 'relative',
            "iLeftWidth": 0
        });

    }

    function postRequestAction(module, sub_module, action_value, next_state, elm) {
	global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');
	        
        $("input[name='prod_list[]']").remove();
        var statusisValidateForm = validateForm();
        if (statusisValidateForm === true) {

// Add BY Akkarapol, 13/12/2013, เพิ่มการวน loop หาตัว input ที่เป็น class 'acturl_location' แล้วทำการส่งค่า ajax ไปหา location_id และ location_code เพื่อนำไปทำการ DataTable.fnUpdate ให้เซ็ตค่าเข้าไปแล้วทำงานในขั้นตอนต่อไป
            $('.actual_location').each(function() {
                var input = $(this).val();
                var parent_selector = $(this).parent('td');
                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url('/location/autoCompleteActualLocation'); ?>",
                    dara: {criteria: input},
                    success: function(data) {
                        data = data['0'];
                        var oTable = $('#showProductTable').dataTable(); // get datatables    
                        var indexOfRow = oTable.fnGetPosition($(parent_selector).closest('tr').get(0)); // find rows index	
                        oTable.fnUpdate(data.location_code, indexOfRow, ci_show_actual_location); // update new data to datatables
                        oTable.fnUpdate(data.location_id, indexOfRow, ci_actual_loc); // update new data to datatables
                    },
                    dataType: 'json',
                    async: false
                });
            });
// END Add BY Akkarapol, 13/12/2013, เพิ่มการวน loop หาตัว input ที่เป็น class 'acturl_location' แล้วทำการส่งค่า ajax ไปหา location_id และ location_code เพื่อนำไปทำการ DataTable.fnUpdate ให้เซ็ตค่าเข้าไปแล้วทำงานในขั้นตอนต่อไป


            var rowData = $('#showProductTable').dataTable().fnGetData();
//            console.log(rowData);
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Order Detail");
                return false;
            }

            for (i in rowData) {
                //var balance_qty = parseInt(rowData[i][ci_balance_qty]); //COMMENT BY POR 2013-12-16 แก้ไขให้เป็น float และเอา comma ออก เพื่อให้ได้ตัวเลขที่สามารถนำมาเปรียบเทียบกันได้
                //var confirm_qty = parseInt(rowData[i][ci_confirm_qty]); //COMMENT BY POR 2013-12-16 แก้ไขให้เป็น float และเอา comma ออก เพื่อให้ได้ตัวเลขที่สามารถนำมาเปรียบเทียบกันได้

                var balance_qty = parseFloat(rowData[i][ci_balance_qty].replace(/\,/g, '')); //Edit BY POR 2013-12-16 แก้ไขให้เป็น float และเอา comma ออก เพื่อให้ได้ตัวเลขที่สามารถนำมาเปรียบเทียบกันได้
                var confirm_qty = parseFloat(rowData[i][ci_confirm_qty].replace(/\,/g, '')); //Edit BY POR 2013-12-16 แก้ไขให้เป็น float และเอา comma ออก เพื่อให้ได้ตัวเลขที่สามารถนำมาเปรียบเทียบกันได้

                if (balance_qty != confirm_qty) {
                    alert('Receive Qty Must be Equal Confirm Qty');
                    return false;
                }

//#Defect ID : 339 #1
//#DATE:2013-08-21
//#BY:Ak
//-- START--
                var chk_suggest_loc = parseInt(rowData[i][ci_suggest_loc]);
                var chk_actual_loc = rowData[i][ci_actual_loc];
                var chk_remark = rowData[i][ci_remark];
                if (chk_actual_loc != "") {
                    if (chk_suggest_loc != chk_actual_loc) {
                        if (chk_remark == "") {
                            alert('Please Check Your Information Remark');

                            // Add By Akkarapol, 11/11/2013, append textbox เข้าไปใน column 'remark' เพื่อให้เค้ากรอกข้อมูล
                            var oTable = $('#showProductTable').dataTable(); // get datatables 
                            var cTable = oTable.fnGetNodes();
                            $(cTable[i]).find("td:eq("+ci_remark+")").html('');
                            var input = $('<input>').attr('class', 'txt_remark required').css('width', '95%').appendTo($(cTable[i]).find("td:eq("+ci_remark+")"));
                            // END Add By Akkarapol, 11/11/2013, append textbox เข้าไปใน column 'remark' เพื่อให้เค้ากรอกข้อมูล

                            return false;
                        }
                    }
                } else {
                    alert('Please Check Actual Location');
                    return false;
                }
//-- END #Defect ID : 339 #1             

            }


//            if (confirm("Are you sure to action " + action_value + "?")) {
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

                var oTable = $('#showProductTable').dataTable().fnGetData();
                for (i in oTable) {
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

                global_data_form = $("#" + form_name).serialize();
                var message = "";
                
//                if(global_sub_module != 'rejectAction' && global_sub_module !='rejectAndReturnAction'){
//                    validation_data();
//                }else{
                    var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
                    $('#div_for_alert_message').html(mess);
                    $('#div_for_modal_message').modal('show').css({
                        'margin-left': function() {
                            return ($(window).width() - $(this).width()) / 2;
                        }
                    });
//                }
                
//                var step_name = "Relocate Product";
//                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
//                    switch (data.status) {
//                        case 'C001':
//                            message = "Save " + step_name + " Complete";
//                            break;
//                        case 'C002':
//                            message = "Confirm " + step_name + " Complete";
//                            break;
//                        case 'C003':
//                            message = "Approve " + step_name + " Complete";
//                            break;
//                        case 'E001':
//                            message = "Save " + step_name + " Incomplete";
//                            break;
//                    }
//                    alert(message);
//                    url = "<?php echo site_url(); ?>/flow/flowPendingList";
//                    redirect(url);
//                }, "json");
//            }
        }
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowPendingList";
            redirect(url);
        }
    }

    function validateForm() {
        if ($('#assigned_id')[0].selectedIndex == 0)
        {
            return false;
        }
        else {
            return true;
        }
    }

    function showProdInLoc(location_id, location_code) {
        var dataSet = {location_id: location_id, location_code: location_code}
        $.post('<?php echo site_url() . "/reLocation/showProductInLocation" ?>', dataSet, function(data) {
            $(".modal-body").html(data);
            var oTable = $('#defDataTable').dataTable({
                "bJQueryUI": true,
                "bAutoWidth": false,
                "bSort": false,
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
                    {"sWidth": "7%", "sClass": "center", "aTargets": [6]},
                    {"sWidth": "7%", "sClass": "left_text", "aTargets": [7]},
                    {"sWidth": "7%", "sClass": "left_text", "aTargets": [8]},
                    {"sWidth": "7%", "sClass": "center", "aTargets": [9]},
                    {"sWidth": "7%", "sClass": "center", "aTargets": [10]},
                    {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [11]}
                ]
            });
        }, "html");
        $('#myModal').modal('toggle');
    }

// Comment By Akkarapol, 11/11/2013, เปลี่ยนการเรียกใช้ AutoComplete เป็นอีกแบบ เนื่องจากอันนี้ใช้งานได้ไม่ตรงจุด  (แก้ defect : 453)

//    $(document).ready(function() {
//
//
//        function selectBind(elm) {
//
//            $('.sel_actual_location').click(function() {
//                var location_id = $(this).data('location_id');
//                $(elm).text($(this).data('location_code'));
//                var datatables = $('#showProductTable').dataTable();
//                var aPos = datatables.fnGetPosition(elm);
//                var rowData = datatables.fnGetData();
//                rowData[aPos[0]][ci_actual_loc] = location_id;
////                        console.log(rowData[aPos[0]][ci_actual_loc]);
//            });
//        }
//
//        $('.actual_loc_class').keyup(function() {
//            var _this = this;
//            var tr_data_no = $(this).parent('tr').children('td')[0].innerHTML;
//
//            $(this).append('<div class="suggestionsBox" id="suggestions-' + tr_data_no + '" uu="' + tr_data_no + '" style="display:none;"> <div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div> </div>');
//            var value = $(this).find('form input').val();
//            var minChar = 1;
//            if (value.length >= minChar) {
//                if (value != '') {
//                    $.post("<?php echo site_url(); ?>/location/autoCompleteActualLocation", {tr_data_no: tr_data_no, text_search: value}, function(val, data) {
//                        if (data.length > 0) {
//                            var response = $.parseJSON(val);
//                            $('#suggestions-' + tr_data_no).show();
//                            $('.suggestionList').html(' '); // Add By Akkarapol, 09/10/2013, เคลียร์ค่าของ DIV $('.suggestionList') ให้เป็นค่าว่าง เพื่อรอรับการ append ต่อไป
//                            //$('#autoSuggestionsList').html(val, data);
//                            $.each(response, function(idx, value) {
//                                var li = $('<li>').attr('class', 'sel_actual_location').data('location_id', value.location_id).data('location_code', value.location_code).html(value.location_code);
//                                $('#autoSuggestionsList').append(li);
//                            });
//                            selectBind(_this);
//                        }
//                    });
//                }
//            }
//
//
//        });
//    });
// END Comment By Akkarapol, 11/11/2013, เปลี่ยนการเรียกใช้ AutoComplete เป็นอีกแบบ เนื่องจากอันนี้ใช้งานได้ไม่ตรงจุด (แก้ defect : 453)


    function hideSuggestions() {
        setTimeout("$('#suggestions').hide();", 100);
    }

    // Add function calculate_qty : by kik : 24-10-2013 
    function calculate_qty() {
        var rowData = $('#showProductTable').dataTable().fnGetData();
        var rowData2 = $('#showProductTable').dataTable();
        var num_row = rowData.length;
        var sum_cf_qty = 0;
        var sum_price = 0; //ADD BY POR 2014-01-16 ราคาทั้งหมดต่อหน่วย
        var sum_all_price = 0; //ADD BY POR 2014-0116 ราคาทั้งหมด
        for (i in rowData) {
            var tmp_qty = 0;

            //+++++ADD BY POR 2013-11-29 แปลงตัวเลขให้อยู่ในรูปแบบคำนวณได้
            var str = rowData[i][ci_confirm_qty];
            rowData[i][ci_confirm_qty] = str.replace(/\,/g, '');

            tmp_qty = parseFloat(rowData[i][ci_confirm_qty]); //+++++ADD BY POR 2013-11-29 แก้ไขให้เป็น parseFloat เนื่องจาก qty เปลี่ยนเป็น float

            if (!($.isNumeric(tmp_qty))) {
                tmp_qty = 0;
            }
            
            //+++++ADD BY POR 2014-01-16 เพิ่มการคำนวณราคา
            var tmp_price = 0;//ราคาต่อหน่วย
            var all_price = 0; //ราค่าทั้งหมดต่อหนึ่งรายการ

            var str2 = rowData[i][ci_price_per_unit]; //ราคาต่อหน่วย
            rowData[i][ci_price_per_unit] = str2.replace(/\,/g, '');
            tmp_price = parseFloat(rowData[i][ci_price_per_unit]);
            if (!($.isNumeric(tmp_price))){
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

        $('#sum_cf_qty').html(set_number_format(sum_cf_qty));
        $('#sum_price_unit').html(set_number_format(sum_price)); 
        $('#sum_all_price').html(set_number_format(sum_all_price));

    }
    // end function calculate_qty : by kik : 24-10-2013 


    // Add By Akkarapol, 11/11/2013, เพิ่ม Script ในส่วนของการทำ AutoComplete ที่ Column 'Actual Location'
    $(document).ready(function() {
//alert('lol');
        function bindDataTD() {
            $('.td_actual_location').click(function() {
//        console.log('x');
//                var input = $('<input>').attr('class', 'actual_location').appendTo($(this));
//                var input = $('<input>').attr('width', '100').appendTo($(this));
//                $('.actual_location').css('width','30px');
            });
        }

// Suggestion
        suggestLocation();
        bindDataTD();
// Add By Akkarapol, 08/10/2013, เพิ่ม script เมื่อตอน click ที่ list ของ location จาก div ที่ได้เตรียมไว้ ว่าให้นำค่าเข้าไปที่ td และอัพเดทเข้าที่ dataTable ด้วย พร้อมทั้ง hide div ตัวนั้นและซ่อน input ออกไปพร้อมกับเซ็ท label ของ td ให้เป็น location Code
        function suggesLocationClick(selector, parent_selector)
        {
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
        function suggestLocation()
        {
            $('.actual_location').on('keyup focus', function(e)
            {
//                console.log($(this));
                var _this = $(this);
                var _parent = $(this).parent('td');
                $('#suggestion_div').css({top: ($(this).offset().top + 30), left: $(this).offset().left}).show();
                var data = $(this).parent('td').data();
                $.post("<?php echo site_url('/reLocation/autoCompleteActualLocation'); ?>", {criteria: $(this).val()}, function(response)
                {
                    $('#suggestionLocationList').html(''); // clear old list 
//                    console.log(response);
                    $.each(response, function(index, value)
                    {
//                        console.log(value);
                        var lists = $('<li>').attr('class', 'actual_location_id').attr('data-id', value.location_id).html(value.location_code);

                        $('#suggestionLocationList').append(lists);
                    });
                    suggesLocationClick(_this, _parent);
                });
//                $('.actual_location').focus();
            }).blur(function() {
                var _this = this;
                var _parent = $(this).parent('td');

                setTimeout(function() {
                    //ADD BY POR 2013-12-13 เพิ่มให้ตรวจสอบ location ว่ามีจริงหรือไม่ (ล้อมาจากเมนู Relocation(locoation))
                    $.get("<?php echo site_url(); ?>" + "/location/check_exist_location", {location_code: $(_this).val()}, function(data)
                    {
                        if (data == "null")
                        {
                            alert('Sorry!!, This actual location not found, Please select another location.');
                            $(_this).val('');
                        } else {
                            //ADD BY POR 2013-12-16 เพิ่มให้ update ค่าใน datatable ด้วยกรณีมีการคีย์ข้อมูล
                            flag = false;
                            $(_this).attr('value', $(_this).val()).removeClass('required');
                            var input = $(_this).val();
                            var oTable = $('#showProductTable').dataTable(); // get datatables                           
                            var indexOfRow = oTable.fnGetPosition($(_parent).closest('tr').get(0)); // find rows index	
                            oTable.fnUpdate(input, indexOfRow, ci_show_actual_location); // update new data to datatables
                            oTable.fnUpdate(data, indexOfRow, ci_actual_loc); // update new data to datatables  
                            //END ADD 
                        }
                    });
                    //END ADD
                    $('#suggestion_div').hide();
                }, 200);
            });
        }
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

        initProductTable();
        // Add By Akkarapol, 08/10/2013, เพิ่ม script ที่จะทำงานตอน คลิกที่ td ที่อยู่ใน column ของ Suggest Location โดยจะ append textbox ขึ้นมาเพื่อใช้สำหรับรับค่าเข้าฟังก์ชั่น autoComplete ต่อไป

        $('td.td_actual_location').click(function() {

            if (!flag)
            {
                if ($(this).text().length == 0)
                {
                    var input = $('<input>').attr('class', 'actual_location').css('width', '95%').appendTo($(this));
                    //$(this).html(input);
                    suggestLocation();
                    input.focus();
                }
                else
                {
                    var temp = $(this).text();
                    $(this).html('');
                    var input = $('<input>').attr({'class': 'actual_location', 'data-val': temp}).prop('value', temp).css('width', '95%').appendTo($(this));
                    suggestLocation();
//                    input.attr('data-val',temp);                    
                    input.focus();
                }
                flag = true;

            }

        });
        // END Add By Akkarapol, 08/10/2013, เพิ่ม script ที่จะทำงานตอน คลิกที่ td ที่อยู่ใน column ของ Suggest Location โดยจะ append textbox ขึ้นมาเพื่อใช้สำหรับรับค่าเข้าฟังก์ชั่น autoComplete ต่อไป
        $("#" + form_name + " :input").not("[name=showProductTable_length],[name=remark],[name=assigned_id] ").attr("disabled", true);
        $("[name='is_urgent']").attr("disabled", false);
        //Define Dialog Model 

    });
    // END Add By Akkarapol, 11/11/2013, เพิ่ม Script ในส่วนของการทำ AutoComplete ที่ Column 'Actual Location'


// Add By Akkarapol, 11/11/2013, เพิ่มฟังก์ชั่นสำหรับการ Print Pending Job
    function exportFile(file_type) {
        if (file_type == 'PDF') {
            //ADD BY POR 2013-12-18  เพิ่มให้ส่งค่าไปแบบ realtime
            var backupForm = document.getElementById('form_flow_id').innerHTML;
            var f = document.getElementById('form_flow_id');

            //นำค่าที่อยู่ใน datatable มาแทนค่าในตัวแปร prod_list
            var oTable = $('#showProductTable').dataTable().fnGetData();
            for (i in oTable) {
                var prod_data = oTable[i].join(separator);
                var prodItem = document.createElement("input");
                prodItem.setAttribute('type', "hidden");
                prodItem.setAttribute('name', "prod_list[]");
                prodItem.setAttribute('value', prod_data);
                f.appendChild(prodItem);
            }
            
            //worker name เนื่องจากสามารถเปลี่ยนแปลงได้
            var worker_id = document.createElement("input");
            worker_id.setAttribute('type', "hidden");
            worker_id.setAttribute('name', "Assigned_Id");
            worker_id.setAttribute('value', $('[name="assigned_id"]').val());
            f.appendChild(worker_id);

            //นำคีย์ต่างๆ ที่จำเป็นต้องใช้แทนเข้าไปในตัวแปร
            $.each(ci_list, function(i, obj) {
                var ci_item = document.createElement("input");
                ci_item.setAttribute('type', "hidden");
                ci_item.setAttribute('name', obj.name);
                ci_item.setAttribute('value', obj.value);
                f.appendChild(ci_item);
            });
            //END ADD
            $("#form_flow_id").attr('action', "<?php echo site_url(); ?>" + "/report/exportPendingPDF")
        }
        $("#form_flow_id").submit();
        document.getElementById("form_flow_id").innerHTML = backupForm;
    }
    // END Add By Akkarapol, 11/11/2013, เพิ่มฟังก์ชั่นสำหรับการ Print Pending Job



</script>
<style>
    /*    #myModal {
            width: 900px;	 SET THE WIDTH OF THE MODAL 
            margin: -250px 0 0 -450px;  CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) 
        }*/


    #myModal{
        width: 1170px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -600px; 
        /*z-index:9999*/
    }

    /*Add by Akkarapol, 01/10/2013, เธ—เธณ autocomplete เน�เธ�เธชเน�เธงเธ�เธ�เธญเธ� Product Code เน�เธฅเธฐเน€เธกเธทเน�เธญเธ�เธฅเธดเธ�เธ—เธตเน� list product เธ”เธฑเธ�เธ�เธฅเน�เธฒเธง เธ�เน�เธ�เธฐเน�เธซเน� auto input to datatable*/

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

    .tooltip{
        width: 200px !important;
        left: 180px !important;
    }

    #suggestion_div {
        width: 220px;
        height: 170px;
        display: none;
        z-index: 2000;
        position: absolute;
        background-color: #FFF;
        overflow-y:hidden;
    } 


</style>
<div class="well">
    <!--Add By Akkarapol, 11/11/2013, เพิ่ม form id='form_flow_id' สำหรับส่งค่า flow_id ไปทำ PDF--> 
    <form id='form_flow_id' method='post' action="" target="_blank" >
        <input name='flow_id' type='hidden' value='<?php echo $flow_id; ?>'>
        <input name='showfooter' type='hidden' value='show'>
    </form>
    <!-- END Add By Akkarapol, 11/11/2013, เพิ่ม form id='form_flow_id' สำหรับส่งค่า flow_id ไปทำ PDF--> 
    <form id="form_pending" method=post action="" >
        <fieldset class="well">
            <legend>&nbsp;Order&nbsp;</legend>
            <?php
            if (isset($flow_id)) {
                echo form_hidden('flow_id', $flow_id);
            }
            if (isset($order_id)) {
                echo form_hidden('order_id', $order_id);
            }
            if (!isset($owner_id)) {
                $owner_id = "";
            }
            if (!isset($renter_id)) {
                $renter_id = "";
            }
            if (!isset($shipper_id)) {
                $shipper_id = "";
            }
            if (!isset($consignee_id)) {
                $consignee_id = "";
            }
            if (!isset($receive_type)) {
                $receive_type = "";
            }
            if (!isset($est_receive_date)) {
                $est_receive_date = "";
            }
            if (!isset($remark)) {
                $remark = "";
            }
            if (!isset($process_type)) {
                $process_type = $data_form['process_type'];
            }
            if (!isset($document_no)) {
                $document_no = "";
            }
            if (!isset($doc_refer_int)) {
                $doc_refer_int = "";
            }
            if (!isset($doc_refer_ext)) {
                $doc_refer_ext = "";
            }
            if (!isset($doc_refer_inv)) {
                $doc_refer_inv = "";
            }
            if (!isset($doc_refer_ce)) {
                $doc_refer_ce = "";
            }
            if (!isset($doc_refer_bl)) {
                $doc_refer_bl = "";
            }
            if (!isset($receive_date)) {
                $receive_date = "";
            }

            if (!isset($vendor_id)) {
                $vendor_id = "";
            }
            if (!isset($driver_name)) {
                $driver_name = "";
            }
            if (!isset($car_no)) {
                $car_no = "";
            }

            if ((!isset($is_pending)) || ($is_pending != 'Y')) {
                $is_pending = FALSE;
            } else {
                $is_pending = TRUE;
            }
            
            if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
                $is_urgent = false;
            } else {
                $is_urgent = true;
            }
            ?>
            <?php echo form_hidden('process_id', $process_id); ?>
            <?php echo form_hidden('present_state', $present_state); ?>
            <?php echo form_hidden('user_id', $user_id); ?>
            <?php echo form_hidden('process_type', $process_type); ?>
            <?php echo form_hidden('owner_id', $owner_id); ?>
            <table width="98%">
                <tr>
                    <td align="right">Renter</td>
                    <td align="left"><?php echo form_dropdown('renter_id', $renter_list, $renter_id, 'class="required"'); ?></td>
                    <td align="right">Shipper</td>
                    <td align="left"><?php echo form_dropdown('shipper_id', $shipper_list, $shipper_id, 'class="required" '); ?></td>
                    <td align="right">Consignee</td>
                    <td align="left"><?php echo form_dropdown('consignee_id', $consignee_list, $consignee_id, 'class="required"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Document No.</td>
                    <td align="left"><?php echo form_input('document_no', $document_no, 'placeholder="Auto Generate GRN" disabled class="required document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">External Document</td>
                    <td align="left"><?php echo form_input('doc_refer_ext', trim($doc_refer_ext), 'placeholder="' . DOCUMENT_EXT . '" class="required document " style="text-transform: uppercase"'); ?></td>
                    <td align="right">Internal Document</td>
                    <td align="left"><?php echo form_input('doc_refer_int', trim($doc_refer_int), 'placeholder="' . DOCUMENT_INT . '"  class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Invoice No.</td>
                    <td align="left"><?php echo form_input('doc_refer_inv', trim($doc_refer_inv), 'placeholder="' . DOCUMENT_INV . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Customs Entry	</td>
                    <td align="left"><?php echo form_input('doc_refer_ce', trim($doc_refer_ce), 'placeholder="' . DOCUMENT_CE . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">BL No.</td>
                    <td align="left"><?php echo form_input('doc_refer_bl', trim($doc_refer_bl), 'placeholder="' . DOCUMENT_BL . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Receive Type</td>
                    <td align="left"><?php echo form_dropdown('receive_type', $receive_list, $receive_type, "onChange=changeOption(this)"); ?></td>
                    <td align="right">Receive Date.</td>
                    <td align="left"><?php echo form_input('receive_date', date("d/m/Y"), 'id="receive_date" placeholder="Receive Date" readonly="readyonly"'); ?></td>
                    <td align="right">Worker Name</td>
                    <td align="left"><?php echo form_dropdown('assigned_id', $assign_list, $assigned_id, " id='assigned_id' class='required' "); ?></td>

                </tr>
                <tr style='display:none'>
                    <td align="right">Vendor</td>
                    <td align="left"><?php echo form_dropdown('vendor_id', $vendor_list, $vendor_id, ''); ?></td>
                    <td align="right">Driver Name</td>
                    <td align="left"><?php echo form_input('driver_name', trim($driver_name), 'placeholder="' . DRIVER_NAME . '" '); ?></td>
                    <td align="right">Car No.</td>
                    <td align="left"><?php echo form_input('car_no', trim($car_no), 'placeholder="' . CAR_NO . '" '); ?></td>
                </tr>
                <tr valign="center">
                    <td align="right">Remark</td>
                   
                    <td align="left" colspan="3">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo trim($remark); ?></TEXTAREA>
		    </td>
                        <td align="right"></td>
                        <td>
                            <!--//add for ISSUE 3312 : by kik : 20140120-->
                            <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                        </td>
			
			<td align="left" style='display:none'>
                        <?php echo form_checkbox('is_pending', ACTIVE, false); ?>&nbsp;Pending
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php echo form_checkbox('is_repackage', ACTIVE, false); ?>&nbsp;Re-Package</td>
		  </tr>
	  </table>
	</fieldset>
	</form>
	<fieldset class="well">
	<legend>&nbsp;Order Detail&nbsp;</legend>
	  <table width="100%">
		  <tr align="center" >
			<td align="center" colspan="6" id="showDataTable" >
				<table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
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
                                _lang('receive_qty'),
                                _lang('confirm_qty'),
                                _lang('unit'),
                                _lang('price_per_unit'),
                                _lang('unit_price'),
                                _lang('all_price'),
                                _lang('from_location'),
                                _lang('suggest_location'),
                                _lang('actual_location'),
                                _lang('remark'),
                                "Product_Id",
                                "Product_Status",
                                "Product_Sub_Status",
                                "Unit_Id",
                                "Item_Id",
                                "Suggest_Location_Id",
                                "Actual_Location_Id",
                                "Old_Location_Id",
                                "Unit Price ID"
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
                            $sum_Receive_Qty = 0;   //add by kik : 25-10-2013   
                            $sum_Cf_Qty = 0;        //add by kik : 25-10-2013
                            $sumPriceUnit = 0; //ADD BY POR 2014-01-16 ราคารวมทั้งหมดต่อหน่วย
                            $sumPrice = 0; //ADD BY POR 2014-01-16 ราคารวมทั้งหมด
                            if (isset($pending_detail)) {
                                $str_body = "";
                                $count_rows = 1;

                                foreach ($pending_detail as $order_column) {
//                                    $str_body .= '<tr title="' . $order_column->Full_Product_Name . '">';
//                                    $str_body .= "<tr title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">"; // Edit by Ton! 20131127

                                    $str_body .= "<tr>"; // Edit By Akkarapol, 18/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้

                                    $str_body .= "<td>" . $count_rows . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Code . "</td>";

                                    // Edit By Akkarapol, 18/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้
                                    // comment By Akkarapol , 04/09/2013, การใช้ quote แบบนี้จะทำให้การแสดงผลที่มี ' มีปัญหา ทำให้ตัว Tooltip แสดงชื่อ Product ไม่สมบูรณ์
                                    // $str_body .= "<td title='" . $order_column->Full_Product_Name . "' >" . $order_column->Product_Name . "</td>";
                                    // END comment By Akkarapol , 04/09/2013, การใช้ quote แบบนี้จะทำให้การแสดงผลที่มี ' มีปัญหา ทำให้ตัว Tooltip แสดงชื่อ Product ไม่สมบูรณ์                              
                                    // Add By Akkarapol , 04/09/2013, เปลี่ยนรูปแบบการใช้ quote ให้สามารถแสดงผลในตัว Tooltip ได้อย่างสมบูรณ์
                                    //                                    $str_body .= '<td  >' . $order_column->Product_Name . '</td>';
                                    // END Add By Akkarapol , 04/09/2013, เปลี่ยนรูปแบบการใช้ quote ให้สามารถแสดงผลในตัว Tooltip ได้อย่างสมบูรณ์
//                                    $str_body .= "<td title='" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "' >" . $order_column->Product_Name . "</td>";
                                    $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                    // END Edit By Akkarapol, 18/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้

                                    $str_body .= "<td>" . $order_column->Product_Status . "</td>";
                                    #Comment : 2013-08-30
                                    #By: POR
                                    #--Start
                                    //$str_body .= "<td>".$order_column->Product_Sub_Status."</td>";
                                    #--End
                                    #2013-08-30
                                    #By: POR
                                    #--Show Name sub status
                                    $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                                    #--End
                                    $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                    $str_body .= "<td>" . set_number_format($order_column->Reserv_Qty) . "</td>";
                                    $str_body .= "<td>" . set_number_format($order_column->Confirm_Qty) . "</td>";
                                    $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                    //ADD BY POR 2014-01-15 เพิ่ม column เกี่ยวกับ price
                                    $str_body .= "<td>" . set_number_format($order_column->Price_Per_Unit) . "</td>";
                                    $str_body .= "<td>" . $order_column->unitprice_name . "</td>";
                                    $str_body .= "<td>" . set_number_format($order_column->All_Price) . "</td>";
                                    //END ADD
                                    if ($order_column->Old_Location == "") {
                                        $order_column->Old_Location = $order_column->Actual_Location;
                                    }
                                    $icon_old_loc = '<a ONCLICK="showProdInLoc(\'' . $order_column->Old_Location_Id . '\',\'' . $order_column->Old_Location . '\')">' . img("css/images/icons/view.png") . '</a>';
                                    $icon_suggest_loc = '<a ONCLICK="showProdInLoc(\'' . $order_column->Suggest_Location_Id . '\',\'' . $order_column->Suggest_Location . '\')">' . img("css/images/icons/view.png") . '</a>';

                                    $str_body .= "<td>" . $order_column->Old_Location . " " . $icon_old_loc . "</td>";
                                    $str_body .= "<td>" . $order_column->Suggest_Location . " " . $icon_suggest_loc . "</td>";
                                    $str_body .= "<td>" . $order_column->Actual_Location . "</td>";
                                    $str_body .= "<td>" . $order_column->Remark . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Status_Code . "</td>";
                                    $str_body .= "<td>" . $order_column->Sub_Status_Code . "</td>";
                                    $str_body .= "<td>" . $order_column->Unit_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Item_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Suggest_Location_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Actual_Location_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Old_Location_Id . "</td>";
                                    $str_body .= "<td style=\"display:none\">" . $order_column->Unit_Price_Id . "</td>"; //ADD BY POR 2014-01-17 เพิ่ม ID ของ unit price
                                    $str_body .= "</tr>";
                                    $count_rows++;
                                    $sum_Receive_Qty+=$order_column->Reserv_Qty;        //add by kik    :   25-10-2013
                                    $sum_Cf_Qty+=$order_column->Confirm_Qty;            //add by kik    :   25-10-2013
                                    //ADD BY POR 2014-01-13 รวมราคาต่อหน่วยและราคารวม
                                    $sumPriceUnit+=$order_column->Price_Per_Unit;
                                    $sumPrice+=$order_column->All_Price;
                                    //END ADD
                                }
                                echo $str_body;
                            }
                            ?>
                                    
                                   </tbody>
                                        
                                    <!-- show total qty : by kik : 25-10-2013-->
                                    <tfoot>
                                       <tr>
                                             <th colspan='9' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                             <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Receive_Qty); ?></span></th>
                                             <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Cf_Qty); ?></span></th>
                                             <th></th>
                                            <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                           <th></th>
                                            <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                             <th colspan='11' class ='ui-state-default indent' ></th>
                                        </tr> 
                                    </tfoot>
                                    <!-- end show total qty : by kik : 25-10-2013-->       
                                   
                        
                        
				</table>
			</td>
		  </tr>
	  </table>
	</fieldset>
</div>
<!-- Modal -->
<div style="min-height:500px;padding:5px 10px;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">List Product In Location</h3>
        <input  value="" type="hidden" name="prdModalval" id="prdModalval" >
    </div>
    <div class="modal-body"><!-- working area--></div>
    <div class="modal-footer"></div>
</div>


<!--Add By Akkarapol, 11/11/2013, เพิ่ม div ขึ้นมาเพื่อรองรับ autoComplete ที่จะ append list เข้ามาแสดงที่นี่-->
<div id="suggestion_div">
	<div class="suggestionList" id="suggestionLocationList"></div>
</div>
<!--END Add By Akkarapol, 11/11/2013, เพิ่ม div ขึ้นมาเพื่อรองรับ autoComplete ที่จะ append list เข้ามาแสดงที่นี่-->

<?php $this->load->view('element_modal_message_alert'); ?>