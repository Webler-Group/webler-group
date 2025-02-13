<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" name="viewport" content="wdth=device-width,initial-scale=1.0">
    <title>OIIAI OOIIAI</title>
    <link rel="stylesheet" href="assets/css/main.css">
  </head>
  <body>
    <div id="loadingScreen" class="loading-overlay">
      <div class="loading-content">
        <img src="assets/images/oiiai.gif" alt="Loading..." class="loading-gif" />
        <div class="loading-text">Loading the Party...</div>
        <div class="loading-progress">0%</div>
      </div>
    </div>
    <button id="startButton">START VIBING</button>
    <div id="controls">
      Controls:<br />
      <span class="control-key">SPACE</span>: Toggle Auto-Rotation<br />
      <span class="control-key">1-5</span>: Visual Effects<br />
      <span class="control-key">Q/E</span>: Rotation Speed<br />
      <span class="control-key">R</span>: Reset Camera<br />
      <span class="control-key">Arrow Keys</span>: Move Camera
    </div>
    <div id="exit-wrapper">
      <a href="/Webler/index.php">Exit</a>
    </div>
    <div class="disco-overlay" id="discoContainer"></div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="assets/js/main.js"></script>
  </body>
</html>
