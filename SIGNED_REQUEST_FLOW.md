# OpenEd JavaScript API - Signed Server Request flow

The method described here allows you to integrate with OpenEd resource library from your web application using the secure signed server request. This way, user is not required to enter his credentials on the OpenEd Sign-in popup windows, instead allowing a user with an already established identity to pass that identity to OpenEd using the secure server request signed with the private key provided by OpenEd. 

## Generating Signed Server Request

Once your web application has completed the authentication process of a certain user and needs to grant that user access to the OpenEd resource library, it needs to create a secure signed server request and pass it to the OpenEd for validation. This request needs to be constructed on your web server application - in order not to expose the secret private key provided by OpenEd. This signed request needs to be generated uniquely per user (thus, embedding the username). In the example below, username is passed from the browser, however we recommend you to implement passing the username internally on the server (to hide the full process from the client):

```html
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Testapp</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <form action="/generate_signed_request" method="POST">
      <input type="text" name="username">
      <input type="submit" value="Submit">
    </form>
  </body>
</html>
```

Example implementation of generate_signed_request routine is provided below (for Sinatra server written in Ruby):

```ruby
require 'base64'
require 'json'
require 'openssl'
require 'securerandom'
require 'sinatra'
require 'sinatra/cross_origin'

enable :cross_origin
set :port, 1337

APP_SECRET = 'THE.APP.SECRET.SHARED.BETWEEN.YOU.AND.OPENEDIO'
CLIENT_ID = 'YOUR.APP.ID.GRANTED.BY.OPENEDIO'

def base64_url_encode(str)
  Base64.encode64(str).tr('+/', '-_').gsub(/\s/, '').gsub(/=+\z/, '')
end

options '*' do
  response.headers['Allow'] = 'HEAD,GET,PUT,POST,DELETE,OPTIONS'
  response.headers['Access-Control-Allow-Headers'] = 'X-Requested-With, X-HTTP-Method-Override, Content-Type, Cache-Control, Accept'
  200
end

post '/generate_signed_request' do
  envelope = @params
  envelope["client_id"] ||= CLIENT_ID
  envelope["algorithm"] ||= 'HMAC-SHA256'
  envelope["token"] ||= SecureRandom.hex #It's important that this is unique by user

  envelope = JSON.dump(envelope)
  encoded_envelope = base64_url_encode(envelope)

  signature = OpenSSL::HMAC.hexdigest(OpenSSL::Digest::SHA256.new, APP_SECRET, encoded_envelope)
  encoded_signature = base64_url_encode(signature)

  @signed_request = "#{encoded_signature}.#{encoded_envelope}"
  @client_id = CLIENT_ID

  erb :login_and_query
end

```

At the end of the signed request generation, your server needs to pass it back to the browser. In the above example, it is done by embedding the generated signed server request into the page that is rendered as a result at the end of the method. After that, the signed secure request can be used to login your authenticated user to the OpenEd system. 

## Using the signed server request to authenticate the user with OpenEd.

Once signed server request is obtained, it can be used to authenticate your user with the OpenEd system. It is performed in 2 steps:

### Initializing the internal Oauth.js routines with your public key on the browser by calling:

```javascript
window.OpenEd.api.init({
  client_id: 'YOUR_PUBLIC_CLIENT_ID_FROM_OPENED'
});
```

###  Completing the login process by passing the signed server request to the OpenEd system:
```javascript
window.OpenEd.api.silentLogin('YOUR_GENERATED_SIGNED_REQUEST_FOR_THE_USER', function(){
  // you're authenticated now, do something useful
  search_params = {
  };
  perform_resources_search();
}, function(data){
  // something happenned, log the error
  console.error(data.error);
});
```

Once authentication has been completed, your web app can use the OpenEd REST API to access the resources library. For example:

```javascript
search_params = {
  descriptive: 'quadrilateral shapes',
  grade: '9'
}
var perform_resources_search = function() {
  window.OpenEd.api.request("/resources", search_params, function(data){
    var resourcesList = document.getElementById('resources_list');
    data.resources.forEach(function(resource){
      var li = document.createElement('li');
      var span = document.createElement('span');
      span.innerText = resource.title;
      li.appendChild(span);
      resourcesList.appendChild(li);
    });
  });
};
```

You can access the complete code examples for the following server implementations:

  - [Sinatra Ruby](signing_server_examples/ruby)
  - [Node.js](signing_server_examples/node)

## Licensing and usage

Before your app attempts to authenticate the user and pass his identity to OpenEd - it is advised that to warn the user of the upcoming authentication routine on a 3rd party application (i.e. OpenEd). Also, it is advised for your app to post a link to the OpenEd's License Terms.
