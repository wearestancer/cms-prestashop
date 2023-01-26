/*!
 * Stancer PrestaShop v1.0.0
 * (c) 2023 Iliad 78
 * Released under the MIT License.
 */
const fs = require('node:fs');

module.exports = [];

fs.readdirSync(__dirname).forEach(file => {
  if (file === 'index.js') {
    return;
  }

  module.exports = module.exports.concat(require('./' + file));
})
