/* jQuery Mustache - v0.2.8 - 2013-06-23
 * https://github.com/jonnyreeves/jquery-Mustache
 * Copyright (c) 2013 Jonny Reeves; Licensed MIT
 */


$(function ($, window) {
	'use strict';

	var templateMap = {};
	var	instance = null;
	var	options = {

			warnOnMissingTemplates: false,


			allowOverwrite: true,


			domTemplateType: 'text/html',


			externalTemplateDataType: 'text'
	};

	function getMustache() {

		if (instance === null) {
			instance = window.Mustache;
			if (instance === void 0) {
				$.error("Failed to locate Mustache instance, are you sure it has been loaded?");
			}
		}
		return instance;
	}

	/**
	 * @return {boolean} if the supplied templateName has been added.
	 */
	function has(templateName) {
		return templateMap[templateName] !== void 0;
	}

	/**
	 * Registers a template so that it can be used by $.Mustache.
	 *
	 * @param templateName		A name which uniquely identifies this template.
	 * @param templateHtml		The HTML which makes us the template; this will be rendered by Mustache when render()
	 *							is invoked.
	 * @throws					If options.allowOverwrite is false and the templateName has already been registered.
	 */
	function add(templateName, templateHtml) {
		if (!options.allowOverwrite && has(templateName)) {
			$.error('TemplateName: ' + templateName + ' is already mapped.');
			return;
		}
		templateMap[templateName] = $.trim(templateHtml);
	}


	function addFromDom() {

		var templateElementIds;

		// If no args are supplied, all script blocks will be read from the document.
		if (arguments.length === 0) {
			templateElementIds = $('script[type="' + options.domTemplateType + '"]').map(function () {
				return this.id;
			});
		}
		else {
			templateElementIds = $.makeArray(arguments);
		}

		$.each(templateElementIds, function() {
			var templateElement = document.getElementById(this);

			if (templateElement === null) {
				$.error('No such elementId: #' + this);
			}
			else {
				add(this, $(templateElement).html());
			}
		});
	}

	/**
	 * Removes a template, the contents of the removed Template will be returned.
	 *
	 * @param templateName		The name of the previously registered Mustache template that you wish to remove.
	 * @returns					String which represents the raw content of the template.
	 */
	function remove(templateName) {
		var result = templateMap[templateName];
		delete templateMap[templateName];
		return result;
	}

	/**
	 * Removes all templates and tells Mustache to flush its cache.
	 */
	function clear() {
		templateMap = {};
		getMustache().clearCache();
	}

	/**
	 * Renders a previously added Mustache template using the supplied templateData object.  Note if the supplied
	 * templateName doesn't exist an empty String will be returned.
	 */
	function render(templateName, templateData) {
		if (!has(templateName)) {
			if (options.warnOnMissingTemplates) {
				$.error('No template registered for: ' + templateName);
			}
			return '';
		}
		return getMustache().to_html(templateMap[templateName], templateData, templateMap);
	}

	/**
	 * Loads the external Mustache templates located at the supplied URL and registers them for later use.  This method
	 * returns a jQuery Promise and also support an `onComplete` callback.
	 *
	 * @param url			URL of the external Mustache template file to load.
	 * @param onComplete	Optional callback function which will be invoked when the templates from the supplied URL
	 *						have been loaded and are ready for use.
	 * @returns				jQuery deferred promise which will complete when the templates have been loaded and are
	 *						ready for use.
	 */
	function load(url, onComplete) {
		return $.ajax({
				url: url,
				dataType: options.externalTemplateDataType
			}).done(function (templates) {
				$(templates).filter('script').each(function (i, el) {
					add(el.id, $(el).html());
				});

				if ($.isFunction(onComplete)) {
					onComplete();
				}
			});
	}

	/**
	 * Returns an Array of templateNames which have been registered and can be retrieved via
	 * $.Mustache.render() or $(element).mustache().
	 */
	function templates() {
		return $.map(templateMap, function (value, key) {
			return key;
		});
	}

	// Expose the public methods on jQuery.Mustache
	$.Mustache = {
		options: options,
		load: load,
		has: has,
		add: add,
		addFromDom: addFromDom,
		remove: remove,
		clear: clear,
		render: render,
		templates: templates,
		instance: instance
	};

	/**
	 * Renders one or more viewModels into the current jQuery element.
	 *
	 * @param templateName	The name of the Mustache template you wish to render, Note that the
	 *						template must have been previously loaded and / or added.
	 * @param templateData	One or more JavaScript objects which will be used to render the Mustache
	 *						template.
	 * @param options.method	jQuery method to use when rendering, defaults to 'append'.
	 */
	$.fn.mustache = function (templateName, templateData, options) {
		var settings = $.extend({
			method:	'append'
		}, options);

		var renderTemplate = function (obj, viewModel) {
			$(obj)[settings.method](render(templateName, viewModel));
		};

		return this.each(function () {
			var element = this;

			// Render a collection of viewModels.
			if ($.isArray(templateData)) {
				$.each(templateData, function () {
					renderTemplate(element, this);
				});
			}

			// Render a single viewModel.
			else {
				renderTemplate(element, templateData);
			}
		});
	};

}(window.jQuery||window.Zepto, window));
