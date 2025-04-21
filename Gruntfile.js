module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    uglify: {
      options: {
        mangle: true, // Shortens variable and function names
        preserveComments: false,
        compress: {
          drop_console: false // Do NOT remove console.* functions
        }
      },
      my_target: {
        files: [{
          expand: true,
          cwd: 'js', // Source folder with original JS files
          src: ['**/*.js', '!**/*.min.js'], // Target all JS files except already minified ones
          dest: 'js/dist', // Destination folder for minified JS files
          ext: '.min.js', // Extension for minified files
          extDot: 'first' // Replaces the first dot in file name for extension (i.e., filename.js becomes filename.min.js)
        }]
      },
    },
    watch: {
      scripts: {
        files: ['js/*.js', '!js/sw.js'],
        tasks: ['uglify'],
        options: {
          spawn: false,
        },
      },
    }
  });

  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.registerTask('default', ['uglify', 'watch']);
};