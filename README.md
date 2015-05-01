# OpenEd JavaScript API

This JavaScript library lets you integrate with the OpenEd resource library from your web application. It provides the client with two ways of authenticating the user with the OpenEd system:

  - via Signed Server Request (using the private secret token provided you by OpenEd)
  - Client-to-client OAuth , allowing user to interact directly with the OpenEd sign-in dialog.

We recommend using the Signed Server Request method, which is more secure and eliminates the need to interact with the OpenEd UI, instead allowing to pass the identity of the user authenticated by your web app directly into OpenEd.

In the following sections - we will describe how to use Signed Server Request method. For those, who is interested in using the Client-to-client approach - it is described [here](CLIENT-TO-CLIENT.md).

# OpenEd JavaScript API - Signed Server Request method

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
  perform_resources_search();
}, function(data){
  // something happenned, log the error
  console.error(data.error);
});
```

### Using the REST API after successful login

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

Full documentation of the REST API can be found [here](http://docs.opened.apiary.io/).  Note however that resources query must be performed via our JavaScript API
 
### /resources.json 
 
Makes a resources search request. 
 
Returns an array of resources and pagination info

for more details visit http://docs.opened.apiary.io/
 
#### Parameters
- **descriptive** (string, optional, example: 3D) - Filters title, description, area_title and subject_title with Solr fulltext search
- **limit** (number, optional, example: 1) - Maximum number of results to return (1..100, 50 by default).
- **offset** (number, optional, example: 0) - 0 by default.
- **standard_group** (number, optional, example: 4) - looks for resources aligned with specified standards in the standard_group by standard_groups ID
- **category** (string, optional, example: Geometry%20%28Elementary%29) - looks for resources aligned with specified standards that belong to the specified category
- **standard** (string, optional, example: K.G.2) - looks for resources aligned with specified standard by standard.id (the internal object ID) or standard.identifier (the Common Core ID)
- **area** (string, optional, example: Math) - looks for resources assigned with specified area (and/or with specified subjects of area) by area.id or area.title
- **subject** (string, optional, example: Geometry) - looks for resources assigned with specified subject by subject.id or subject.title
- **grade** (string, optional, example: K) - restricts to specified grades (expressed as K,1, .. 12)
- **grade_group** (string, optional, example: Elementary) - restricts to specified grade_group (e.g. "Elementary", "Middle School", "High School")
- **contribution_name** (string, optional) - the name of the contribution (e.g. "BrightStorm", "KhanAcademy")
- **resource_types** (string, optional ) - array of resource types which can be "video", "game", "assessment", "homework", "lesson_plan"
 
#### Example:
 
##### request:
```javascript
window.OpenEd.api.request('/resources.json', {
    offset: 5,
    limit: 5,
    descriptive: 'area of a circle'
}, callback)
```
##### response:
```json
{
  "meta":{
    "pagination":{
      "offset":5,
      "total_entries":8177,
      "entries":5,
      "limit":5
      }
    },
    "resources":[
      ...
    ]
}
```

## Complete code samples

You can access the complete code examples for the following server implementations:

  - [Sinatra Ruby](signing_server_examples/ruby)
  - [Node.js](signing_server_examples/node)

## Licensing and usage

Before your app attempts to authenticate the user and pass his identity to OpenEd - it is advised to warn the user of the upcoming authentication routine on a 3rd party application (i.e. OpenEd). Also, it is advised for your app to post a link to the OpenEd's License Terms.
