!function(e){var t={};function n(r){if(t[r])return t[r].exports;var i=t[r]={i:r,l:!1,exports:{}};return e[r].call(i.exports,i,i.exports,n),i.l=!0,i.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)n.d(r,i,function(t){return e[t]}.bind(null,i));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/bundles/dfwconnector/",n(n.s="seA+")}({"+eZA":function(e,t){function n(e){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function i(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function o(e,t){return(o=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function c(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=u(e);if(t){var i=u(this).constructor;n=Reflect.construct(r,arguments,i)}else n=r.apply(this,arguments);return s(this,n)}}function s(e,t){if(t&&("object"===n(t)||"function"==typeof t))return t;if(void 0!==t)throw new TypeError("Derived constructors may only return object or undefined");return function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e)}function u(e){return(u=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var a=Shopware.Classes.ApiService,l=Shopware.Application,f=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&o(e,t)}(l,e);var t,n,s,u=c(l);function l(e,t){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"api-bridge-test";return r(this,l),u.call(this,e,t,n)}return t=l,(n=[{key:"check",value:function(e){var t=this.getBasicHeaders({});return this.httpClient.post("_action/".concat(this.getApiBasePath(),"/updatekey"),e,{headers:t}).then((function(e){return a.handleResponse(e)})).catch((function(e){return e}))}}])&&i(t.prototype,n),s&&i(t,s),Object.defineProperty(t,"prototype",{writable:!1}),l}(a);l.addServiceProvider("apiUpdateKey",(function(e){var t=l.getContainer("init");return new f(t.httpClient,e.loginService)}))},"3blL":function(e,t){function n(e){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function i(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function o(e,t){return(o=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function c(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=u(e);if(t){var i=u(this).constructor;n=Reflect.construct(r,arguments,i)}else n=r.apply(this,arguments);return s(this,n)}}function s(e,t){if(t&&("object"===n(t)||"function"==typeof t))return t;if(void 0!==t)throw new TypeError("Derived constructors may only return object or undefined");return function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e)}function u(e){return(u=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var a=Shopware.Classes.ApiService,l=Shopware.Application,f=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&o(e,t)}(l,e);var t,n,s,u=c(l);function l(e,t){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"api-bridge-test";return r(this,l),u.call(this,e,t,n)}return t=l,(n=[{key:"check",value:function(e){var t=this.getBasicHeaders({});return this.httpClient.post("_action/".concat(this.getApiBasePath(),"/verify"),e,{headers:t}).then((function(e){return a.handleResponse(e)})).catch((function(e){return e}))}}])&&i(t.prototype,n),s&&i(t,s),Object.defineProperty(t,"prototype",{writable:!1}),l}(a);l.addServiceProvider("apiBridgeTest",(function(e){var t=l.getContainer("init");return new f(t.httpClient,e.loginService)}))},"4KSo":function(e){e.exports=JSON.parse('{"api-bridge-test-button":{"title":"Bridge Test","success":"Bridge wurde erfolgreich getestet","error":"Verbindung konnte nicht hergestellt werden. Bitte prüfe die Zugangsdaten"},"api-update-key-button":{"title":"Update Store Key","success":"Store Key Updated","error":"Error"}}')},"7U3P":function(e){e.exports=JSON.parse('{"api-bridge-test-button":{"title":"Bridge Test","success":"Bridge was successfully tested","error":"Connection could not be established. Please check the access data"},"api-update-key-button":{"title":"Update Store Key","success":"Store Key Updated","error":"Error"}}')},"seA+":function(e,t,n){"use strict";n.r(t);n("3blL"),n("+eZA");var r=Shopware,i=r.Component,o=r.Mixin;i.register("api-bridge-test-button",{template:'<div style="display: block; margin-bottom: 20px">\n    <sw-button-process\n        :isLoading="isLoading"\n        :processSuccess="isSaveSuccessful"\n        @process-finish="saveFinish"\n        @click="check"\n    >{{ $tc("api-bridge-test-button.title") }}</sw-button-process>\n</div>',props:["label"],inject:["apiBridgeTest","systemConfigApiService"],mixins:[o.getByName("notification")],data:function(){return{config:{},configKey:"dfwconnector.config.StoreKey",storeKeyElem:{},isLoading:!1,isSaveSuccessful:!1}},methods:{saveFinish:function(){this.isSaveSuccessful=!1},check:function(){var e=this;this.isLoading=!0;var t="";document.getElementById("dfwconnector.config.StoreKey")&&(t=document.getElementById("dfwconnector.config.StoreKey").value),""!==t?this.apiBridgeTest.check(t).then((function(t){if(t.success)e.isSaveSuccessful=!0,e.createNotificationSuccess({title:e.$tc("api-bridge-test-button.title"),message:e.$tc("api-bridge-test-button.success")});else{var n,r,i=null!==(n=null!==(r=t.message)&&void 0!==r?r:t.error)&&void 0!==n?n:"Check Bridge failed!";e.createNotificationError({title:e.$tc("api-bridge-test-button.title"),message:i})}e.isLoading=!1})):(this.createNotificationError({title:this.$tc("api-bridge-test-button.title"),message:"StoreKey not defined!"}),this.isSaveSuccessful=!0,this.isLoading=!1)}}});var c=Shopware,s=c.Component,u=c.Mixin;s.register("api-update-key-button",{template:'<div style="display: block;">\n    <sw-button-process\n        :isLoading="isLoading"\n        :processSuccess="isSaveSuccessful"\n        @process-finish="saveFinish"\n        @click="updatekey"\n    >{{ $tc("api-update-key-button.title") }}</sw-button-process>\n</div>',props:["label"],inject:["apiUpdateKey","systemConfigApiService"],mixins:[u.getByName("notification")],data:function(){return{config:{},configKey:"dfwconnector.config.StoreKey",storeKeyElem:{},isLoading:!1,isSaveSuccessful:!1}},computed:{pluginConfig:function(){var e,t=this;if(this.configKey="dfwconnector.config.StoreKey",this.$parent.$parent.$parent.$children.length>1)var n=this.$parent.$parent.$parent.$children;else n=this.$parent.$parent.$parent.$children[0].$children;return n.map((function(t,n){void 0!==t.$children[0]&&void 0!==t.$children[0].$attrs.name&&t.$children[0].$attrs.name.includes("StoreKey")&&(e=t.$children[0])})),void 0!==e&&void 0!==e.$refs.component?this.storeKeyElem=e.$refs.component:this.storeKeyElem=e,this.systemConfigApiService.getValues(this.configKey.replaceAll(".StoreKey","")).then((function(e){t.config=e}))}},methods:{saveFinish:function(){this.isSaveSuccessful=!1},updatekey:function(){var e=this;this.isLoading=!0,this.apiUpdateKey.check(this.pluginConfig).then((function(t){if(t.success)e.isSaveSuccessful=!0,e.createNotificationSuccess({title:e.$tc("api-update-key-button.title"),message:e.$tc("api-update-key-button.success")}),e.$set(e.config,e.configKey,t.storekey),e.systemConfigApiService.saveValues(e.config,null).then((function(){e.isLoading=!1,e.storeKeyElem?e.storeKeyElem.$emit("input",t.storekey):document.getElementById("dfwconnector.config.StoreKey").value=t.storekey}));else{var n,r,i=null!==(n=null!==(r=t.message)&&void 0!==r?r:t.error)&&void 0!==n?n:"Update StoreKey failed!";e.createNotificationError({title:e.$tc("api-update-key-button.title"),message:i})}}))}}});var a=n("4KSo"),l=n("7U3P");Shopware.Locale.extend("de-DE",a),Shopware.Locale.extend("en-GB",l)}});