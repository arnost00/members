# Jak si postavit vlastní prostředí:

* naklonuj [repo](https://github.com/arnost00/members) do pracovního adresáře ( můžeš potřebovat [git windows klient](https://git-scm.com/downloads/win) )
* nainstaluj docker composer - např. [Docker Desktop](https://www.docker.com/products/docker-desktop/)
* do pracovního adresáře rozpakuj soubory pro docker [members_development_files.zip](https://drive.google.com/file/d/1c_JVYLujrJ-RBVMeQIZFR8fcdf34aC0l/view?usp=drive_link)

* konfigurace
  * zkopíruj soubor members/cfg/_cfg.php.default do members/cfg/_cfg.php a uprav nebo použij připravený [_cfg.php](https://drive.google.com/file/d/11nkISJdLrl9_t4FozFOgOiijqJBmhAIY/view?usp=drive_link)

  * zkopíruj soubor members/cfg/_globals.php.default do members/cfg/_globals.php a uprav nebo použij připravený [_globals.php](https://drive.google.com/file/d/1ZGUzue-S6uKR9Tv25kjuwLfDZNUmpyB2/view?usp=drive_link)

  * zkopíruj soubor members/cfg/_tables.php.default do members/cfg/_tables.php a uprav nebo použij připravený [_tables.php](https://drive.google.com/file/d/1QJemFI7jXyCR11kXv-wBJOEq93lECrf3/view?usp=drive_link)

  Výsledek:
  ```
  ── docker-compose.yml
    ├── db_init
    │   └── d235220_members.sql
    ├── members
    │   ...
    │   ├── cfg
    │       ...
    │       ├── _cfg.php
    │       ... 
    │   ...
    └── web
         └── DockerFile
  ```
* spusť docker compose - z VS Code, docker desktopu, příkazové řádky, …

  `docker compose -f docker-compose.yml up`
* přihlaš se do [members](http://127.0.0.1/members/) heslo 54321
  * admin - administrátor - aktualizace databáze, opravy
  * tnov_1 - přihlašovatel
  * tnov_2 - trenér
  * tnov_3 - oddílový administrátor
  * tnov_4 - malý trenér
  * tnov_5 - člen
  * tnov_6 - finančník
* databáze je přístupná v [phpMyAdmin](http://127.0.0.1:8080/index.php?route=/database/structure&db=d235220_members)
  * uživatel root, heslo dev4password
* kód můžeš měnit z hosta i z kontejneru, logy jsou v members/logs
  * cli přístup do php servru
    `docker exec -it zbm-web-1 bash`
  * cli přístup do mysql
    `docker exec -it zbm-db-1 mysql -u root -p`


## Známé problémy
* nevytvoří se adresář logs
  `docker exec -it zbm-web-1 mkdir -p /var/www/html/members/logs`
  `docker exec -it zbm-web-1 chown -R www-data:www-data /var/www/html/members/logs`

## Jak dodat změny:

* změny jdou přes pull request na [arnost00/members](https://github.com/arnost00/members) z nějakého repository forku
* udělej branch, commituj změny, ptej se a navrhuj s odkazem na commit
* otestuj, proklikej gui s různými uživateli
* udělej pull request
* proběhne 
  * diskuze
  * nasazení na test
  * testování
  * pojeb
  * opravy
  * nasazení na produkci
