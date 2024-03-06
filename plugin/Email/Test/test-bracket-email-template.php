<?php

use WStrategies\BMB\Email\Template\BracketEmailTemplate;

require __DIR__ . '/../Template/BracketEmailTemplate.php';

echo BracketEmailTemplate::render('test', 'test', 'test');
