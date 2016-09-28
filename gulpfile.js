var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var shell = require('gulp-shell');
var watch = require('gulp-watch');
var gutil = require('gulp-util');
var plumber = require('gulp-plumber');
var connect = require('gulp-connect');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var cssnano = require('gulp-cssnano');
var imagemin = require('gulp-imagemin');
var rev = require('gulp-rev');
var buffer = require('gulp-buffer');
var revCollector = require('gulp-rev-collector');
var htmlmin = require('gulp-htmlmin');
var rename = require('gulp-rename');
var runSequence = require('run-sequence');

var hugoTheme = 'mdb-addhen';

var sassInput = './assets/sass/**/*.scss';
var sassOutput = './public/css/';

var jsInput = [
  './themes/'+hugoTheme+'/static/js/jquery-2.2.3.js',
  './themes/'+hugoTheme+'/static/js/tether.js',
  './themes/'+hugoTheme+'/static/js/bootstrap.js',
  './themes/'+hugoTheme+'/static/js/mdb.js',
  './themes/'+hugoTheme+'/static/js/addhen.js',
  './themes/'+hugoTheme+'/static/js/jqBootstrapValidation-1.3.7.js',
  './themes/'+hugoTheme+'/static/js/contact.js'
];

var htmlInput = [
  './rev-manifest.json',
  './public/**/*.html'
];

var jsOutput = './public/js/';

var sassOptions = {
  errLogToConsole: true,
  outputStyle: 'expanded'
};

var hugoInput = [
  './archetypes/**/*.*',
  './content/**/*.*',
  './data/**/*.*',
  './layouts/**/*.*',
  './static/**/*.*',
  './themes/**/*.*',
  './config.yml'
];

var imageInput = './static/img/**/*';
var imageOutput = './public/img';

var configDevFile = './config-dev.yaml';
var configProdFile = './config-prod.yaml';

gulp.task('default', ['serve']);

gulp.task('build', ['hugo', 'sass', 'js', 'img', 'html']);

gulp.task('hugo', function() {
  return buildHugo();
});

gulp.task('sass', function() {
  return buildSass();
});

gulp.task('js', function() {
  return buildJs();
});

gulp.task('img', function() {
  return buildImages();
});

gulp.task('html', function() {
  return buildHtml();
});

gulp.task('config-dev', function() {
  return changeConfigFile(configDevFile);
});

gulp.task('config-prod', function() {
  return changeConfigFile(configProdFile);
});

gulp.task('watch', function() {
  changeConfigFile(configDevFile);
  buildHugo();
  buildJs();
  buildSass();
  buildHtml();

  watch(sassInput, function (vinyl) {
    gutil.log(gutil.colors.green(vinyl.relative), 'fired', gutil.colors.green(vinyl.event));
    return buildSass().pipe(connect.reload());
  });
  watch(jsInput, function (vinyl) {
    gutil.log(gutil.colors.green(vinyl.relative), 'fired', gutil.colors.green(vinyl.event));
    return buildJs().pipe(connect.reload());
  });
  watch(hugoInput, function (vinyl) {
    gutil.log(gutil.colors.green(vinyl.relative), 'fired', gutil.colors.green(vinyl.event));
    return buildHugo().pipe(connect.reload());
  });
});

gulp.task('serve', function() {
  connect.server({
      'root': 'public',
      'livereload': true,
      'port': 6789
  });
  gulp.start('watch');
});

function buildHugo() {
  var hugoCommand = 'hugo --buildDrafts';

  if (hugoTheme) {
    hugoCommand += ' --theme=' + hugoTheme;
  }

  return gulp
    .src('')
    .pipe(plumber({
      errorHandler: handleError
    }))
    .pipe(shell([
      hugoCommand
    ]
  ));
}

function buildSass() {
  gutil.log(gutil.colors.red('building sass...'));
  return gulp
    .src(sassInput)
    .pipe(plumber({
      errorHandler: handleError
    }))
    .pipe(sourcemaps.init())
    .pipe(sass(sassOptions))
    .pipe(sourcemaps.write())
    .pipe(autoprefixer())
    .pipe(cssnano())
    .pipe(buffer())
    .pipe(rev())
    .pipe(gulp.dest(sassOutput))
    .pipe(rev.manifest({
      base:  sassOutput,
      merge: true
    }))
    .pipe(gulp.dest(sassOutput));
}

function buildJs() {
  gutil.log(gutil.colors.red('building js...'));
  return gulp.src(jsInput)
    .pipe(plumber({
      errorHandler: handleError
    }))
    .pipe(concat('addhen.min.js'))
    .pipe(uglify())
    .pipe(buffer())
    .pipe(rev())
    .pipe(gulp.dest(jsOutput))
    .pipe(rev.manifest({
      base: jsOutput,
      merge: true
    }))
    .pipe(gulp.dest(jsOutput));
}

function buildImages() {
  gutil.log(gutil.colors.red('building images...'));
  return gulp.src(imageInput)
    .pipe(imagemin({
      progressive: true,
      svgoPlugins: [
        {removeViewBox: false},
        {cleanupIDs: false}
      ]
    }))
    .pipe(gulp.dest(imageOutput));
}

function buildHtml() {
  gutil.log(gutil.colors.red('building html...'));
  return gulp
    .src(htmlInput)
    .pipe(revCollector({
      replaceReved: true
    }))
    .pipe(htmlmin({collapseWhitespace: true}))
    .pipe(gulp.dest('public'));
}

function handleError(error) {
  gutil.beep();
  var message = null;
  if (error.hasOwnProperty('formatted')) {
    message = error.formatted;
  } else {
    message = error;
  }
  gutil.log(gutil.colors.red(message));
  this.emit('end');
}

function changeConfigFile(newConfigFile) {
  return gulp.src(newConfigFile)
    .pipe(rename('config.yaml'))
    .pipe(gulp.dest('./'));
}