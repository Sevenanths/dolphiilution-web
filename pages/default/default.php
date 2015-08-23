<div class="content">
  <div class="pane options">
    <div class="about">
      <img class="logo" src="img/icon-colour.png"/>
      <h1>Dolphiilution</h1>
      <h3>by Anthe</h3>
      <h4 id="state">Hit &lt;&lt; ENTER &gt;&gt; to refresh library</h4>
      <img id="loading" src="img/loading.gif">
    </div>
  </div>
  <div class="pane coverflow">
    <div id="coverflow">

    </div>
  </div>
  
  <div class="pane patching">

  </div>
</div>

<script>
  var games = JSON.parse('<?php printLibrary(); ?>');
  initCoverflow();
</script>
<?php

?>