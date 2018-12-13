//============== COLLAPSE =================
var awLNFilterCollapse = Class.create();
awLNFilterCollapse.prototype = {
    initialize: function(config){
        var me = this;
        this.config = config;
        config.columnsCount = config.columnsCount || 1;
        this.targetEl = $$(config.targetElSelector).first();
        this.showTriggerEl = $$(config.collapseShowElSelector).first();
        this.hideTriggerEl = $$(config.collapseHideElSelector).first();
        this.visibleBlockHeight = 0;
        this.targetEl.select('li').each(function(rowEl, index){
            if (index >= parseInt(config.limit * config.columnsCount)) {
                return;
            }
            if (config.columnsCount > 1 && ((index % config.columnsCount) !== 1)) {
                return;
            }
            me.visibleBlockHeight += rowEl.getHeight()
                + Math.max(
                parseInt(rowEl.getStyle('margin-top')),
                parseInt(rowEl.getStyle('margin-bottom'))
            )
            ;
        });
        this.initHandlers();
        this._slideUp();
    },

    initHandlers: function(){
        var me = this;
        this.showTriggerEl.observe('click', function(e){
            me._slideDown();
        });
        this.hideTriggerEl.observe('click', function(e){
            me._slideUp();
        });
    },

    _slideUp: function(){
        this.targetEl.setStyle({
            'height': this.visibleBlockHeight + 'px'
        });
        this.hideTriggerEl.setStyle({
            'display': 'none'
        });
        this.showTriggerEl.setStyle({
            'display': 'inline-block'
        });
    },

    _slideDown: function(){
        this.targetEl.setStyle({
            'height': 'auto'
        });
        this.hideTriggerEl.setStyle({
            'display': 'inline-block'
        });
        this.showTriggerEl.setStyle({
            'display': 'none'
        });
    }
};

//============== POPUP =================
var awLNFilterPopup = Class.create();
awLNFilterPopup.prototype = {
    initialize: function(config){
        this.targetElement = $$(config.targetElementSelector).first();
        this.content = config.content;
        this.tooltipWrapperElement = $$(config.tooltipWrapperSelector).first();
        this.popupClassName = config.popupClassName;
        this.arrowClassName = config.arrowClassName;

        this._createPopup();
        this.init();
    },
    init:function(){
        var me = this;
        this.targetElement.observe('mouseover', function(e){
            me.showPopup();
        });
        this.targetElement.observe('mouseout', function(e){
            me.timeoutId = setTimeout(me.hidePopup.bind(me), 250);
        });
        this.tooltipWrapperElement.observe('mouseover', function(e){
            me.showPopup();
        });
        this.tooltipWrapperElement.observe('mouseout', function(e){
            me.timeoutId = setTimeout(me.hidePopup.bind(me), 250);
        });
    },
    showPopup:function(){
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
            this.timeoutId = null;
            return;
        }
        this._clearPopupPosition('top');
        this._clearPopupPosition('bottom');

        if (this._canShowUpper()) {
            this._setPopupPosition('top');
        } else {
            this._setPopupPosition('bottom');
        }
        this.tooltipWrapperElement.show();
    },
    hidePopup:function(){
        this._clearPopupPosition('top');
        this._clearPopupPosition('bottom');
        this.tooltipWrapperElement.hide();
        this.timeoutId = null;
    },
    _canShowUpper:function(){
        var topOffset = this.targetElement.viewportOffset()['top'];
        this.tooltipWrapperElement.show();
        var popupHeight = this.popup.getHeight() + this.arrow.getHeight() + 10;
        this.tooltipWrapperElement.hide();
        return topOffset >= popupHeight;
    },
    _setPopupPosition:function(position){
        this.popup.addClassName(this.popupClassName + '__' + position);
        this.arrow.addClassName(this.arrowClassName + '__' + position);
    },
    _clearPopupPosition:function(position){
        this.popup.removeClassName(this.popupClassName + '__' + position);
        this.arrow.removeClassName(this.arrowClassName + '__' + position);
    },
    _createPopup: function(){
        this.popup = new Element('div');
        this.popup.addClassName(this.popupClassName);
        this.popup.innerHTML = this.content;

        this.arrow = new Element('div');
        this.arrow.addClassName(this.arrowClassName);

        this.tooltipWrapperElement.insert(this.popup);
        this.tooltipWrapperElement.insert(this.arrow);
        this.tooltipWrapperElement.hide();
    }
};

