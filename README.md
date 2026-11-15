# LogViewer per Laravel 13

Fork di [ARCANEDEV/LogViewer](https://github.com/ARCANEDEV/LogViewer), basato sulla release `11.0.1` (`806498da9792eb34e39df9350a7379a9018eaf5d`). Nome Composer: `andicot/log-viewer`.

Richiede PHP 8.3+ e Laravel 13. Mantiene l'interfaccia originale, i temi Bootstrap 3/4/5, i contatori cliccabili per livello nell'elenco, ricerca, stack trace, contesto, download e cancellazione.

È stato corretto anche il pulsante del menu mobile Bootstrap 5, senza variazioni grafiche.

La dipendenza `arcanedev/support` è stata eliminata: provider, binding, rotte e comandi utilizzano le API native Laravel. Namespace `Arcanedev\LogViewer`, helper, configurazione, nomi delle rotte e tag di pubblicazione sono conservati. Il pacchetto originale non può essere installato contemporaneamente, perché espone le stesse classi e funzioni.

## Installazione

Il repository pubblico è [Andicot/log-viewer](https://github.com/Andicot/log-viewer).

Il pacchetto è disponibile su [Packagist](https://packagist.org/packages/andicot/log-viewer). Nell’app Laravel eseguire:

```sh
composer require andicot/log-viewer:^13.0
```

Se presente, rimuovere prima `arcanedev/log-viewer` dai requisiti dell'app. Per eseguire la sostituzione in un unico aggiornamento delle dipendenze:

```sh
composer remove arcanedev/log-viewer --no-update
composer require andicot/log-viewer:^13.0 -W
```

Pubblicare la configurazione solo se non è già presente:

```sh
php artisan log-viewer:publish --tag=config
```

I provider vengono scoperti automaticamente. Configurazioni e viste già pubblicate continuano a funzionare; non utilizzare `--force` se si vogliono mantenere le proprie personalizzazioni.

## Configurazione dell'app

```dotenv
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DAILY_DAYS=14
ARCANEDEV_LOGVIEWER_MIDDLEWARE=web,auth
```

Il visualizzatore legge i file giornalieri locali. L'app deve applicare anche il proprio middleware di autorizzazione amministrativa. Come nel pacchetto originale, senza configurazione non viene aggiunta automaticamente una protezione alle rotte.

Per il tema predefinito Bootstrap 5, nel `boot()` dell'`AppServiceProvider` dell'app configurare la paginazione, come richiesto anche dal pacchetto originale:

```php
\Illuminate\Pagination\Paginator::useBootstrapFive();
```

Questa impostazione vale per l'app intera: in app con altre interfacce di paginazione personalizzare le viste pubblicate del visualizzatore invece di cambiare il default globale.

Aprire `/log-viewer`. Dall'elenco `/log-viewer/logs`, ogni contatore diverso da zero apre direttamente il livello corrispondente.

## Test e verifica

```sh
composer install
composer test
```

Il [workflow CI](tools/ci/run-tests.yml) configura PHP 8.3/8.4/8.5 e dipendenze minime/correnti. Per attivarlo, spostarlo in `.github/workflows/run-tests.yml` usando credenziali GitHub con permesso `workflow`. I test usano Testbench 11 e PHPUnit 12; le annotazioni della suite originale sono state convertite in attributi.

- [Installazione dettagliata e app di verifica](_docs/1.Installation-and-Setup.md)
- [Configurazione originale](_docs/2.Configuration.md)
- [API e utilizzo originale](_docs/3.Usage.md)
- [Risultati e limiti della verifica Laravel 13](docs/verifiche/2026-11-15-laravel13.md)

Il parser originale carica i file in memoria: questa versione non introduce la lettura in streaming. Il comando `log-viewer:check` riconosce il driver `daily` sia direttamente sia all’interno di stack, anche annidati e con nomi personalizzati. Gli stack senza canali giornalieri continuano a segnalare requisiti non soddisfatti.

## Licenza e attribuzione

Codice originale di ARCANEDEV e relativi contributori. Licenza [MIT](LICENSE.md), copyright e attribuzioni originali conservati. Questo fork non è una release ufficiale ARCANEDEV.
