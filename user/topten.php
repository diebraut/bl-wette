<table width="150px" cellpadding="2" cellspacing="0" class="tablestyle"> 
<tr>
	<td class="head"><strong>Platz</strong></td>
	<td class="head"><strong>Username</strong></td>
	<td class="head"><strong>Punkte</strong></td>
</tr>
<?php

/* userpunkte-tabelle holen */  
$userPoints = getUserResultArray();

$rank = 0;

foreach ($userPoints as $item)
{
    $alias = $item[1];
    $point = $item[2];
    $rank++;
    ?>
	<tr>
		<td width="25" align="center"><?php echo $rank?>.</td>
		<td><?php echo $alias?></td>
		<td width="50" align="center"><?php echo $point?></td>
	</tr>
    <?php
}
?>
</table>