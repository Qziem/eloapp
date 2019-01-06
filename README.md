# Eloapp

Apka służy do wyznaczania rankinków graczy w dowolnej grze. W grze rywalizować ze sobą mogą dwie osoby, a możliwy wynik to wygrana - przegrana.
Algorytm obliczający punkty w rankingu jest oparty na systemie Elo:
https://en.wikipedia.org/wiki/Elo_rating_system  
https://www.geeksforgeeks.org/elo-rating-algorithm  
https://metinmediamath.wordpress.com/2013/11/27/how-to-calculate-the-elo-rating-including-example  

## Opis jak skonfigurować środowisko lokalnie
Aplikacja jest dostosowana aby działać na dockerze.

### KONFIGURACJA BE:
1. Instalujemy dockera i docker-compose
2. Kopiujemy plik config.php.distr i zmieniamy nazwę na config.php
3. Kopiujemy plik .env.distr i zmieniamy nazwę na .env - edytujemy go i w SOURCE_DIR wpisujemy ścieżkę do apki
4. Odpalamy dockera z głównego katalogu apki, komendą: sudo docker/start.sh
5. Ściągamy zależności composerem, odpalamy: sudo bin/composer.sh install

### KONFIGURACJA FE:
1. Najpierw trzeba zainstalować node: https://nodejs.org
2. Opcjonalnie instalujemy yarna: https://yarnpkg.com
Ja korzystam z yarna ale jak ktoś chce można korzystać z npm - wtedy we wszystkich poniższych komendach zamiast słówka yarn używajcie npm
3. W konsoli wchodzimy do katalogu fe i tam: yarn install
4. Następnie odpalamy: yarn start - to odpali kompilację reasona do javascript
5. Otwieramy drugie okno konsoli i tam: yarn webpack - to odpali właściwe budowanie wynikowego pliku do katalogu build.

Zarówno yarn start jak i yarn webpack działają w trybie watch, więc modyfikując coś w kodzie wszystko odrazu samo będzie się przebudowywać (niestety nie ma hmr więc w przeglądarce trzeba odświeżać ręcznie)

Odpalamy przeglądarkę i w adres wpisujemy: http://localhost:8000
Jeśli wszystko jest poprawnie skonfigurowane to powinna pojawić się aplikacja - jeśli nie i coś spierdzieliłem w powyższym opisie to dawajta znać.

## TODO list
Dobrze by było, że jeśli znadzie się jakiś chętny do zrobinia, to aby skonsultował to z innymi zainteresowanymi, po to aby po pierwsze być może zgroomować rozwiązanie, po drugie aby uniknąć konfiliktów gdy ktoś będzie robił jakąś inną funkcjonalność, po trzecie aby nagle się nie okazało że dwie osoby robią równolegle to samo.

### Zagadnienia czysto techniczne
- [FE BE] - Otestować

### Zagadnienia funkcjonalne
- Dorobić możliwość logowania się każdego użytkownia, aby każdy mógł mieć swoje hasło
- Gdy powyższe już będzie, można w jakiś sposób blokować, aby nie można było wpisać wyniku bez swojego udziału i być może jakieś inne restrykcje/możliwości (np. może kasowanie gry, jeśli ktoś by się pomylił ??)
- [Do uzgodnienia] Pokazywanie par które najmniej z sobą grały aby można było parować userów którzy powinni z sobą zagrać
- [Do uzgodnienia] Możliwość wpisania potencjalnego wyniku gry aby sprawdzić jak zmieniłby się ranking

Qziem
