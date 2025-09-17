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
  let content = fs.readFileSync(filepath, { encoding: 'utf-8' });

  if (file.endsWith('.css') || file.endsWith('.js')) {
    if (!content.startsWith('/*!')) {
      content = licenseFront + content;
    } else {
      content = addYear('front', content);
    }
  }

  if (file.endsWith('.php')) {
    content = addYear('back', content.replace('<?php\n\n', licensePhp + '\n\n'));
  }

  if (file.endsWith('.tpl')) {
    if (!content.startsWith('{*')) {
      content = licenseSmarty + '\n\n' + content;
    } else {
      content = addYear('back', content);
    }
  }

  if (file === 'LICENSE') {
    content = addYear('front', content);
  }

  fs.writeFileSync(filepath, content, (err) => {
    if (err) {
      throw err;
    }
  });
};

processFile('LICENSE');

const options = {
  ignore: [
    'node_modules/**',
    'scripts/**',
    'vendor/**',
    'views/scripts/**',
  ],
};

for (const file of globSync('**/*.{css,js,php,tpl}', options)) {
  processFile(file);
}
