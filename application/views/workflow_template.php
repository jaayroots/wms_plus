<?php include "header.php" ?>  
<TR class="content" style='height:100%' valign="top">
    <TD>
        <table class="roundedTable" border="0" cellpadding="0" cellspacing="0" >
            <thead>
                <tr align="left">
                    <th align="left">{state_name} <i class="icon-pencil icon-white"></i> {toggle}</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>{form}</td></tr>
                <!-- Remove By Ball Change to float menu -->
                <!-- <tr align="center">
                              <td>&nbsp;{button_cancel}&emsp;&emsp;{button_action}&nbsp;</td>
                </tr> -->
                <!--<tr><td>&nbsp;</td></tr>-->
            </tbody>
        </table>
    </TD>
</TR>
<?php include "footer.php"; ?>
<!-- Float Menu -->
 <!--style="width: 100%; height: 30px; background-color: #C7C8C1; position: fixed; top: 100%;  margin-top: -50px; text-align: center; padding: 10px;"-->
<div id="button_line" style="width: 100%; height: 30px; background-color: #C7C8C1; position: fixed; top: 100%;  margin-top: -50px; text-align: center; padding: 10px;">
        &nbsp;{button_cancel}&emsp;&emsp;{button_action}&nbsp;&emsp;
        <?php 
          
                if(isset($button_print_tag)){
                    echo '&emsp;{button_print_tag}&nbsp;';
                    } 
                if(isset($button_pre_dispatch_gen)){
                    echo '{button_pre_dispatch_gen} &nbsp;';       
                }
                if(isset($button_export)){
                   echo '{button_export} &nbsp;';          
                }
                if(isset($button_export)){
                   echo '{button_export} &nbsp;';          
                }
                if($this->router->fetch_method() == 'inventory_swa'){
                  
                    echo '    <input type="button" value="Stock Count Tag" class="button dark_blue" id="stock_count_tag" style="" data-toggle="modal" data-target="#Modal_filter">';
                }
        ?>
        
   <!-- <input type="button" id="btn_export_excel" class="button orange" value= "Export To Excel" style="margin: 10px;"> -->
</div>
<?php  
    if($this->router->fetch_method() == 'inventory_swa'){
            include 'pdf_report/f_stock_count_tag.php'; 
     }
?>