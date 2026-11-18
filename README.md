# LogViewer per Laravel 12 e 13

Fork di [ARCANEDEV/LogViewer](https://github.com/ARCANEDEV/LogViewer), basato sulla release `11.0.1` (`806498da9792eb34e39df9350a7379a9018eaf5d`). Nome Composer: `andicot/log-viewer`.

Richiede PHP 8.3+ e Laravel 12 o 13. Mantiene l'interfaccia originale, i temi Bootstrap 3/4/5, i contatori cliccabili per livello nell'elenco, ricerca, stack trace, contesto, download e cancellazione.

È stato corretto anche il pulsante del menu mobile Bootstrap 5, senza variazioni grafiche.

La dipendenza `arcanedev/support` è stata eliminata: provider, binding, rotte e comandi utilizzano le API native Laravel. Namespace `Arcanedev\LogViewer`, helper, configurazione, nomi delle rotte e tag di pubblicazione sono conservati. Il pacchetto originale non può essere installato contemporaneamente, perché espone le stesse classi e funzioni.

## Novità della 13.1

- Cache dei contatori di elenco e dashboard: memorizza solo conteggi e impronta del contenuto, mai messaggi o stack trace. È attiva per impostazione predefinita sullo store Laravel `file`; si configura con `log-viewer.stats-cache` (`enabled`, `store`, `ttl` in secondi).
- Modalità sola lettura: impostare `LOG_VIEWER_READ_ONLY=true`. Nasconde le azioni di cancellazione e le blocca lato HTTP, API PHP standard e comando `log-viewer:clear`. Consultazione, ricerca, copia e download rimangono disponibili. Non sostituisce l’autenticazione e l’autorizzazione dell’app.
- Pulsante **Copia dettagli** in tutti i temi: copia data, livello, ambiente, messaggio, stack e contesto come testo. Richiede il supporto agli appunti del browser (normalmente HTTPS o localhost); se negato, mostra il testo selezionabile e un messaggio esplicito.
- CSS, JavaScript, icone e font locali, inclusi nel pacchetto e serviti sotto lo stesso prefisso/middleware del viewer. Non occorre pubblicare asset o eseguire npm. Versioni dei temi conservate; [origine e licenze delle risorse](assets/README.md).

La cache controlla il contenuto con SHA-256 per riconoscere anche riscritture della stessa dimensione e nello stesso secondo. Evita il parsing ripetuto, ma deve comunque leggere il file per calcolarne l’impronta. La prima lettura e il dettaglio degli eventi continuano a utilizzare il parser in memoria.

### Aggiornamento dalla 13.0

```sh
composer require andicot/log-viewer:^13.1
php artisan optimize:clear
```

Ricreare poi le cache normalmente utilizzate dall’app. Le nuove opzioni sono unite automaticamente alla configurazione già pubblicata; non è necessario sovrascriverla. Se le viste sono state pubblicate, confrontarle e integrare le modifiche di `_master`, `logs`, `show` e il nuovo partial `partials/copy`. Le vecchie viste continuano a funzionare, ma non acquisiscono automaticamente copia, risorse locali e pulsanti nascosti; il blocco server della sola lettura resta efficace. Lo stesso vale per traduzioni JSON pubblicate: integrare le nuove chiavi italiane. Utilizzare `log-viewer:publish --tag=views --force` solo se si intende sostituire tutte le proprie viste personalizzate.

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
node --test tests/js/copy.test.cjs
```

Il [workflow CI](.github/workflows/run-tests.yml) configura Laravel 12/13, PHP 8.3/8.4/8.5 e dipendenze minime/correnti. Viene eseguito automaticamente su push e pull request. I test usano Testbench 10 con Laravel 12 e Testbench 11 con Laravel 13, insieme a PHPUnit 12; le annotazioni della suite originale sono state convertite in attributi.

- [Installazione dettagliata e app di verifica](_docs/1.Installation-and-Setup.md)
- [Configurazione originale](_docs/2.Configuration.md)
- [API e utilizzo originale](_docs/3.Usage.md)
- [Risultati e limiti della verifica Laravel 13](docs/verifiche/2026-11-15-laravel13.md)

Il parser originale carica i file in memoria: questa versione non introduce la lettura in streaming. Il comando `log-viewer:check` riconosce il driver `daily` sia direttamente sia all’interno di stack, anche annidati e con nomi personalizzati. Gli stack senza canali giornalieri continuano a segnalare requisiti non soddisfatti.

## Licenza e attribuzione

Codice originale di ARCANEDEV e relativi contributori. Licenza [MIT](LICENSE.md), copyright e attribuzioni originali conservati. Questo fork non è una release ufficiale ARCANEDEV.
