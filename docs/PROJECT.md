# metplus-vrn.ru — документация проекта

## О проекте

Сайт на **1С-Битрикс** — каталог металлопроката ООО «КОРПОРАЦИЯ МЕТАЛЛИНВЕСТ» (Воронеж).  
Разработка шаблона и кастомных компонентов — **Prime Ltd**.

| Параметр | Значение |
|----------|----------|
| CMS | 1С-Битрикс |
| Шаблон | `bitrix/templates/metplus/` |
| Кастомный код | `bitrix/php_interface/`, `include/`, `ajax/`, `catalog/` |
| Медиа товаров | `upload/` (iblock, medialibrary, resize_cache) |
| Локальный URL | **http://localhost:8086/** |
| Локальная БД | `test_metplus` @ `127.0.0.1` |
| Git | [github.com/bziksv/metplus](https://github.com/bziksv/metplus) |
| Hostname сервера | `server2.metplus-vrn.ru` |

### Бизнес-особенности

- Каталог металлопроката с ценой **за метр** и **за штуку**
- Обмен с **1С** (модуль `askaron.pro1c`)
- Динамическая надбавка **+20%**, если количество не кратно половине длины (`DLINA_RASCHET / 2`)
- Услуги **резки** (газовая / абразивная) — доплата в корзине
- Оформление заказа через **страницу `/cart/`** + overlay (быстрый просмотр / checkout), без полноценного `/personal/`

---

## Сервер и окружения

### Правило работы

> **Сейчас работаем только локально** (`http://localhost:8086/`).  
> Если сказано «выкатить» / «задеплоить» — это означает **test**, не prod.  
> **Prod** (`metplus-vrn.ru`) — только по явной отдельной просьбе.

| Окружение | Домен | Роль сейчас |
|-----------|-------|-------------|
| **Local** | localhost:8086 | Основная разработка |
| **Test** | test.metplus-vrn.ru | Выкат для проверки |
| **Prod** | metplus-vrn.ru | Не трогаем без явного указания |

| Окружение | Домен | Путь на сервере | IP |
|-----------|-------|-----------------|-----|
| **Prod** | metplus-vrn.ru | `/var/www/metplus-vrn.ru/data/www/metplus-vrn.ru` | 37.230.112.153 |
| **Test** (выкат) | test.metplus-vrn.ru | `/var/www/test_metplus_usr/data/www/test.metplus-vrn.ru` | 37.230.112.153 |

**Workflow:** локально → test (по команде «выкатить») → prod (только если явно попросили).

### SSH-доступ

Ключ с Mac (`~/.ssh/id_ed25519`) добавлен на сервер:

```bash
ssh root@37.230.112.153          # hostname: server2.metplus-vrn.ru
```

Рекомендуемая запись в `~/.ssh/config`:

```
Host metplus
    HostName 37.230.112.153
    User root
    IdentityFile ~/.ssh/id_ed25519
    IdentitiesOnly yes
```

### Деплой на test

«Выкатить» = залить на **test.metplus-vrn.ru**:

```bash
# Пример: шаблон
scp bitrix/templates/metplus/header.php \
    root@37.230.112.153:/var/www/test_metplus_usr/data/www/test.metplus-vrn.ru/bitrix/templates/metplus/

# Очистка кеша Битрикс
ssh root@37.230.112.153 \
    "rm -rf /var/www/test_metplus_usr/data/www/test.metplus-vrn.ru/bitrix/cache/*"
```

---

## Архитектура

```
Пользователь
    ↓
bitrix/templates/metplus/   (header, footer, CSS, JS)
    ↓
catalog/index.php           (bitrix:catalog, iblock 36)
ajax/index.php              (корзина, заказ, резка)
    ↓
bitrix/php_interface/init.php
    ├── PriceUpdater        (после импорта 1С)
    ├── custom_price.php    (выбор типа цены +20%)
    └── basket_update.php   (виртуальная строка резки)
```

### Цепочка ценообразования

1. **1С-импорт** → базовая цена (тип 16) + свойства `KOEFFITSENT_RASCHET`, `DLINA_RASCHET`
2. **`PriceUpdater`** (`OnSuccessCatalogImport1C`) — пересчёт цен за метр (тип **17**) и +20% (тип **18**)
3. **`customBasketPriceTypeHandler`** (`OnGetOptimalPrice`) — при добавлении в корзину:
   - если `quantity % (DLINA_RASCHET / 2) != 0` → цена **+20%**
   - иначе — стандартная цена за метр
4. **Резка** — свойства `REZKA_GAZ_RASCHET`, `REZKA_ABRAZIV_RASCHET` → виртуальная строка корзины (`PRODUCT_ID=0`)

Ключевые файлы:

| Файл | Назначение |
|------|------------|
| `bitrix/php_interface/init.php` | Подключение хуков и вспомогательных функций |
| `bitrix/php_interface/include/price_updater.php` | Пересчёт цен после 1С |
| `bitrix/php_interface/include/custom_price.php` | Логика +20% |
| `bitrix/php_interface/include/basket_update.php` | Доплата за резку |
| `bitrix/php_interface/include/functions.php` | `getLengthProduct()`, `isCustomPrice()`, `getProductCuttingServices()` |

### Типы цен

| ID | Код | Назначение |
|----|-----|------------|
| 16 | — | Базовая (из 1С) |
| 17 | `PER_METER` | Цена за метр |
| 18 | `PER_METER_PLUS20` | Цена за метр +20% |

---

## Каталог

| Параметр | Значение |
|----------|----------|
| Основной iblock | **36** — товары, 1С, цены |
| Второй iblock | **13** — сетка категорий на главной, поиск в шапке |
| SEF | `/catalog/#SECTION_CODE#/#ELEMENT_CODE#/` |
| Компонент | `bitrix:catalog` → шаблон `catalog` |
| Фильтр | Smart Filter |
| Точка входа | `catalog/index.php` |

Левое меню каталога строится динамически через `.left.menu_ext.php` + `prime:menu.sections`.

> **Важно:** iblock 13 и 36 — разные сущности. Главная и поиск используют 13, коммерция — 36.

---

## Корзина и AJAX

**Основная корзина:** страница [`/cart/`](../cart/index.php) (полный UX резки).  
**Быстрый просмотр:** выдвижная панель (overlay) по клику на иконку в шапке — кнопка «Перейти в корзину».  
**Переключатель** (слева внизу, как у каталога): «Корзина: Оригинал» / «Новая» — `localStorage` + `data-cart-view`.  
Оформление заказа пока остаётся в overlay (`?component=order`).

Общий include: `include/sale_basket.php` (`CART_DISPLAY_MODE` = `page` | `overlay`).

**Роутер:** `ajax/index.php` → `?component=<name>`

| Endpoint | Файл | Назначение |
|----------|------|------------|
| `?component=add_cart` | `ajax/add_cart.php` | Добавить товар (метры + резка) |
| `?component=cart_small` | `ajax/cart_small.php` | Мини-корзина в шапке |
| `?component=cart` | `ajax/cart.php` | Overlay-корзина |
| `/cart/` | `cart/index.php` | Страница корзины |
| `?component=order` | `ajax/order.php` | Форма заказа (`prime:order`) |
| `/ajax/cutting_services_options.php` | — | Выбор типа резки (Fancybox) |
| `/ajax/update_cutting_type_in_cart.php` | — | Обновить резку в корзине |
| `/ajax/update_cutting_plan_in_cart.php` | — | План резки (целые / неполная) |

Фронтенд: `bitrix/templates/metplus/js/main.js`.  
Резка неполной: строка плана `неполная | CODE | 1+2+5 |`; при плане авто-1 рез за неполную отключается.

---

## Инфоблоки (из кода)

| ID | Назначение |
|----|------------|
| 1 | Новости |
| 4 | Услуги |
| 5, 6, 7 | Слайдер, преимущества |
| 13 | Категории на главной, поиск |
| 18 | Контакты |
| 19, 20 | Отзывы |
| 22, 23 | Доставка |
| 25, 34 | ГОСТ |
| 29 | Статьи |
| 30, 31 | Формы обратной связи |
| 32 | Вакансии |
| **36** | **Основной каталог товаров** |

---

## Шаблон `metplus`

```
bitrix/templates/metplus/
├── header.php, footer.php     — шапка и подвал
├── css/main.css, custom.css   — стили (+ система product-hint, см. docs/catalog-visual-language.md)
├── js/main.js                 — корзина, слайдеры, каталог
├── libs/                      — jQuery, Bootstrap, Slick, Fancybox…
├── components/
│   ├── bitrix/                — переопределения каталога, корзины, меню…
│   └── prime/                 — main.feedback, order, menu.sections…
└── img/static/                — logo.webp, logo.png, logo.svg
```

### Интеграции (footer.php)

| Сервис | Назначение |
|--------|------------|
| Яндекс.Метрика | ID 29872139 |
| Roistat | CRM, cookie `roistat_visit` в письмах заказов |
| Bitrix24 | CRM-кнопка на сайте |
| Prime incut | Виджет `incut.prime-ltd.su` |
| reCAPTCHA | Формы обратной связи |

### Кастомные модули

| Модуль | Назначение |
|--------|------------|
| `askaron.pro1c` | Оптимизированный обмен с 1С |
| `prime.roistatbitrixcms` | Roistat |
| `prime.smartbanners` | Умные баннеры |
| `prime.cleaner` | Утилита очистки |
| `kda.exportexcel` | Экспорт в Excel |
| `sng.secure` | Безопасность |

---

## Структура проекта

```
bitrix/templates/metplus/   — шаблон сайта, компоненты (~748 файлов)
bitrix/php_interface/       — init.php, dbconn, хуки, 1С-импорт
include/                    — подключаемые блоки (телефон, копирайт…)
catalog/                    — точка входа каталога
ajax/                       — AJAX-обработчики корзины и заказа
about/, contact/, delivery/  — контентные разделы
services/, reviews/, gost/
news/, articles/, vacancy/
prays/                      — прайс-листы XLS/PDF
scripts/                    — локальный dev
.local/                     — nginx/php-fpm конфиги (gitignored)
docs/                       — документация
html/                       — старые HTML-прототипы
dev/                        — отладочная страница (не для prod)
```

Корневые dot-файлы (меню, доступ, ЧПУ):

| Файл | Назначение |
|------|------------|
| `.top.menu.php` | Верхнее меню |
| `.left.menu_ext.php` | Выпадающий каталог из iblock |
| `.bottom.menu.php` | Нижнее меню футера |
| `.htaccess` | Apache: редиректы, ЧПУ |
| `.access.php` | Права доступа Bitrix |

---

## Локальная разработка

Без Docker. Homebrew **nginx** + **PHP 8.3 FPM**.

```bash
./scripts/setup-local-db.sh    # импорт test_metplus.sql.gz (один раз)
./scripts/setup-local-db.sh --force  # пересоздать БД из дампа
./scripts/start-dev.sh         # nginx :8086 + php-fpm :9086
./scripts/stop-dev.sh          # остановка
```

| Параметр | Значение |
|----------|----------|
| URL | http://localhost:8086/ |
| Nginx | `.local/nginx/nginx.conf` → `.local/run/nginx.conf` |
| PHP-FPM | `.local/php/fpm.conf`, `pools.conf` |
| Учётные данные БД | `.local/db.env` (gitignored) |
| Локальный dbconn | `bitrix/php_interface/dbconn.local.php` (gitignored) |

При первом запуске `start-dev.sh` вызывает `apply-local-db-config.sh`, если `dbconn.local.php` отсутствует.

### Порты соседних проектов

| Проект | Порт |
|--------|------|
| almamed | 8080 |
| vilmed | 8082 |
| kosmamed | 8083 |
| polimer | 8084 |
| **metplus-vrn.ru** | **8086** |
| p.datagon.ru | 3000 |
| titlo.ru (datagon legacy) | 3001 |
| cabinet.titlo.ru | 3002 |
| titlo.ru (маркетинг) | 3003 |

---

## Git

**Репозиторий:** ~136k файлов (полное ядро Битрикс + сайт).  
**Кастомный код:** ~800 файлов в `templates/metplus`, `php_interface`, `ajax`, `include`, `catalog`.

### Что НЕ коммитить

Секреты исключены в `.gitignore`:

- `bitrix/.settings.php` — пароль БД, crypto_key
- `bitrix/php_interface/dbconn.php`, `dbconn.local.php` — пароль БД
- `bitrix/license_key.php`
- `.local/` — локальный dev и бэкапы конфигов
- `upload/`, кеш, дампы (`*.sql`, `*.tar.gz`)

Примеры без секретов: `bitrix/.settings.example.php`, `bitrix/php_interface/dbconn.example.php`.

### Исключения из индексации Cursor

Настроены в `.cursorignore`:

- кеш Битрикс (`bitrix/cache`, `managed_cache`, `stack_cache`)
- медиа товаров (`upload/`)
- дампы БД и архивы (`*.sql`, `*.tar.gz`)
- бинарные файлы (изображения, шрифты, PDF)

После изменения `.cursorignore`: **Cursor Settings → Indexing → Reindex**.

---

## Синхронизация с архивом сервера

В июле 2026 архив `test.metplus-vrn.ru.tar.gz` был распакован и сравнен с корнем проекта.  
Подробный отчёт: [`docs/archive-sync-report.md`](archive-sync-report.md).

**Итог:** архив ≈ снимок prod/test. Из него в корень добавлены dot-файлы меню, `.htaccess`, `.access.php`, PDF-прайсы.

Папку `test/` (распаковка архива) **не коммитить**.

---

## Известные особенности и технический долг

| # | Проблема | Где |
|---|----------|-----|
| 1 | Два iblock каталога (13 и 36) — риск рассинхрона | `catalog/index.php`, `header.php` |
| 2 | Виртуальные строки корзины (`PRODUCT_ID=0`) для резки | `basket_update.php` |
| 3 | `isDebug()` перевёрнут — возвращает `true`, когда debug выключен | `init.php`, `footer.php` |
| 4 | `/personal/` прописан в конфиге, но checkout только через AJAX | `catalog/index.php` |
| 5 | `dev/index.php` — хардкод product ID для тестов | не выкатывать на prod |
| 6 | Кнопка «Заказать звонок» в шапке закомментирована | `header.php` |
| 7 | reCAPTCHA-ключи захардкожены в шаблонах | компоненты `prime:main.feedback` |
| 8 | Полное ядро Битрикс в git — тяжёлый репозиторий | весь `bitrix/` |

---

## Журнал изменений

| Дата | Что сделано |
|------|-------------|
| 2026-07-12 | Git: `.gitignore`, example-конфиги, push в bziksv/metplus. Зафиксированы prod/test серверы |
| 2026-07-12 | Локальный dev-сервер :8086, импорт БД test_metplus (1019 таблиц), скрипты в `scripts/` |
| 2026-07-12 | `.cursorignore`, правила Cursor, `docs/PROJECT.md` |
| 2026-07-12 | Синхронизация с архивом: dot-меню, `.htaccess`, `.access.php`, PDF-прайсы. Отчёт: `docs/archive-sync-report.md` |
| 2026-07-12 | SSH-ключ с Mac на 37.230.112.153 (`server2.metplus-vrn.ru`) |
| 2026-07-13 | Легенда: бейдж 500+/замок для доп. условий; правило вывода подсказок в `catalog-visual-language.md` |
| 2026-07-13 | Корзина: явная порезка по партиям (без авто-резки), автосохранение плана, итог «с резкой», компактный UI |
| 2026-07-13 | Каталог: переключатель видов + стили `catalog-views.css` / `catalog-view-switcher.js` |
| 2026-07-17 | Корзина `/cart/` + overlay; резка неполной; переключатель «Корзина: Оригинал / Новая» (как каталог) |

### Шаблон записи

```markdown
### YYYY-MM-DD — краткое описание
- **Проблема:** ...
- **Решение:** ...
- **Файлы:** path/to/file.php
```
