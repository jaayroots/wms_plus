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

            document.getElementById("ProductBrand_Code").readOnly = true;
            document.getElementById("ProductBrand_NameEN").readOnly = true;
            document.getElementById("ProductBrand_NameTH").readOnly = true;
            document.getElementById("ProductBrand_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="ProductBrand_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="ProductBrand_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#ProductBrand_Code').val('');
        $('#ProductBrand_Code').addClass('required');
        $('#ProductBrand_NameEN').val('');
        $('#ProductBrand_NameEN').addClass('required');
        $('#ProductBrand_NameTH').val('');
        $('#ProductBrand_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of product_brand page.
        window.location = "<?php echo site_url() ?>/product_brand";
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var ProductBrandCode = $('#ProductBrand_Code').val();
        var ProductBrandNameEN = $('#ProductBrand_NameEN').val();

        if (ProductBrandCode == "") {
            alert("Please input Product Brand Code.");
            $('#ProductBrand_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(ProductBrandCode)) {
            alert("Product Brand Code must not is special Character.");
            $('#ProductBrand_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (ProductBrandNameEN == "") {
            alert("Please input Product Brand Name En.");
            $('#ProductBrand_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('.div_custom').children('select').size() == 0) {
            validation_in_controllers();
        } else { // check duplicate Custom UOM
            $.each($('.div_custom'), function() {

                var _this = $(this);
                var tmp_in = '';
                var tmp_out = '';
                $.each(_this.children('select'), function() {
                    if ($(this).attr('class') == 'select_custom_uom_in') {
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
        $.post('<?php echo site_url() . '/product_brand/validation' ?>', $("#frmProductBrand").serialize(), function(data) {
            if (data.result === 1) {
                submitProductBrand();
            } else {
                if (data.note === "BRAND_CODE_ALREADY") {
                    alert("ProductBrand Code already exists.");
                    $('#ProductBrand_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitProductBrand() {
        if (confirm("You want to save the data Product Brand?")) {
            $.post('<?php echo site_url() . "/product_brand/save_product_brand" ?>', $('#frmProductBrand').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save Product Brand Master successfully.");
                    window.location = "<?php echo site_url() ?>/product_brand";
                } else {
                    alert("Save Product Brand Master unsuccessfully.");
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
        <TITLE> Product Brand </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmProductBrand" NAME="frmProductBrand" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="ProductBrand_Id" name="ProductBrand_Id" value="<?php echo $ProductBrand_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $ProductBrand_Code ?>"/>
            <FIELDSET class="well"><LEGEND>Product Brand</LEGEND>
                <TABLE>
                    <TR>    
                        <TD>Product Brand Code : </TD>
                        <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="ProductBrand_Code" NAME="ProductBrand_Code" VALUE="<?php echo $ProductBrand_Code ?>"></TD>
                        <TD colspan="2">
                            <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                        </TD>  
                    <TR>
                        <TD>Product Brand Name EN : </TD>
                        <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="ProductBrand_NameEN" NAME="ProductBrand_NameEN" VALUE="<?php echo $ProductBrand_NameEN ?>"></TD>
                        <TD>Product Brand Name TH : </TD>
                        <TD><INPUT TYPE="text" class="string_special_characters-f" ID="ProductBrand_NameTH" NAME="ProductBrand_NameTH" VALUE="<?php echo $ProductBrand_NameTH ?>"></TD>
                    </TR>
                    <TR>
                        <TD>Product Brand Desc:</TD>
                        <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="ProductBrand_Desc" NAME="ProductBrand_Desc" style="resize:none; width:98%;" rows="2"><?php echo $ProductBrand_Desc ?></TEXTAREA></TD>
                </TR>                    
            </TABLE>
            </FIELDSET>

            <!--Add By Akkarapol, 17/01/2014, Add <FIELDSET  class="well"> for manage UOM In Group-->
            <!--comment by kik : cos not use table CTL_M_UOM_Template_Of_Product : 20150704-->
<!--            <FIELDSET  class="well"><LEGEND>UOM In Group</LEGEND>
                <TABLE>                    
                    <?php/*
                    if (!empty($all_uoms)):
                        foreach ($all_uoms as $key_all_uom => $all_uom):
                            $data_checkbox = array(
                                'name' => 'check_uom[]',
                                'id' => 'check_uom-' . $key_all_uom,
                                'value' => $all_uom->id . SEPARATOR . $all_uom->Unit_Id_In . SEPARATOR . $all_uom->Unit_Id_Out,
                            );
                            if (!empty($brand_uom)):
                                if (in_array($all_uom->id, $brand_uom)):
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
                    */?>
                    <TR>
                        <TD colspan="2" id="container_custom_uom"></TD>
                    </TR>
                    <TR>
                        <TD colspan="2">
                            <?/*php if ($mode !== 'V'): ?>    
                                    <a href="javascript:;" onClick="add_custom_uom();">Add Custom UOM In Group</a>    
                            <?php endif; */?>
                        </TD>
                    </TR>
                </TABLE>
            </FIELDSET>            -->
        </FORM>
        <?php
        echo form_dropdown('Standard_Unit', $prodUnitList, NULL, 'id="Standard_Unit" style="display:none;"') // Add By Akkarapol, 15+16/01/2014, Add Template Of UOM for use in another field
        ?>
    </BODY>
</HTML>
