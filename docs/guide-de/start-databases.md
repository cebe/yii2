Arbeiten mit Datenbanken
========================
Dieses Kapitel beschreibt wie man eine Seite erstellt, welche Länderdaten aus einer Datenbanktabelle
namens `country` darstellt. Um dies zu erreichen, werden wir eine Datenbankverbindung aufbauen, eine 
[Active Record](db-active-record.md) Klasse erstellen, eine [Action](structure-controllers.md) definieren
und eine [View](structure-views.md) erstellen.

In diesem Tutorial lernen Sie:
* eine Datenbankverbindung zu konfigurieren,
* eine Active Record Klasse zu erstellen,
* Daten abzufragen mittels der Active Record Klasse,
* Daten in einer View darzustellen in Seitenweiser Darstellung

Um dieses Kapitel erfolgreich abschliessen zu können, sollten Sie grundlegende Kenntnis und Erfahrung im Umgang
mit Datenbanken haben. Sie sollten wissen, wie man eine Datenbank erstellt und wie man SQL-Abfragen mittels 
eines DB-Client Tools ausführt.


Vorbereiten der Datenbank <span id="preparing-database"></span>
-------------------------

Um zu beginnen erstellen Sie eine Datenbank namens `yii2basic`, mit welcher wir in Ihrer Applikation arbeiten werden.
Sie können eine SQLite, MySQL, PostgreSQL, MSSQL oder auch Oracle Datenbank erstellen. Yii ist kompatibel mit vielen
Datenbank Engines. Der Einfachheit halber wird im weiteren Verlauf des Tutorials MySQL als Engine angenommen.

> Info: MariaDB war lange Zeit ein 1:1 Ersatz für MySQL. Dies stimmt nicht mer ganz. Falls Sie erweiterte Funktionen 
  wie z.B. `JSON` verwenden möchten, prüfen Sie die Verwendung der MariaDB-Erweiterung weiter unten.

Als nächstes, erstellen Sie eine Tabelle names `country` und fügen Sie Beispieldaten ein. Sie können folgende SQL
Statments dazu verwenden:

```sql
CREATE TABLE `country` (
  `code` CHAR(2) NOT NULL PRIMARY KEY,
  `name` CHAR(52) NOT NULL,
  `population` INT(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `country` VALUES ('AU','Australia',24016400);
INSERT INTO `country` VALUES ('BR','Brazil',205722000);
INSERT INTO `country` VALUES ('CA','Canada',35985751);
INSERT INTO `country` VALUES ('CN','China',1375210000);
INSERT INTO `country` VALUES ('DE','Germany',81459000);
INSERT INTO `country` VALUES ('FR','France',64513242);
INSERT INTO `country` VALUES ('GB','United Kingdom',65097000);
INSERT INTO `country` VALUES ('IN','India',1285400000);
INSERT INTO `country` VALUES ('RU','Russia',146519759);
INSERT INTO `country` VALUES ('US','United States',322976000);
```

An diesem Punkt haben Sie eine Datenbank names `yii2basic` mit einer Tabelle `country` mit 3 Feldern mit 10 Datensätzen.

Kofiguration einer Datenbankverbindung <span id="configuring-db-connection"></span>
--------------------------------------

