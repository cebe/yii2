Hallo sagen
===========

Dieses Kapitel beschreibt wie man eine "Hallo"-Seite innerhalb der Appliakation erstellt.
Um dies zu erreichen werden Sie eine [Aktion](structure-controllers.md#creating-actions) und eine
[View](structure-views.md) erstellen:

* Die Applikation wird den Request zu der Aktion durchstellen
* und die Aktion wird die View rendern welche dem Besucher das Wort "Hello" anzeigt.

Während dieses Tutorials werden Sie drei Sachen lernen:

1. Wie man eine [Aktion](structure-controllers.md#creating-actions) erstellt um auf Anfragen zu antworten
2. Wie man eine [View](structure-views.md) erstellt um Inhalte zu erstellen und
3. Wie eine Applikation ein Request verarbeitet und zu [Aktionen](structure-controllers.md#creating-actions) weiterleitet.

Eine Aktion erstellen <span id="creating-action"></span>
---------------------

Für die "Hello"-Aufgabe erstellen Sie eine [Aktion](structure-controllers.md#creating-actions) `say` welche einen Parameter
`message` des Requests ausliest und wieder ausgiebt. Wenn der Request keinen `message`-Parameter enthält soll "Hello"
(als Standard) ausgegeben werden. 

> Info: [Aktionen](structure-controllers.md#creating-actions) sind Objekte auf welche Besucher direkt navigieren können.
  Aktionen werden in [Controllern](structure-controllers.md) gebündelt. Das Resultat der Ausführung der Aktion ist die
  Antwort die der Besucher erhalten wird.
  
Aktionen müssen in [Controllern](structure-controllers.md) definiert werden. Zur Vereinfachung können Sie die `say`-Aktion
in dem existierenden `SiteController` erstellen. Dieser Controller ist definiert in der Datei `controllers/SiteController.php`.
Folgend der Start der neuen Aktion:

```php
<?php

namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    // ...existing code...

    public function actionSay($message = 'Hello')
    {
        return $this->render('say', ['message' => $message]);
    }
}
```

Im obigen Code wird die `say` Aktion definiert als Methode namens `actionSay` innerhalb der `SiteController`-Klasse.
Yii verwendet das Präfix `action` um zwischen Aktionsmethoden und gewöhnlichen Methoden innerhalb eines Controllers
zu unterscheiden. Der Name nach dem `action`-Präfix wird zur Aktions-ID.

Sobald Sie Ihre Aktionen bennen müssen sollten Sie verstehen, wie Yii Aktions-IDs behandelt. Aktion-IDs sind jeweils
referenziert in Kleinbuchstaben. Wenn eine Aktions-ID mehr als ein Wort enthält, werden diese mittels Dash verbunden
(z.B. `create-comment`). Die dazugehörigen Methodennamen werden genriert indem alle Dashes aus den IDs entfernt werden,
der erste Buchstabe des jewiligen Wortes grossgeschrieben wird und dem Resultat das Wort `action` vorangestellt wird.
Zum Beispiel: Die Aktions-ID `create-comment` korrespondiert mit dem Methodennamen `actionCreateComment`.

Die Aktionsmethode in unserem Beispiel akzeptiert den Parameter `$message` wessen Standardwert "Hello" ist (dieser wird
exakt gleich definiert wie jeder andere Standardwert eines Arguments einer Methode in PHP). Sobald die Applikation ein
Request erhält wo die `say` Aktion verantwortlich zur Bearbeitung ist, wird die Applikation diesen Parameter befüllen 
einem allfälligen Parameter im Request (mit selbem Namen). In anderen Worten: Wenn der Request einen `message`-Parameter
mit einem Wert `"Goodbye"` enthält, erhält die `$message`-Variable innerhalb der Aktion diesen Wert.

Innerhalb der Aktionsmethode wird die Methode [[yii\web\Controller::render()|render()]] aufgerufen um die [View](structure-views.md)
mit dem Namen `say` zu rendern. Der `message`-Parameter wird auch der View übergeben so dass er dort verwendet werden
kann. Das Resultat wird der Aktionsmethode zurückgegeben. Dieses Resultat wird von der Applikation weitergegeben an den
Browser des Besuchers (als Teil einer vollständigen HTML-Seite).

Erstellen einer View <span id="creating-view"></span>
--------------------

[Views](structure-views.md) sind Skripte welche Sie schreiben die verwendet werden zur Generierung von Inhalt.
Für die "Hallo"-Aufgabe erstellen Sie eine `say`-View welche den `message` Parameter aus der Aktionsmethode ausgiebt:

```php
<?php
/* @var $message string */

use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

Die `say` View sollte abgelegt werden in der Datei `views/site/say.php`. When die Methode [[yii\web\Controller::render()|render()]]
in einer Aktion aufgerufen wird, sucht Sie nach einer PHP-Datei mit den Namenskonvention `views/<ControllerID>/<ViewName>.php`.

Beachten Sie das in obigem Beispiel der `message` Parameter [[yii\helpers\Html::encode()|HTML-enkodiert]] wird bevor er
ausgegeben wird. Das ist nötig da der Parameter vom Besucher kommt, welcher in gegenüber 
[Cross-Site Scripting (XSS) Attacken](https://de.wikipedia.org/wiki/Cross-Site-Scripting) mit bösartigem JavaScript Code
anfällig macht.

Selbstverständlich könnten Sie mehr Inhalt in die `say`-View packen. Der Inhalt kann HTML tags beinhalten, Plain Text oder
auch PHP Statements. Tatsächlich ist die `say`-View nichts anderes als ein PHP-Skript welches ausgeführt wird von der
[[yii\web\Controller::render()|render()]] Methode. Der Inhalt der vom Skript ausgegeben wird, wird zurückgegeben als
Antwort der Applikation. Die Applikation wird diese Antwort an den Besucher weiterleiten.

Testen <span id="trying-it-out"></span>
------

Nach dem Erstellen der Aktion und der View öffnen Sie in Ihrem Browser folgende URL um das Ganze zu testen:

```
http://hostname/index.php?r=site%2Fsay&message=Hello+World
```

![Hello World](images/start-hello-world.png)

Das Resultat ist eine Seite die "Hello World" anzeigt. Diese Seite teilt sich den Header und Footerbereich mit den anderen
Applikationsseiten.

Wenn Sie dem `message`-Parameter in der URL weglassen, sehen Sie dieselbe Seite ausschliesslich das Wort "Hello" anzeigen.
Dies, weil `message` als Parameter an die `actionSay()` Methode übregeben wird und wenn diese weggelassen wird, der 
Standardwert `"Hello"` zum Zuge kommt.

> Info: Die neue Seite teilt sich den Header und Footerbereich mit den anderen Seite weil die [[yii\web\Controller::render()|render()]]
  Methode das Resultat der `say`-View automatisch in ein sogenanntes [Layout](structure-views.md#layouts) einbettet. Diese
  ist in diesem Fall zu finden unter `views/layouts/main.php`.
  
Der `r`-Parameter in der obigen URL steht für "[route](runtime-routing.md)", eine applikationsweit eindeutige ID welche
auf eine Aktion verweist. Das Format dieser "route" enspricht folgender Konvention: `<ControllerID>/<ActionID>`. Sobald
die Applikation einen Request erhält prüft sie diesen Parameter und instantiert den zuständigen Controller anhand der 
`ControllerID`. Danach verwender der Controller die `ActionID` um die Aktion zu instantiieren welche die Arbeit übernimmt.
In diesem Beispiel, die Route `site/say` wird aufgelöst zur `SiteController` Controller-Klasse und der `say` Aktion. Als
Resultat wird die `SiteController::actionSay()`-Methode aufgerufen um den Request zu bewältigen.

> Info: Wie auch Aktionen haben Controller eindeutige IDs welche sie eindeutig innerhalb der Applikation identifizieren.
  Controller IDs entsprechen denselben Konventionen wie Aktions IDs. Controller Klassennamen werden gebildert aus den
  Controller IDs indem Dashes entfernt werden, die jeweils ersten Buchstaben der Wörter grossgeschrieben werden und
  das Resultat mit dem Begriff `Controller` ergänzt wird. Zum Beispiel: Die Controller ID `post-comment` entspricht dem
  Controllerklassennamen `PostCommentController`.


Zusammenfassung <span id="summary"></span>
---------------

In diesem Kapitel sahen sie den Controller sowie View Teil innerhalb der MVC-Architektur.
Sie haben eine Aktion als Teil eines Controllers erstellt, welche ein bestimmtes Request behandelt. Sie haben ebenso eine
View erstellt welche die Antwort definiert. In diesem einfachen Beispiel war kein Model involviert und die einzigen Daten
die verwendet wurden war der `message`-Parameter

Sie haben ebenso gesehen wie "Routes" in Yii funktionieren, welche die Brücke zwischen Request und Controller-Aktionen bilden.

Im nächsten Kapitel werden Sie lernen, wie man ein Model und eine neue Seite erstellt welche ein HTML Formular beinhaltet.
