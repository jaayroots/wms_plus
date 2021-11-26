<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<style>
    .disPlayNone{
        display: none;
    }
</style>
<script>
    var oTable = null;
    var statusprice = "<?php echo $price_per_unit; ?>"; //add by kik : 20140115
    var separator = "<?php echo SEPARATOR; ?>"; // Add By Akkarapol, 21/01/2013, Add Separator for use in Page
    
    // add var for validation data
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Transfer Stock Order'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = '#ShowDataTableForInsert'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/transferStock"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
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
    var ci_reserv_qty = 10;
    var ci_remark = 14;
    var ci_location_code = 15;
    var ci_product_sub_status = 17;
    var ci_unit_Id = 18;
    var ci_inbound_id = 19;
    
    // add by kik : 2014-01-14
    var ci_price_per_unit = 11;
    var ci_unit_price = 12;
    var ci_all_price = 13;
    var ci_unit_price_id = 20;
    //end add by kik : 2014-01-14
    var ci_item_id = 21;        //add for edit No. to running number : by kik : 20140226
    
    var ci_list = [
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_product_status', value: ci_product_status},
        {name: 'ci_product_lot', value: ci_product_lot},
        {name: 'ci_product_serial', value: ci_product_serial},
        {name: 'ci_mfd', value: ci_mfd},
        {name: 'ci_exp', value: ci_exp},
        {name: 'ci_reserv_qty', value: ci_reserv_qty},     
        {name: 'ci_remark', value: ci_remark},            
        {name: 'ci_location_code', value: ci_location_code},              
        {name: 'ci_price_per_unit', value: ci_price_per_unit},      // add by kik : 2014-01-15
        {name: 'ci_unit_price', value: ci_unit_price},              // add by kik : 2014-01-15
        {name: 'ci_all_price', value: ci_all_price},                // add by kik : 2014-01-15
        {name: 'ci_product_sub_status', value: ci_product_sub_status},
        {name: 'ci_unit_Id', value: ci_unit_Id},
        {name: 'ci_inbound_id', value: ci_inbound_id},
        {name: 'ci_unit_price_id', value: ci_unit_price_id}         // add by kik : 2014-01-15
    ]
    
    
    $(document).ready(function() {
            
        // Add By Akkarapol, 19/12/2013, เพิ่ม $('#ShowDataTableForInsert tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย
        $('#ShowDataTableForInsert tbody tr td[title]').hover(function() {
            
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

        // Add By Akkarapol, 20/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '' ) {
                    if($(this).val() != ' ' ){
                        $(this).removeClass('required');
                    }
                    
                }
                
        });
        // END Add By Akkarapol, 20/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        // Add By Akkarapol, 20/09/2013, เพิ่ม onKeyup ของช่อง Document External ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="doc_refer_ext"]').keyup(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 20/09/2013, เพิ่ม onKeyup ของช่อง Document External ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง


        initProductTable();
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);


        $("#preDispatchDate").datepicker();
        $("#preDeliveryDate").datepicker();
        $("#estDispatchDate").datepicker({onRender: function(date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }});

