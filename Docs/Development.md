# Jak si připravit vývojové prostředí

## Požadavky

* naklonuj [repozitář](https://github.com/arnost00/members) do pracovního adresáře
* ve Windows se může hodit [Git for Windows](https://git-scm.com/downloads/win)
* nainstaluj Docker s podporou `docker compose`, například [Docker Desktop](https://www.docker.com/products/docker-desktop/)

## Plné vývojové prostředí

* v kořenovém adresáři `members` spusť:

```bash
docker compose -p members-dev -f docker-compose.dev.yml up --build
```

* do Visual Studio Code přidej [Remote Explorer extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode.remote-explorer)
* připoj Visual Studio Code ke kontejneru `members-dev-web-1`
* otevři adresář `/var/www/html/members`

Prostředí obsahuje Git, Playwright a další nástroje.

Konfigurační soubory, které aplikace v kontejneru používá, jsou publikované v gitu a připojené z `docker/config/dev` do `cfg` jako read-only. Upravuj proto soubory v `docker/config/dev`, ne jejich cíle v `cfg`.

Dostupné služby:

* [members](http://127.0.0.1:10100/members)
* [phpMyAdmin](http://127.0.0.1:10101)

## Automatické testy

V kořenovém adresáři `members` spusť:

```bash
docker compose -p members-autotest -f docker-compose.autotest.yml up -d --build
docker compose -p members-autotest -f docker-compose.autotest.yml exec web npm run test:e2e
docker compose -p members-autotest -f docker-compose.autotest.yml down
```

## Minimální konfigurace

Vhodná pro manuální testování a změny nastavení.

* zkopíruj `members/cfg/_cfg.php.default` do `members/cfg/_cfg.php` a uprav jej, nebo použij připravený [`_cfg.php`](X_cfg.php)
* zkopíruj `members/cfg/_globals.php.default` do `members/cfg/_globals.php` a uprav jej, nebo použij připravený [`_globals.php`](X_globals.php)
* zkopíruj `members/cfg/_tables.php.default` do `members/cfg/_tables.php` a uprav jej, nebo použij připravený [`_tables.php`](X_tables.php)

Výsledná struktura:

```text
members
├── cfg
│   ├── _cfg.php
│   ├── _globals.php
│   └── _tables.php
├── docker
│   └── db_init
└── ...
```

Spuštění:

```bash
docker compose up --build
```

Přihlášení do aplikace:

* [members](http://127.0.0.1/members/), heslo `54321`
* `admin` - administrátor, aktualizace databáze, opravy
* `tnov_1` - přihlašovatel
* `tnov_2` - trenér
* `tnov_3` - oddílový administrátor
* `tnov_4` - malý trenér
* `tnov_5` - člen
* `tnov_6` - finančník

Databáze:

* [phpMyAdmin](http://127.0.0.1:8080)
* uživatel `root`, heslo `dev4password`

Práce s kontejnery:

* kód můžeš měnit z hosta i z kontejneru, logy jsou v `members/logs`
* CLI přístup do PHP kontejneru:

```bash
docker compose exec web bash
```

* CLI přístup do databáze:

```bash
docker compose exec db mariadb -u root -p
```

Přerušení a obnovení práce:

```bash
docker compose stop
docker compose start
```

Ukončení a smazání kontejnerů:

```bash
docker compose down
```

Po změně Docker image nebo závislostí:

```bash
docker compose up --build
```

## Známé problémy

Nevytvoří se adresář `logs`:

```bash
docker compose exec web mkdir -p /var/www/html/members/logs
docker compose exec web chown -R www-data:www-data /var/www/html/members/logs
```

## Jak dodat změny

* změny posílej přes pull request do [arnost00/members](https://github.com/arnost00/members) z vlastního forku
* vytvoř branch a průběžně commituj
* průběžně automaticky testuj
* ptej se, navrhuj řešení a odkazuj na konkrétní commit
* změny otestuj a proklikej GUI s různými uživateli
* pošli pull request
* obvyklý průběh je: diskuze, nasazení na test, testování, připomínky, opravy a nasazení do produkce
