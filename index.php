<?php
/* Declaration of global variables */

$pages 			= "pages/";
$apis  			= "api/";
$db 			= "db/";
$lib 			= "lib/";
$library 		= $lib . "library.json";
$boxart 		= "boxart/";
$gamespath 		= "/NAS/Games/Nintendo Wii/Retail";
$sd 			= "sd/";
$riivolution	= $sd . "/riivolution";

$maindol 		= "/main.dol";
$apploader		= "/apploader.img";

$dolphii		= $gamespath . "/dolphii/";
$mountfuse		= $dolphii . "/fuse/";
$mountdata		= $mountfuse . "/iso/part/data/";
$mountfiles		= $mountdata . "/files/";
$mountsys		= $mountdata . "/sys/";

$patch			= $dolphii . "/patch/";
$patchfiles		= $patch . "/files/";
$patchsys		= $patch . "/sys/";

/* My pagination system */
/* API is not really an API, maybe it is idk */
/* it just returns doesn't return an HTML page */

$allowedpages = array_diff(scandir($pages), array('..', '.'));
$allowedapis   = array_diff(scandir($apis), array('..', '.'));

if (isset($_GET['page']))
{
	includePage($_GET['page']);
}
elseif (isset($_GET['api']))
{
	includeAPI($_GET['api']);
}
else
{
	includePage('default');
}

function includePage($page)
{
	global $allowedpages;
	global $pages;

	if(!in_array($page, $allowedpages))
	{
		$page = 'default';
	}
	
	include("start.php");
	
	$pathprefix = "$pages/$page/";
	include("$pathprefix/$page.php");
}

function includeAPI($api)
{
	global $allowedchannels;
	global $allowedapis;
	global $apis;
	global $sd;

	global $library;
	global $patch;
	global $mountfiles;

	if(!in_array($api, $allowedapis))
	{
		$api = 'default';
	}
	
	$pathprefix = "$apis/$api/";
	include("$pathprefix/$api.php");
}

function printLibrary()
{
	global $library;
	echo str_replace("'", "\'", file_get_contents($library));
}

function updateLibraryOld()
{
	global $gamespath;
	global $library;

	$arguments = "";

	$games 		= array_diff(scandir($gamespath), array('..', '.'));
	foreach ($games as $game)
	{
		$iso = $gamespath . "/" . $game;
		if (is_file($iso))
		{
			$arguments .= " \"$iso\"";
		}
	}

	$rawid = shell_exec("wit id " . trim($arguments, " "));
	$id = explode("\n", $rawid);
	
	$gamesinforaw = shell_exec("wit anaid -H \"" . rtrim(join('" "', $id), ' "') . '"');
	
	$output = array();
	
	foreach (explode("\n", $gamesinforaw) as $gameinfo)
	{
		if (!empty($gameinfo))
		{
			$gameinfoarray 	= explode(" ", $gameinfo, 6);
			$ascii			= $gameinfoarray[3];
			$hex			= $gameinfoarray[1];
			$title			= $gameinfoarray[5];
	
			$output[$ascii]['hex'] 		= $hex;
			$output[$ascii]['ascii']	= $ascii;
			$output[$ascii]['title']	= $title;
			$output[$ascii]['boxart']	= downloadBoxart($ascii);
	}
}

file_put_contents($library, json_encode($output));

echo "updated";
}

function updateLibrary()
{
	global $gamespath;
	global $library;

	$output = array();
	$games 	= array_diff(scandir($gamespath), array("..", "."));
	foreach ($games as $game)
	{
		$iso = $gamespath . "/" . $game;
		if (is_file($iso))
		{
			$arguments 	= "id \"$iso\"";
			$id 		= trim(shell_exec("wit $arguments"));
			$info 		= shell_exec("wit anaid -H \"$id\"");

			$gameinfoarray 	= explode(" ", $info, 6);
			$ascii			= $gameinfoarray[3];
			$hex			= $gameinfoarray[1];
			$title			= trim($gameinfoarray[5]);
			$output[$ascii]['hex'] 		= $hex;
			$output[$ascii]['ascii']	= $ascii;
			$output[$ascii]['title']	= $title;
			$output[$ascii]['boxart']	= downloadBoxart($ascii);
			$output[$ascii]['iso']		= $iso;
		}
	}

	file_put_contents($library, json_encode($output));

	echo json_encode(array("state" => "success"));
}

