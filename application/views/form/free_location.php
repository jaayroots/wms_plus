<script src="<?php echo base_url('js/jquery.multi-select.js'); ?>" type="text/javascript"></script>
<script src="<?php echo base_url('js/jquery.quicksearch.js'); ?>" type="text/javascript"></script>
<script lang="javascript">
    var allVals = new Array();
    var selected_temp = {};
    var flag_last_bottom = 0;
    var flag_click_button_all = false;
    var flag_last_bottom_selected = 0;
    var flag_click_button_all_selected = false;

    $(document).ready(function() {
        getFreeLocation('first');

        $('#select-all').hide();
        $('#deselect-all').hide();

        // Ball
        // Add validate rule
        $("#frmFL").validate({
            rules: {
                //warehouse: {required: true},
                //zone: {required: true},
                putaway_name: {required: true},
                product_status: {required: true},
                product_sub_status: {required: true}
            }
        });

        $("#frmFL").submit(function() {
            var validate_results = $(this).valid();
            var selected_number = $("#location_list").find('option:selected').length;

            if (!validate_results) {
                return false;
            }

            if (selected_number == 0) {
                alert('Please select atleast one location');
                return false;
            }

            if (selected_number > 0 && validate_results) {
                return true;
            }
            else {
                return false;
            }
        });

        // END

        $('#select-all').click(function() {
            $('#location_list').multiSelect('select_all');
            flag_click_button_all = true;
            return false;
        });

        $('#deselect-all').click(function() {
            $('#location_list').multiSelect('deselect_all');
            flag_click_button_all = true;
            return false;
        });

        $('#warehouse').change(function() {
            warehouse_loaded($(this));
        });

        warehouse_loaded($("#warehouse"));

        //EDIT BY POR 2013-11-11 
        function warehouse_loaded(elm) {
            var warehouse = elm.val();
            var zone = $("input[name='Zone_edit']").val(); //ADD BY POR ดูว่า zone ที่ต้องการแก้ไขคืออะไร 

            $('#product').html('Please select Warehouse, Zone');
            if (warehouse != "") {
                $.get("<?php echo site_url(); ?>/putaway/getZoneSelect?w=" + warehouse + "&z=" + zone, function(data) {
                    $('div#zone').html(data);
                    $("#zone").trigger("change"); //ADD BY BALL 2013-11-11 สั่งให้มีการ load zone
                });

                getFreeLocation();
            } else {
                $('#zone').find('option').remove();
                $('#zone').find('select').append('<option value="">Please select warehouse</option>');
            }
            $('#category').val('');
        }
        //$('#warehouse').val(''); //COMMENT BY POR 2013-11-08 because it's make dropdown not set default for selected

        $('#zone').change(function() {
            var w = $('#warehouse').val();
//            var zone = $('select#zone').val();
            var cate = $("input[name='cate_edit']").val(); //ADD BY POR ดูว่า Product Category ที่ต้องการแก้ไขคืออะไร 

//            if (zone != "") {
            if ($('#zone_list').val() !== undefined && $('#zone_list').val() !== "") {
                $.get("<?php echo site_url(); ?>/putaway/getZoneCategory?w=" + w + "&zone=" + $('#zone_list').val() + "&cate=" + cate, function(data) {
                    if (data == 0) {
                        alert("Unavailable Location in this Zone");
                        $('#product').html('Please select Warehouse, Zone ');
                        $('select#zone').val('');
                    } else {
                        $('div#product_cate').html(data);
                        $('#product_cate').show();
                        $('#product_status').show();

                        getFreeLocation();
                    }
                });
            }
        });

        $('#product_cate').change(function() {
            getFreeLocation();
        });

    });

    function bind_scroll_bottom() {
        $('.ms-selectable .ms-list').scrollTop(flag_last_bottom);
        $('.ms-selectable .ms-list').scroll(function() {
            if (flag_click_button_all) {
                flag_click_button_all = false;
            } else {
                if (($('.ms-selectable .ms-list').prop("scrollHeight") - $('.ms-selectable .ms-list').scrollTop()) == $('.ms-selectable .ms-list').height()) {
                    getFreeLocation();
                    flag_last_bottom = $('.ms-selectable .ms-list').scrollTop();
                }
            }
        });
        $('.ms-selection .ms-list').scrollTop(flag_last_bottom_selected);
        $('.ms-selection .ms-list').scroll(function() {
            if (flag_click_button_all_selected) {
                flag_click_button_all_selected = false;
            } else {
                if (($('.ms-selection .ms-list').prop("scrollHeight") - $('.ms-selection .ms-list').scrollTop()) == $('.ms-selection .ms-list').height()) {
                    getFreeLocation();
                    flag_last_bottom_selected = $('.ms-selection .ms-list').scrollTop();
                }
            }
        });
    }

    var old_limit_start;
