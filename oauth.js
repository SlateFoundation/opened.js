;(function (root) {

  'use strict';

  root.OpenEd = root.OpenEd || {};

  root.OpenEd.api = {

    tokenPrefix: '_openEd',

    apiHost: 'https://api-staging.opened.io',

    openedHost: 'http://staging.opened.io',

    _events: {},

    on: function (eventName, callback) {
      if (eventName && callback) {
        if (!this._events[eventName]) {
          this._events[eventName] = [];
        }
        this._events[eventName].push(callback);
      }
    },

    off: function (eventName, callback) {
      if (eventName && callback) {
        if (this._events[eventName]) {
          var callbackIndex = this._events[eventName].indexOf(callback);
          if (callbackIndex >= 0) {
            this._events[eventName].slice(callbackIndex, 1);
          }
        }
      }
    },

    trigger: function () {
      var args = Array.prototype.slice.call(arguments);
      var eventName = args.slice(0)[0];
      var eventsArr = this._events[eventName];
      var data = args.slice(1);
      if (eventsArr && eventsArr.length) {
        eventsArr.forEach(function (callback) {
          callback.apply(null, data);
        });
      }
    },
    
    init: function (options, callback) {
      if (!options || !options.client_id) {
        throw new Error('Bad init options.');
      }
      this.options = options;
      var token = this.getToken();
      if (token && options.status) {
        //check logged in status
        var self = this;
        this.checkLoginStatus(function () {
          self.trigger('auth.userLoggedIn');
          callback && callback();
        });
      } else {
        callback && callback();
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
      var self = this;
      var token = self.getToken();
      if (token) {
        this.xhr({
          type: 'POST',
          url: self.apiHost + '/oauth/revoke',
          data: {token: token},
          success: function () {
            self.resetToken();  
            callback && callback();
          },
          error: function () {
            self.resetToken();  
            callback && callback();
          }
        });
      } else {
        self.resetToken();  
        callback && callback();
      }
    },

    resetToken: function () {
      this.saveToken({access_token: null});  
      this.trigger('auth.userSignedOut');
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
        if (xmlhttp.readyState==4) {
          if (xmlhttp.status==200) {
            options.success(JSON.parse(xmlhttp.responseText));
          } else if (xmlhttp.status>=400) {
            options.error && options.error(JSON.parse(xmlhttp.statusText));
          }
        }
      }
      var type = options.type || 'GET';
      var url = options.url;
      if (type === 'GET') {
        var urlData = '?';
        var params = [];
        for (var a in options.data) {
          params.push(a + '=' + options.data[a]);
        }
        if (params.length) {
          url += '?' + (params.join('&'));
        }
      }
      xmlhttp.open(type, url, true);
      if (options.headers) {
        for (var name in options.headers) {
          xmlhttp.setRequestHeader(name, options.headers[name]);
        }
        
      }
      if (type !== 'GET') {
        xmlhttp.send(this.prepareReqData(options.data));
      } else {
        xmlhttp.send();
      }
    },

    request: function (api, data, callback, errorCallback) {
      var self = this;
      this.xhr({
        url: this.apiHost + api,
        data: data,
        success: callback,
        error: errorCallback,
        headers: {
          Authorization: 'Bearer ' + self.getToken()
        }
      });
    },

    verifyToken: function (callback) {
      var self = this;
      if (this.checkTokenDate()) {
        this.request('/oauth/token/info', null, function (token) {
          if (token && token.application && token.application.uid && token.application.uid === self.options.client_id) {
            callback();
          } else {
            self.resetToken();
            callback(new Error('Wrong client id'));
          }
        }, function (error) {
          self.resetToken();
          callback(error);
        });
      } else {
        callback(new Error('token has expired'));
      }
    },

    prepareReqData: function (data) {
      return JSON.stringify(data);
    },

    checkTokenDate: function () {
      var tokenDate = new Date(parseInt(localStorage.getItem(this.tokenPrefix + '.expires_in')));
      var now = this.now();
      return now.getTime() < tokenDate.getTime();
    },

    expairDate: function (expairsIn) {
      var date = this.now();
      date.setTime(date.getTime() + (parseInt(expairsIn) * 1000) );
      return date;
    },

    now: function () {
      return new Date();
    },

    checkLoginStatus: function (callback) {
      this.verifyToken(function (err) {
        if (err) {
          callback(err);
        } else {
          callback();
        }
      });
    },

    _setToken: function (token) {
      this.saveToken(this.parseToken(token));
      this.trigger('auth.userLoggedIn', token);
      this._lastCallback && this._lastCallback();
    },

    parseToken: function (token) {
      var params = token.substr(1);
      var result = {};
      params.split('&').forEach(function (paramPairStr) {
        var paramPair = paramPairStr.split('=');
        result[paramPair[0]] = paramPair[1];
      });
      if (result.expires_in) {
        result.expires_in = this.expairDate(result.expires_in).getTime();
      }
      return result;
    }

  };
  
  root.OpenEd.api.runOnInit();
    
})(this);
