<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>

    var conf_pallet = '<?php echo ($conf_pallet) ? true : false; ?>';
    $(document).ready(function() {

    	// Add By Ball
    	// Prevent unwant redirect pages
    	$(window).on('keydown keyup', function(e){

            if (e.which === 116) {
                window.onbeforeunload = null;
            }

            if (e.which === 82 && e.ctrlKey) { // F5 with Ctrl
                window.onbeforeunload = null;
            }
            
    	});
    	
        window.onbeforeunload = function() {
            return "You have not yet saved your work.Do you want to continue? Doing so, may cause loss of your work?";
        };

        // If found key F5 remove unload event!!
        $(document).on('keydown keyup', function(e) {

            if (e.which === 116) {
                window.onbeforeunload = null;
            }

            if (e.which === 82 && e.ctrlKey) { // F5 with Ctrl
                window.onbeforeunload = null;
            }
            
        });
        // Add By Ball    
    
        //call intitial function
        initProductTable();
        createFormEvent();
        
        //datepicker
        $.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());
        if ($.browser.chrome) {
            // alert("this is chrome");
            $("#date_from").datepicker({dateFormat: 'mm/dd/yy'});
            $("#date_to").datepicker({dateFormat: 'mm/dd/yy'});
        }
        else {
            $("#date_from").datepicker({dateFormat: 'mm/dd/yy'});
            $("#date_to").datepicker({dateFormat: 'mm/dd/yy'});
        }

        $("#calulate_btn").click(function() {
            var chkValidate = validateForm();
            if (chkValidate === true) {
                var inputManPower = $("#manPower").val();
                var workingDay = $("#workingDay").val();
                // var totalWorkTodo = $('#showMovement tr').length;//total
                var trax = $('#total').val();
                var jobTrax = Math.ceil((trax / workingDay) / inputManPower);
                //alert("No. Of Job(s) :"+jobTrax);
                $("#workingLoad").val(jobTrax);
                var avgPerMan = trax / jobTrax
                $("#taskAverage").val(Math.ceil(avgPerMan));
                //  alert("AVG. Of Per Man :"+avgPerMan);
            }
        });

        // Export data
        $("#btn_export").click(function(){
			var export_type = $("#export_type").val();
			var export_document = $("#export_document").val();
			var export_order_by = $("input[name='order_by']:checked").val();
            var url = '<?php echo site_url("/countingCriteria/exportCounting/")?>?t=' + export_type + '&d=' + export_document + '&o=' + export_order_by + '&f=<?php echo $flow_id; ?>';
			var new_Tab = window.open (url , '_blank');
			$("#exportFileModal").modal('hide');
		});

    });
    function initProductTable() {
        //var aJaxurl = '<?php echo site_url() . "/countingCriteria/criteriaCountSelectedAjax/"; ?>';
        //var passiveUrl = '<?php echo site_url() . "/countingCriteria/countingCriteriaMovementListShow/"; ?>'
        var flag = '<?php echo $selected_chk; ?>';
        if (flag == 0) {
            $('#showMovement').dataTable({
                "bJQueryUI": true,
                "bSort": false,
                "sAjaxSource": '<?php echo site_url() . "/countingCriteria/criteriaCountSelected/"; ?>', //$selected_chk
                "fnServerParams": function(aoData)
                {
                    aoData.push({"name": "selected_chk", "value": '<?php echo $flow_id; ?>'});
                },
                "fnDrawCallback": function ( settings ) {
         			$.each(settings.aoData, function(a,b){
						console.log(b._aData['1']);
             		});
                    //var api = this.api();
                    //var rows = api.rows( {page:'current'} ).nodes();
                    //var last=null;                   
                    /*api.column(2, {page:'current'} ).data().each( function ( group, i ) {
                        if ( last !== group ) {
                            $(rows).eq( i ).before(
                                '<tr class="group"><td colspan="5">'+group+'</td></tr>'
                            );
         
                            last = group;
                        }
                    } );*/
                },                
                "bDestroy": true,
                "sPaginationType": "full_numbers",
                "sDom": '<"H"lfr>t<"F"ip>',
                "aoColumns": [
                    null,
                    null,
                    {"sClass": "left_text set_title"},
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
                    null,
                    {"sClass": "right_text set_number_format"},
                    {"sClass": "right_text set_number_format"},
                ]

            });
            if(!conf_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
                $('#showMovement').dataTable().fnSetColumnVis(9, false);
            }
            
            $('#showMovement').dataTable().fnSetColumnVis(0, false);
            $('#showMovement').dataTable().fnSetColumnVis(12, false);
            $('#showMovement').dataTable().fnSetColumnVis(13, false);
            
            
            
        }
        else {
            //var data = $("#selected_chk").val();
            $('#showMovement').dataTable({
                "bJQueryUI": true,
                "bSort": true,
                "sAjaxSource": '<?php echo site_url() . "/countingCriteria/criteriaCountSelectedAjax/"; ?>',
                "fnServerParams": function(aoData)
                {
                    aoData.push({"name": "selected_chk", "value": flag});
                },
                "bDestroy": true,
                "sPaginationType": "full_numbers",
                "sDom": '<"H"lfr>t<"F"ip>'
           });
			//calculate_qty();
            $('#showMovement').dataTable().fnSetColumnVis( 5, false );
            $('#showMovement').dataTable().fnSetColumnVis( 6, false );
        }


        setTimeout(function() {
            set_title_of_td();
            calculate_qty();
            set_hover();
        }, 500);


    }
    function postRequestAction(module, sub_module, action_value, next_state) {
        $("#calulate_btn").trigger('click');
        //validate Engine called here.//
        var statusisValidateForm = validateForm();
        //alert(statusisValidateForm);
        if (statusisValidateForm === true) {
            var rowData = $('#showMovement').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Order Detail");
                return false;
            }
            if (confirm("Are you sure to do following action : " + action_value + "?")) {
                //handmade call data from datatable.//    
                //getValueFromTableData();

                var f = document.getElementById("frmCountingCriteriaStep2");
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

                var oTable = $('#showMovement').dataTable().fnGetData();

                for (i in oTable) {

                    //ADD BY POR 2013-12-03 เอา comma ออกก่อนส่งค่าไปบันทึก
                    // Remove for temp
                    //oTable[i][11] = oTable[i][11].replace(/\,/g, '');
                    //oTable[i][12] = oTable[i][12].replace(/\,/g, '');
                    //END ADD

                    var prodItem = document.createElement("input");
                    prodItem.setAttribute('type', "hidden");
                    prodItem.setAttribute('name', "prod_list[]");
                    prodItem.setAttribute('value', oTable[i]);
                    f.appendChild(prodItem);
                }
                $("#workingLoad").removeAttr("disabled");
                $("#taskAverage").removeAttr("disabled");

                var data_form = $("#frmCountingCriteriaStep2").serialize();
                var message = "";

                //alert("module:"+module);
                //alert("module:"+sub_module);


                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
                    // alert("Update Complete,Current Status : " + data.status);
                    switch (data.status) {
                        case 'C001':
                            message = "Save Criteria Counting Complete";
                            break;
                        case 'C002':
                            message = "Confirm Criteria Counting Complete";
                            break;
                        case 'C003':
                            message = "Approve Criteria Counting Complete";
                            break;
                        case 'C004':        // add by kik : 08-01-2013
                            window.onbeforeunload = null;
                            message = "Reject Criteria Counting Complete";
                            break;
                        case 'C005':        // add by kik : 08-01-2013
                            window.onbeforeunload = null;
                            message = "Reject and Return Criteria Counting Complete";
                            break;
                    }
                    alert(message);
                    url = "<?php echo site_url(); ?>/countingCriteria/";
                    redirect(url);
                }, "json");
            }
        }
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }
    function validateForm() {

        //validate engine 
        var status;
        $("form").each(function() {
            $(this).validate({
                rules: {
                    manPower: {required: true, number: true, min: 1},
                    workingLoad: {
                        required: true,
                        number: true,
                        min: 1
                    },
                    taskAverage: {
                        required: true,
                        number: true,
                        min: 1
                    },
                    workingDay: {
                        required: true,
                        number: true,
                        min: 1
                    },
                    txtProductCode: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    txtfrom: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    txtto: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    aging: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    date_from: {
                        required: true,
                        dates: true

                    },
                    date_to: {
                        required: true,
                        dates: true

                    },
                    top: {
                        required: true,
                        number: true,
                        min: 0
                    }
                }
            });
            $(this).valid();
            status = $(this).valid();
        });
        return status;

        //end of validate engine
    }

    function createFormEvent() {//form selection//

        var chk = $("input[name=conditionDetail]");
        var chkChecked = $("input[name=conditionDetail]:checked");
        var chkValue = '<?php echo $conditionDetail; ?>';
        //alert(chkValue);
        //default select hilight
        chk.each(function() {
            $(this).closest('tr').find("select").attr("disabled", "disabled");
            $(this).closest('tr').find("input[type=text]").attr("disabled", "disabled");
            if ($(this).val() == chkValue) {
                $(this).attr("checked", "checked");
                //intitial disabled and hilight
                $(this).closest('tr').attr('style', 'background-color:#8fb0bc;');
                //make other rows display:none;
                $(this).closest('tr').siblings("tr  .hide_rows").attr("style", "display:none");
                //  $(this).closest('tr').find("input[type=text]").removeAttr("disabled","disabled");
                //  $(this).closest('tr').find("style").removeAttr("disabled","disabled");
            }
        });

        //check that what type was selected 
        chk.change(function() {
            // alert($(this).val()); 
            var allRowInput = $(this).closest('tr');
            allRowInput.each(function() {//loop though a radio choice!  
                //set itself to be selector//
                $(this).attr('style', 'background-color:#8fb0bc;');
                $(this).find("input[type=text]").removeAttr("disabled", "disabled");
                $(this).find("select").removeAttr("disabled", "disabled");

                //clear value in non - selected // 
                $(this).siblings(this).find("input[type=text]").val("");
                $(this).siblings(this).find("select").val("");

                //disabled other //
                $(this).siblings(this).find("input[type=text]").attr("disabled", "disabled");
                $(this).siblings(this).find("select").attr("disabled", "disabled");

                //make other back to normal//
                $(this).siblings(this).removeAttr('style', 'background-color:#8fb0bc;');

            });
        });
    }


    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
			window.onbeforeunload = null;
            url = "<?php echo site_url(); ?>/countingCriteria/";
            redirect(url);
        }
    }

