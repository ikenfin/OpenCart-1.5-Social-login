# Авторизация через соцсети для Opencart 1.5

Модуль представляет из себя доработанную версию [Opencart social authorization by Nikita_SP](https://nikita-sp.com.ua/2015/01/modul-avtorizatsiya-sots-seti-opencart.html)

## Отличия от оригинала

1. Исправлена работа с Facebook
2. Добавлена авторизация через Одноклассники
3. Добавлена авторизация через Twitter
4. Произведён рефакторинг и упрощение кода
5. **Эта версия требует установленного php-curl**

## TODO

1. Добавить авторизацию через Instagram
2. Добавить возможность добавления ссылок на странице через модуль
3. Провести рефакторинг кода и добавить корректную обработку ошибок API

## Зачем CURL?

Это требование связано с API Одноклассников, которое требует отправлять им POST запросы.
В будущем можно сделать это требование опциональным.