// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Development save server for VvvebJs page builder.
 *
 * @package   local_edwiserpagebuilder
 * @copyright (c) 2023 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const express = require('express');
const app = express();
const bodyParser = require('body-parser');
const fs = require('fs');
const path = require('path');

//app.use(express.json({ limit: "200mb" }));
app.use(express.static("./"));
app.use(bodyParser.urlencoded({
  extended: true,
  limit: "200mb"
}));

const sanitizeFileName = (file, allowedExtension = 'html') => {
    const basename = path.basename(file);
    const disallow = ['.htaccess', 'passwd'];
    if (disallow.includes(basename)) {
        console.error('Filename not allowed!');
        return '';
    }
    
    // sanitize, remove double dot .. and remove get parameters if any
    file = file.replace(/\?.*$/, '')
               .replace(/\.{2,}/g, '')
               .replace(/[^\/\\a-zA-Z0-9\-\._]/g, '');
    
    if (file) {
        file = path.join(__dirname, file);
    } else {
        return '';
    }
    
    // allow only specified extension
    if (allowedExtension) {
        const ext = path.extname(file);
        if (ext) {
            file = file.slice(0, -ext.length);
        }
        file = `${file}.${allowedExtension}`;
    }
    
    return file;
}

app.post('/save.php', (req, res) => {
  let { file, html } = req.body;

  let result = "Error saving file!";
  file = sanitizeFileName(file);

  if (file && html) {
	try {
	  fs.writeFileSync(file, html);
	  result = "File saved!";
	} catch (err) {
	  console.error(err);
	}
  }

  res.send(result);
});
 
app.listen(8080, () => {
  console.log('Started');
});
