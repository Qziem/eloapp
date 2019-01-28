# Eloapp

Apka służy do wyznaczania rankinków graczy w dowolnej grze. W grze rywalizować ze sobą mogą dwie osoby, a możliwy wynik to wygrana - przegrana.
Algorytm obliczający punkty w rankingu jest oparty na systemie Elo:
https://en.wikipedia.org/wiki/Elo_rating_system  
https://www.geeksforgeeks.org/elo-rating-algorithm  
https://metinmediamath.wordpress.com/2013/11/27/how-to-calculate-the-elo-rating-including-example  

## Opis jak skonfigurować środowisko lokalnie
Aplikacja jest dostosowana aby działać na dockerze. Docker jest dostosowany do działania i przetestowany na linuxie. Na windowsie mogą występować problemy ale nikt nie broni próbować :)

### KONFIGURACJA BE:
1. Instalujemy dockera i docker-compose
2. Konfigurujemy dockera aby można aby można było odpalać bez sudo: https://docs.docker.com/install/linux/linux-postinstall
2. Kopiujemy plik config.php.distr i zmieniamy nazwę na config.php
3. Kopiujemy plik .env.distr i zmieniamy nazwę na .env - edytujemy go i w SOURCE_DIR wpisujemy ścieżkę do apki
4. Odpalamy dockera z głównego katalogu apki, komendą: docker/start.sh
5. Ściągamy zależności composerem, odpalamy: bin/composer.sh install

### KONFIGURACJA FE:
1. Trzeba najpierw odpalić docker/start.sh (jeśli został odpalony w punkcie 4 przy konfiguracji BE to pomijamy ten punkt)
3. W konsoli wchodzimy do katalogu fe i tam: bin/yarn.sh install
4. Następnie odpalamy: bin/yarn.sh start - to odpali kompilację reasona do javascript
5. Otwieramy drugie okno konsoli i tam: bin/yarn.sh webpack - to odpali właściwe budowanie wynikowego pliku do katalogu build.

Zarówno yarn start jak i yarn webpack działają w trybie watch, więc modyfikując coś w kodzie wszystko odrazu samo będzie się przebudowywać (niestety nie ma hmr więc w przeglądarce trzeba odświeżać ręcznie)

Odpalamy przeglądarkę i w adres wpisujemy: http://localhost:8000
Jeśli wszystko jest poprawnie skonfigurowane to powinna pojawić się aplikacja - jeśli nie i coś spierdzieliłem w powyższym opisie to dawajta znać.

Qziem
