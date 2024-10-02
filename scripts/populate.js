const fs = require('node:fs');
const path = require('node:path');

const { globSync } = require('glob');

const ignore = require('./ignored');
const search = /\('Last-Modified: .+'\)/;
const replace = `('Last-Modified: ${new Date().toUTCString()}')`;
const ref = fs.readFileSync('index.php', {encoding: 'utf-8'}).replace(search, replace);

for (const dir of globSync('**/', { ignore })) {
  fs.writeFileSync(path.join(dir, 'index.php'), ref, {encoding: 'utf-8'});
}

fs.writeFileSync('index.php', ref, {encoding: 'utf-8'});
