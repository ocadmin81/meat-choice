var awFieldDependence = Class.create({
    initialize:function (config) {
        this.mainFiled = $(config.mainFieldId);
        this.dependenceField = $(config.dependenceFieldId);
        this.dependenceFieldCell = this.dependenceField.up('td');
        this.asteriskSpanField = this.dependenceField.up().up().select('span.required').first();
        this.config = {};
        this.config.message = config.message;
        this.config.available = config.available;
        this.init();
    },

    init:function () {
        this.messageBlock = new Element('p');
        this.messageBlock.update(this.config.message);
        this.messageBlock.setStyle({fontSize: '13px', height: '15px'});
        this.messageBlockCell = new Element('td');
        this.messageBlockCell.addClassName('value');
        this.messageBlockCell.hide();
        this.messageBlockCell.appendChild(this.messageBlock);
        this.dependenceFieldCell.insert({'before': this.messageBlockCell});

        this.process();
        Event.observe(this.mainFiled, 'change', this.process.bind(this));
    },

    process:function() {
        if (this.config.available.indexOf(parseInt(this.mainFiled.value)) === -1) {
            this.dependenceFieldCell.hide();
            this.hideAsteriskSpanField();
            this.hideAdvices();
            this.messageBlockCell.show();
            this.dependenceField.removeClassName('required-entry');
            this.dependenceField.removeClassName('validate-digit');
        } else {
            this.messageBlockCell.hide();
            this.showAsteriskSpanField();
            this.dependenceFieldCell.show();
            this.dependenceField.addClassName('required-entry');
            this.dependenceField.addClassName('validate-digit');
        }
    },

    hideAdvices: function() {
        this.dependenceField.up().select('.validation-advice').each(function(element){ element.hide()});
    },

    hideAsteriskSpanField:function() {
        if (this.asteriskSpanField) {
            this.asteriskSpanField.hide();
        }
    },

    showAsteriskSpanField:function() {
        if (this.asteriskSpanField) {
            this.asteriskSpanField.show();
        }
    }
});