<?php // Create by Ton! 20130709                                                  ?>
<SCRIPT>
    
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'UOM Template';
    var data_table_id_class = '#frm_uom_template_master';
    var redirect_after_save = site_url + "/uom/template_master";
    
    $(document).ready(function() {
        var type = $('#type').val();
        if (type === 'V') {
            $('#btn_clear').hide();
            $('#btn_save').hide();

            $('.input_select').attr("disabled", true);
            $('.template_active').attr("disabled", true);
            $('.dimension_unit').attr("disabled", true);
            $('.input_text').attr("readonly", true);
        } else if (type == 'A') {
            $('#btn_clear').show();
        } else if (type == 'E') {
            $('#btn_clear').hide();
//            document.getElementById("Product_Code").readOnly = true;
//            $('#pradStatus').show();
        }

        $('.required').each(function() {
            if ($(this).attr('class').match(/input_select/g) == null) {
                if ($(this).val() != '') {
                    $(this).removeClass('required');
                }
            } else {
                if ($(this).val() != '*') {
                    $(this).removeClass('required');
                }
            }

        });

        $('.input_required').keyup(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });

        $('.input_select_required').change(function() {
            if ($(this).val() != '*') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });

        $('.master_duplicate').keyup(function() {
            document.getElementById($(this).data('duplicate')).value = $(this).val();
            $('.input_required').each(function() {
                if ($(this).val() != '') {
                    $(this).removeClass('required');
                } else {
                    $(this).addClass('required');

                }
            });
        });

    });

    function checkSpecialCharacterOnForm($str) {
        var iChars = "~`!#$%^&*+=-[]\\\';,/{}|\":<>?";

        if (!$str) {
            return false;
        }

        for (var i = 0; i < $str.length; i++) {
            if (iChars.indexOf($str.charAt(i)) != -1) {
                return false;
            }
        }
        return true;
    }
    
    function cal_cbm(){
        var mul = 1;
        $('.input_of_cbm').each(function(){
            mul *= $(this).val();
        });
        $('#cubic_meters').val(mul);
    }
    
    function isDigit(obj) {
        var strValue = obj.value;
        var strId = obj.id;
        //        var numericReg = /^\d*[0-9](|.\d*[0-9]|,\d*[0-9])?$/;
        var numericReg = /^[0-9]*\.?[0-9]*$/;
        if (strValue != "") {
            if (numericReg.test(strValue) == false) {
                alert("Please Fill Number Only.");
//                setFocus(strId);
                $('#' + strId).focus();
                return false;
            }
        }
         cal_cbm();
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        $("form").validate({
            rules: {
                type_id: {required: true}
                , unit_id: {required: true}
            }
        });

        var chk_required_form = $("form").valid();

        if ($('#type_id').val() == '*') {
            if ($('#unit_id').val() == '*') {
                chk_required_form = false;
            }
        }
        
        if($('#child_id').val()!=0){
            if($('#quantity').val() == 0){
                alert("Please input quantity.");
                $('#quantity').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        if (chk_required_form === true) {
            submitFrm();
        } else {
            alert("Please Check Your Require Information (Red label).");
            $("#btn_save").removeAttr("disabled");
            return false;
        }
    }

    function submitFrm() {
        $("#btn_save").attr("disabled", "disabled");
        var type = $('#type').val();
        
        global_module = "uom";
        global_sub_module = "save_template";
        global_data_form = $('#frm_uom_template_master').serialize();
//        global_action_value = action_value;
//        global_next_state = next_state;
        curent_flow_action = $("#btn_save").data('dialog');
        
        var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
                        $('#div_for_alert_message').html(mess);
                        $('#div_for_modal_message').modal('show').css({
                            'margin-left': function() {
                                return ($(window).width() - $(this).width()) / 2;
                            }
                        });
            $("#btn_save").removeAttr("disabled");
        
//        if (confirm("You want to save the data UOM Template Master?")) {
//            $.post('<?php echo site_url() . "/uom/save_template" ?>', $('#frm_uom_template_master').serialize(), function(data) {
//                if (data == "1") {
//                    alert("Save successfully.");
//                    window.location = "<?php echo site_url() ?>/uom/template_master";
//                    return true;
//                } else {
//                    alert("Save unsuccessfully.");
//                    $("#btn_save").removeAttr("disabled");
//                    return false;
//                }
//            }, "html");
//        } else {
//            $("#btn_save").removeAttr("disabled");
//        }
    }

    function clearData() {
        var type = $('#type').val();
        $('#type_id option[value="*"]').attr('selected', 'selected');
        $('#unit_id option[value="*"]').attr('selected', 'selected');
        $('#child_id option[value="*"]').attr('selected', 'selected');

        $('.input_text').val('');
    }

    function backToList() {// back to list product master page.
        window.location = "<?php echo site_url() ?>/uom/template_master";
    }
</SCRIPT>
<HTML>
    <HEAD>
        <TITLE> UOM Template Master </TITLE>
        <style>
            .error {
                width:93% !important;
            }
        </style>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frm_uom_template_master" NAME="frm_uom_template_master" METHOD='post' >
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="uom_id" name="template_id" value="<?php echo @$data_template['id'] ?>"/>
            <?php
            if (!isset($ProductCategory_Id)) :
                $ProductCategory_Id = "";
            endif;
            if (!isset($Standard_Unit_Id)) :
                $Standard_Unit_Id = "";
            endif;
            if (!isset($PickUp_Rule)) :
                $PickUp_Rule = "";
            endif;
            if (!isset($PutAway_Rule)) :
                $PutAway_Rule = "";
            endif;
            if (!isset($STD_WeightUnit)) :
                $STD_WeightUnit = "";
            endif;
            if (!isset($Dimension_Unit)) :
                $Dimension_Unit = "";
            endif;
            if (!isset($Supplier_Id)) :
                $Supplier_Id = "";
            endif;
            if (!isset($FG_LICSE)) :
                $FG_LICSE = "";
            endif;

            if ((!isset($IsFG)) || ($IsFG != 0)) :
                $IsFG = true;
            else :
                $IsFG = false;
            endif;
            ?>
            <!--Start P'Nook change field layout 16 Aug 2013-->
            <TABLE width='95%' align='center'>
                <TR>
                    <TD>
                        <FIELDSET  class="well" ><LEGEND>UOM Template</LEGEND>
                            <TABLE>
                                <TR>
                                    <!--<TD>CTL_M_UOM_Select.type_id : </TD>-->
                                    <TD>Type CODE : </TD>
                                    <TD><?php echo form_dropdown('type_id', $uom_type_list, @$data_template['type_id'], 'class="input_select required input_select_required" id="type_id"') ?></TD>
                                </TR>
                                <TR>
                                    <!--<TD>CTL_M_UOM_Select.unit_id : </TD>-->
                                    <TD>Unit Code : </TD>
                                    <TD><?php echo form_dropdown('unit_id', $uom_unit_list, @$data_template['unit_id'], 'class="input_select required input_select_required" id="unit_id"') ?></TD>
                                </TR>
                                <TR>
                                    <!--<TD>CTL_M_UOM_Template.child_id : </TD>-->
                                    <TD>Child : </TD>
                                    <TD>
                                        <?php echo form_dropdown('child_id', $uom_template_list, @$data_template['child_id'], 'class="input_select" id="child_id"') ?>
                                    </TD>
                                </TR>
                                <TR>
                                    <!--<TD>CTL_M_UOM_Template.quantity : </TD>-->
                                    <TD>Quantity(per 1 Unit) : </TD>

                                    <TD>
                                        <INPUT TYPE="text" CLASS="input_text"   ID="quantity" NAME="quantity"  VALUE="<?php echo @$data_template['quantity'] ?>">
                                    </TD>
                                </TR>
                                <TR>
                                    <!--<TD>CTL_M_UOM.active : </TD>-->
                                    <TD>Active : </TD>
                                    <TD>
                                        <?php
                                        if (@$data_template['active'] != 'N'):
                                            $radio_Y = TRUE;
                                            $radio_N = FALSE;
                                        else:
                                            $radio_Y = FALSE;
                                            $radio_N = TRUE;
                                        endif;
                                        ?>

                                        <?php echo form_radio('template_active', 'Y', $radio_Y); ?>&nbsp;Active&nbsp;&nbsp;
                                        <?php echo form_radio('template_active', 'N', $radio_N); ?>&nbsp;InActive&nbsp;&nbsp;      
                                    </TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD>
                </TR>
                <?php foreach ($languages as $key_language => $language): ?>
                    <TR>
                        <TD>
                            <FIELDSET  class="well" ><LEGEND><?php echo $language; ?></LEGEND>
                                <TABLE>
                                    <TR>
                                        <!--<TD>CTL_M_UOM_Template_Language.name : </TD>-->
                                        <TD>Name : </TD>
                                        <TD>
                                            <INPUT CLASS="input_text required input_required master_duplicate" TYPE="text"  ID="name-<?php echo $key_language; ?>" data-duplicate="public_name-<?php echo $key_language; ?>" NAME="data[<?php echo $key_language; ?>][name]" VALUE="<?php echo @$data_template['data'][$key_language]['name'] ?>">
                                        </TD>
                                    </TR>
                                    <TR>
                                        <!--<TD>CTL_M_UOM_Template_Language.public_name : </TD>-->
                                        <TD>Public Name : </TD>
                                        <TD>
                                            <INPUT CLASS="input_text required input_required" TYPE="text"  ID="public_name-<?php echo $key_language; ?>" NAME="data[<?php echo $key_language; ?>][public_name]" VALUE="<?php echo @$data_template['data'][$key_language]['public_name'] ?>">
                                        </TD>
                                    </TR>
                                    <TR>
                                        <!--<TD>CTL_M_UOM_Template_Language.description : </TD>-->
                                        <TD>Description : </TD>
                                        <TD>
                                            <INPUT CLASS="input_text" TYPE="text"  ID="description-<?php echo $key_language; ?>" NAME="data[<?php echo $key_language; ?>][description]" VALUE="<?php echo @$data_template['data'][$key_language]['description'] ?>">
                                            <INPUT TYPE="hidden" ID="lang_id-<?php echo $key_language; ?>" NAME="data[<?php echo $key_language; ?>][id]" VALUE="<?php echo @$data_template['data'][$key_language]['id'] ?>">
                                            <INPUT TYPE="hidden" ID="language-<?php echo $key_language; ?>" NAME="data[<?php echo $key_language; ?>][language]" VALUE="<?php echo $key_language ?>">
                                        </TD>
                                    </TR>
                                </TABLE>
                            </FIELDSET>
                        </TD>
                    </TR>
                <?php endforeach; ?>
                    
                    <TR>
                    <TD>
                        <FIELDSET  class="well" ><LEGEND>Measures</LEGEND>
                            <TABLE>
                                
                                <TR>
                                    <!--<TD>CTL_M_UOM_Template.quantity : </TD>-->
                                    <TD>Dimension Unit :  </TD>

                                    <TD>
                                        <?php echo form_dropdown('dimension_unit', $DimensionUnitList, @$data_template['Dimension_Unit'], 'id=dimension_unit , class ="dimension_unit"') ?>
                                    </TD>
                                    
                                    <!--<TD>CTL_M_UOM_Template.child_id : </TD>-->
                                    <TD>Cubic Meters : </TD>
                                    <TD colspan="3">
                                        <INPUT TYPE="text" CLASS="input_text"   ID="cubic_meters" NAME="cbm"  VALUE="<?php echo @$data_template['Cubic_Meters'] ?>">
                                    </TD>
                                </TR>
                                
                                <TR>
                                    <!--<TD>CTL_M_UOM_Select.type_id : </TD>-->
                                    <TD>Width : </TD>
                                    <TD><INPUT TYPE="text"  CLASS="input_of_cbm input_text" ONKEYUP="isDigit(this)" ID="width" NAME="width"  VALUE="<?php echo @$data_template['Width'] ?>"></TD>
                                     <!--<TD>CTL_M_UOM_Select.unit_id : </TD>-->
                                    <TD>Length : </TD>
                                    <TD><INPUT TYPE="text" CLASS="input_of_cbm input_text"  ONKEYUP="isDigit(this)"  ID="length" NAME="length"  VALUE="<?php echo @$data_template['Length'] ?>"></TD>
                                    <!--<TD>CTL_M_UOM_Template.child_id : </TD>-->
                                    <TD>Height : </TD>
                                    <TD>
                                        <INPUT TYPE="text" CLASS="input_of_cbm input_text"   ONKEYUP="isDigit(this)" ID="height" NAME="height"  VALUE="<?php echo @$data_template['Height'] ?>">
                                    </TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD>
                </TR>
                
            </TABLE>
        </FIELDSET>
    </TD>
</TR>
</TABLE>
</FORM>
<?php $this->load->view('element_modal_message_alert'); ?>
</BODY>
</HTML>


