var express = require('express');
var resumable = require('./resumable-node.js')('/tmp/resumable.js/');
var app = express();
var multipart = require('connect-multiparty');
// Host most stuff in the public folder
var bodyParser = require('body-parser');
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({
    extended:true
 }));



app.use(express.static(__dirname + '/public'));
//app.use(bodyparser.json());
app.use(multipart());

// Handle uploads through Resumable.js
app.post('/upload', function(req, res){

	//console.log(req);

    resumable.post(req, function(status, filename, original_filename, identifier){
        console.log('POST', status, original_filename, identifier);

        res.send(status, {
            // NOTE: Uncomment this funciton to enable cross-domain request.
            //'Access-Control-Allow-Origin': '*'
        });
    });
});

// Handle cross-domain requests
// NOTE: Uncomment this funciton to enable cross-domain request.
/*
  app.options('/upload', function(req, res){
  console.log('OPTIONS');
  res.send(true, {
  'Access-Control-Allow-Origin': '*'
  }, 200);
  });
*/

var exec = require('child_process').exec;
// Handle status checks on chunks through Resumable.js
app.get('/upload', function(req, res){
    resumable.get(req, function(status, filename, original_filename, identifier){
        console.log('GET', status);
        res.send((status == 'found' ? 200 : 404), status);
      });
  });

app.post('/success', function(req, res) {
  //var dir = req.body.dir;
  //var dir = '/tmp/resumable.js';
  var dir = './temp/';

  var uniqueIdentifier = req.body.uniqueIdentifier;
  var name = req.body.name;
  var chunk = req.body.chunk
  var totalsize = req.body.totalsize
  console.log(name)
  console.log(dir)
  console.log(uniqueIdentifier)

  exec('php combiner.php ' + [dir, name, uniqueIdentifier, totalsize].join(' '), function(err, stdout, stderr) {console.log(stdout);
    });
});
app.get('/download/:identifier', function(req, res){
	resumable.write(req.params.identifier, res);
});
app.get('/resumable.js', function (req, res) {
  var fs = require('fs');
  res.setHeader("content-type", "application/javascript");
  fs.createReadStream("../../resumable.js").pipe(res);
});

app.listen(3000);
