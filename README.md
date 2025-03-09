# PHPTable Dokumentation

## Übersicht

PHPTable ist eine konfigurierbare PHP-Klasse zur Erstellung von HTML-Tabellen mit verschiedenen Funktionen:

- Sortierfunktion (mit HTML-kompatiblem Sortieralgorithmus)
- Paginierung (Seitenumbruch für große Datensätze)
- Individuelles Styling für Zeilen, Spalten und Zellen
- HTML-Rendering in Zellen
- Anpassbare Spaltenausrichtung

Version: 13.4

## Dateien

- `PHPTable.php` - Hauptklasse
- `table.class.config.php` - Standardkonfiguration
- `table.class.sorter.php` - Sortierklasse
- `table.class.paginator.php` - Paginierungsklasse

## Installation

1. Kopieren Sie alle Dateien in Ihr Projektverzeichnis
2. Binden Sie die Hauptdatei ein:

```php
require_once 'PHPTable.php';
```

## Grundlegende Verwendung

```php
// Tabellen-ID für die Ausgabe
$tableVar = 12345;

// Daten definieren
$data = [
    ['id' => 1, 'name' => 'Max Mustermann', 'email' => 'max@example.com'],
    ['id' => 2, 'name' => 'Anna Schmidt', 'email' => 'anna@example.com']
];

// Header definieren
$headers = [
    'id' => 'ID',
    'name' => 'Name',
    'email' => 'E-Mail'
];

// Tabelle erstellen und konfigurieren
$table = new PHPTable(['tableVar' => $tableVar]);
$table->setData($data)
      ->setHeaders($headers)
      ->enableSorting()
      ->setDefaultSort('name', 'asc')
      ->render();
```

## Konfiguration

### Konstruktor-Optionen

Die PHPTable-Klasse akzeptiert ein Konfigurationsarray im Konstruktor:

```php
$config = [
    'tableVar' => 12345,              // 5-stellige Tabellen-ID (erforderlich)
    'table_class' => 'my-table',      // CSS-Klasse
    'table_id' => 'user-table',       // HTML-ID
    'header_bg_color' => '#4a6ea9',   // Kopfzeile Hintergrundfarbe
    'header_text_color' => '#ffffff', // Kopfzeile Textfarbe
    'render_html' => true             // HTML in Zellen rendern
];

$table = new PHPTable($config);
```

### Wichtige Konfigurationsparameter

| Parameter | Typ | Beschreibung | Standard |
|-----------|-----|--------------|----------|
| tableVar | int | 5-stellige ID für die Tabellenvariable | - |
| table_class | string | CSS-Klasse für die Tabelle | php-table |
| table_id | string | HTML-ID für die Tabelle | - |
| table_width | string | Tabellenbreite (CSS) | 100% |
| header_bg_color | string | Hintergrundfarbe der Kopfzeile | #f8f9fa |
| header_text_color | string | Textfarbe der Kopfzeile | #212529 |
| row_even_bg_color | string | Hintergrundfarbe gerader Zeilen | #ffffff |
| row_odd_bg_color | string | Hintergrundfarbe ungerader Zeilen | #f2f2f2 |
| row_hover_color | string | Hintergrundfarbe bei Hover | #e8e8e8 |
| row_even_text_color | string | Textfarbe gerader Zeilen | #212529 |
| row_odd_text_color | string | Textfarbe ungerader Zeilen | #212529 |
| border_color | string | Rahmenfarbe | #000000 |
| render_html | bool | HTML in Zellen rendern | false |
| sorting_enabled | bool | Sortierung aktivieren | true |
| pagination_enabled | bool | Paginierung aktivieren | false |
| rows_per_page | int | Anzahl Zeilen pro Seite | 10 |

## Methoden

### Basismethoden

#### `setData(array $data)`
Setzt die Tabellendaten.
```php
$table->setData([
    ['id' => 1, 'name' => 'Max', 'email' => 'max@example.com'],
    ['id' => 2, 'name' => 'Anna', 'email' => 'anna@example.com']
]);
```

