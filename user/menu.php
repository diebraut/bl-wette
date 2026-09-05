<script>
	function submitaction(act, day)
	{
		document.menuform.action.value=act
		document.menuform.actDay.value="";
		if(day != null)
		{
			document.menuform.action.value=""
			document.menuform.actDay.value=day;
		}
		document.menuform.submit();
	}
	function showDays(ysn,obj)
	{
		if(ysn)
		{
			document.getElementById('menulist').innerHTML = document.getElementById('days').innerHTML;
		}
		else
			document.getElementById('menulist').innerHTML = "";
	}
</script>
<form method="post" name="menuform" style="display: none;">
<input type="hidden" name="user" value="<?php echo $menuuser?>">
<input type="hidden" name="action" value="">
<input type="hidden" name="actDay" value="">
</form>
<table border="0" cellpadding="0" cellspacing="0" width="600">
	<tr>
		<td><img src="pic/fussball.jpg" width="600" height="100" alt="" border="0"></td>
	</tr>
</table>
<table border="0" cellpadding="1" cellspacing="1" width="600" class="ttable">
	<tr class="head">
		<td align="center" width="100" class="menuout" style="cursor:pointer" onclick="submitaction('help')" onmouseover="showDays(false);style.background='#496241';style.color='white'" onmouseout="style.background='gray';style.color=''"><strong>Übersicht</strong></td>
		<td align="center" width="90" class="menuout" style="cursor:pointer" onclick="submitaction('list')" onmouseover="showDays(false);style.background='#496241';style.color='white'" onmouseout="style.background='gray';style.color=''"><strong>Tippen</strong></td>
		<td align="center" width="100" class="menuout" style="cursor:pointer" onclick="submitaction('bltable')" onmouseover="showDays(false);style.background='#496241';style.color='white'" onmouseout="style.background='gray';style.color=''"><strong>Tabellen</strong></td>
		<td align="center" width="100" class="menuout" style="cursor:pointer" onclick="submitaction('table')" onmouseover="showDays(false);style.background='#496241';style.color='white'" onmouseout="style.background='gray';style.color=''"><strong>Ranking</strong></td>
		<td align="center" width="100" class="menuout" style="cursor:pointer" onclick="showDays(true,this)" onmouseover="style.background='#496241';style.color='white'" onmouseout="style.background='gray';style.color=''"><strong>Spieltage</strong></td>
		<td align="center" width="110" class="menuout" style="cursor:pointer" onclick="submitaction('useroffice')" onmouseover="showDays(false);style.background='#496241';style.color='white'" onmouseout="style.background='gray';style.color=''"><strong>Mein Account</strong></td>
		<td align="center" width="110" class="menuout" style="cursor:pointer" onclick="self.location.href='./login.php?action=logout'" onmouseover="showDays(false);style.background='#496241';style.color='white'" onmouseout="style.background='gray';style.color=''"><strong>Logout</strong></td>
	</tr>
	<tr>
		<td><img src="pic/shim.gif" width="1" height="1" alt="" border="0"></td>
		<td><img src="pic/shim.gif" width="1" height="1" alt="" border="0"></td>
		<td><img src="pic/shim.gif" width="1" height="1" alt="" border="0"></td>
		<td><img src="pic/shim.gif" width="1" height="1" alt="" border="0"></td>
		<td><DIV id="menulist" style="position:absolute;z-index:0;"></DIV></td>
		<td><img src="pic/shim.gif" width="1" height="1" alt="" border="0"></td>
		<td><img src="pic/shim.gif" width="1" height="1" alt="" border="0"></td>
	</tr>
</table>
<div id="days" style="position:absolute;visibility:hidden;z-index=0;left:0px">
	<table class="tablestyle" cellpadding="2" cellspacing="0" width="160">
		<?php 
		$result = mysqli_query($db,"select DISTINCT(intTag) FROM tblspieltag");
		$anzahl = mysqli_num_rows($result);
		for($i=0; $i < $anzahl; $i++)
		{
			$nDay = mysql_result($result, $i, "intTag");
			?>
				<tr>
					<td width="100%" style="cursor:pointer" onclick="submitaction('', '<?php echo $nDay?>')" <?php if(($i+1) % 2){echo "class=\"menusecondline\"";}else{echo "class=\"menufirstline\"";}?>>
						<?php echo $nDay?>. Spieltag
					</td>
				</tr>
			<?php
		}
		?>
	</table>
</div>
