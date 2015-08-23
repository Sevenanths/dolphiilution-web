<?php
if (isset($_GET['id']))
{
	$id = $_GET['id'];
}
?>

<div class="card">
	<form id='patch' action='./?api=patch' method='post'>
		<input id='id' name='id' type='hidden' value='<?php echo($id); ?>'>

<?php
$compatiblexmls = getCompatibleXML($id);
printOptions($compatiblexmls);
?>
	</form>
</div>