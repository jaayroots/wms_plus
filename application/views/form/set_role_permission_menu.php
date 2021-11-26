<SCRIPT>
    $(document).ready(function() {
        var $mode = $('#mode').val();
        if ($mode === 'V') {
            $('#btn_save').hide();
            
            $(":checkbox").each(function () {
                $(this).attr("disabled", "disabled");
            });
        } else if ($mode === 'E') {
            $('#btn_save').show();
        }
        
        $(":text").each(function () {
            $(this).attr("readonly", true);
        });

        // childChk
        $('.childChk').click(function(e, flag) {
            if (flag === false) {
                e.preventDefault();
            }

            var _secondChk = $(this).parent().parent().parent().find('input.secondChk');
            var _childChk = $(this).parent().parent().parent().find('input.childChk');
            var _lengthChild = _childChk.length;
            var _countChild = 0;
            if ($(this).prop('checked')) {
                $.each(_childChk, function(idx, value) {
                    if ($(value).prop('checked') === true) {
                        _countChild++;
                    }
                });

                if (_countChild === _lengthChild) {
                    $(_secondChk).prop('checked', true);
                }

            } else {
                if (_secondChk.prop('checked') === true) {
                    _secondChk.prop('checked', '');
                }
            }
        });

        // secondChk
        $(".secondChk").click(function(e, flag) {
            if (flag === false) {
                e.preventDefault();
            }

            var _fristChk = $(this).parent().parent().parent().find('input.fristChk');
            var _secondChk = $(this).parent().parent().parent().find('input.secondChk');
            var _lengthSecond = _secondChk.length;
            var _countSecond = 0;
            if ($(this).prop('checked')) {
                $.each(_secondChk, function(idx, value) {
                    if ($(value).prop('checked') === true) {
                        _countSecond++;
                    }
                });
                if (_countSecond === _lengthSecond) {
                    $(_fristChk).prop('checked', 'checked');
                }
            } else {
                $.each(_secondChk, function(idx, value) {
                    if ($(value).prop('checked') === false) {
                        _countSecond++;
                    }
                });
                if (_countSecond !== _lengthSecond) {
                    $(_fristChk).prop('checked', '');
                }
            }
        });

        // viewChk
        $(".viewChk").click(function() {
            if (this.checked === false) {
                $(this).parents('.treeParent:eq(0)').parents('.treeParent:eq(0)').find('.addChk').prop('checked', false);
                $(this).parents('.treeParent:eq(0)').parents('.treeParent:eq(0)').find('.editChk').prop('checked', false);
                $(this).parents('.treeParent:eq(0)').parents('.treeParent:eq(0)').find('.deleteChk').prop('checked', false);
            }
        });

        // addChk
        $(".addChk").click(function() {
            if (this.checked === true) {
                $(this).parents('.treeParent:eq(0)').parents('.treeParent:eq(0)').find('.viewChk').prop('checked', true);
            }
        });

        // editChk
        $(".editChk").click(function() {
            if (this.checked === true) {
                $(this).parents('.treeParent:eq(0)').parents('.treeParent:eq(0)').find('.viewChk').prop('checked', true);
            }
        });

        // deleteChk
        $(".deleteChk").click(function() {
            if (this.checked === true) {
                $(this).parents('.treeParent:eq(0)').parents('.treeParent:eq(0)').find('.viewChk').prop('checked', true);
            }
        });

        $('.childChk').trigger('click', false);
    });

    function submitForm() {
        if (confirm("You want to save the data Role Permission?")) {
            $.post('<?php echo site_url('/role_permission/save_role_permission') ?>', $('#frmSetRolePermission').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save Set Role Permission Menu successfully.");
                    window.location = '<?php echo site_url('/role_permission') ?>';
                } else {
                    alert("Save Set Role Permission Menu unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                }
                return;
            });
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }

    function backToList() {
        window.location = "<?php echo site_url() ?>/role_permission";
    }
</SCRIPT>
<style>
    label {
        display: inline;
        margin-bottom: 2px;
    }
</style>
<STYLE tyle="css/text">
    .w120{ width: 120px;  padding: 5px; }
    .w150{ width: 150px;  padding: 5px; }
    .txt-r{ text-align: right; }
    .txt-l{ text-align: left; }
    .txt-c{ text-align: center; }
</style>
<HTML>
    <HEAD>
        <TITLE> Set Use Permission Menu. </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmSetRolePermission" NAME="frmSetRolePermission" METHOD='post'>
            <input type="hidden" id="mode" name="mode" value="<?php echo $mode ?>"/>
            <input type="hidden" id="UserRole_Id" name="UserRole_Id" value="<?php echo $UserRole_Id ?>"/>
            <FIELDSET class="well" ><LEGEND>Role Detail</LEGEND>
                <TABLE>
                    <TR>
                        <TD class="w150 txt-r">UserRole Code : </TD>
                        <TD><INPUT TYPE="text" class="string_special_characters-f" ID="UserRole_Code" NAME="UserRole_Code" VALUE="<?php echo $UserRole_Code ?>"></TD>
                        <TD class="w150 txt-r">UserRole Name EN : </TD>
                        <TD><INPUT TYPE="text" class="string_special_characters-f" ID="UserRole_NameEN" NAME="UserRole_NameEN" VALUE="<?php echo $UserRole_NameEN ?>"></TD>
                        <TD class="w150 txt-r">UserRole Name TH : </TD>
                        <TD><INPUT TYPE="text" class="string_special_characters-f" ID="UserRole_NameTH" NAME="UserRole_NameTH" VALUE="<?php echo $UserRole_NameTH ?>"></TD>
                    </TR>
                    <TR>
                        <TD class="w150 txt-r">UserRole Desc : </TD>
                        <TD colspan="5"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="UserRole_Desc" NAME="UserRole_Desc" style="resize:none; width:99%;" rows="2" readonly><?php echo $UserRole_Desc ?></TEXTAREA></TD>
                    </TR>
                </TABLE>
            </FIELDSET>
            <FIELDSET class="well" ><LEGEND>Permission List</LEGEND>
                <TABLE width="100%">
                    <TR style="width: 100%;">
                        <TD align="center" style="width: 100%;">    
                            <?php
                            if (count($permission_list) > 0) :
                                foreach ($permission_list as $index_mnu_parent => $value_mnu_parent) :
                                    ?>
                                            <ul class="tree treeParent" style="border: none;">
                                        <?php echo "<li><input type=\"checkbox\" class=\"fristChk\" id=\"mnu_pasrent[" . $value_mnu_parent['MenuBar_NameEn'] . "]\" name=\"mnu_pasrent[" . $value_mnu_parent['MenuBar_NameEn'] . "]\" value=\"" . $value_mnu_parent['MenuBar_NameEn'] . "\">&nbsp;&nbsp;<label for='mnu_pasrent[" . $value_mnu_parent['MenuBar_NameEn'] . "]'><b>" . $value_mnu_parent['MenuBar_NameEn'] . "</b></label>" ?>
                                        <?php foreach ($value_mnu_parent['Child_Menu'] as $index_mnu_child => $value_mnu_child) : ?>
                                            <?php
                                            if (isset($value_mnu_child['Child_Module'])) :
                                                echo "<ul class=\"tree treeParent\" style=\"border: none;\"><li><input type=\"checkbox\" class=\"secondChk\" id=\"mnu_child[" . $value_mnu_child['MenuBar_Id'] . "]\" name=\"mnu_child[" . $value_mnu_child['MenuBar_Id'] . "]\" value=\"" . $value_mnu_child['MenuBar_Id'] . "\">&nbsp;&nbsp;<label for='mnu_child[" . $value_mnu_child['MenuBar_Id'] . "]'><b>" . $value_mnu_child['MenuBar_NameEn'] . "</b></label>";
                                                foreach ($value_mnu_child['Child_Module'] as $index_child_module => $value_child_module) :
                                                    if (isset($value_child_module['Edge_Id'])) :
                                                        $proc_type = "W";
                                                        $proc_id = $value_child_module['Edge_Id'];
                                                        $class = " class=\"childChk\" ";
                                                    else :
                                                        $proc_type = "M";
                                                        $proc_id = $value_child_module['Action_Id'];

                                                        if ($proc_id == "-1"):
                                                            $class = " class=\"viewChk childChk\" ";
                                                        elseif ($proc_id == "-2"):
                                                            $class = " class=\"addChk childChk\" ";
                                                        elseif ($proc_id == "-3"):
                                                            $class = " class=\"editChk childChk\" ";
                                                        elseif ($proc_id == "-4"):
                                                            $class = " class=\"deleteChk childChk\" ";
                                                        endif;
                                                    endif;
                                                    echo "<ul class=\"tree treeParent\" style=\"border: none;\"><li><input type=\"checkbox\"" . $class . "id=\"mnu_child_module[".$value_mnu_child['MenuBar_Id']."][]\" name=\"mnu_child_module[" . $value_mnu_child['MenuBar_Id'] . "][]\" value=\"" . $proc_id . "|" . $proc_type . "\" " . (array_key_exists($value_mnu_child['MenuBar_Id'], $Permission) ? (in_array($proc_id, $Permission[$value_mnu_child['MenuBar_Id']]) ? "checked=\"checked\"" : "") : "") . ">&nbsp;&nbsp;<label for='mnu_child_module[".$value_mnu_child['MenuBar_Id']."][]'>" . $value_child_module['Description'] . "</label></li></ul>";
                                                endforeach;
                                            endif;
                                            echo "</ul>";
                                        endforeach;
                                        ?>
                                            </ul>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </TD>
                    </TR>
                </TABLE>
            </FIELDSET>
        </FORM>
    </BODY>
</HTML>
