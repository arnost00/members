<HR>
<table>
<?
$query_fin_club_sum = "select sum(if(f.amount<0,f.amount,0)) as minus, sum(if(f.amount>0,f.amount,0)) as plus, sum(f.amount) as total from ".TBL_FINANCE." f where (f.storno != 1 or f.storno is null);";

@$result=query_db($query_fin_club_sum);

$record=mysqli_fetch_array($result);

echo "<tr><td><b>Total</b></td><td align=right><b>$record[total]</b></td></tr>";
echo "<tr><td>Plus</td><td align=right>$record[plus]</td></tr><tr><td>Minus</td><td align=right>$record[minus]</td></tr>";

?>
</table>