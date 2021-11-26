<?php
//
/*
 * Create by Ton! 20140113
 * Set User Authorized Menu.
 */
?>
<SCRIPT>
    $(document).ready(function() {
        var $mode = $('#mode').val();
        if ($mode === 'V') {
            $('#btn_save').hide();
        } else if ($mode === 'E') {
            $('#btn_save').show();
        }

        $(".deleteCheckBox").click(function() {
            if (this.checked === true) {
                $(this).parents('fieldset:eq(0)').find('.viewCheckBox').prop('checked', true);
            }
        });
    });

    function submitForm() {
        $.post('<?php echo site_url() . "/user_role/save_user_role" ?>', $('#frmSetAuthorized').serialize(), function(dataSave) {
            if (dataSave == 1) {
                alert("Save Set User Authorized Menu successfully.");
                window.location = "<?php echo site_url() ?>/user_role";
            } else {
                alert("Save Set User Authorized Menu unsuccessfully.");
            }
            return;
        });
    }

    function backToList() {
        window.location = "<?php echo site_url() ?>/user_role";
    }
</SCRIPT>
<style>
    label {
        display: inline;
        margin-bottom: 2px;
    }
</style>
<HTML>
    <HEAD>
        <TITLE> Set User Authorized Menu. </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmSetAuthorized" NAME="frmSetAuthorized" METHOD='post'>
            <input type="hidden" id="mode" name="mode" value="<?php echo $mode ?>"/>
            <input type="hidden" id="UserLogin_Id" name="UserLogin_Id" value="<?php echo $UserLogin_Id ?>"/>
            <FIELDSET class="well" ><LEGEND>User Detail</LEGEND>
                <TABLE>
                    <TR>
                        <TD>User Account : </TD>
                        <TD><INPUT TYPE="text" ID="UserAccount" NAME="UserAccount" VALUE="<?php echo $UserAccount ?>" readonly></TD>
                        <TD>Name : </TD>
                        <TD><INPUT TYPE="text" ID="Contact_Name" NAME="Contact_Name" VALUE="<?php echo $Contact_Name ?>" readonly></TD>
                        <TD colspan="2"></TD>
                    </TR>
                    <TR>
                        <TD>Department Name: </TD>
                        <TD><INPUT TYPE="text" ID="Department_NameEN" NAME="Department_NameEN" VALUE="<?php echo $Department_NameEN ?>" readonly></TD>
                        <TD>Position Name: </TD>
                        <TD><INPUT TYPE="text" ID="Position_NameEN" NAME="Position_NameEN" VALUE="<?php echo $Position_NameEN ?>" readonly></TD>
                        <TD>Company Name: </TD>
                        <TD><INPUT TYPE="text" ID="Company_NameEN" NAME="Company_NameEN" VALUE="<?php echo $Company_NameEN ?>" readonly></TD>
                    </TR>
                </TABLE>
            </FIELDSET>
            <FIELDSET class="well" ><LEGEND>Permission List</LEGEND>
                <TABLE width="100%">
                    <TR style="width: 100%;">
                        <TD align="center" style="width: 100%;">    
                            <?php
                            if (count($permission_list) > 0) :
                                foreach ($permission_list as $index_module => $value_module) :
                                    ?>
                                    <ul class="tree">
                                        <?php echo "<li><input type=\"checkbox\" name=\"module[" . $index_module . "]\" value=\"" . $index_module . "\">&nbsp;&nbsp;<label><b>" . $index_module . "</b></label>" ?>
                                        <?php
                                        if (count($value_module) > 0):
                                            foreach ($value_module as $index_action => $value_action) :
                                                echo "<ul><li><input type=\"checkbox\" name=\"permission[" . $index_module . "][" . $value_action['Type_Menu'] . "][] \" value=\"" . $value_action['Edge_Id'] . "\" " . (array_key_exists($index_module, $Permission) ? (in_array($value_action['Edge_Id'], $Permission[$index_module]) ? "checked" : "") : "") . ">&nbsp;&nbsp;<label>" . $value_action['Sub_Module'] . "</label></li></ul>";
                                            endforeach;

                                            $check = FALSE;
                                            if (count($delete_jobs) > 0) :
                                                foreach ($delete_jobs as $value_del_jobs) :
                                                    if ($index_module == $value_del_jobs):
                                                        $check = TRUE;
                                                    endif;
                                                endforeach;
                                            endif;

                                            if ($check === TRUE):
                                                echo "<ul><li><input type=\"checkbox\" class=\" viewCheckBox\" name=\"permission[" . $index_module . "][-1]\" value=\"-1\" " . (array_key_exists($index_module, $Permission) ? (in_array("-1", $Permission[$index_module]) ? "checked" : "") : "") . ">&nbsp;&nbsp;<label>View List</label></li></ul>"; //View List.
                                                echo "<ul><li><input type=\"checkbox\" class=\" deleteCheckBox\" name=\"permission[" . $index_module . "][-2]\" value=\"-2\" " . (array_key_exists($index_module, $Permission) ? (in_array("-2", $Permission[$index_module]) ? "checked" : "") : "") . ">&nbsp;&nbsp;<label>Delete Job</label></li></ul>"; // Delete Job.        
                                            endif;
                                        endif;
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