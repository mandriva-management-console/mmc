<?php

//require("modules/squid/includes/config.inc.php");
//require("modules/squid/includes/squid.inc.php");
require("modules/squid/normalgroup/localSidebar.php");
require("graph/navbar.inc.php");


$p = new PageGenerator();
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

?>



	<link href="modules/squid/normalgroup/accesslog/basic.css" media="screen" rel="Stylesheet" type="text/css" />
	<link href="modules/squid/normalgroup/accesslog/tabs.css" media="screen" rel="Stylesheet" type="text/css" />
	<link href="modules/squid/normalgroup/accesslog/rsys.css" media="screen" rel="Stylesheet" type="text/css" />
	<link href="modules/squid/normalgroup/accesslog/login.css" media="screen" rel="Stylesheet" type="text/css" />

	<script src="modules/squid/normalgroup/accesslog/includes/detect_browser.js" type="text/javascript"></script>
	<script src="modules/squid/normalgroup/accesslog/includes/prototype.js" type="text/javascript"></script>
	<script src="modules/squid/normalgroup/accesslog/includes/effects.js" type="text/javascript"></script>
	<script src="modules/squid/normalgroup/accesslog/includes/controls.js" type="text/javascript"></script>
	<script src="modules/squid/normalgroup/accesslog/includes/dragdrop.js" type="text/javascript"></script>
	<script src="modules/squid/normalgroup/accesslog/includes/application.js" type="text/javascript"></script>
	
	<script src='modules/squid/normalgroup/accesslog/includes/squidnow.js'></script>
	<script src='modules/squid/normalgroup/accesslog/includes/drag.js'></script>



   <div id='controlcontainer' style='width: 300px; top: 160px; display: none'>
      <div class="bar" style="height: 20; background: #aaa; cursor: move" onmousedown="dragStart(event, 'controlcontainer')">
      	<center><font color='black'>move</font></center>
      </div>

         <div id='control' style='padding: 10px'>
   
   		<!--SPAN id='info'></SPAN--> 
    		<center>
		<input id='transbutton' type=button onclick="trans(document.getElementById('controlcontainer'))" value='<?php echo  _T("Opacity"); ?>' >
		<input type=button onclick="reposition(document.getElementById('controlcontainer'))" value='<?php echo  _T("Default Position"); ?>'>
   		<form name='form1' id='form1'><br>
    		<table>
    			<tr>	
			<td align=right><?php echo  _T("User"); ?> </td><td> <input type=text name='formuser' size=10></td>
      			</tr><tr>
			<td align=right><?php echo  _T("IP"); ?></td><td> <input type=text name='formip' size=10></td>
      			</tr><tr>
			<td align=right><?php echo  _T("Url"); ?></td><td> <input type=text name='formurl' size=10></td>
      			</tr>
		</table>

		<input type=button name=limpar onclick='resetall()' value='<?php echo  _T("Reset"); ?>'>
      
		<font title='<?php echo  _T("Refresh rate"); ?>'>fps:</font> 

		<select name=interval onchange='setinterval(this.value)'>
			<option value=0.5>0.5</option>
			<option value=1>1</option>
			<option value=3>3</option>
			<option value=5>5</option>
			<option value=10>10</option>
			<option value=15>15</option>
		</select>

      		<script lanaguage='text/javascript' > document.form1.interval.value = interval</script>
      
      		<IMG id='img' title='Refresh activate' src='modules/squid/normalgroup/accesslog/images/on.gif'><br/><br/>
      		<table border=0 style='display: inline'>
        	<tr>
		<td width=100><input id='pausebutton' type=button value='<?php echo  _T("Pause"); ?>' onclick='pausecont(this)'><font size=1></font><td>
          		<td><input id='prev' type=button value='  <<  ' onclick='go_history(-1)'><font size=1></font></td>
          		<td><input id='next' type=button value='  >>  ' onclick='go_history(1)'><font size=1></font><span id='debug'></span></td>
        	</tr>
      		</table>
      		<br><br>
		
		<span id='bar'></span><br/><br/>
      
		<table>
    		<tr>
    		<td>
			<input type=checkbox name='ignoreurl' onclick='this.checked = false; alert("Option not allowed.")'>
		</td><td>
		<font size=2><?php echo  _T("Hide diferent paths"); ?></font>
		</td>
      		</tr><tr>
		<td>
      			<input type=checkbox name='ignorednsn' onclick='this.checked = false; alert("Option not allowed.")'>
		</td><td>
      			
		<font size=2><?php echo  _T("Hide diferent sub domains"); ?></font>
		</td>
      		</tr>
      		</table>
      		</form> 
    		</center>
    </div> 
  </div>

<table cellpadding=20>
	<tr><td>
	<table id='tblbox' name='tblbox' border=0>  
		<tr id='tbheader' style='font-size: 80%'></tr>
		<tr></tr>
	</table>
	</td></tr>
</table>
	<center><table id='wait_warn'>
		<tr>
			<td><img style='display: inline' src='modules/squid/normalgroup/accesslog/images/icon_wait.gif'></td>
			<td><b><?php echo  _T("Verify logs..."); ?></b></td>
		</tr>
	</table></center>
    
<script src='modules/squid/normalgroup/accesslog/includes/squidnow_poshtml.js'></script>

