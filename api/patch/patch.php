<?php
$id = $_POST['id'];
unset($_POST['id']);

$json  = file_get_contents($library);
$games = json_decode($json, true);
$iso   = $games[$id]['iso'];
$title = $games[$id]['title'];

$compatiblexmls = getCompatibleXML($id);

/* mount the virtual iso */
mount($iso);
clean($patch);
init($patch);

$index 			= array();
$base 			= array();
$replacements	= array();
$dolphiidol 	= null;

folderScrape($base, $index, $mountfiles, true, false, $prefix = "files/");
folderScrape($index, $index, $mountfiles, true, true, $prefix = "files/");

foreach ($compatiblexmls as $compatiblexml)
{
	$xmlraw = str_replace('{$__maker}', substr($id, 4, 5), str_replace('{$__region}', $id[3], str_replace('{$__gameid}', substr($id, 0, 2), file_get_contents($compatiblexml))));
	$xml  = simplexml_load_string($xmlraw);
	$root = "";
	$memorypatch = false;
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
								addReplacement($replacements, "files/" . $replacementnode['disc'], $sd . $root . $replacementnode['external']);
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

								if (isset($replacementnode['disc']))
								{
									$prefix = "files/"  . $replacementnode['disc'];
									
								}
								else
								{
									$prefix = null;
								}
								
								folderScrape($replacements, $index, $externalfull, $recursive, false, $prefix, $externalfull); 	
							break;
							case "memory":
								$memorypatch = true;
							break;
							case "dolphiidol":
								$dolphiidol  = $sd . $root . "/" . $replacementnode['external'];
							break;
						}
					}
				}
				else
				{
					
				}
			}
		}
	}

	if ($memorypatch)
	{
		memoryPatch(realpath($compatiblexml), $patchsys . $maindol, realpath($sd . $root));
	}
}

copyDolphiiDol($dolphiidol);

//print_r_pre(directoryScan($patchfiles, false, false));
//print_r_pre($base);
//print_r_pre($replacements);

//print_r_pre($index);

createSymlinkStructure(array_replace($base, $replacements));

echo json_encode(array("state" => "success"));
?>