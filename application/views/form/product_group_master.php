<?php
/*
 * Create by Ton! 20131209
 */
?>
<SCRIPT>
    $(document).ready(function() {
<?php if ($Active == true): ?>
            $("#Active").prop("checked", true);
<?php else: ?>
            $("#Active").prop("checked", false);
<?php endif; ?>

<?php if ($mode == 'E'): ?>
            $('#btn_save').show();
            $('#btn_clear').hide();
<?php elseif ($mode == 'A'): ?>
            $('#btn_clear').show();
            document.getElementById("Active").setAttribute("checked", "checked");
<?php else: ?>
            $('#btn_save').hide();
            $('#btn_clear').hide();

            document.getElementById("ProductGroup_Code").readOnly = true;
            document.getElementById("ProductGroup_NameEN").readOnly = true;
            document.getElementById("ProductGroup_NameTH").readOnly = true;
            document.getElementById("ProductGroup_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="ProductGroup_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="ProductGroup_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#ProductGroup_Code').val('');
        $('#ProductGroup_Code').addClass('required');
        $('#ProductGroup_NameEN').val('');
        $('#ProductGroup_NameEN').addClass('required');
        $('#ProductGroup_NameTH').val('');
        $('#ProductGroup_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of product_group page.
        window.location = "<?php echo site_url() ?>/product_group";
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var ProductGroupCode = $('#ProductGroup_Code').val();
        var ProductGroupNameEN = $('#ProductGroup_NameEN').val();

        if (ProductGroupCode === "") {
            alert("Please input Product Group Code.");
            $('#ProductGroup_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(ProductGroupCode)) {
            alert("Product Group Code must not is special Character.");
            $('#ProductGroup_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (ProductGroupNameEN === "") {
            alert("Please input Product Group Name En.");
            $('#ProductGroup_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('.div_custom').children('select').size() === 0) {
            validation_in_controllers();
        } else { // check duplicate Custom UOM
            $.each($('.div_custom'), function() {

                var _this = $(this);
                var tmp_in = '';
                var tmp_out = '';
                $.each(_this.children('select'), function() {
                    if ($(this).attr('class') === 'select_custom_uom_in') {
                        tmp_in = $("option:selected", $(this)).val();
                    } else {
                        tmp_out = $("option:selected", $(this)).val();
                    }
                });

                var dataSet = {
                    in: tmp_in,
                    out: tmp_out,
                    ProductGroup_Id: $('#ProductGroup_Id').val()
                }
                $.post('<?php echo site_url() . "/uom/ajax_chk_custom_uom" ?>', dataSet, function(data) {
                    if (data['status'] == 'C001') {
                        validation_in_controllers();
                    } else if (data['status'] == 'E001') {
                        alert(data['error_msg']);
                        $("#btn_save").removeAttr("disabled");
                        return;
                    }
                }, "json");

            });
        }

    }

    function validation_in_controllers() {// Add by Ton! 20140306
        $.post('<?php echo site_url() . '/product_group/validation' ?>', $("#frmProductGroup").serialize(), function(data) {
            if (data.result === 1) {
                submitProductGroup();
            } else {
                if (data.note === "GROUP_CODE_ALREADY") {
                    alert("ProductGroup Code already exists.");
                    $('#ProductGroup_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitProductGroup() {
        if (confirm("You want to save the data Product Group?")) {
            $.post('<?php echo site_url() . "/product_group/save_product_group" ?>', $('#frmProductGroup').serialize(), function(dataSave) {
                if (dataSave === "1") {
                    alert("Save Product Group Master successfully.");
                    window.location = "<?php echo site_url() ?>/product_group";
                } else {
                    alert("Save Product Group Master unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                }
                return;
            });
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }
    
    function add_custom_uom() { // function for append new child in container_custom_uom when need Custom UOM
        var set_id = $('#container_custom_uom').children().length;
        var Standard_Unit = $('#Standard_Unit').html();
        var str = '<div class="div_custom" id="div_custom-' + set_id + '">In(<select class="select_custom_uom_in" name="custom_uom[' + set_id + '][in]">' + Standard_Unit + '</select>) | Out(<select class="select_custom_uom_out" name="custom_uom[' + set_id + '][out]">' + Standard_Unit + '</select>) <a href="javascript:;" onClick="$(\'#div_custom-' + set_id + '\').remove();"> Del </a> </div>';
        $('#container_custom_uom').append(str);
    }

</SCRIPT>
<HTML>
    <HEAD>
        <TITLE> Product Group </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmProductGroup" NAME="frmProductGroup" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="ProductGroup_Id" name="ProductGroup_Id" value="<?php echo $ProductGroup_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $ProductGroup_Code ?>"/>
            <FIELDSET class="well"><LEGEND>Product Group</LEGEND>
                <TABLE>
                    <TR>    
                        <TD>Product Group Code : </TD>
                        <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="ProductGroup_Code" NAME="ProductGroup_Code" VALUE="<?php echo $ProductGroup_Code ?>"></TD>
                        <TD colspan="2">
                            <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                        </TD>  
                    <TR>
                        <TD>Product Group Name EN : </TD>
                        <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="ProductGroup_NameEN" NAME="ProductGroup_NameEN" VALUE="<?php echo $ProductGroup_NameEN ?>"></TD>
                        <TD>Product Group Name TH : </TD>
                        <TD><INPUT TYPE="text" class="string_special_characters-f" ID="ProductGroup_NameTH" NAME="ProductGroup_NameTH" VALUE="<?php echo $ProductGroup_NameTH ?>"></TD>
                    </TR>
                    <TR>
                        <TD>Product Group Desc:</TD>
                        <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="ProductGroup_Desc" NAME="ProductGroup_Desc" style="resize:none; width:98%;" rows="2"><?php echo $ProductGroup_Desc ?></TEXTAREA></TD>
                    </TR>                    
                </TABLE>
            </FIELDSET>

            <!--Add By Akkarapol, 15+16/01/2014, Add <FIELDSET  class="well"> for manage UOM In Group-->
            <FIELDSET  class="well"><LEGEND>UOM In Group</LEGEND>
                <TABLE>                    
                    <?php
                    if (!empty($all_uoms)):
                        foreach ($all_uoms as $key_all_uom => $all_uom):
                            $data_checkbox = array(
                                'name' => 'check_uom[]',
                                'id' => 'check_uom-' . $key_all_uom,
                                'value' => $all_uom->id . SEPARATOR . $all_uom->Unit_Id_In . SEPARATOR . $all_uom->Unit_Id_Out
                            );
                            if (!empty($group_uom)):
                                if (in_array($all_uom->id, $group_uom)):
                                    $data_checkbox['checked'] = TRUE;
                                endif;
                            endif;
                            if ($mode === 'V'):
                                $data_checkbox['disabled'] = TRUE;
                            endif;
                            ?>
                                            <TR>
                                                <TD width="15">
                                    <?php echo form_checkbox($data_checkbox); ?>
                                                </TD>
                                                <TD>
                                                    <label for="check_uom-<?php echo $key_all_uom; ?>">
                                                        In(<?php echo $all_uom->Unit_Value_In; ?>) | Out(<?php echo $all_uom->Unit_Value_Out; ?>)                                
                                                    </label>
                                                </TD>
                                            </TR>
                            <?php
                        endforeach;
                    endif;
                    ?>
                    <TR>
                        <TD colspan="2" id="container_custom_uom"></TD>
                    </TR>
                    <TR>
                        <TD colspan="2">
                            <?php if ($mode !== 'V'): ?>   
                                        <a href="javascript:;" onClick="add_custom_uom();">Add Custom UOM In Group</a>
                            <?php endif; ?>
                        </TD>
                    </TR>
                </TABLE>
            </FIELDSET>
            
        </FORM>
        
        <?php
        echo form_dropdown('Standard_Unit', $prodUnitList, NULL, 'id="Standard_Unit" style="display:none;"') // Add By Akkarapol, 15+16/01/2014, Add Template Of UOM for use in another field
        ?>
        
        
    </BODY>
</HTML>
