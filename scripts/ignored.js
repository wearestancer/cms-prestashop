module.exports = [];

// These scripts
module.exports.push('scripts/**');
module.exports.push('**.zip');

// Gulp process
module.exports.push('node_modules/**');
module.exports.push('gulp*');
module.exports.push('package*');
module.exports.push('yarn*');
module.exports.push('pnpm*');
module.exports.push('views/scripts/**');
module.exports.push('views/scss/**');

// Composer stuff
module.exports.push('composer*');
module.exports.push('reports/**');

// Rep stuff
module.exports.push('CHANGELOG.md');

// PS stuff
module.exports.push('config*.xml');
