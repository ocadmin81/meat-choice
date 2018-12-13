var awLnFilterTypeFromTo = Class.create(awLnFilterTypeAbstract, {
    _stopObservingFns: [],

    initialize: function(config) {
        this.config = config;
        this.container = $$(config.containerElSelector).first();
        this.isApplied = config.isApplied;
    },

    startObservers: function() {
        var me = this;
        me.container.select('button').each(function(el){
            var fn = function(e){
                me.isApplied = true;
                awLnUpdaterInstance.onFilterChange.bindAsEventListener(awLnUpdaterInstance, el)();
            };
            el.observe('click', fn);
            me._stopObservingFns.push(function(){
                el.stopObserving('click', fn);
            });
        });
        me.container.select('input').each(function(el){
            var fn = function(e){
                if (e.keyCode !== Event.KEY_RETURN) {
                    return;
                }
                me.isApplied = true;
                awLnUpdaterInstance.onFilterChange.bindAsEventListener(awLnUpdaterInstance, el)();
                Event.stop(e);
            };
            el.observe('keydown', fn);
            me._stopObservingFns.push(function(){
                el.stopObserving('click', fn);
            });
        });

        awLnFilterTypeAbstract.prototype.startObservers.bind(this)();
    },

    clear: function() {
        this._getInputList().each(function(el){
            el.setValue('');
            el.setAttribute('disabled', 'disabled');
        });
        this.isApplied = false;
    },

    getParams: function() {
        if (!this.isApplied) {
            return {};
        }
        return Form.serializeElements(this._getInputList(), true);
    }
});