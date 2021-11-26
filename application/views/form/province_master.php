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

            $('#Country_Id').attr("disabled", true);
            document.getElementById("Province_Code").readOnly = true;
            document.getElementById("Province_NameEN").readOnly = true;
            document.getElementById("Province_NameTH").readOnly = true;
            document.getElementById("Province_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="Province_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Province_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        if ($('#Country_Id').val() <= 0) {
            $('#Country_Id').addClass('required');
        }

        $('[name="Country_Id"]').change(function() {
            if ($(this).val() > 0) {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#Country_Id option[value=""]').attr('selected', 'selected');
        $('#Country_Id').addClass('required');
        $('#Province_Code').val('');
        $('#Province_Code').addClass('required');
        $('#Province_NameEN').val('');
        $('#Province_NameEN').addClass('required');
        $('#Province_NameTH').val('');
        $('#Province_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of province page.
        window.location = "<?php echo site_url() ?>/province";
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var ProvinceCode = $('#Province_Code').val();
        var ProvinceNameEN = $('#Province_NameEN').val();

        if ($('#Country_Id').val() <= 0) {
            alert("Please input Country.");
            $('#Country_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        if (ProvinceCode == "") {
            alert("Please input Province Code.");
            $('#Province_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(ProvinceCode)) {
            alert("Province Code must not is special Character.");
            $('#Province_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (ProvinceNameEN == "") {
            alert("Please input Province Name En.");
            $('#Province_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140306
        $.post('<?php echo site_url() . '/province/validation' ?>', $("#frmProvince").serialize(), function(data) {
            if (data.result === 1) {
                submitProvince();
            } else {
                if (data.note === "PRO_CODE_ALREADY") {
                    alert("Province Code already exists.");
                    $('#Province_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitProvince() {
        if (confirm("You want to save the data Province?")) {
            $.post('<?php echo site_url() . "/province/save_province" ?>', $('#frmProvince').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save Province Master successfully.");
                    window.location = "<?php echo site_url() ?>/province";
                } else {
                    alert("Save Province Master unsuccessfully.");
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
        <TITLE> Province </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmProvince" NAME="frmProvince" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="Province_Id" name="Province_Id" value="<?php echo $Province_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $Province_Code ?>"/>
            <TABLE>
                <TR>
                    <TD>Country : </TD>
                    <TD Colspan='3'><?php echo form_dropdown('Country_Id', $optionCountry, $Country_Id, 'id=Country_Id') ?></TD>
                </TR>
                <TR>    
                    <TD>Province Code : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Province_Code" NAME="Province_Code" VALUE="<?php echo $Province_Code ?>"></TD>
                    <TD colspan="2">
                        <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                    </TD>  
                <TR>
                    <TD>Province Name EN : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Province_NameEN" NAME="Province_NameEN" VALUE="<?php echo $Province_NameEN ?>"></TD>
                    <TD>Province Name TH : </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="Province_NameTH" NAME="Province_NameTH" VALUE="<?php echo $Province_NameTH ?>"></TD>
                </TR>
                <TR>
                    <TD>Province Desc:</TD>
                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="Province_Desc" NAME="Province_Desc" style="resize:none; width:98%;" rows="2"><?php echo $Province_Desc ?></TEXTAREA></TD>
                </TR>                    
            </TABLE>
        </FORM>
    </BODY>
</HTML>