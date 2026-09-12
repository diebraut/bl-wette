<?php if (!empty($liveFragment)) return; ?>
<script>
window.blLiveConfig = <?php echo json_encode(['user' => $menuuser, 'view' => in_array($action ?? '', ['table','bltable','list','help'], true) ? $action : 'day', 'day' => (int)($actDay ?? 1), 'currentDay' => (int)mysqli_fetch_row(mysqli_query($db, 'SELECT intDay FROM tblinfo LIMIT 1'))[0]], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>
<script src="live.js" defer></script>
<div id="live-status" hidden></div>
<script>
	<?php if (!empty($localPersistentLogin)) { ?>
	(function () {
		var timeout = <?php echo isset($localSessionLifetime) ? (int)$localSessionLifetime * 1000 : 1800000; ?>;
		var timer;
		var sessionUser = <?php echo json_encode($menuuser); ?>;
		var redirectToLogin = function () {
			if (sessionUser && navigator.sendBeacon) {
				var data = new FormData();
				data.append('action', 'logout');
				data.append('user', sessionUser);
				navigator.sendBeacon('login.php', data);
			}
			window.location.replace('login.php?timeout=1');
		};
		var resetTimer = function () {
			window.clearTimeout(timer);
			timer = window.setTimeout(redirectToLogin, timeout);
		};
		['click', 'keydown', 'input', 'touchstart', 'mousemove'].forEach(function (eventName) {
			window.addEventListener(eventName, resetTimer, { passive: true });
		});
		resetTimer();
	}());
	<?php } ?>

	function logout()
	{
		<?php if (!empty($localPersistentLogin)) { ?>
			var data = new FormData();
			data.append('action', 'logout');
			data.append('user', <?php echo json_encode($menuuser); ?>);
			if (navigator.sendBeacon) navigator.sendBeacon('login.php', data);
			window.location.replace('login.php?timeout=1');
		<?php } else { ?>
			self.location.href='./login.php?action=logout';
		<?php } ?>
	}

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
		<td align="center" width="110" class="menuout" style="cursor:pointer" onclick="logout()" onmouseover="showDays(false);style.background='#496241';style.color='white'" onmouseout="style.background='gray';style.color=''"><strong>Logout</strong></td>
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