//============== SLIDER + RANGE =================
var awLNControlSlider = Class.create(Control.Slider, {
    initialize: function(handle, track, options) {
        Control.Slider.prototype.initialize.bind(this)(handle, track, options);

        var me = this;
        this.handles.each( function(h,i) {
            h.observe("touchstart", me.eventMouseDown);
        });

        this.track.stopObserving("mousedown", this.eventMouseDown);
        $(this.track.parentNode.parentNode).stopObserving("mousemove", this.eventMouseMove);
        $$('body').first().observe("mousemove", this.eventMouseMove);
        $$('body').first().observe("touchmove", this.eventMouseMove);
        document.observe("touchend", this.eventMouseUp);
    },
    dispose: function() {
        var slider = this;
        Event.stopObserving(document, "mouseup", this.eventMouseUp);
        Event.stopObserving(document, "touchend", this.eventMouseUp);
        Event.stopObserving($$('body').first(), "mousemove", this.eventMouseMove);
        Event.stopObserving($$('body').first(), "touchmove", this.eventMouseMove);
        this.handles.each( function(h) {
            Event.stopObserving(h, "mousedown", slider.eventMouseDown);
            Event.stopObserving(h, "touchstart", slider.eventMouseDown);
        });
    },
    /**
     * copy-paster for IE9+ support
     */
    startDrag: function(event) {
        var isLeftClick = Event.isLeftClick(event);
        if (Prototype.Browser.IE) {
            var ieVersion = parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5));
            if (ieVersion > 8) {
                isLeftClick = event.which === 1;
            }
        }
        if (isLeftClick || event.type === 'touchstart') {
            if (!this.disabled){
                this.active = true;

                var handle = Event.element(event);
                var pointer  = [Event.pointerX(event), Event.pointerY(event)];
                var track = handle;
                if (track==this.track) {
                    var offsets  = Position.cumulativeOffset(this.track);
                    this.event = event;
                    this.setValue(this.translateToValue(
                        (this.isVertical() ? pointer[1]-offsets[1] : pointer[0]-offsets[0])-(this.handleLength/2)
                    ));
                    var offsets  = Position.cumulativeOffset(this.activeHandle);
                    this.offsetX = (pointer[0] - offsets[0]);
                    this.offsetY = (pointer[1] - offsets[1]);
                } else {
                    // find the handle (prevents issues with Safari)
                    while((this.handles.indexOf(handle) == -1) && handle.parentNode)
                        handle = handle.parentNode;

                    if (this.handles.indexOf(handle)!=-1) {
                        this.activeHandle    = handle;
                        this.activeHandleIdx = this.handles.indexOf(this.activeHandle);
                        this.updateStyles();

                        var offsets  = Position.cumulativeOffset(this.activeHandle);
                        this.offsetX = (pointer[0] - offsets[0]);
                        this.offsetY = (pointer[1] - offsets[1]);
                    }
                }
            }
            Event.stop(event);
        }
    }
});

