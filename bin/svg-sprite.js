#!/usr/bin/env node

'use strict';

/**
 * Module dependencies.
 */
var _ = require('lodash'),
    path = require('path'),
    fs = require('fs'),
    mkdirp = require('mkdirp'),
    File = require('vinyl'),
    glob = require('glob'),
    SVGSpriter = require('svg-sprite');

/**
 * Recursively write files to disc
 *
 * @param {Object} files Files
 * @return {Number} Number of written files
 */
function writeFiles(files) {
    var written = 0;
    for (var key in files) {
        if (_.isObject(files[key])) {
            if (files[key].constructor === File) {
                mkdirp.sync(path.dirname(files[key].path));
                fs.writeFileSync(files[key].path, files[key].contents);
                ++written;
            } else {
                written += writeFiles(files[key]);
            }
        }
    }
    return written;
}

var files = glob.sync('resources/svg/*.svg');
var transparentify = ['#000', '#333', 'NONE', '#1B6EBE', '#000000', '#333333', 'WHITE', '#BDBDBD', '#FFF'];
var spriter = new SVGSpriter({
    dest: 'public/svg',
    svg: {
        transform: [
            function(svg) {
                return svg.replace(/fill="[^"]+"/g, function(match) {
                    var color = match.replace('fill="', '').replace('"', '').toUpperCase();
                    if (transparentify.indexOf(color) === -1) {
                        return match;
                    }
                    return '';
                });
            },
        ]
    },
    mode: {
        defs: {
            dest: 'spritesheet',
            sprite: 'sprite.svg',
            example: {
                template: 'bin/template/sprite.html',
                dest: 'icons.html',
            },
        },
    },
});

files.forEach(function (file) {
    var basename = file;
    file = path.resolve(file);
    var stat = fs.lstatSync(file);
    if (stat.isSymbolicLink()) {
        file = fs.readlinkSync(file);
        basename = path.basename(file);
    } else {
        var basepos = basename.lastIndexOf('./');
        basename = (basepos >= 0) ? basename.substr(basepos + 2) : path.basename(file);
    }
    spriter.add(file, basename, fs.readFileSync(file));
});

spriter.compile(function (error, result) {
    if (error) {
        console.error(error);
    } else {
        writeFiles(result);
    }
});
