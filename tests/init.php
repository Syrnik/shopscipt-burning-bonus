<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */

error_reporting(E_ALL | E_NOTICE);
require_once dirname(__FILE__, 6) . '/wa-config/SystemConfig.class.php';
waSystem::getInstance(null, new SystemConfig());

// Инициализация приложения регистрирует автозагрузку его классов и классов его плагинов
wa('shop');
