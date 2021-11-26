<?php
/*
 * Create by Ton! 20131206
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

            document.getElementById("AddressType_Code").readOnly = true;
            document.getElementById("AddressType_NameEN").readOnly = true;
            document.getElementById("AddressType_NameTH").readOnly = true;
            document.getElementById("AddressType_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="AddressType_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="AddressType_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#AddressType_Code').val('');
        $('#AddressType_Code').addClass('required');
        $('#AddressType_NameEN').val('');
        $('#AddressType_NameEN').addClass('required');
        $('#AddressType_NameTH').val('');
        $('#AddressType_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of address_type page.
        window.location = "<?php echo site_url() ?>/address_type";
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var AddressTypeCode = $('#AddressType_Code').val();
        var AddressTypeNameEN = $('#AddressType_NameEN').val();

        if (AddressTypeCode == "") {
            alert("Please input AddressType Code.");
            $('#AddressType_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(AddressTypeCode)) {
            alert("AddressType Code must not is special Character.");
            $('#AddressType_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (AddressTypeNameEN == "") {
            alert("Please input AddressType Name En.");
            $('#AddressType_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140306
        $.post('<?php echo site_url() . '/address_type/validation' ?>', $("#frmAddressType").serialize(), function(data) {
            if (data.result === 1) {
                submitAddressType();
            } else {
                if (data.note === "ADD_CODE_ALREADY") {
                    alert("AddressType Code already exists.");
                    $('#AddressType_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitAddressType() {
        $("#btn_save").attr("disabled", "disabled");
        if (confirm("You want to save the data Address Type?")) {
            $.post('<?php echo site_url() . "/address_type/save_address_type" ?>', $('#frmAddressType').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save AddressType Master successfully.");
                    window.location = "<?php echo site_url() ?>/address_type";
                } else {
                    alert("Save AddressType Master unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                }
                return;
            });
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }
</SCRIPT>
<HTML>
    <HEAD>
        <TITLE> Address Type </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmAddressType" NAME="frmAddressType" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="AddressType_Id" name="AddressType_Id" value="<?php echo $AddressType_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $AddressType_Code ?>"/>
            <TABLE>
                <TR>    
                    <TD>AddressType Code : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="AddressType_Code" NAME="AddressType_Code" VALUE="<?php echo $AddressType_Code ?>"></TD>
                    <TD colspan="2">
                        <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                    </TD>  
                <TR>
                    <TD>AddressType Name EN : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="AddressType_NameEN" NAME="AddressType_NameEN" VALUE="<?php echo $AddressType_NameEN ?>"></TD>
                    <TD>AddressType Name TH : </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="AddressType_NameTH" NAME="AddressType_NameTH" VALUE="<?php echo $AddressType_NameTH ?>"></TD>
                </TR>
                <TR>
                    <TD>AddressType Desc:</TD>
                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="AddressType_Desc" NAME="AddressType_Desc" style="resize:none; width:98%;" rows="2"><?php echo $AddressType_Desc ?></TEXTAREA></TD>
                </TR>                    
            </TABLE>
        </FORM>
    </BODY>
</HTML>
