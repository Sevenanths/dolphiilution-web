<?php
if (isset($_GET['id']))
{
	$id = $_GET['id'];
}
?>

<div class="content overlay">
	<div class="card">
		<form id='patch' action='./?page=patch' method='post'>
		<input id='id' name='id' type='hidden'>
		<?php
			$compatiblexmls = getCompatibleXML($id);
			printOptions($compatiblexmls);
		?>
		</form>
	</div>
</div>

<script>
	var id	  = getParameterByName("id");
	var games = JSON.parse('<?php printLibrary(); ?>');
	var front = document.getElementsByTagName('body')[0];
	front.style.backgroundImage = "url(" + games[id].boxart + ")";
	
	document.getElementById('info').innerHTML 	= games[id].title; 
	document.getElementById('id').value			= id;
	document.addEventListener('keydown', keyDown, false);

	function keyDown(event)
	{
        switch( event.keyCode )
        {
            case 13:
            	patch();
            	event.preventDefault();
            break; // enter/accept
        }
    }

    function patch()
    {
    	document.getElementById('patch').submit();
    }
</script>