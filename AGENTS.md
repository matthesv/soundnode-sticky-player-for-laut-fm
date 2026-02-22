diff --git a/AGENTS.md b/AGENTS.md
index e69de29bb2d1d6434b8b29ae775ad8c2e48c5391..ec86438b02a46c25eecc6fd5142997d9ee8052c4 100644
--- a/AGENTS.md
+++ b/AGENTS.md
@@ -0,0 +1,14 @@
+# AGENTS Hinweise (Repository: /workspace/laut-fm-sticky-player)
+
+Diese Datei ist die zentrale, vorhersehbare Stelle für Agent-Anweisungen in diesem Repository.
+
+## Arbeitsregeln
+- Bei jeder Änderung die Version gemäß SemVer erhöhen (mindestens PATCH bei kleinen Änderungen).
+- Immer beide Versionen aktualisieren:
+  - Plugin-Header `Version:` in `laut-fm-sticky-player.php`
+  - Konstante `LFSP_VERSION` in `laut-fm-sticky-player.php`
+- Änderungen nachvollziehbar dokumentieren:
+  - `readme.txt` im Abschnitt `== Changelog ==` und ggf. `== Upgrade Notice ==`
+  - `README.md` im Changelog/Release-Hinweis
+- Plugin-Name konsistent halten: **SoundNode Sticky Player for laut.fm**.
+- Antworten an den Nutzer auf Deutsch formulieren.
