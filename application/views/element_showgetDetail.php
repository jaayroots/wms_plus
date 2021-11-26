<script>
    var item_in_lists;

    function showModalTable() {

        allVals = new Array();
        if (oTable != null) {
            console.log('not null');
            oTable.fnDestroy();
        }

        var config_pallet = <?php echo ($this->config->item('build_pallet') == 1 ? 'true' : 'false')?>;
        var product_code = $('#productCode').val();
        var productStatus_select = $('#productStatus_select').val();
        var productSubStatus_select = $('#productSubStatus_select').val();
        var productLot = $('#productLot').val();
        var productSerial = $('#productSerial').val();
        var productMfd = $('#productMfd').val();
        var productExp = $('#productExp').val();
        var docRefExt = $('#docRefExt').val();
        var getDetailurl;

     //   $("#pallet_detail").hide(); //ADD BY POR 2014-02-18 กำหนดค่าเริ่มต้นให้ซ่อน จะแสดงก็ต่อเมื่อมีการเลือก pallet และ dispatch full

        if(config_pallet == true){
            var palletCode = $('#palletCode').val();
            var palletIsFull = $('[name="palletIsFull"]:checked').val();
            var palletDispatchType = $('[name="palletDispatchType"]:checked').val();
            var chkPallet = 0;

            if($("#chkPallet").is(':checked') ){
                var chkPallet = 1;
            }
        }

        getDetailurl = "<?php echo site_url(); ?>/product_info/getSelectProductModal";

        //EDIT BY POR 2014-02-18 กรณีเป็นแบบ Dispatch Full ให้เรียกใช้  modal_data_table_pallet
        //ที่ต้องแยก datatable เนื่องจากมีการแสดงค่าไม่เหมือนกัน
        if ((config_pallet == true && palletDispatchType == 'FULL') && chkPallet == 1){
            $('#modal_data_table').hide();
            $('#modal_data_table_pallet').show();
            oTable = $('#modal_data_table_pallet').dataTable({
                "bJQueryUI": true,
                "bSort": false,
                "bAutoWidth": false,
                "iDisplayLength": 100,
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": getDetailurl,
                "fnServerData": function(sSource, aoData, fnCallback) {
                    aoData.push(
                    {"name": "productCode_val", "value": product_code},
                    {"name": "productStatus_val", "value": productStatus_select},
                    {"name": "productSubStatus_val", "value": productSubStatus_select},
                    {"name": "productLot_val", "value": productLot},
                    {"name": "productSerial_val", "value": productSerial},
                    {"name": "productMfd_val", "value": productMfd},
                    {"name": "productExp_val", "value": productExp},
                    {"name": "docRefExt_val", "value": docRefExt},
                    {"name": "productFilter_val", "value": item_in_lists}
                    );

                    if(config_pallet == true && chkPallet == 1){
                        aoData.push(
                         {"name": "palletCode_val", "value": palletCode},
                         {"name": "palletIsFull_val", "value": palletIsFull},
                         {"name": "palletDispatchType_val", "value": palletDispatchType},
                         {"name": "chkPallet_val", "value": chkPallet}
                         );
                    }
                    $.getJSON(sSource, aoData, function(json) {
                        fnCallback(json);
                        for (i in allVals) {
                            $('#chkBoxVal' + allVals[i]).prop('checked', true);
                        }
                    });
                },
            });

            //getDetailurl = "<?php echo site_url(); ?>/product_info/getSelectPalletModal";

       }else{
            $('#modal_data_table_pallet').hide();
            $('#modal_data_table').show();
            oTable = $('#modal_data_table').dataTable({
                "bJQueryUI": true,
                "bSort": false,
                "bAutoWidth": false,
                "iDisplayLength": 100,
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": getDetailurl,
                "fnRowCallback" : processRow, // Add for trigger row event
                "fnServerData": function(sSource, aoData, fnCallback) {
                    aoData.push(
                    {"name": "productCode_val", "value": product_code},
                    {"name": "productStatus_val", "value": productStatus_select},
                    {"name": "productSubStatus_val", "value": productSubStatus_select},
                    {"name": "productLot_val", "value": productLot},
                    {"name": "productSerial_val", "value": productSerial},
                    {"name": "productMfd_val", "value": productMfd},
                    {"name": "productExp_val", "value": productExp},
                    {"name": "docRefExt_val", "value": docRefExt},
                    {"name": "productFilter_val", "value": item_in_lists}
                    );

                    if(config_pallet == true && chkPallet == 1){
                        aoData.push(
                         {"name": "palletCode_val", "value": palletCode},
                         {"name": "palletIsFull_val", "value": palletIsFull},
                         {"name": "palletDispatchType_val", "value": palletDispatchType},
                         {"name": "chkPallet_val", "value": chkPallet}
                         );
                    }
                    $.getJSON(sSource, aoData, function(json) {
                        fnCallback(json);
                        for (i in allVals) {
                            $('#chkBoxVal' + allVals[i]).prop('checked', true);
                        }
                    });
                },
                "aoColumnDefs": [
                    {"sClass": "right_text", "aTargets": [10]},
                    {"sClass": "right_text", "aTargets": [11]},
                    {"bVisible": config_pallet, "aTargets": [12] }
                ]
            });

            //getDetailurl = "<?php echo site_url(); ?>/product_info/getSelectProductModal";

       }

        // Edit By Akkarapol, 24/02/2014,set dataTable #modal_data_table_pallet_filter bind search when enter key
        $('#modal_data_table_pallet_filter label input')
        .unbind('keypress keyup')
        .bind('keypress keyup', function(e){
            if (e.keyCode == 13){
              oTable.fnFilter($(this).val());
            }
        });
        // Edit By Akkarapol, 24/02/2014,set dataTable #modal_data_table_pallet_filter bind search when enter key

    }//end function showModalTable

    function getCheckValue(obj,dispatchtype,inbound) {
        var isChecked = $(obj).attr("checked");
        if (isChecked) {
            if(dispatchtype=='FULL'){ //ADD BY POR 2014-02-19 กรณี dispatch full จะนำ inbound_id ทั้งหมด ที่หาได้จาก pallet_id มาแทนค่าใน array
                for(var i in inbound){
                    allVals.push(inbound[i]);
                }
            }else{
                allVals.push($(obj).val()); //EDIT BY POR กำหนดให้ส่ง dispatchtype ไปด้วย ถ้าเลือกเงื่อนไขแบบไม่มี pallet ค่าจะเป็น 0
            }

            dispatch_type = dispatchtype;

        } else {
// Add BY Akkarapol, 28/11/2013, เพิ่มการใช้ฟังก์ชั่น grep เพื่อ return ค่าที่เหลือหลังจากการตัด ค่าที่ไม่ได้เลือกออกไป จะเหลือเฉพาะค่าที่ เลือกแล้วเท่านั้น จะได้ค่าที่ตรงตามต้องการจริงๆ
            allVals = jQuery.grep(allVals, function(value) {
                return value != $(obj).val();
            });
// END Add BY Akkarapol, 28/11/2013, เพิ่มการใช้ฟังก์ชั่น grep เพื่อ return ค่าที่เหลือหลังจากการตัด ค่าที่ไม่ได้เลือกออกไป จะเหลือเฉพาะค่าที่ เลือกแล้วเท่านั้น จะได้ค่าที่ตรงตามต้องการจริงๆ
        }
    }

