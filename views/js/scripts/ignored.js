/*!
 * Stancer PrestaShop v1.0.0
 * (c) 2023 Iliad 78
 * Released under the MIT License.
 */
module.exports = [];

// These scripts
module.exports.push("scripts/**");
module.exports.push("**.zip");

// Gulp process
module.exports.push("node_modules/**");
module.exports.push("gulp*");
module.exports.push("package*");
module.exports.push("yarn*");
module.exports.push("pnpm*");
module.exports.push("views/scss/**");
module.exports.push("views/js/scripts/**");

// Composer stuff
module.exports.push("composer*");
module.exports.push("reports/**");

// Rep stuff
module.exports.push("CHANGELOG.md");

// PS stuff
module.exports.push("config*.xml");
