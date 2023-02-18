const fs = require('node:fs');
const path = require('node:path');
const glob = require('glob');

const pack = require('../package.json');

const licenseFront = `/*!
 * Stancer PrestaShop v${pack.version}
 * (c) 2023 Iliad 78
 * Released under the MIT License.
 */
`;
const licensePhp = `<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023 Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   ${pack.version}
 */`;
const licenseSmarty = `{*
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023 Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   ${pack.version}
 *}`;

glob(
  '**/*.{css,js,php,tpl}',
  {
    ignore: [
      'node_modules/**',
      'scripts/**',
      'vendor/**',
      'views/scripts/**',
    ],
  },
  (err, files) => {
    if (err) {
      throw err;
    }

    files.forEach((file) => {
      const filepath = path.join(process.cwd(), file);

      fs.readFile(filepath, { encoding: 'utf-8' }, (err, data) => {
        if (err) {
          throw err;
        }

        if ((file.endsWith('.css') || file.endsWith('.js')) && !data.startsWith('/*!')) {
          data = licenseFront + data;
        }

        if (file.endsWith('.php')) {
          data = data.replace('<?php\n\n', licensePhp + '\n\n');
        }

        if (file.endsWith('.tpl') && !data.startsWith('{*')) {
          data = licenseSmarty + '\n\n' + data;
        }

        fs.writeFile(filepath, data, (err) => {
          if (err) {
            throw err;
          }
        });
      });
    });
  },
);
