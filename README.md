# opened-oauth.js
## Getting started
1. Create a callback route on your website (you can see an exmaple at callback.html)
2. Get application key(redirect_uri must be an url that you created in step 1)
3. Download and include opened-oauth.js to your web-site
4. ``` window.OpenEd.api.init({client_id: 'your app id here', redirect_uri: 'your application success callback uri'}) ```
5. ``` window.OpenEd.api.login() ``` will open a popup with OpenEd oauth flow
6. After success u have your token avaivable via ``` window.OpenEd.api.getToken() ```
7. Now u can access OpenEd API endpoints  via ``` window.OpenEd.api.request() ```

## Methods
### init(initOptions)
```
window.OpenEd.api.init(initOptions)
```
initOptions:
 - client_id - String, required

 Your client app id. Exmaple: 'fj34892fj20984ujgf3029guj89324ujgt09u4g'
 - redirect_uri - String, required

 Your apps callback url. Exmaple: 'https://exmaple.com/oauth-callback/'

### login(callback)
```
window.OpenEd.api.login(callback)
```
callback(error) - function

error is empty if success

### logout(callback)
```
window.OpenEd.api.logout(callback)
```
callback(error) - function

error is empty if success

### request(apiName, data, callback)
```
window.OpenEd.api.request(apiName, data, callback)
```
callback(responseData) - function

responseData - result data object

## API endpoints

### /current_user.json 

Returns current user object

Exmaple: 

request:
```
window.OpenEd.api.request('/current_user.json', null, callback)
```
response:
```
{
    "current_user":{
        "first_name":"Andrew",
        "last_name":"Saenkov",
        "full_name":"Andrew Saenkov",
        "username":"andrew",
        "email":"andrew@example.com"
     }
}
```

### /resources.json 

Makes a resource query. 

Returns an array of resources and pagination info

Example:

request:
```
window.OpenEd.api.request('/resources.json', {
    offset: 10,
    limit: 10,
    descriptive: 'keyword search',
    grade: 'k',
    resource_type: 'game',
    contribution_name: 'Pearson'
}, callback)
```
response:
```
{"meta":
    {"pagination":
        {
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