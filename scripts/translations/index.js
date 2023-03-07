const fs = require('node:fs');

module.exports = [];

fs.readdirSync(__dirname).forEach((file) => {
  if (file === 'index.js') {
    return;
  }

  module.exports = module.exports.concat(require('./' + file));
});
