/* eslint-env node */
/* jshint node: true */
/* jshint esversion: 6 */

module.exports = grunt => {
    const path = require('path');
    const originalCwd = process.cwd();
    const moodleRoot = path.resolve(__dirname, '../../../');

    // Always load the sass task before configuring/merging.
    grunt.loadNpmTasks('grunt-sass');

    // One place to keep Sass options consistent.
    const sassOptions = {
        implementation: require('sass'),
        includePaths: [path.join(originalCwd, 'scss/')],
        outputStyle: 'expanded',   // pretty output
        indentType: 'space',       // spaces instead of tabs
        indentWidth: 4             // 4-space indents
    };


    try {
        // Move to Moodle root to allow loading its Gruntfile in-context.
        process.chdir(moodleRoot);

        const rootGruntfile = path.join(moodleRoot, 'Gruntfile.js');
        if (grunt.file.exists(rootGruntfile)) {
            require(rootGruntfile)(grunt);
        }

        // Extend/override with your project-specific target using absolute paths.
        grunt.config.merge({
            sass: {
                sprogramme: {
                    files: {
                        [path.join(originalCwd, 'styles.css')]:
                            path.join(originalCwd, 'scss/styles.scss')
                    },
                    options: sassOptions
                }
            }
        });

    } catch (error) {
        grunt.log.error('Erreur lors du chargement du Gruntfile racine:', error.message);

        // Fallback: configure a minimal local build from the original CWD.
        grunt.initConfig({
            sass: {
                sprogramme: {
                    files: {
                        'styles.css': 'scss/styles.scss'
                    },
                    options: sassOptions
                }
            }
        });

    } finally {
        // Always return to the original working directory.
        try { process.chdir(originalCwd); } catch (_) {}
    }
    // Default task available in both success/failure paths.
    grunt.registerTask('default', ['sass:sprogramme']);
};