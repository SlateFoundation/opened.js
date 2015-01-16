;(function (root) {

  'use strict';

  root.OpenEd = root.OpenEd || {};

  root.OpenEd.api = {

    tokenPrefix: '_openEd',

    isInited: false,

    apiHost: 'https://openedengine-sandbox1.herokuapp.com',

    openedHost: 'http://local.opened.io:9000',
    
    init: function (options) {
      if (!options || !options.client_id) {
        throw new Error('Bad init options.');
      }
      this.options = options;
      var token = this.getToken();
      if (false && token && options.status) {//not implemented
        //check logged in status
        var self = this;
        this.checkLoginStatus(function () {
          self.isInited = true;
        });
      } else {
          this.isInited = true;
      }
    },

    runOnInit: function () {
      root.OpenEd.oninit && root.OpenEd.oninit()
    },

    login: function (callback) {
      this._lastCallback = callback;
      var params = '?mode=implict';
      var self = this;
      ['client_id', 'redirect_uri'].forEach(function (paramName) {
        var paramValue = self.options[paramName];
        if (paramValue) {
          params += '&' + paramName + '=' + paramValue
        }
      });
      var popup = window.open(this.openedHost + '/oauth/authorize' + params, '_blank', 'width=500, height=300');
      popup.focus && popup.focus();
    },

    logout: function (callback) {
      this.saveToken(null);  
    },

    saveToken: function (tokenData) {
      for (var name in tokenData) {
          localStorage.setItem(this.tokenPrefix + '.' + name, tokenData[name]);
      }
      
    },

    getToken: function () {
      return localStorage.getItem(this.tokenPrefix + '.access_token');
    },

    xhr: function (options) {
      var xmlhttp=new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          options.success(JSON.parse(xmlhttp.responseText));
        }
      }

      xmlhttp.open(options.type || 'GET', options.url, true);
      if (options.headers) {
        for (var name in options.headers) {
          xmlhttp.setRequestHeader(name, options.headers[name]);
        }
        
      }
      xmlhttp.send(this.prepareReqData(options.data));
    },

    request: function (api, data, callback) {
        var self = this;
        this.xhr({
            url: this.apiHost + api,
            data: data,
            success: callback,
            headers: {
                Authorization: 'Bearer ' + self.getToken()
            }
        });
    },

    prepareReqData: function (data) {
      return data;
    },
    /*
    checkLoginStatus: function (callback) {
      callback(false);
      return false;
      
      var token = this.getToken();
      if (token) {
        this.xhr({
          type: 'post',
          url: '',
          data: {},
          success: function (response) {
            
          }
        });
      }
      
    },
    */
    _setToken: function (token) {
        this.saveToken(this.parseToken(token));
        this._lastCallback && this._lastCallback();
    },

    parseToken: function (token) {
        var params = token.substr(1);
        var result = {};
        params.split('&').forEach(function (paramPairStr) {
            var paramPair = paramPairStr.split('=');
            result[paramPair[0]] = paramPair[1];
        });
        return result;
    }

  };
  
  root.OpenEd.api.runOnInit();
    
})(this);
