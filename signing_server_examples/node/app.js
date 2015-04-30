var bodyParser = require('body-parser');
var cors = require('cors');
var crypto = require('crypto');
var express = require('express');

var APP_SECRET = 'THE.APP.SECRET.SHARED.BETWEEN.YOU.AND.OPENEDIO';
var CLIENT_ID  = 'YOUR.APP.ID.GRANTED.BY.OPENEDIO';

var app = express();

app.set('view engine', 'ejs');
app.set('views', './views');

app.use(bodyParser.json());
app.use(bodyParser.urlencoded({
  extended: true
}));

app.use(cors());

app.listen(1337);

var base64UrlEncode = function (input){
  return new Buffer(input).toString('base64').replace('+','-').replace('/','_').replace(/=+$/,'');
};

var generateSignedRequest = function(username, client_id) {
  var envelope = {username: username, client_id: client_id};
  envelope.token = crypto.randomBytes(64).toString('hex'); //It's important that this is unique by user
  envelope.algorithm = 'HMAC-SHA256';
  envelope = JSON.stringify(envelope);

  var encoded_envelope = base64UrlEncode(envelope);

  var hmac = crypto.createHmac('sha256', APP_SECRET);
  var signature = hmac.update(encoded_envelope).digest().toString('hex');
  var encoded_signature = base64UrlEncode(signature);

  return encoded_signature + "." + encoded_envelope;
};

app.use(express.static('public'));
app.use(express.static('../../'));

app.post('/generate_signed_request', function (request, response) {
  response.render('test', {
    signed_request: generateSignedRequest(request.body.username, CLIENT_ID),
    client_id: CLIENT_ID
  });
});

