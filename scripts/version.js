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
const version = `* @version   ${pack.version}`;

const content = fs.readFileSync(file, fsOptions);

const data = content
  .replace(/\* @version.+/, version)
  .replace(/\$this->version.+/, `$this->version = '${pack.version}';`)
  .replace(/public const VERSION .+/, `public const VERSION = '${pack.version}';`);

fs.writeFileSync(file, data, fsOptions);

for (const file of globSync('**/*.{js,php,tpl}', globOptions)) {
    const filepath = path.join(process.cwd(), file);

    const content = fs.readFileSync(filepath, fsOptions);

    fs.writeFileSync(filepath, content.replace(/\* @version.+/, version), fsOptions);
}
