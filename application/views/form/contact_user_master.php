<?php // Create by Ton! 20130912                                                                                                                                                                                                                                                                                                                 ?>
<SCRIPT>
    $(document).ready(function() {
        $('#defDataTableLogin').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "sPaginationType": "full_numbers"
        });

        var type = $('#type').val();
        if (type === 'V') {
            $('#btn_clear').hide();
            $('#btn_save').hide();
            $('#btnReset').hide();

            $('#TitleName_Id').attr("disabled", true);
            document.getElementById("Contact_Code").readOnly = true;
            document.getElementById("First_NameEN").readOnly = true;
            document.getElementById("Last_NameEN").readOnly = true;
            document.getElementById("First_NameTH").readOnly = true;
            document.getElementById("Last_NameTH").readOnly = true;
            document.getElementById("PhoneNo1").readOnly = true;
            document.getElementById("PhoneNo2").readOnly = true;
            document.getElementById("FaxNo").readOnly = true;
            document.getElementById("EmailAddress").readOnly = true;
            $('#Deparment_Id').attr("disabled", true);
            $('#Position_Id').attr("disabled", true);
            $('#Company_Id').attr("disabled", true);

            document.getElementById("UserAccount").readOnly = true;
            document.getElementById("Password").readOnly = true;
        } else if (type === 'E') {
            $('#btn_save').show();
            $('#btn_clear').hide();
        } else if (type === 'A') {
            $('#btn_clear').show();
            $('#btnReset').hide();
            $('#Password').val('password');
        }

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="Contact_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="First_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Last_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="First_NameTH"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Last_NameTH"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="UserAccount"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        if ($('#TitleName_Id').val() <= 0) {
            $('#TitleName_Id').addClass('required');
        }

        $('[name="TitleName_Id"]').change(function() {
            if ($(this).val() > 0) {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        if ($('#Deparment_Id').val() <= 0) {
            $('#Deparment_Id').addClass('required');
        }

        $('[name="Deparment_Id"]').change(function() {
            if ($(this).val() > 0) {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        if ($('#Position_Id').val() <= 0) {
            $('#Position_Id').addClass('required');
        }

        $('[name="Position_Id"]').change(function() {
            if ($(this).val() > 0) {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        if ($('#Company_Id').val() <= 0) {
            $('#Company_Id').addClass('required');
        }

        $('[name="Company_Id"]').change(function() {
            if ($(this).val() > 0) {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="EmailAddress"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function check_length_user($str) {
        if ($str.length < 4) {
            return false;
        } else {
            return true;
        }
    }

    function checkSpecialCharacterOnForm($str) {
        var iChars = "~`!#$%^&*+=-[]\\\';,/{}|\":<>?";

        if (!$str) {
            return false;
        }

        for (var i = 0; i < $str.length; i++) {
            if (iChars.indexOf($str.charAt(i)) !== -1) {
                return false;
            }
        }

        return true;
    }

    function isDigit(obj) {
        var strValue = obj.value;
        var strId = obj.id;
        var numericReg = /^[0-9]*\.?[0-9]*$/;
        if (strValue !== "") {
            if (numericReg.test(strValue) === false) {
                alert("Please Fill Number Only.");
                $('#' + strId).focus();
                return false;
            }
        }
    }

    function chkEmail() {
        var email = $('#EmailAddress').val();
        //var filter=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i
        var filter = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        if (!filter.test(email)) {
            alert('Email format is not valid.');
            $('#EmailAddress').focus();
            return false;
        } else {
            return true;
        }
    }

    function resetPassword($pw) {
        <?php if ($reset_pw === TRUE): ?>
            $.post('<?php echo site_url() . "/contact_user/reset_password" ?>', $('#frm_contact_user').serialize(), function(data_reset_pw) {
                if (data_reset_pw !== "0") {
                    $('#fPassword').val(data_reset_pw);
                    $('#Password').val(data_reset_pw);
                    alert("New Password : " + data_reset_pw);
                }
            }, "html");
        <?php else: ?>
            $('#Password').val($pw);
            $.post('<?php echo site_url() . "/contact_user/change_password" ?>', $('#frm_contact_user').serialize(), function(data_reset_pw) {
                $('#fPassword').val(data_reset_pw);
                $('#Password').val(data_reset_pw);
            }, "html");
        <?php endif; ?>
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");

        var type = $('#type').val();
        var conCode = $('#Contact_Code').val();
        if (conCode === "") {
            alert("Please input Contact Code.");
            $('#Contact_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if (!checkSpecialCharacterOnForm(conCode)) {
            alert("Contact Code must not is special Character.");
            $('#Contact_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        if ($('#TitleName_Id').val() <= 0) {
            alert("Please input Title Name.");
            $('#TitleName_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        var FirstNameEN = $('#First_NameEN').val();
        if (FirstNameEN === "") {
            alert("Please input First Name EN.");
            $('#First_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if (!checkSpecialCharacterOnForm(FirstNameEN)) {
            alert("First Name EN must not is special Character.");
            $('#First_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        var LastNameEN = $('#Last_NameEN').val();
        if (LastNameEN === "") {
            alert("Please input Last Name EN.");
            $('#Last_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if (!checkSpecialCharacterOnForm(LastNameEN)) {
            alert("Last Name EN must not is special Character.");
            $('#Last_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        var FirstNameTH = $('#First_NameTH').val();
        if (FirstNameTH === "") {
            alert("Please input First Name TH.");
            $('#First_NameTH').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if (!checkSpecialCharacterOnForm(FirstNameTH)) {
            alert("First Name TH must not is special Character.");
            $('#First_NameTH').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        var LastNameTH = $('#Last_NameTH').val();
        if (LastNameTH === "") {
            alert("Please input Last Name TH.");
            $('#Last_NameTH').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if (!checkSpecialCharacterOnForm(LastNameTH)) {
            alert("Last Name TH must not is special Character.");
            $('#Last_NameTH').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        if (chkEmail() === false) {
            alert("Please check your e-mail format.");
            $('#EmailAddress').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        if ($('#Deparment_Id').val() <= 0) {
            alert("Please input Deparment.");
            $('#Deparment_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        if ($('#Position_Id').val() <= 0) {
            alert("Please input Position.");
            $('#Position_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        if ($('#Company_Id').val() <= 0) {
            alert("Please input Company.");
            $('#Company_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        var UserAccount = $('#UserAccount').val();
        if (UserAccount === "") {
            alert("Please input User Account.");
            $('#UserAccount').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if (!check_length_user(UserAccount)) {
            alert("User Account must have no less than 4 Character.");
            $('#UserAccount').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if (!checkSpecialCharacterOnForm(UserAccount)) {
            alert("User Account must not is special Character.");
            $('#UserAccount').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        var currentPW = $('#fPassword').val();
        var Password = $('#Password').val();
        if (Password === "") {
            alert("Please input Password.");
            $('#Password').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if (!check_length_user(Password)) {
            alert("Password must have no less than 4 Character.");
//            $('#Password').focus();
            $('#Password').select();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if (!checkSpecialCharacterOnForm(Password)) {
            alert("Password must not is special Character.");
            $('#Password').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        if (currentPW !== Password) {// Must Save Password (YES\NO)?
            $('#savePW').val('SAVE');
        } else {
            $('#savePW').val('NOT_SAVE');
        }

        validation_in_controllers();
    }

    function check_contact_code() {
        $.post("<?php echo site_url() . "/contact_user/check_contact_code" ?>", $('#frm_contact_user').serialize(), function(data) {
            if (data === "1") {
                alert('Contact Code already exists.');
                $('#Contact_Code').focus();
                return false;
            }
        }, "html");
    }

    function check_user_account() {
        $.post("<?php echo site_url() . "/contact_user/check_user_account" ?>", $('#frm_contact_user').serialize(), function(data) {
            if (data === "1") {
                alert('User Account already exists.');
                $('#UserAccount').focus();
                return false;
            }
        }, "html");

    }

    function validation_in_controllers() {// Add by Ton! 20140310
        $.post('<?php echo site_url() . '/contact_user/validation' ?>', $("#frm_contact_user").serialize(), function(data) {
            if (data.result === 1) {
                submitFrm();
            } else {
                if (data.note === "CON_CODE_ALREADY") {
                    alert('Contact Code already exists.');
                    $("#Contact_Code").val("");
                    $('#Contact_Code').focus();
                }

                if (data.note === "USER_CODE_ALREADY") {
                    alert('User Account already exists.');
                    $("#UserAccount").val("");
                    $('#UserAccount').focus();
                }

                if (data.note === "PASS_OLD") {
                    alert("Current Password is incorrect. Please check.");
                    $("#oldPassword").val("");
                    $("#oldPassword").focus();
                }

                if (data.note === "PASS_NEW") {
                    alert("New Password must be identical to the Old Password. Please check.");
                    $("#Password").val("");
                    $("#conNewPassword").val("");
                    $("#oldPassword").val("");
                    $("#Password").focus();
                }

                if (data.note === "PASS_NEW_OLD") {
                    alert("New Password ever used. Not available.");
                    $("#Password").val("");
                    $("#conNewPassword").val("");
                    $("#oldPassword").val("");
                    $("#Password").focus();
                }

                if (data.note === "PASS_CON") {
                    alert("Confirm New Password must be identical to the New Password. Please check.");
                    $("#conNewPassword").val("");
                    $("#conNewPassword").focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitFrm() {
        if (confirm("You want to save the data Contact User?")) {
            $.post('<?php echo site_url() . "/contact_user/save_contact_user" ?>', $('#frm_contact_user').serialize(), function(data_save) {
                if (data_save !== "0") {// Save Ok.
                    alert("Save Contact User successfully.");
                    window.location = "<?php echo site_url() ?>/contact_user";
                    return;
                } else {// Save Not Ok.
                    alert("Save Contact User Unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                    return;
                }
            }, "html");
        } else {// Cancel Save.
            $("#btn_save").removeAttr("disabled");
            return;
        }
    }

    function clear_data_input() {
        $('#TitleName_Id option[value=""]').attr('selected', 'selected');
        $('#TitleName_Id').addClass('required');
        $('#Contact_Code').val('');
        $('#Contact_Code').addClass('required');
        $('#First_NameEN').val('');
        $('#First_NameEN').addClass('required');
        $('#Last_NameEN').val('');
        $('#Last_NameEN').addClass('required');
        $('#First_NameTH').val('');
        $('#First_NameTH').addClass('required');
        $('#Last_NameTH').val('');
        $('#Last_NameTH').addClass('required');

        $('#PhoneNo1').val('');
        $('#PhoneNo2').val('');
        $('#FaxNo').val('');
        $('#EmailAddress').val('');

        $('#Deparment_Id option[value=""]').attr('selected', 'selected');
        $('#Deparment_Id').addClass('required');
        $('#Position_Id option[value=""]').attr('selected', 'selected');
        $('#Position_Id').addClass('required');
        $('#Company_Id option[value=""]').attr('selected', 'selected');
        $('#Company_Id').addClass('required');

        $('#UserAccount').val('');
        $('#UserAccount').addClass('required');

        resetPassword("password");
    }

    function backToList() {// back to list contact_user page.
        window.location = "<?php echo site_url() ?>/contact_user";
    }

    function check_password() {
        var type = $('#type').val();
        var pw = $('#Password').val();//Current Password OR New Password
        var fPw = $('#fPassword').val();//Current Password

        if (type === "E") {
            if (fPw !== pw) {
                $('#savePW').val('SAVE');
                document.getElementById("conNewPW").style.display = '';
                document.getElementById("OPW").style.display = '';
                $('#conNewPassword').focus();
            }
        }
    }

    function save_force_logout($Log_Id) {
        if (confirm("You want to force User Logout?")) {
            $.post('<?php echo site_url() . "/contact_user/force_user_logout" ?>', {Log_id: $Log_Id}, function(data) {
                if (data !== 0) {
                    window.location.reload();
                }
            });
        }
    }

    $('#modal_about_user').modal('toggle').css({
        // make width 90% of screen
        'width': function() {
            return ($(document).width() * 0.95) + 'px';
        },
        // center model
        'margin-left': function() {
            return -($(this).width() / 2);
        }
    });

    function getAboutUser() {// Display Detail of User Group, User Role & Individual Permission Menu. [Add by Ton! 20140314] $('#frm_contact_user').serialize()
        $.post('<?php echo site_url('/contact_user/get_about_user_detail')?>', {UserLogin_Id:$('#UserLogin_Id').val()}, function(data) {
            if (data.length > 0) {
                var html_group = "";
                var html_role = "";
                var html_individual_mnu = "";
                var response = $.parseJSON(data);
                $.each(response, function(i, obj) {
//                    if (obj[0].length > 0) {
                    if ((obj[0].role.length) > 0) {
                        html_role += "<FIELDSET class=\"well\"><LEGEND><h6>User Role</h6></LEGEND>";
                        html_role += "<table id=\"defDataTableRole\" class=\"display dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" aria-describedby=\"defDataTable_info\">";
                        html_role += "<thead>";
                        html_role += "<tr>";
                        html_role += "<th>Role Code</th>";
                        html_role += "<th>Role Name</th>";
                        html_role += "</tr>";
                        html_role += "</thead>";
                        html_role += "<tbody>";
                        for (var k = 0; k < obj[0].role.length; k++) {
                            html_role += "<tr>";
                            html_role += "<td>" + obj[0].role[k].UserRole_Code + "</td>";
                            html_role += "<td>" + obj[0].role[k].UserRole_Name + "</td>";
                            html_role += "</tr>";
                        }
                        html_role += "</tbody>";
                        html_role += "</table>";
                        html_role += "</FIELDSET>";
                    }

                    if ((obj[0].group.length) > 0) {
                        html_group += "<FIELDSET class=\"well\"><LEGEND><h6>User Group</h6></LEGEND>";
                        html_group += "<table id=\"defDataTableGroup\" class=\"display dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" aria-describedby=\"defDataTable_info\">";
                        html_group += "<thead>";
                        html_group += "<tr>";
                        html_group += "<th>Group Code</th>";
                        html_group += "<th>Group Name</th>";
                        html_group += "</tr>";
                        html_group += "</thead>";
                        html_group += "<tbody>";
                        for (var j = 0; j < obj[0].group.length; j++) {
                            html_group += "<tr>";
                            html_group += "<td>" + obj[0].group[j].UserGroup_Code + "</td>";
                            html_group += "<td>" + obj[0].group[j].UserGroup_Name + "</td>";
                            html_group += "</tr>";
                        }
                        html_group += "</tbody>";
                        html_group += "</table>";
                        html_group += "</FIELDSET>";
                    }

                    if ((obj[0].individual_mnu.length) > 0) {
                        html_individual_mnu += "<FIELDSET class=\"well\"><LEGEND><h6>Individual Permission Menu</h6></LEGEND>";
                        html_individual_mnu += "<table id=\"defDataTableIndividual\" class=\"display dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" aria-describedby=\"defDataTable_info\">";
                        html_individual_mnu += "<thead>";
                        html_individual_mnu += "<tr>";
                        html_individual_mnu += "<th>Menu Code</th>";
                        html_individual_mnu += "<th>Menu Name</th>";
                        html_individual_mnu += "</tr>";
                        html_individual_mnu += "</thead>";
                        html_individual_mnu += "<tbody>";
                        for (var l = 0; l < obj[0].individual_mnu.length; l++) {
                            html_individual_mnu += "<tr>";
                            html_individual_mnu += "<td>" + obj[0].individual_mnu[l].MenuBar_Code + "</td>";
                            html_individual_mnu += "<td>" + obj[0].individual_mnu[l].MenuBar_Name + "</td>";
                            html_individual_mnu += "</tr>";
                        }
                        html_individual_mnu += "</tbody>";
                        html_individual_mnu += "</table>";
                        html_individual_mnu += "</FIELDSET>";
                    }
//                    }
                });

                if (html_role === "" && html_group === "" && html_individual_mnu === "") {
                    $('#modal_about_user').modal('hide');
                    alert("No Data.");
                } else {
                    $("#modal_about_user .modal-body").html(html_role + html_group + html_individual_mnu);
                    $('#defDataTableRole').dataTable({
                        "bJQueryUI": true,
                        "bSort": false,
                        "sPaginationType": "full_numbers"
                    });
                    $('#defDataTableGroup').dataTable({
                        "bJQueryUI": true,
                        "bSort": false,
                        "sPaginationType": "full_numbers"
                    });
                    $('#defDataTableIndividual').dataTable({
                        "bJQueryUI": true,
                        "bSort": false,
                        "sScrollY": "150px",
                        "sPaginationType": "full_numbers"
                    });
                }
            }
        });
    }
</SCRIPT>
<style>
    #modal_about_user {
        width: 1024px; /* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -512px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }

    label {
        display: inline;
        margin-bottom: 2px;
    }
</style>
<HTML>
    <HEAD>
        <TITLE> Contact & User Login </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frm_contact_user" NAME="frm_contact_user" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="Contact_Id" name="Contact_Id" value="<?php echo $Contact_Id ?>"/>
            <input type="hidden" id="UserLogin_Id" name="UserLogin_Id" value="<?php echo $UserLogin_Id ?>"/>
            <input type="hidden" id="fPassword" name="fPassword" value="<?php echo $Password ?>"/>
            <input type="hidden" id="savePW" name="savePW" value="SAVE"/>'

            <?php
            $extra_disabled = "";
            $extra_active = "";
            if ($mode == 'V'):
                $extra_disabled = " disabled=disabled ";
            elseif ($mode == 'A'):
                $extra_active = " checked=checked ";
            endif;

            if ((!isset($IsCustomer)) || ($IsCustomer != 0)) :
                $IsCustomer = true;
            else :
                $IsCustomer = false;
            endif;
            if ((!isset($IsEmployee)) || ($IsEmployee != 0)) :
                $IsEmployee = true;
            else :
                $IsEmployee = false;
            endif;
            if ((!isset($IsSupplier)) || ($IsSupplier != 0)) :
                $IsSupplier = true;
            else :
                $IsSupplier = false;
            endif;
            if ((!isset($IsVendor)) || ($IsVendor != 0)) :
                $IsVendor = true;
            else :
                $IsVendor = false;
            endif;
            if ((!isset($IsRenter)) || ($IsRenter != 0)) :
                $IsRenter = true;
            else :
                $IsRenter = false;
            endif;
            if ((!isset($IsShipper)) || ($IsShipper != 0)) :
                $IsShipper = true;
            else :
                $IsShipper = false;
            endif;
            if ((!isset($IsSuperUser)) || ($IsSuperUser != 0)) :
                $IsSuperUser = true;
            else :
                $IsSuperUser = false;
            endif;
            if ((!isset($Active)) || ($Active != 0)) :
                $Active = true;
            else :
                $Active = false;
            endif;
            ?>

            <TABLE width='95%' align='center'>
                <TR><TD>
                        <FIELDSET class="well"><LEGEND>Contact</LEGEND>
                            <TABLE>
                                <TR>
                                    <TD>Contact Code:</TD>
                                    <TD><INPUT TYPE="text" CLASS="required" ID="Contact_Code" NAME="Contact_Code" VALUE="<?php echo $Contact_Code ?>" onblur="check_contact_code();"></TD>
                                    <TD Colspan='4'>
                                        <?php echo form_checkbox('Active', 1, $Active, $extra_disabled . $extra_active); ?>&nbsp;&nbsp;&nbsp;Active&nbsp;&nbsp;&nbsp;
                                    </TD>
                                </TR>
                                <TR>
                                    <TD>Title Name:</TD>
                                    <TD><?php echo form_dropdown('TitleName_Id', $optionTitleName, $TitleName_Id, 'id=TitleName_Id') ?></TD>
                                    <TD Colspan='4'></TD>
                                </TR>
                                <TR>
                                    <TD>First Name EN:</TD>
                                    <TD><INPUT TYPE="text" CLASS="required" ID="First_NameEN" NAME="First_NameEN" VALUE="<?php echo $First_NameEN ?>"></TD>
                                    <TD>Last Name EN:</TD>
                                    <TD><INPUT TYPE="text" CLASS="required" ID="Last_NameEN" NAME="Last_NameEN" VALUE="<?php echo $Last_NameEN ?>"></TD>
                                    <TD Colspan='2'></TD>
                                </TR>
                                <TR>
                                    <TD>First Name TH:</TD>
                                    <TD><INPUT TYPE="text" CLASS="required" ID="First_NameTH" NAME="First_NameTH" VALUE="<?php echo $First_NameTH ?>"></TD>
                                    <TD>Last Name TH:</TD>
                                    <TD><INPUT TYPE="text" CLASS="required" ID="Last_NameTH" NAME="Last_NameTH" VALUE="<?php echo $Last_NameTH ?>"></TD>
                                    <TD Colspan='2'></TD>
                                </TR>
                                <TR>
                                    <TD>Phone No. 1:</TD>
                                    <TD><INPUT TYPE="text" ID="PhoneNo1" NAME="PhoneNo1" VALUE="<?php echo $Phone_No1 ?>"></TD>
                                    <TD>Phone No. 2:</TD>
                                    <TD><INPUT TYPE="text" ID="PhoneNo2" NAME="PhoneNo2" VALUE="<?php echo $Phone_No2 ?>"></TD>
                                    <TD>Fax No.:</TD>
                                    <TD><INPUT TYPE="text" ID="FaxNo" NAME="FaxNo" VALUE="<?php echo $Fax_No ?>"></TD>
                                </TR>
                                <TR>
                                    <TD>Email Address:</TD>
                                    <TD><INPUT TYPE="text" CLASS="required" ID="EmailAddress" NAME="EmailAddress" VALUE="<?php echo $Email_Address ?>" autocomplete="off" placeholder="Example : xxx@gmail.com"></TD>
                                    <TD Colspan='4'></TD>
                                </TR>
                                <TR>
                                    <TD>Deparment:</TD>
                                    <TD><?php echo form_dropdown('Deparment_Id', $optionDeparment, $Deparment_Id, 'id=Deparment_Id') ?></TD>
                                    <TD>Position:</TD>
                                    <TD><?php echo form_dropdown('Position_Id', $optionPosition, $Position_Id, 'id=Position_Id') ?></TD>
                                    <TD>Company:</TD>
                                    <TD><?php echo form_dropdown('Company_Id', $optionCompany, $Company_Id, 'id=Company_Id') ?></TD>
                                </TR>
                                <TR>
                                    <TD Colspan='6'>
                                        <?php echo form_checkbox('IsCustomer', 1, $IsCustomer, $extra_disabled); ?>&nbsp;&nbsp;&nbsp;IsCustomer&nbsp;&nbsp;&nbsp;
                                        <?php echo form_checkbox('IsEmployee', 1, $IsEmployee, $extra_disabled); ?>&nbsp;&nbsp;&nbsp;IsEmployee&nbsp;&nbsp;&nbsp;
                                        <?php echo form_checkbox('IsSupplier', 1, $IsSupplier, $extra_disabled); ?>&nbsp;&nbsp;&nbsp;IsSupplier&nbsp;&nbsp;&nbsp;
                                        <?php echo form_checkbox('IsVendor', 1, $IsVendor, $extra_disabled); ?>&nbsp;&nbsp;&nbsp;IsVendor&nbsp;&nbsp;&nbsp;
                                        <?php echo form_checkbox('IsRenter', 1, $IsRenter, $extra_disabled); ?>&nbsp;&nbsp;&nbsp;IsRenter&nbsp;&nbsp;&nbsp;
                                        <?php echo form_checkbox('IsShipper', 1, $IsShipper, $extra_disabled); ?>&nbsp;&nbsp;&nbsp;IsShipper&nbsp;&nbsp;&nbsp;    
                                    </TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD></TR>
                <TR><TD>
                        <FIELDSET class="well"><LEGEND>User Login</LEGEND>
                            <TABLE>
                                <TR>
                                    <TD>User Account:</TD>
                                    <TD><INPUT TYPE="text" CLASS="required" ID="UserAccount" NAME="UserAccount" VALUE="<?php echo $UserAccount ?>" autocomplete="off" placeholder="User Account" onblur="check_user_account();"></TD>
                                    <TD>Password:</TD>
                                    <TD><INPUT TYPE="password" ID="Password" NAME="Password" VALUE="<?php echo $Password ?>" onblur="check_password();" autocomplete="off" placeholder="Password"></TD>
                                    <TD><button type="button" class="btn btn-success" onclick="resetPassword('password');" id="btnReset" name="btnReset" rel="tooltip" title="Reset">Reset</button></TD>
                                    <TD>
                                        &nbsp;&nbsp;&nbsp;IsSuperUser&nbsp;&nbsp;&nbsp;<?php echo form_checkbox('IsSuperUser', 1, $IsSuperUser, $extra_disabled); ?>
                                        <?php if ($mode == 'E' || $mode == 'V'): ?>
                                            <a href="#modal_about_user" data-toggle="modal" ONCLICK="getAboutUser();">
                                                <img class="minicon" src="<?php echo base_url() ?>css/images/icons/about_user.png" alt="About User" width="20" height="20" border="0">
                                            </a>
                                        <?php endif; ?>
                                    </TD>
                                </TR>
                                <TR id="conNewPW" style="display: none;">
                                    <TD Colspan='2'></TD>
                                    <TD>Confirm New Password:</TD>
                                    <TD><INPUT TYPE="password" ID="conNewPassword" NAME="conNewPassword" autocomplete="off" placeholder="Confirm New Password"></TD>
                                    <TD Colspan='2'></TD>
                                </TR>
                                <TR id="OPW" style="display: none;">
                                    <TD Colspan='2'></TD>
                                    <TD>Current Password:</TD>
                                    <TD><INPUT TYPE="password" ID="oldPassword" NAME="oldPassword" autocomplete="off" placeholder="Current Password"></TD>
                                    <TD Colspan='2'></TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD></TR>
                <?php if ($mode !== "A"): ?>
                    <TR><TD>
                            <FIELDSET class="well"><LEGEND>Status Login</LEGEND>
                                <table id="defDataTableLogin" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
                                    <thead>
                                        <tr>
                                            <th>IP Address</th>
                                            <th>Browser</th>
                                            <th>Login Date</th>
                                            <th>On App Type</th>
                                            <?php if ($mode == "E"): ?>
                                                <th>Force Logout</th>    
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (count($ststus_login_list) > 0) :
                                            foreach ($ststus_login_list as $value) :
                                                ?>
                                                <tr>
                                                    <td><?php echo $value->IP_Address; ?></td>
                                                    <td><?php echo $value->Browser_Name; ?></td>
                                                    <td><?php echo $value->Login_Date; ?></td>
                                                    <td><?php echo $value->App_Type; ?></td>
                                                    <?php if ($mode == "E"): ?>
                                                        <td><a ONCLICK="save_force_logout(<?php echo $value->Log_Id; ?>);"><?php echo img("css/images/icons/clear.png"); ?></a></td>
                                                    <?php endif; ?>
                                                </tr>
                                                <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </tbody>
                                </table>
                            </FIELDSET>
                        </TD></TR>
                <?php endif; ?>
            </TABLE>
        </FORM>
        <!--$("#modal_about_user .modal-body").html();-->
        <!-- Modal -->
        <div style="min-height:500px;padding:5px 10px;display:none;" id="modal_about_user" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modal_about_userLabel" aria-hidden="true" >
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                <h3 id="modal_about_userLabel">About User</h3>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <div style="float:right;">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </div>
        </div>

    </BODY>
</HTML>

