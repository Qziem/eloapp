# Eloapp

Apka służy do wyznaczania rankinków graczy w dowolnej grze. W grze rywalizować ze sobą mogą dwie osoby, a możliwy wynik to wygrana - przegrana.
Algorytm obliczający punkty w rankingu jest oparty na systemie Elo:
https://metinmediamath.wordpress.com/2013/11/27/how-to-calculate-the-elo-rating-including-example/
https://en.wikipedia.org/wiki/Elo_rating_system

## Opis jak skonfigurować środowisko lokalnie
Przede wszystkim potrzebujemy serwerka z obsługą php i bazę danych mysql.
Ja skorzystałem z xampa (działa pod windows, dla linuxa jest coś podobnego: lamp) który ma już to wszystko (apache + php + mysql).
Oczywiście jak ktoś chce może sobie skomponować dowolny serwer http z php + mysql

W tym pliku opiszę jak ja to zrobiłem u siebie na windowsie i na xampie:
1. Instalujemy xampa: https://www.apachefriends.org/ (domyślnie instaluje się w katalogu c:/xampp)
2. Wchdzimy do c:/xampp/htdocs, tam tworzymy sobie katalog, np. eloapp i tu klonujemy repo

### KONFIGURACJA BE:
1. Otwieramy control panel xampa i startujemy apache i mysql
2. W przeglądarce wchodząc na: localhost powinna pojawić się strona, z menu klikamy w link do phpmyadmin
3. Najpierw tworzymy swoją bazę danych i dodajemy jakiegoś user i nadajemy mu hasło i nadajmy mu oczywiście dostęp do nowo stworzonej bazy
4. Importujemy plik: be/db_schema.sql do nowo stworzonej bazki
5. Kopiujemy plik: be/config.php.schema i wklejamy w to samo miejsce ale z nazwą config.php
6. Uzupełniamy w pliku config.php dane bazy: dbname, dbuser, dbpass
7. Instalujemy composera: https://getcomposer.org/
8. W konsoli wchodzimy do kagalogu be i tam: composer install

### KONFIGURACJA FE:
1. Najpierw trzeba zainstalować node: https://nodejs.org
2. Opcjonalnie instalujemy yarna: https://yarnpkg.com
Ja korzystam z yarna ale jak ktoś chce można korzystać z npm - wtedy we wszystkich poniższych komendach zamiast słówka yarn używajcie npm
3. W konsoli wchodzimy do katalogu fe i tam: yarn install
4. Następnie odpalamy: yarn start - to odpali kompilację reasona do javascript
5. Otwieramy drugie okno konsoli i tam: yarn webpack - to odpali właściwe budowanie wynikowego pliku do katalogu build.

Zarówno yarn start jak i yarn webpack działają w trybie watch, więc modyfikując coś w kodzie wszystko odrazu samo będzie się przebudowywać (niestety nie ma hmr więc w przeglądarce trzeba odświeżać ręcznie)

Odpalamy przeglądarkę i w adres wpisujemy: http://localhost/eloapp (eloapp to nazwa katalogu w c:/xampp/htdocs)
Jeśli wszystko jest poprawnie skonfigurowane to powinna pojawić się aplikacja - jeśli nie i coś spierdzieliłem w powyższym opisie to dawajta znać.

## TODO list
Dobrze by było, że jeśli znadzie się jakiś chętny do zrobinia, to aby skonsultował to z innymi zainteresowanymi, po to aby po pierwsze być może zgroomować rozwiązanie, po drugie aby uniknąć konfiliktów gdy ktoś będzie robił jakąś inną funkcjonalność, po trzecie aby nagle się nie okazało że dwie osoby robią równolegle to samo.

### Zagadnienia czysto techniczne
- [FE] W reasonie stworzyć odpowiednie, bardziej szczegółowe typy danych (kod, nazwa, itp.)
- [FE] Stan aplikacji jest obecnie rozproszony na komponenty, być może trzebaby przerobić aby był jeden komponent który byłby kontenerem stanu, a pozostałe tylko wysyłały do niego akcjie - coś na kształt reduxa
- [BE] Użyć doctrine [KUZ]
- [FE BE] - Otestować

### Zagadnienia funkcjonalne
- Dorobić aby wynik rozgrywki był zapisywany w bazie (czyli kto z kim grał i jaki był wynik) - to umożliwiłoby pokazywanie bardziej szczegółowych statystyk danego gracza (przedewszystkim można by pokazywać oponenta)
- Dorobić możliwość logowania się każdego użytkownia, aby każdy mógł mieć swoje hasło
- Gdy powyższe już będzie, można w jakiś sposób blokować, aby nie można było wpisać wyniku bez swojego udziału i być może jakieś inne restrykcje/możliwości (np. może kasowanie gry, jeśli ktoś by się pomylił ??)
- [Do uzgodnienia] Pokazywanie par które najmniej z sobą grały aby można było parować userów którzy powinni z sobą zagrać
- [Do uzgodnienia] Możliwość wpisania potencjalnego wyniku gry aby sprawdzić jak zmieniłby się ranking
- Jeśli dojdzie sporo sekcji być może warto będzie zrobić jakieś menu - wtedy pewnie też trzebaby skorzystać z react routera.


Qziem
