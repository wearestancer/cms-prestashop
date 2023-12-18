const fs = require('node:fs');
const path = require('node:path');

const glob = require('glob');

const ignore = require('./ignored');
const search = /\('Last-Modified: .+'\)/;
const replace = `('Last-Modified: ${new Date().toUTCString()}')`;
const ref = fs.readFileSync('index.php', 'utf8').replace(search, replace);

glob('**/', { ignore }, (err, dirs) => {
  if (err) {
    throw err;
  }

  dirs.forEach((dir) => {
    fs.writeFile(path.join(dir, 'index.php'), ref, 'utf8', (e) => {
      if (e) {
        throw e;
      }
    });
  });
});

fs.writeFile('index.php', ref, 'utf8', (e) => {
  if (e) {
    throw e;
  }
});