//        function getFreeLocation(warehouse, zone, product_cate, putaway_id) {
    function getFreeLocation(first) {// Edit by Ton! 20140128
        var g_wh = ''; // warehouse
        var g_zone = ''; // zone_list
        var g_prod_cate = ''; //product_cate
        var g_pa = 0; // putaway_id
        var limit_start = $('#location_list').children().length;
        if(first === 'first'){
            limit_start = 0;
        }

        if ($('#warehouse').val() !== undefined && $('#warehouse').val() !== '') {
            g_wh = $('#warehouse').val();
        }
        if ($('#zone_list').val() !== undefined && $('#zone_list').val() !== '') {
            g_zone = $('#zone_list').val();
        }
        if ($('#product_cate').val() !== undefined && $('#product_cate').val() !== '') {
            g_prod_cate = $('#product_cate').val();
        }
        if ($('#putaway_id').val() !== '') {
            g_pa = $('#putaway_id').val();
        }

        var limit_max = 100;
        if (g_pa != 0) {
            limit_max = 99999;
        }
        if(old_limit_start == limit_start){
            return false;
        }
        old_limit_start = limit_start;

        $('#loading_text').html(' Loading . . . <img src="<?php echo base_url() ?>images/ajax-loader.gif" />');

//        if (warehouse != "" && zone != "") {
//            $('#product').html(' Loading . . . <img src="<?php echo base_url() ?>images/ajax-loader.gif" />');
//            $.get("<?php echo site_url(); ?>/putaway/showFreeLocation?warehouse=" + g_wh + "&zone=" + g_zone + "&product_cate=" + g_prod_cate + "&putaway_id=" + g_pa, function(data) {// Edit by Ton! 20140128
        $.get("<?php echo site_url(); ?>/putaway/showFreeLocation?warehouse=" + g_wh + "&zone=" + g_zone + "&product_cate=" + g_prod_cate + "&putaway_id=" + g_pa + "&limit_start=" + limit_start + "&limit_max=" + limit_max, function(data) {// Edit by Ton! 20140128
            $('#product').html('');
            $('#loading_text').html('');
            if (data.length == 0) {
//                    alert("This Zone not have free location");
                $('#product').html('Please select Warehouse, Zone ');
            } else {
//                    ('#location_list').length = 0;
//console.log(data);
                $.each(data, function(i, item) {
                    var is_exist = $('#location_list option[value="' + item.location_id + '"]').length;
                    if (is_exist == 0) {
                        $selected = (item.selected ? true : false);
                        var opt = $("<option>").attr("value", item.location_id).prop("selected", $selected).html(item.location_code);
                        $('#location_list').append(opt);
                    }
                });

                init_multi_select();
                $('#location_list').multiSelect('refresh');
                $('#location_list').show();
                $('#select-all').show();
                $('#deselect-all').show();

                bind_scroll_bottom();

            }
        }, "json");
    }

    // Multiselect
    function init_multi_select() {
        $('#location_list').multiSelect({
            selectableHeader: "<input type='text' class='search-input' autocomplete='off' placeholder='Type for filter' style='width: 240px;'>",
            selectionHeader: "<input type='text' class='search-input' autocomplete='off' placeholder='Type for filter' style='width: 240px;'>",
            afterInit: function(ms) {
                var that = this,
                        $selectableSearch = that.$selectableUl.prev(),
                        $selectionSearch = that.$selectionUl.prev(),
                        selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                        selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                        .on('keydown', function(e) {
                            if (e.which === 40) {
                                that.$selectableUl.focus();
                                return false;
                            }
                        });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                        .on('keydown', function(e) {
                            if (e.which == 40) {
                                that.$selectionUl.focus();
                                return false;
                            }
                        });
            },
            afterSelect: function(id) {
                this.qs1.cache();
                this.qs2.cache();
                var txt = $('#location_list option[value="' + id[0] + '"]').html();
                selected_temp[id] = txt;
            },
            afterDeselect: function(id) {
                this.qs1.cache();
                this.qs2.cache();
                delete selected_temp[id];
            }

        });
    }
    // End

    function validateForm() {
        $("form").validate({
            rules: {
                warehouse: {required: true},
                zone: {required: true}
            }
        });
        return $("form").valid();
    }

    function scancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/putaway/freeLocationList"; //EDIT BY POR 2013-11-07 แก้ไข freeLocation  เป็น freeLocationList
            redirect(url);
        }
    }