//      comment by kik (08-10-2013)
//        $('#getBtn').click(function() {
//            showModalTable();
//            $('#defDataTableModal_filter label input[type=text]').unbind('keypress keyup')
//                    .bind('keypress keyup', function(e) {
//                if (e.keyCode == 13)
//                {
//                    showModalTable($('#defDataTableModal_filter label input[type=text]').val());
//                }
//            });
//        });

        // add by kik (08-10-2013)
        // start script filter getDetail
        // script use togeter element_filtergetDetail
        $('#getBtn').click(function() {
            if ($('#productCode').val() == "") {
                alert("Please fill <?php echo _lang('product_code'); ?>");
                $('#productCode').focus();
                return false;
            } else {
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

        $('#select_all').click(function() {
            var cdata = $('#defDataTable').dataTable();
            allVals = new Array();
            $(cdata.fnGetNodes()).find(':checkbox').each(function() {
                $this = $(this);
                $this.attr('checked', 'checked');
                allVals.push($this.val());
            });
            //alert('select all '+allVals);
        });

        $('#deselect_all').click(function() {
            var selected = new Array();
            var cdata = $('#defDataTable').dataTable();
            $(cdata.fnGetNodes()).find(':checkbox').each(function() {
                $this = $(this);
                $this.attr('checked', false);
                selected.push($this.val());
                allVals.pop($this.val());
            });
            allVals = new Array();
        });

        $('#search_submit').click(function() {

            //fix defect : 219 :  add by kik : 2013-12-16 
            if (allVals.length == 0) {
                alert("No Product was selected ! Please select a product or click cancle button to exit.");
                return false;
            }
            var oTable = $('#ShowDataTableForInsert').dataTable();
           
            //end fix defect : 219 :  add by kik : 2013-12-16 
            
            var allprice="";
            var price=set_number_format(0);

            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '<?php echo site_url() . "/transferStock/showSelectData" ?>', // in here you should put your query 
                data: 'post_val=' + allVals + "&tableName=ShowDataTableForInsert", // here you pass your id via ajax .
                // in php you should use $_POST['post_id'] to get this value 
                success: function(r)
                {
                    $.each(r.product, function(i, item) {
                        //add by kik : 20140115
                        if(item.Price_Per_Unit != ""){
                            price=item.Price_Per_Unit;
                        }
                        //end add by kik : 20140115
                        var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
                        var Inbound_Id = item.Inbound_Id;
                        $('#ShowDataTableForInsert').dataTable().fnAddData([
                            recordsTotal    //add for edit No. to running number : by kik : 20140226
                                    , item.product_code
                                    , item.Product_NameEN
                                    , item.Product_Status
                                    , item.Product_Sub_Status
                                    , item.Product_lot
                                    , item.Product_Serial
                                    , item.Product_Mfd
                                    , item.Product_Exp
                                    , set_number_format(item.Est_Balance_Qty)
                                    , ''
                                    , price                     //add by kik : 20140115
                                    , item.Unit_Price_value     //add by kik : 20140115
                                    , allprice                  //add by kik : 20140115
                                    , ''
                                    , item.Location_Code
                                    , del
                                    , item.Sub_Status_Code
                                    , item.Unit_Id    //add by kik (08-10-2013)
                                    , Inbound_Id
                                    , item.Unit_Price_Id,        //add by kik : 20140115
                                    "new"                       //add for edit No. to running number : by kik : 20140226
                        ]);
                        
                        // add by kik : 2013-11-13
                        var new_td_item =  $('td:eq(1)', $('#ShowDataTableForInsert tr:last'));
                        new_td_item.addClass("td_click");
                        new_td_item.attr('onclick','showProductEstInbound('+'"'+item.product_code+'"'+','+item.Inbound_Id+')');
                        // end add by kik 
                        
                        recordsTotal++;     //add for edit No. to running number : by kik : 20140226
                    });

                    initProductTable();
                }
            });
            $('.modal.in').modal('hide');
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
        
        var recordsTotal = $('#ShowDataTableForInsert').dataTable().fnSettings().fnRecordsTotal() + 1;//add for edit No. to running number : by kik : 20140226
        
    });
    $('#myModal').modal('toggle').css({
        // make width 90% of screen
        'width': function() {
            return ($(document).width() * 0.95) + 'px';
        },
        // center model
        'margin-left': function() {
            return -($(this).width() / 2);
        }

    });

    //START BY KIK 2014-01-28 function สำหรับเรียกข้อมูลบันทึก Inbound_Item_Id
    function updateInbound() {
        
//        alert('test');exit();
        var varerror = new Array();
        var chk_err = 0;

        //get order id
        var order_id = $('#frmPreDispatch').find('input[name="order_id"]').val();
        //end get order id
        
        //get order detail data by kik
        var oTable = $('#ShowDataTableForInsert').dataTable().fnGetData();
        var product_item = new Array();

        for (i in oTable) {
            
            $.each(oTable[i], function(idx, val) {
                if (idx == ci_item_id && val != 'new') {
                    product_item.push(val);
                }

            });
        }
//        alert(product_item);exit();

        $.ajax({
            type: 'post',
            dataType: 'json',
            async: false,
            url: '<?php echo site_url() . "/pre_dispatch/update_Inbound_ItemId" ?>',
            data: 'order_id=' + order_id + '&order_detail=' + product_item, // ส่ง order_id ไปเพื่อ update detail ทั้งหมดที่มี order_id เท่ากับค่าที่ส่งไป

            success: function(r)
            {
                chk_err = 1;
                varerror = r;

                for (var prop in varerror) {
                    
                
                    if (varerror.hasOwnProperty(prop)) {
                        var item_id = 0;
                        var className = $("#" + item_id).attr('class');
                        //วน Loop เพื่อแสดง value
                        var elmDatatables = $('#ShowDataTableForInsert').dataTable(); // get datatables
                        $.each(varerror[prop], function(key, value_item) {

                            // แสดงผลข้อมูลที่ได้มาจาก inbound ลงใน dataTable
                            // ตรวจสอบ key ที่ต้องการและตรวจสอบว่าไม่เท่ากับค่า null จึงจะให้เข้าไป update ข้อมูลใน dataTable                                   
                            if (key == 'Item_Id') {
                                item_id = value_item                       //add key item_id

                            }
                            else if (key == 'sts_error' && value_item == 0)     //ตรวจสอบว่า ถ้าไม่มีข้อมูลที่ดึงมาจาก inbound ให้แสดงผล record นั้นเป็นสีแดง เพื่อแสดงให้ user ทราบว่าไม่มีข้อมูลใน inbound
                            {
                                $("#" + item_id).addClass("item_err");      //เพิ่ม class ใหม่ที่แสดงผลให้แถวเป็นสีแดง
                                chk_err = 0;

                            } else if (key == 'Product_Status' && value_item != null) {

                                $("#status_" + item_id).text(value_item);        //show Product_Status in dataTable by kik : 20-11-2013

                            } else if (key == 'Product_Sub_Status' && value_item != null) {

                                $("#sub_status_" + item_id).text(value_item);        //show Product_Sub_Status in dataTable by kik : 20-11-2013

                            } else if (key == 'Product_Lot' && value_item != null) {

                                $("#lot_" + item_id).text(value_item);        //show Product_lot in dataTable by kik : 18-10-2013

                            } else if (key == 'Product_Serial' && value_item != null) {

                                $("#sel_" + item_id).text(value_item);        //show Product_Serial in dataTable by kik : 18-10-2013

                            } else if (key == 'Product_Mfd' && value_item != null) {

                                $("#mfd_" + item_id).text(value_item);        //show Product_Mfd in dataTable by kik : 18-10-2013 

                            } else if (key == 'Product_Exp' && value_item != null) {

                                $("#exp_" + item_id).text(value_item);        //show Product_Exp in dataTable by kik : 18-10-2013

                            } else if (key == 'Est_balance' && value_item != null) {

                                $("#est_" + item_id).text(set_number_format(value_item));        //show Est_balance in dataTable by kik : 18-10-2013

                            } else if (key == 'Inbound_item_Id' && value_item != null) {

                                // defect#458 ไม่สามารถคลิกข้อมูล showProductEstInbound ได้ในกรณีที่ import ข้อมูลเข้ามา
                                // by kik : 2013-11-12
                                product_code = $("#prod_code_" + item_id).text();
                                $("#prod_code_" + item_id).addClass("td_click");
                                $("#prod_code_" + item_id).attr('onclick', 'showProductEstInbound(' + product_code + ',' + value_item + ')');
                                // end defect#458

                                $("#inbound_" + item_id).text(value_item);    //show Inbound_item_Id in dataTable by kik : 18-10-2013
                                var indexOfRow = elmDatatables.fnGetPosition($("#" + item_id).get(0)); // find rows index by BALL 22-10-2013
                                elmDatatables.fnUpdate(value_item, indexOfRow, ci_inbound_id); // update new data to datatables by BALL 22-10-2013
                            }

                        });


                    }
                }
            }
        });
        return chk_err;
    }
    //END
    
    function postRequestAction(module, sub_module, action_value, next_state, elm) {
    	global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');
        $("input[name='prod_list[]']").remove();
        //validate Engine called here.//
        var statusisValidateForm = validateForm();

        if (statusisValidateForm === true) {
            
//================================= Check Validation Form ==================================   

            //check format fill Est. Transfer Date
            var estTransferDate = $('#estDispatchDate').val();
            if(estTransferDate != ""){
                var reg_prod_mfd = /^(((0[1-9]|[12]\d|3[01])\/(0[13578]|1[02])\/((19|[2-9]\d)\d{2}))|((0[1-9]|[12]\d|30)\/(0[13456789]|1[012])\/((19|[2-9]\d)\d{2}))|((0[1-9]|1\d|2[0-8])\/02\/((19|[2-9]\d)\d{2}))|(29\/02\/((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))))$/g;
                
                if ( !(reg_prod_mfd.test(estTransferDate))) {
                    alert('Please fill Est. Transfer Date format dd/mm/yyyy only. example 31/01/2000');
                    flagSubmit = false;
                    return false;
                }
            }
            
            var rowData = $('#ShowDataTableForInsert').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Order Detail");
                return false;
            }

            var cTable = $('#ShowDataTableForInsert').dataTable().fnGetNodes();
            var cells = [];
            for (var i = 0; i < cTable.length; i++)
            {
                // Get HTML of 3rd column (for example)
                var qty = $(cTable[i]).find("td:eq("+ci_reserv_qty+")").html();
//                alert(' qty ='+qty);
                if (qty == 'Edit...' || qty == 'Click Here to edit' || qty == 0) {
                    cells.push($(cTable[i]).find("td:eq("+ci_reserv_qty+")").html());
                }
            }

            var all_qty = cells.length;
            
            if (all_qty != 0) {
                alert('Please fill all Reserve Qty');
                return false;
            }
//------------------------ End check validate Form ---------------------------------


//=========================Start find new Inbound Id ================================  

            var update_inb;

            if (sub_module == 'confirmTransferStock' || sub_module == 'approveTransferStock') {
                update_inb = updateInbound();
            } else {
                update_inb = 1;
            }
            
            if(!update_inb){
                return false;
            }
            
//------------------------ End find new Inbound Id ---------------------------------
             
             
//================================= Start data_form ================================    

            var backupForm = document.getElementById('frmPreDispatch').innerHTML;
            var f = document.getElementById("frmPreDispatch");
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

            var oTable = $('#ShowDataTableForInsert').dataTable().fnGetData();
            for (i in oTable) {
                
                //ADD BY POR 2013-12-02 เอา comma ออกก่อนส่งค่าไปบันทึก
                oTable[i][ci_reserv_qty] = oTable[i][ci_reserv_qty].replace(/\,/g,'');
                //END ADD

                var prod_data = oTable[i].join(separator); // Change By Akkarapol, 22/01/2014, Change code set prod_data from 'oTable.each' to 'oTable.join' 

                var prodItem = document.createElement("input");
                prodItem.setAttribute('type', "hidden");
                prodItem.setAttribute('name', "prod_list[]");
                prodItem.setAttribute('id', "prod_list");
                prodItem.setAttribute('value', prod_data); // Add By Akkarapol, 21/02/2014, use data with SEPARATOR for $.POST to controller
                f.appendChild(prodItem);
            }
            
            //add for set index in dataTable : by kik : 20140115
            $.each(ci_list, function(i, obj) {
                var ci_item = document.createElement("input");
                ci_item.setAttribute('type', "hidden");
                ci_item.setAttribute('name', obj.name);
                ci_item.setAttribute('value', obj.value);
                f.appendChild(ci_item);
            });
            //end add for set index in dataTable : by kik : 20140115

           global_data_form = $("#frmPreDispatch").serialize();
                    
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

//------------------------ End check validate Data ---------------------------------

        }
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function getValueFromTableData() {
        // var items = [[1,2],[3,4],[5,6]];
        var xx = [];
        var rowCount = $('#ShowDataTableForInsert tbody tr');
        $(rowCount).each(function() {
            var $tds = $(this).find('td');
            var tmp = [];
            $tds.each(function() {//alert(i+","+j+":"+$(this).text());
                //tmpArray[i][j] = $(this).text();
                tmp.push($(this).text());
            });
            tmp.push("%%%");
            xx.push(tmp);
        });

        $("#queryText").val(xx);

    }
    
    function initProductTable() {
        $('#ShowDataTableForInsert').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bRetrieve": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "aoColumnDefs": [
                {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
                {"sWidth": "20%", "sClass": "left_text", "aTargets": [2]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [3]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [4]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [5]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [6]},
                {"sWidth": "7%", "sClass": "indent obj_mfg", "aTargets": [7]},
                {"sWidth": "7%", "sClass": "center obj_exp", "aTargets": [8]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [9]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [10]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [11]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [12]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [13]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [14]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [15]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [16]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [17]},
            ]
        }).makeEditable({
            sUpdateURL: '<?php echo site_url() . "/transferStock/saveEditedRecord"; ?>'
                    , sAddURL: '<?php echo site_url() . "/transferStock/saveEditedRecord"; ?>'
                    , "aoColumns": [
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                {
                    "onblur": 'submit',
                    "cssclass": "required number",
                    "width": '90%', // Add By Akkarapol, 15/10/2013, เซ็ตให้ความกว้างของ input Reserv Qty มีขนาด 90% ของ parent
                    "event": 'click', // Add By Akkarapol, 15/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Reserve Qty นี้ โดยการ คลิกเพียง คลิกเดียว 
                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
                        calculate_qty();
                    }
                }
//               , null
//               , null
//               , null
                {price_per_unit_table}
                , {
                    onblur: 'submit',
                    sUpdateURL: "<?php echo site_url() . "/transferStock/saveEditedRecord"; ?>",
                    event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว 
                }
                ,null
                ,{"bSearchable": false}
                ,null
                ,null // add by kik (08-10-2013)
                ,null    // add by kik (08-10-2013)

            ]
        });

        var oTable = $('#ShowDataTableForInsert').dataTable();

        //var bVis = oTable.fnSettings().aoColumns[11].bVisible;

        oTable.fnSetColumnVis(ci_product_sub_status, false);
        oTable.fnSetColumnVis(ci_unit_Id, false); // add by kik (08-10-2013)
        oTable.fnSetColumnVis(ci_inbound_id, false);
        oTable.fnSetColumnVis(ci_unit_price_id, false);
        oTable.fnSetColumnVis(ci_item_id, false);   //add for edit No. to running number : by kik : 20140226

        //add by kik : 20140113
        if(statusprice!=true){
            oTable.fnSetColumnVis(ci_price_per_unit, false);
            oTable.fnSetColumnVis(ci_unit_price, false);
            oTable.fnSetColumnVis(ci_all_price, false);
            
        }
        //end add by kik : 20140113
        
        oTable.fnDraw(false);
    }

    function removeItem(obj) {
        var index = $(obj).closest("table tr").index();
        $('#ShowDataTableForInsert').dataTable().fnDeleteRow(index);
        calculate_qty();
    }

    function deleteItem(obj) {
        var index = $(obj).closest("table tr").index();
        var data = $('#ShowDataTableForInsert').dataTable().fnGetData(index);
        data = data.join(separator);
        $('#ShowDataTableForInsert').dataTable().fnDeleteRow(index);
        var f = document.getElementById("frmPreDispatch");
        var prodDelItem = document.createElement("input");
        prodDelItem.setAttribute('type', "hidden");
        prodDelItem.setAttribute('name', "prod_del_list[]");
        prodDelItem.setAttribute('value', data);
        f.appendChild(prodDelItem);
        calculate_qty();
        
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
           // url = "<?php echo site_url(); ?>/flow/flowPreDispatchList";
			window.onbeforeunload = null;
            url = "<?php echo site_url();?>/transferStock/transferStockList";
            redirect(url)
        }
    }
        
    // add by kik : 20-11-2013
    function validateForm() {
        //validate engine 
        var status;
        var index = 0;
        var flag = 0;
        $("form").each(function() {//focusCleanup: true,

            $(this).valid();
            flag += ($(this).valid() == true ? 1 : 0);
            index++;
        });
        status = (index == flag ? true : false);
        return status;
    }
    // end by kik : 20-11-2013
    
    
    function checkOnlyEnglish(id) {
        var selector = '#' + id;
        $(selector).keypress(function(event) {
            var ew = event.which;
//            if(ew == 32)
//                return true; SpaceBar Is Not Allow here.
            if (48 <= ew && ew <= 57) {
                return true;
            }
            if (65 <= ew && ew <= 90) {
                return true;
            }
            if (97 <= ew && ew <= 122) {
                return true;
            }
            return false;
        });
    }
    function setNumberFormat(id) {
        var selector = "#".id;
        $(selector).blur(function() {
            $(this).parseNumber({format: "#,###.00", locale: "us"});
            $(this).formatNumber({format: "#,###.00", locale: "us"});
        });
    }


    // Add function calculate_qty : by kik : 28-10-2013 
    function calculate_qty() {

        var rowData = $('#ShowDataTableForInsert').dataTable().fnGetData();
        var rowData2 = $('#ShowDataTableForInsert').dataTable();        //add by kik : 20140113
        
        var num_row = rowData.length;
        var sum_cf_qty = 0;
        var sum_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมดต่อหน่วย
        var sum_all_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมด 
        for (i in rowData) {
            var tmp_qty = 0;
            var tmp_price = 0;//ราคาต่อหน่วย
            var all_price = 0; //ราค่าทั้งหมดต่อหนึ่งรายการ
            
//            tmp_qty = parseInt(rowData[i][10].replace(/\,/g,'')); //+++++ADD BY POR 2013-12-02 แก้ไขให้เอา comma ออกก่อนนำไปคำนวณ
            tmp_qty = parseFloat(rowData[i][ci_reserv_qty].replace(/\,/g,'')); //fix : defect 545 : change from parseInt to parseFloat : by kik : 2013-12-16
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

        $('#sum_recieve_qty').html(set_number_format(sum_cf_qty));
        $('#sum_price_unit').html(set_number_format(sum_price)); //ADD BY POR 2014-01-09 เพิ่มให้แสดงราคารวมทั้งหมดต่อหน่วย
        $('#sum_all_price').html(set_number_format(sum_all_price)); //ADD BY POR 2014-01-09 เพิ่มให้แสดงราคารวมทั้งหมด

    }
    // end function calculate_qty : by kik : 28-10-2013 


</script>
<style>

    #myModal{
        width: 1170px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -600px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */

    }
    /*COMMENT BY KIK 2013-10-18 เพิ่ม class สำหรับบอกว่าเป็น record ที่ไม่มี inbound_id หรือ ไม่มี location ที่สามารถหยิบได้*/
    .item_err{
        color: red;
    }
    /*END COMMENT*/
