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
}

const content = fs.readFileSync(file, fsOptions);

const data = content
  .replace(/\$this->version.+/, `$this->version = '${pack.version}';`)
  .replace(/public const VERSION .+/, `public const VERSION = '${pack.version}';`);

fs.writeFileSync(file, data, fsOptions);

const version = `* @version   ${pack.version}`;

for (const file of globSync('**/*.php', globOptions)) {
  const filepath = path.join(process.cwd(), file);

  const content = fs.readFileSync(filepath, fsOptions);

  fs.writeFileSync(filepath, content.replace(/\* @version.+/, version), fsOptions);
}
