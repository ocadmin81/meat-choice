var awLnFilterTypeInput = Class.create(awLnFilterTypeAbstract, {
    _stopObservingFns: [],

    initialize: function(config) {
        this.config = config;
        this.container = $$(config.containerElSelector).first();
    },

    startObservers: function() {
        var me = this;
        me.container.select('label').each(function(el){
            var input = el.down('input');
            if (!input) {
                return;
            }
            var fn = function(e){
                if (input.getAttribute('type') == 'checkbox' && input.getValue()) {
                    input.removeAttribute('checked');
                } else {
                    input.setAttribute('checked', 'checked');
                }
                awLnUpdaterInstance.onFilterChange.bindAsEventListener(awLnUpdaterInstance)(e, input);
                Event.stop(e);
            };
            el.observe('click', fn);
            me._stopObservingFns.push(function(){
                el.stopObserving('click', fn);
            });
        });
        awLnFilterTypeAbstract.prototype.startObservers.bind(this)();
    },

    clear: function() {
        this._getInputList().each(function(el){
            el.removeAttribute('checked');
            el.checked = false;
        });
    }
});