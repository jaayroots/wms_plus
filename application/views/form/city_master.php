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

            $('#Province_Id').attr("disabled", true);
            document.getElementById("City_Code").readOnly = true;
            document.getElementById("City_NameEN").readOnly = true;
            document.getElementById("City_NameTH").readOnly = true;
            document.getElementById("City_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="City_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="City_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        if ($('#Province_Id').val() <= 0) {
            $('#Province_Id').addClass('required');
        }

        $('[name="Province_Id"]').change(function() {
            if ($(this).val() > 0) {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#Province_Id option[value=""]').attr('selected', 'selected');
        $('#City_Code').val('');
        $('#City_NameEN').val('');
        $('#City_NameTH').val('');
        $('#City_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of city page.
        window.location = "<?php echo site_url() ?>/city";
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var CityCode = $('#City_Code').val();
        var CityNameEN = $('#City_NameEN').val();

        if ($('#Province_Id').val() <= 0) {
            alert("Please input Province.");
            $('#Province_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        if (CityCode == "") {
            alert("Please input City Code.");
            $('#City_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(CityCode)) {
            alert("City Code must not is special Character.");
            $('#City_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (CityNameEN == "") {
            alert("Please input City Name En.");
            $('#City_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140306
        $.post('<?php echo site_url() . '/city/validation' ?>', $("#frmCity").serialize(), function(data) {
            if (data.result === 1) {
                submitCity();
            } else {
                if (data.note === "CITY_CODE_ALREADY") {
                    alert("City Code already exists.");
                    $('#City_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitCity() {
        if (confirm("You want to save the data City?")) {
            $.post('<?php echo site_url() . "/city/save_city" ?>', $('#frmCity').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save City Master successfully.");
                    window.location = "<?php echo site_url() ?>/city";
                } else {
                    alert("Save City Master unsuccessfully.");
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
        <TITLE> City </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmCity" NAME="frmCity" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="City_Id" name="City_Id" value="<?php echo $City_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $City_Code ?>"/>
            <TABLE>
                <TR>
                    <TD>Province : </TD>
                    <TD Colspan='3'><?php echo form_dropdown('Province_Id', $optionProvince, $Province_Id, 'id=Province_Id') ?></TD>
                </TR>
                <TR>    
                    <TD>City Code : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="City_Code" NAME="City_Code" VALUE="<?php echo $City_Code ?>"></TD>
                    <TD colspan="2">
                        <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                    </TD>  
                <TR>
                    <TD>City Name EN : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="City_NameEN" NAME="City_NameEN" VALUE="<?php echo $City_NameEN ?>"></TD>
                    <TD>City Name TH : </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="City_NameTH" NAME="City_NameTH" VALUE="<?php echo $City_NameTH ?>"></TD>
                </TR>
                <TR>
                    <TD>City Desc:</TD>
                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="City_Desc" NAME="City_Desc" style="resize:none; width:98%;" rows="2"><?php echo $City_Desc ?></TEXTAREA></TD>
                </TR>                    
            </TABLE>
        </FORM>
    </BODY>
</HTML>
