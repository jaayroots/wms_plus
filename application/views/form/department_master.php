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

            document.getElementById("Department_Code").readOnly = true;
            document.getElementById("Department_NameEN").readOnly = true;
            document.getElementById("Department_NameTH").readOnly = true;
            document.getElementById("Department_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="Department_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Department_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#Department_Code').val('');
        $('#Department_Code').addClass('required');
        $('#Department_NameEN').val('');
        $('#Department_NameEN').addClass('required');
        $('#Department_NameTH').val('');
        $('#Department_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of department page.
        window.location = "<?php echo site_url() ?>/department";
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var DepartmentCode = $('#Department_Code').val();
        var DepartmentNameEN = $('#Department_NameEN').val();

        if (DepartmentCode == "") {
            alert("Please input Department Code.");
            $('#Department_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(DepartmentCode)) {
            alert("Department Code must not is special Character.");
            $('#Department_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (DepartmentNameEN == "") {
            alert("Please input Department Name En.");
            $('#Department_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140306
        $.post('<?php echo site_url() . '/department/validation' ?>', $("#frmDepartment").serialize(), function(data) {
            if (data.result === 1) {
                submitDepartment();
            } else {
                if (data.note === "DEP_CODE_ALREADY") {
                    alert("Department Code already exists.");
                    $('#Department_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitDepartment() {
        if (confirm("You want to save the data Department?")) {
            $.post('<?php echo site_url() . "/department/save_department" ?>', $('#frmDepartment').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save Department Master successfully.");
                    window.location = "<?php echo site_url() ?>/department";
                } else {
                    alert("Save Department Master unsuccessfully.");
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
        <TITLE> Department </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmDepartment" NAME="frmDepartment" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="Department_Id" name="Department_Id" value="<?php echo $Department_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $Department_Code ?>"/>
            <TABLE>
                <TR>    
                    <TD>Department Code : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Department_Code" NAME="Department_Code" VALUE="<?php echo $Department_Code ?>"></TD>
                    <TD colspan="2">
                        <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                    </TD>  
                <TR>
                    <TD>Department Name EN : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Department_NameEN" NAME="Department_NameEN" VALUE="<?php echo $Department_NameEN ?>"></TD>
                    <TD>Department Name TH : </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="Department_NameTH" NAME="Department_NameTH" VALUE="<?php echo $Department_NameTH ?>"></TD>
                </TR>
                <TR>
                    <TD>Department Desc:</TD>
                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="Department_Desc" NAME="Department_Desc" style="resize:none; width:98%;" rows="2"><?php echo $Department_Desc ?></TEXTAREA></TD>
                </TR>                    
            </TABLE>
        </FORM>
    </BODY>
</HTML>
