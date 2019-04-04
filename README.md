# Framework life balance 

![ядро: v1.1.8.21](https://img.shields.io/badge/Ядро-v1.1.8.21-blue.svg) ![интерфейс: v1.0.7.24](https://img.shields.io/badge/Интерфейс-v1.0.7.24-blue.svg)

Framework life balance предназначен для **сопровождения** всех этапов разработки отеческого сайта с любым масштабом на исходном php-коде, html-разметках ( css, image ) и js-скриптах, с применением чёткой последовательностью разработки (смотрите <a target="_blank" href="/Компоненты ядра/2.Условия/Структуры/Напутствующие/Напутствующая структура разработки.md">структуру разработки ядра</a> и <a target="_blank" href="/Компоненты интерфейса/2.Условия/Структуры/Напутствующие/Напутствующая структура разработки.md">структуру разработки интерфейса</a>) 10 компонентов сайта:

| № | Компоненты ядра | Компоненты интерфейса
 ------------- |  ------------- | ------------- | 
| 1. | Ориентировка: цели, сведения, стандарты | Ориентировка: цели, сведения, стандарты
| 2. | Условия: структуры | Условия: структуры
| 3. | Распределение: нормативы | Распределение: нормативы
| 4. | Движение: протоколы, функции | Движение: протоколы, функции
| 5. | Модули | Модули

<a target="_blank" href="https://framework-life-balance.ru/#about">Подробнее о проекте</a> (в том числе про этапы развёртки).

![hr](https://avatars.mds.yandex.net/get-pdb/1239772/b26b34b9-e654-47a9-b09c-b38c9899b7e1/s1200?webp=false)


### Среда

У Framework life balance две среды разработки: back-end (ядро) и front-end (интерфейс). 

![Framework life balance](https://framework-life-balance.ru/Компоненты%20интерфейса/2.Условия/Структуры/Картиночные/slider/slide1_bg.jpg)

Здесь нет места для php-кода в интерфейсе, и html-а в ядре. Ядро и интерфейс разделены и изолированы, что позволяет безпрепятственно разрабатывать оба направления одновременно, почтительно дополняя (без возникновения каких либо merge) на git'e.

![hr](http://arabesko.ru/images/files/vector/arabesq/arabesko.ru_06.png)


### Ядро

В ядре реализованы Стандарты (детализации) ядра, наглядность Структуры (планировка), Функции 4-х компонентов (распределенная альтернатива контролёров в mvc), Функции категорий сайта (упрощённая альтернатива моделей в mvc) и Нормативы (упрощённая альтернатива yii2 migrate, установки и настроек). Благодаря такому подходу был реализован норматив таблиц базы данных и функция автоматической реконструкции базы данных, что освободило разработчиков от необходимости конструировать sql-запросы вручную. А так же реализован внутренний самовызов из консоли, оттого на фоновый режим отработки была переведена отправка почтового сообщения и реструктуризация базы данных, что для пользователя значительно уменьшило время ожидания ответа, а у разработчиков отпала необходимость настраивать cron.

Стандарты: 

> Под стандартом понимается образец, эталон, модель.

- <a target="_blank" href="/Компоненты ядра/1.Ориентировка/Стандарты/Основополагающие/1.Основополагающий стандарт среды.md">Стандарт среды</a>
- <a target="_blank" href="/Компоненты ядра/1.Ориентировка/Стандарты/Основополагающие/2.Основополагающий стандарт кода.md">Стандарт кода</a>
- <a target="_blank" href="/Компоненты ядра/1.Ориентировка/Стандарты/Основополагающие/3.Основополагающий стандарт компонентов.md">Стандарт компонентов</a>
- <a target="_blank" href="/Компоненты ядра/1.Ориентировка/Стандарты/Основополагающие/4.Основополагающий стандарт разработки.md">Стандарт разработки</a>
- <a target="_blank" href="/Компоненты ядра/1.Ориентировка/Стандарты/Основополагающие/5.Основополагающий стандарт разработчиков.md">Стандарт разработчиков</a>
- <a target="_blank" href="/Компоненты ядра/1.Ориентировка/Стандарты/Нормативные/Стандарт норматива базы данных.md">Стандарт норматива базы данных</a>
- <a target="_blank" href="/Компоненты ядра/1.Ориентировка/Стандарты/Нормативные/Стандарт норматива взаимодействия с базой данных.md">Стандарт норматива взаимодействия с базой данных</a>
- <a target="_blank" href="/Компоненты ядра/1.Ориентировка/Стандарты/Нормативные/Стандарт норматива наработок.md">Стандарт норматива наработок</a>


Структуры:

> Под структурами понимается внутреннии устройства с их взаимосвязями.

- <a target="_blank" href="/Компоненты ядра/2.Условия/Структуры/Напутствующие/Напутствующая структура разработки.md">Структура разработки</a>
- <a target="_blank" href="/Компоненты ядра/2.Условия/Структуры/Базы данных/Структура таблиц базы данных.md">Структура таблиц базы данных</a>
- <a target="_blank" href="/Компоненты ядра/2.Условия/Структуры/Базы данных/Структура взаимодействия с базой данных.md">Структура взаимодействия с базой данных</a>
- <a target="_blank" href="/Компоненты ядра/2.Условия/Структуры/Функций/Структура функций компонентов.md">Структура функций компонентов</a>
- <a target="_blank" href="/Компоненты ядра/2.Условия/Структуры/Функций/Структура функций сайта.md">Структура функций сайта</a>


Нормативы:

> Под нормативом понимается объем деятельности, которому всегда принято следовать.

- <a target="_blank" href="/Компоненты ядра/3.Распределение/Нормативы/Компонентов/1.Норматив компонента орентировка.md">Норматив компонента орентировка</a>
- <a target="_blank" href="/Компоненты ядра/3.Распределение/Нормативы/Компонентов/2.Норматив компонента условия.md">Норматив компонента условия</a>
- <a target="_blank" href="/Компоненты ядра/3.Распределение/Нормативы/Компонентов/3.Норматив компонента распределение.md">Норматив компонента распределение</a>
- <a target="_blank" href="/Компоненты ядра/3.Распределение/Нормативы/Компонентов/4.Норматив компонента движение.md">Норматив компонента движение</a>
- <a target="_blank" href="/Компоненты ядра/3.Распределение/Нормативы/Компонентов/5.Норматив компонента модули.md">Норматив компонента модули</a>
- <a target="_blank" href="/Компоненты ядра/3.Распределение/Нормативы/Функций/Норматив функций компонентов.md">Норматив функций компонентов</a>


![hr](http://www.coollady.ru/pic/0003/068/41.jpg)

### Интерфейс

В интерфейсе реализована структура landing-page + ajax подгрузка данных с ядра по api, что позволяет пользователю взаимодействовать с сайтом без прерываний, а разработчику интерфейса иметь исходники без каких либо php-вставок.

Стандарты: 

> Под стандартом понимается образец, эталон, модель.

- <a target="_blank" href="/Компоненты интерфейса/1.Ориентировка/Стандарты/Основополагающие/1.Основополагающий стандарт среды.md">Стандарт среды</a>
- <a target="_blank" href="/Компоненты интерфейса/1.Ориентировка/Стандарты/Основополагающие/2.Основополагающий стандарт кода.md">Стандарт кода</a>
- <a target="_blank" href="/Компоненты интерфейса/1.Ориентировка/Стандарты/Основополагающие/3.Основополагающий стандарт компонентов.md">Стандарт компонентов</a>
- <a target="_blank" href="/Компоненты интерфейса/1.Ориентировка/Стандарты/Основополагающие/4.Основополагающий стандарт разработки.md">Стандарт разработки</a>
- <a target="_blank" href="/Компоненты интерфейса/1.Ориентировка/Стандарты/Основополагающие/5.Основополагающий стандарт разработчиков.md">Стандарт разработчиков</a>


Структуры:

> Под структурами понимается внутреннии устройства с их взаимосвязями.

- <a target="_blank" href="/Компоненты интерфейса/2.Условия/Структуры/Напутствующие/Напутствующая структура разработки.md">Структура разработки</a>


Нормативы:

> Под нормативом понимается объем деятельности, которому всегда принято следовать.

- <a target="_blank" href="/Компоненты интерфейса/3.Распределение/Нормативы/Компонентов/1.Норматив компонента орентировка.md">Норматив компонента орентировка</a>
- <a target="_blank" href="/Компоненты интерфейса/3.Распределение/Нормативы/Компонентов/2.Норматив компонента условия.md">Норматив компонента условия</a>
- <a target="_blank" href="/Компоненты интерфейса/3.Распределение/Нормативы/Компонентов/3.Норматив компонента распределение.md">Норматив компонента распределение</a>
- <a target="_blank" href="/Компоненты интерфейса/3.Распределение/Нормативы/Компонентов/4.Норматив компонента движение.md">Норматив компонента движение</a>
- <a target="_blank" href="/Компоненты интерфейса/3.Распределение/Нормативы/Компонентов/5.Норматив компонента модули.md">Норматив компонента модули</a>



![hr](http://steklu.net/scinaly/gjel/khokhloma-018.jpg)

### Разработка

Порядок разработки выстроен так, что равномерно переводит команду разработчиков на форму управления "холакратия", которая эффективна в непрерывной и распределительной разработке web-сайтов. При этом изучать холакратию не нужно, достаточно каждому участнику разработки соотвествовать нормативам.

> Холакратия — это способ децентрализации власти, который позволяет выстроить иерархию (холархию) таким образом, чтобы каждый сотрудник мог влиять на жизнь компании и обладал полной властью в рамках своей роли и возложенных на неё обязательств.


![Framework life balance](https://framework-life-balance.ru/Компоненты%20интерфейса/2.Условия/Структуры/Картиночные/illustrators/4values.jpg)

### Примечание

Исходный код сайта https://framework-life-balance.ru подгружается с https://github.com/veter-love/framework-life-balance-v1 репозитория.