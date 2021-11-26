<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>
    
    var build_pallet = '<?php echo $this->config->item('build_pallet'); ?>';
    
    // add var for validation data
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Transfer Stock Order'; // เน€เธเธฅเธตเนเธขเธเนเธซเนเน€เธเนเธเธ•เธฒเธก Flow เธ—เธตเนเธ—เธณเธญเธขเธนเนเน€เธเนเธ Pre-Dispatch, Adjust Stock, Stock Transfer เน€เธเนเธเธ•เนเธ
    var data_table_id_class = '#ShowDataTableForInsert'; // เน€เธเธฅเธตเนเธขเธเนเธซเนเน€เธเนเธเธ•เธฒเธก id เธซเธฃเธทเธญ class เธเธญเธ data table เนเธ”เธขเธ–เนเธฒเน€เธเนเธ id เธเนเนเธชเน # เน€เธเธดเนเธกเนเธ เธซเธฃเธทเธญเธ–เนเธฒเน€เธเนเธ class เธเนเนเธชเน . เน€เธเธดเนเธกเนเธเน€เธซเธกเธทเธญเธเธเธฒเธฃเน€เธฃเธตเธขเธเนเธเนเธ”เนเธงเธข javascript เธเธเธ•เธด
    var redirect_after_save = site_url + "/transferStock"; // เน€เธเธฅเธตเนเธขเธเนเธซเนเน€เธเนเธเธ•เธฒเธก url เธเธญเธเธซเธเนเธฒเธ—เธตเนเธ•เนเธญเธเธเธฒเธฃเธเธฐเนเธซเน redirect เนเธ เนเธ”เธขเธเธเธ•เธดเธเธฐเน€เธเนเธเธซเธเนเธฒ list เธเธญเธ flow เธเธฑเนเธเน
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';
    // end add var for validation data
    
    var oTable = null;
    var statusprice = "<?php echo $price_per_unit; ?>"; //add by kik : 20140115
    var separator = "<?php echo SEPARATOR; ?>"; // Add By Akkarapol, 21/01/2013, Add Separator for use in Page
    var ci_item_id = 0; //add for edit No. to running number : by kik : 20140226
    var ci_prod_code = 1;
    var ci_product_status = 3;
    var ci_product_lot = 5;
    var ci_product_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_reserv_qty = 10;
    var ci_pallet_id = 14;
    var ci_remark = 15;
    var ci_location_code = 16;
    var ci_product_sub_status = 18;
    var ci_unit_Id = 19;
    
    // add by kik : 2014-01-14
    var ci_price_per_unit = 11;
    var ci_unit_price = 12;
    var ci_all_price = 13;
    var ci_unit_price_id = 20;
    //end add by kik : 2014-01-14
    var ci_inbound_id = 21; //add for edit No. to running number : by kik : 20140226
    
    var ci_list = [
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_product_status', value: ci_product_status},
        {name: 'ci_product_lot', value: ci_product_lot},
        {name: 'ci_product_serial', value: ci_product_serial},
        {name: 'ci_mfd', value: ci_mfd},
        {name: 'ci_exp', value: ci_exp},
        {name: 'ci_reserv_qty', value: ci_reserv_qty},     
        {name: 'ci_pallet_id', value: ci_pallet_id},            
        {name: 'ci_remark', value: ci_remark},            
        {name: 'ci_location_code', value: ci_location_code},              
        {name: 'ci_price_per_unit', value: ci_price_per_unit},      // add by kik : 2014-01-15
        {name: 'ci_unit_price', value: ci_unit_price},              // add by kik : 2014-01-15
        {name: 'ci_all_price', value: ci_all_price},                // add by kik : 2014-01-15
        {name: 'ci_product_sub_status', value: ci_product_sub_status},
        {name: 'ci_unit_Id', value: ci_unit_Id},
        {name: 'ci_unit_price_id', value: ci_unit_price_id},         // add by kik : 2014-01-15
        {name: 'ci_inbound_id', value: ci_inbound_id}         //add for edit No. to running number : by kik : 20140226
    ]
    
    
    
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


        // Add By Akkarapol, 20/09/2013, เน€เธเนเธเธงเนเธฒ เธ–เนเธฒเน€เธเนเธ Class required เนเธฅเนเธงเธกเธตเธเนเธญเธกเธนเธฅเนเธเธเนเธญเธเนเธฅเนเธง เธเนเนเธซเนเธ–เธญเธ” Class เธญเธญเธ เน€เธเธทเนเธญเธเธฐเนเธ”เนเนเธกเนเธกเธตเธเธญเธเนเธ”เธ
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 20/09/2013, เน€เธเนเธเธงเนเธฒ เธ–เนเธฒเน€เธเนเธ Class required เนเธฅเนเธงเธกเธตเธเนเธญเธกเธนเธฅเนเธเธเนเธญเธเนเธฅเนเธง เธเนเนเธซเนเธ–เธญเธ” Class เธญเธญเธ เน€เธเธทเนเธญเธเธฐเนเธ”เนเนเธกเนเธกเธตเธเธญเธเนเธ”เธ

        // Add By Akkarapol, 20/09/2013, เน€เธเธดเนเธก onKeyup เธเธญเธเธเนเธญเธ Document External เธงเนเธฒเธ–เนเธฒเธกเธตเธเนเธญเธกเธนเธฅเนเธซเนเน€เธญเธฒเธเธฃเธญเธเนเธ”เธเธญเธญเธ เนเธ•เนเธ–เนเธฒเธเนเธญเธเธกเธฑเธเธงเนเธฒเธ เนเธซเนเนเธชเนเธเธญเธเนเธ”เธ
        $('[name="doc_refer_ext"]').keyup(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 20/09/2013, เน€เธเธดเนเธก onKeyup เธเธญเธเธเนเธญเธ Document External เธงเนเธฒเธ–เนเธฒเธกเธตเธเนเธญเธกเธนเธฅเนเธซเนเน€เธญเธฒเธเธฃเธญเธเนเธ”เธเธญเธญเธ เนเธ•เนเธ–เนเธฒเธเนเธญเธเธกเธฑเธเธงเนเธฒเธ เนเธซเนเนเธชเนเธเธญเธเนเธ”เธ

        initProductTable();
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

        $("#preDispatchDate").datepicker();
        $("#preDeliveryDate").datepicker();
        $("#estDispatchDate").datepicker({onRender: function(date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }});


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

        $('#search_submit').click(function() {

            //Add code for fix defect : 219 :  add by kik : 2013-12-16 
            if (allVals.length == 0) {
                alert("No Product was selected ! Please select a product or click cancle button to exit.");
                return false;
            }
            var oTable = $('#ShowDataTableForInsert').dataTable();
            var recordsTotal = oTable.fnSettings().fnRecordsTotal() + 1;
            //end Add code for fix defect : 219 :  add by kik : 2013-12-16 

            var allprice="";
            var price=set_number_format(0);

            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '<?php echo site_url() . "/transferStock/showSelectData" ?>', // in here you should put your query 
                data: 'post_val=' + allVals + "&tableName=ShowDataTableForInsert", // here you pass your id via ajax .
                // in php you should use $_POST['post_id'] to get this value 
                success: function(r) {
                    $.each(r.product, function(i, item) {
                        //add by kik : 20140115
                        if(item.Price_Per_Unit != ""){
                            price=item.Price_Per_Unit;
                        }
                        //end add by kik : 20140115
                        var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
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
                                    , item.Est_Balance_Qty
                                    , ''
                                    , price                     //add by kik : 20140115
                                    , item.Unit_Price_value     //add by kik : 20140115
                                    , allprice                  //add by kik : 20140115
                                    , item.Pallet_Code
                                    , ''
                                    , item.Location_Code    // Edit by Ton! 20130808 *add item.Location_Id
                                    , del
                                    , item.Sub_Status_Code
                                    , item.Unit_Id
                                    , item.Unit_Price_Id        //add by kik : 20140115
                                    , item.Inbound_Id   //add for edit No. to running number : by kik : 20140226
                                    ]
                                );// add by kik (27-09-2013)

                        // add by kik : 2013-11-13
                        var new_td_item = $('td:eq(1)', $('#ShowDataTableForInsert tr:last'));
                        new_td_item.addClass("td_click");
                        new_td_item.attr('onclick', 'showProductEstInbound(' + '"' + item.product_code + '"' + ',' + item.Inbound_Id + ')');
                        // end add by kik 
                        
                        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_product_sub_status, false);// add by kik (16-09-2013)
                        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_unit_Id, false);// add by kik (16-09-2013)
                        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_unit_price_id, false);// add by kik (15-01-2014)
                        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_inbound_id, false); //add for edit No. to running number : by kik : 20140226
                        
                        if(!build_pallet){ // check config build_pallet if It's false then hide a column Pallet Code
                            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_pallet_id, false);
                        }
                        recordsTotal++; //add for edit No. to running number : by kik : 20140226
                    });

                    initProductTable();
                }
            });
            $('.modal.in').modal('hide');
            allVals = new Array();
        });

        $("#ShowDataTableForInsert tbody tr td input").each(function() {
            alert(this);
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
            
            for (var i = 0; i < cTable.length; i++) {
                // Get HTML of 3rd column (for example)
                var qty = $(cTable[i]).find("td:eq("+ci_reserv_qty+")").html(); //edit by kik (16-09-2013)
                if (qty == 'Click to edit') {
                    cells.push($(cTable[i]).find("td:eq("+ci_reserv_qty+")").html());  //edit by kik (16-09-2013)
                }
            }
            var all_qty = cells.length;
            if (all_qty != 0) {
                alert('Please fill all Dispatch Qty');
                return false;
            }
            
//------------------------ End check validate Form ---------------------------------
             
//================================= Start data_form ================================ 
      
                //handmade call data from datatable.//    
                getValueFromTableData();

                var f = document.getElementById("transferStockForm");
                var actionType = [];
                actionType = document.createElement("input");
                actionType.setAttribute('type', "hidden");
                actionType.setAttribute('name', "action_type");
                actionType.setAttribute('value', action_value);
                f.appendChild(actionType);

                var toStateNo = [];
                toStateNo = document.createElement("input");
                toStateNo.setAttribute('type', "hidden");
                toStateNo.setAttribute('name', "next_state");
                toStateNo.setAttribute('value', next_state);
                f.appendChild(toStateNo);

                var oTable = $('#ShowDataTableForInsert').dataTable().fnGetData();

                var prodItem = [];
                for (i in oTable) {
                    //ADD BY POR 2013-12-02 เน€เธญเธฒ comma เธญเธญเธเธเนเธญเธเธชเนเธเธเนเธฒเนเธเธเธฑเธเธ—เธถเธ
                    oTable[i][ci_reserv_qty] = oTable[i][ci_reserv_qty].replace(/\,/g, '');
                    //END ADD

                    var prod_data = oTable[i].join(separator); // Change By Akkarapol, 22/01/2014, Change code set prod_data from 'oTable.each' to 'oTable.join' 
                    // Add By Akkarapol, 21/02/2014, Add loop for set data with SEPARATOR
                    
                    
                    prodItem = document.createElement("input");
                    prodItem.setAttribute('type', "hidden");
                    prodItem.setAttribute('name', "prod_list[]");
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

            global_data_form = $("#transferStockForm").serialize();

            
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


        } else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function getValueFromTableData() {
        var xx = [];
        var rowCount = $('#ShowDataTableForInsert tbody tr');
        $(rowCount).each(function() {
            var $tds = $(this).find('td');
            var tmp = [];
            $tds.each(function() {//alert(i+","+j+":"+$(this).text());
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
//            "bSort": true,
            "bStateSave": true,
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
                {"sWidth": "7%", "sClass": "center", "aTargets": [15]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [16]},
            ]
        }).makeEditable({
            "aoColumns": [
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
                    sSortDataType: "dom-text",
                    sType: "numeric",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    cssclass: "required number",
                    sUpdateURL: "<?php echo site_url() . "/transferStock/saveEditedRecord"; ?>",
                    loadfirst: true, // Add By Akkarapol, 14/10/2013, เน€เธเธดเนเธก loadfirst เน€เธเธทเนเธญเนเธซเนเธเนเธญเธ Confirm Qty เธเธตเนเธกเธต textbox เธเธถเนเธเธกเธฒเธ•เธฑเนเธเนเธ•เนเนเธซเธฅเธ”เธซเธเนเธฒ
                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
                        calculate_qty();
                    }
                }
                {price_per_unit_table}
                , {
                    onblur: 'submit',
                    sUpdateURL: "<?php echo site_url() . "/transferStock/saveEditedRecord"; ?>",
                    event: 'click', // Add By Akkarapol, 14/10/2013, เน€เธเนเธ•เนเธซเน event เนเธเธเธฒเธฃเนเธเนเนเธเธเนเธญเธกเธนเธฅเธเนเธญเธ Remark เธเธตเน เนเธ”เธขเธเธฒเธฃ เธเธฅเธดเธเน€เธเธตเธขเธ เธเธฅเธดเธเน€เธ”เธตเธขเธง 
                }
                , null
                        , null
                        , null
            ]
        });
         //add by kik : 20140113
        if(statusprice!=true){
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_all_price, false);
        }
        //end add by kik : 20140113
        
        if(!build_pallet){ // check config build_pallet if It's false then hide a column Pallet Code
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_pallet_id, false);
        }
    }
    function removeItem(obj) {
        var index = $(obj).closest("table tr").index();
        $('#ShowDataTableForInsert').dataTable().fnDeleteRow(index);
        calculate_qty();
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/transferStock";
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


    function validateDateRange(dateFrom, dateTo, interval) {
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

        var checkin = $(dateFrom).datepicker({
            onRender: function(date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function(ev) {
            if (ev.date.valueOf() > checkout.date.valueOf()) {
                var newDate = new Date(ev.date);
                newDate.setDate(newDate.getDate() + 1);
                checkout.setValue(newDate);
            }
            checkin.hide();
            $(dateTo)[0].focus();
        }).data('datepicker');
        var checkout = $(dateTo).datepicker({
            onRender: function(date) {
                return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function(ev) {
            checkout.hide();
        }).data('datepicker');
    }

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
        var sum_price = 0; //ADD BY POR 2014-01-09 เธฃเธฒเธเธฒเธ—เธฑเนเธเธซเธกเธ”เธ•เนเธญเธซเธเนเธงเธข
        var sum_all_price = 0; //ADD BY POR 2014-01-09 เธฃเธฒเธเธฒเธ—เธฑเนเธเธซเธกเธ” 
        for (i in rowData) {
            var tmp_qty = 0;
            var tmp_price = 0;//เธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข
            var all_price = 0; //เธฃเธฒเธเนเธฒเธ—เธฑเนเธเธซเธกเธ”เธ•เนเธญเธซเธเธถเนเธเธฃเธฒเธขเธเธฒเธฃ
            tmp_qty = parseFloat(rowData[i][ci_reserv_qty].replace(/\,/g, '')); //+++++ADD BY POR 2013-11-29 เนเธเนเนเธเนเธซเนเน€เธเนเธ parseFloat เน€เธเธทเนเธญเธเธเธฒเธ qty เน€เธเธฅเธตเนเธขเธเน€เธเนเธ float

            if (!($.isNumeric(tmp_qty))) {
                tmp_qty = 0;
            }

            //+++++ADD BY POR 2014-01-09 เน€เธเธดเนเธกเธเธฒเธฃเธเธณเธเธงเธ“เธฃเธฒเธเธฒ
            var str2 = rowData[i][ci_price_per_unit]; //เธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข
            rowData[i][ci_price_per_unit] = str2.replace(/\,/g, '');
            tmp_price = parseFloat(rowData[i][ci_price_per_unit]);
            if (!($.isNumeric(tmp_price))) {
                tmp_price = 0;
            }
            
            //เธเธณ qty เธกเธฒเธเธนเธ“เธเธฑเธเธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข เน€เธเธทเนเธญเธซเธฒเธฃเธฒเธเธฒเธ—เธฑเนเธเธซเธกเธ”เธ•เนเธญเธซเธเธถเนเธเธฃเธฒเธขเธเธฒเธฃ
            all_price = tmp_price * tmp_qty;
            sum_price = sum_price + tmp_price; //เธฃเธงเธกเธฃเธฒเธเธฒเธ—เธธเธเธฃเธฒเธขเธเธฒเธฃเธ•เนเธญเธซเธเนเธงเธข
            sum_all_price = sum_all_price + all_price; //เธฃเธงเธกเธฃเธฒเธเธฒเธ—เธฑเนเธเธซเธกเธ”
         
            rowData2.fnUpdate(set_number_format(all_price), parseFloat(i), ci_all_price); //update เธฃเธฒเธเธฒเธฃเธงเธกเธ—เธฑเนเธเธซเธกเธ”เนเธ datatable
            //END ADD
            
            sum_cf_qty = sum_cf_qty + tmp_qty;
        }

        $('#sum_recieve_qty').html(set_number_format(sum_cf_qty));
        $('#sum_price_unit').html(set_number_format(sum_price)); //ADD BY POR 2014-01-09 เน€เธเธดเนเธกเนเธซเนเนเธชเธ”เธเธฃเธฒเธเธฒเธฃเธงเธกเธ—เธฑเนเธเธซเธกเธ”เธ•เนเธญเธซเธเนเธงเธข
        $('#sum_all_price').html(set_number_format(sum_all_price)); //ADD BY POR 2014-01-09 เน€เธเธดเนเธกเนเธซเนเนเธชเธ”เธเธฃเธฒเธเธฒเธฃเธงเธกเธ—เธฑเนเธเธซเธกเธ”

    }
    // end function calculate_qty : by kik : 28-10-2013 

</script>
<style>
    #myModal{
        width: 1170px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -600px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */

    }


</style>
<?php
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
if (!isset($remark)) {
    $remark = "";
}
if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
    $is_urgent = false;
} else {
    $is_urgent = true;
}
?>
<div class="content well" style='height:100%'>
    <form class="" method="POST" action="" id="transferStockForm" name="transferStockForm" >
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
                        <input type="text" placeholder="Date Format" id="estDispatchDate" name="estDispatchDate" class="dates"/>
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
                        <input type="text" placeholder="Auto Generate" id="document_no" name="document_no" disabled />
                    </td>
                    <td align="right">
                        Refer External No. :
                    </td>
                    <td align="left">
                        <input type="text" placeholder="Invoice No." id="doc_refer_ext" name="doc_refer_ext" class="required" style="text-transform: uppercase"/>
                    </td>
                    <td align="right">
                        Refer Internal No. :
                    </td>
                    <td align="left">
                        <input type="text" placeholder="Transfer Order No." id="doc_refer_int" name="doc_refer_int" style="text-transform: uppercase"/>
                    </td>                    
                </tr>
                <tr>
                    <td align="right">Invoice No.</td>
                    <td align="left"><?php echo form_input('doc_refer_inv', $doc_refer_inv, 'placeholder="Invoice Number"  style="text-transform: uppercase"'); ?></td>
                    <td align="right">Customs Entry	</td>
                    <td align="left"><?php echo form_input('doc_refer_ce', $doc_refer_ce, 'placeholder="Entry Number"  style="text-transform: uppercase"'); ?></td>
                    <td align="right">BL No.</td>
                    <td align="left"><?php echo form_input('doc_refer_bl', $doc_refer_bl, 'placeholder="Bill Number"  style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Remark</td>
                    <td align="left" colspan="3">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo $remark; ?></TEXTAREA>
                    </td>
                    <td align="right">  </td>
                    <td align="left">
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
    </form>
    <fieldset>
        <legend>Product Detail </legend>
        <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
            
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
                                        <th style='text-align:center;'><?php echo _lang('dispatch_qty'); ?></th>
                                        <th style='text-align:center;'><?php echo _lang('price_per_unit'); ?></th>
                                        <th style='text-align:center;'><?php echo _lang('unit_price'); ?></th>
                                        <th style='text-align:center;'><?php echo _lang('all_price'); ?></th>
                                        <th><?php echo _lang('pallet_code'); ?></th>
                                        <th><?php echo _lang('remark'); ?></th>
                                        <th><?php echo _lang('from_location'); ?></th>
                                        <th><?php echo _lang('del'); ?></th>
                                        <th style="display:none;">Sub Status Code</th>
                                        <th style="display:none;">Unit_Id</th>
                                        <th style="display:none;">Price/Unit ID</th>
                                        <th style="display:none;">Inbound Id</th>   <!--add for edit No. to running number : by kik : 20140226 -->
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                                 <!-- show total qty : by kik : 29-10-2013-->
                                        <tfoot>
                                           <tr>
                                                 <th colspan='10' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                                 <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"></span></th>
                                                 <?php if($price_per_unit):?>
                                                    <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"></span></th>
                                                    <th></th>
                                                    <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"></span></th>
                                                 <?php endif;?>
                                                 <th colspan='8' class ='ui-state-default indent' ></th> <!--add for edit No. to running number : by kik : 20140226-->
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