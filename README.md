# OpenEd JavaScript API

This JavaScript library lets you integrate with the OpenEd resource library from your web or mobile application. It provides the client with two ways of authenticating the user with the OpenEd system.  Your API calls should then always use the authenticated user token to interact with the OpenEd API (such as querying for resources).  

  - [Server to Server](SIGNED-SERVER-REQUEST.md) (create a user on OpenEd.com and login in via a Signed Server Request)
  - [Client-to-Client](CLIENT-TO-CLIENT.md) (allowing user to interact directly with the OpenEd-provided sign-in dialog)

If you choose to create an OpenEd user yourself, you can pass the identity of the user authenticated by your web app directly into OpenEd. This method is described [here](SIGNED-SERVER-REQUEST.md).  This provides a seamless experience for the user.   

For those interested in the the Client-to-Client approach where OpenEd pops up an authentication/account creation JavaScript dialog, this is described [here](CLIENT-TO-CLIENT.md). This approach can be a bit less work for the integrating developer.  

After creating a teacher user you can create classes and students for them using the API calls below. 

### Using the REST API after successful login

Once authentication has been completed, your web app can use the OpenEd REST API to query the resources library. For example:

```javascript
search_params = {
  standard: 'A.APR.1'
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

Full documentation of the REST API can be found [here](http://docs.opened.apiary.io/).
 
### /resources.json 
 
Makes a resources query request. 
 
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
    standard: 'A.APR.1'
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
{
  "meta":{
    "pagination":{
      "offset":0,
      "total_entries":96,
      "entries":5,
      "limit":5
    }
  },
  "resources":[
    {"contribution_name":"LearnZillion",
      "rating":3,
      "title":"Determine which operations are closed for polynomials",
      "description":"In this lesson you will learn what operations are closed for polynomials by applying the definition of a polynomial to each operation."},
    {"contribution_name":"Khan Academy",
      "rating":5,
      "title":"Example Problem - Adding Polynomials",
      "description":"Learn more: http://www.khanacademy.org/video?v=Oe1PKI_6-38\nu11_l2_t2_we1 Adding Polynomials\nContent provided by TheNROCproject.org - (c) Monterey Institute for Technology and Education"},
    {"contribution_name":"patrickJMT",
      "rating":5,
      "title":"Polynomials: Adding and Subtracting",
      "description":"In this video, the instructor defines polynomials, then gives some examples of how to add and subtract polynomials.\u00a0 He works through the steps of example problems to explain how to work add and subject polynomials.\u00a0 His explanations are clear and thorough and it would be easy to follow along as he explains."},
    {"contribution_name":"Brandon DormanOpen",
      "rating":5,
      "title":"APR 1",
      "description":null},
    {"contribution_name":"FunnyVideoHaydar",
      "rating":5,
      "title":"A.APR.1 - Calculating with Polynomials - YouTube",
      "description":""}
    ],
  "standards":[
    {"id":9292,
      "identifier":"A.APR.1",
      "title":"Understand that polynomials form a system analogous to the integers, namely, they are closed under the operations of addition, subtraction, and multiplication; add, subtract, and multiply polynomials."},
    {"id":25940,
      "identifier":"SBAC.Math.CR11.2",
      "title":"REASONING with POLYNOMIALS \u0026 RATIONAL EXPRESSIONS"}
  ]
}
```
