<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once ("connect.inc.php");
require_once ("sess.inc.php");
require_once ("ctable.inc.php");
if (!IsLoggedFinance()) {
    header("location: ".$g_baseadr."error.php?code=21");
    exit;
}
require_once("cfg/_globals.php");
require_once("cfg/race_enums.php");

db_Connect();

$id = (int)(filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 0);
$is_new = isset($_REQUEST['new']);

$finTypes = query_db("SELECT * FROM " . TBL_FINANCE_TYPES);
$zaznam = null;

if ( !$is_new ) {
    $res = query_db("SELECT * FROM " . TBL_PAYRULES . " WHERE id = $id");
    $zaznam = mysqli_fetch_array($res);
}

if (!isset($head_addons)) $head_addons = ''; 
$head_addons .="\t".'<script src="finance.js" type="text/javascript"></script>'."\n";
require_once ("./header.inc.php");
require_once ("./common.inc.php");
require_once ("./common_fin.inc.php");

DrawPageTitle( ($is_new ? 'Nová' : 'Editace' ) . ' definice platby');
/**
 * Helper to render one checkbox row
 */
function renderCheckboxRow(string $label, CheckboxRow $cbr): void
{
    echo "<tr>
            <td align='right' valign='top'>{$label}</td>
            <td><div class='checkbox-row'>{$cbr->render()}</div></td>
          </tr>";
}
?>

<table cellpadding="0" cellspacing="0" border="0">

<form method="post" action="fin_payrule_edit_exc.php">
<input type="hidden" name="id" value="<?= $id ?>">

    <?php
    // --- Sport ---
    $cbr = new CheckboxRow ( '', 'typ', true, 'typ', !$is_new );
    foreach ($g_racetype as $t) {
        $checked = !$is_new && (!isset($zaznam['typ']) || $zaznam['typ'] === $t['enum']);
        $cbr->addEntry($t['nm'], null, $t['enum'], $checked, true);
    }
    renderCheckboxRow('Sport', $cbr);

    // --- Typ akce ---
    $cbr = new CheckboxRow ( '', 'typ0', true, 'typ0', !$is_new );

    foreach ($g_racetype0 as $key => $value) {
        $checked = !$is_new && (!isset($zaznam['typ0']) || $zaznam['typ0'] === $key);
        $cbr->addEntry($value, null, $key, $checked, true);
    }
    renderCheckboxRow('Typ akce', $cbr);

    // --- Termín ---
    $cbr = new CheckboxRow ( '', 'termin', true, 'termin', !$is_new );

    for ($t=1; $t<=5; $t++) {
        $checked = !$is_new && (
            !isset($zaznam['termin']) ||
            $zaznam['termin'] == $t ||
            ($zaznam['termin'] < 0 && $zaznam['termin'] >= -$t)
        );
        $cbr->addEntry($t, null, $t, $checked, true);
    }
    renderCheckboxRow('Termín', $cbr);

    // --- Žebříček ---
    $cbr = new CheckboxRow ( '', 'zebricek', true, 'zebricek', !$is_new );
    foreach ($g_zebricek as $zb) {
        $checked = !$is_new && (!isset($zaznam['zebricek']) || ( ( $zaznam['zebricek'] & $zb['id'] ) != 0));
        $cbr->addEntry($zb['nm'], null, $zb['id'], $checked, true);
    }
    renderCheckboxRow('Žebříček', $cbr);

    // --- Finanční typ ---
    $cbr = new CheckboxRow ( '', 'finType', true, 'finance_type' );
    while ($ft = mysqli_fetch_array($finTypes)) {
        $checked = !$is_new && ( !isset($zaznam['finance_type']) || $zaznam['finance_type'] === $ft['id']);
        $cbr->addEntry($ft['nazev'], $ft['popis'], $ft['id'], $checked, true);
    }
    renderCheckboxRow('Finanční typ', $cbr);
?>

    <!-- Typ platby -->
    <tr>
        <td align="right">Typ platby</td>
        <td>
            <?php
    $paymentTypes = ['C' => 'Z celé', 'R' => 'Z rozdílu', 'P' => 'Pevná'];
    foreach ($paymentTypes as $key => $label) {
                $checked = (
                    ($zaznam['druh_platby'] ?? '') === $key
                    || ($is_new && $key === 'C')
                ) ? 'checked' : '';
                echo <<<HTML
                    <input type="radio" name="payment_type" value="$key" id="pt_$key"
                           onclick="toggleAmount('$key')" $checked>
                    <label for="pt_$key">$label</label>&nbsp;
HTML;
    }
	    ?>
        </td>
    </tr>

    <!-- Platba -->
    <tr id="amountRow">
        <td align="right">Platba</td>
        <td>
        &nbsp;<input type="text" name="amount" 
                value="<?= isset ( $zaznam['druh_platby'] ) ? $zaznam['platba'] : '' ?>">
            <span id="amount_unit"><?= isset ( $zaznam['druh_platby'] ) &&  $zaznam['druh_platby'] === 'P' ? 'Kč' : '%' ?></span>
        </td>
    </tr>

    <?php
    // --- Účtováno ---
        $cbr = new CheckboxRow ( '', 'uctovano', true, 'uctovano', false );
        foreach ($g_uctovano as $uc) {
            $checked = !$is_new && (!isset($zaznam['uctovano']) || (($zaznam['uctovano'] & $uc['id']) != 0));
            $cbr->addEntry($uc['nm'], null, $uc['id'], $checked, true);
        }
        renderCheckboxRow('Účtováno', $cbr);
    ?>

    <tr><td colspan="2" align="center"><input type="submit" value="Uložit"></td></tr>
</form>
<BR><hr><BR>
<? echo('<A HREF="index.php?id='._FINANCE_GROUP_ID_.'&subid=4">Zpět</A><BR>'); ?>
<BR><hr><BR>
</CENTER>
</TD>
<TD></TD>
</TR>
<TR><TD COLSPAN=3 ALIGN=CENTER>
<!-- Footer Begin -->
<? require_once ("footer.inc.php"); ?>
<!-- Footer End -->
</TD></TR>
</TABLE>

<script>
document.addEventListener("DOMContentLoaded", () => { initCheckboxGroups(); });

function toggleAmount( type ) {
    document.getElementById('amount_unit').innerHTML = (type === 'P') ? 'Kč' : '%';
}
</script>

<? HTML_Footer(); ?>
