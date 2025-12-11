const fs = require('node:fs');
const path = require('node:path');
const { globSync } = require('glob');

const pack = require('../package.json');

const file = 'stancer.php';
const globOptions = {
  ignore: [
    'node_modules/**',
    'scripts/**',
    'vendor/**',
    file,
  ],
};
const fsOptions = {
  encoding: 'utf-8',
};
const version = `* @version   ${pack.version}`;

fs.readFile(file, fsOptions, (err, content) => {
  if (err) {
    throw err;
  }
  const data = content
    .replace(/\$this->version.+/, `$this->version = '${pack.version}';`)
    .replace(/const STANCER_MODULE_VERSION .+/, `const STANCER_MODULE_VERSION = '${pack.version}';`);

  fs.writeFile(file, data, (err) => {
    if (err) {
      throw err;
    }
  });
});

globSync('**/*.php', globOptions, (err, files) => {
  if (err) {
    throw err;
  }

  files.forEach((file) => {
    const filepath = path.join(process.cwd(), file);

    fs.readFile(filepath, fsOptions, (err, content) => {
      if (err) {
        throw err;
      }

      fs.writeFile(filepath, content.replace(/\* @version.+/, version), (err) => {
        if (err) {
          throw err;
        }
      });
    });
  });
});
