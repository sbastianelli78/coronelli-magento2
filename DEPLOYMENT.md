# Coronelli Magento 2 deployment baseline

This repository branch captures the code currently running in `/cicd/m2`.
It deliberately excludes credentials, Magento runtime directories, generated
code, database dumps, order exports, and editor/backup files.

The `update` directory and `vendor/mageplaza/magento-2-italian-language-pack`
were nested repositories without a usable `.gitmodules` mapping. Their
deployed contents are therefore versioned as normal directories in this
baseline, so a fresh clone contains all required source files.

## Before a deployment

1. Confirm the working tree is clean and record the commit SHA.
2. Synchronize only versioned code and explicitly preserve `app/etc/env.php`,
   `var/`, and runtime media.
3. Create a recoverable copy of `pub/static` and `var/generation` on the
   target or keep an equivalent verified release copy.
4. Run `php bin/magento setup:di:compile` from `/cicd/m2`.
5. Check `php bin/magento cache:status` and the public homepage.

## Static content

Do not run static-content deployment blindly on the live build. It overwrites
the active CSS and JavaScript. Run it only after a visual check in a controlled
window and with the preceding static files available for rollback.

## Rollback

Restore the prior release code plus its matching `pub/static` and
`var/generation` artifacts, then validate the homepage and Magento CLI.
