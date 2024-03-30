import gulp from 'gulp';
import autoprefixer from 'autoprefixer';
import cssnano from 'cssnano';
import gulpSass from 'gulp-sass';
import postcss from 'gulp-postcss';
import uglify from 'gulp-uglify';
import pump from 'pump';
import * as sassCompiler from 'sass';

const sass = gulpSass(sassCompiler);
const paths = {
  styles: {
    src: 'views/scss/**/*.scss',
    dest: 'views/css/',
  },
  scripts: {
    src: 'views/scripts/*.js',
    dest: 'views/js/',
  },
};
const uglifyConf = {
  mangle: false,
};

export const styles = () => {
  return gulp
    .src(paths.styles.src)
    .pipe(sass().on('error', sass.logError))
    .pipe(
      postcss([
        autoprefixer(),
        cssnano(),
      ]),
    )
    .pipe(gulp.dest(paths.styles.dest));
};

export const scripts = (cb) => {
  pump(
    [
      gulp.src(paths.scripts.src),
      uglify(uglifyConf),
      gulp.dest(paths.scripts.dest),
    ],
    cb,
  );
};

export const build = gulp.parallel(styles, scripts);

export const watch = () => {
  gulp.watch(paths.styles.src, styles);
  gulp.watch(paths.scripts.src, scripts);

  build();
};