Bevor Sie fortfahren, stellen Sie sicher dass Sie sowohl die [PDO](http://www.php.net/manual/en/book.pdo.php) PHP Erweiterung
sowie den PDO Treiber für die von Ihnen verwendeten Datenbank-Engine (z.B. `pdo_mysql` für MySQL) installiert haben. Dies ist
eine Voraussetzung für Applikationen welche relationale Datenbanken verwenden.

Öffnen Sie danach die Datei `config/db.php` und ändern Sie die Parameter, so dass sie für Ihre Datenbank stimmen. 
Standarmässig enthält die Datei:

```php
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
```

Die Datei `config/db.php` ist eine typische, Datei basierte [Konfigurationsdatei](concept-configurations.md). Diese spezielle Datei
enthält die Parameter, welche benötigt werden um eine [[yii\db\Connection]] Instanz zu initialisieren, durch welche Sie SQL
Abfragen gegen eine Datenbank ausführen können.

Die Datenbankverbindung die wir oben konfiguriert habe, kann im Applikationscode mittels des Ausdrucks `Yii::$app->db`
verwendet werden.

> Info: Die Datei `config/db.php` wird eingebunden von der Hauptapplikationskonfigurationsdatei `config/web.php`, welche
  spezifiziert wie die [Applikationsinstanz](structure-applications.md) initialisiert werden soll. Weitere Informationen
  erhalten Sie unter dem Kapitel [Konfigurationen](concept-configurations.md).
  
Falls Sie mit Datenbanken arbeiten möchten, welche nicht nativ von Yii unterstützt werden, können Sie auf folgende
Erweiterungen zurückgreifen:

- [Informix](https://github.com/edgardmessias/yii2-informix)
- [IBM DB2](https://github.com/edgardmessias/yii2-ibm-db2)
- [Firebird](https://github.com/edgardmessias/yii2-firebird)
- [MariaDB](https://github.com/sam-it/yii2-mariadb)

Erstellen eines Active Records <span id="creating-active-record"></span>
------------------------------

Um einen Datensatz der Tabelle `country` zu repräsentieren bzw. abzufragen, erstellen Sie eine 
[Active Record](db-active-record.md)-basierte Klasse names `Country` und speichern Sie diese in der Datei `models/Country.php`

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

class Country extends ActiveRecord
{
}
```

Die `Country` Klasse erbt von [[yii\db\ActiveRecord]]. Dadurch müssen Sie überhaupt keinen Code schreiben! Mit nur diesem
bisschen Code wird Yii den Tabellennamen vom Klassennamen ableiten.

> Info: Falls keine direkte Übereinstimmung vom Klassennamen und dem Tabellennamen existiert, können Sie die
  [[yii\db\ActiveRecord::tableName()]] überschreiben um den Tabellennamen explizit zu definieren.

Mit der Verwendung der `Country`-Klasse können Sie einfach Daten innerhalb der `country`-Tabelle manipulieren, wie in diesem
Beispiel demonstriert:

```php
use app\models\Country;

// Abfragen aller Datensätze aus der country Tabelle sortiert nach "name"
$countries = Country::find()->orderBy('name')->all();

// Abfragen des Datensatzes mit dem Primärschlüssel "US"
$country = Country::findOne('US');

// Anzeigen von "United States"
echo $country->name;

// Anpassen des Namens zu "U.S.A." und speichern des Datensatzes 
$country->name = 'U.S.A.';
$country->save();
```

> Info: Active Records sind eine mächtige Waffe um Daten innerhalb einer Datenbank abzufragen oder zu manipulieren in
  einer objektorientierten Art und Weise. Sie finden mehr Informationen im Kapitel [Active Record](db-active-record.md).
  Alternativ können Sie auf eine ein Level tiefer liegende Weise arbeiten mittels [Database Access Objects](db-dao.md).

Erstellen einer Action <span id="creating-action"></span>
----------------------

Um die country-Daten dem Besucher nun anzeigen zu können, müssen Sie eine Action erstellen. Anstatt die Action im `site`
controller zu erstellen wie in den vorhergehenden Kapiteln, macht es in diesem Fall mehr Sinn, einen neuen Controller für
alle mit den country-Daten zusammenhängenden Aktionen zu verwenden. Nennen Sie diesen Controller `CountryController` und
erstellen Sie eine `index`-Aktion innerhalb des Controllers, wie hier beschrieben:

```php
<?php

namespace app\controllers;

use yii\web\Controller;
use yii\data\Pagination;
use app\models\Country;

class CountryController extends Controller
{
    public function actionIndex()
    {
        $query = Country::find();

        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $query->count(),
        ]);

        $countries = $query->orderBy('name')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'countries' => $countries,
            'pagination' => $pagination,
        ]);
    }
}
```

Speichern Sie diesen Code in der Datei `controllers/CountryController.php`.

Die `index`-Aktion ruft die Methode `Country::find()` auf. Diese Active Record Methode erstellt ein DB-Query welches
verwendet wird, um alle Datensätze der Tabelle `country` abzufragen. Um die Anzahl Länder pro Request zu limitieren wird
die Abfrage mittels [[yii\data\Pagination]]-Objekt geteilt in mehrere Seiten. Das `Pagination`-Objekt erfüllt 2 Zwecke:

* Es setzt die `offset` und `limit` Anweisungen im SQL Statement so dass es lediglich die Daten einer Seite pro mal abfragt
  (meist 5 Datensätze pro Seite).
* Es wird verwendet um einen Pager mit einer Liste aller Seiten-Buttons darzustellen. Auf dies wird in der nächsten Sektion
  näher eingegangen. 
  
Am Ende der `index`-Aktion wird eine View namens `index` gerendert und die country-Daten sowie das Pagination-Objekt übergeben.

Erstellen einer View <span id="creating-view"></span>
--------------------

Innerhalb des `views`-Verzeichnisses, erstellen Sie erst ein Unterverzeichnis namens `country`. Dieses Verzeichnis wird 
verwendet um alle Views die vom `country` Controller verwendet werden zu beherbergen. Innerhalb des `views/country`
Verzeichnisses, erstellen Sie eine Datei `index.php` mit folgendem Inhalt:

```php
<?php
/* @var $countries app\models\Country[] */
/* @var $pagination yii\data\Pagination */

use yii\helpers\Html;
use yii\widgets\LinkPager;
?>
<h1>Countries</h1>
<ul>
<?php foreach ($countries as $country): ?>
    <li>
        <?= Html::encode("{$country->code} ({$country->name})") ?>:
        <?= $country->population ?>
    </li>
<?php endforeach; ?>
</ul>

<?= LinkPager::widget(['pagination' => $pagination]) ?>
```

Die View enthält zwei Abschnitte im Zusammenhang mit der Darstellung der country-Daten. Im ersten Abschnitt wird durch die
country-Daten durchgegangen und sie werden gerendert als Aufzählung. Im zweiten Abschnitt wird ein [[yii\widgets\LinkPager]]
Widget gerendert unter Verwendung des in der Aktion erstellen Pagination-Objekts. Das `LinkPager`-Widget erstellt eine Liste
mit "Seiten-Buttons". Durch das klicken auf einen dieser Buttons werden die country-Daten der jeweiligen Seite geladen.

Testen <span id="trying-it-out"></span>
------

Öffnen Sie in Ihrem Browser folgende URL um das Ganze zu testen:

```
http://hostname/index.php?r=country%2Findex
```

![Country List](images/start-country-list.png)

Zu aller erst sehen Sie eine Seite welche 5 Länder anzeigt. Unter diesen Ländern werden Sie einen Pager sehen mit 4 Buttons.
Beim Klicken auf den Button "2" wird die Seite 5 weitere Länder laden und darstellen: die zweite Seite mit Datensätzen.
Sie werden feststellen, dass auch die URL im Browser sich ändert zu:

```
http://hostname/index.php?r=country%2Findex&page=2
```

Im Hintergrund liefert die [[yii\data\Pagination|Pagination]] Klasse alle nötige Funktionalität um die Aufteilung der Daten
in Seiten zu gewährleisten:

* Anfangs repräsentiert [[yii\data\Pagination|Pagination]] die erste Seite, welche eine Abbildung zur country SELECT Abfrage
  mit der Einschränkung `LIMIT 5 OFFSET 0` darstellt. Als Resultat werden die ersten 5 Datensätze abgefragt und dargestellt.
* Das [[yii\widgets\LinkPager|LinkPager]]-Widget rendert die "Seiten-Buttons" unter Verwendung der URLs erstellt von
  [[yii\data\Pagination::createUrl()|Pagination]]. Die URLs enthalten den Query-Parameter `page`, welcher die Seitenzahl
  repräsentiert.
* Beim Klicken auf den Button "2" wird einer neuer Request auf die Route `country/index` ausgelöst und abgehandelt.
  Das [[yii\data\Pagination|Pagination]]-Objekt liest den `page` Query-Parameter aus der URL aus und setzt die aktive Seite
  auf 2. Das neue Country-Query wird daraufhin die Einschränkung `LIMIT 5 OFFSET 5` aufweisen und die nächsten 5 Daentsätze
  zurückgeben.
  
Zusammenfassung <span id="summary"></span>
---------------

In diesem Kapitel haben Sie gelernt wie man mit Datenbanken arbeitet. Sie haben des Weiteren gelernt, wie man Daten auslesen
und anzeigen kann aufgeteilt in verschiedene Seiten unter Verwendung von [[yii\data\Pagination]] sowie [[yii\widgets\LinkPager]].

Im nächsten Kapitel lernen Sie die Verwendung des mächtigen Code-Generierungs-Tools genannt [Gii](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide),
welches Ihnen hilft gängige Features wie Create-Read-Update-Delete (CRUD) Operationen im Zusammenhang mit Datenbanken massiv
schneller zu implementieren. Der eben geschrieben Code kann vollumfänglich von Gii generiert werden.
