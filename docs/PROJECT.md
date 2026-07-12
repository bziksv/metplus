# metplus-vrn.ru — документация проекта

## О проекте

Сайт на **1С-Битрикс** — каталог металлопроката ООО «КОРПОРАЦИЯ МЕТАЛЛИНВЕСТ» (Воронеж).

| Параметр | Значение |
|----------|----------|
| CMS | 1С-Битрикс |
| Шаблон | `bitrix/templates/metplus/` |
| Кастомный код | `bitrix/php_interface/`, `include/`, `ajax/`, `catalog/` |
| Медиа товаров | `upload/` (iblock, medialibrary, resize_cache) |
| Локальный URL | **http://localhost:8086/** |
| Локальная БД | `test_metplus` @ `127.0.0.1` |
| Git | [github.com/bziksv/metplus](https://github.com/bziksv/metplus) |

## Сервер и окружения

| Окружение | Домен | Путь на сервере | IP |
|-----------|-------|-----------------|-----|
| **Prod** | metplus-vrn.ru | `/var/www/metplus-vrn.ru/data/www/metplus-vrn.ru` | 37.230.112.153 |
| **Test** (доработки) | test.metplus-vrn.ru | `/var/www/test_metplus_usr/data/www/test.metplus-vrn.ru` | 37.230.112.153 |

**Workflow:** доработки на test → согласование → выкат на prod (metplus-vrn.ru).

## Git — что НЕ коммитить

Секреты исключены в `.gitignore`:
- `bitrix/.settings.php` — пароль БД, crypto_key
- `bitrix/php_interface/dbconn.php`, `dbconn.local.php` — пароль БД
- `bitrix/license_key.php`
- `.local/` — локальный dev и бэкапы конфигов
- `upload/`, кеш, дампы (`*.sql`, `*.tar.gz`)

Примеры без секретов: `bitrix/.settings.example.php`, `bitrix/php_interface/dbconn.example.php`.

## Локальная разработка

```bash
./scripts/setup-local-db.sh    # импорт test_metplus.sql (один раз)
./scripts/start-dev.sh         # nginx + php-fpm 8.3, порт 8086
./scripts/stop-dev.sh          # остановка
```

Конфиг: `.local/` (nginx, php-fpm). Локальный dbconn: `bitrix/php_interface/dbconn.local.php`.

## Порты соседних проектов

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

## Структура (рабочие зоны)

```
bitrix/templates/metplus/   — шаблон сайта, компоненты
bitrix/php_interface/       — init.php, dbconn, хуки
include/                    — подключаемые блоки
catalog/                    — разделы каталога
ajax/                       — AJAX-обработчики
dev/                        — dev-окружение
```

## Исключения из индексации Cursor

Настроены в `.cursorignore`:
- кеш Битрикс (`bitrix/cache`, `managed_cache`, `stack_cache`)
- ядро (`bitrix/admin`, стандартные компоненты)
- медиа товаров (`upload/`)
- дампы БД и архивы (`*.sql`, `*.tar.gz`)
- бинарные файлы (изображения, шрифты, PDF)

После изменения `.cursorignore`: **Cursor Settings → Indexing → Reindex**.

---

## Журнал изменений

| Дата | Что сделано | Автор |
|------|-------------|-------|
| 2026-07-12 | Git: `.gitignore`, example-конфиги, push в bziksv/metplus. Зафиксированы prod/test серверы | — |
| 2026-07-12 | Локальный dev-сервер :8086, импорт БД test_metplus (1019 таблиц), скрипты в `scripts/` | — |
| 2026-07-12 | Создан `.cursorignore`, правила Cursor, этот файл. Причина: Cursor не индексировал проект из-за объёма (~10k+ файлов, 1.9 ГБ bitrix/) | — |

### Шаблон записи

```markdown
### YYYY-MM-DD — краткое описание
- **Проблема:** ...
- **Решение:** ...
- **Файлы:** path/to/file.php
```
