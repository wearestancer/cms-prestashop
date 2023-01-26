
import gulp from 'gulp';
import autoprefixer from 'autoprefixer';
import cssnano from 'cssnano';
import gulpif from 'gulp-if';
import gulpSass from 'gulp-sass';
import postcss from 'gulp-postcss';
import sourcemaps from 'gulp-sourcemaps';
import uglify from 'gulp-uglify';
import pump from 'pump';
import sassCompiler from 'sass';

const sass = gulpSass(sassCompiler);
const paths = {
  styles: {
    src: 'views/scss/**/*.scss',
    dest: 'views/css/'
  },
  scripts: {
    src: 'views/js/scripts/views/*.js',
    dest: 'views/js/'
  },
};
const uglifyConf = {
  mangle: false,
};

let maps = true;

const noMap = (cb) => {
  maps = false;

  cb();
};

export const styles = () => {
  return gulp.src(paths.styles.src)
    .pipe(gulpif(maps, sourcemaps.init()))
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([
      autoprefixer(),
      cssnano(),
    ]))
    .pipe(gulpif(maps, sourcemaps.write('.')))
    .pipe(gulp.dest(paths.styles.dest))
  ;
};

export const scripts = (cb) => {
  pump([
    gulp.src(paths.scripts.src),
    gulpif(maps, sourcemaps.init()),
    uglify(uglifyConf),
    gulpif(maps, sourcemaps.write('.')),
    gulp.dest(paths.scripts.dest),
  ], cb);
};


export const build = gulp.series(noMap, gulp.parallel(styles, scripts));

export const watch = () => {
  gulp.watch(paths.styles.src, styles);
  gulp.watch(paths.scripts.src, scripts);

  build();
};
