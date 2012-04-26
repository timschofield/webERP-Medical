<?php
/* $Id$*/

include('includes/session.inc');

$title=_('File Upload');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $title.'</p><br />';

echo '<form enctype="multipart/form-data" action="Z_UploadResult.php" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />';
echo '<table class="selection">
		<tr>
			<td>' . _('Send this file') . ': </td>
			<td><input name="userfile" type="file" /></td>
		</tr>
		<tr>
			<td colspan="2"><div class="centre"><button type="submit">' . _('Send File') . '</button></div></td>
		</tr>
	</table></form>';

include('includes/footer.inc');
?>