// Add BY Akkarapol, 19/12/2013, เพิ่มฟังก์ชั่น set_title_of_td สำหรับเซ็ตค่า title ให้กับ td ของ Product Name โดยเฉพาะ เพื่อให้สามารถใช้งาน tooltip ได้
    function set_title_of_td() {
        $('.set_title').each(function() {
            $(this).attr('title', $(this).text());
        });
    }
// END Add BY Akkarapol, 19/12/2013, เพิ่มฟังก์ชั่น set_title_of_td สำหรับเซ็ตค่า title ให้กับ td ของ Product Name โดยเฉพาะ เพื่อให้สามารถใช้งาน tooltip ได้

// Add By Akkarapol, 19/12/2013, เพิ่ม ฟังก์ชั่น set_hover สำหรับ BIND ให้ $('#table_name tbody tr td[title]').hover แสดง tooltip เวลา Mouse Over และ ซ่อน tooltip เวลาที่ Mouse Out
function set_hover(){   
        $('#showMovement tbody tr td.set_title').hover(function() {
            
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
}
// END Add By Akkarapol, 19/12/2013, เพิ่ม ฟังก์ชั่น set_hover สำหรับ BIND ให้ $('#table_name tbody tr td[title]').hover แสดง tooltip เวลา Mouse Over และ ซ่อน tooltip เวลาที่ Mouse Out

    // Add function calculate_qty : by kik : 24-10-2013 
    function calculate_qty() {
        var rowData = $('#showMovement').dataTable().fnGetData();
        var num_row = rowData.length;
        var sum_reserv_qty = 0;
        var sum_confirm_qty = 0;
        for (i in rowData) {
            var tmp_qty = 0;
            var confirm_qty = 0;

            tmp_qty = parseFloat(rowData[i]['13'].replace(/\,/g, '')); //Edit BY POR 2013-12-02 แก้ไขให้นำ comma ออก และแก้ไข parseInt เป็น parseFloat เพื่อ
            confirm_qty = parseFloat(rowData[i]['14'].replace(/\,/g, '')); //Edit BY POR 2013-12-02 แก้ไขให้นำ comma ออก และแก้ไข parseInt เป็น parseFloat เพื่อ

            if (!($.isNumeric(tmp_qty))) {
                tmp_qty = 0;
            }

            if (!($.isNumeric(confirm_qty))) {
                confirm_qty = 0;
            }

            sum_reserv_qty = sum_reserv_qty + tmp_qty;
            sum_confirm_qty = sum_confirm_qty + confirm_qty;
        }
        $('#sum_all_qty').html(set_number_format(sum_reserv_qty));
        $('#sum_confirm_qty').html(set_number_format(sum_confirm_qty));
        
    }
    
    // Function Export
    function exportFile(type, document) {
		$('#exportFileModal').modal('show');
		$('#exportFileModal').on('shown.bs.modal', function (e) {
			$("#export_type").val(type);
			$("#export_document").val(document);
		})
	}
</script>

<div class="well" style='height:100%'>
    <!--////check value of value section////-->
    <?php
    //shorthand if
    $is_value1 = (isset($value1) ? $value1 : "");
    $is_value2 = (isset($value2) ? $value2 : "");
    $is_value3 = (isset($value3) ? $value3 : "");
    ?>

    <!--////end of value section////-->
    <form class="" method="POST" action="" id="frmCountingCriteriaStep2" name="frmCountingCriteriaStep2" >
        <?php if (!isset($listCounting)) { ?>
            <label style="font-weight: bold;">Counting Criteria Step 2</label>
        <?php } else { ?>
            <label style="font-weight: bold;"><?php echo $currentstep . " "; ?>Counting Criteria</label>
        <? } ?>
        <div id="content">
            <?php if (!isset($listCounting)) { ?>

                <fieldset>
                    <legend>Condition Detail</legend>
                    <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
                        <tr class="hide_rows">
                            <td style="text-align:left; width:15%; vertical-align: middle; padding-left: 20px;">
                                <input type="radio" name="conditionDetail" id="product_code" value="3"> <?php echo _lang('product_code') ?>
                            </td> 
                            <td style="text-align:right;">
                                <?php echo _lang('product_code') ?> : 
                            </td>
                            <td style="text-align:left;">
                                <input value="<?php echo $is_value1; ?>" type="text" name="txtProductCode" id="txtProductCode" placeholder="<?php echo _lang('product_code') ?>" class="required">
                            </td> 
                            <td style="text-align:right;">

                            </td> 
                            <td style="text-align:left;">

                            </td> 
                            <td style="text-align:right;">

                            </td>
                            <td style="text-align:left;">

                            </td>
                        </tr>
                        <tr class="hide_rows">
                            <td style="text-align:left; width:15%; vertical-align: middle; padding-left: 20px;">
                                <input type="radio" name="conditionDetail" id="movement" value="0" checked="checked"> Movement 
                            </td> 
                            <td style="text-align:right;">
                                From : 
                            </td>
                            <td style="text-align:left;">
                                <input type="text" value="<?php echo $is_value1; ?>" name="txtfrom" id="txtfrom" placeholder="From" class="required number">
                            </td> 
                            <td style="text-align:right;">
                                To :
                            </td> 
                            <td style="text-align:left;">
                                <input type="text" value="<?php echo $is_value2; ?>" name="txtto" id="txtto" placeholder="To" class="required number"> 
                            </td> 
                            <td style="text-align:right;">

                            </td>
                            <td style="text-align:left;">

                            </td>
                        </tr>
                        <tr class="hide_rows">
                            <td style="text-align:left; width:15%; vertical-align: middle; padding-left: 20px;">
                                <input type="radio" name="conditionDetail" id="deadstock" value="1"> Dead Stock 
                            </td> 
                            <td style="text-align:right;">
                                Condition : 
                            </td>
                            <td style="text-align:left;">
                                <Select name="selectOperand" id="selectOperand" class="required warn">
                                    <option value="-1" > -- Please select -- </option>
                                    <option value="=" <?php
                                    if ($is_value1 == "=") {
                                        echo "selected='selected' ";
                                    }
                                    ?> > = </option>
                                    <option value=">=" <?php
                                    if ($is_value1 == ">=") {
                                        echo "selected='selected' ";
                                    }
                                    ?>> >= </option>
                                    <option value="<=" <?php
                                            if ($is_value1 == "<=") {
                                                echo "selected='selected' ";
                                            }
                                            ?>> <= </option>
                                    <option value=">" <?php
                                if ($is_value1 == ">") {
                                    echo "selected='selected' ";
                                }
                                ?> > > </option>
                                    <option value="<" <?php
                                if ($is_value1 == "<") {
                                    echo "selected='selected' ";
                                }
                                ?>> < </option>
                                </select>
                            </td> 
                            <td style="text-align:right;">
                                Aging : 
                            </td> 
                            <td style="text-align:left;">
                                <input type="text" value="<?php echo $is_value2; ?>" name="aging" id="aging" placeholder="Aging" class="required"> 
                            </td> 
                            <td style="text-align:right;">

                            </td>
                            <td style="text-align:left;">

                            </td>
                        </tr>
                        <tr class="hide_rows">
                            <td style="text-align:left; width:15%; vertical-align: middle; padding-left: 20px;">
                                <input type="radio" name="conditionDetail" id="topmove" value="2"> Top Movement 
                            </td> 
                            <td style="text-align:right;">
                                Data From : 
                            </td>
                            <td style="text-align:left;">
                                <input type="text" value="<?php echo $is_value1; ?>" name="date_from" id="date_from" placeholder="Date From" class="required dates">
                            </td> 
                            <td style="text-align:right;">
                                Date To : 
                            </td> 
                            <td style="text-align:left;">
                                <input type="text" value="<?php echo $is_value2; ?>" name="date_to" id="date_to" placeholder="Date To" class="required dates"> 
                            </td> 
                            <td style="text-align:right;">
                                Top : 
                            </td>
                            <td style="text-align:left;">
                                <input type="text" value="<?php echo $is_value3; ?>" name="top" id="top" placeholder="Top" class="required number"> 
                            </td>
                        </tr>
                        <tr >
                            <td style="text-align:left; width:15%; vertical-align: middle; padding-left: 20px;">
                                &nbsp;
                            </td> 
                            <td style="text-align:right">Working Day</td>
                            <td style="text-align:left">
                                <input type="text" name="workingDay" id="workingDay" value="">
                            </td>
                            <td style="text-align:right"> Man Power / Work </td>
                            <td style="text-align:left">
                                <input type="text" name="manPower" id="manPower" value="">
                            </td>
                            <td style="text-align:right"></td>
                            <td style="text-align:left"></td>
                        </tr>
                        <tr >
                            <td style="text-align:left; width:15%; vertical-align: middle; padding-left: 20px;">
                                &nbsp;
                            </td> 
                            <td style="text-align:right">Working Load</td>
                            <td style="text-align:left"><input type="text" name="workingLoad" id="workingLoad" value="" disabled></td>
                            <td style="text-align:right">Task Average</td>
                            <td style="text-align:left"><input type="text" name="taskAverage" id="taskAverage" value="" disabled></td>
                            <td style="text-align:right"></td>
                            <td style="text-align:left"></td>
                        </tr>
                    </table>
                    <table style="width:100%; border:0px; margin:0px auto;"> 
                        <tr>
                            <td colspan="100%;" style="text-align:center;">
                                <input type="button" id="calulate_btn" class="btn btn-primary" value="Calulate" style="font-weight: bold;">
                            </td>
                        </tr>
                    </table>
                </fieldset>
<?php } ?>
            <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
                <tr>
                    <td colspan="2">
                        <div id="defDataTable_wrapper_top" class="dataTables_wrapper" role="grid" style="width:100%;overflow-x: auto;margin:0px auto;">
                            <div style="width:100%;overflow-x: auto;" id="showDataTableTop"> 
                                <table cellpadding="2" cellspacing="0" border="0" class="display" id="showMovement">
                                    <thead>
                                        <tr>
                                            <th><?php echo _lang('no'); ?></th>
                                            <th><?php echo _lang('product_code'); ?></th>
                                            <th><?php echo _lang('product_name'); ?></th>
                                            <th><?php echo _lang('lot'); ?></th>
                                            <th><?php echo _lang('serial'); ?></th>
                                            <th><?php echo _lang('product_mfd'); ?></th>
                                            <th><?php echo _lang('product_exp'); ?></th>
                                            <th><?php echo _lang('product_status'); ?></th>
                                            <th><?php echo _lang('location_code'); ?></th>
                                            <th><?php echo _lang('pallet_code'); ?></th>
                                            <th><?php echo _lang('booked'); ?></th>
                                            <th><?php echo _lang('pre_dispatch'); ?></th>
                                            <th>Id</th>
                                            <th>Actual_Location_Id</th>   
                                            <th><?php echo _lang('system'); ?></th>
                                            <th><?php echo _lang('physical'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <!-- show total qty : by kik : 24-10-2013-->
                                    <tfoot>
                                        <tr>
                                            <th colspan="14" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                                            <th class ='ui-state-default indent'  style='text-align: right;'><span  id="sum_all_qty"><?php echo set_number_format(@$sumQty); ?></span></th>
                                            <th class ='ui-state-default indent'  style='text-align: right;'><span  id="sum_confirm_qty"><?php echo set_number_format(@$sumQty); ?></span></th>                                            
                                        </tr>
                                    </tfoot>
                                    <!-- end show total qty : by kik : 24-10-2013-->                                        
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <input type="hidden" name="queryText" id="queryText" value=""/>
        <input type="hidden" name="search_param" id="search_param" value=""/>
        <input type="hidden" name="total" id="total" value="{total}"/>
        <input type="hidden" name="counting_type" id="counting_type" value="CT02">

<?php echo form_hidden('process_id', $process_id); ?>
<?php echo form_hidden('present_state', $present_state); ?>

<?php
if (isset($flow_id)) {
    echo form_hidden('flow_id', $flow_id);
}
?>
	<!--<input type="hidden" name="token" value="<?php //echo $token?>" />-->
    </form>
    <div>

        <!-- Modal -->
        <div style="min-height:500px;padding:5px 10px;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <!--    <form action="" method="post">-->

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                <h3 id="myModalLabel">Transfer Order</h3>
            </div>
            <div class="modal-body">

                <!-- // working area-->
            </div>
            <div class="modal-footer">
                <div style="float:left;">
                    <input class="btn red" value="Select All" type="button" id="select_all">
                    <input class="btn red" value="Deselect All" type="button" id="deselect_all">
                </div>
                <div style="float:right;">
                    <input class="btn btn-primary" value="Select" type="submit" id="search_submit">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                </div>
            </div>
            <!--    </form>-->
        </div>

        <!-- Modal -->
        <div style="min-height:200px;padding:5px 10px;" id="exportFileModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                <h3 id="myModalLabel">Please select order type</h3>
            </div>
            <div class="modal-body">
                <!-- // Content will put here -->
				<div class="form-group">
				    <div class="col-sm-offset-2 col-sm-10">
				      <div class="radio">
				        <label>
				          <input type="radio" name="order_by" value="location" checked/> Order by Location Code
				        </label>
				      </div>
				      <div class="radio">
				        <label>
				          <input type="radio" name="order_by" value="sku" /> Order By SKU
				        </label>
				      </div>
				    </div>
				  </div>
            </div>
            <div class="modal-footer">
                <div>
                    <input class="btn btn-primary" value="Export" type="submit" id="btn_export">
                    <input type="hidden" id="export_type" value="" >
                    <input type="hidden" id="export_document" value="" >                    
                </div>
            </div>
        </div>