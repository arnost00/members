<?php
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>

<?
/**
 * Helper to render one checkbox row
 */
function renderCheckboxRow(string $label, string $all, CheckboxRow $cbr): void
{
    echo "<tr><td style='padding-top: 10px' colspan='2'>{$label}</td></tr>";
    echo "<tr>
            <td style='padding-left: 10px' align='right'>{$all}</td>
            <td><div class='checkbox-row'>{$cbr->render()}</div></td>
          </tr>";
}
if (IsLoggedFinance ())
	{
?>

<br><hr><br>

<?php
if(IsSet($update))
		DrawPageSubTitle('Formulář pro editaci definice platby');
	else
	{
		DrawPageSubTitle('Formulář pro vložení nové definice platby');
		$zaznam['id'] = -1;
	}
?>

</CENTER>
    Pokud se skupinám členů účtují další náklady, lze zde definovat pravidla,
    podle kterých se určí výše doplatku. Pokud není nalezeno žádné pravidlo,
    člen nic nedoplácí.<br>
    Pro aktivaci pravidla musí platit alespoň jedna hodnota v každé skupině
    podmínek (sport, typ akce, termín, žebříček, finanční typ).

<form method="post" action="fin_payrule_edit_exc.php" <?if (IsSet($update)) echo "?update=".!$update?>">
<input type="hidden" name="id" value="<?= $id ?>">

    <table cellpadding="0" cellspacing="0" border="0">
    <TR>
    <TD width="2%"></TD>
    <TD width="90%" ALIGN=left>
    <CENTER>


    <?php
    // --- Sport ---
    $cbr = new CheckboxRow ( '', 'typ', true, 'typ', !$is_new );
    foreach ($g_racetype as $t) {
        $checked = !$is_new && (!isset($zaznam['typ']) || $zaznam['typ'] === $t['enum']);
        $cbr->addEntry($t['nm'], null, $t['enum'], $checked, true);
    }
    renderCheckboxRow('Člen doplácí pro druhy sportu', 'Všechny', $cbr);

    // --- Typ akce ---
    $cbr = new CheckboxRow ( '', 'typ0', true, 'typ0', !$is_new );

    foreach ($g_racetype0 as $key => $value) {
        $checked = !$is_new && (!isset($zaznam['typ0']) || $zaznam['typ0'] === $key);
        $cbr->addEntry($value, null, $key, $checked, true);
    }
    renderCheckboxRow('Člen doplácí za typy akcí', 'Všechny', $cbr);

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
    renderCheckboxRow('Člen doplácí startovné nebo zvýšení startovného za termíny', 'Všechny', $cbr);

    // --- Žebříček ---
    $cbr = new CheckboxRow ( '', 'zebricek', true, 'zebricek', !$is_new );
    foreach ($g_zebricek as $zb) {
        $checked = !$is_new && (!isset($zaznam['zebricek']) || ( ( $zaznam['zebricek'] & $zb['id'] ) != 0));
        $cbr->addEntry($zb['nm'], null, $zb['id'], $checked, true);
    }
    renderCheckboxRow('Člen doplácí podle žebříčků závodu', 'Všechny', $cbr);

    // --- Finanční typ ---
    $cbr = new CheckboxRow ( '', 'finType', true, 'finance_type' );
    foreach ($financial_types as $ft) {
        $checked = !$is_new && ( !isset($zaznam['finance_type']) || $zaznam['finance_type'] === $ft['id']);
        $cbr->addEntry($ft['nazev'], $ft['popis'], $ft['id'], $checked, true);
    }
    renderCheckboxRow('Doplácí členové s finančním typem', 'Všichni', $cbr);
?>

    <!-- Typ platby -->
    <tr><td style="padding-top: 10px" colspan="2">Jak se určuje výše doplatku člena</td></tr>
    <tr><td/>
        <td>
            <?php
    $paymentTypes = ['C' => 'Procenta z celých nákladů', 'R' => 'Procenta z rozdílu v nákladech', 'P' => 'Pevná částka v Kč'];
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
        <tr><td style="padding-top: 10px" colspan="2">Zadejte výši doplatku</td>
        <tr><td></td>
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
            $cbr->addEntry($uc['char'] . ' ' . $uc['nm'], null, $uc['id'], $checked, true);
        }
        renderCheckboxRow('Položky jichž se zvolený doplatek týká', 'Vše', $cbr);
    ?>


    <tr><td/><td style="padding-top: 10px"><input type="submit" value="Uložit"></td></tr>
    </table>

</form>

<script>
document.addEventListener("DOMContentLoaded", () => { initCheckboxGroups(); });

function toggleAmount( type ) {
    document.getElementById('amount_unit').innerHTML = (type === 'P') ? 'Kč' : '%';
}
</script>

<?php
}
?>
