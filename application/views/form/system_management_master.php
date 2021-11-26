<FORM ACTION="saveSystem" METHOD="POST">
    <FIELDSET class="well">
        <TABLE width="100%">
            <TR>
                <TD width="150em">System Type</TD>
                <TD>
                    <SELECT NAME="system_type" ID="system_type">
                        <OPTION>SELECT System type</OPTION>
                        <?php foreach ($data['selectSystem'] as $type) : ?>
                            <OPTION VALUE="<?php echo $type; ?>" <?php if ($type == $data['code']) echo 'SELECTED="SELECTED"'; ?>><?php echo $type; ?></OPTION>
                        <?php endforeach; ?>
                    </SELECT>
                </TD>
            </TR>
            <?php $value = $data['values']; ?>
            <TR>
                <TD width="150em">CODE</TD>
                <TD><INPUT TYPE="text" NAME="code" ID="code" value="<?php
                    if (array_key_exists('Dom_Code', $value)) :
                        echo $value['Dom_Code'];
                    endif;
                    ?>"></TD>
            </TR>
            <TR>
                <TD width="150em">Description Thai</TD>
                <TD><INPUT TYPE="text" NAME="description_thai" id="description_thai" value="<?php
                    if (array_key_exists('Dom_TH_Desc', $value)) :
                        echo $value['Dom_TH_Desc'];
                    endif;
                    ?>"></TD>
            </TR>
            <TR>
                <TD width="150em">Description English</TD>
                <TD><INPUT TYPE="text" NAME="description_eng" id="description_eng" value="<?php
                    if (array_key_exists('Dom_EN_Desc', $value)) :
                        echo $value['Dom_EN_Desc'];
                    endif;
                    ?>"></TD>
            </TR>

            <!-- <TR>
                  <TD>Status</TD>
                  <TD><INPUT TYPE="radio" NAME="status" VALUE="1" <?php
//            if (array_key_exists('Dom_Active', $value)) :
//                if ($value['Dom_Active'] == "1")
//                    echo 'checked="checked"';
//            endif;
            ?>> YES
                          <INPUT TYPE="radio" NAME="status" VALUE="0" <?php
//            if (array_key_exists('Dom_Active', $value)) :
//                if ($value['Dom_Active'] == "0")
//                    echo 'checked="checked"';
//            endif;
            ?>> NO
                  </TD>
            </TR> -->
        </TABLE>
        <TABLE width="100%">
            <TR>
                <TD align="center">
                    <?php if($data['flag_back_to_main']): ?>
                        <INPUT TYPE="button" class="button dark_blue" VALUE="BACK" ONCLICK="location.href = '<?php echo site_url(); ?>/systemManagement';"> 
                    <?php else: ?>
                        <INPUT TYPE="button" class="button dark_blue" VALUE="BACK" ONCLICK="location.href = '<?php echo site_url(); ?>/systemManagement/showDetail?id=<?php echo $data['main_id']; ?>';"> 
                    <?php endif; ?>
                    <!--<INPUT TYPE="button" class="button dark_blue" VALUE="CLEAR" ONCLICK="">--> 
                    <INPUT TYPE="submit" class="button dark_blue" VALUE="SAVE">

                    <input type="hidden" name="do_action" value="<?php echo $data['action']; ?>" >
                    <input type="hidden" name="edit_id" value="<?php
                    if (array_key_exists('Dom_ID', $value)) :
                        echo $value['Dom_ID'];
                    endif;
                    ?>">
                    <input type="hidden" name="main_id" value="<?php echo $data['main_id']; ?>" >
                </TD>
            </TR>            
        </TABLE>
    </FIELDSET>
</FORM>