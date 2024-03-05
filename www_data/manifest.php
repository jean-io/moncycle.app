<?php

header('Content-Type: text/cache-manifest');

?>CACHE MANIFEST

# files that should be offline
CACHE
.
/
index.php
js/tableau.js?v=<?= filemtime('js/tableau.js') ?> 
vendor/chartjs/chart.js?v=<?= filemtime('vendor/chartjs/chart.js') ?> 
vendor/components/jquery/jquery.min.js?v=<?= filemtime('vendor/components/jquery/jquery.min.js') ?> 
css/commun.css?v=<?= filemtime('css/commun.css') ?> 
css/tableau.css?v=<?= filemtime('css/tableau.css') ?> 
img/moncycleapp512.jpg
favicon.ico
font/montserrat/Montserrat-VariableFont_wght.ttf

# files that should not be offline
NETWORK
api/*
