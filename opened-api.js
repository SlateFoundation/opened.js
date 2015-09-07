;(function (root) {

  'use strict';

  root.OpenEd = root.OpenEd || {};

  root.OpenEd.api = {

    tokenPrefix: '_openEd',

    apiHost: 'https://api.opened.io',

    openedHost: 'https://www.opened.com',

    _events: {},

    on: function (eventName, callback) {
      if (eventName && typeof callback === 'function') {
        this._events[eventName] = this._events[eventName] || [];
        this._events[eventName].push(callback);
      }
    },

    off: function (eventName, callback) {
      var eventsArr = this._events[eventName];

      if (eventsArr && typeof callback === 'function') {
        var callbackIndex = eventsArr.indexOf(callback);
        if (callbackIndex !== -1) {
          eventsArr.slice(callbackIndex, 1);
        }
      }
    },

    trigger: function (eventName) {
      var data = Array.prototype.slice.call(arguments).slice(1),
          eventsArr = this._events[eventName] || [];

        eventsArr.forEach(function (callback) {
          callback.apply(null, data);
        });
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
          (typeof callback !== 'function') || callback();
        });
      } else {
        (typeof callback !== 'function') || callback();
      }
    },

    runOnInit: function () {
      (typeof root.OpenEd.oninit !== 'function') || root.OpenEd.oninit()
    },

    silentLogin: function(signedRequest, callback, errorCallback) {
      var self = this;
      self.xhr({
        type: 'POST',
        url: self.apiHost + '/oauth/silent_login',
        raw_data: signedRequest,
        success: function(data){
          self.saveToken(data);
          (typeof callback !== 'function') || callback();
        },
        error: errorCallback
      });
    },

    login: function (callback) {
      if (typeof this.options === 'undefined') {
        throw new Error("You must call init() before calling login()");
      }

      this._lastCallback = callback;

      var params = {
        mode: 'implicit',
        client_id: this.options.client_id,
        redirect_uri: this.options.redirect_uri
      },
      popup = window.open(this.openedHost + '/oauth/authorize' + this.objToQueryString(params), '_blank', 'width=500, height=300');

      if (typeof popup === 'undefined') {
        alert("The OpenEd login popup was blocked by your browser. Please allow popups on this site and try again.");
      } else {
        popup.focus();
      }
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
          },
          headers: {
            Authorization: 'Bearer ' + self.getToken()
          }
        });
      } else {
        self.resetToken();
        callback && callback();
      }
    },

    resetToken: function () {
      localStorage.removeItem(this.tokenPrefix + '.access_token');
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

    parseJSON: function (str) {
      try {
        return JSON.parse(str);
      } catch (e) {
        return null;
      }
    },

    objToQueryString: function (obj) {
      var result = [];

      Object.keys(obj).forEach(function(key) {
        var val = obj[key];

        if (typeof val !== 'undefined' && val !== null) {
          result.push(encodeURIComponent(key) + '=' + encodeURIComponent(val));
        }
      });

      return (result.length === 0) ? '' : '?' + result.join('&');
    },

    xhr: function (options) {
      var xmlhttp = new XMLHttpRequest(),
          self = this,
          response;

      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4) {
          response = self.parseJSON(xmlhttp.responseText);
          if (xmlhttp.status == 200 && response) {
            options.success(response);
          } else if (xmlhttp.status >= 400 && typeof options.error === 'function') {
            options.error(response || { error: 'HTTP ' + xmlhttp.status + ': ' + (xmlhttp.statusText || 'Unknown error') });
          }
        }
      };
      var type = options.type || 'GET';
      var url = options.url;
      if (type === 'GET' && typeof options.data === 'object') {
        url += self.objToQueryString(options.data);
      }
      xmlhttp.open(type, url, true);

      if (type === 'POST') {
        if (options.raw_data){
          xmlhttp.setRequestHeader('Content-Type', 'text/plain');
        }else{
          xmlhttp.setRequestHeader('Content-Type', 'application/json');
        }
      }

      if (options.headers) {
        for (var name in options.headers) {
          xmlhttp.setRequestHeader(name, options.headers[name]);
        }

      }
      if (type !== 'GET') {
        if(options.raw_data){
          xmlhttp.send(options.raw_data);
        }else{
          xmlhttp.send(JSON.stringify(options.data));
        }
      } else {
        xmlhttp.send();
      }
    },

    request: function (api, data, callback, errorCallback) {
      var self = this;
      data.access_token = self.getToken();
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

    checkTokenDate: function () {
      var tokenDate = new Date(parseInt(localStorage.getItem(this.tokenPrefix + '.expires_in'), 10));
      return new Date() < tokenDate;
    },

    expireDate: function (expiresIn) {
      var date = new Date();
      date.setTime(date.getTime() + (parseInt(expiresIn) * 1000, 10));
      return date;
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
      var self = this;
      this.verifyToken(function (err) {
        if (!err) {
          self.trigger('auth.userLoggedIn', token);
        }
        self._lastCallback && self._lastCallback(err);
      });
    },

    parseToken: function (token) {
      var params = token.substr(1);
      var result = {};
      params.split('&').forEach(function(paramPairStr) {
        var paramPair = paramPairStr.split('=');
        result[decodeURIComponent(paramPair[0])] = decodeURIComponent(paramPair[1]);
      });
      if (result.expires_in) {
        result.expires_in = this.expireDate(result.expires_in).getTime();
      }
      return result;
    }

  };

  root.OpenEd.api.runOnInit();

})(this);
