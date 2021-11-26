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
            document.getElementById("Active").setAttribute("checked", "checked");
<?php else: ?>
            $('#btn_save').hide();
            $('#btn_clear').hide();

            document.getElementById("Country_Code").readOnly = true;
            document.getElementById("Country_NameEN").readOnly = true;
            document.getElementById("Country_NameTH").readOnly = true;
            document.getElementById("Country_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="Country_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Country_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#Country_Code').val('');
        $('#Country_Code').addClass('required');
        $('#Country_NameEN').val('');
        $('#Country_NameEN').addClass('required');
        $('#Country_NameTH').val('');
        $('#Country_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of country page.
        window.location = "<?php echo site_url() ?>/country";
    }

    function validation() {
        var CountryCode = $('#Country_Code').val();
        var CountryNameEN = $('#Country_NameEN').val();

        if (CountryCode == "") {
            alert("Please input Country Code.");
            $('#Country_Code').focus();
            return;
        }
        if (!check_special_character(CountryCode)) {
            alert("Country Code must not is special Character.");
            $('#Country_Code').focus();
            return;
        }

        if (CountryNameEN == "") {
            alert("Please input Country Name En.");
            $('#Country_NameEN').focus();
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140306
        $.post('<?php echo site_url() . '/country/validation' ?>', $("#frmCountry").serialize(), function(data) {
            if (data.result === 1) {
                submitCountry();
            } else {
                if (data.note === "COU_CODE_ALREADY") {
                    alert("Country Code already exists.");
                    $('#Country_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitCountry() {
        if (confirm("You want to save the data Country?")) {
            $.post('<?php echo site_url() . "/country/save_country" ?>', $('#frmCountry').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save Country Master successfully.");
                    window.location = "<?php echo site_url() ?>/country";
                } else {
                    alert("Save Country Master unsuccessfully.");
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
        <TITLE> Country </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmCountry" NAME="frmCountry" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="Country_Id" name="Country_Id" value="<?php echo $Country_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $Country_Code ?>"/>
            <TABLE>
                <TR>    
                    <TD>Country Code : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Country_Code" NAME="Country_Code" VALUE="<?php echo $Country_Code ?>"></TD>
                    <TD colspan="2">
                        <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                    </TD>  
                </TR>   
                <TR>
                    <TD>Country Name EN : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Country_NameEN" NAME="Country_NameEN" VALUE="<?php echo $Country_NameEN ?>"></TD>
                    <TD>Country Name TH : </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="Country_NameTH" NAME="Country_NameTH" VALUE="<?php echo $Country_NameTH ?>"></TD>
                </TR>
                <TR>
                    <TD>Country Desc:</TD>
                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="Country_Desc" NAME="Country_Desc" style="resize:none; width:98%;" rows="2"><?php echo $Country_Desc ?></TEXTAREA></TD>
                </TR>                    
            </TABLE>
        </FORM>
    </BODY>
</HTML>
