var AWLayerednavigationSynchronizeButton = Class.create({
    initialize:function (config) {
        document.observe("dom:loaded", this.init.bind(this));

        this.config = config;

    },

    init:function () {
        this._button = $('aw_layerednavigation_synchronize');
        if (this._button) {
            this._button.observe('click', this.submitSynchronize.bind(this));
            this._loadingMask = $('loading-mask');
            this._msgContainer = $$('.aw_layerednavigation_message').first();
            this._msgContainer.hide();
        }
    },

    submitSynchronize:function () {
        if (confirm(this.config.msgConfirm)) {
            this._loadingMask.show();
            new Ajax.Request(this.config.synchronizeActionUrl, {
                parameters:{},
                onSuccess:function (response) {
                    this._loadingMask.hide();
                    try {
                        var resp = response.responseText.evalJSON();
                        if (typeof(resp.result) != 'undefined') {
                            if (resp.result) {
                                this.showSuccess(this.config.msgSuccess);
                            } else {
                                this.showError((typeof(resp.message) != 'undefined' && resp.message) ? resp.message : this.config.msgFailure);
                            }
                        }
                    } catch (ex) {
                        console.log(ex.getMessage());
                    }
                }.bind(this),
                onFailure:function () {
                    this._loadingMask.hide();
                    this.showError(this.config.msgFailure);
                }.bind(this)
            });
        }
    },

    showSuccess:function (msg) {
        this._msgContainer.removeClassName('error');
        this._msgContainer.addClassName('success');
        this._msgContainer.innerHTML = msg;
        this._msgContainer.show();
        setTimeout(function () {
            this._msgContainer.hide()
        }.bind(this), 5000);
    },

    showError:function (msg) {
        this._msgContainer.removeClassName('success');
        this._msgContainer.addClassName('error');
        this._msgContainer.innerHTML = msg;
        this._msgContainer.show();
        setTimeout(function () {
            this._msgContainer.hide()
        }.bind(this), 5000);
    }
});