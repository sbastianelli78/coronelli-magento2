# Coronelli Magento 2 deployment baseline

This repository branch captures the code currently running in `/cicd/m2`.
It deliberately excludes credentials, Magento runtime directories, generated
code, database dumps, order exports, and editor/backup files.

The `update` directory and `vendor/mageplaza/magento-2-italian-language-pack`
were nested repositories without a usable `.gitmodules` mapping. Their
deployed contents are therefore versioned as normal directories in this
baseline, so a fresh clone contains all required source files.

## Platform constraints

- Application root: `/cicd/m2`
- Magento: 2.1.7
- PHP CLI: 5.6.40
- Composer: 1.10.x

The deployment account cannot upgrade PHP, Magento, or system packages. Treat
runtime upgrades as a separate infrastructure project.

## Before a deployment

1. Confirm the working tree is clean and record the commit SHA.
2. Synchronize only versioned code and explicitly preserve `app/etc/env.php`,
   `var/`, and runtime media.
3. Build in a sibling release directory with independent `var/` and
   `pub/static/`; never compile against the active static directory.
4. Check the generated CSS, RequireJS, Knockout templates, and asset URLs.
5. Stage the complete theme directory next to the active release.
6. Keep the previous active theme directory as a rollback copy.

## Static content

The command verified for the Coronelli theme is:

```bash
php bin/magento setup:static-content:deploy \
  --theme Hoop/coronelli \
  --language it_IT \
  --area frontend \
  --jobs=1 \
  --no-interaction
```

Run it only in the isolated release directory. A valid result currently ends
with zero errors and generates the theme CSS, `requirejs/require.js`, and the
checkout templates under:

```text
pub/static/frontend/Hoop/coronelli/it_IT/
```

Copy the complete generated theme to a staging directory on the same
filesystem as the live theme. Verify hashes for `styles-m.css`, `styles-l.css`,
and `requirejs/require.js`, then activate it by renaming directories. Update
`pub/static/deployed_version.txt` only after the complete release is staged.
Finally run:

```bash
php bin/magento cache:clean layout block_html full_page
```

Do not patch files directly in `pub/static`: production changes must originate
from the theme source and pass through the isolated build.

## Smoke test

Verify HTTP responses for the homepage, cart, one product page, both main CSS
files, RequireJS, and any changed Knockout template. Confirm that the HTML uses
the new static version before considering the release complete.

## Rollback

Rename the saved theme directory back into place, restore its matching
`deployed_version.txt`, clean `layout`, `block_html`, and `full_page`, then
repeat the smoke test. Restore the prior Git commit if source code was also
activated.

The server cannot currently authenticate directly to GitHub. Transfer an
incremental Git bundle over SSH and fast-forward the checked-out branch; never
replace tracked files manually.
