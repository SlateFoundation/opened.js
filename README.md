# opened-oauth.js

This JavaScript library lets you integrate with the OpenEd resource library from your web application pages. This JavaScript library focuses on the process of using OpenEd as an OAuth provider to login to OpenEd and then perform actions such as queries of the resource library.

## Table of content
1. [Getting Started](#getting-started)
  - [Setup your application](#setup-your-application)
  - [Getting API keys for your application](#getting-api-keys-for-your-application)
  - [Working with OpenEd API](#working-with-opened-api)
2. [Exmaples](#examples)
3. [Methods](#methods)
  - [init](#init)
  - [login](#login)
  - [logout](#logout)
  - [request](#request)
  - [verifyToken](#verifytoken)
4. [API endpoints](#api-endpoints)
  - [Me](#me)
  - [Resources](#resources)
5. [OpenEd API Events](#opened-api-events)
  - [Event Methods](#event-methods)
  - [Auth Events](#auth-events)
6. [Async loading](#async-loading)
7. [OpenEd Implicit Flow](#opened-implicit-flow)
8. [Security issues](#security-issues)

## Getting Started

### Setup your application
- Create an empty html-page on your server
- Add this simple javascript code to your page
```html
<script>
    var hash = document.location.hash;
    if (hash) {
	    window.opener.OpenEd.api._setToken(hash);
    }
    window.close();
</script>
```
*Note*: The URL of created page is required in order to get your API keys

### Getting API keys for your application
- Indicate that you agree to [API terms](http://developers.opened.io/)
- Send an email request to api@opened.io. Request email should contain: 
  - your application URL
  - your application callback script URL (this is the URL of the page you created in step 1)

*Note*: For more info follow this [page](http://developers.opened.io/)

### Working with OpenEd API

- include opened-oauth.js to your web-site scripts
```html
<script src="opened-oauth.js"></script>
```
- initialize OpenEd API with this simple code:
```javascript
window.OpenEd.api.init(
    {client_id: 'you will get this with your app keys', redirect_uri: 'your application callback url'},
    function () {
        // A callback function that is called when OpenEd API is fully initialized
        // ...
        // Your code here
        // ...
    }
); 
```
- Call OpenEd API *login* method to open popup window with signin flow
```javascript
// Subscribe on user successfully signin event
window.OpenEd.api.on('auth.userLoggedIn', function () {
    // A callback function that is called when is successfully signin through OpenEd
    // ...
    // Your code here
    // ...
});
// Trigger signin popup window
window.OpenEd.api.login()
```
- Start using OpenEd API
```javascript
// Request for current user info
window.OpenEd.api.request('/users/me.json', null, function (user) {
    // Received current user info
    console.log(user);
});
```
## Examples
For more details you can checkout OpenEd API commented example applications.

Examples:
- [Simple example](/index.html)
- [Event-based example](/events-based-example.html)

*Note*: We recommend following the event-based technic for more advanced futures of the API
## Methods
### init
arguments - **initOptions**, **callback**

Initializes the OpenEd API. You should pass your client id and redirect_uri.
This is require to execute subsequent calls with the API.

#### Arguments:
**initOptions:**
 - **client_id** - String, required

 Your client application id. For more info on how to get client id - please visit [this page](http://developers.opened.io/)
 
 *Example*: 'fj34892fj20984ujgf3029guj89324ujgt09u4g'
 
 - **redirect_uri** - String, required

 Your app's callback URL. 
 
 *Example*: 'https://example.com/oauth-callback/'

 - **status** - Boolean, default: false

 On initialize finished checks if user is already has access to OpenEd API

**callback** - (optional) callback function. Fired when OpenEd API is fully initialized.

#### Example
```javascript
window.OpenEd.api.init({
    client_id: 'your client id',
    redirect_uri: 'your callback url'
}, function(){
    //OpenEd is initialized here
    // ...
    // Your code here
    // ...
});
```
 
### login
arguments - **callback**

Runs OpenEd OAuth flow. Opens a popup with OpenEd signin/signup capability. Redirects on success(sets the token).
You can pass a callback argument, which will be called after user successfully grants token.
The best practice is to use [event system](#opened-api-events).
#### Arguments:
 
**callback(error)** - (optional) function
 
A callback function that fires on error/success
 
argument *error* is empty if success
#### Example
```javascript
window.OpenEd.api.login(function (err) {
    if (err) {
        // something went wrong
        console.log(err);
    } else {
        // User signed in successfully
        // ...
        // Your code here
        // ...
    }

});
```
### logout
arguments - **callback**

Revokes current user OAuth access_token. Makes API inaccessible by current user OAuth access_token.
You can pass a callback argument that is called when token rejected successfully, 
but the best practice is to use [event system](#opened-api-events).
#### Arguments:
**callback(error)** - (optional) function

A callback function that fires on error/success
 
argument *error* is empty if success
#### Example
```javascript
window.OpenEd.api.logout(function (err) {
    if (err) {
        // something went wrong
        console.log(err);
    } else {
        // User signed out successfully
    }
});
```

### request
arguments - **apiName**, **data**, **callback**, **errorCallback**

Allows user to communicate with OpenEd API. This is the main method for OpenEd API calls.
Make a request to OpenEd API with OAuth access_token. More info [here](http://docs.opened.apiary.io/)
#### Arguments:
**apiName** - string

API end point name
**data** - object

query data object
**callback(responseData)** - function

argument *responseData* - result data object

**errorCallback(error)** - function

argument *error* - error object
#### Example
```javascript
window.OpenEd.api.request(
    '/users/me.json', 
    null, 
    function (user) {
        // Success request callback
        console.log(user);
    }, 
    function (error) {
        // Error request callback
        console.log(error);
    }
);
```

### verifyToken
arguments - **callback**

Verifies current user token. Can be used to see if the token is outdated or invalid.
Also this method verifies the token based on our application clients id.

#### Arguments:
**callback(error)** - (optional) function
argument *error* is empty on success

#### Example
```javascript
window.OpenEd.api.verifyToken(function (err) {
  if (err) {
    console.log('Sorry token is invalid');
  } else {
    console.log('Nice! Your token is perfect');
  }
});
```


## API endpoints
Full Api documentation can be found [here](http://docs.opened.apiary.io/)
 
### Me
URL - **/users/me.json**
 
Returns current user object
 
#### Example: 
 
##### request:
```javascript
window.OpenEd.api.request('/users/me.json', null, function (user){
    // Your current user info
    console.log(user);
});
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
 
### Rosources
URL - **/resources.json**
 
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
- **resource_types** (string, optional ) - array of resource types which can be "video", "game", "assessment", "question"
 
#### Example:
 
##### request:
```javascript
window.OpenEd.api.request('/resources.json', {
    offset: 10,
    limit: 10,
    descriptive: 'keyword search'
}, function (resources) {
    // The results of your query
    console.log(resources);
});
```
##### response:
```json
{
    "meta":{
        "pagination":{
            "offset":10,
            "total_entries":894208,
            "entries":10,
            "limit":10
        }
     },
     "resources":[
        {"standard_idents":["4.NBT.1"],"grade_idents":["4"],"grades_range":"4","course_id":null,"grade_group_ids":[46],"featured":false,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[14],"subject_titles":["Number Sense and Operations"],"resource_type":"question","id":1077403,"state":"public","thumb":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","thumbnails":{"mini":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","small":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","medium":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","large":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg"},"contribution_name":"Pearson","publisher":"Pearson","owner_id":null,"share_url":"https://www.opened.io/resources/1077403","rating":5,"is_allowed":false,"is_locked":true,"title":"The 3 in 139 is how many times bigger than the 3 in 53?","description":"","is_premium":true,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1077403"},
        {"standard_idents":["5.NBT.1"],"grade_idents":["5"],"grades_range":"5","course_id":null,"grade_group_ids":[46],"featured":false,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[14,63],"subject_titles":["Number Sense and Operations","Mathematics"],"resource_type":"question","id":1100666,"state":"public","thumb":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","thumbnails":{"mini":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","small":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","medium":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","large":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png"},"contribution_name":"Houghton Mifflin Harcourt","publisher":"Houghton Mifflin Harcourt","owner_id":null,"share_url":"https://www.opened.io/resources/1100666","rating":5,"is_allowed":false,"is_locked":true,"title":"The 3 in the number 124.035 represents the ______...","description":"","is_premium":true,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1100666"},
        {"standard_idents":["1997.CA.7.MG.3.6"],"grade_idents":["7"],"grades_range":"7","course_id":null,"grade_group_ids":[47],"featured":false,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[63],"subject_titles":["Mathematics"],"resource_type":"question","id":1102471,"state":"public","thumb":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","thumbnails":{"mini":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","small":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","medium":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","large":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png"},"contribution_name":"Houghton Mifflin Harcourt","publisher":"Houghton Mifflin Harcourt","owner_id":null,"share_url":"https://www.opened.io/resources/1102471","rating":5,"is_allowed":false,"is_locked":false,"title":"A 3-dimensional cube is shown below.","description":"","is_premium":true,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1102471"},
        {"standard_idents":["G.GPE.7"],"grade_idents":["10"],"grades_range":"10","course_id":null,"grade_group_ids":[51,80],"featured":false,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[1,63],"subject_titles":["Geometry","Mathematics"],"resource_type":"question","id":1096588,"state":"public","thumb":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","thumbnails":{"mini":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","small":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","medium":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png","large":"https://s3.amazonaws.com/opened/contributions/riverside/assess_2_know.png"},"contribution_name":"Houghton Mifflin Harcourt","publisher":"Houghton Mifflin Harcourt","owner_id":null,"share_url":"https://www.opened.io/resources/1096588","rating":5,"is_allowed":false,"is_locked":true,"title":"A 3-dimensional object is shown in 3 views below.","description":"","is_premium":true,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1096588"},
        {"standard_idents":["A.APR.3"],"grade_idents":["9","10","11","12"],"grades_range":"9-12","course_id":null,"grade_group_ids":[49,48],"featured":false,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[2],"subject_titles":["Algebra"],"resource_type":"question","id":1080309,"state":"public","thumb":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","thumbnails":{"mini":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","small":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","medium":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","large":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg"},"contribution_name":"Brandon DormanOpen","publisher":"Brandon DormanOpen","owner_id":null,"share_url":"https://www.opened.io/resources/1080309","rating":5,"is_allowed":false,"is_locked":true,"title":"The 3rd-degree polynomial function ","description":"","is_premium":true,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1080309"},
        {"standard_idents":["S.CP.9"],"grade_idents":["9","10","11","12"],"grades_range":"9-12","course_id":null,"grade_group_ids":[53,80],"featured":false,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[3,63],"subject_titles":["Statistics and Probability","Mathematics"],"resource_type":"question","id":1080538,"state":"public","thumb":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","thumbnails":{"mini":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","small":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","medium":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","large":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg"},"contribution_name":"Pearson","publisher":"Pearson","owner_id":null,"share_url":"https://www.opened.io/resources/1080538","rating":5,"is_allowed":false,"is_locked":true,"title":"A 4-digit number is formed from using the digits...","description":"","is_premium":true,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1080538"},
        {"standard_idents":["A.CED.1"],"grade_idents":["9","10","11","12"],"grades_range":"9-12","course_id":null,"grade_group_ids":[49,48,80],"featured":false,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[2,63],"subject_titles":["Algebra","Mathematics"],"resource_type":"question","id":1080526,"state":"public","thumb":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","thumbnails":{"mini":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","small":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","medium":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg","large":"https://s3.amazonaws.com/opened/contributions/pearson/pearson_thumb.jpeg"},"contribution_name":"Pearson","publisher":"Pearson","owner_id":null,"share_url":"https://www.opened.io/resources/1080526","rating":5,"is_allowed":false,"is_locked":false,"title":"A 4.5 L solution of alcohol and water contains 25% alcohol.","description":"","is_premium":true,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1080526"},
        {"standard_idents":["4.MD.2"],"grade_idents":["4"],"grades_range":"4","course_id":null,"grade_group_ids":[46],"featured":false,"embeddable":false,"area_ids":[1],"area_titles":["Mathematics"],"subject_ids":[6,63],"subject_titles":["Measurement \u0026 Data","Mathematics"],"resource_type":"question","id":1071646,"state":"public","thumb":"https://s3.amazonaws.com/opened/resource_types/images/question_freeresponse.png","thumbnails":{"mini":"https://s3.amazonaws.com/opened/resource_types/images/question_freeresponse.png","small":"https://s3.amazonaws.com/opened/resource_types/images/question_freeresponse.png","medium":"https://s3.amazonaws.com/opened/resource_types/images/question_freeresponse.png","large":"https://s3.amazonaws.com/opened/resource_types/images/question_freeresponse.png"},"contribution_name":"Janet Woodthorpe","publisher":"Janet Woodthorpe","owner_id":8611,"share_url":"https://www.opened.io/resources/1071646","rating":5,"is_allowed":true,"is_locked":true,"title":"A 5 gallon bottle of mountain spring water costs...","description":"A 5 gallon bottle of mountain spring water costs $6.50. A package including 4 single-quart packets of mountain spring water costs $1.80. If someone needs to buy 5 gallons of mountain spring water, how much money do they save by buying the 5 gallon bottle rather than packets of quarts? ","is_premium":null,"duration":null,"my_rating":null,"safe_url":"https://www.opened.io/resources/1071646"},
     ]
}
```

## OpenEd API Events
OpenEd Oauth library has basic event support for all general process.
### Event Methods
#### on(eventName, callback)
This method add an event handler(callback) for eventName.
##### Example
```javascript
var eventHandler = function () {
    //Note: that arguments will be filled depending on event type
}
window.OpenEd.api.on('somethingHappend', eventHandler);
```
#### off(eventName, callback)
Removes event handler(callback) from the event namespace(eventName)
##### Example
```javascript
window.OpenEd.api.off('somethingHappend', eventHandler);
```
### Auth Events
#### userLoggedIn
This event triggers when current user grants access to API. Its fired on ``` login ``` right before the callback. And also it can be fired when user already had hes token in session and it was verified successfully.
##### Example
```javascript
window.OpenEd.api.on('auth.userLoggedIn', function () {
    // ...
    // Your code here
    // ...
});
``` 

 
## Async loading
 
```html
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