var awLNFilterOptionRange = Class.create();
awLNFilterOptionRange.prototype = {
    initialize: function(config){
        this.sliderConfig = {};
        this.sliderConfig.trackEl = $$(config.trackElSelector).first();
        this.sliderConfig.minHandleEl = $$(config.minHandleElSelector).first();
        this.sliderConfig.maxHandleEl = $$(config.maxHandleElSelector).first();
        this.sliderConfig.rangeMinValue  = config.rangeMinValue;
        this.sliderConfig.rangeMaxValue  = config.rangeMaxValue;
        this.sliderConfig.minValue  = config.minValue;
        this.sliderConfig.maxValue  = config.maxValue;

        this.activeStatusEl = $$(config.activeStatusElSelector).first();
        this.minStatusEl = $$(config.minStatusElSelector).first();
        this.maxStatusEl = $$(config.maxStatusElSelector).first();

        //init inputs
        var minInput = this.minStatusEl.up().down('input');
        var maxInput = this.maxStatusEl.up().down('input');
        minInput.setAttribute("disabled", "disabled");
        maxInput.setAttribute("disabled", "disabled");
        if (
            parseInt(config.rangeMinValue) !== parseInt(config.minValue)
            || parseInt(config.rangeMaxValue) !== parseInt(config.maxValue)
        ) {
            minInput.removeAttribute("disabled");
            maxInput.removeAttribute("disabled");
        }
        this.initSlider();
    },

    initSlider: function() {
        var handles = [
            this.sliderConfig.minHandleEl,
            this.sliderConfig.maxHandleEl
        ];
        this.slider = new awLNControlSlider(handles, this.sliderConfig.trackEl, {
            range: $R(this.sliderConfig.rangeMinValue, this.sliderConfig.rangeMaxValue, false),
            sliderValue: [this.sliderConfig.minValue, this.sliderConfig.maxValue],
            restricted: true,
            onSlide: this.onSlideFn.bind(this),
            onChange: this.onChangeFn.bind(this)
        });
        this._drawStatusActiveEl();
    },

    onSlideFn: function(values) {
        var min = Math.round(values[0]);
        var max = Math.round(values[1]);
        this.minStatusEl.update(min);
        this.maxStatusEl.update(max);

        this._drawStatusActiveEl();
    },

    onChangeFn: function(values) {
        var min = Math.round(values[0]);
        var max = Math.round(values[1]);
        var minInput = this.minStatusEl.up().down('input');
        var maxInput = this.maxStatusEl.up().down('input');
        minInput.removeAttribute("disabled");
        maxInput.removeAttribute("disabled");
        minInput.setValue(min);
        maxInput.setValue(max).fire("aw_ln:input_change");
    },

    _drawStatusActiveEl: function() {
        var minHandleLeft = parseInt(this.sliderConfig.minHandleEl.getStyle('left'));
        var maxHandleLeft = parseInt(this.sliderConfig.maxHandleEl.getStyle('left'));
        this.activeStatusEl.setStyle({
            width: (maxHandleLeft - minHandleLeft) + 'px',
            marginLeft: minHandleLeft + 'px'
        });
    }
};

