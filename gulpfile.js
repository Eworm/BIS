var gulp = require('gulp'),
    // Get packages from package.json
    plugins = require('gulp-load-plugins')();

// Set source paths
var src_paths = {
    preprocess: 'sass/**/*.scss',
    autoprefixer: '*.css',
    sprites: 'images-src/sprites/**/*.svg',
    functions: ['bower_components/hideShowPassword/hideShowPassword.js',
                'bower_components/parsleyjs/dist/parsley.js',
                'js-src/functions.js'],
    labjs: ['bower_components/labjs/LAB.min.js',
                'js-src/lab-loader.js'],
    javascript: 'scripts/*.*'
};


// Set destination paths
var dest_paths = {
    preprocess: '.',
    images: 'images',
    javascript: 'js'
};


// CSS Preprocessing
gulp.task('preprocess', function() {
    gulp.src(src_paths.preprocess)
    
        .pipe(plugins.plumber({errorHandler: plugins.notify.onError('Error: <%= error.message %>')}))
        
        .pipe(plugins.compass({
            config_file: 'config.rb',
            sourcemap: false,
            css: dest_paths.preprocess,
            sass: 'sass',
            import_path: 'bower_components/normalize.scss'
        }))
        
        .pipe(plugins.autoprefixer('last 2 versions', '> 1%', 'ie 9'))
		.pipe(gulp.dest(dest_paths.preprocess))
        
        .pipe(plugins.livereload())
        .pipe(plugins.notify({ message: 'Preprocessing complete' }))
});


// Uglify
gulp.task('uglify', function() {
    
    gulp.src(src_paths.functions)
    
        .pipe(plugins.plumber({errorHandler: plugins.notify.onError('Error: <%= error.message %>')}))
    
        .pipe(plugins.concat('functions.min.js'))
        .pipe(plugins.uglify({
            compress: false
        }))
        .pipe(gulp.dest(dest_paths.javascript))
        
        .pipe(plugins.livereload())
        .pipe(plugins.notify({ message: 'Uglify complete' }))
        
    gulp.src(src_paths.labjs)
    
        .pipe(plugins.plumber({errorHandler: plugins.notify.onError('Error: <%= error.message %>')}))
    
        .pipe(plugins.concat('lab.min.js'))
        .pipe(plugins.uglify({
            compress: false
        }))
        .pipe(gulp.dest(dest_paths.javascript))
        
        .pipe(plugins.livereload())
        .pipe(plugins.notify({ message: 'Uglify complete' }))
});


// SCSS lint
gulp.task('lint', function() {
    gulp.src(src_paths.preprocess)
        .pipe(plugins.scssLint())
});


// Watch
gulp.task('watch', function(ev) {
    plugins.livereload.listen();
	gulp.watch(src_paths.preprocess, ['preprocess']);
	gulp.watch(src_paths.javascript, ['uglify']);
});


// Default
gulp.task('default', ['watch']);
