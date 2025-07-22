# System Zarządzania Dokumentami

System do zarządzania dokumentami prawnymi z kodami kreskowymi i drukowaniem etykiet.

## Funkcjonalności

- Zarządzanie miejscami na półkach (cywilne/karne)
- Dodawanie dokumentów z automatycznym generowaniem kodów kreskowych
- Drukowanie kodów kreskowych na drukarkę Brother QL-820NWB
- Wyszukiwanie dokumentów
- Archiwizacja dokumentów
- Skanowanie kodów kreskowych

## Wymagania

- PHP 7.4+
- MySQL/MariaDB
- Composer
- Drukarka Brother QL-820NWB (opcjonalnie)

## Instalacja

1. Sklonuj repozytorium
2. Zainstaluj zależności: `composer install`
3. Skonfiguruj bazę danych w `config.php`
4. Zaimportuj strukturę bazy: `database.sql`
5. Ustaw uprawnienia dla katalogów `assets/` i `includes/`

## Struktura projektu

```
dokumenty/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── common.js
├── includes/
│   ├── header.php
│   └── footer.php
├── vendor/
├── admin_documents.php
├── admin_locations.php
├── admin_archive.php
├── barcode_image.php
├── barcode_scan.php
├── config.php
├── Database.php
├── DocumentManager.php
├── index.php
├── login.php
├── logout.php
├── print_document.php
├── search.php
└── composer.json
```

## Konfiguracja drukarki

W pliku `config.php` ustaw:
- `PRINTER_NAME` - nazwa drukarki w CUPS
- `PRINTER_MEDIA_SIZE` - rozmiar etykiety (np. '17x54mm')

## Użycie

1. Zaloguj się jako administrator
2. Dodaj miejsca na półkach
3. Dodaj dokumenty - system automatycznie wygeneruje kod kreskowy
4. Kliknij "Drukuj" aby wydrukować kod kreskowy
5. Użyj skanera do wyszukiwania dokumentów

## Technologie

- PHP 7.4+
- MySQL
- HTML5/CSS3/JavaScript
- Composer (Picqer/php-barcode-generator)
- CUPS (drukowanie) 