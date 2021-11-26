<?php // Create by Ton! 20130709                                      ?>
<SCRIPT>
    
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'UOM';
    var data_table_id_class = '#frm_uom_master';
    var redirect_after_save = site_url + "/uom";
    
    $(document).ready(function() {
        var type = $('#type').val();
        if (type == 'V') {
            $('#btn_clear').hide();
            $('#btn_save').hide();

            $('#ProductCategory_Id').attr("disabled", true);
            $('#Standard_Unit_Id').attr("disabled", true);

            $('.input_text').attr('readonly', 'readonly');
        } else if (type == 'A') {
            $('#btn_clear').show();
        } else if (type == 'E') {
            $('#btn_clear').hide();
            document.getElementById("uom_code").readOnly = true;
        }

        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });

        $('.input_required').keyup(function() {
            if ($(this).val() != '') {
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
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        $("form").validate({
            rules: {
                uom_code: {required: true}
            }
        });

        if ($("form").valid() === true) {
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
        global_sub_module = "save_uom";
        global_data_form = $('#frm_uom_master').serialize();
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
        
//        if (confirm("You want to save the data UOM Master?")) {
//            $.post('<?php echo site_url() . "/uom/save_uom" ?>', $('#frm_uom_master').serialize(), function(data) {
//                console.log(data);
//                var mess = '';
//                switch (data.status) {
//
//                }
////                if (data == "1") {
////                    alert("Save successfully.");
////                    window.location = "<?php echo site_url() ?>/uom";
////                    return true;
////                } else {
////                    alert("Save unsuccessfully.");
////                    $("#btn_save").removeAttr("disabled");
////                    return false;
////                }
//            }, "json");
//        } else {
//            $("#btn_save").removeAttr("disabled");
//        }
    }

    function clearData() {
        var type = $('#type').val();
        $('#parent_id option[value="*"]').attr('selected', 'selected');
        if (type != 'E') {
            $('#uom_code').val('');
        }
        $('.input_text').val('');
    }

    function backToList() {// back to list product master page.
        window.location = "<?php echo site_url() ?>/uom";
    }
</SCRIPT>
<HTML>
    <HEAD>
        <TITLE> UOM Master </TITLE>
        <style>
            .error {
                width:93% !important;
            }
        </style>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frm_uom_master" NAME="frm_uom_master" METHOD='post' >
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="uom_id" name="uom_id" value="<?php echo @$data_uom['id'] ?>"/>
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
                        <FIELDSET  class="well" ><LEGEND>UOM</LEGEND>
                            <TABLE>
                                <TR>
                                    <!--<TD>CTL_M_UOM.parent_id : </TD>-->
                                    <TD>UOM Type : </TD>
                                    <?php $disabled = ($mode == 'V' ? 'disabled="disabled"' : ''); ?>
                                    <TD><?php echo form_dropdown('parent_id', $uom_master_list, @$data_uom['parent_id'], $disabled . ' id="parent_id"') ?></TD>
                                </TR>
                                <TR>
                                    <!--<TD>CTL_M_UOM.code : </TD>-->
                                    <TD>CODE : </TD>
                                    <TD>
                                        <INPUT TYPE="text" CLASS="input_text  required input_required"  ID="uom_code" NAME="uom_code" VALUE="<?php echo @$data_uom['code'] ?>">
                                    </TD>
                                </TR>
                                <TR>
                                    <!--<TD>CTL_M_UOM.active : </TD>-->
                                    <TD>Active : </TD>
                                    <TD>
                                        <?php
                                        if (@$data_uom['active'] != 'N'):
                                            $radio_Y = TRUE;
                                            $radio_N = FALSE;
                                        else:
                                            $radio_Y = FALSE;
                                            $radio_N = TRUE;
                                        endif;
                                        ?>

                                        <?php echo form_radio('uom_active', 'Y', $radio_Y, $disabled); ?>&nbsp;Active&nbsp;&nbsp;
                                        <?php echo form_radio('uom_active', 'N', $radio_N, $disabled); ?>&nbsp;InActive&nbsp;&nbsp;      
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
                                        <!--<TD>CTL_M_UOM_Language.name : </TD>-->
                                        <TD>Name : </TD>
                                        <TD>
                                            <INPUT CLASS="input_text required input_required master_duplicate" TYPE="text"  ID="name-<?php echo $key_language; ?>" data-duplicate="short_text-<?php echo $key_language; ?>" NAME="data[<?php echo $key_language; ?>][name]" VALUE="<?php echo @$data_uom['data'][$key_language]['name'] ?>">
                                        </TD>
                                    </TR>
                                    <TR>
                                        <!--<TD>CTL_M_UOM_Language.short_text : </TD>-->
                                        <TD>Shot Text : </TD>
                                        <TD>
                                            <INPUT CLASS="input_text required input_required" TYPE="text"  ID="short_text-<?php echo $key_language; ?>" NAME="data[<?php echo $key_language; ?>][short_text]" VALUE="<?php echo @$data_uom['data'][$key_language]['short_text'] ?>">
                                        </TD>
                                    </TR>
                                    <TR>
                                        <!--<TD>CTL_M_UOM_Language.description : </TD>-->
                                        <TD>Description : </TD>
                                        <TD>
                                            <INPUT CLASS="input_text" TYPE="text"  ID="description-<?php echo $key_language; ?>" NAME="data[<?php echo $key_language; ?>][description]" VALUE="<?php echo @$data_uom['data'][$key_language]['description'] ?>">
                                            <INPUT TYPE="hidden" ID="lang_id-<?php echo $key_language; ?>" NAME="data[<?php echo $key_language; ?>][id]" VALUE="<?php echo @$data_uom['data'][$key_language]['id'] ?>">
                                            <INPUT TYPE="hidden" ID="language-<?php echo $key_language; ?>" NAME="data[<?php echo $key_language; ?>][language]" VALUE="<?php echo $key_language ?>">
                                        </TD>
                                    </TR>
                                </TABLE>
                            </FIELDSET>
                        </TD>
                    </TR>
                <?php endforeach; ?>
            </TABLE>
        </FIELDSET>
    </TD>
</TR>
</TABLE>
</FORM>
<?php $this->load->view('element_modal_message_alert'); ?>
</BODY>
</HTML>


