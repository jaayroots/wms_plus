<?php include "header.php" ?>
<!--Add By Akkarapol, 08/11/2013, เพิ่มสคริปต์สำหรับ accordion-->
<script>
    $(function() {
        $("#accordion").accordion({
            collapsible: true
        });
    });
</script>
<!--END Add By Akkarapol, 08/11/2013, เพิ่มสคริปต์สำหรับ accordion-->
<TR class="content" style='height:100%' valign="top">
    <TD>
        <?php if($show_dashboard): ?>
            <TABLE class="roundedTable" border="0" cellpadding="0" cellspacing="0">
                <THEAD>
                    <TR>
                        <TH>
                            Dashboard
                        </TH>
                    </TR>
                </THEAD>
                <TBODY>
                    <TR>
                        <TD>                        
                            <div id="accordion">
                                <?php foreach ($dash_boards as $key_dash_board => $dash_board): ?>
                                <h3 style="font-size: 14px; font-weight: bold; ">
                                    <table>
                                        <tr>
                                            <td width="330">
                                                <?php echo $dash_board['name']; ?>
                                            </td>
                                            <td>
                                                ( <?php echo $dash_board['sum_count']; ?> )
                                            </td>
                                        </tr>
                                    </table>
                                </h3>
                                <div>
                                    <?php if (!empty($dash_board['state'])): ?>
                                    <table width="100%" border="0">
                                        <?php $stateedges = array_chunk($dash_board['state'], 3); ?>
                                        <?php foreach ($stateedges as $key_st => $st): ?>
                                        <tr>
                                            <?php $index_box = 0; ?>
                                            <?php foreach ($st as $key_stateedge => $stateedge): ?>
                                            <?php
                                            $css_left_border = '';
                                            if ($index_box != 0):
                                            $css_left_border .= 'border-left: 1px solid grey;';
                                            endif;
                                            $index_box = $index_box + 1;

                                            $css_margin_top = '';
    //                                        $css_top_border = '';
                                            if ($key_st != 0):
                                            $css_margin_top .= 'margin-top: 40px;';
    //                                        $css_top_border .= 'border-top: 1px solid grey;';
                                            endif;
                                            ?> 
                                            <td valign="top" style="<?php echo $css_left_border; ?>">
                                                <table border="0" style="margin-left:30px; <?php echo $css_margin_top; ?>">
                                                    <tr>
                                                        <td width="300">
                                                            <p style="font-size: 13px; font-weight: bold; cursor:pointer;"  class="li_dash_board ul_dash_board_<?php echo $key_dash_board; ?>" data-url="<?php echo @$url[$stateedge['Module']]; ?>"><?php echo _lang($stateedge['name']); ?>  </p>
                                                        </td>
                                                        <td width="15">

                                                        </td>
                                                        <td>
                                                            <strong><?php echo $stateedge['sum_count']; ?></strong>
                                                        </td>
                                                    </tr>
                                                    <?php if (!empty($stateedge['count'])): ?>
                                                    <?php $index_state = 0; ?>
                                                    <?php foreach ($stateedge['count'] as $key_state => $state): ?>
                                                    <tr>
                                                        <td>
                                                            <?php //echo ($index_state != 0 ? ' | ' : ''); ?> <?php echo $key_state; ?>
                                                        </td>
                                                        <td>

                                                        </td>
                                                        <td align="right">
                                                            <b>   <?php echo $state; ?> </b>
                                                        </td>
                                                    </tr>
                                                    <?php $index_state = $index_state + 1; ?>
                                                    <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </table>
                                            </td>
                                            <?php
                                            for ($i = 0;
                                            $i < (3 - count($st));
                                            ++$i):
                                            ?>
                                            <td style="border-left: 1px solid grey;">&nbsp;</td>
                                            <?php endfor; ?>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        
                        
                        </TD>
                    </TR>
                </TBODY>
                <TFOOT>                
                    <TR>
                        <TD>

                        </TD>
                    </TR>
                </TFOOT>
            </TABLE> 
                        
                        <?php else: ?>
                        
                        <image src="<?php echo base_url() . 'css/images/WMSPlus.png'; ?>" width="100%" />
                        
                        <?php endif; ?>
    </TD>
</TR>

<script>
    $(document).ready(function() {
//        $('.li_dash_board').hide();
//        $('.ul_dash_board').click(function() {
//            $('.' + $(this).data('ul')).toggle();
//        });
        $('.li_dash_board').click(function() {
            console.log($(this).data('url'));
            window.open('<?php echo base_url(); ?>' + $(this).data('url'));
        });

        <?php if($suggest_change_password == TRUE): ?>
            $('#myModalPassword').modal('show');
        <?php endif; ?>
    });
</script>

<?php include "footer.php" ?>