function downloadBoxart($id)
{
	global $boxart;

	/* Guess the region from the id */

	$region = $id[3];
	$prefix = "EN";
	switch ($region)
	{
		case "P":
			$prefix = "EN";
		break;
		case "E":
			$prefix = "US";
		break;
		case "J":
			$prefix = "JA";
		break;
		case "K":
			$prefix = "KO";
		break;
	}

	/* Create a URL based off the the region and id */
	$handle = curl_init("http://art.gametdb.com/wii/coverfullHQ/$prefix/$id.png");
	curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
	/* Get the HTML or whatever is linked in $url. */
	$response = curl_exec($handle);
	/* Check for 404 (file not found). */
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	if($httpCode == 404)
	{
		/* if there is no box art on the server, return the "leave luck to heaven" box art */
	    return "$boxart/generic.png";
	}
	else
	{
		/* else save it locally */
		file_put_contents("$boxart/$id.png", $response);
		return "$boxart/$id.png";
	}
	curl_close($handle);
}

function listFiles($iso)
{
	$filesraw	= shell_exec("wit files -H --pmode \"NONE\" \"$iso\"");
	$files 		= explode("\n", $filesraw);
	return $files;
}

function folderScrape(&$replacements, $index, $directory, $resursive, $plainfile = false, $prefix = null, $external = null)
{
	if (isset($directory) && is_readable($directory))
    {
    	if ($resursive)
    	{
    		$directory = realpath($directory);
    		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    		foreach($objects as $entry => $object)
        	{
            	$entry = str_replace($directory, '', $entry);
	
        		if (basename($entry) == "." || basename($entry) == "..")
        		{
        			continue;
        		}

        		$externalfull = completeExternal($external, $entry);
        		$discfull 	  = completeDisc($index, $prefix, $entry, $externalfull);

        		//verbose($externalfull . " >> " . $discfull);

        		if (isset($discfull))
        		{
        			if ($plainfile)
        			{
        				addPlain($replacements, $discfull);
        			}
        			else
        			{
        				addReplacement($replacements, $discfull, $externalfull);
        			}
        		}
        	}
    	}
    	else
    	{
    		$it = new DirectoryIterator($directory);
			foreach($it as $entry)
			{
				if (!$it->isDot())
				{
					$externalfull 	= completeExternal($external, $entry);
					$discfull 		= completeDisc($index, $prefix, $entry, $externalfull);

					if (isset($discfull))
        			{
						if ($plainfile)
        				{
        					addPlain($replacements, $discfull);
        				}
        				else
        				{
							addReplacement($replacements, $discfull, $externalfull);
						}
					}
				}
			}
    	}
    }
}

function completeDisc($index, $prefix, $entry, $externalfull)
{
	if (isset($prefix))
  	{
  		return $prefix . '/' . $entry;
  	}
  	else
  	{
  		$filename = strtolower(basename($externalfull));
  		//verbose($filename);
  		if (isset($index[$filename]))
  		{
  			return $index[$filename];
  		}
  		else
  		{
  			return null;
  		}
  	}
}

function completeExternal($external, $entry)
{
	if (isset($external))
	{
		$external = $external . '/' . $entry;
	}

	return $external;
}

function directoryScan($dir, $pefix = "")
{
	$files = array();

	$it = new DirectoryIterator($dir);
	foreach($it as $file)
	{
		if (!$it->isDot())
		{
			$files["$prefix$file"]['patch'] = true;
			$files["$prefix$file"]['external'] = $dir . '/' . $file;
		}
	}

	return $files;
}

// http://php.net/manual/en/function.rmdir.php#98622
function clean($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") clean($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   }
}

function init($dir)
{
	global $mountsys;
	global $patchsys;
	global $maindol;
	global $apploader;

	/* create the patch directory after cleansing */
	mkdir($dir, 0777);
	/* create the patch sys directory */
	mkdir($patchsys);
	/* copy over the main dol, since we can't symlink it as it might need to be patched */
	copy($mountsys . $maindol, $patchsys . $maindol); 
	copy($mountsys . $apploader, $patchsys . $apploader);
}