</script>
<?php
//ดึง zone_id ที่ต้องการ edit ออกมา
if (count($data_edit)):
    echo form_hidden('Zone_edit', $data_edit['Zone_Id']);
    echo form_hidden('cate_edit', $data_edit['Product_Category_Id']);
else:
    echo form_hidden('Zone_edit', "");
    echo form_hidden('cate_edit', "");
endif;


if (!isset($data_edit['Warehouse_Id'])) {
    $data_edit['Warehouse_Id'] = "";
}
if (!isset($data_edit['Zone_Id'])) {
    $data_edit['Zone_Id'] = "";
}
if (!isset($data_edit['$shipper_id'])) {
    $data_edit['$shipper_id'] = "";
}
if (!isset($data_edit['Product_Status_Id'])) {
    $data_edit['Product_Status_Id'] = "";
}

if (!isset($data_edit['pro_status'])) {
    $data_edit['pro_status'] = "";
}

if (!isset($data_edit['Product_Sub_Status_Id'])) {
    $data_edit['Product_Sub_Status_Id'] = "";
}

if (!isset($data_edit['pro_substatus'])) {
    $data_edit['pro_substatus'] = "";
}

if (!isset($data_edit['Product_Category_Id'])) {
    $data_edit['Product_Category_Id'] = "";
}

if (!isset($data_edit['pro_catestatus'])) {
    $data_edit['pro_catestatus'] = "";
}

//if (!isset($data_edit['Active'])) {
//    $data_edit['Active'] = "";
//}
if (!isset($data_edit['Remarks'])) {
    $data_edit['Remarks'] = "";
}
if (!isset($data_edit['putaway_name'])) {
    $data_edit['putaway_name'] = "";
}
?>
<FORM ACTION="savePutAwayRule" METHOD="POST" id="frmFL">
    <div class="well">
        <fieldset>
            <legend>&nbsp;Add Product to Free Location&nbsp;</legend>
            <TABLE width="100%">
                <TR>
                    <TD width="150" valign="middle" align="right"><label for="warehouse">Warehouse </label></TD>
                    <TD valign="top">
                        <SELECT NAME="warehouse" ID="warehouse">
                            <OPTION VALUE="">Please select Warehouse</OPTION>
                            <?php
                            foreach ($data['selectWarehouse'] as $warehouse) {
                                $chk = "";
                                if ($data_edit['Warehouse_Id'] == $warehouse['id']):
                                    $chk = "selected";
                                endif;
                                ?>
                                <OPTION VALUE="<?php echo $warehouse['id']; ?>" <?php echo $chk; ?>><?php echo $warehouse['name']; ?></OPTION>
                                <?php
                            }
                            ?>
                        </SELECT>
