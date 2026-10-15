# maidem_stacktree

Tech-Stack als Baumdiagramm (Linear-Stil, geschwungene Linien). Knoten werden im Backend gepflegt, die Ausgabe ist eine Inline-SVG ohne JS-Layout.

## Setup
1. `ddev composer require maidem/stacktree:@dev`, `ddev typo3 extension:setup`, Cache leeren.
2. Site Set `maidem/stacktree` ist Abhängigkeit von `gripsraum/my-sitepackage` (lädt Template-Pfade + `stacktree.css`).
3. SysFolder anlegen, Knoten dort erfassen.
4. Plugin „Stack-Baum" auf eine Seite legen, im Feld *Startpunkt* den SysFolder wählen. Der Plugin-Header wird als Überschrift genutzt.

## Pflege
- **Inhalt → Listenmodul** (oder Modul *Web › Stack-Baum*): Felder Titel, Untertitel, Icon (SVG/PNG, optional, sonst Monogramm), Status (gebaut/geplant), Link, **Elternknoten** (Baumauswahl, leer = oberste Ebene).
- Reihenfolge der Geschwister: Drag im Listenmodul (`sorting`).
- Modul *Web › Stack-Baum*: Seitenbaum-Ordner wählen → Live-Vorschau, Klick auf Kachel = Bearbeiten, Buttons „Neuer Knoten" / Listenmodul.

## Funktionsweise
- `TreeBuilder`: Flat-Query → Baum (verwaiste/zyklische Knoten landen oben, nichts geht verloren).
- `TreeLayout`: berechnet Kachelpositionen und Pfade in viewBox-Einheiten. Zwei SVGs: horizontal (S-Kurven, ab 48rem) und vertikal (Dateibaum mit runden Ecken, Handy). CSS blendet eine ein.
- Animation: nur `stroke-dashoffset`/`opacity`, gestartet über den IntersectionObserver in `Main.entry.js` (`.cb-stacktree`), `prefers-reduced-motion` = statisch.
- Seiten-Cache: Extbase setzt Cache-Tags, Änderungen leeren automatisch.
- Titel werden pro Kachel gekürzt (~22 Zeichen horizontal), kurze Titel halten.