function copyDolphiiDol($dolphiidol)
{
	global $patchsys;
	global $maindol;

	if (isset($dolphiidol))
	{
		copy($dolphiidol, "$patchsys/$maindol");
	}
}

function verbose($message)
{
	echo "$message<br>";
}

function print_r_pre($mixed)
{
	echo "<pre>";
	print_r($mixed);
	echo "</pre>";
}

function createSymlinkStructureOld($files)
{
	global $patch;
	global $mountdata;
	for ($i = 1; $i < count($files); $i++)
	{
		if (!empty($files[$i]))
		{
			$directory = dirname($patch . $files[$i]);
			//echo "---<br>" . $directory ."</br>";
			if (!file_exists($directory))
			{
				mkdir($directory, 0777);
			}

			if (is_file($mountdata. $files[$i]))
			{
				//echo "<br>----<br>" . $mountdata . $files[$i] . "<br>" . $patch . $files[$i];
				if (!file_exists($patch . $files[$i]))
				{
					symlink($mountdata . $files[$i], $patch . $files[$i]);
				}
			}
		}
	}
}

function createSymlinkStructure($files)
{
	global $patch;
	global $mountdata;
	global $sd;

	foreach ($files as $file => $extra)
	{

		if (isset($extra['patch']))
		{
			$target = realpath($extra['external']);
		}
		else
		{
			$target = $mountdata . $file;
		}

		if (!empty($target))
		{
			$link = $patch . $file;

			$directory =  dirname($patch . $file);
			if (!file_exists($directory))
			{
				mkdir($directory, 0777, true);
			}

			//verbose("Linked \"$patch$file\"");
			symlink($target, $link);
			//verbose($target . ", " . $link);
		}
	}
}

function filePatch($disc, $external)
{
	global $sd;
	global $patchfiles;
	if (file_exists($patchfiles . $disc))
	{
		unlink($patchfiles . $disc);
	}
	symlink($sd . $external, $patchfiles . $disc);
	verbose("Linked \"$sd$external\" to \"$patchfiles$disc\"");
}

function memoryPatch($xml, $dol, $source)
{
	shell_exec("wit dolpatch \"$dol\" xml=\"$xml\" --source \"$source\"");
}

/* grab all the compatible XML's by comparing it with the game id */
/* I have no frikkin clue how they got this to work on the actual Wii */
function getCompatibleXML($id)
{
	global $riivolution;

	$results = array();
	$files = scandir($riivolution);

    foreach($files as $key => $value)
    {
        $path = realpath($riivolution.DIRECTORY_SEPARATOR.$value);
        if (!is_dir($path))
        {
        	if (pathinfo($path, PATHINFO_EXTENSION) == "xml")
        	{
            	$xml 		= simplexml_load_file($path);
            	$idnode		= $xml->xpath("./id")[0];
            	$regionnode = $idnode->xpath("./region");
            	
            	$totalid	= $idnode['game'];
            	if (count($regionnode) > 0)
            	$totalid	.= $regionnode[0]['type'];

            	/*echo $totalid . " " . substr($id, 0, strlen($totalid)) . "</br>";*/
            	if ($totalid == substr($id, 0, strlen($totalid)))
            	{
					$results[] 	= $path;
				}
        	}
        }
    }

    return $results;
}

