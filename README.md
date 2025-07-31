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
│       ├── common.js
│       └── print.js
├── includes/
│   ├── header.php
│   └── footer.php
├── templates/
│   └── label_template.lbx
├── vendor/
├── admin_documents.php
├── admin_locations.php
├── admin_archive.php
├── barcode_image.php
├── barcode_scan.php
├── BrotherPrinter.php
├── config.php
├── create_template.php
├── Database.php
├── DocumentManager.php
├── index.php
├── list_printers.php
├── login.php
├── logout.php

├── search.php
├── test_printer.php
├── test_printing.html
├── PRINTING_SETUP.md
└── composer.json
```

## Konfiguracja drukarki Brother QL-820NWB

### Wymagania
- Drukarka Brother QL-820NWB podłączona przez USB
- Etykiety 17x54mm załadowane w drukarce
- Sterowniki Brother zainstalowane w systemie Windows

### Konfiguracja drukarki
1. **Zainstaluj sterowniki** Brother QL-820NWB ze strony producenta
2. **Podłącz drukarkę** przez USB
3. **Załóż etykiety** 17x54mm w drukarce
4. **Skonfiguruj drukarkę** w systemie Windows:
   - Otwórz Panel sterowania → Urządzenia i drukarki
   - Kliknij prawym na drukarkę Brother QL-820NWB
   - Wybierz "Właściwości drukarki"
   - W zakładce "Dokument domyślny" ustaw rozmiar na 17x54mm

### Konfiguracja aplikacji
W pliku `config.php` ustaw:
- `PRINTER_NAME` - nazwa drukarki w systemie (domyślnie "Brother QL-820NWB")
- `PRINTER_MEDIA_SIZE` - rozmiar etykiety (domyślnie "17x54mm")

### Użycie
1. **Kliknij "Drukuj"** w tabeli dokumentów
2. **Etykieta otworzy się** w notatniku
3. **Naciśnij Ctrl+P** aby wydrukować
4. **Wybierz drukarkę** Brother QL-820NWB
5. **Ustaw rozmiar** na 17x54mm jeśli potrzebne
6. **Wydrukuj** etykietę

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
- Windows Notepad (drukowanie etykiet)
- Windows Print Spooler (komunikacja z drukarką) 