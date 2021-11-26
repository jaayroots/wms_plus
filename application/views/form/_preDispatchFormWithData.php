<style>
    .disPlayNone{
        display: none;
    }
</style>
<script>

    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Pre-Dispatch';
    var data_table_id_class = '#ShowDataTableForInsert';
    var redirect_after_save = site_url + "/flow/flowPreDispatchList";
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';
    
    $(document).ready(function() {

        // Add By Akkarapol, 20/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
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

        var separator = "<?php echo SEPARATOR; ?>";
        initProductTable();
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
        $("#preDispatchDate").datepicker().keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
            //$('#est_relocate_date').datepicker('hide');
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $("#preDeliveryDate").datepicker().keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
            //$('#est_relocate_date').datepicker('hide');
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $("#estDispatchDate").datepicker({onRender: function(date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }}).on('changeDate', function(ev) {
            //$('#sDate1').text($('#datepicker').data('date'));
            //$('#estDispatchDate').datepicker('hide');
        }).keypress(function(event) {
            event.preventDefault();
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $('#getBtn').click(function() {
            showModalTable();
            $('#defDataTableModal_filter label input[type=text]').unbind('keypress keyup')
                    .bind('keypress keyup', function(e) {
                if (e.keyCode == 13)
                {
                    showModalTable($('#defDataTableModal_filter label input[type=text]').val());
                }
            });
        });

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
//            var allVals = [];
//            $('.modal-body :checked').each(function() {
//                allVals.push($(this).val());
//            });
            if (allVals.length == 0) {
                alert("No Product was selected ! Please select a product or click cancle button to exit.");
                return false;
            }
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '<?php echo site_url() . "/pre_dispatch/showSelectData" ?>', // in here you should put your query 
                data: 'post_val=' + allVals + "&tableName=ShowDataTableForInsert", // here you pass your id via ajax .
                // in php you should use $_POST['post_id'] to get this value 
                success: function(r)
                {
                    $.each(r.product, function(i, item) {
                        var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
                        var Inbound_Id = item.Inbound_Id;
                        $('#ShowDataTableForInsert').dataTable().fnAddData([
                            "new"
                                    , item.product_code
                                    , item.Product_NameEN
                                    , item.Product_Status
                                    , item.Product_lot
                                    , item.Product_Serial
                                    , item.Product_Mfd
                                    , item.Product_Exp
                                    , item.Balance_Qty
                                    , null
                                    , null
                                    , Inbound_Id
                                    , del
                        ]);

                    });

                    initProductTable();
                }
            });
            $('.modal.in').modal('hide');
        });

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
	
        //validate Engine called here.//
        var statusisValidateForm = validateForm();

        if (statusisValidateForm === true) {
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
                var qty = $(cTable[i]).find("td:eq(9)").html();
                //alert(' qty ='+qty);
                if (qty == 'Click Here to edit' || qty == "0" || qty == "") {
                    cells.push($(cTable[i]).find("td:eq(9)").html());
                }
                qty = parseFloat(qty)
                if (qty < 0) {
                    alert('Negative Reserve Qty is not allow');
                    return false;
                }
            }
            var all_qty = cells.length;
            if (all_qty != 0) {
                alert('Please fill all Reserve Qty');
                return false;
            }

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
                    var prod_data = oTable[i].join(separator);
                    var prodItem = document.createElement("input");
                    prodItem.setAttribute('type', "hidden");
                    prodItem.setAttribute('name', "prod_list[]");
                    prodItem.setAttribute('value', prod_data);
                    f.appendChild(prodItem);
                }
                
                global_data_form = $("#frmPreDispatch").serialize();
                var message = "";

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
    function showModalTable() {
        allVals = new Array();
        var product_code = $('#productCode').val();
        var dataSet = {post_val: product_code}
        $('#prdModalval').val(product_code);
        $.post('<?php echo site_url() . "/pre_dispatch/getSelectPreDispatchData" ?>', dataSet, function(data) {
            $(".modal-body").html(data);
            var oTable = $('#defDataTable').dataTable({
                "bJQueryUI": true,
                "bSort": false,
                "oSearch": {"sSearch": product_code},
                "bRetrieve": true,
                "bDestroy": true,
                "sPaginationType": "full_numbers"
            });



        }, "html");
        $('#mymodal').show();

        /*
         //Backup Code Datatable
         var product_code = $('#productCode').val();
         // $('#defDataTableModal_filter label input[type=text]').val(product_code); // <-- add this line for search option
         $.ajax({
         type: 'post',
         url: '<?php echo site_url() . "/pre_dispatch/getSelectPreDispatchData" ?>', // in here you should put your query 
         data: 'post_val=' + product_code + '&tableName=defDataTableModal', // here you pass your id via ajax .
         // in php you should use $_POST['post_id'] to get this value 
         success: function(r)
         {
         $(".modal-body").attr("style", "padding:0px;");
         }
         });
         
         $('#mymodal').show();  // put your modal id 
         */
    }


    function initProductTable() {
        $('#ShowDataTableForInsert').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bRetrieve": true,
            "iDisplayLength"    : 250,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
        }).makeEditable({
            sUpdateURL: '<?php echo site_url() . "/pre_dispatch/saveEditedRecord"; ?>'
                    , sAddURL: '<?php echo site_url() . "/pre_dispatch/saveEditedRecord"; ?>'
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
                {"onblur": 'submit', "cssclass": "required number"},
                {
                    "onblur": 'submit',
                    event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว 
                },
                {"bSearchable": false}, //Inbound_Id
                null
            ]
        });

        var oTable = $('#ShowDataTableForInsert').dataTable();

        //var bVis = oTable.fnSettings().aoColumns[11].bVisible;
        oTable.fnSetColumnVis(11, false);
        oTable.fnDraw(false);
    }

    function removeItem(obj) {
        var index = $(obj).closest("table tr").index();
        $('#ShowDataTableForInsert').dataTable().fnDeleteRow(index);
    }

    function deleteItem(obj) {
        var index = $(obj).closest("table tr").index();
        var data = $('#ShowDataTableForInsert').dataTable().fnGetData(index);
        $('#ShowDataTableForInsert').dataTable().fnDeleteRow(index);
        var f = document.getElementById("frmPreDispatch");
        var prodDelItem = document.createElement("input");
        prodDelItem.setAttribute('type', "hidden");
        prodDelItem.setAttribute('name', "prod_del_list[]");
        prodDelItem.setAttribute('value', data);
        f.appendChild(prodDelItem);
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowPreDispatchList";
            redirect(url)
        }
    }
    function getCheckValue(obj) {
        var isChecked = $(obj).attr("checked");
        if (isChecked) {
            allVals.push($(obj).val());
        } else {
            allVals.pop($(obj).val());
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



</script>
<style>
    #myModal {
        width: 1024px; /* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -512px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
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
?>
<div class="content <?php echo config_item("css_form"); ?>" style='height:100%; width:95%;' >

    <form class="" method="POST" action="" id="frmPreDispatch" name="frmPreDispatch" >
        <fieldset style="margin:0px auto;">
            <legend>Pre-Dispatch Order</legend>
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
                        Dispatch Type :
                    </td>
                    <td>
                        {dispatch_type_select}
                    </td>
                    <td align="right">
                        Est. Dispatch Date :
                    </td>
                    <td align="left">
                        <input type="text" placeholder="Date Format" id="estDispatchDate" name="estDispatchDate" value="<?php echo $est_action_date; ?>" style="text-transform: uppercase"/>
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
						<td style="text-align:left;"></td>
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
                            <div style="width:90%;">
                                &nbsp;&nbsp;&nbsp;&nbsp;Product Code : 
                                <input type="text" name="productCode" id="productCode" class="input-medium" placeholder="Product Code">
                                <a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn">Get Detail</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div id="defDataTable_wrapper" class="dataTables_wrapper" role="grid" style="width:100%;overflow-x: auto;margin:0px auto;">
                                <div style="width:100%;overflow-x: auto;" id="showDataTable"> 
                                    <table cellpadding="2" cellspacing="0" border="0" class="display" id="ShowDataTableForInsert">
                                        <thead>
                                            <tr>
                                                <td>Id</td>
                                                <td>Product Code</td>
                                                <td>Product Name</td>
                                                <td>Product Status</td>
                                                <td>Product Lot</td>
                                                <td>Product Serial</td>
                                                <td>Product Mfd</td>
                                                <td>Product Exp</td>
                                                <td>Balance Qty</td>
                                                <td>Reserve Qty</td>
												<td>Remark</td>
                                                <td style="display:none;">Inbound Id</td>
                                                <td>Del</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                    <?php
                                    if (isset($order_detail_data)) {
                                        $str_body = "";
                                        $j = 1;
                                        foreach ($order_detail_data as $order_column) {
                                            $order_id = $order_column->Order_Id;
                                            $str_body .= "<tr id=\"" . $order_column->Item_Id . "\">";
                                            $str_body .= "<td>" . $j . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Code . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_NameEN . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Status . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                            $str_body .= "<td>" . $order_column->Balance_Qty . "</td>";
                                            $str_body .= "<td>" . $order_column->Reserv_Qty . "</td>";
                                            $str_body .= "<td>" . $order_column->Remark . "</td>";
                                            $str_body .= "<td style=\"display:none;\">" . $order_column->Item_Id . "</td>";
                                            $str_body .= "<td><a ONCLICK=\"deleteItem(this)\" >" . img("css/images/icons/del.png") . "</a></td>";
                                            $str_body .= "</tr>";
                                            $j++;
                                        }
                                        echo $str_body;
                                    }
                                    ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
</div>

<!-- Modal -->
<div style="min-height:500px;padding:5px 10px;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">Transfer Order</h3>
    </div>
    <div class="modal-body">
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
</div>


<?php $this->load->view('element_modal_message_alert'); ?>