/* print options */
function printOptions($compatiblexmls)
{
	foreach ($compatiblexmls as $compatiblexml)
	{
		$xml 			= simplexml_load_file($compatiblexml);
		$optionsnode	= $xml->xpath("./options");

		if (count($optionsnode) > 0)
		{
			$sectionnodes = $optionsnode[0]->xpath("./section");
			foreach ($sectionnodes as $sectionnode)
			{
				echo "<div class='section'>";
				echo "<h1>" . $sectionnode['name'] . "</h1>";
				echo "<table class='patchtable'>";
				$optionnodes = $sectionnode->xpath("./option");
				foreach ($optionnodes as $optionnode)
				{
					if (isset($optionnode['default']))
					{
						(int)$default = $optionnode['default'];
					}
					else
					{
						$default = 0;
					}

					echo "<tr>";
					echo "<td class='option' id='" . $optionnode['id'] . "'>" . $optionnode['name'] . "</td>";
					if (isset($optionnode['id']))
					{
						$selectid = $optionnode['id'];
					}
					else
					{
						$selectid = str_replace(".", "", $optionnode['name']);
					}
					echo "<td class='choice'><select name='" . $selectid . "'>";
					echo "<option value=''>Disabled</option>";
					$choicenodes = $optionnode->xpath("./choice");

					$n = 1;
					foreach ($choicenodes as $choicenode)
					{
						if ($n == $default)
						{
							$extra = " selected";
						}
						else
						{
							$extra = "";
						}

						$value = "";

						$patchnodes = $choicenode->xpath("./patch");
						foreach ($patchnodes as $patchnode)
						{
							$value.= $patchnode['id'] . ";"; 
						}

						$value = trim($value, ";");

						echo "<option value=\"$value\"$extra>" . $choicenode['name'] . "</option>";

						$n++;
					}
					echo "</select></td>";
					echo "</tr>";
				}
				echo "</table>";
				echo "</div>";
			}
		}
	}
}

function getOptions($compatiblexmls)
{
	$patches = array();

	foreach ($compatiblexmls as $compatiblexml)
	{
		$xml 			= simplexml_load_file($compatiblexml);
		$optionsnode	= $xml->xpath("./options");

		if (count($optionsnode) > 0)
		{
			$sectionnodes = $optionsnode[0]->xpath("./section");
			foreach ($sectionnodes as $sectionnode)
			{
				$patches[$sectionnode['name']] = array();
				$optionnodes = $sectionnode->xpath("./option");
				foreach ($optionnodes as $optionnode)
				{
					if (isset($optionnode['default']))
					{
						(int)$default = $optionnode['default'];
					}
					else
					{
						$default = 0;
					}

					echo "<td class='option' id='" . $optionnode['id'] . "'>" . $optionnode['name'] . "</td>";
					if (isset($optionnode['id']))
					{
						$selectid = $optionnode['id'];
					}
					else
					{
						$selectid = str_replace(".", "", $optionnode['name']);
					}
					echo "<td class='choice'><select name='" . $selectid . "'>";
					echo "<option value=''>Disabled</option>";
					$choicenodes = $optionnode->xpath("./choice");

					$n = 1;
					foreach ($choicenodes as $choicenode)
					{
						if ($n == $default)
						{
							$extra = " selected";
						}
						else
						{
							$extra = "";
						}

						$value = "";

						$patchnodes = $choicenode->xpath("./patch");
						foreach ($patchnodes as $patchnode)
						{
							$value.= $patchnode['id'] . ";"; 
						}

						$value = trim($value, ";");

						echo "<option value=\"$value\"$extra>" . $choicenode['name'] . "</option>";

						$n++;
					}
					echo "</select></td>";
					echo "</tr>";
				}
				echo "</table>";
				echo "</div>";
			}
		}
	}
}

/* add a replacement to the replacements array */
/* I cheated and made this also work with the base array yo */
function addReplacement(&$replacements, $disc, $external = null)
{
	/* cleansing the disc path from multiple slashes */
	$disc = preg_replace('#/+#', '/', $disc); 

	if (isset($external))
	{
		$options['patch']	 = true;
		$options['external'] = preg_replace('#/+#', '/', $external);
		$replacements[$disc] = $options; 
	}
	else
	{
		$replacements[$disc] = "";
	}
}

/* index for no disc folder replacements */
function addPlain(&$index, $disc)
{
	$index[strtolower(basename($disc))] = $disc;
}

/* get all files in the folder, create symlinks! */
function createSymlinks()
{
	foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($data)) as $filename)
	{
	    // filter out "." and ".."
	    if ($filename->isDir()) continue;
	
	    echo "$filename\n";
}
}

function umount()
{
	global $fuse;
	shell_exec("wfuse -u \"$fuse\"");
}

function mount($iso)
{
	global $mountfuse;
	shell_exec("wfuse -r -O \"$iso\" \"$mountfuse\"");
}
?>