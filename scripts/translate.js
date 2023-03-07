const fs = require('node:fs');

const md5 = require('js-md5');
const vsprintf = require('sprintf-js').vsprintf;

const trads = require('./translations/index');

const name = 'stancer';
const langs = {};

trads.forEach((trad) => {
  const key = md5(trad.texts.en.replace(/'/g, "\\'"));

  for (let lang in trad.texts) {
    if (trad.texts.hasOwnProperty(lang)) {
      if (!langs.hasOwnProperty(lang)) {
        langs[lang] = new Set();
      }

      trad.domains.forEach((domain) => {
        langs[lang].add(
          vsprintf("$_MODULE['<{%s}prestashop>%s_%s'] = '%s';", [
            name,
            domain !== 'core' ? domain : name,
            key,
            trad.texts[lang].replace(/'/g, "\\'").replace(/"/g, '\\"'),
          ]),
        );
      });
    }
  }
});

fs.mkdir(__dirname + '/../translations/', () => {
  for (let lang in langs) {
    const file = fs.createWriteStream(__dirname + '/../translations/' + lang + '.php');

    file.write(
      [
        '<?php',
        'global $_MODULE;',
        '$_MODULE = [];',
        '',
        ...Array.from(langs[lang]).sort(),
        '',
        'return $_MODULE;',
        '',
      ].join('\n'),
    );

    file.end();
  }
});
