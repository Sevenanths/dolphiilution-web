Dolphiilution
====================
Dolphiilution is a PHP/JS project which allows you to play altered games on [Dolphin Emulator](https://github.com/dolphin-emu/dolphin).
![Interface](http://g2f.nl/0vrcp8p.png)

Notice
--------------------
In some earlier versions of Dolphin from around June 2015, running games from a folder was broken. Use the [latest build](https://dolphin-emu.org/download/) to prevent any issues.

How it works
--------------------
Dolphiilution mounts a game with [wfuse](http://wit.wiimm.de/wfuse/). It then replicates the file structure of the disk with symlinks.
Unaltered files will point to the files on the mount, altered files will point to those found on your (virtual) SD card.
DOL patches are executed using [WIT](http://wit.wiimm.de/wit/).

Compatibility
--------------------
**DOLPHIILUTION DOES NOT AND MIGHT NEVER WORK WITH POPULAR HACKS LIKE CTGP REVOLUTION OR NEWER SUPER MARIO BROS WII**

Most hacks have reached the sheer complexity of ASM hacking: this currently is not well supported by Dolphiilution, though
regular file/folder patches will work fine. You can provide an alternate patch type in your XML, called "dolphiidol".
Dolphiilution will pick up the dol provided and use it as the "main.dol" file for the game.

**EXAMPLE:**
```
<dolphiidol external="/mkwii/Level1/PAL/main.dol"/>
```

Setup
--------------------
Dolphiilution will not work on Windows since it does not support the FUSE file system. If you'd like to make use of Dolphiilution
on Windows, you can run it on a Linux/Mac machine and share the files in your local network.

1. Download the master branch as a zip
2. Extract the ZIP to a local (!) webserver, PHP5 needs to be installed and running.
3. Make sure [WIT](http://wit.wiimm.de/download.html) is installed.
4. Open "index.php" on the root of the Dolphiilution folder. You are required to change $gamespath to the path leading to your 
games. Save your changes.
5. Copy the folder "dolphii" on the root of the Dolphiilution folder to the folder where your games are located. Dolphiilution
will use this file structure to mount games in and create patches.
6. The "sd/" folder on the root of the Dolphiilution folder acts as a virtual SD card. Dolphiilution uses the same file structure
as Riivolution. Place your XML's and patches as you would for a regular Wii.  
  **STRUCTURE EXAMPLE:**
  * mkwii
  * nsmb
  * riivolution
    * nsmb.xml
    * mkwii.xml
    * demo.xml
  * smg2.5
7. Navigate to 'yourwebserveradress/pathtodolphiilution/?api=update' using your web browser. The adress varies per machine,
per network and your setup. '192.168.0.184' and '127.0.0.1' are examples of adresses. Running this script will scan your
library and find available boxart.
8. You can now browse to 'yourwebserveradress/pathtodolphiilution/'. You should get a coverflow-eqsue display of your games.
To browse through your games, use the LEFT and RIGHT arrow keys. To select a game, hit up. If any patches are available, they
will be displayed. Select your desired options and hit ENTER to patch your game.
9. When "Patch succesful!" appears, your game is almost ready to be played. Open Dolphin and click on "Config". Select the tab
"Paths". Set these paths like so:
  * Default ISO: pathtoyourgames/dolphii/patch/sys/main.dol
  * DVD Root: pathtoyourgames/dolphii/patch/files
  * Apploader: pathtoyourgames/dolphii/patch/sys/apploader.img
9. Hit "Play".
10. ???
11. Enjoy!

Note: it can take a while for Dolphin to load a game from a folder. If you get a black screen, but the game is still running,
just be patient.