#### `setHeaders(array $headers)`
Setzt die Spaltenüberschriften.
```php
$table->setHeaders([
    'id' => 'ID',
    'name' => 'Name',
    'email' => 'E-Mail'
]);
```

#### `setConfig(array $config)`
Aktualisiert die Konfiguration.
```php
$table->setConfig([
    'header_bg_color' => '#336699',
    'header_text_color' => '#ffffff'
]);
```

#### `render()`
Rendert die Tabelle und gibt sie zurück.
```php
$table->render();
```

### Paginierungsmethoden

#### `enablePagination(int $rowsPerPage = 10)`
Aktiviert die Paginierung mit angegebener Zeilenzahl pro Seite.
```php
$table->enablePagination(5);
```

#### `disablePagination()`
Deaktiviert die Paginierung.
```php
$table->disablePagination();
```

#### `showPage(int $pageNumber)`
Setzt die anzuzeigende Seite.
```php
$table->showPage(2);
```

### Sortiermethoden

#### `enableSorting()`
Aktiviert die Sortierung.
```php
$table->enableSorting();
```

#### `disableSorting()`
Deaktiviert die Sortierung.
```php
$table->disableSorting();
```

#### `setDefaultSort(string $column, string $direction = 'asc')`
Setzt die Standardsortierung.
```php
$table->setDefaultSort('name', 'asc');
```

### Styling-Methoden

#### `setAlternateRowColors(string $evenColor, string $oddColor)`
Setzt Hintergrundfarben für gerade und ungerade Zeilen.
```php
$table->setAlternateRowColors('#f0f8ff', '#e6f2ff');
```

#### `setAlternateTextColors(string $evenColor, string $oddColor)`
Setzt Textfarben für gerade und ungerade Zeilen.
```php
$table->setAlternateTextColors('#000080', '#0000cd');
```

#### `setBorderColor(string $color)`
Setzt die Rahmenfarbe.
```php
$table->setBorderColor('#4682b4');
```

#### `setCellAlignment(string $alignment)`
Setzt die Textausrichtung für alle Zellen.
```php
$table->setCellAlignment('center');
```

#### `setColumnAlignment(string $column, string $alignment)`
Setzt die Textausrichtung für eine bestimmte Spalte.
```php
$table->setColumnAlignment('id', 'center');
$table->setColumnAlignment('price', 'right');
```

#### `setCellFontStyle(string $style)`
Setzt den Schriftstil für alle Zellen.
```php
$table->setCellFontStyle('italic');
```

#### `setCellFontWeight(string $weight)`
Setzt die Schriftstärke für alle Zellen.
```php
$table->setCellFontWeight('bold');
```

#### `setTextColor(string $color)`
Setzt die Textfarbe für alle Zeilen.
```php
$table->setTextColor('#333333');
```

### HTML-Rendering

#### `enableHtmlRendering()`
Aktiviert das HTML-Rendering in Zellen.
```php
$table->enableHtmlRendering();
```

#### `disableHtmlRendering()`
Deaktiviert das HTML-Rendering in Zellen.
```php
$table->disableHtmlRendering();
```

## Fortgeschrittene Beispiele

### Tabelle mit HTML-Inhalten

```php
$data = [
    [
        'id' => 1,
        'name' => 'Max <strong>Mustermann</strong>',
        'email' => '<a href="mailto:max@example.com">max@example.com</a>',
        'status' => '<span style="color:green">Aktiv</span>'
    ],
    [
        'id' => 2,
        'name' => 'Anna <em>Schmidt</em>',
        'email' => '<a href="mailto:anna@example.com">anna@example.com</a>',
        'status' => '<span style="color:red">Inaktiv</span>'
    ]
];

$table = new PHPTable(['tableVar' => 12345]);
$table->setData($data)
      ->setHeaders([
          'id' => 'ID',
          'name' => 'Name',
          'email' => 'E-Mail',
          'status' => 'Status'
      ])
      ->enableHtmlRendering()
      ->render();
```

