# Bundled frontend resources

The three original themes use local copies of their existing dependencies. No npm build or asset publishing is required. Resources are served by the named `log-viewer::assets` route, under the viewer's configured prefix and middleware. Only files in `manifest.json` can be served; source URLs and license files are not exposed by that route.

`sources.json` maps upstream URLs to the vendored filenames; `manifest.json` records SHA-256 hashes of the distributed bytes. CSS font references are rewritten to local relative URLs and source-map directives removed. No fonts or runtime code have been redesigned. The Google Fonts stylesheet is a static snapshot for Montserrat 400/700 and Source Sans Pro 400/600.

| Component | Version | License |
| --- | --- | --- |
| Bootstrap (including bundled Glyphicons in v3) | 3.3.5, 4.0.0, 5.3.2 | MIT |
| Font Awesome | 4.4.0 | CSS: MIT; fonts: SIL OFL 1.1 |
| Chart.js | 2.3.0, 2.7.1, 4.4.0 | MIT |
| jQuery | 1.11.3, 3.2.1 | MIT |
| Popper.js | 1.12.9 | MIT |
| html5shiv | 3.7.2 | MIT (dual MIT/GPL distribution) |
| Respond.js | 1.4.2 | MIT |
| Montserrat / Source Sans Pro | Google Fonts snapshot | SIL OFL 1.1 |

Copyright notices and license texts are in `licenses/`; upstream notices are also retained in the vendored files. The compatibility themes retain their historical dependency versions; bundling them does not imply that these old versions have been upgraded or independently security-audited.
