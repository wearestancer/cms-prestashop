const fs = require('node:fs');

const pack = require('../package.json');

const file = 'stancer.php';
const version = `$this->version = '${pack.version}';`;

fs.readFile(file, { encoding: 'utf8' }, (err, content) => {
  if (err) {
    throw err;
  }

  fs.writeFile(file, content.replace(/\$this->version.+/, version), (err) => {
    if (err) {
      throw err;
    }
  });
});
