<?php // Create by Ton! 20130910                       ?>
<SCRIPT>
    $(document).ready(function () {
        var type = $('#type').val();
        if (type == 'V') {
            $('#btn_clear').hide();
            $('#btn_save').hide();

            $('#BusinessType_Id').attr("disabled", true);

            $(":text").each(function () {
                $(this).attr("readonly", true);
            });

            $(":checkbox").each(function () {
                $(this).attr("disabled", "disabled");
            });
        } else if (type == 'E') {
            $('#Company_Code').attr('readonly', true);

            $('#btn_clear').hide();
            $('#btn_save').show();
        } else if (type == 'A') {
            $('#btn_clear').show();
            $("#Active").attr("checked", true);
        }

        $('.required').each(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="Company_Code"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Company_NameEN"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var comCode = $('#Company_Code').val();
        var comNameEN = $('#Company_NameEN').val();

        if (comCode === "") {
            alert("Please input Company Code.");
            $('#Company_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(comCode)) {
            $('#preload').fadeOut('slow', function () {
                alert("Company Code must not is special Character.");
                $('#Company_Code').focus();
                $("#btn_save").removeAttr("disabled");
            });
            return;
        }

        if (comNameEN === "") {
            alert("Please input Company Name EN.");
            $('#Company_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140303
        $.post('<?php echo site_url('/company/validation') ?>', $("#frm_company_master").serialize(), function (data) {
            if (data.result === 1) {
                submitFrm();
            } else {
                if (data.note === "COM_CODE_ALREADY") {
                    alert("Company Code already exists.");
                    $('#Company_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitFrm() {
        if (confirm("You want to save the data Company?")) {
            $.post('<?php echo site_url('/company/saveCompany') ?>', $('#frm_company_master').serialize(), function (dataSaveCom) {
                if (dataSaveCom === "1") {// Save Company Ok.
                    alert("Save Company successfully.");
                    window.location = "<?php echo site_url() ?>/company";
                    return true;
                } else {
                    alert("Save Company unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                    return false;
                }
            }, "HTML");
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }

    function clearData() {
        $('#BusinessType_Id option[value=""]').attr('selected', 'selected');
        $('#Company_Code').val('');
        $('#Company_Code').addClass('required');

        $('#Company_NameEN').val('');
        $('#Company_NameEN').addClass('required');

        $('#Company_NameTH').val('');
        $('#Company_Desc').val('');

        $(":checkbox").each(function () {
            $(this).attr("checked", false);
        });

        $("#Active").attr("checked", true);
    }

    function backToList() {// back to list company master page.
        window.location = "<?php echo site_url() ?>/company";
    }
</SCRIPT>
<STYLE tyle="css/text">
    .w30{ width: 30px; padding: 5px; }
    .w70{ width: 70px;  padding: 5px; }
    .w100{ width: 100px;  padding: 5px; }
    .w120{ width: 120px;  padding: 5px; }
    .w150{ width: 150px;  padding: 5px; }
    .txt-r{ text-align: right; }
    .txt-l{ text-align: left; }
    .txt-c{ text-align: center; }
</style>
<HTML>
    <HEAD>
        <TITLE> Company Information </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frm_company_master" NAME="frm_company_master" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="Company_Id" name="Company_Id" value="<?php echo $Company_Id ?>"/>
            <?php
            if (!isset($BusinessType_Id)) :
                $BusinessType_Id = "";
            endif;

            $chkIsOwner = FALSE;
            if (ISSET($IsOwner)):
                $chkIsOwner = $IsOwner;
            endif;

            $chkIsCustomer = FALSE;
            if (ISSET($IsCustomer)):
                $chkIsCustomer = $IsCustomer;
            endif;

            $chkIsSupplier = FALSE;
            if (ISSET($IsSupplier)):
                $chkIsSupplier = $IsSupplier;
            endif;

            $chkIsVendor = FALSE;
            if (ISSET($IsVendor)):
                $chkIsVendor = $IsVendor;
            endif;

            $chkIsShipper = FALSE;
            if (ISSET($IsShipper)):
                $chkIsShipper = $IsShipper;
            endif;

            $chkIsRenter = FALSE;
            if (ISSET($IsRenter)):
                $chkIsRenter = $IsRenter;
            endif;

            $chkIsRenterBranch = FALSE;
            if (ISSET($IsRenterBranch)):
                $chkIsRenterBranch = $IsRenterBranch;
            endif;
            ?>
            <TABLE width='95%' align='center'>
                <TR><TD>
                        <FIELDSET  class="well" >
                            <TABLE>
                                <TR>
                                    <TD>Business Type:</TD>
                                    <TD><?php echo form_dropdown('BusinessType_Id', $optionBusinessType, $BusinessType_Id, "id=BusinessType_Id style='width:auto'") ?>
                                        <label for="Active" style="display: inline;">
                                            <?php echo form_checkbox('Active', 1, $Active, 'id="Active" name="Active"'); ?>&nbsp;&nbsp;&nbsp;Active&nbsp;&nbsp;&nbsp;
                                        </label>
                                    </TD>
                                </TR>
                                <TR>
                                    <TD>Company Code:</TD>
                                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Company_Code" NAME="Company_Code" VALUE="<?php echo $Company_Code ?>"></TD>
                                </TR>
                                <TR>
                                    <TD>Company Name EN:</TD>
                                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Company_NameEN" NAME="Company_NameEN" VALUE="<?php echo $Company_NameEN ?>"></TD>
                                </TR>
                                <TR>
                                    <TD>Company Name TH:</TD>
                                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="Company_NameTH" NAME="Company_NameTH" VALUE="<?php echo $Company_NameTH ?>"></TD>
                                </TR>
                                <TR>
                                    <TD>Company Desc:</TD>
                                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="Company_Desc" NAME="Company_Desc" VALUE="<?php echo $Company_Desc ?>"></TD>
                                </TR>
                                <TR>
                                    <TD Colspan='2'>
                                        <label for="IsOwner" style="display: inline;"><?php echo form_checkbox('IsOwner', 1, $chkIsOwner, "id='IsOwner' name='IsOwner'"); ?>&nbsp;&nbsp;&nbsp;IsOwner&nbsp;&nbsp;&nbsp;</label>
                                        <label for="IsCustomer" style="display: inline;"><?php echo form_checkbox('IsCustomer', 1, $chkIsCustomer, "id='IsCustomer' name='IsCustomer'"); ?>&nbsp;&nbsp;&nbsp;IsCustomer&nbsp;&nbsp;&nbsp;</label>
                                        <label for="IsSupplier" style="display: inline;"><?php echo form_checkbox('IsSupplier', 1, $chkIsSupplier, "id='IsSupplier' name='IsSupplier'"); ?>&nbsp;&nbsp;&nbsp;IsSupplier&nbsp;&nbsp;&nbsp;</label>
                                        <label for="IsVendor" style="display: inline;"><?php echo form_checkbox('IsVendor', 1, $chkIsVendor, "id='IsVendor' name='IsVendor'"); ?>&nbsp;&nbsp;&nbsp;IsVendor&nbsp;&nbsp;&nbsp;</label>
                                        <label for="IsShipper" style="display: inline;"><?php echo form_checkbox('IsShipper', 1, $chkIsSupplier, "id='IsShipper' name='IsShipper'"); ?>&nbsp;&nbsp;&nbsp;IsShipper&nbsp;&nbsp;&nbsp;</label>
                                        <label for="IsRenter" style="display: inline;"><?php echo form_checkbox('IsRenter', 1, $chkIsRenter, "id='IsRenter' name='IsRenter'"); ?>&nbsp;&nbsp;&nbsp;IsRenter&nbsp;&nbsp;&nbsp;</label>
                                        <label for="IsRenterBranch" style="display: inline;"><?php echo form_checkbox('IsRenterBranch', 1, $chkIsRenterBranch, "id='IsRenterBranch' name='IsRenterBranch'"); ?>&nbsp;&nbsp;&nbsp;IsRenterBranch&nbsp;&nbsp;&nbsp;</label>
                                    </TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD></TR>
            </TABLE>
        </FORM>
    </BODY>
</HTML>