//============== CATEGORY TREE =============
var awLNFilterCategoryAsTree = Class.create();
awLNFilterCategoryAsTree.prototype = {
    initialize: function(config) {
        this.config = config;
        this.containerEl = $$(this.config.containerElSelector).first();

        this.init();
        this.initHandlers();
    },

    init: function() {
        var me = this;
        me.cachedData = [];
        this.containerEl.select('li').each(function(el){
            var newItem = {
                'el': el,
                'key': el.select('input').first().value,
                'level': parseInt(parseInt(el.getStyle('padding-left')) / me.config.paddingOnLevel),
                'childs': [],
                'parent': null
            };
            var parentItem = me.cachedData.findAll(function(item){
                return item.level === (newItem.level - 1);
            }).last();
            if (parentItem) {
                parentItem.childs.push(newItem.el);
                newItem.parent = parentItem.el;
            }
            me.cachedData.push(newItem);
        });
        var isOneLevelTree = me.cachedData.all(function(item){
            return item.level === 0;
        });
        if (isOneLevelTree) {
            return;
        }
        me.cachedData.each(function(item){
            var actionEl = me._addActionElToEl(item.el);
            if (item.childs.length < 1) {
                return;
            }
            var isItemOpen = me._getLocalStorageValue(item.key);
            if (null === isItemOpen) {
                isItemOpen = item.level === 0;
            }
            if (!isItemOpen) {
                actionEl.addClassName(me.config.plusElementCSSClassName);
                item.childs.each(function(el){
                    el.setStyle({'display': 'none'});
                });
                me._setLocalStorageValue(item.key, false);
            } else {
                actionEl.addClassName(me.config.minusElementCSSClassName);
                me._setLocalStorageValue(item.key, true);
            }
        });
    },

    initHandlers: function() {
        var me = this;
        var cssClassList = [
            '.' + this.config.plusElementCSSClassName,
            '.' + this.config.minusElementCSSClassName
        ];
        this.containerEl.select(cssClassList.join(',')).each(function(el){
            el.observe('click', function(e){
                me.toggleState(el);
            });
        });
    },

    toggleState: function(el) {
        if (el.hasClassName(this.config.plusElementCSSClassName)) {
            el.removeClassName(this.config.plusElementCSSClassName);
            el.addClassName(this.config.minusElementCSSClassName);
            this._slideDown(el);
        } else {
            el.removeClassName(this.config.minusElementCSSClassName);
            el.addClassName(this.config.plusElementCSSClassName);
            this._slideUp(el);
        }
    },

    _addActionElToEl: function(el) {
        var actionEl = new Element('span');
        actionEl.addClassName(this.config.actionElementCSSCLass);
        el.insertBefore(actionEl, el.firstChild);
        return actionEl;
    },

    _slideUp: function(actionEl) {
        var me = this;
        var item = me._getCachedItemByActionElement(actionEl);
        item.childs.each(function(el){
            var item = me.cachedData.find(function(item){
                return item.el === el;
            });
            var actionEl = item.el.select('.' + me.config.minusElementCSSClassName).first();
            if (actionEl) {
                me.toggleState(actionEl);
            }
            el.setStyle({'display': 'none'});
        });
        this._setLocalStorageValue(item.key, false);
    },

    _slideDown: function(actionEl) {
        var item = this._getCachedItemByActionElement(actionEl);
        item.childs.each(function(el){
            el.setStyle({'display': 'inline-block'});
        });
        this._setLocalStorageValue(item.key, true);
    },

    _getCachedItemByActionElement: function(element) {
        return this.cachedData.find(function(item){
            return item.el === element.up();
        });
    },

    _getLocalStorageValue: function(key) {
        var data = window.awLNFilterCategoryStateData || {};
        if (key in data) {
            return data[key];
        }
        return null;
    },

    _setLocalStorageValue: function(key, value) {
        window.awLNFilterCategoryStateData = window.awLNFilterCategoryStateData || {};
        window.awLNFilterCategoryStateData[key] = value;
    }
};

