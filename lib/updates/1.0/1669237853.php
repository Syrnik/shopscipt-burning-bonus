<?php
$model = new waModel();
$db_schema_file = wa('shop')->getConfig()->getPluginPath('burningbonus') . '/lib/config/db.php';
if(!file_exists($db_schema_file) || !is_file($db_schema_file) || !is_readable($db_schema_file))
    throw new waException('Shop/Burningbonus: Database schema isn\'t readable');

$schema = include($db_schema_file);

$model->createSchema($schema);
