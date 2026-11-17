<?php
return array(
    'shop_burningbonus_notifications' => array(
        'id' => array('int', 10, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0),
        'transport' => array('enum', "'email','sms'", 'null' => 0, 'default' => 'email'),
        'status' => array('tinyint', 4, 'null' => 0, 'default' => '1'),
        'from' => array('varchar', 200),
        'schedule_type' => array('enum', "'monthly','weekly'", 'null' => 0, 'default' => 'monthly'),
        'schedule_day' => array('int', 11, 'null' => 0, 'default' => '1'),
        'scheduled_time' => array('char', 5),
        'sent' => array('datetime'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_burningbonus_notification_texts' => array(
        'notification_id' => array('int', 10, 'unsigned' => 1, 'null' => 0),
        'locale' => array('varchar', 16, 'null' => 0, 'default' => ''),
        'subject' => array('mediumtext'),
        'body' => array('mediumtext', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('notification_id', 'locale'),
        ),
    ),
);