//============== UPDATER =================
var awLnUpdater = Class.create();
awLnUpdater.prototype = {
    _stopObservingFns: [],
    _filterList: [],
    _ajaxQueue: [],
    _noItemsFilterCodes: [],

    initialize: function() {
        this._filterList = [];
        this._ajaxQueue = [];
    },

    init: function(config) {
        var me = this;
        this.config = config;
        this.layerContainer = $$(config.layerContainerElSelector).first();
        this.toolbarElList = [];
        this.clearAllEl = this.layerContainer.select(config.clearAllElSelector).first();
        this.clearFilterElList = $$(config.clearFilterElSelector);
        this.isAjax = config.isAjax;
        this.canAddNonFilteredParams = config.canAddNonFilteredParams;
        this.searchQueryParamCode = config.searchQueryParamCode;
        this.overlaySettings = config.overlaySettings;
        this.overlayCssClass = config.overlayCssClass;
        this._initContainers(config);
        this.startObservers();
    },

    _initContainers: function(config) {
        var me = this;

        var body = $$('body').first();
        config.productsContainerElSelectorList.each(function(selector){
            if (me.productContainer || !body.select(selector).length > 0) {
                return;
            }
            me.productContainer = body.select(selector).first();
        });

        config.toolbarContainerElSelectorList.each(function(selector){
            if (!$$(selector).length > 0) {
                return;
            }
            me.toolbarElList = $$(selector);
            config.toolbarContainerElSelector = selector;
        });

        config.emptyProductsContainerElSelectorList.each(function(selector){
            if (me.emptyProductContainer || !$$(selector).length > 0) {
                return;
            }
            me.emptyProductContainer = $$(selector).first();
        });
    },

    registerFilter: function(filter) {
        this._filterList.push(filter);
    },

    registerNoItemsFilterCode: function(code) {
        this._noItemsFilterCodes.push(code);
    },

    startObservers: function() {
        var me = this;
        me.stopObservers();

        this._filterList.each(function(filterItem) {
            filterItem.startObservers();
        });

        if (me.clearAllEl) {
            var fn = me.onClearAll.bindAsEventListener(me);
            me.clearAllEl.observe('click', fn);
            me._stopObservingFns.push(function(){
                me.clearAllEl.stopObserving('click', fn);
            });
        }
        me.clearFilterElList.each(function(el){
            var fn = me.onClearFilter.bindAsEventListener(me, el.up('dt').next('dd'));
            el.observe('click', fn);
            me._stopObservingFns.push(function(){
                el.stopObserving('click', fn);
            });
        });

        //pagination and sorting
        me.toolbarElList.each(function(toolbar){
            toolbar.select('a').each(function(el){
                var fn = me.onFilterChange.bindAsEventListener(me, el);
                el.observe('click', fn);
                me._stopObservingFns.push(function(){
                    el.stopObserving('click', fn);
                });
            });
            toolbar.select('select').each(function(el){
                var changeAttr = el.getAttribute('onchange');
                el.setAttribute('_onchange', changeAttr);
                el.removeAttribute('onchange');
                var fn = me.onFilterChange.bindAsEventListener(me, el);
                el.observe('change', fn);
                me._stopObservingFns.push(function(){
                    el.stopObserving('change', fn);
                    var changeAttr = el.getAttribute('_onchange');
                    el.setAttribute('onchange', changeAttr);
                    el.removeAttribute('_onchange');
                });
            });
        });

        if (typeof(window.history.pushState) == 'function') {
            var popStateFn = function(e) {
                if (!e.state) {
                    return;
                }
                var newParams = document.location.search.replace("?","").toQueryParams();
                me.doUpdate(newParams, Object.keys(newParams), true);
            };
            Event.observe(window, 'popstate', popStateFn);
            me._stopObservingFns.push(function(){
                Event.stopObserving(window, 'popstate', popStateFn);
            });
        }

        //for history HTML4 support
        if (typeof(window.history.pushState) != 'function') {
            Event.observe(window, 'load', function(e){
                if (document.location.hash) {
                    window.setTimeout(function(){//IE on load sometimes error fix
                        me._doUpdateViaHash();
                    }, 1000);
                }
            });
            me.lastHash = document.location.hash;
            var intervalId = setInterval(function(){
                if (me.lastHash == document.location.hash) {
                    return;
                }
                me._doUpdateViaHash();
                me.lastHash = document.location.hash;
            }, 500);
            me._stopObservingFns.push(function(){
                clearInterval(intervalId);
            });
        }
    },

    stopObservers: function() {
        this._stopObservingFns.each(function(fn){
            fn();
        });
        this._stopObservingFns = [];
        this._filterList.each(function(filterItem){
            filterItem.stopObservers();
        });
    },

    onFilterChange: function(e, targetEl) {
        var newParams = {};
        this._initContainers(this.config);

        this._filterList.each(function(filterItem){
            Object.extend(newParams, filterItem.getParams());
        });

        //pagination and sorting
        var params = document.location.search.toQueryParams();
        this.toolbarElList.each(function(toolbar){
            toolbar.select('a, select').each(function(el){
                if (el !== targetEl) {
                    return;
                }
                if (el.tagName == 'A') {
                    var href = el.getAttribute('href');
                } else {
                    var href = el.getValue();
                }
                var hrefParams = href.toQueryParams();
                Object.keys(hrefParams).each(function(hrefParamKey){
                    if (typeof(params[hrefParamKey]) == "undefined" || params[hrefParamKey] !== hrefParams[hrefParamKey]) {
                        newParams[hrefParamKey] = hrefParams[hrefParamKey];
                    }
                });
            });
        });

        var filterParamKeys = Object.keys(newParams);
        this._filterList.each(function(filterItem){
            filterParamKeys.push(filterItem.getKey());
        });
        this._noItemsFilterCodes.each(function(code){
            filterParamKeys.push(code);
        });
        filterParamKeys = filterParamKeys.uniq();
        this.doUpdate(newParams, filterParamKeys);
        Event.stop(e);
    },

    onClearAll: function(e) {
        this.layerContainer.select('dd').each(function(el){
            el.fire('aw_ln:clear_filter');
        });
        this.onFilterChange(e, this.layerContainer);
    },

    onClearFilter: function(e, filterContainerEl) {
        filterContainerEl.fire('aw_ln:clear_filter');
        this.onFilterChange(e, filterContainerEl);
    },

    doUpdate: function(newParams, filterParamKeys, skipPushState) {
        skipPushState = skipPushState || false;
        this._addNonFilteredParams(newParams, filterParamKeys);
        var queryString = this._toQueryString(newParams);
        if (!this.isAjax) {
            document.location.search = queryString;
            return;
        }
        var me = this;
        var url = location.protocol + '//' + location.host + location.pathname + '?' + queryString;
        this._ajaxQueue.push(url);
        if (this._ajaxQueue.length === 1) {
            this._uiShowOverlay();
            var ajaxUrl = this._ajaxQueue.last();
            this._doAjaxRequest(ajaxUrl);
        }
        if (!skipPushState) {
            if (typeof(window.history.pushState) == 'function') {
                window.history.pushState({}, 'New URL: ' + url, url);
            } else {
                //for HTML4 compatibility
                document.location.hash = queryString;
                me.lastHash = document.location.hash;
            }
        }
    },

    onAjaxSuccess: function(json) {
        this.stopObservers();
        this._updateHtml(this.layerContainer, json['block']['layer'], [this.config.layerContainerElSelector]);

        var targetEl = this.productContainer||this.emptyProductContainer;
        var possibleSelectors = this.config.productsContainerElSelectorList.concat(this.config.emptyProductsContainerElSelectorList);
        this._updateHtml(targetEl, json['block']['catalog'], possibleSelectors);

        this._evalScripts(json['block']['catalog']);
        this._evalScripts(json['block']['layer']);
        //compatibility with EE multiple wishlist
        this._enterpriseWishlistInit();

        updateLayerBlock();
    },

    onAjaxFailure: function(targetUrl) {
        document.location.href = targetUrl;
    },

    _doAjaxRequest: function(url) {
        var me = this;
        new Ajax.Request(url, {
            method: 'get',
            parameters: {
                'aw_layerednavigation': 1
            },
            onSuccess: function(transport) {
                var lastUrl = me._ajaxQueue.last();
                if (lastUrl !== url) {
                    me._doAjaxRequest(lastUrl);
                    return;
                }
                me._ajaxQueue = [];
                try {
                    eval("var json = " + transport.responseText + " || {}");
                } catch(e) {
                    me.onAjaxFailure(url);
                    return;
                }
                if (!json.success) {
                    me.onAjaxFailure(url);
                    return;
                }
                try {
                    me.onAjaxSuccess(json);
                } catch (e) {
                    me.onAjaxFailure(url);
                    return;
                }
                me._uiHideOverlay();
            },
            onFailure: function(transport) {
                var lastUrl = me._ajaxQueue.last();
                if (lastUrl !== url) {
                    me._doAjaxRequest(lastUrl);
                    return;
                }
                me.onAjaxFailure(url);
            }
        });
    },

    /**
     * copy from prototype.js
     */
    _toQueryString: function(params) {
        var me = this;
        var results = [];
        $H(params).each(function(pair){
            var key = encodeURIComponent(pair.key), values = pair.value;
            if (values && typeof values == 'object') {
                if (Object.isArray(values)) {
                    values = values.join(',');
                }
            }
            results.push(me._toQueryPair(key, values));
        });
        return results.join('&');
    },

    /**
     * copy from prototype.js
     */
    _toQueryPair: function (key, value) {
        if (Object.isUndefined(value)) {
            return key;
        }
        value = String.interpret(value);

        // Normalize newlines as \r\n because the HTML spec says newlines should
        // be encoded as CRLFs.
        value = value.gsub(/(\r)?\n/, '\r\n');
        value = encodeURIComponent(value);
        // Likewise, according to the spec, spaces should be '+' rather than
        // '%20'.
        value = value.gsub(/%20/, '+');
        return key + '=' + value;
    },

    _updateHtml: function(node, html, cssSelectorList) {
        var storage = new Element('div');
        storage.innerHTML = html;
        if (storage.childElements().length < 1) {
            node.parentNode.removeChild(node);
            return;
        }
        var newNode = null;
        cssSelectorList.each(function(selector){
            if (newNode) {
                return;
            }
            newNode = storage.select(selector).first();
        });
        if (newNode) {
            node.parentNode.replaceChild(newNode, node);
        }
    },

    _evalScripts: function(html) {
        var scripts = html.extractScripts();
        scripts.each(function(script){
            try {
                //FIX CDATA comment
                script = script.replace('//<![CDATA[', '').replace('//]]>', '');
                script = script.replace('/*<![CDATA[*/', '').replace('/*]]>*/', '');
                eval(script.replace(/var /gi, ""));
            } catch(e){
                if(window.console) {
                    console.log(e.message);
                }
            }
        });
    },

    _uiShowOverlay: function() {
        this.layerContainer.insertBefore(
            this._uiCreateOverlay(this.layerContainer),
            this.layerContainer.firstChild
        );
        if (this.productContainer) {
            this.productContainer.insertBefore(
                this._uiCreateOverlay(this.productContainer),
                this.productContainer.firstChild
            );
        }
        if (this.emptyProductContainer) {
            this.emptyProductContainer.up().insertBefore(
                this._uiCreateOverlay(this.emptyProductContainer.up()),
                this.emptyProductContainer.up().firstChild
            );
        }
    },

    _uiHideOverlay: function() {
        var parentOverlayElementSelector = [this.config.layerContainerElSelector]
            .concat(this.config.productsContainerElSelectorList)
            .concat(this.config.emptyProductsContainerElSelectorList)
        ;
        var me = this;
        parentOverlayElementSelector.each(function(cssSelector){
            var el = $$(cssSelector).first();
            if (!el || !el.up()) {
                return;
            }
            el.up().select('.' + me.overlayCssClass).each(function(el){
                el.remove();
            });
        });
    },

    _uiCreateOverlay: function(parent) {
        var overlay = new Element('div');
        $(overlay).addClassName(this.overlayCssClass);
        overlay.setStyle({
            backgroundColor: this.overlaySettings.color,
            opacity: (this.overlaySettings.opacity)/100,
            filter: "alpha(opacity=" + this.overlaySettings.opacity + ")"
        });
        if (parent) {
            overlay.setStyle({
                width: parent.getWidth() + "px",
                height: parent.getHeight() + "px"
            });
        }
        if (this.overlaySettings.image) {
            overlay.setStyle({
                backgroundImage: "url(" + this.overlaySettings.image + ")",
                backgroundSize: this.overlaySettings.imageWidth + "px " + this.overlaySettings.imageHeight + "px"
            })
        }
        return overlay;
    },

    /**
     * compatibility with HTML4
     * @private
     */
    _doUpdateViaHash: function() {
        var newParams = document.location.hash.replace("#","").toQueryParams();
        this.doUpdate(newParams, Object.keys(newParams), true);
    },

    //compatibility with EE multiple wishlist
    _enterpriseWishlistInit: function() {
        if (Object.isUndefined(window.Enterprise) || Object.isUndefined(Enterprise.Wishlist)) {
            return;
        }
        var registry = Element.retrieve(document, 'prototype_event_registry');
        if (Object.isUndefined(registry)) {
            return;
        }
        var responderList = registry.get('dom:loaded');
        if (Object.isUndefined(responderList)) {
            return;
        }
        var responder = responderList.detect(function(item) {
            if (!item.handler) {
                return false;
            }
            return item.handler.toString().indexOf('Enterprise.Wishlist.list.length') > -1;
        });
        if (Object.isUndefined(responder)) {
            return;
        }
        responder.handler();
    },

    _addNonFilteredParams: function(newParams, filterParamKeys) {
        var params = document.location.search.replace(/\+/g, ' ').toQueryParams();
        var nonFilterParamKeys = Object.keys(params).filter(function(i) {return !(filterParamKeys.indexOf(i) > -1);}, this);
        if (this.canAddNonFilteredParams) {
            nonFilterParamKeys.each(function(key){
                newParams[key] = params[key];
            });
        }
        // search query param should be added anyway
        var q = this.searchQueryParamCode;
        if (nonFilterParamKeys.indexOf(q) > -1 && !newParams[q]) {
            newParams[q] = params[q];
        }
    }
};
