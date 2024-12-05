[![Aktuelle Version](https://img.shields.io/github/package-json/v/rrze-webteam/rrze-events/main?label=Version)](https://github.com/RRZE-Webteam/rrze-events) [![Release Version](https://img.shields.io/github/v/release/rrze-webteam/rrze-events?label=Release+Version)](https://github.com/rrze-webteam/rrze-events/releases/) [![GitHub License](https://img.shields.io/github/license/rrze-webteam/rrze-events)](https://github.com/RRZE-Webteam/rrze-events) [![GitHub issues](https://img.shields.io/github/issues/RRZE-Webteam/rrze-events)](https://github.com/RRZE-Webteam/rrze-events/issues) ![GitHub milestone details](https://img.shields.io/github/milestones/progress-percent/RRZE-Webteam/rrze-events/3)

# RRZE-Events

WordPress-Plugin für Veranstaltungen

## Features
- Custom Post Types für Referenten und Vorträge (mit anpassbarer Benennung)
- Flexible Darstellung von Referenten und Vorträgen mithilfe von Shortcodes (siehe unten) und Blöcken
- Suchmaschinenoptimierte Darstellung der Veranstaltung durch strukturierte Daten (schema.org)

## Shortcodes
### Auflistung aller Vorträge: `[talk]`
- `category="2018, workshop"`: Beschränkung auf eine oder mehrere Kategorien (AND-verknüpft)
- `number="5"`: Anzahl der Vorträge (default: 30)
- `format="table"`: Als Standard werden die Vorträge als Artikel ausgegeben (Titel, Detailangaben (Datum, Uhrzeit), Referent, Bild, Text. `format="table"` erzeugt eine tabellarische Ansicht. Anzahl und Reihenfolge der Spalten können dabei über folgendes Attribut gesteuert werden:
    - `columns="date, duration, title, speaker"` (default)
    - verfügbare Werte: date, start, end, duration, title, speaker, location, participants, available, short

### Alphabetische Auflistung aller Referenten: `[speaker]`
- `category="2018, webdesign"`: Beschränkung auf eine oder mehrere Kategorien (AND-verknüpft)
- `number="5"`: Anzahl der Referenten (default: 30)
- `id="1234, 9876"`: einen oder mehrere einzelne Referenten auswählen
- `format="list"`: Als Standard werden die Referenten in einer Übersicht mit Name und Teaser dargestellt. `format="list"` erzeugt eine einfache Namensliste.