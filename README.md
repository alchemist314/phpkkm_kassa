# phpkkm_kassa

Программа предназначена для организации работы с онлайн-кассами.
<br>Скрипт забирает задания от <a href="https://github.com/alchemist314/phpkkm_kassa_tasks" target="blank">платежного сервера</a> и отправляет на ККМ.

Поддерживается работа напрямую с кассой, Web сервером АТОЛ, KKM сервером.
<br>Для работы напрямую с кассой необходим установленный драйвер АТОЛ.

Перед запуском отредактируйте файл конфигурации Config.php:
<br>https://github.com/alchemist314/phpkkm_kassa/blob/master/app/Config/Config.php

Если для хранения заданий будет использоваться MySQL или SQLite запустите соответствующий скрипт из папки /<a href="https://github.com/alchemist314/phpkkm_kassa/tree/master/install" target="blank">install</a>

Файл запуска находится в папке /public/index.php, лучше всего его запускать по крону каждые 5 минут, в этом случае скрипт будет отслеживать состояние смены и закрывать ее автоматически:
<br>https://github.com/alchemist314/phpkkm_kassa/blob/master/public/index.php

