# OpenEd JavaScript API

This JavaScript library lets you integrate with the OpenEd resource library from your web application pages.  This JavaScript library focuses on the process of using OpenEd as an OAuth provider to login to OpenEd and then perform actions such as queries of the resource library. 

## Getting Started
- Create a callback route on your website (you can see an example at callback.html)
```
<script>
	var hash = document.location.hash;
	if (hash) {
	    window.opener.OpenEd.api._setToken(hash);
	}
	window.close();
</script>
```
- Get an application key by sending a request to api@opened.io with a redirect_uri - url that you created in step 1 (more info http://developers.opened.io/).  Indicate that you agree to [API terms](http://developers.opened.io)
- Download and include opened-oauth.js in your web site
- Init your app
``` 
window.OpenEd.api.init(
    {client_id: 'your app id here', redirect_uri: 'your application success callback uri'},
    callback
) 
```
- Run ``` window.OpenEd.api.login() ```. It will open a popup with OpenEd OAuth flow. 
- After success you have your token available via ``` window.OpenEd.api.getToken() ```
- Now you can access OpenEd API endpoints via ``` window.OpenEd.api.request() ```
- And do logout via ``` window.OpenEd.api.logout() ```

## Querying Resources from the OpenEd Resource Library

**init**  - this initialize the JS library (details [here](#initinitoptions-callback))

```javascript
window.OpenEd.api.init({
  client_id: 'your client id',
  redirect_uri: 'https://your.site.com/callback.html'
}, function () {
  //Api init successfully here
  console.log('OpenEd JS API is ready to use!');
});

```

**login** - login with the user identity via OpenEd OAuth provider (details [here](#logincallback))

```javascript
window.OpenEd.api.login(function () {
  //your successfully signin here
  
});
```

**request** - do REST API call like search for resources (details [here](#requestapiname-data-callback-errorcallback))

example call can search based on standard, keyword, and resource_types (e.g. all A.APR.1 polynomial videos OR assessments for 9th grade)

```javascript
search_params = {
  standard: 'A.APR.1',
  descriptive: 'polynomial',
  resource_types: ['video', 'assessment'],
  grade: '9'
}
window.OpenEd.api.request('/resources.json', search_params, function (data) {
  // handling the result
  var template = '';
  $('#resource-cont').html('');
  $.each(data.resources, function () {
    template += '<div class="panel panel-default">';
    template += '<div class="panel-heading">';
    template += this.title;
    template += '</div>';  
    template += '<div class="panel-body">';
    template += '<img scr="' + this.thumb + '">';
    template += '</div>';  
    template += '</div>'; 
  });
  $(template).appendTo('#resource-cont');
});
```

**logout** - revoke OAuth token (details [here](#logoutcallback))

```javascript
window.OpenEd.api.login(function () {
  //your successfully signin here
  
});
```

A longer example of this is [here](./blob/master/index.html). 
 

## JavaScript Method Reference
### init(initOptions, callback)

Initializes the OpenEd API. You should pass your client id and redirect_uri.

This is require to execute subsequent calls with the API.

#### Parameters:
**initOptions:**
 - **client_id** - String, required
 
 Your client app id. 
 
 Example: 'fj34892fj20984ujgf3029guj89324ujgt09u4g'
 
 - **redirect_uri** - String, required
 
 Your app's callback URL. 
 
 Example: 'https://example.com/oauth-callback/'

 - **status** - Boolean, default: false

 On init checks if user already has access to OpenEd API

**callback** - (optional) callback function. Fired when OpenEd API is fully initialized.

#### Example
```
window.OpenEd.api.init({
    client_id: 'your client id',
    redirect_uri: 'your callback url'
}, function(){
    //OpenEd is inited here
});
```
 
### login(callback)
Runs OpenEd OAuth flow. Opens a popup with OpenEd signin/signup capability. Redirects on success(sets the token)
#### Parameters:
 
**callback(error)** - function
 
A callback function that fires on error/success
 
argument *error* is empty if success
#### Example
```
window.OpenEd.api.login(callback)
```
### logout(callback)

Revokes current user OAuth access_token. Makes API inaccessible by current user OAuth access_token.

#### Parameters:
**callback(error)** - function
A callback function that fires on error/success
 
argument *error* is empty if success
#### Example
```
window.OpenEd.api.logout(callback)
```
### request(apiName, data, callback, errorCallback)
Make a request to OpenEd API with OAuth access_token. More info http://docs.opened.apiary.io/
#### Parameters:
**apiName** - string
API end point
**data** - object
query data object
**callback(responseData)** - function
argument *responseData* - result data object
**errorCallback(error)** - function
argument *error* - error object
#### Example
```
window.OpenEd.api.request(apiName, data, callback, errorCallback)
```

### verifyToken(callback)
Verifies current user token.

#### Parameters:
**callback(error)** - function
argument *error* is empty on success

#### Example
```
window.OpenEd.api.verifyToken(function (err) {
  if (err) {
    console.log('Sorry token is invalid');
  } else {
    console.log('Nice! Your token is perfect');
  }
});
```


## Rest API endpoints
These are some of the more important REST API calls.  Full documentation of the REST API can be found [here](http://docs.opened.apiary.io/).  Note however that resource query must be performed via our JavaScript API
 
### /users/me.json 
 
Returns current user object
 
#### Example: 
 
##### request:
```
window.OpenEd.api.request('/users/me.json', null, callback)
```
##### response:
```json
{
    "first_name":"Andrew",
    "last_name":"Saenkov",
    "full_name":"Andrew Saenkov",
    "username":"andrew",
    "email":"andrew@example.com"
}
```
 
### /resources.json 
 
Makes a resource query. 
 
Returns an array of resources and pagination info

for more details visit http://docs.opened.apiary.io/
 
#### Parameters
- **descriptive** (string, optional, example: 3D) - Filters title, description, area_title and subject_title with Solr fulltext search
- **limit** (number, optional, example: 1) - Maximum number of results to return (1..100, 50 by default).
- **offset** (number, optional, example: 0) - 0 by default.
- **standard_group** (number, optional, example: 4) - looks for resources aligned with specified standards in the standard_group by standard_groups ID
- **category** (string, optional, example: Geometry%20%28Elementary%29) - looks for resources aligned with specified standards are in the category by category.id or category.title
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
      {"standard_idents":["3.MD.5"],"grade_idents":["3"],"grades_range":"3","group_id":null,"grade_group_ids":[46],"featured":true,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[6,63],"subject_titles":["Measurement \u0026 Data","Mathematics"],"resource_type":"assessment","id":2165320,"state":"public","thumb":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","thumbnails":{"mini":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","small":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","medium":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","large":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png"},"contribution_name":"Brandon DormanOpen","publisher":"Brandon DormanOpen","owner_id":5781,"share_url":"https://www.opened.io/resources/2165320","rating":5,"is_allowed":true,"is_locked":false,"title":"Measure Area","description":null,"is_premium":true,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/2165320","sort_key":null},
      {"standard_idents":["3.MD.5.b","3.MD.5","3.MD.5.a","3.MD.6"],"grade_idents":["3"],"grades_range":"3","group_id":null,"grade_group_ids":[46],"featured":false,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[6,63],"subject_titles":["Measurement \u0026 Data","Mathematics"],"resource_type":"assessment","id":1068556,"state":"public","thumb":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","thumbnails":{"mini":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","small":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","medium":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","large":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png"},"contribution_name":"Michelle Moody","publisher":"Michelle Moody","owner_id":6584,"share_url":"https://www.opened.io/resources/1068556","rating":3,"is_allowed":true,"is_locked":false,"title":"Area","description":null,"is_premium":false,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1068556","sort_key":null},
      {"standard_idents":["7.G.6"],"grade_idents":["7","8"],"grades_range":"7-8","group_id":null,"grade_group_ids":[47,46],"featured":true,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[1,63],"subject_titles":["Geometry","Mathematics"],"resource_type":"assessment","id":1068772,"state":"public","thumb":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","thumbnails":{"mini":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","small":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","medium":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","large":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png"},"contribution_name":"Kellie Zimmer","publisher":"Kellie Zimmer","owner_id":6610,"share_url":"https://www.opened.io/resources/1068772","rating":5,"is_allowed":true,"is_locked":false,"title":"Area, Volume, Surface Area Real-World","description":null,"is_premium":false,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1068772","sort_key":null},
      {"standard_idents":["7.G.4"],"grade_idents":["6","7","8"],"grades_range":"6-8","group_id":null,"grade_group_ids":[47],"featured":true,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[1,63],"subject_titles":["Geometry","Mathematics"],"resource_type":"assessment","id":1068727,"state":"public","thumb":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","thumbnails":{"mini":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","small":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","medium":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","large":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png"},"contribution_name":"Kellie Zimmer","publisher":"Kellie Zimmer","owner_id":6610,"share_url":"https://www.opened.io/resources/1068727","rating":5,"is_allowed":true,"is_locked":false,"title":"Circles, Area \u0026 Circumference","description":null,"is_premium":false,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1068727","sort_key":null},
      {"standard_idents":["6.G.3"],"grade_idents":["6","7"],"grades_range":"6-7","group_id":null,"grade_group_ids":[47],"featured":true,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[1,63],"subject_titles":["Geometry","Mathematics"],"resource_type":"assessment","id":1068746,"state":"public","thumb":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","thumbnails":{"mini":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","small":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","medium":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png","large":"https://d82yecxzdbhgs.cloudfront.net/images/resource_types/logo.png"},"contribution_name":"Kellie Zimmer","publisher":"Kellie Zimmer","owner_id":6610,"share_url":"https://www.opened.io/resources/1068746","rating":5,"is_allowed":true,"is_locked":false,"title":"Coordinate Plane Areas","description":null,"is_premium":false,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1068746","sort_key":null}
    ]
}
```

## OpenEd API Events
OpenEd Oauth library has basic event support for all general proccess.
### Methods
#### on(eventName, callback)
This method add an event handler(callback) for eventName.
##### Example
```
  window.OpenEd.api.on('somethingHappend', function () {
    //Note: that arguments will be filled depending on event type
  });
```
#### off(eventName, callback)
Removes event handler(callback) from the event namespace(eventName)
##### Example
```
  window.OpenEd.api.off('somethingHappend', eventHandler);
```
### Auth Events
#### userLoggedIn
This event triggers when current user grants access to API. Its fired on ``` login ``` right before the callback. And also it can be fired when user already had hes token in session and it was verified successfully.
##### Example
```
  window.OpenEd.api.on('auth.userLoggedIn', function () {
    //Your logic here
  });
``` 

 
## Async loading
 
```
<script>
    window.OpenEd.api.oninit = function () {
        //OpenEd is loaded and ready to use
    }
</script>
<script src="oauth.js" async="true"></script>
```
 
## OpenEd Implicit Flow
 
1. User goes to your app
2. User sends an action to login with OpenEd Provider
3. Your app opens popup window with opened
3. After successful signin you will be redirect to your app with access token
4. Send a request to validate token (please, read Security issues)
5. You can access OpenEd API with the received token
 
![OAuth Implict Flow](https://d82yecxzdbhgs.cloudfront.net/dev_artefacts/images/tokenflow.png)
 
## Security issues
 
Please read [this](http://technotes.iangreenleaf.com/posts/closing-a-nasty-security-hole-in-oauth.html)
