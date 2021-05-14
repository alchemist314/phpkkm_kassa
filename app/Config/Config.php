<?php

ini_set("display_errors", 0);
//ini_set("display_errors", 1);
//ini_set("error_reporting", E_ALL & ~E_NOTICE & ~E_DEPRECATED);
setlocale(LC_ALL, "ru_RU.utf8");

// Путь до корня программы
//define ('PHPKKM_ROOT', '/var/www/kassa');
define ('PHPKKM_ROOT', '/home/PHP/KASSA_RELEASE/08.05.2021/kassa');

// Ссылка на папку с начальным скриптом index.php (используется для vlog.html)
define ('PHPKKM_ROOT_HTTP', 'https://127.0.0.1/kassa/public');

//Какой обработчик заданий использовать:

// Локальный драйвер JSONPROXY
define('PHPKKM_ENGINE', 'ATOL_LOCAL'); 
// WEB сервер ATOL
//define('PHPKKM_ENGINE', 'ATOL_HTTP'); 
// WEB сервер KKM
//define('PHPKKM_ENGINE', 'KKMS_HTTP'); 

//-----------------------------------------------------------------------------
// Указывается в случае использования WEB сервера ATOL или WEB сервера KKM:
define('PHPKKM_URL_KKM', 'http://127.0.0.1:16732');

//-----------------------------------------------------------------------------
// Указывается в случае использования JSONPROXY драйвера:

// Путь до папки где расположен файл libfptr
define('PHPKKM_JSONDRV_LIBFPTRPATH', "/home/pi/Downloads/jsonproxy/java/*");
// Путь до папки где лежит jsonproxy драйвер
define('PHPKKM_JSONDRV_DIRPATH', "/home/pi/Downloads/jsonproxy/src");
// Команда для запуска jsonproxy драйвера
define('PHPKKM_JSONDRV_EXEC', "/usr/bin/java -classpath .:".PHPKKM_LIBFPTR_PATH." proxydrv.proxydrv");
//-----------------------------------------------------------------------------

// Ссылка для получения заданий
define('PHPKKM_URL_TASKS', 'https://127.0.0.1/kassa_tasks/public/index.php');
// Логин
define('PHPKKM_LOGIN', 'kassa'); 
// Пароль
define('PHPKKM_PASSWD', '123123');

// Вариант хранения заданий 'DIR' - хранить в директории; MYSQL, SQLITE - хранить в базе данных
define('PHPKKM_STORAGE', 'DIR'); 
//define('PHPKKM_STORAGE', 'SQLITE'); 
//define('PHPKKM_STORAGE', 'MYSQL'); 

//-----------------------------------------------------------------------------
// Указывается если для хранения заданий используется SQLITE

// Путь до базы данных SQLITE
define('PHPKKM_SQLITE_PATH', PHPKKM_ROOT.'/db/SQLITE/tasks.db'); 

//-----------------------------------------------------------------------------
// Указывается если для хранения заданий используется MYSQL

// Логин (MYSQL)
define('PHPKKM_PDO_LOGIN', 'kassa'); 
// Пароль (MYSQL) 
define('PHPKKM_PDO_PASSWD', '123123'); 
// Название БД (MYSQL) 
define('PHPKKM_PDO_DBNAME', 'kassa'); 
// Название таблицы (MYSQL) 
define('PHPKKM_PDO_TABLENAME', 'kassa'); 
// Хост (MYSQL)
//define('PDO_HOSTNAME', 'localhost:3306'); 
define('PHPKKM_PDO_HOSTNAME', '127.0.0.1:3306'); 

//-----------------------------------------------------------------------------
// Указывается если для хранения заданий используются директории

// Папка для заданий
define('PHPKKM_TASKS_DIR', PHPKKM_ROOT.'/db/Tasks'); 
// Папка для завершенных заданий
define('PHPKKM_TASKS_DIR_COMPLETE', PHPKKM_ROOT.'/db/TasksArchive');
// Папка для незавершенных заданий
define('PHPKKM_TASKS_DIR_INCOMPLETE', PHPKKM_ROOT.'/db/TasksIncomplete'); 
// Папка для ожидающих исполнения заданий
define('PHPKKM_TASKS_DIR_WAIT', PHPKKM_ROOT.'/db/TasksWait'); 
//-----------------------------------------------------------------------------


// Файл хранения состояния кассы
define('PHPKKM_KKM_STATUS', PHPKKM_ROOT.'/tmp/kassa.status'); 

// Файл хранения времени кассы
define('PHPKKM_KKM_TIME', PHPKKM_ROOT.'/tmp/kassa.time'); 


// Логгирование
define('PHPKKM_LOGGER', true); 
// Выводить лог на экран
define('PHPKKM_SHOWLOG', true); 
// Файл логов
define('PHPKKM_LOG_PATH', PHPKKM_ROOT.'/tmp/log.txt'); 

// Файл блокирования
//define('PHPKKM_LOCK', PHPKKM_ROOT.'/tmp/lock.txt'); 

// Интервал (секунды) между запросами к ККМ серверу 
define('PHPKKM_TASK_DELAY', 3); 
// Таймаут ответа сервера
define('PHPKKM_SERVER_TIMEOUT', 120); 

// Период (дней) за который будет формироваться список выполненных заданий и отправляться на платежный сервер
define('PHPKKM_DAY_DELTA', 1); 

// Фамилия кассира для отображения в чеке
define('PHPKKM_OPERATOR_NAME', 'Иванов И.И.'); 
// ИНН кассира для отображения в чеке
define('PHPKKM_OPERATOR_INN', '000000000001'); 

define('PHPKKM_LOG_CRIT', 'CRIT');
define('PHPKKM_LOG_WARN', 'WARN');
define('PHPKKM_LOG_SUCC', 'SUCC');


?>