</style>
<?php
if (!isset($DocNo)) {
    $DocNo = "";
}
if (!isset($est_action_date)) {
    $est_action_date = "";
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
if (!isset($remark)) {
    $remark = "";
}

if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
    $is_urgent = false;
} else {
    $is_urgent = true;
}

?>
<div class="content <?php echo config_item("css_form"); ?>" style='height:100%; width:99.5%;'>

    <form class="" method="POST" action="" id="frmPreDispatch" name="frmPreDispatch" >
        <fieldset style="margin:0px auto;">
            <legend>Transfer Stock Order</legend>
            <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                <tr>
                    <td align="right">
                        Renter :
                    </td>
                    <td align="left">
                        {renter_id_select}
                    </td>
                    <td align="right">
                        Shipper :
                    </td>
                    <td align="left">
                        {frm_warehouse_select}
                    </td>
                    <td align="right">
                        Consignee :
                    </td>
                    <td>
                        {to_warehouse_select}
                    </td>                    
                </tr>
                <tr>
                    <td align="right">
                        Transfer Type :
                    </td>
                    <td>
                        {dispatch_type_select}
                    </td>
                    <td align="right">
                        Est. Transfer Date :
                    </td>
                    <td align="left">
                        <input type="text" class="required" placeholder="Date Format" id="estDispatchDate" name="estDispatchDate" value="<?php echo $est_action_date; ?>" style="text-transform: uppercase"/> <!--Edit By Akkarapol, 21/01/2013, Add class="required" in textbox#estDispatchDate because this field need data-->
                    </td>
                    <td>
                        <!--Delivery Date :-->
                    </td>
                    <td>
                        <!--<input type="text" placeholder="Date Format" id="preDeliveryDate" name="preDeliveryDate" value="">-->
                    </td>                    
                </tr>
                <tr>
                    <td align="right">
                        Document No. :
                    </td>
                    <td align="left">
                        <input type="text" placeholder="Auto Generate" id="document_no" name="document_no" disabled value="<?php echo $DocNo; ?>" style="text-transform: uppercase"/>
                    </td>
                    <td align="right">
                        Refer External No. :
                    </td>
                    <td align="left">
                        <input type="text" class="required" placeholder="Invoice No." id="doc_refer_ext" name="doc_refer_ext" value="<?php echo $doc_refer_ext; ?>" style="text-transform: uppercase"/>
                    </td>
                    <td align="right">
                        Refer Internal No. :
                    </td>
                    <td align="left">
                        <input type="text" placeholder="Transfer Order No." id="doc_refer_int" name="doc_refer_int" value="<?php echo $doc_refer_int; ?>"  style="text-transform: uppercase"/>
                    </td>                    
                </tr>
                <tr>
                    <td style="text-align:right;">Invoice No.</td>
                    <td style="text-align:left;"><?php echo form_input('doc_refer_inv', $doc_refer_inv, 'placeholder="�����Ţ㺡ӡѺ�Թ���"  style="text-transform: uppercase"'); ?></td>
                    <td style="text-align:right;">Customs Entry	</td>
                    <td style="text-align:left;"><?php echo form_input('doc_refer_ce', $doc_refer_ce, 'placeholder="�����Ţ㺢��Թ���"  style="text-transform: uppercase"'); ?></td>
                    <td style="text-align:right;">BL No.</td>
                    <td style="text-align:left;"><?php echo form_input('doc_refer_bl', $doc_refer_bl, 'placeholder="㺵�����Թ��ҷҧ����"  style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td style="text-align:right;">Remark</td>
                    <td style="text-align:left;" colspan="3">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo $remark; ?></TEXTAREA>
						</td>
						<td style="text-align:right;"></td>
						<td style="text-align:left;">
                                                      <!--//add for ISSUE 3312 : by kik : 20140120-->
                                                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                                                 </td>
					  </tr>
                </table>
            </fieldset>
            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
        <?php echo form_hidden('process_id', $process_id); ?>
        <?php echo form_hidden('present_state', $present_state); ?>
		
        <?php
        if (isset($flow_id)) {
            echo form_hidden('flow_id', $flow_id);
        }
        ?>
            
            <?php
            if (isset($order_id)) {
                echo form_hidden('order_id', $order_id);
            }
            ?>
            <input type="hidden" name="token" value="<?php echo $token?>" />
        </form>
            <fieldset>
                <legend>Product Detail </legend>
                <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
                   
                    
  <!--            #ISSUE 2395 Stock  Adjustment 
                    #DATE:2013-09-11
                    #BY:KIK
                    #เพิ่มการค้นหาด้วย Status & Sub Status ในส่วนของ get product detail

                    #START New Comment Code #ISSUE 2395-->

<!--                   <tr>
                          <td>
                              &nbsp;&nbsp;&nbsp;&nbsp;
                                Product Status :  {productStatus_select}
                                Product Sub Status :  {productSubStatus_select}
                                Product Code : <?php //echo form_input("productCode" ,"" ,"id='productCode' placeholder='Product Code' "); ?>
                            <a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn">Get Detail</a>
                          </td>
                   </tr>-->

  <!--            #End New Comment Code #ISSUE 2395 
                     #Start Old Comment Code #ISSUE 2395
                     <tr>
                    <td>
                        <div style="width:90%;">
                            &nbsp;&nbsp;&nbsp;&nbsp;Product Code : 
                            <input type="text" name="productCode" id="productCode" class="input-medium" placeholder="Product Code">
                            <a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn">Get Detail</a>
                        </div>
                    </td>
                 </tr>-->
  <!--            #End Old Comment Code #ISSUE 2395-->


                    <tr>
                        <td>
                            <table style="width:100%; margin:0px auto;"> 
                        <?php $this->load->view('element_filtergetDetail'); ?>
                            </table>
                        </td>
                    </tr>
                    
                    
                    <tr>
                        <td>
                            <div id="defDataTable_wrapper" class="dataTables_wrapper" role="grid" style="width:100%;overflow-x: auto;margin:0px auto;">
                                <div style="width:100%;overflow-x: auto;" id="showDataTable"> 
                                    <table cellpadding="2" cellspacing="0" border="0" class="display" id="ShowDataTableForInsert">
                                        <thead>
                                            <tr>
                                                <th><?php echo _lang('no'); ?></th>
                                                <th><?php echo _lang('product_code'); ?></th>
                                                <th><?php echo _lang('product_name'); ?></th>
                                                <th><?php echo _lang('product_status'); ?></th>
                                                <th><?php echo _lang('product_sub_status'); ?></th>
                                                <th><?php echo _lang('lot'); ?></th>
                                                <th><?php echo _lang('serial'); ?></th>
                                                <th><?php echo _lang('product_mfd'); ?></th>
                                                <th><?php echo _lang('product_exp'); ?></th>
                                                <th style='text-align:center;'><?php echo _lang('est_balance_qty'); ?></th>
                                                <th style='text-align:center;'><?php echo _lang('reserve_qty'); ?></th>
                                                <th style='text-align:center;'><?php echo _lang('price_per_unit'); ?></th>
                                                <th style='text-align:center;'><?php echo _lang('unit_price'); ?></th>
                                                <th style='text-align:center;'><?php echo _lang('all_price'); ?></th>
                                                <th><?php echo _lang('remark'); ?></th>
                                                <th><?php echo _lang('from_location'); ?></th>
                                                <th><?php echo _lang('del'); ?></th>
                                                <th style="display:none;">Sub Status Code</th>
                                                <th style="display:none;">Unit_Id</th>  <!--add by kik (08-10-2013)-->
                                                <th style="display:none;">Inbound Id</th> <!--add by kik (08-10-2013)-->
                                                <th style="display:none;">Price/Unit ID</th>
                                                <th style="display:none;">Item Id</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                    <?php
                                    $sum_Reserv_Qty = 0;   //add by kik : 25-10-2013   
                                    $sumPriceUnit = 0;      //add by kik : 14-01-2014 ราคารวมทั้งหมดต่อหน่วย
                                    $sumPrice = 0;          //add by kik : 14-01-2014 ราคารวมทั้งหมด
                                    if (isset($order_detail_data)) {
                                        $str_body = "";
                                        $i = 1;//add for edit No. to running number : by kik : 20140226
                                        foreach ($order_detail_data as $order_column) {
                                            $all_price = ($order_column->Reserv_Qty)*($order_column->Price_Per_Unit);
                                            $order_id = $order_column->Order_Id;
                                            $str_body .= "<tr id=\"" . $order_column->Item_Id . "\">";
                                            $str_body .= "<font-color='red'><td>" . $i . "</td>"; //add for edit No. to running number : by kik : 20140226
                                            //add class td_click and ONCLICK for show Product Est. balance Detail modal : by kik : 06-11-2013
//                                            $str_body .= "<td id='prod_code_" . $order_column->Item_Id . "'  " . (!empty($order_column->Inbound_Item_Id) ? " class='td_click' ONCLICK='showProductEstInbound(\"{$order_column->Product_Code}\",{$order_column->Inbound_Id})'>" . $order_column->Product_Code . "</td>";
//                                            $str_body .= "<td id='prod_code_" . $order_column->Item_Id . "'  " . (!empty($order_column->Inbound_Item_Id) ? " class='td_click' ONCLICK='showProductEstInbound(\"{$order_column->Product_Code}\",{$order_column->Inbound_Item_Id})'" : "") . ">" . $order_column->Product_Code . "</td>"; // add id : by kik : 2013-11-12
                                            $str_body .= "<td id='prod_code_" . $order_column->Item_Id . "'  " . (!empty($order_column->Inbound_Id) ? " class='td_click' ONCLICK='showProductEstInbound(\"{$order_column->Product_Code}\",{$order_column->Inbound_Id})'" : "") . ">" . $order_column->Product_Code . "</td>"; // add id : by kik : 2013-11-12
                                                                                                                                    
                                            //  Edit By Akkarapol, 19/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้
                                            //  $str_body .= '<td>' . $order_column->Product_Name . '</td>';
//                                            $str_body .= "<td title='" . htmlspecialchars($order_column->Product_NameEN, ENT_QUOTES) . "' >" . $order_column->Product_NameEN . "</td>";
//                                            $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                            $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Product_NameEN) . "\">" . $order_column->Product_NameEN . "</td>"; // Edit for fix error  Undefined property: stdClass::$Full_Product_Name and  Undefined property: stdClass::$Product_Name : approve by P'ball : kik 20140115
                                            // END Edit By Akkarapol, 19/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้
                                                                                        
//                                            $str_body .= "<td>" . $order_column->Product_NameEN . "</td>";
                                            $str_body .= "<td id='status_" . $order_column->Item_Id . "'>" . $order_column->Product_Status . "</td>";
                                            $str_body .= "<td id='sub_status_" . $order_column->Item_Id . "'>  " . $order_column->Product_Sub_Status . "</td>";
                                            $str_body .= "<td id='lot_" . $order_column->Item_Id . "'>" . $order_column->Product_Lot . "</td>";
                                            $str_body .= "<td id='sel_" . $order_column->Item_Id . "'> " . $order_column->Product_Serial . "</td>";
                                            $str_body .= "<td id='mfd_" . $order_column->Item_Id . "'>" . $order_column->Product_Mfd . "</td>";
                                            $str_body .= "<td id='exp_" . $order_column->Item_Id . "'>" . $order_column->Product_Exp . "</td>";
                                            $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Est_Balance_Qty) . "</td>";
                                            $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Reserv_Qty) . "</td>";
                                            $str_body .= "<td>" . set_number_format($order_column->Price_Per_Unit) . "</td>";
                                            $str_body .= "<td>" . $order_column->Unit_Price_value . "</td>";
                                            $str_body .= "<td>" . set_number_format($all_price) . "</td>";
                                            $str_body .= "<td>" . $order_column->Remark . "</td>";
                                            $str_body .= "<td>" . $order_column->Location_Code . "</td>";
                                            $str_body .= "<td><a ONCLICK=\"deleteItem(this)\" >" . img("css/images/icons/del.png") . "</a></td>";
                                            $str_body .= "<td style=\"display:none;\">" . $order_column->Sub_Status_Code . "</td>";
                                            $str_body .= "<td style=\"display:none;\">" . $order_column->Unit_Id . "</td>"; // add by kik (08-10-2013)
                                            $str_body .= "<td id='inbound_" . $order_column->Item_Id . "' style=\"display:none;\">" . $order_column->Inbound_Id . "</td>"; // edit by kik (07-10-2013)
                                            $str_body .= "<td style=\"display:none;\">" . $order_column->Unit_Price_Id . "</td>"; // edit by kik (07-10-2013)
                                            $str_body .= "<td>" . $order_column->Item_Id . "</td>"; //add for edit No. to running number : by kik : 20140226
                                            $str_body .= "</tr>";
                                            $sum_Reserv_Qty+=$order_column->Reserv_Qty;        //add by kik    :   25-10-2013
                                            $sumPriceUnit+=$order_column->Price_Per_Unit;       //add by kik    :   20140114
                                            $sumPrice+=$all_price;                              //add by kik    :   20140114  
                                            $i++; //add for edit No. to running number : by kik : 20140226
                                        }
                                        echo $str_body;
                                    }
                                    ?>
                                        </tbody>
                                         <!-- show total qty : by kik : 29-10-2013-->
                                                <tfoot>
                                                   <tr>
                                                         <th colspan='10' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                                         <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Reserv_Qty); ?></span></th>
                                                         <?php if($price_per_unit):?>
                                                            <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                                            <th></th>
                                                            <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                                         <?php endif;?>
                                                         <th colspan='6' class ='ui-state-default indent' ></th>
                                                    </tr> 
                                                </tfoot>
                                        <!-- end show total qty : by kik : 29-10-2013-->  
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
</div>

<!--call element element show product get Detail modal : add by kik : 16-12-2013-->
<?php $this->load->view('element_showgetDetail'); ?>

<!--call element Product Est. balance Detail modal : add by kik : 06-11-2013-->
<?php $this->load->view('element_showEstBalance'); ?>

<?php $this->load->view('element_modal_message_alert'); ?>
