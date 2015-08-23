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

$compatiblexmls = getCompatibleXML($id);

echo "<p>";

/* mount the virtual iso */
verbose("Attempting to mount $title in \"$mountfuse\"");
mount($iso);
verbose("Cleaning \"$patch\"");
clean($patch);
verbose("Initialising \"$patch\"");
init($patch);

$base		  = directoryScanRecursive($mountfiles, true, false, "files");
$replacements = array();

foreach ($compatiblexmls as $compatiblexml)
{
	$xmlraw = str_replace('{$__maker}', substr($id, 4, 5), str_replace('{$__region}', $id[3], str_replace('{$__gameid}', substr($id, 0, 2), file_get_contents($compatiblexml))));
	$xml  = simplexml_load_string($xmlraw);
	$root = "";
	if (isset($xml["root"]))
	{
		$root = $xml["root"];
	}

	foreach ($_POST as $option => $patchesraw)
	{
		if (!empty($patchesraw))
		{
			$patches = explode(";", $patchesraw);
			foreach ($patches as $patchunique)
			{
				$patchnode 	= $xml->xpath("./patch[@id='$patchunique']");
				if (count($patchnode) > 0)
				{
					foreach ($patchnode[0] as $replacementnode)
					{
						switch($replacementnode->getName())
						{
							case "file":
								$diskfull	 = "files" . $replacementnode['disc'];
								$replacements[$diskfull]['patch'] 	 = true;
								$replacements[$diskfull]['external'] = $root . $replacementnode['external'];
							break;
							case "folder":
								$recursive = true;
								if (isset($replacementnode['recursive']))
								{
									if ($replacementnode['recursive'] == "false")
									{
										$recursive = false;
									}
								}

								$externalfull = $sd . $root . "/" . $replacementnode['external'];

								if ($recursive)
								{
									$folderfiles = directoryScanRecursive($externalfull, true, false, "files/" . $replacementnode['disc']);
								}
								else
								{
									$folderfiles = directoryScan($externalfull, false);
								}

								print_r_pre($folderfiles);
							break;
							case "memory":
								
							break;
						}
					}
				}
				else
				{
					echo "Error: patch with id '$patchunique' does not exist.";
				}
			}
		}
	}
}

//print_r_pre(directoryScan($patchfiles, false, false));
//print_r_pre($base);
//print_r_pre($replacements);

print_r_pre(array_replace($base, $replacements));

//createSymlinkStructure(array_replace($base, $replacements));
?>
</div>