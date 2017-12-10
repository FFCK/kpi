// Cachejs is a javascript library that uses LocalStorage and SessionStorage for cache datas on your application.
// https://github.com/ozee31/cachejs


var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
var Cachejs = {};

Cachejs.Engine = (function (undefined) {

    function Cachejs() {};

    /**
     * Return current storage object
     * @return {object} sessionStorage || localStorage
     */
    Cachejs.getStorage = function() {
        return this.storage;
    };

    /**
     * Get complete element by key with value, date, expiration and readonly options, remove if expired
     * @param  {string} key
     * @return {object} or null
     */
    Cachejs.getData = function(key) {

        var data = _get(this.storage, key);

        if ( _isExpired(data) ) {
            _remove(this.storage, key);
            return null;
        }

        return data;
    };

    /**
     * Get only value, remove if expired
     * @param  {string} key
     * @return {mixed} string, array, object...
     */
    Cachejs.get = function(key) {
        var data = this.getData(key);
        return (data) ? data[self.VALUE] : null;
    }

    /**
     * Add the key to the storage, or update that key's value if it already exists
     * @param {string} key      : name of the key you want to create/updat
     * @param {mixed} value     : value you want to give the key you are creating/updating (string, int, array, object...)
     * @param {mixed} expire    : (int) the data expires in x seconds || (falsy) no expiration
     * @param {mixed} readOnly  : (true) prohibit modification || (falsy)
     * @return {bool}
     */
    Cachejs.set = function(key, value, expire, readOnly) {

        var currentData = this.getData(key);

        if ( currentData && _isReadonly(currentData) )
            return false;

        var data = {};
        var now  = time();

        data[self.VALUE]   = value;
        data[self.CREATED] = now;

        if ( expire ) {
            expire = parseInt(expire, 10);

            if ( ! expire )
                return false;

            data[self.EXPIRED] = now + expire;
        }

        if ( readOnly )
            data[self.READ_ONLY] = true;

        data = JSON.stringify(data);

        try {
            this.storage.setItem(key, data);
            return true;
        } catch(e) {
            return false;
        }
    };

    /**
     * Remove data if don't readonly
     * @param  {string} key
     * @return {bool}
     */
    Cachejs.remove = function(key) {
        var currentData = this.getData(key);

        if ( currentData && _isReadonly(currentData) )
            return false;

        return _remove(this.storage, key);
    };

    /**
     * Remove all datas
     * @return true
     */
    Cachejs.clear = function() {
        this.storage.clear();
        return true;
    };

    /**
     * indicates whether the value has expired
     * @param  {string} key
     * @return {Boolean}
     */
    Cachejs.isExpired = function(key) {
        return _isExpired( _get(this.storage, key) );
    };

    /**
     * indicates whether the value is readonly
     * @param  {string} key
     * @return {Boolean}
     */
    Cachejs.isReadonly = function(key) {
        return _isReadonly( _get(this.storage, key) );
    };



    /** Private functions */

        /**
         * Get current timestamp in second
         * @return {int}
         */
        var time = function () {
            return Math.floor(Date.now() / 1000);
        };

        /**
         * Get complete element by key with value, date, expiration and readonly options
         * @param  {object} storage : object storage
         * @param  {string} key
         * @return {object} or null
         */
        var _get = function (storage, key) {
            try {
                var data = storage.getItem(key);

                if ( ! data )
                    return null;
                try {
                    return JSON.parse(data);
                } catch(e) {
                    var _data = {};
                    _data[self.VALUE] = data;

                    return _data;
                }
            } catch(e) {
                return null;
            }
        };

        /**
         * indicates whether the value has expired
         * @param  {string} key
         * @return {Boolean}
         */
        var _isExpired = function (data) {

            if ( ! data )
                return null;

            return ( time() > data[self.EXPIRED] );
        };

        /**
         * indicates whether the value is readonly
         * @param  {string} key
         * @return {Boolean}
         */
        var _isReadonly = function (data) {

            if ( ! data )
                return null;

            return ( data[self.READ_ONLY] == true );
        };

        /**
         * Remove data
         * @param {object} storage : object storage
         * @param  {string} key
         * @return {bool}
         */
        var _remove = (function (storage, key) {
            storage.removeItem(key);
            return true;
        });

    Cachejs.storage;
    var self = Cachejs;

    /** Constantes */
        Cachejs.VALUE     = 0;
        Cachejs.CREATED   = 1;
        Cachejs.EXPIRED   = 2;
        Cachejs.READ_ONLY = 3;

    return Cachejs;
})(undefined);;Cachejs.Local = (function (_super) {
   __extends(CachejsLocal, _super);

   function CachejsLocal() {
       _super.call(this);
   }

   CachejsLocal.storage = localStorage;

   return CachejsLocal;
})(Cachejs.Engine);;Cachejs.Session = (function (_super) {
   __extends(CachejsSession, _super);

   function CachejsSession() {
       _super.call(this);
   }

   CachejsSession.storage = sessionStorage;

   return CachejsSession;
})(Cachejs.Engine);