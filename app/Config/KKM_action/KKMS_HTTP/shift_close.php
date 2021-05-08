<?php

return array (
                // Команда серверу
                "Command"=>"CloseShift",
                // Номер устройства. Если 0 то первое не блокированное на сервере
                "NumDevice"=>0,
                // Продавец, тег ОФД 1021
                "CashierName"=>PHPKKM_OPERATOR_NAME,
                // ИНН продавца тег ОФД 1203
                "CashierVATIN"=>PHPKKM_OPERATOR_INN,
                // Не печатать чек на бумагу
                "NotPrint"=>true,
                // Id устройства. Строка. Если = "" то первое не блокированное на сервере
                "IdDevice"=>"",
                // Уникальный идентификатор команды. Любая строока из 40 символов - должна быть уникальна для каждой подаваемой команды
                // По этому идентификатору можно запросить результат выполнения команды
                "IdCommand"=>fGuid()
);

?>