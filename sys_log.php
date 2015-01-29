<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require('./cfg/_uc.php');
require("./cfg/_colors.php");
require("./cfg/_globals.php");
require ("./connect.inc.php");
require ("./sess.inc.php");
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>

DrawPageTitle('Přihlášení do přihláškového systému');
?>
<FORM METHOD=POST ACTION="./login.php">
<TABLE border="0" cellpadding="0" cellspacing="2">
<TR><TD class="login">Jméno&nbsp;</TD><TD><INPUT TYPE="text" NAME="<? echo(_VAR_USER_LOGIN);?>" SIZE=10 class="login"></TD></TR>
<TR><TD class="login">Heslo&nbsp;</TD><TD><INPUT TYPE="password" NAME="<? echo(_VAR_USER_PASS);?>" SIZE=10 class="login"></TD></TR>
<TR><TD colspan="2" height="4"></TD></TR>
<TR><TD></TD><TD><INPUT TYPE="submit" VALUE="Přihlásit"></TD></TR>
</TABLE>
</FORM>


<BR>

</body>
</html>