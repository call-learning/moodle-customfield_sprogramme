/* eslint-env node */
/* jshint node: true */
/* jshint esversion: 6 */

module.exports = grunt => {
    const path = require('path');
    const moodleRoot = path.resolve(__dirname, '../../../');

    // Always load the sass task before configuring/merging.
    grunt.loadNpmTasks('grunt-sass');

    // One place to keep Sass options consistent.
    const sassOptions = {
        implementation: require('sass'),
        includePaths: [path.join(moodleRoot, 'customfield/field/sprogramme/scss/')],
        outputStyle: 'expanded', // Pretty output.

    };
    process.chdir(moodleRoot);
    try {
        const rootGruntfile = path.join(moodleRoot, 'Gruntfile.js');
        if (grunt.file.exists(rootGruntfile)) {
            require(rootGruntfile)(grunt);
        }
        // Extend/override with your project-specific target using absolute paths.
        grunt.config.merge({
            sass: {
                sprogramme: {
                    files: {
                        [path.join(moodleRoot, 'customfield/field/sprogramme/styles.css')]:
                            path.join(moodleRoot, 'customfield/field/sprogramme/scss/styles.scss')
                    },
                    options: sassOptions
                }
            },
            stylelint: {
                sprogramme: {
                    options: {
                        fix: true,
                    },
                    src: [path.join(moodleRoot, 'customfield/field/sprogramme/styles.css')]
                }
            }
        });
        // Default task available in both success/failure paths.
        grunt.registerTask('sprogramme_sass', ['sass:sprogramme', 'stylelint:sprogramme']);
        grunt.registerTask('default', ['sprogramme_sass']);
    } finally {
        // Always return to the original working directory.
        process.env.PWD = moodleRoot; // optional, helps code that prefers PWD.
    }
};