//

    $("#show_search_pallet").hide();
  <?php if($this->config->item('build_pallet')): ?>

        $('#chkPallet').click(function() {
            $("#show_search_pallet").toggle(this.checked);
        });

    <? endif;?>

</script>
<style>
    #myModal{
        width: 90%!important;	/* SET THE WIDTH OF THE MODAL */
        top:42%!important;
        margin-left: -45%!important;


    }

</style>

<!-- Modal -->
<div style="min-height:70%;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">Product Order Details </h3>
    </div>
    <div class="modal-body">
        <!--Add By Akkarapol, 28/11/2013, เพิ่ม table เข้ามาเพื่อใช้รับ JSON ที่เราทำการ Customize ตัว dataTable ขึ้นมาเอง เพื่อรองรับการทำ performance ในเรื่องของการ load page ทีละ page -->
        <table id="modal_data_table" cellpadding="0" cellspacing="0" border="0" aria-describedby="modal_data_table_info" class="well" style="max-width: none;">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Doc Ref</th>
                    <th><?php echo _lang('product_code'); ?></th>
                    <th><?php echo _lang('product_name'); ?></th>
                    <th><?php echo _lang('product_status'); ?></th>
                    <th><?php echo _lang('product_sub_status'); ?></th>
                    <th><?php echo _lang('location_code'); ?></th>
                    <th><?php echo _lang('lot'); ?></th>
                    <th><?php echo _lang('serial'); ?></th>
                    <th><?php echo _lang('receive_date'); ?></th>
                    <th><?php echo _lang('product_mfd'); ?></th>
                    <th><?php echo _lang('product_exp'); ?></th>
                    <!-- -------------------------------------------------------- -->
                    <th><?php echo _lang('aging'); ?></th>
                    <!-- -------------------------------------------------------- -->
                    <th><?php echo _lang('est_balance_qty'); ?></th>
                    <th><?php echo _lang('balance'); ?></th>
                    <th><?php echo _lang('pallet_code'); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot></tfoot>
        </table>
        <!--ADD BY POR 2014-02-18 สร้าง datatable เพิ่มเติมกรณีแสดงแบบ dispatch full-->
        <table id="modal_data_table_pallet" cellpadding="0" cellspacing="0" border="0" aria-describedby="modal_data_table_info" class="well" style="max-width: none;">
            <thead>
                <tr>
                    <th>Select</th>
                    <th><?php echo _lang('pallet_code'); ?></th>
                    <th><?php echo _lang('pallet_type'); ?></th>
                    <th><?php echo _lang('pallet_name'); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot></tfoot>
        </table>
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

<SCRIPT>
    $(document).ready(function() {

        $('#select_all').click(function() {// Add By Akkarapol
            var cdata = $('#modal_data_table').dataTable();
            console.log(modal_data_table)

            $(cdata.fnGetNodes()).find(':checkbox').each(function() {
                $this = $(this);
                $this.attr('checked', 'checked');
                allVals.push($this.val());
            });
        });

        $('#deselect_all').click(function() {// Add By Akkarapol
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

    });
</SCRIPT>
