var awLnFilterTypeRange = Class.create(awLnFilterTypeAbstract, {
    _stopObservingFns: [],

    initialize: function(config) {
        this.config = config;
        this.container = $$(config.containerElSelector).first();
    },

    startObservers: function() {
        var me = this;
        this._getInputList().each(function(el){
            var fn = awLnUpdaterInstance.onFilterChange.bindAsEventListener(awLnUpdaterInstance, el);
            el.observe('aw_ln:input_change', fn);
            me._stopObservingFns.push(function(){
                el.stopObserving('aw_ln:input_change', fn);
            });
        });

        awLnFilterTypeAbstract.prototype.startObservers.bind(this)();
    },

    clear: function() {
        this._getInputList().each(function(el){
            el.setValue('');
            el.setAttribute('disabled', 'disabled');
        });
    }
});