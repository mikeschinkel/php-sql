<?php

require __DIR__ . '/autoloader.php';

use Sql\Grammar\Sql92\Sql92;
use Sql\Grammar\Entries;

$grammar = new Sql92();
$tree = $grammar->getTree();
Entries::printAll();
echo "\nDone.";

