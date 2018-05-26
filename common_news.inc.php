<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?

function PrintNewsItem(&$zaznam,$admin, $user, $passive)
{
	global $g_www_admin_id;
	
	$show_all = true;
	$class_item = 'NewsItem';
	$class_title = 'NewsItemTitle';
	$class_date = 'NewsItemDate';
	if ($zaznam['internal'])
	{
		$show_all = ($admin || $user->logged);
		$class_item .= 'Int';
		$class_title .= 'Int';
		$class_date .= 'Int';
	}

	$datum = Date2String($zaznam['datum']);
	echo '<TR><TD class="'.$class_date.'">'.$datum.'&nbsp;&nbsp;</TD>';
	if ($zaznam['nadpis']!='') echo '<TD class="'.$class_title.'">'.$zaznam['nadpis'].' </TD></TR><TR><TD></TD>';
	$name_id = $zaznam['id_user'];

	echo '<TD class="'.$class_item.'">';
	if ($show_all)
		echo $zaznam['text'];
	else
		echo ('<i>- interní novinka / po přihlášení -</i>');
	
	if ($name_id && $zaznam['podpis'] != '' && $name_id != $g_www_admin_id)
		echo '&nbsp;<span class="NewsAutor">[&nbsp;'.$zaznam['podpis'].'&nbsp;]</span>';
	if ( ( ($user->account_id == $name_id) || $admin ) && !$passive)
		echo '<span class="DisableText">&nbsp;&nbsp;(&nbsp;<A HREF="./news_edit.php?id='.$zaznam['id'].'" class="NewsEdit">Editovat</A>&nbsp;/&nbsp;<A HREF="./news_del_exc.php?id='.$zaznam['id'].'" onclick="return confirm_delete(\''.$datum.'\')" class="NewsErase">Smazat</A>&nbsp;)</span>';
	echo '</TD></TR>';

}

?>