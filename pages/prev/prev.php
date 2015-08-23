<!doctype html>

<meta charset="utf-8">
<link rel="stylesheet" href="css/coverflow.css">
<!--<script src="lib/modernizr.js"></script>-->
<div id="coverflow">
</div>

  <!--<nav id="controls">
      Use <a id="prev">left</a> & <a id="next">right</a> arrows 
  </nav>-->

  <!-- Javascript  -->
  <script>
  var games = JSON.parse('<?php printLibrary(); ?>');
  var coverflow = document.getElementById('coverflow');
  var ratio =  window.innerHeight / 1000;

  coverflow.style.transform = "scale(" + ratio + ", " + ratio + ")";

  Object.keys(games).forEach(function (id)
	{
	 var opt = document.createElement("section");
		opt.setAttribute("data-cover", games[id].boxart)
    opt.setAttribute("id", id);
   		
   	coverflow.appendChild(opt);
	});
  </script>
  <script src="lib/coverflow.js"></script>  
</body>
</html>