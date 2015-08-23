<div class="content">
<div class="cardsmall">
<h1>Patching..</h1>
<?php
$id = $_POST['id'];
unset($_POST['id']);

$json  = file_get_contents($library);
$games = json_decode($json, true);
$iso   = $games[$id]['iso'];
$title = $games[$id]['title'];

echo "<p>";

/* mount the virtual iso */
verbose("Attempting to mount $title in \"$mountfuse\"");
mount($iso);
/* list all files */
$files = listFiles($iso);

verbose("Cleaning \"$patch\"");
clean($patch);
verbose("Initialising \"$patch\"");
init($patch);
verbose("Duplicating file structure in \"$patch\"");
createSymlinkStructure($files);
verbose("Collecting XML files");

$compatiblexmls = getCompatibleXML($id);

foreach ($compatiblexmls as $compatiblexml)
{
	$xml = simplexml_load_file($compatiblexml);
	foreach ($_POST as $option => $patchesraw)
	{
		if (!empty($patchesraw))
		{
			$patches = explode(";", $patchesraw);
			foreach ($patches as $patch)
			{
				$patchnode 	= $xml->xpath("./patch[@id='$patch']");
				if (count($patchnode) > 0)
				{
					foreach ($patchnode[0] as $replacementnode)
					{
						switch($replacementnode->getName())
						{
							case "file":
								filePatch($replacementnode['disc'], $replacementnode['external']);
							break;
							case "folder":
								
							break;
							case "memory":
								
							break;
						}
					}
				}
				else
				{
					echo "Error: patch with id '$patch' does not exist.";
				}
			}
		}
	}
}
?>
</div>