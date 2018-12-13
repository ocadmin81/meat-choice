var awLnFilterTypeAbstract = Class.create();
awLnFilterTypeAbstract.prototype = {

    startObservers: function() {
        var fn = this.clear.bindAsEventListener(this);
        var ddEl = this.container.up('dd');
        ddEl.observe('aw_ln:clear_filter', fn);
        this._stopObservingFns.push(function(){
            ddEl.stopObserving('aw_ln:clear_filter', fn);
        });
    },

    stopObservers: function() {
        this._stopObservingFns.each(function(fn){
            fn();
        });
        this._stopObservingFns = [];
    },

    getParams: function() {
        return Form.serializeElements(this._getInputList(), true);
    },

    getKey: function() {
        return this.config.name;
    },

    _getInputList: function() {
        return this.container.select('input[name="' + this.config.name + '"]');
    }
}