<!--                        <?php // echo form_checkbox('Active', 1, ($data_edit['Active'] == "Y" ? true : false), "disabled=disabled"); ?>&nbsp;&nbsp;&nbsp;Active&nbsp;&nbsp;&nbsp;-->
                    </TD>
                    <TD valign="middle" align="right"><label for="zone">Zone </label></TD>
                    <TD valign="top"><div id="zone">
                            <SELECT NAME="zone">
                                <OPTION VALUE="">Please select Warehouse</OPTION>
                            </SELECT> 
                        </div>
                    </TD>

                </TR>
                <TR> 
                <INPUT TYPE="hidden" ID="putaway_id" NAME="putaway_id" VALUE="<?php echo $data['putaway_id']; ?>">
                <TD width="150" valign="middle" align="right"><label for="putaway_name">PutAway Name <span class="required">*</span></label></TD>
                <TD colspan="5"><INPUT TYPE="text" ID="putaway_name" NAME="putaway_name" VALUE="<?php echo $data_edit['putaway_name']; ?>"></TD>
                </TR>
                <TR>
                    <TD valign="middle"  align="right"><label for="product_status">Product Status <span class="required">*</span></label></TD>
                    <TD valign="top"><?php echo form_dropdown('product_status', $data['selectStatus'], $data_edit['Product_Status_Id'], 'id=product_status') ?></TD>

                    <TD valign="middle"  align="right"><label for="product_sub_status">Product Sub Status <span class="required">*</span></label></TD>
                    <TD valign="top"><?php echo form_dropdown('product_sub_status', $data['selectSubstatus'], $data_edit['Product_Sub_Status_Id'], 'id=product_sub_status') ?></TD>

                    <TD valign="middle"  align="right"><label for="product_cate">DIW Class</label></TD>
                    <TD  valign="top" width="300">
                        <!--<div id="product_cate" > //COMMENT BY POR 2014-09-19 because can not show category in view
                            <SELECT NAME="product_cate" ID="product_cate">
                                <OPTION VALUE="">Please select Product Category</OPTION>	
                            </SELECT>
                        </div>-->
                        <?php echo form_dropdown('product_cate', $data['selectCategory'], $data_edit['Product_Category_Id'], 'id=product_cate') ?>
                    </TD>
                </TR>
                <TR>
                    <TD valign="middle" align="right"><label for="remark">Remark</label></TD>
                    <TD valign="top" colspan="5"><TEXTAREA ID="remark" NAME="remark" class="span4" rows="3"><?php echo $data_edit['Remarks']; ?></TEXTAREA></TD>
                </TR>
                <TR id="sub_zone" align="top"></TR>  
            </TABLE>
        </fieldset>
        <fieldset>
            <legend>Location List</legend>
            <div id="product" style="margin:5px auto;width:98%;text-align:center;">Please select Warehouse, Zone</div>
            <div id="product" style="margin:5px auto;width:570px;text-align:center;">
                <table width='100%' style='margin-bottom: 10px;'>
                    <tr>
                        <td>
                            <a id="select-all" class="button">Select All</a>
                            <!--<INPUT TYPE="button" class='button dark_blue' VALUE="Select All" id="select-all" name="select-all"/>-->
                        </td>
                        <td>
                            <a id="deselect-all" class="button">De-select All</a>
                            <!--<INPUT TYPE="button" class='button dark_blue' VALUE="De-select All" id="deselect-all" name="select-all"/>-->
                        </td>
                    </tr>
                </table>
                <select multiple="multiple" id="location_list" name="location_list[]" style="display:none"></select>            
            </div>
            <div style="text-align:center;margin:2px auto;height: 35px;">                
                <i id='loading_text'>Loading</i>        
            </div>
    <div align="center" style="text-align:center;margin:2px auto;">  
        <INPUT TYPE="button" class='button dark_blue' VALUE="BACK" id="cancel" name="cancel" ONCLICK="scancel();" />
        <INPUT TYPE="submit" class='button dark_blue' VALUE="SAVE" id="save" name="save"/>
    </div>
</FORM>
