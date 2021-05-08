# phpkkm_kassa

Программа предназначена для организации работы с онлайн-кассами.
<br>Скрипт забирает задания от <a href="https://github.com/alchemist314/phpkkm_kassa_tasks" target="blank">платежного сервера</a> и отправляет на ККМ.

Поддерживается работа напрямую с кассой, <a href="https://app.swaggerhub.com/apis-docs/atol-dev/fptr-web-server/" target="blank">Web сервером АТОЛ, <a href="https://kkmserver.ru/KkmServer" target="blank">KKM сервером</a>.
<br>Для работы напрямую с кассой необходим установленный <a href="http://fs.atol.ru/SitePages/%D0%A6%D0%B5%D0%BD%D1%82%D1%80%20%D0%B7%D0%B0%D0%B3%D1%80%D1%83%D0%B7%D0%BA%D0%B8.aspx?raz1=%D0%9F%D1%80%D0%BE%D0%B3%D1%80%D0%B0%D0%BC%D0%BC%D0%BD%D0%BE%D0%B5+%D0%BE%D0%B1%D0%B5%D1%81%D0%BF%D0%B5%D1%87%D0%B5%D0%BD%D0%B8%D0%B5&raz2=%D0%94%D0%A2%D0%9E" target="blank">драйвер АТОЛ</a>.

Перед запуском отредактируйте файл конфигурации Config.php:
<br>https://github.com/alchemist314/phpkkm_kassa/blob/master/app/Config/Config.php

Если для хранения заданий будет использоваться MySQL или SQLite запустите соответствующий скрипт из папки /<a href="https://github.com/alchemist314/phpkkm_kassa/tree/master/install" target="blank">install</a>

Файл запуска программы находится в папке /public/index.php, лучше всего его запускать по крону каждые 5 минут, в этом случае скрипт будет отслеживать состояние смены и закрывать ее автоматически:
<br>https://github.com/alchemist314/phpkkm_kassa/blob/master/public/index.php

