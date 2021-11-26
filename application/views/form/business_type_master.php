<?php
/*
 * Create by Ton! 20131204
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
<?php else: ?>
            $('#btn_save').hide();
            $('#btn_clear').hide();

            document.getElementById("BusinessType_Code").readOnly = true;
            document.getElementById("BusinessType_NameEN").readOnly = true;
            document.getElementById("BusinessType_NameTH").readOnly = true;
            document.getElementById("BusinessType_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="BusinessType_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="BusinessType_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#BusinessType_Code').val('');
        $('#BusinessType_Code').addClass('required');
        $('#BusinessType_NameEN').val('');
        $('#BusinessType_NameEN').addClass('required');
        $('#BusinessType_NameTH').val('');
        $('#BusinessType_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of business type page.
        window.location = "<?php echo site_url() ?>/business_type";
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var BusinessTypeCode = $('#BusinessType_Code').val();
        var BusinessTypeNameEN = $('#BusinessType_NameEN').val();

        if (BusinessTypeCode == "") {
            alert("Please input BusinessType Code.");
            $('#BusinessType_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(BusinessTypeCode)) {
            alert("BusinessType Code must not is special Character.");
            $('#BusinessType_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (BusinessTypeNameEN == "") {
            alert("Please input BusinessType Name En.");
            $('#BusinessType_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140306
        $.post('<?php echo site_url() . '/business_type/validation' ?>', $("#frmBusinessType").serialize(), function(data) {
            if (data.result === 1) {
                submitBusinessType();
            } else {
                if (data.note === "BUSI_CODE_ALREADY") {
                    alert("BusinessType Code already exists.");
                    $('#BusinessType_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitBusinessType() {
        if (confirm("You want to save the data Business Type?")) {
            $.post('<?php echo site_url() . "/business_type/save_business_type" ?>', $('#frmBusinessType').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save Business Type Master successfully.");
                    window.location = "<?php echo site_url() ?>/business_type";
                } else {
                    alert("Save Business Type Master unsuccessfully.");
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
        <TITLE> Business Type </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmBusinessType" NAME="frmBusinessType" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="BusinessType_Id" name="BusinessType_Id" value="<?php echo $BusinessType_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $BusinessType_Code ?>"/>
            <TABLE>
                <TR>    
                    <TD>BusinessType Code : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="BusinessType_Code" NAME="BusinessType_Code" VALUE="<?php echo $BusinessType_Code ?>"></TD>
                    <TD colspan="2">
                        <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                    </TD>  
                <TR>
                    <TD>BusinessType Name EN : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="BusinessType_NameEN" NAME="BusinessType_NameEN" VALUE="<?php echo $BusinessType_NameEN ?>"></TD>
                    <TD>BusinessType Name TH : </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="BusinessType_NameTH" NAME="BusinessType_NameTH" VALUE="<?php echo $BusinessType_NameTH ?>"></TD>
                </TR>
                <TR>
                    <TD>BusinessType Desc:</TD>
                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="BusinessType_Desc" NAME="BusinessType_Desc" style="resize:none; width:98%;" rows="2"><?php echo $BusinessType_Desc ?></TEXTAREA></TD>
                </TR>                    
            </TABLE>
        </FORM>
    </BODY>
</HTML>
