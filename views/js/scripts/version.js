/*!
 * Stancer PrestaShop v1.0.0
 * (c) 2023 Iliad 78
 * Released under the MIT License.
 */
const fs = require("node:fs");

const pack = require("../../../package.json");

const file = "stancer.php";
const version = `$this->version = '${pack.version}';`;

fs.readFile(file, { encoding: "utf8" }, (err, content) => {
  if (err) {
    throw err;
  }

  fs.writeFile(file, content.replace(/\$this->version.+/, version), (err) => {
    if (err) {
      throw err;
    }
  });
});
