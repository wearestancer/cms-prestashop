const fs = require('node:fs');
const path = require('node:path');
const { globSync } = require('glob');

const pack = require('../package.json');
const currentYear = new Date().getFullYear();

const regexBack = new RegExp('@copyright (\\d{4})(?:-\\d{4})? Stancer / Iliad 78', 'gsm');
const regexFront = new RegExp('\\(c\\) (\\d{4})(?:-\\d{4})? Stancer / Iliad 78', 'gsm');
const yearBack = `@copyright $1-${currentYear} Stancer / Iliad 78`;
const yearFront = `(c) $1-${currentYear} Stancer / Iliad 78`;
const licenseFront = `/*!
 * Stancer PrestaShop v${pack.version}
 * ${yearFront.replace('$1-', '')}
 * Released under the MIT License.
 */
`;
const licensePhp = `<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * ${yearBack.replace('$1-', '')}
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   ${pack.version}
 */`;
const licenseSmarty = `{*
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * ${yearBack.replace('$1-', '')}
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   ${pack.version}
 *}`;

const addYear = (mode, text) => {
  let regex = regexBack;
  let base = yearBack;

  if (mode === 'front') {
    regex = regexFront;
    base = yearFront;
  }

  return text.replace(regex, (match, year) => {
    if (year == currentYear) {
      return match;
    }

    return base.replace('$1', year);
  });
};

const processFile = (file) => {
  const filepath = path.join(process.cwd(), file);

  fs.readFile(filepath, { encoding: 'utf-8' }, (err, data) => {
    if (err) {
      throw err;
    }

    if (file.endsWith('.css') || file.endsWith('.js')) {
      if (!data.startsWith('/*!')) {
        data = licenseFront + data;
      } else {
        data = addYear('front', data);
      }
    }

    if (file.endsWith('.php')) {
      data = addYear('back', data.replace('<?php\n\n', licensePhp + '\n\n'));
    }

    if (file.endsWith('.tpl')) {
      if (!data.startsWith('{*')) {
        data = licenseSmarty + '\n\n' + data;
      } else {
        data = addYear('back', data);
      }
    }

    if (file === 'LICENSE') {
      data = addYear('front', data);
    }

    fs.writeFile(filepath, data, (err) => {
      if (err) {
        throw err;
      }
    });
  });
};

processFile('LICENSE');

globSync(
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

    files.forEach(processFile);
  },
);
