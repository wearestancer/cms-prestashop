/*!
 * Stancer PrestaShop v1.0.0
 * (c) 2023 Iliad 78
 * Released under the MIT License.
 */
const fs = require("node:fs");

const md5 = require("js-md5");
const vsprintf = require("sprintf-js").vsprintf;

const trads = require("./translations/index");

const name = "stancer";
const langs = {};

trads.forEach((trad) => {
  const key = md5(trad.texts.en.replace(/'/g, "\\'"));

  for (let lang in trad.texts) {
    if (trad.texts.hasOwnProperty(lang)) {
      if (!langs.hasOwnProperty(lang)) {
        langs[lang] = [];
      }

      trad.domains.forEach((domain) => {
        langs[lang].push(
          vsprintf("$_MODULE['<{%s}prestashop>%s_%s'] = '%s';", [
            name,
            domain !== "core" ? domain : name,
            key,
            trad.texts[lang].replace(/'/g, "\\'").replace(/"/g, '\\"'),
          ])
        );
      });

      if (langs[lang].length) {
        langs[lang] = [...new Set(langs[lang])].sort(); // unique and sort
      }
    }
  }
});

fs.mkdir(__dirname + "/../../../translations/", () => {
  for (let lang in langs) {
    const file = fs.createWriteStream(
      __dirname + "/../../../translations/" + lang + ".php"
    );

    file.write(
      `<?php
global $_MODULE;
$_MODULE = [];

` +
        langs[lang].join("\n") +
        `

return $_MODULE;
`
    );

    file.end();
  }
});