### Tabelle mit individuellen Spaltenausrichtungen

```php
$data = [
    ['id' => 1, 'name' => 'Produkt A', 'price' => '19,99 €', 'stock' => '250 Stück'],
    ['id' => 2, 'name' => 'Produkt B', 'price' => '29,99 €', 'stock' => '100 Stück']
];

$table = new PHPTable(['tableVar' => 12345]);
$table->setData($data)
      ->setHeaders([
          'id' => 'Art.-Nr.',
          'name' => 'Produkt',
          'price' => 'Preis',
          'stock' => 'Lagerbestand'
      ])
      ->setCellAlignment('left')            // Globale Ausrichtung
      ->setColumnAlignment('id', 'center')  // ID zentriert
      ->setColumnAlignment('price', 'right') // Preis rechtsbündig
      ->setColumnAlignment('stock', 'right') // Lagerbestand rechtsbündig
      ->render();
```

### Tabelle mit Paginierung

```php
// Viele Daten
$data = [];
for ($i = 1; $i <= 100; $i++) {
    $data[] = [
        'id' => $i,
        'name' => 'Eintrag ' . $i,
        'description' => 'Beschreibung für Eintrag ' . $i
    ];
}

$table = new PHPTable(['tableVar' => 12345]);
$table->setData($data)
      ->setHeaders([
          'id' => 'ID',
          'name' => 'Name',
          'description' => 'Beschreibung'
      ])
      ->enablePagination(10)  // 10 Einträge pro Seite
      ->showPage(2)           // Zeige Seite 2
      ->render();
```

### Episodenliste mit Sortierung

```php
$episodes = [
    [
        'title' => '<div align="left" style="font-size:90%">Sherlock - S01E02 - Der blinde Banker</div>',
        'duration' => '90 min'
    ],
    [
        'title' => '<div align="left" style="font-size:90%">Barbie - S01E44 - Barbie & Teresa: So schmeckt Freundschaft</div>',
        'duration' => '25 min'
    ],
    [
        'title' => '<div align="left" style="font-size:90%">Sherlock - S01E01 - Ein Skandal in Belgravia</div>',
        'duration' => '90 min'
    ],
    [
        'title' => '<div align="left" style="font-size:90%">Barbie - S01E45 - Ein Tag am Strand</div>',
        'duration' => '25 min'
    ]
];

$table = new PHPTable(['tableVar' => 12345]);
$table->setData($episodes)
      ->setHeaders([
          'title' => 'Titel',
          'duration' => 'Dauer'
      ])
      ->enableHtmlRendering()
      ->enableSorting()
      ->setDefaultSort('title', 'asc')
      ->render();
```

## Hinweise

### Speicherung des Tabellen-HTML

Die PHPTable-Klasse verwendet die `SetValueString()`-Funktion zum Speichern der generierten HTML-Ausgabe:

```php
SetValueString($this->tableVar, $output);
```

Diese Funktion muss in Ihrer Umgebung definiert sein oder kann bei Bedarf angepasst werden.

### Sortieren von HTML-Inhalten

Die Sortierfunktion erkennt automatisch Inhalte im Format "Name - SXXEXX - Titel" und sortiert korrekt nach Serienname, Staffel und Episode, auch wenn der Text in HTML-Tags eingebettet ist.

### Fehlerbehebung

- Wenn Sortiericons nicht angezeigt werden, überprüfen Sie, ob die verwendeten Unicode-Symbole von Ihrem Browser unterstützt werden
- Bei Problemen mit der HTML-Darstellung, überprüfen Sie, ob `enableHtmlRendering()` aufgerufen wurde
- Wenn unerwartete Sortierergebnisse auftreten, prüfen Sie, ob der Sortierschlüssel mit einem Schlüssel im Header-Array übereinstimmt
