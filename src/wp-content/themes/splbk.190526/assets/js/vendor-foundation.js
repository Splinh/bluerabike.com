function getDefaultExportFromCjs(x) {
  return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, "default") ? x["default"] : x;
}
var jqueryGlobal;
var hasRequiredJqueryGlobal;
function requireJqueryGlobal() {
  if (hasRequiredJqueryGlobal) return jqueryGlobal;
  hasRequiredJqueryGlobal = 1;
  jqueryGlobal = window.jQuery;
  return jqueryGlobal;
}
var jqueryGlobalExports = requireJqueryGlobal();
const $ = /* @__PURE__ */ getDefaultExportFromCjs(jqueryGlobalExports);
function rtl() {
  return $("html").attr("dir") === "rtl";
}
function GetYoDigits(length = 6, namespace) {
  let str = "";
  const chars = "0123456789abcdefghijklmnopqrstuvwxyz";
  const charsLength = chars.length;
  for (let i = 0; i < length; i++) {
    str += chars[Math.floor(Math.random() * charsLength)];
  }
  return namespace ? `${str}-${namespace}` : str;
}
function RegExpEscape(str) {
  return str.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
}
function transitionend($elem) {
  var transitions = {
    "transition": "transitionend",
    "WebkitTransition": "webkitTransitionEnd",
    "MozTransition": "transitionend",
    "OTransition": "otransitionend"
  };
  var elem = document.createElement("div"), end;
  for (let transition in transitions) {
    if (typeof elem.style[transition] !== "undefined") {
      end = transitions[transition];
    }
  }
  if (end) {
    return end;
  } else {
    setTimeout(function() {
      $elem.triggerHandler("transitionend", [$elem]);
    }, 1);
    return "transitionend";
  }
}
function onLoad($elem, handler) {
  const didLoad = document.readyState === "complete";
  const eventType = (didLoad ? "_didLoad" : "load") + ".zf.util.onLoad";
  const cb = () => $elem.triggerHandler(eventType);
  if ($elem) {
    if (handler) $elem.one(eventType, handler);
    if (didLoad)
      setTimeout(cb);
    else
      $(window).one("load", cb);
  }
  return eventType;
}
function ignoreMousedisappear(handler, { ignoreLeaveWindow = false, ignoreReappear = false } = {}) {
  return function leaveEventHandler(eLeave, ...rest) {
    const callback = handler.bind(this, eLeave, ...rest);
    if (eLeave.relatedTarget !== null) {
      return callback();
    }
    setTimeout(function leaveEventDebouncer() {
      if (!ignoreLeaveWindow && document.hasFocus && !document.hasFocus()) {
        return callback();
      }
      if (!ignoreReappear) {
        $(document).one("mouseenter", function reenterEventHandler(eReenter) {
          if (!$(eLeave.currentTarget).has(eReenter.target).length) {
            eLeave.relatedTarget = eReenter.target;
            callback();
          }
        });
      }
    }, 0);
  };
}
window.matchMedia || (window.matchMedia = (function() {
  var styleMedia = window.styleMedia || window.media;
  if (!styleMedia) {
    var style = document.createElement("style"), script = document.getElementsByTagName("script")[0], info = null;
    style.type = "text/css";
    style.id = "matchmediajs-test";
    if (!script) {
      document.head.appendChild(style);
    } else {
      script.parentNode.insertBefore(style, script);
    }
    info = "getComputedStyle" in window && window.getComputedStyle(style, null) || style.currentStyle;
    styleMedia = {
      matchMedium: function(media) {
        var text = "@media " + media + "{ #matchmediajs-test { width: 1px; } }";
        if (style.styleSheet) {
          style.styleSheet.cssText = text;
        } else {
          style.textContent = text;
        }
        return info.width === "1px";
      }
    };
  }
  return function(media) {
    return {
      matches: styleMedia.matchMedium(media || "all"),
      media: media || "all"
    };
  };
})());
var MediaQuery = {
  queries: [],
  current: "",
  /**
   * Initializes the media query helper, by extracting the breakpoint list from the CSS and activating the breakpoint watcher.
   * @function
   * @private
   */
  _init() {
    if (this.isInitialized === true) {
      return this;
    } else {
      this.isInitialized = true;
    }
    var self = this;
    var $meta = $("meta.foundation-mq");
    if (!$meta.length) {
      $('<meta class="foundation-mq" name="foundation-mq" content>').appendTo(document.head);
    }
    var extractedStyles = $(".foundation-mq").css("font-family");
    var namedQueries;
    namedQueries = parseStyleToObject(extractedStyles);
    self.queries = [];
    for (var key in namedQueries) {
      if (namedQueries.hasOwnProperty(key)) {
        self.queries.push({
          name: key,
          value: `only screen and (min-width: ${namedQueries[key]})`
        });
      }
    }
    this.current = this._getCurrentSize();
    this._watcher();
  },
  /**
   * Reinitializes the media query helper.
   * Useful if your CSS breakpoint configuration has just been loaded or has changed since the initialization.
   * @function
   * @private
   */
  _reInit() {
    this.isInitialized = false;
    this._init();
  },
  /**
   * Checks if the screen is at least as wide as a breakpoint.
   * @function
   * @param {String} size - Name of the breakpoint to check.
   * @returns {Boolean} `true` if the breakpoint matches, `false` if it's smaller.
   */
  atLeast(size) {
    var query = this.get(size);
    if (query) {
      return window.matchMedia(query).matches;
    }
    return false;
  },
  /**
   * Checks if the screen is within the given breakpoint.
   * If smaller than the breakpoint of larger than its upper limit it returns false.
   * @function
   * @param {String} size - Name of the breakpoint to check.
   * @returns {Boolean} `true` if the breakpoint matches, `false` otherwise.
   */
  only(size) {
    return size === this._getCurrentSize();
  },
  /**
   * Checks if the screen is within a breakpoint or smaller.
   * @function
   * @param {String} size - Name of the breakpoint to check.
   * @returns {Boolean} `true` if the breakpoint matches, `false` if it's larger.
   */
  upTo(size) {
    const nextSize = this.next(size);
    if (nextSize) {
      return !this.atLeast(nextSize);
    }
    return true;
  },
  /**
   * Checks if the screen matches to a breakpoint.
   * @function
   * @param {String} size - Name of the breakpoint to check, either 'small only' or 'small'. Omitting 'only' falls back to using atLeast() method.
   * @returns {Boolean} `true` if the breakpoint matches, `false` if it does not.
   */
  is(size) {
    const parts = size.trim().split(" ").filter((p) => !!p.length);
    const [bpSize, bpModifier = ""] = parts;
    if (bpModifier === "only") {
      return this.only(bpSize);
    }
    if (!bpModifier || bpModifier === "up") {
      return this.atLeast(bpSize);
    }
    if (bpModifier === "down") {
      return this.upTo(bpSize);
    }
    throw new Error(`
      Invalid breakpoint passed to MediaQuery.is().
      Expected a breakpoint name formatted like "<size> <modifier>", got "${size}".
    `);
  },
  /**
   * Gets the media query of a breakpoint.
   * @function
   * @param {String} size - Name of the breakpoint to get.
   * @returns {String|null} - The media query of the breakpoint, or `null` if the breakpoint doesn't exist.
   */
  get(size) {
    for (var i in this.queries) {
      if (this.queries.hasOwnProperty(i)) {
        var query = this.queries[i];
        if (size === query.name) return query.value;
      }
    }
    return null;
  },
  /**
   * Get the breakpoint following the given breakpoint.
   * @function
   * @param {String} size - Name of the breakpoint.
   * @returns {String|null} - The name of the following breakpoint, or `null` if the passed breakpoint was the last one.
   */
  next(size) {
    const queryIndex = this.queries.findIndex((q) => this._getQueryName(q) === size);
    if (queryIndex === -1) {
      throw new Error(`
        Unknown breakpoint "${size}" passed to MediaQuery.next().
        Ensure it is present in your Sass "$breakpoints" setting.
      `);
    }
    const nextQuery = this.queries[queryIndex + 1];
    return nextQuery ? nextQuery.name : null;
  },
  /**
   * Returns the name of the breakpoint related to the given value.
   * @function
   * @private
   * @param {String|Object} value - Breakpoint name or query object.
   * @returns {String} Name of the breakpoint.
   */
  _getQueryName(value) {
    if (typeof value === "string")
      return value;
    if (typeof value === "object")
      return value.name;
    throw new TypeError(`
      Invalid value passed to MediaQuery._getQueryName().
      Expected a breakpoint name (String) or a breakpoint query (Object), got "${value}" (${typeof value})
    `);
  },
  /**
   * Gets the current breakpoint name by testing every breakpoint and returning the last one to match (the biggest one).
   * @function
   * @private
   * @returns {String} Name of the current breakpoint.
   */
  _getCurrentSize() {
    var matched;
    for (var i = 0; i < this.queries.length; i++) {
      var query = this.queries[i];
      if (window.matchMedia(query.value).matches) {
        matched = query;
      }
    }
    return matched && this._getQueryName(matched);
  },
  /**
   * Activates the breakpoint watcher, which fires an event on the window whenever the breakpoint changes.
   * @function
   * @private
   */
  _watcher() {
    $(window).on("resize.zf.trigger", () => {
      var newSize = this._getCurrentSize(), currentSize = this.current;
      if (newSize !== currentSize) {
        this.current = newSize;
        $(window).trigger("changed.zf.mediaquery", [newSize, currentSize]);
      }
    });
  }
};
function parseStyleToObject(str) {
  var styleObject = {};
  if (typeof str !== "string") {
    return styleObject;
  }
  str = str.trim().slice(1, -1);
  if (!str) {
    return styleObject;
  }
  styleObject = str.split("&").reduce(function(ret, param) {
    var parts = param.replace(/\+/g, " ").split("=");
    var key = parts[0];
    var val = parts[1];
    key = decodeURIComponent(key);
    val = typeof val === "undefined" ? null : decodeURIComponent(val);
    if (!ret.hasOwnProperty(key)) {
      ret[key] = val;
    } else if (Array.isArray(ret[key])) {
      ret[key].push(val);
    } else {
      ret[key] = [ret[key], val];
    }
    return ret;
  }, {});
  return styleObject;
}
var FOUNDATION_VERSION = "6.9.0";
var Foundation = {
  version: FOUNDATION_VERSION,
  /**
   * Stores initialized plugins.
   */
  _plugins: {},
  /**
   * Stores generated unique ids for plugin instances
   */
  _uuids: [],
  /**
   * Defines a Foundation plugin, adding it to the `Foundation` namespace and the list of plugins to initialize when reflowing.
   * @param {Object} plugin - The constructor of the plugin.
   */
  plugin: function(plugin, name) {
    var className = name || functionName(plugin);
    var attrName = hyphenate$1(className);
    this._plugins[attrName] = this[className] = plugin;
  },
  /**
   * @function
   * Populates the _uuids array with pointers to each individual plugin instance.
   * Adds the `zfPlugin` data-attribute to programmatically created plugins to allow use of $(selector).foundation(method) calls.
   * Also fires the initialization event for each plugin, consolidating repetitive code.
   * @param {Object} plugin - an instance of a plugin, usually `this` in context.
   * @param {String} name - the name of the plugin, passed as a camelCased string.
   * @fires Plugin#init
   */
  registerPlugin: function(plugin, name) {
    var pluginName = name ? hyphenate$1(name) : functionName(plugin.constructor).toLowerCase();
    plugin.uuid = GetYoDigits(6, pluginName);
    if (!plugin.$element.attr(`data-${pluginName}`)) {
      plugin.$element.attr(`data-${pluginName}`, plugin.uuid);
    }
    if (!plugin.$element.data("zfPlugin")) {
      plugin.$element.data("zfPlugin", plugin);
    }
    plugin.$element.trigger(`init.zf.${pluginName}`);
    this._uuids.push(plugin.uuid);
    return;
  },
  /**
   * @function
   * Removes the plugins uuid from the _uuids array.
   * Removes the zfPlugin data attribute, as well as the data-plugin-name attribute.
   * Also fires the destroyed event for the plugin, consolidating repetitive code.
   * @param {Object} plugin - an instance of a plugin, usually `this` in context.
   * @fires Plugin#destroyed
   */
  unregisterPlugin: function(plugin) {
    var pluginName = hyphenate$1(functionName(plugin.$element.data("zfPlugin").constructor));
    this._uuids.splice(this._uuids.indexOf(plugin.uuid), 1);
    plugin.$element.removeAttr(`data-${pluginName}`).removeData("zfPlugin").trigger(`destroyed.zf.${pluginName}`);
    for (var prop in plugin) {
      if (typeof plugin[prop] === "function") {
        plugin[prop] = null;
      }
    }
    return;
  },
  /**
   * @function
   * Causes one or more active plugins to re-initialize, resetting event listeners, recalculating positions, etc.
   * @param {String} plugins - optional string of an individual plugin key, attained by calling `$(element).data('pluginName')`, or string of a plugin class i.e. `'dropdown'`
   * @default If no argument is passed, reflow all currently active plugins.
   */
  reInit: function(plugins) {
    var isJQ = plugins instanceof $;
    try {
      if (isJQ) {
        plugins.each(function() {
          $(this).data("zfPlugin")._init();
        });
      } else {
        var type = typeof plugins, _this = this, fns = {
          "object": function(plgs) {
            plgs.forEach(function(p) {
              p = hyphenate$1(p);
              $("[data-" + p + "]").foundation("_init");
            });
          },
          "string": function() {
            plugins = hyphenate$1(plugins);
            $("[data-" + plugins + "]").foundation("_init");
          },
          "undefined": function() {
            this.object(Object.keys(_this._plugins));
          }
        };
        fns[type](plugins);
      }
    } catch (err) {
      console.error(err);
    } finally {
      return plugins;
    }
  },
  /**
   * Initialize plugins on any elements within `elem` (and `elem` itself) that aren't already initialized.
   * @param {Object} elem - jQuery object containing the element to check inside. Also checks the element itself, unless it's the `document` object.
   * @param {String|Array} plugins - A list of plugins to initialize. Leave this out to initialize everything.
   */
  reflow: function(elem, plugins) {
    if (typeof plugins === "undefined") {
      plugins = Object.keys(this._plugins);
    } else if (typeof plugins === "string") {
      plugins = [plugins];
    }
    var _this = this;
    $.each(plugins, function(i, name) {
      var plugin = _this._plugins[name];
      var $elem = $(elem).find("[data-" + name + "]").addBack("[data-" + name + "]").filter(function() {
        return typeof $(this).data("zfPlugin") === "undefined";
      });
      $elem.each(function() {
        var $el = $(this), opts = { reflow: true };
        if ($el.attr("data-options")) {
          $el.attr("data-options").split(";").forEach(function(option) {
            var opt = option.split(":").map(function(el) {
              return el.trim();
            });
            if (opt[0]) opts[opt[0]] = parseValue(opt[1]);
          });
        }
        try {
          $el.data("zfPlugin", new plugin($(this), opts));
        } catch (er) {
          console.error(er);
        } finally {
          return;
        }
      });
    });
  },
  getFnName: functionName,
  addToJquery: function() {
    var foundation = function(method) {
      var type = typeof method, $noJS = $(".no-js");
      if ($noJS.length) {
        $noJS.removeClass("no-js");
      }
      if (type === "undefined") {
        MediaQuery._init();
        Foundation.reflow(this);
      } else if (type === "string") {
        var args = Array.prototype.slice.call(arguments, 1);
        var plugClass = this.data("zfPlugin");
        if (typeof plugClass !== "undefined" && typeof plugClass[method] !== "undefined") {
          if (this.length === 1) {
            plugClass[method].apply(plugClass, args);
          } else {
            this.each(function(i, el) {
              plugClass[method].apply($(el).data("zfPlugin"), args);
            });
          }
        } else {
          throw new ReferenceError("We're sorry, '" + method + "' is not an available method for " + (plugClass ? functionName(plugClass) : "this element") + ".");
        }
      } else {
        throw new TypeError(`We're sorry, ${type} is not a valid parameter. You must use a string representing the method you wish to invoke.`);
      }
      return this;
    };
    $.fn.foundation = foundation;
    return $;
  }
};
Foundation.util = {
  /**
   * Function for applying a debounce effect to a function call.
   * @function
   * @param {Function} func - Function to be called at end of timeout.
   * @param {Number} delay - Time in ms to delay the call of `func`.
   * @returns function
   */
  throttle: function(func, delay) {
    var timer = null;
    return function() {
      var context = this, args = arguments;
      if (timer === null) {
        timer = setTimeout(function() {
          func.apply(context, args);
          timer = null;
        }, delay);
      }
    };
  }
};
window.Foundation = Foundation;
(function() {
  if (!Date.now || !window.Date.now)
    window.Date.now = Date.now = function() {
      return (/* @__PURE__ */ new Date()).getTime();
    };
  var vendors = ["webkit", "moz"];
  for (var i = 0; i < vendors.length && !window.requestAnimationFrame; ++i) {
    var vp = vendors[i];
    window.requestAnimationFrame = window[vp + "RequestAnimationFrame"];
    window.cancelAnimationFrame = window[vp + "CancelAnimationFrame"] || window[vp + "CancelRequestAnimationFrame"];
  }
  if (/iP(ad|hone|od).*OS 6/.test(window.navigator.userAgent) || !window.requestAnimationFrame || !window.cancelAnimationFrame) {
    var lastTime = 0;
    window.requestAnimationFrame = function(callback) {
      var now = Date.now();
      var nextTime = Math.max(lastTime + 16, now);
      return setTimeout(
        function() {
          callback(lastTime = nextTime);
        },
        nextTime - now
      );
    };
    window.cancelAnimationFrame = clearTimeout;
  }
  if (!window.performance || !window.performance.now) {
    window.performance = {
      start: Date.now(),
      now: function() {
        return Date.now() - this.start;
      }
    };
  }
})();
if (!Function.prototype.bind) {
  Function.prototype.bind = function(oThis) {
    if (typeof this !== "function") {
      throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable");
    }
    var aArgs = Array.prototype.slice.call(arguments, 1), fToBind = this, fNOP = function() {
    }, fBound = function() {
      return fToBind.apply(
        this instanceof fNOP ? this : oThis,
        aArgs.concat(Array.prototype.slice.call(arguments))
      );
    };
    if (this.prototype) {
      fNOP.prototype = this.prototype;
    }
    fBound.prototype = new fNOP();
    return fBound;
  };
}
function functionName(fn) {
  if (typeof Function.prototype.name === "undefined") {
    var funcNameRegex = /function\s([^(]{1,})\(/;
    var results = funcNameRegex.exec(fn.toString());
    return results && results.length > 1 ? results[1].trim() : "";
  } else if (typeof fn.prototype === "undefined") {
    return fn.constructor.name;
  } else {
    return fn.prototype.constructor.name;
  }
}
function parseValue(str) {
  if ("true" === str) return true;
  else if ("false" === str) return false;
  else if (!isNaN(str * 1)) return parseFloat(str);
  return str;
}
function hyphenate$1(str) {
  return str.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase();
}
const keyCodes = {
  9: "TAB",
  13: "ENTER",
  27: "ESCAPE",
  32: "SPACE",
  35: "END",
  36: "HOME",
  37: "ARROW_LEFT",
  38: "ARROW_UP",
  39: "ARROW_RIGHT",
  40: "ARROW_DOWN"
};
var commands = {};
function findFocusable($element) {
  if (!$element) {
    return false;
  }
  return $element.find("a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, *[tabindex], *[contenteditable]").filter(function() {
    if (!$(this).is(":visible") || $(this).attr("tabindex") < 0) {
      return false;
    }
    return true;
  }).sort(function(a, b) {
    if ($(a).attr("tabindex") === $(b).attr("tabindex")) {
      return 0;
    }
    let aTabIndex = parseInt($(a).attr("tabindex"), 10), bTabIndex = parseInt($(b).attr("tabindex"), 10);
    if (typeof $(a).attr("tabindex") === "undefined" && bTabIndex > 0) {
      return 1;
    }
    if (typeof $(b).attr("tabindex") === "undefined" && aTabIndex > 0) {
      return -1;
    }
    if (aTabIndex === 0 && bTabIndex > 0) {
      return 1;
    }
    if (bTabIndex === 0 && aTabIndex > 0) {
      return -1;
    }
    if (aTabIndex < bTabIndex) {
      return -1;
    }
    if (aTabIndex > bTabIndex) {
      return 1;
    }
  });
}
function parseKey(event) {
  var key = keyCodes[event.which || event.keyCode] || String.fromCharCode(event.which).toUpperCase();
  key = key.replace(/\W+/, "");
  if (event.shiftKey) key = `SHIFT_${key}`;
  if (event.ctrlKey) key = `CTRL_${key}`;
  if (event.altKey) key = `ALT_${key}`;
  key = key.replace(/_$/, "");
  return key;
}
var Keyboard = {
  keys: getKeyCodes(keyCodes),
  /**
   * Parses the (keyboard) event and returns a String that represents its key
   * Can be used like Foundation.parseKey(event) === Foundation.keys.SPACE
   * @param {Event} event - the event generated by the event handler
   * @return String key - String that represents the key pressed
   */
  parseKey,
  /**
   * Handles the given (keyboard) event
   * @param {Event} event - the event generated by the event handler
   * @param {String} component - Foundation component's name, e.g. Slider or Reveal
   * @param {Objects} functions - collection of functions that are to be executed
   */
  handleKey(event, component, functions) {
    var commandList = commands[component], keyCode = this.parseKey(event), cmds, command, fn;
    if (!commandList) return console.warn("Component not defined!");
    if (event.zfIsKeyHandled === true) return;
    if (typeof commandList.ltr === "undefined") {
      cmds = commandList;
    } else {
      if (rtl()) cmds = $.extend({}, commandList.ltr, commandList.rtl);
      else cmds = $.extend({}, commandList.rtl, commandList.ltr);
    }
    command = cmds[keyCode];
    fn = functions[command];
    if (fn && typeof fn === "function") {
      var returnValue = fn.apply();
      event.zfIsKeyHandled = true;
      if (functions.handled || typeof functions.handled === "function") {
        functions.handled(returnValue);
      }
    } else {
      if (functions.unhandled || typeof functions.unhandled === "function") {
        functions.unhandled();
      }
    }
  },
  /**
   * Finds all focusable elements within the given `$element`
   * @param {jQuery} $element - jQuery object to search within
   * @return {jQuery} $focusable - all focusable elements within `$element`
   */
  findFocusable,
  /**
   * Returns the component name name
   * @param {Object} component - Foundation component, e.g. Slider or Reveal
   * @return String componentName
   */
  register(componentName, cmds) {
    commands[componentName] = cmds;
  },
  // TODO9438: These references to Keyboard need to not require global. Will 'this' work in this context?
  //
  /**
   * Traps the focus in the given element.
   * @param  {jQuery} $element  jQuery object to trap the foucs into.
   */
  trapFocus($element) {
    var $focusable = findFocusable($element), $firstFocusable = $focusable.eq(0), $lastFocusable = $focusable.eq(-1);
    $element.on("keydown.zf.trapfocus", function(event) {
      if (event.target === $lastFocusable[0] && parseKey(event) === "TAB") {
        event.preventDefault();
        $firstFocusable.focus();
      } else if (event.target === $firstFocusable[0] && parseKey(event) === "SHIFT_TAB") {
        event.preventDefault();
        $lastFocusable.focus();
      }
    });
  },
  /**
   * Releases the trapped focus from the given element.
   * @param  {jQuery} $element  jQuery object to release the focus for.
   */
  releaseFocus($element) {
    $element.off("keydown.zf.trapfocus");
  }
};
function getKeyCodes(kcs) {
  var k = {};
  for (var kc in kcs) {
    if (kcs.hasOwnProperty(kc)) k[kcs[kc]] = kcs[kc];
  }
  return k;
}
var Box = {
  ImNotTouchingYou,
  OverlapArea,
  GetDimensions,
  GetExplicitOffsets
};
function ImNotTouchingYou(element, parent, lrOnly, tbOnly, ignoreBottom) {
  return OverlapArea(element, parent, lrOnly, tbOnly, ignoreBottom) === 0;
}
function OverlapArea(element, parent, lrOnly, tbOnly, ignoreBottom) {
  var eleDims = GetDimensions(element), topOver, bottomOver, leftOver, rightOver;
  if (parent) {
    var parDims = GetDimensions(parent);
    bottomOver = parDims.height + parDims.offset.top - (eleDims.offset.top + eleDims.height);
    topOver = eleDims.offset.top - parDims.offset.top;
    leftOver = eleDims.offset.left - parDims.offset.left;
    rightOver = parDims.width + parDims.offset.left - (eleDims.offset.left + eleDims.width);
  } else {
    bottomOver = eleDims.windowDims.height + eleDims.windowDims.offset.top - (eleDims.offset.top + eleDims.height);
    topOver = eleDims.offset.top - eleDims.windowDims.offset.top;
    leftOver = eleDims.offset.left - eleDims.windowDims.offset.left;
    rightOver = eleDims.windowDims.width - (eleDims.offset.left + eleDims.width);
  }
  bottomOver = ignoreBottom ? 0 : Math.min(bottomOver, 0);
  topOver = Math.min(topOver, 0);
  leftOver = Math.min(leftOver, 0);
  rightOver = Math.min(rightOver, 0);
  if (lrOnly) {
    return leftOver + rightOver;
  }
  if (tbOnly) {
    return topOver + bottomOver;
  }
  return Math.sqrt(topOver * topOver + bottomOver * bottomOver + leftOver * leftOver + rightOver * rightOver);
}
function GetDimensions(elem) {
  elem = elem.length ? elem[0] : elem;
  if (elem === window || elem === document) {
    throw new Error("I'm sorry, Dave. I'm afraid I can't do that.");
  }
  var rect = elem.getBoundingClientRect(), parRect = elem.parentNode.getBoundingClientRect(), winRect = document.body.getBoundingClientRect(), winY = window.pageYOffset, winX = window.pageXOffset;
  return {
    width: rect.width,
    height: rect.height,
    offset: {
      top: rect.top + winY,
      left: rect.left + winX
    },
    parentDims: {
      width: parRect.width,
      height: parRect.height,
      offset: {
        top: parRect.top + winY,
        left: parRect.left + winX
      }
    },
    windowDims: {
      width: winRect.width,
      height: winRect.height,
      offset: {
        top: winY,
        left: winX
      }
    }
  };
}
function GetExplicitOffsets(element, anchor, position, alignment, vOffset, hOffset, isOverflow) {
  var $eleDims = GetDimensions(element), $anchorDims = anchor ? GetDimensions(anchor) : null;
  var topVal, leftVal;
  if ($anchorDims !== null) {
    switch (position) {
      case "top":
        topVal = $anchorDims.offset.top - ($eleDims.height + vOffset);
        break;
      case "bottom":
        topVal = $anchorDims.offset.top + $anchorDims.height + vOffset;
        break;
      case "left":
        leftVal = $anchorDims.offset.left - ($eleDims.width + hOffset);
        break;
      case "right":
        leftVal = $anchorDims.offset.left + $anchorDims.width + hOffset;
        break;
    }
    switch (position) {
      case "top":
      case "bottom":
        switch (alignment) {
          case "left":
            leftVal = $anchorDims.offset.left + hOffset;
            break;
          case "right":
            leftVal = $anchorDims.offset.left - $eleDims.width + $anchorDims.width - hOffset;
            break;
          case "center":
            leftVal = isOverflow ? hOffset : $anchorDims.offset.left + $anchorDims.width / 2 - $eleDims.width / 2 + hOffset;
            break;
        }
        break;
      case "right":
      case "left":
        switch (alignment) {
          case "bottom":
            topVal = $anchorDims.offset.top - vOffset + $anchorDims.height - $eleDims.height;
            break;
          case "top":
            topVal = $anchorDims.offset.top + vOffset;
            break;
          case "center":
            topVal = $anchorDims.offset.top + vOffset + $anchorDims.height / 2 - $eleDims.height / 2;
            break;
        }
        break;
    }
  }
  return { top: topVal, left: leftVal };
}
const Nest = {
  Feather(menu, type = "zf") {
    menu.attr("role", "menubar");
    menu.find("a").attr({ "role": "menuitem" });
    var items = menu.find("li").attr({ "role": "none" }), subMenuClass = `is-${type}-submenu`, subItemClass = `${subMenuClass}-item`, hasSubClass = `is-${type}-submenu-parent`, applyAria = type !== "accordion";
    items.each(function() {
      var $item = $(this), $sub = $item.children("ul");
      if ($sub.length) {
        $item.addClass(hasSubClass);
        if (applyAria) {
          const firstItem = $item.children("a:first");
          firstItem.attr({
            "aria-haspopup": true,
            "aria-label": firstItem.attr("aria-label") || firstItem.text()
          });
          if (type === "drilldown") {
            $item.attr({ "aria-expanded": false });
          }
        }
        $sub.addClass(`submenu ${subMenuClass}`).attr({
          "data-submenu": "",
          "role": "menubar"
        });
        if (type === "drilldown") {
          $sub.attr({ "aria-hidden": true });
        }
      }
      if ($item.parent("[data-submenu]").length) {
        $item.addClass(`is-submenu-item ${subItemClass}`);
      }
    });
    return;
  },
  Burn(menu, type) {
    var subMenuClass = `is-${type}-submenu`, subItemClass = `${subMenuClass}-item`, hasSubClass = `is-${type}-submenu-parent`;
    menu.find(">li, > li > ul, .menu, .menu > li, [data-submenu] > li").removeClass(`${subMenuClass} ${subItemClass} ${hasSubClass} is-submenu-item submenu is-active`).removeAttr("data-submenu").css("display", "");
  }
};
var Touch = {};
var startPosX, startTime, elapsedTime, startEvent, isMoving = false, didMoved = false;
function onTouchEnd(e) {
  this.removeEventListener("touchmove", onTouchMove);
  this.removeEventListener("touchend", onTouchEnd);
  if (!didMoved) {
    var tapEvent = $.Event("tap", startEvent || e);
    $(this).trigger(tapEvent);
  }
  startEvent = null;
  isMoving = false;
  didMoved = false;
}
function onTouchMove(e) {
  if (true === $.spotSwipe.preventDefault) {
    e.preventDefault();
  }
  if (isMoving) {
    var x = e.touches[0].pageX;
    var dx = startPosX - x;
    var dir;
    didMoved = true;
    elapsedTime = (/* @__PURE__ */ new Date()).getTime() - startTime;
    if (Math.abs(dx) >= $.spotSwipe.moveThreshold && elapsedTime <= $.spotSwipe.timeThreshold) {
      dir = dx > 0 ? "left" : "right";
    }
    if (dir) {
      e.preventDefault();
      onTouchEnd.apply(this, arguments);
      $(this).trigger($.Event("swipe", Object.assign({}, e)), dir).trigger($.Event(`swipe${dir}`, Object.assign({}, e)));
    }
  }
}
function onTouchStart(e) {
  if (e.touches.length === 1) {
    startPosX = e.touches[0].pageX;
    startEvent = e;
    isMoving = true;
    didMoved = false;
    startTime = (/* @__PURE__ */ new Date()).getTime();
    this.addEventListener("touchmove", onTouchMove, { passive: true === $.spotSwipe.preventDefault });
    this.addEventListener("touchend", onTouchEnd, false);
  }
}
function init() {
  this.addEventListener && this.addEventListener("touchstart", onTouchStart, { passive: true });
}
class SpotSwipe {
  constructor() {
    this.version = "1.0.0";
    this.enabled = "ontouchstart" in document.documentElement;
    this.preventDefault = false;
    this.moveThreshold = 75;
    this.timeThreshold = 200;
    this._init();
  }
  _init() {
    $.event.special.swipe = { setup: init };
    $.event.special.tap = { setup: init };
    $.each(["left", "up", "down", "right"], function() {
      $.event.special[`swipe${this}`] = { setup: function() {
        $(this).on("swipe", $.noop);
      } };
    });
  }
}
Touch.setupSpotSwipe = function() {
  $.spotSwipe = new SpotSwipe($);
};
Touch.setupTouchHandler = function() {
  $.fn.addTouch = function() {
    this.each(function(i, el) {
      $(el).bind("touchstart touchmove touchend touchcancel", function(event) {
        handleTouch(event);
      });
    });
    var handleTouch = function(event) {
      var touches = event.changedTouches, first = touches[0], eventTypes = {
        touchstart: "mousedown",
        touchmove: "mousemove",
        touchend: "mouseup"
      }, type = eventTypes[event.type], simulatedEvent;
      if ("MouseEvent" in window && typeof window.MouseEvent === "function") {
        simulatedEvent = new window.MouseEvent(type, {
          "bubbles": true,
          "cancelable": true,
          "screenX": first.screenX,
          "screenY": first.screenY,
          "clientX": first.clientX,
          "clientY": first.clientY
        });
      } else {
        simulatedEvent = document.createEvent("MouseEvent");
        simulatedEvent.initMouseEvent(type, true, true, window, 1, first.screenX, first.screenY, first.clientX, first.clientY, false, false, false, false, 0, null);
      }
      first.target.dispatchEvent(simulatedEvent);
    };
  };
};
Touch.init = function() {
  if (typeof $.spotSwipe === "undefined") {
    Touch.setupSpotSwipe($);
    Touch.setupTouchHandler($);
  }
};
const initClasses = ["mui-enter", "mui-leave"];
const activeClasses = ["mui-enter-active", "mui-leave-active"];
const Motion = {
  animateIn: function(element, animation, cb) {
    animate(true, element, animation, cb);
  },
  animateOut: function(element, animation, cb) {
    animate(false, element, animation, cb);
  }
};
function Move(duration, elem, fn) {
  var anim, prog, start = null;
  if (duration === 0) {
    fn.apply(elem);
    elem.trigger("finished.zf.animate", [elem]).triggerHandler("finished.zf.animate", [elem]);
    return;
  }
  function move(ts) {
    if (!start) start = ts;
    prog = ts - start;
    fn.apply(elem);
    if (prog < duration) {
      anim = window.requestAnimationFrame(move, elem);
    } else {
      window.cancelAnimationFrame(anim);
      elem.trigger("finished.zf.animate", [elem]).triggerHandler("finished.zf.animate", [elem]);
    }
  }
  anim = window.requestAnimationFrame(move);
}
function animate(isIn, element, animation, cb) {
  element = $(element).eq(0);
  if (!element.length) return;
  var initClass = isIn ? initClasses[0] : initClasses[1];
  var activeClass = isIn ? activeClasses[0] : activeClasses[1];
  reset();
  element.addClass(animation).css("transition", "none");
  requestAnimationFrame(() => {
    element.addClass(initClass);
    if (isIn) element.show();
  });
  requestAnimationFrame(() => {
    element[0].offsetWidth;
    element.css("transition", "").addClass(activeClass);
  });
  element.one(transitionend(element), finish);
  function finish() {
    if (!isIn) element.hide();
    reset();
    if (cb) cb.apply(element);
  }
  function reset() {
    element[0].style.transitionDuration = 0;
    element.removeClass(`${initClass} ${activeClass} ${animation}`);
  }
}
const MutationObserver = (function() {
  var prefixes = ["WebKit", "Moz", "O", "Ms", ""];
  for (var i = 0; i < prefixes.length; i++) {
    if (`${prefixes[i]}MutationObserver` in window) {
      return window[`${prefixes[i]}MutationObserver`];
    }
  }
  return false;
})();
const triggers = (el, type) => {
  el.data(type).split(" ").forEach((id) => {
    $(`#${id}`)[type === "close" ? "trigger" : "triggerHandler"](`${type}.zf.trigger`, [el]);
  });
};
var Triggers = {
  Listeners: {
    Basic: {},
    Global: {}
  },
  Initializers: {}
};
Triggers.Listeners.Basic = {
  openListener: function() {
    triggers($(this), "open");
  },
  closeListener: function() {
    let id = $(this).data("close");
    if (id) {
      triggers($(this), "close");
    } else {
      $(this).trigger("close.zf.trigger");
    }
  },
  toggleListener: function() {
    let id = $(this).data("toggle");
    if (id) {
      triggers($(this), "toggle");
    } else {
      $(this).trigger("toggle.zf.trigger");
    }
  },
  closeableListener: function(e) {
    let animation = $(this).data("closable");
    e.stopPropagation();
    if (animation !== "") {
      Motion.animateOut($(this), animation, function() {
        $(this).trigger("closed.zf");
      });
    } else {
      $(this).fadeOut().trigger("closed.zf");
    }
  },
  toggleFocusListener: function() {
    let id = $(this).data("toggle-focus");
    $(`#${id}`).triggerHandler("toggle.zf.trigger", [$(this)]);
  }
};
Triggers.Initializers.addOpenListener = ($elem) => {
  $elem.off("click.zf.trigger", Triggers.Listeners.Basic.openListener);
  $elem.on("click.zf.trigger", "[data-open]", Triggers.Listeners.Basic.openListener);
};
Triggers.Initializers.addCloseListener = ($elem) => {
  $elem.off("click.zf.trigger", Triggers.Listeners.Basic.closeListener);
  $elem.on("click.zf.trigger", "[data-close]", Triggers.Listeners.Basic.closeListener);
};
Triggers.Initializers.addToggleListener = ($elem) => {
  $elem.off("click.zf.trigger", Triggers.Listeners.Basic.toggleListener);
  $elem.on("click.zf.trigger", "[data-toggle]", Triggers.Listeners.Basic.toggleListener);
};
Triggers.Initializers.addCloseableListener = ($elem) => {
  $elem.off("close.zf.trigger", Triggers.Listeners.Basic.closeableListener);
  $elem.on("close.zf.trigger", "[data-closeable], [data-closable]", Triggers.Listeners.Basic.closeableListener);
};
Triggers.Initializers.addToggleFocusListener = ($elem) => {
  $elem.off("focus.zf.trigger blur.zf.trigger", Triggers.Listeners.Basic.toggleFocusListener);
  $elem.on("focus.zf.trigger blur.zf.trigger", "[data-toggle-focus]", Triggers.Listeners.Basic.toggleFocusListener);
};
Triggers.Listeners.Global = {
  resizeListener: function($nodes) {
    if (!MutationObserver) {
      $nodes.each(function() {
        $(this).triggerHandler("resizeme.zf.trigger");
      });
    }
    $nodes.attr("data-events", "resize");
  },
  scrollListener: function($nodes) {
    if (!MutationObserver) {
      $nodes.each(function() {
        $(this).triggerHandler("scrollme.zf.trigger");
      });
    }
    $nodes.attr("data-events", "scroll");
  },
  closeMeListener: function(e, pluginId) {
    let plugin = e.namespace.split(".")[0];
    let plugins = $(`[data-${plugin}]`).not(`[data-yeti-box="${pluginId}"]`);
    plugins.each(function() {
      let _this = $(this);
      _this.triggerHandler("close.zf.trigger", [_this]);
    });
  }
};
Triggers.Initializers.addClosemeListener = function(pluginName) {
  var yetiBoxes = $("[data-yeti-box]"), plugNames = ["dropdown", "tooltip", "reveal"];
  if (pluginName) {
    if (typeof pluginName === "string") {
      plugNames.push(pluginName);
    } else if (typeof pluginName === "object" && typeof pluginName[0] === "string") {
      plugNames = plugNames.concat(pluginName);
    } else {
      console.error("Plugin names must be strings");
    }
  }
  if (yetiBoxes.length) {
    let listeners = plugNames.map((name) => {
      return `closeme.zf.${name}`;
    }).join(" ");
    $(window).off(listeners).on(listeners, Triggers.Listeners.Global.closeMeListener);
  }
};
function debounceGlobalListener(debounce, trigger, listener) {
  let timer, args = Array.prototype.slice.call(arguments, 3);
  $(window).on(trigger, function() {
    if (timer) {
      clearTimeout(timer);
    }
    timer = setTimeout(function() {
      listener.apply(null, args);
    }, debounce || 10);
  });
}
Triggers.Initializers.addResizeListener = function(debounce) {
  let $nodes = $("[data-resize]");
  if ($nodes.length) {
    debounceGlobalListener(debounce, "resize.zf.trigger", Triggers.Listeners.Global.resizeListener, $nodes);
  }
};
Triggers.Initializers.addScrollListener = function(debounce) {
  let $nodes = $("[data-scroll]");
  if ($nodes.length) {
    debounceGlobalListener(debounce, "scroll.zf.trigger", Triggers.Listeners.Global.scrollListener, $nodes);
  }
};
Triggers.Initializers.addMutationEventsListener = function($elem) {
  if (!MutationObserver) {
    return false;
  }
  let $nodes = $elem.find("[data-resize], [data-scroll], [data-mutate]");
  var listeningElementsMutation = function(mutationRecordsList) {
    var $target = $(mutationRecordsList[0].target);
    switch (mutationRecordsList[0].type) {
      case "attributes":
        if ($target.attr("data-events") === "scroll" && mutationRecordsList[0].attributeName === "data-events") {
          $target.triggerHandler("scrollme.zf.trigger", [$target, window.pageYOffset]);
        }
        if ($target.attr("data-events") === "resize" && mutationRecordsList[0].attributeName === "data-events") {
          $target.triggerHandler("resizeme.zf.trigger", [$target]);
        }
        if (mutationRecordsList[0].attributeName === "style") {
          $target.closest("[data-mutate]").attr("data-events", "mutate");
          $target.closest("[data-mutate]").triggerHandler("mutateme.zf.trigger", [$target.closest("[data-mutate]")]);
        }
        break;
      case "childList":
        $target.closest("[data-mutate]").attr("data-events", "mutate");
        $target.closest("[data-mutate]").triggerHandler("mutateme.zf.trigger", [$target.closest("[data-mutate]")]);
        break;
      default:
        return false;
    }
  };
  if ($nodes.length) {
    for (var i = 0; i <= $nodes.length - 1; i++) {
      var elementObserver = new MutationObserver(listeningElementsMutation);
      elementObserver.observe($nodes[i], { attributes: true, childList: true, characterData: false, subtree: true, attributeFilter: ["data-events", "style"] });
    }
  }
};
Triggers.Initializers.addSimpleListeners = function() {
  let $document = $(document);
  Triggers.Initializers.addOpenListener($document);
  Triggers.Initializers.addCloseListener($document);
  Triggers.Initializers.addToggleListener($document);
  Triggers.Initializers.addCloseableListener($document);
  Triggers.Initializers.addToggleFocusListener($document);
};
Triggers.Initializers.addGlobalListeners = function() {
  let $document = $(document);
  Triggers.Initializers.addMutationEventsListener($document);
  Triggers.Initializers.addResizeListener(250);
  Triggers.Initializers.addScrollListener();
  Triggers.Initializers.addClosemeListener();
};
Triggers.init = function(__, Foundation2) {
  onLoad($(window), function() {
    if ($.triggersInitialized !== true) {
      Triggers.Initializers.addSimpleListeners();
      Triggers.Initializers.addGlobalListeners();
      $.triggersInitialized = true;
    }
  });
  if (Foundation2) {
    Foundation2.Triggers = Triggers;
    Foundation2.IHearYou = Triggers.Initializers.addGlobalListeners;
  }
};
function onImagesLoaded(images, callback) {
  var unloaded = images.length;
  if (unloaded === 0) {
    callback();
  }
  images.each(function() {
    if (this.complete && typeof this.naturalWidth !== "undefined") {
      singleImageLoaded();
    } else {
      var image = new Image();
      var events = "load.zf.images error.zf.images";
      $(image).one(events, function me() {
        $(this).off(events, me);
        singleImageLoaded();
      });
      image.src = $(this).attr("src");
    }
  });
  function singleImageLoaded() {
    unloaded--;
    if (unloaded === 0) {
      callback();
    }
  }
}
function Timer(elem, options, cb) {
  var _this = this, duration = options.duration, nameSpace = Object.keys(elem.data())[0] || "timer", remain = -1, start, timer;
  this.isPaused = false;
  this.restart = function() {
    remain = -1;
    clearTimeout(timer);
    this.start();
  };
  this.start = function() {
    this.isPaused = false;
    clearTimeout(timer);
    remain = remain <= 0 ? duration : remain;
    elem.data("paused", false);
    start = Date.now();
    timer = setTimeout(function() {
      if (options.infinite) {
        _this.restart();
      }
      if (cb && typeof cb === "function") {
        cb();
      }
    }, remain);
    elem.trigger(`timerstart.zf.${nameSpace}`);
  };
  this.pause = function() {
    this.isPaused = true;
    clearTimeout(timer);
    elem.data("paused", true);
    var end = Date.now();
    remain = remain - (end - start);
    elem.trigger(`timerpaused.zf.${nameSpace}`);
  };
}
class Plugin {
  constructor(element, options) {
    this._setup(element, options);
    var pluginName = getPluginName(this);
    this.uuid = GetYoDigits(6, pluginName);
    if (!this.$element.attr(`data-${pluginName}`)) {
      this.$element.attr(`data-${pluginName}`, this.uuid);
    }
    if (!this.$element.data("zfPlugin")) {
      this.$element.data("zfPlugin", this);
    }
    this.$element.trigger(`init.zf.${pluginName}`);
  }
  destroy() {
    this._destroy();
    var pluginName = getPluginName(this);
    this.$element.removeAttr(`data-${pluginName}`).removeData("zfPlugin").trigger(`destroyed.zf.${pluginName}`);
    for (var prop in this) {
      if (this.hasOwnProperty(prop)) {
        this[prop] = null;
      }
    }
  }
}
function hyphenate(str) {
  return str.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase();
}
function getPluginName(obj) {
  return hyphenate(obj.className);
}
const POSITIONS = ["left", "right", "top", "bottom"];
const VERTICAL_ALIGNMENTS = ["top", "bottom", "center"];
const HORIZONTAL_ALIGNMENTS = ["left", "right", "center"];
const ALIGNMENTS = {
  "left": VERTICAL_ALIGNMENTS,
  "right": VERTICAL_ALIGNMENTS,
  "top": HORIZONTAL_ALIGNMENTS,
  "bottom": HORIZONTAL_ALIGNMENTS
};
function nextItem(item, array) {
  var currentIdx = array.indexOf(item);
  if (currentIdx === array.length - 1) {
    return array[0];
  } else {
    return array[currentIdx + 1];
  }
}
class Positionable extends Plugin {
  /**
   * Abstract class encapsulating the tether-like explicit positioning logic
   * including repositioning based on overlap.
   * Expects classes to define defaults for vOffset, hOffset, position,
   * alignment, allowOverlap, and allowBottomOverlap. They can do this by
   * extending the defaults, or (for now recommended due to the way docs are
   * generated) by explicitly declaring them.
   *
   **/
  _init() {
    this.triedPositions = {};
    this.position = this.options.position === "auto" ? this._getDefaultPosition() : this.options.position;
    this.alignment = this.options.alignment === "auto" ? this._getDefaultAlignment() : this.options.alignment;
    this.originalPosition = this.position;
    this.originalAlignment = this.alignment;
  }
  _getDefaultPosition() {
    return "bottom";
  }
  _getDefaultAlignment() {
    switch (this.position) {
      case "bottom":
      case "top":
        return rtl() ? "right" : "left";
      case "left":
      case "right":
        return "bottom";
    }
  }
  /**
   * Adjusts the positionable possible positions by iterating through alignments
   * and positions.
   * @function
   * @private
   */
  _reposition() {
    if (this._alignmentsExhausted(this.position)) {
      this.position = nextItem(this.position, POSITIONS);
      this.alignment = ALIGNMENTS[this.position][0];
    } else {
      this._realign();
    }
  }
  /**
   * Adjusts the dropdown pane possible positions by iterating through alignments
   * on the current position.
   * @function
   * @private
   */
  _realign() {
    this._addTriedPosition(this.position, this.alignment);
    this.alignment = nextItem(this.alignment, ALIGNMENTS[this.position]);
  }
  _addTriedPosition(position, alignment) {
    this.triedPositions[position] = this.triedPositions[position] || [];
    this.triedPositions[position].push(alignment);
  }
  _positionsExhausted() {
    var isExhausted = true;
    for (var i = 0; i < POSITIONS.length; i++) {
      isExhausted = isExhausted && this._alignmentsExhausted(POSITIONS[i]);
    }
    return isExhausted;
  }
  _alignmentsExhausted(position) {
    return this.triedPositions[position] && this.triedPositions[position].length === ALIGNMENTS[position].length;
  }
  // When we're trying to center, we don't want to apply offset that's going to
  // take us just off center, so wrap around to return 0 for the appropriate
  // offset in those alignments.  TODO: Figure out if we want to make this
  // configurable behavior... it feels more intuitive, especially for tooltips, but
  // it's possible someone might actually want to start from center and then nudge
  // slightly off.
  _getVOffset() {
    return this.options.vOffset;
  }
  _getHOffset() {
    return this.options.hOffset;
  }
  _setPosition($anchor, $element, $parent) {
    if ($anchor.attr("aria-expanded") === "false") {
      return false;
    }
    if (!this.options.allowOverlap) {
      this.position = this.originalPosition;
      this.alignment = this.originalAlignment;
    }
    $element.offset(Box.GetExplicitOffsets($element, $anchor, this.position, this.alignment, this._getVOffset(), this._getHOffset()));
    if (!this.options.allowOverlap) {
      var minOverlap = 1e8;
      var minCoordinates = { position: this.position, alignment: this.alignment };
      while (!this._positionsExhausted()) {
        let overlap = Box.OverlapArea($element, $parent, false, false, this.options.allowBottomOverlap);
        if (overlap === 0) {
          return;
        }
        if (overlap < minOverlap) {
          minOverlap = overlap;
          minCoordinates = { position: this.position, alignment: this.alignment };
        }
        this._reposition();
        $element.offset(Box.GetExplicitOffsets($element, $anchor, this.position, this.alignment, this._getVOffset(), this._getHOffset()));
      }
      this.position = minCoordinates.position;
      this.alignment = minCoordinates.alignment;
      $element.offset(Box.GetExplicitOffsets($element, $anchor, this.position, this.alignment, this._getVOffset(), this._getHOffset()));
    }
  }
}
Positionable.defaults = {
  /**
   * Position of positionable relative to anchor. Can be left, right, bottom, top, or auto.
   * @option
   * @type {string}
   * @default 'auto'
   */
  position: "auto",
  /**
   * Alignment of positionable relative to anchor. Can be left, right, bottom, top, center, or auto.
   * @option
   * @type {string}
   * @default 'auto'
   */
  alignment: "auto",
  /**
   * Allow overlap of container/window. If false, dropdown positionable first
   * try to position as defined by data-position and data-alignment, but
   * reposition if it would cause an overflow.
   * @option
   * @type {boolean}
   * @default false
   */
  allowOverlap: false,
  /**
   * Allow overlap of only the bottom of the container. This is the most common
   * behavior for dropdowns, allowing the dropdown to extend the bottom of the
   * screen but not otherwise influence or break out of the container.
   * @option
   * @type {boolean}
   * @default true
   */
  allowBottomOverlap: true,
  /**
   * Number of pixels the positionable should be separated vertically from anchor
   * @option
   * @type {number}
   * @default 0
   */
  vOffset: 0,
  /**
   * Number of pixels the positionable should be separated horizontally from anchor
   * @option
   * @type {number}
   * @default 0
   */
  hOffset: 0
};
class Dropdown extends Positionable {
  /**
   * Creates a new instance of a dropdown.
   * @class
   * @name Dropdown
   * @param {jQuery} element - jQuery object to make into a dropdown.
   *        Object should be of the dropdown panel, rather than its anchor.
   * @param {Object} options - Overrides to the default plugin settings.
   */
  _setup(element, options) {
    this.$element = element;
    this.options = $.extend({}, Dropdown.defaults, this.$element.data(), options);
    this.className = "Dropdown";
    Touch.init($);
    Triggers.init($);
    this._init();
    Keyboard.register("Dropdown", {
      "ENTER": "toggle",
      "SPACE": "toggle",
      "ESCAPE": "close"
    });
  }
  /**
   * Initializes the plugin by setting/checking options and attributes, adding helper variables, and saving the anchor.
   * @function
   * @private
   */
  _init() {
    var $id = this.$element.attr("id");
    this.$anchors = $(`[data-toggle="${$id}"]`).length ? $(`[data-toggle="${$id}"]`) : $(`[data-open="${$id}"]`);
    this.$anchors.attr({
      "aria-controls": $id,
      "data-is-focus": false,
      "data-yeti-box": $id,
      "aria-haspopup": true,
      "aria-expanded": false
    });
    this._setCurrentAnchor(this.$anchors.first());
    if (this.options.parentClass) {
      this.$parent = this.$element.parents("." + this.options.parentClass);
    } else {
      this.$parent = null;
    }
    if (typeof this.$element.attr("aria-labelledby") === "undefined") {
      if (typeof this.$currentAnchor.attr("id") === "undefined") {
        this.$currentAnchor.attr("id", GetYoDigits(6, "dd-anchor"));
      }
      this.$element.attr("aria-labelledby", this.$currentAnchor.attr("id"));
    }
    this.$element.attr({
      "aria-hidden": "true",
      "data-yeti-box": $id,
      "data-resize": $id
    });
    super._init();
    this._events();
  }
  _getDefaultPosition() {
    var position = this.$element[0].className.match(/(top|left|right|bottom)/g);
    if (position) {
      return position[0];
    } else {
      return "bottom";
    }
  }
  _getDefaultAlignment() {
    var horizontalPosition = /float-(\S+)/.exec(this.$currentAnchor.attr("class"));
    if (horizontalPosition) {
      return horizontalPosition[1];
    }
    return super._getDefaultAlignment();
  }
  /**
   * Sets the position and orientation of the dropdown pane, checks for collisions if allow-overlap is not true.
   * Recursively calls itself if a collision is detected, with a new position class.
   * @function
   * @private
   */
  _setPosition() {
    this.$element.removeClass(`has-position-${this.position} has-alignment-${this.alignment}`);
    super._setPosition(this.$currentAnchor, this.$element, this.$parent);
    this.$element.addClass(`has-position-${this.position} has-alignment-${this.alignment}`);
  }
  /**
   * Make it a current anchor.
   * Current anchor as the reference for the position of Dropdown panes.
   * @param {HTML} el - DOM element of the anchor.
   * @function
   * @private
   */
  _setCurrentAnchor(el) {
    this.$currentAnchor = $(el);
  }
  /**
   * Adds event listeners to the element utilizing the triggers utility library.
   * @function
   * @private
   */
  _events() {
    var _this = this, hasTouch = "ontouchstart" in window || typeof window.ontouchstart !== "undefined";
    this.$element.on({
      "open.zf.trigger": this.open.bind(this),
      "close.zf.trigger": this.close.bind(this),
      "toggle.zf.trigger": this.toggle.bind(this),
      "resizeme.zf.trigger": this._setPosition.bind(this)
    });
    this.$anchors.off("click.zf.trigger").on("click.zf.trigger", function(e) {
      _this._setCurrentAnchor(this);
      if (
        // if forceFollow false, always prevent default action
        _this.options.forceFollow === false || // if forceFollow true and hover option true, only prevent default action on 1st click
        // on 2nd click (dropown opened) the default action (e.g. follow a href) gets executed
        hasTouch && _this.options.hover && _this.$element.hasClass("is-open") === false
      ) {
        e.preventDefault();
      }
    });
    if (this.options.hover) {
      this.$anchors.off("mouseenter.zf.dropdown mouseleave.zf.dropdown").on("mouseenter.zf.dropdown", function() {
        _this._setCurrentAnchor(this);
        var bodyData = $("body").data();
        if (typeof bodyData.whatinput === "undefined" || bodyData.whatinput === "mouse") {
          clearTimeout(_this.timeout);
          _this.timeout = setTimeout(function() {
            _this.open();
            _this.$anchors.data("hover", true);
          }, _this.options.hoverDelay);
        }
      }).on("mouseleave.zf.dropdown", ignoreMousedisappear(function() {
        clearTimeout(_this.timeout);
        _this.timeout = setTimeout(function() {
          _this.close();
          _this.$anchors.data("hover", false);
        }, _this.options.hoverDelay);
      }));
      if (this.options.hoverPane) {
        this.$element.off("mouseenter.zf.dropdown mouseleave.zf.dropdown").on("mouseenter.zf.dropdown", function() {
          clearTimeout(_this.timeout);
        }).on("mouseleave.zf.dropdown", ignoreMousedisappear(function() {
          clearTimeout(_this.timeout);
          _this.timeout = setTimeout(function() {
            _this.close();
            _this.$anchors.data("hover", false);
          }, _this.options.hoverDelay);
        }));
      }
    }
    this.$anchors.add(this.$element).on("keydown.zf.dropdown", function(e) {
      var $target = $(this);
      Keyboard.handleKey(e, "Dropdown", {
        open: function() {
          if ($target.is(_this.$anchors) && !$target.is("input, textarea")) {
            _this.open();
            _this.$element.attr("tabindex", -1).focus();
            e.preventDefault();
          }
        },
        close: function() {
          _this.close();
          _this.$anchors.focus();
        }
      });
    });
  }
  /**
   * Adds an event handler to the body to close any dropdowns on a click.
   * @function
   * @private
   */
  _addBodyHandler() {
    var $body = $(document.body).not(this.$element), _this = this;
    $body.off("click.zf.dropdown tap.zf.dropdown").on("click.zf.dropdown tap.zf.dropdown", function(e) {
      if (_this.$anchors.is(e.target) || _this.$anchors.find(e.target).length) {
        return;
      }
      if (_this.$element.is(e.target) || _this.$element.find(e.target).length) {
        return;
      }
      _this.close();
      $body.off("click.zf.dropdown tap.zf.dropdown");
    });
  }
  /**
   * Opens the dropdown pane, and fires a bubbling event to close other dropdowns.
   * @function
   * @fires Dropdown#closeme
   * @fires Dropdown#show
   */
  open() {
    this.$element.trigger("closeme.zf.dropdown", this.$element.attr("id"));
    this.$anchors.addClass("hover").attr({ "aria-expanded": true });
    this.$element.addClass("is-opening");
    this._setPosition();
    this.$element.removeClass("is-opening").addClass("is-open").attr({ "aria-hidden": false });
    if (this.options.autoFocus) {
      var $focusable = Keyboard.findFocusable(this.$element);
      if ($focusable.length) {
        $focusable.eq(0).focus();
      }
    }
    if (this.options.closeOnClick) {
      this._addBodyHandler();
    }
    if (this.options.trapFocus) {
      Keyboard.trapFocus(this.$element);
    }
    this.$element.trigger("show.zf.dropdown", [this.$element]);
  }
  /**
   * Closes the open dropdown pane.
   * @function
   * @fires Dropdown#hide
   */
  close() {
    if (!this.$element.hasClass("is-open")) {
      return false;
    }
    this.$element.removeClass("is-open").attr({ "aria-hidden": true });
    this.$anchors.removeClass("hover").attr("aria-expanded", false);
    this.$element.trigger("hide.zf.dropdown", [this.$element]);
    if (this.options.trapFocus) {
      Keyboard.releaseFocus(this.$element);
    }
  }
  /**
   * Toggles the dropdown pane's visibility.
   * @function
   */
  toggle() {
    if (this.$element.hasClass("is-open")) {
      if (this.$anchors.data("hover")) return;
      this.close();
    } else {
      this.open();
    }
  }
  /**
   * Destroys the dropdown.
   * @function
   */
  _destroy() {
    this.$element.off(".zf.trigger").hide();
    this.$anchors.off(".zf.dropdown");
    $(document.body).off("click.zf.dropdown tap.zf.dropdown");
  }
}
Dropdown.defaults = {
  /**
   * Class that designates bounding container of Dropdown (default: window)
   * @option
   * @type {?string}
   * @default null
   */
  parentClass: null,
  /**
   * Amount of time to delay opening a submenu on hover event.
   * @option
   * @type {number}
   * @default 250
   */
  hoverDelay: 250,
  /**
   * Allow submenus to open on hover events
   * @option
   * @type {boolean}
   * @default false
   */
  hover: false,
  /**
   * Don't close dropdown when hovering over dropdown pane
   * @option
   * @type {boolean}
   * @default false
   */
  hoverPane: false,
  /**
   * Number of pixels between the dropdown pane and the triggering element on open.
   * @option
   * @type {number}
   * @default 0
   */
  vOffset: 0,
  /**
   * Number of pixels between the dropdown pane and the triggering element on open.
   * @option
   * @type {number}
   * @default 0
   */
  hOffset: 0,
  /**
   * Position of dropdown. Can be left, right, bottom, top, or auto.
   * @option
   * @type {string}
   * @default 'auto'
   */
  position: "auto",
  /**
   * Alignment of dropdown relative to anchor. Can be left, right, bottom, top, center, or auto.
   * @option
   * @type {string}
   * @default 'auto'
   */
  alignment: "auto",
  /**
   * Allow overlap of container/window. If false, dropdown will first try to position as defined by data-position and data-alignment, but reposition if it would cause an overflow.
   * @option
   * @type {boolean}
   * @default false
   */
  allowOverlap: false,
  /**
   * Allow overlap of only the bottom of the container. This is the most common
   * behavior for dropdowns, allowing the dropdown to extend the bottom of the
   * screen but not otherwise influence or break out of the container.
   * @option
   * @type {boolean}
   * @default true
   */
  allowBottomOverlap: true,
  /**
   * Allow the plugin to trap focus to the dropdown pane if opened with keyboard commands.
   * @option
   * @type {boolean}
   * @default false
   */
  trapFocus: false,
  /**
   * Allow the plugin to set focus to the first focusable element within the pane, regardless of method of opening.
   * @option
   * @type {boolean}
   * @default false
   */
  autoFocus: false,
  /**
   * Allows a click on the body to close the dropdown.
   * @option
   * @type {boolean}
   * @default false
   */
  closeOnClick: false,
  /**
   * If true the default action of the toggle (e.g. follow a link with href) gets executed on click. If hover option is also true the default action gets prevented on first click for mobile / touch devices and executed on second click.
   * @option
   * @type {boolean}
   * @default true
   */
  forceFollow: true
};
class DropdownMenu extends Plugin {
  /**
   * Creates a new instance of DropdownMenu.
   * @class
   * @name DropdownMenu
   * @fires DropdownMenu#init
   * @param {jQuery} element - jQuery object to make into a dropdown menu.
   * @param {Object} options - Overrides to the default plugin settings.
   */
  _setup(element, options) {
    this.$element = element;
    this.options = $.extend({}, DropdownMenu.defaults, this.$element.data(), options);
    this.className = "DropdownMenu";
    Touch.init($);
    this._init();
    Keyboard.register("DropdownMenu", {
      "ENTER": "open",
      "SPACE": "open",
      "ARROW_RIGHT": "next",
      "ARROW_UP": "up",
      "ARROW_DOWN": "down",
      "ARROW_LEFT": "previous",
      "ESCAPE": "close"
    });
  }
  /**
   * Initializes the plugin, and calls _prepareMenu
   * @private
   * @function
   */
  _init() {
    Nest.Feather(this.$element, "dropdown");
    var subs = this.$element.find("li.is-dropdown-submenu-parent");
    this.$element.children(".is-dropdown-submenu-parent").children(".is-dropdown-submenu").addClass("first-sub");
    this.$menuItems = this.$element.find('li[role="none"]');
    this.$tabs = this.$element.children('li[role="none"]');
    this.$tabs.find("ul.is-dropdown-submenu").addClass(this.options.verticalClass);
    if (this.options.alignment === "auto") {
      if (this.$element.hasClass(this.options.rightClass) || rtl() || this.$element.parents(".top-bar-right").is("*")) {
        this.options.alignment = "right";
        subs.addClass("opens-left");
      } else {
        this.options.alignment = "left";
        subs.addClass("opens-right");
      }
    } else {
      if (this.options.alignment === "right") {
        subs.addClass("opens-left");
      } else {
        subs.addClass("opens-right");
      }
    }
    this.changed = false;
    this._events();
  }
  _isVertical() {
    return this.$tabs.css("display") === "block" || this.$element.css("flex-direction") === "column";
  }
  _isRtl() {
    return this.$element.hasClass("align-right") || rtl() && !this.$element.hasClass("align-left");
  }
  /**
   * Adds event listeners to elements within the menu
   * @private
   * @function
   */
  _events() {
    var _this = this, hasTouch = "ontouchstart" in window || typeof window.ontouchstart !== "undefined", parClass = "is-dropdown-submenu-parent";
    var handleClickFn = function(e) {
      var $elem = $(e.target).parentsUntil("ul", `.${parClass}`), hasSub = $elem.hasClass(parClass), hasClicked = $elem.attr("data-is-click") === "true", $sub = $elem.children(".is-dropdown-submenu");
      if (hasSub) {
        if (hasClicked) {
          if (!_this.options.closeOnClick || !_this.options.clickOpen && !hasTouch || _this.options.forceFollow && hasTouch) {
            return;
          }
          e.stopImmediatePropagation();
          e.preventDefault();
          _this._hide($elem);
        } else {
          e.stopImmediatePropagation();
          e.preventDefault();
          _this._show($sub);
          $elem.add($elem.parentsUntil(_this.$element, `.${parClass}`)).attr("data-is-click", true);
        }
      }
    };
    if (this.options.clickOpen || hasTouch) {
      this.$menuItems.on("click.zf.dropdownMenu touchstart.zf.dropdownMenu", handleClickFn);
    }
    if (_this.options.closeOnClickInside) {
      this.$menuItems.on("click.zf.dropdownMenu", function() {
        var $elem = $(this), hasSub = $elem.hasClass(parClass);
        if (!hasSub) {
          _this._hide();
        }
      });
    }
    if (hasTouch && this.options.disableHoverOnTouch) this.options.disableHover = true;
    if (!this.options.disableHover) {
      this.$menuItems.on("mouseenter.zf.dropdownMenu", function() {
        var $elem = $(this), hasSub = $elem.hasClass(parClass);
        if (hasSub) {
          clearTimeout($elem.data("_delay"));
          $elem.data("_delay", setTimeout(function() {
            _this._show($elem.children(".is-dropdown-submenu"));
          }, _this.options.hoverDelay));
        }
      }).on("mouseleave.zf.dropdownMenu", ignoreMousedisappear(function() {
        var $elem = $(this), hasSub = $elem.hasClass(parClass);
        if (hasSub && _this.options.autoclose) {
          if ($elem.attr("data-is-click") === "true" && _this.options.clickOpen) {
            return false;
          }
          clearTimeout($elem.data("_delay"));
          $elem.data("_delay", setTimeout(function() {
            _this._hide($elem);
          }, _this.options.closingTime));
        }
      }));
    }
    this.$menuItems.on("keydown.zf.dropdownMenu", function(e) {
      var $element = $(e.target).parentsUntil("ul", '[role="none"]'), isTab = _this.$tabs.index($element) > -1, $elements = isTab ? _this.$tabs : $element.siblings("li").add($element), $prevElement, $nextElement;
      $elements.each(function(i) {
        if ($(this).is($element)) {
          $prevElement = $elements.eq(i - 1);
          $nextElement = $elements.eq(i + 1);
          return;
        }
      });
      var nextSibling = function() {
        $nextElement.children("a:first").focus();
        e.preventDefault();
      }, prevSibling = function() {
        $prevElement.children("a:first").focus();
        e.preventDefault();
      }, openSub = function() {
        var $sub = $element.children("ul.is-dropdown-submenu");
        if ($sub.length) {
          _this._show($sub);
          $element.find("li > a:first").focus();
          e.preventDefault();
        } else {
          return;
        }
      }, closeSub = function() {
        var close = $element.parent("ul").parent("li");
        close.children("a:first").focus();
        _this._hide(close);
        e.preventDefault();
      };
      var functions = {
        open: openSub,
        close: function() {
          _this._hide(_this.$element);
          _this.$menuItems.eq(0).children("a").focus();
          e.preventDefault();
        }
      };
      if (isTab) {
        if (_this._isVertical()) {
          if (_this._isRtl()) {
            $.extend(functions, {
              down: nextSibling,
              up: prevSibling,
              next: closeSub,
              previous: openSub
            });
          } else {
            $.extend(functions, {
              down: nextSibling,
              up: prevSibling,
              next: openSub,
              previous: closeSub
            });
          }
        } else {
          if (_this._isRtl()) {
            $.extend(functions, {
              next: prevSibling,
              previous: nextSibling,
              down: openSub,
              up: closeSub
            });
          } else {
            $.extend(functions, {
              next: nextSibling,
              previous: prevSibling,
              down: openSub,
              up: closeSub
            });
          }
        }
      } else {
        if (_this._isRtl()) {
          $.extend(functions, {
            next: closeSub,
            previous: openSub,
            down: nextSibling,
            up: prevSibling
          });
        } else {
          $.extend(functions, {
            next: openSub,
            previous: closeSub,
            down: nextSibling,
            up: prevSibling
          });
        }
      }
      Keyboard.handleKey(e, "DropdownMenu", functions);
    });
  }
  /**
   * Adds an event handler to the body to close any dropdowns on a click.
   * @function
   * @private
   */
  _addBodyHandler() {
    const $body = $(document.body);
    this._removeBodyHandler();
    $body.on("click.zf.dropdownMenu tap.zf.dropdownMenu", (e) => {
      var isItself = !!$(e.target).closest(this.$element).length;
      if (isItself) return;
      this._hide();
      this._removeBodyHandler();
    });
  }
  /**
   * Remove the body event handler. See `_addBodyHandler`.
   * @function
   * @private
   */
  _removeBodyHandler() {
    $(document.body).off("click.zf.dropdownMenu tap.zf.dropdownMenu");
  }
  /**
   * Opens a dropdown pane, and checks for collisions first.
   * @param {jQuery} $sub - ul element that is a submenu to show
   * @function
   * @private
   * @fires DropdownMenu#show
   */
  _show($sub) {
    var idx = this.$tabs.index(this.$tabs.filter(function(i, el) {
      return $(el).find($sub).length > 0;
    }));
    var $sibs = $sub.parent("li.is-dropdown-submenu-parent").siblings("li.is-dropdown-submenu-parent");
    this._hide($sibs, idx);
    $sub.css("visibility", "hidden").addClass("js-dropdown-active").parent("li.is-dropdown-submenu-parent").addClass("is-active");
    var clear = Box.ImNotTouchingYou($sub, null, true);
    if (!clear) {
      var oldClass = this.options.alignment === "left" ? "-right" : "-left", $parentLi = $sub.parent(".is-dropdown-submenu-parent");
      $parentLi.removeClass(`opens${oldClass}`).addClass(`opens-${this.options.alignment}`);
      clear = Box.ImNotTouchingYou($sub, null, true);
      if (!clear) {
        $parentLi.removeClass(`opens-${this.options.alignment}`).addClass("opens-inner");
      }
      this.changed = true;
    }
    $sub.css("visibility", "");
    if (this.options.closeOnClick) {
      this._addBodyHandler();
    }
    this.$element.trigger("show.zf.dropdownMenu", [$sub]);
  }
  /**
   * Hides a single, currently open dropdown pane, if passed a parameter, otherwise, hides everything.
   * @function
   * @param {jQuery} $elem - element with a submenu to hide
   * @param {Number} idx - index of the $tabs collection to hide
   * @fires DropdownMenu#hide
   * @private
   */
  _hide($elem, idx) {
    var $toClose;
    if ($elem && $elem.length) {
      $toClose = $elem;
    } else if (typeof idx !== "undefined") {
      $toClose = this.$tabs.not(function(i) {
        return i === idx;
      });
    } else {
      $toClose = this.$element;
    }
    var somethingToClose = $toClose.hasClass("is-active") || $toClose.find(".is-active").length > 0;
    if (somethingToClose) {
      var $activeItem = $toClose.find("li.is-active");
      $activeItem.add($toClose).attr({
        "data-is-click": false
      }).removeClass("is-active");
      $toClose.find("ul.js-dropdown-active").removeClass("js-dropdown-active");
      if (this.changed || $toClose.find("opens-inner").length) {
        var oldClass = this.options.alignment === "left" ? "right" : "left";
        $toClose.find("li.is-dropdown-submenu-parent").add($toClose).removeClass(`opens-inner opens-${this.options.alignment}`).addClass(`opens-${oldClass}`);
        this.changed = false;
      }
      clearTimeout($activeItem.data("_delay"));
      this._removeBodyHandler();
      this.$element.trigger("hide.zf.dropdownMenu", [$toClose]);
    }
  }
  /**
   * Destroys the plugin.
   * @function
   */
  _destroy() {
    this.$menuItems.off(".zf.dropdownMenu").removeAttr("data-is-click").removeClass("is-right-arrow is-left-arrow is-down-arrow opens-right opens-left opens-inner");
    $(document.body).off(".zf.dropdownMenu");
    Nest.Burn(this.$element, "dropdown");
  }
}
DropdownMenu.defaults = {
  /**
   * Disallows hover events from opening submenus
   * @option
   * @type {boolean}
   * @default false
   */
  disableHover: false,
  /**
   * Disallows hover on touch devices
   * @option
   * @type {boolean}
   * @default true
   */
  disableHoverOnTouch: true,
  /**
   * Allow a submenu to automatically close on a mouseleave event, if not clicked open.
   * @option
   * @type {boolean}
   * @default true
   */
  autoclose: true,
  /**
   * Amount of time to delay opening a submenu on hover event.
   * @option
   * @type {number}
   * @default 50
   */
  hoverDelay: 50,
  /**
   * Allow a submenu to open/remain open on parent click event. Allows cursor to move away from menu.
   * @option
   * @type {boolean}
   * @default false
   */
  clickOpen: false,
  /**
   * Amount of time to delay closing a submenu on a mouseleave event.
   * @option
   * @type {number}
   * @default 500
   */
  closingTime: 500,
  /**
   * Position of the menu relative to what direction the submenus should open. Handled by JS. Can be `'auto'`, `'left'` or `'right'`.
   * @option
   * @type {string}
   * @default 'auto'
   */
  alignment: "auto",
  /**
   * Allow clicks on the body to close any open submenus.
   * @option
   * @type {boolean}
   * @default true
   */
  closeOnClick: true,
  /**
   * Allow clicks on leaf anchor links to close any open submenus.
   * @option
   * @type {boolean}
   * @default true
   */
  closeOnClickInside: true,
  /**
   * Class applied to vertical oriented menus, Foundation default is `vertical`. Update this if using your own class.
   * @option
   * @type {string}
   * @default 'vertical'
   */
  verticalClass: "vertical",
  /**
   * Class applied to right-side oriented menus, Foundation default is `align-right`. Update this if using your own class.
   * @option
   * @type {string}
   * @default 'align-right'
   */
  rightClass: "align-right",
  /**
   * Boolean to force overide the clicking of links to perform default action, on second touch event for mobile.
   * @option
   * @type {boolean}
   * @default true
   */
  forceFollow: true
};
class Accordion extends Plugin {
  /**
   * Creates a new instance of an accordion.
   * @class
   * @name Accordion
   * @fires Accordion#init
   * @param {jQuery} element - jQuery object to make into an accordion.
   * @param {Object} options - a plain object with settings to override the default options.
   */
  _setup(element, options) {
    this.$element = element;
    this.options = $.extend({}, Accordion.defaults, this.$element.data(), options);
    this.className = "Accordion";
    this._init();
    Keyboard.register("Accordion", {
      "ENTER": "toggle",
      "SPACE": "toggle",
      "ARROW_DOWN": "next",
      "ARROW_UP": "previous",
      "HOME": "first",
      "END": "last"
    });
  }
  /**
   * Initializes the accordion by animating the preset active pane(s).
   * @private
   */
  _init() {
    this._isInitializing = true;
    this.$tabs = this.$element.children("[data-accordion-item]");
    this.$tabs.each(function(idx, el) {
      var $el = $(el), $content = $el.children("[data-tab-content]"), id = $content[0].id || GetYoDigits(6, "accordion"), linkId = el.id ? `${el.id}-label` : `${id}-label`;
      $el.find("a:first").attr({
        "aria-controls": id,
        "id": linkId,
        "aria-expanded": false
      });
      $content.attr({ "role": "region", "aria-labelledby": linkId, "aria-hidden": true, "id": id });
    });
    var $initActive = this.$element.find(".is-active").children("[data-tab-content]");
    if ($initActive.length) {
      this._initialAnchor = $initActive.prev("a").attr("href");
      this._openSingleTab($initActive);
    }
    this._checkDeepLink = () => {
      var anchor = window.location.hash;
      if (!anchor.length) {
        if (this._isInitializing) return;
        if (this._initialAnchor) anchor = this._initialAnchor;
      }
      var $anchor = anchor && $(anchor);
      var $link = anchor && this.$element.find(`[href$="${anchor}"]`);
      var isOwnAnchor = !!($anchor.length && $link.length);
      if (isOwnAnchor) {
        if ($anchor && $link && $link.length) {
          if (!$link.parent("[data-accordion-item]").hasClass("is-active")) {
            this._openSingleTab($anchor);
          }
        } else {
          this._closeAllTabs();
        }
        if (this.options.deepLinkSmudge) {
          onLoad($(window), () => {
            var offset = this.$element.offset();
            $("html, body").animate({ scrollTop: offset.top - this.options.deepLinkSmudgeOffset }, this.options.deepLinkSmudgeDelay);
          });
        }
        this.$element.trigger("deeplink.zf.accordion", [$link, $anchor]);
      }
    };
    if (this.options.deepLink) {
      this._checkDeepLink();
    }
    this._events();
    this._isInitializing = false;
  }
  /**
   * Adds event handlers for items within the accordion.
   * @private
   */
  _events() {
    var _this = this;
    this.$tabs.each(function() {
      var $elem = $(this);
      var $tabContent = $elem.children("[data-tab-content]");
      if ($tabContent.length) {
        $elem.children("a").off("click.zf.accordion keydown.zf.accordion").on("click.zf.accordion", function(e) {
          e.preventDefault();
          _this.toggle($tabContent);
        }).on("keydown.zf.accordion", function(e) {
          Keyboard.handleKey(e, "Accordion", {
            toggle: function() {
              _this.toggle($tabContent);
            },
            next: function() {
              var $a = $elem.next().find("a").focus();
              if (!_this.options.multiExpand) {
                $a.trigger("click.zf.accordion");
              }
            },
            previous: function() {
              var $a = $elem.prev().find("a").focus();
              if (!_this.options.multiExpand) {
                $a.trigger("click.zf.accordion");
              }
            },
            first: function() {
              var $a = _this.$tabs.first().find(".accordion-title").focus();
              if (!_this.options.multiExpand) {
                $a.trigger("click.zf.accordion");
              }
            },
            last: function() {
              var $a = _this.$tabs.last().find(".accordion-title").focus();
              if (!_this.options.multiExpand) {
                $a.trigger("click.zf.accordion");
              }
            },
            handled: function() {
              e.preventDefault();
            }
          });
        });
      }
    });
    if (this.options.deepLink) {
      $(window).on("hashchange", this._checkDeepLink);
    }
  }
  /**
   * Toggles the selected content pane's open/close state.
   * @param {jQuery} $target - jQuery object of the pane to toggle (`.accordion-content`).
   * @function
   */
  toggle($target) {
    if ($target.closest("[data-accordion]").is("[disabled]")) {
      console.info("Cannot toggle an accordion that is disabled.");
      return;
    }
    if ($target.parent().hasClass("is-active")) {
      this.up($target);
    } else {
      this.down($target);
    }
    if (this.options.deepLink) {
      var anchor = $target.prev("a").attr("href");
      if (this.options.updateHistory) {
        history.pushState({}, "", anchor);
      } else {
        history.replaceState({}, "", anchor);
      }
    }
  }
  /**
   * Opens the accordion tab defined by `$target`.
   * @param {jQuery} $target - Accordion pane to open (`.accordion-content`).
   * @fires Accordion#down
   * @function
   */
  down($target) {
    if ($target.closest("[data-accordion]").is("[disabled]")) {
      console.info("Cannot call down on an accordion that is disabled.");
      return;
    }
    if (this.options.multiExpand)
      this._openTab($target);
    else
      this._openSingleTab($target);
  }
  /**
   * Closes the tab defined by `$target`.
   * It may be ignored if the Accordion options don't allow it.
   *
   * @param {jQuery} $target - Accordion tab to close (`.accordion-content`).
   * @fires Accordion#up
   * @function
   */
  up($target) {
    if (this.$element.is("[disabled]")) {
      console.info("Cannot call up on an accordion that is disabled.");
      return;
    }
    const $targetItem = $target.parent();
    if (!$targetItem.hasClass("is-active")) return;
    const $othersItems = $targetItem.siblings();
    if (!this.options.allowAllClosed && !$othersItems.hasClass("is-active")) return;
    this._closeTab($target);
  }
  /**
   * Make the tab defined by `$target` the only opened tab, closing all others tabs.
   * @param {jQuery} $target - Accordion tab to open (`.accordion-content`).
   * @function
   * @private
   */
  _openSingleTab($target) {
    const $activeContents = this.$element.children(".is-active").children("[data-tab-content]");
    if ($activeContents.length) {
      this._closeTab($activeContents.not($target));
    }
    this._openTab($target);
  }
  /**
   * Opens the tab defined by `$target`.
   * @param {jQuery} $target - Accordion tab to open (`.accordion-content`).
   * @fires Accordion#down
   * @function
   * @private
   */
  _openTab($target) {
    const $targetItem = $target.parent();
    const targetContentId = $target.attr("aria-labelledby");
    $target.attr("aria-hidden", false);
    $targetItem.addClass("is-active");
    $(`#${targetContentId}`).attr({
      "aria-expanded": true
    });
    $target.finish().slideDown(this.options.slideSpeed, () => {
      this.$element.trigger("down.zf.accordion", [$target]);
    });
  }
  /**
   * Closes the tab defined by `$target`.
   * @param {jQuery} $target - Accordion tab to close (`.accordion-content`).
   * @fires Accordion#up
   * @function
   * @private
   */
  _closeTab($target) {
    const $targetItem = $target.parent();
    const targetContentId = $target.attr("aria-labelledby");
    $target.attr("aria-hidden", true);
    $targetItem.removeClass("is-active");
    $(`#${targetContentId}`).attr({
      "aria-expanded": false
    });
    $target.finish().slideUp(this.options.slideSpeed, () => {
      this.$element.trigger("up.zf.accordion", [$target]);
    });
  }
  /**
   * Closes all active tabs
   * @fires Accordion#up
   * @function
   * @private
   */
  _closeAllTabs() {
    var $activeTabs = this.$element.children(".is-active").children("[data-tab-content]");
    if ($activeTabs.length) {
      this._closeTab($activeTabs);
    }
  }
  /**
   * Destroys an instance of an accordion.
   * @fires Accordion#destroyed
   * @function
   */
  _destroy() {
    this.$element.find("[data-tab-content]").stop(true).slideUp(0).css("display", "");
    this.$element.find("a").off(".zf.accordion");
    if (this.options.deepLink) {
      $(window).off("hashchange", this._checkDeepLink);
    }
  }
}
Accordion.defaults = {
  /**
   * Amount of time to animate the opening of an accordion pane.
   * @option
   * @type {number}
   * @default 250
   */
  slideSpeed: 250,
  /**
   * Allow the accordion to have multiple open panes.
   * @option
   * @type {boolean}
   * @default false
   */
  multiExpand: false,
  /**
   * Allow the accordion to close all panes.
   * @option
   * @type {boolean}
   * @default false
   */
  allowAllClosed: false,
  /**
   * Link the location hash to the open pane.
   * Set the location hash when the opened pane changes, and open and scroll to the corresponding pane when the location changes.
   * @option
   * @type {boolean}
   * @default false
   */
  deepLink: false,
  /**
   * If `deepLink` is enabled, adjust the deep link scroll to make sure the top of the accordion panel is visible
   * @option
   * @type {boolean}
   * @default false
   */
  deepLinkSmudge: false,
  /**
   * If `deepLinkSmudge` is enabled, animation time (ms) for the deep link adjustment
   * @option
   * @type {number}
   * @default 300
   */
  deepLinkSmudgeDelay: 300,
  /**
   * If `deepLinkSmudge` is enabled, the offset for scrollToTtop to prevent overlap by a sticky element at the top of the page
   * @option
   * @type {number}
   * @default 0
   */
  deepLinkSmudgeOffset: 0,
  /**
   * If `deepLink` is enabled, update the browser history with the open accordion
   * @option
   * @type {boolean}
   * @default false
   */
  updateHistory: false
};
class AccordionMenu extends Plugin {
  /**
   * Creates a new instance of an accordion menu.
   * @class
   * @name AccordionMenu
   * @fires AccordionMenu#init
   * @param {jQuery} element - jQuery object to make into an accordion menu.
   * @param {Object} options - Overrides to the default plugin settings.
   */
  _setup(element, options) {
    this.$element = element;
    this.options = $.extend({}, AccordionMenu.defaults, this.$element.data(), options);
    this.className = "AccordionMenu";
    this._init();
    Keyboard.register("AccordionMenu", {
      "ENTER": "toggle",
      "SPACE": "toggle",
      "ARROW_RIGHT": "open",
      "ARROW_UP": "up",
      "ARROW_DOWN": "down",
      "ARROW_LEFT": "close",
      "ESCAPE": "closeAll"
    });
  }
  /**
   * Initializes the accordion menu by hiding all nested menus.
   * @private
   */
  _init() {
    Nest.Feather(this.$element, "accordion");
    var _this = this;
    this.$element.find("[data-submenu]").not(".is-active").slideUp(0);
    this.$element.attr({
      "aria-multiselectable": this.options.multiOpen
    });
    this.$menuLinks = this.$element.find(".is-accordion-submenu-parent");
    this.$menuLinks.each(function() {
      var linkId = this.id || GetYoDigits(6, "acc-menu-link"), $elem = $(this), $sub = $elem.children("[data-submenu]"), subId = $sub[0].id || GetYoDigits(6, "acc-menu"), isActive = $sub.hasClass("is-active");
      if (_this.options.parentLink) {
        let $anchor = $elem.children("a");
        $anchor.clone().prependTo($sub).wrap('<li data-is-parent-link class="is-submenu-parent-item is-submenu-item is-accordion-submenu-item"></li>');
      }
      if (_this.options.submenuToggle) {
        $elem.addClass("has-submenu-toggle");
        $elem.children("a").after('<button id="' + linkId + '" class="submenu-toggle" aria-controls="' + subId + '" aria-expanded="' + isActive + '" title="' + _this.options.submenuToggleText + '"><span class="submenu-toggle-text">' + _this.options.submenuToggleText + "</span></button>");
      } else {
        $elem.attr({
          "aria-controls": subId,
          "aria-expanded": isActive,
          "id": linkId
        });
      }
      $sub.attr({
        "aria-labelledby": linkId,
        "aria-hidden": !isActive,
        "role": "group",
        "id": subId
      });
    });
    var initPanes = this.$element.find(".is-active");
    if (initPanes.length) {
      initPanes.each(function() {
        _this.down($(this));
      });
    }
    this._events();
  }
  /**
   * Adds event handlers for items within the menu.
   * @private
   */
  _events() {
    var _this = this;
    this.$element.find("li").each(function() {
      var $submenu = $(this).children("[data-submenu]");
      if ($submenu.length) {
        if (_this.options.submenuToggle) {
          $(this).children(".submenu-toggle").off("click.zf.accordionMenu").on("click.zf.accordionMenu", function() {
            _this.toggle($submenu);
          });
        } else {
          $(this).children("a").off("click.zf.accordionMenu").on("click.zf.accordionMenu", function(e) {
            e.preventDefault();
            _this.toggle($submenu);
          });
        }
      }
    }).on("keydown.zf.accordionMenu", function(e) {
      var $element = $(this), $elements = $element.parent("ul").children("li"), $prevElement, $nextElement, $target = $element.children("[data-submenu]");
      $elements.each(function(i) {
        if ($(this).is($element)) {
          $prevElement = $elements.eq(Math.max(0, i - 1)).find("a").first();
          $nextElement = $elements.eq(Math.min(i + 1, $elements.length - 1)).find("a").first();
          if ($(this).children("[data-submenu]:visible").length) {
            $nextElement = $element.find("li:first-child").find("a").first();
          }
          if ($(this).is(":first-child")) {
            $prevElement = $element.parents("li").first().find("a").first();
          } else if ($prevElement.parents("li").first().children("[data-submenu]:visible").length) {
            $prevElement = $prevElement.parents("li").find("li:last-child").find("a").first();
          }
          if ($(this).is(":last-child")) {
            $nextElement = $element.parents("li").first().next("li").find("a").first();
          }
          return;
        }
      });
      Keyboard.handleKey(e, "AccordionMenu", {
        open: function() {
          if ($target.is(":hidden")) {
            _this.down($target);
            $target.find("li").first().find("a").first().focus();
          }
        },
        close: function() {
          if ($target.length && !$target.is(":hidden")) {
            _this.up($target);
          } else if ($element.parent("[data-submenu]").length) {
            _this.up($element.parent("[data-submenu]"));
            $element.parents("li").first().find("a").first().focus();
          }
        },
        up: function() {
          $prevElement.focus();
          return true;
        },
        down: function() {
          $nextElement.focus();
          return true;
        },
        toggle: function() {
          if (_this.options.submenuToggle) {
            return false;
          }
          if ($element.children("[data-submenu]").length) {
            _this.toggle($element.children("[data-submenu]"));
            return true;
          }
        },
        closeAll: function() {
          _this.hideAll();
        },
        handled: function(preventDefault) {
          if (preventDefault) {
            e.preventDefault();
          }
        }
      });
    });
  }
  /**
   * Closes all panes of the menu.
   * @function
   */
  hideAll() {
    this.up(this.$element.find("[data-submenu]"));
  }
  /**
   * Opens all panes of the menu.
   * @function
   */
  showAll() {
    this.down(this.$element.find("[data-submenu]"));
  }
  /**
   * Toggles the open/close state of a submenu.
   * @function
   * @param {jQuery} $target - the submenu to toggle
   */
  toggle($target) {
    if (!$target.is(":animated")) {
      if (!$target.is(":hidden")) {
        this.up($target);
      } else {
        this.down($target);
      }
    }
  }
  /**
   * Opens the sub-menu defined by `$target`.
   * @param {jQuery} $target - Sub-menu to open.
   * @fires AccordionMenu#down
   */
  down($target) {
    if (!this.options.multiOpen) {
      const $targetBranch = $target.parentsUntil(this.$element).add($target).add($target.find(".is-active"));
      const $othersActiveSubmenus = this.$element.find(".is-active").not($targetBranch);
      this.up($othersActiveSubmenus);
    }
    $target.addClass("is-active").attr({ "aria-hidden": false });
    if (this.options.submenuToggle) {
      $target.prev(".submenu-toggle").attr({ "aria-expanded": true });
    } else {
      $target.parent(".is-accordion-submenu-parent").attr({ "aria-expanded": true });
    }
    $target.slideDown(this.options.slideSpeed, () => {
      this.$element.trigger("down.zf.accordionMenu", [$target]);
    });
  }
  /**
   * Closes the sub-menu defined by `$target`. All sub-menus inside the target will be closed as well.
   * @param {jQuery} $target - Sub-menu to close.
   * @fires AccordionMenu#up
   */
  up($target) {
    const $submenus = $target.find("[data-submenu]");
    const $allmenus = $target.add($submenus);
    $submenus.slideUp(0);
    $allmenus.removeClass("is-active").attr("aria-hidden", true);
    if (this.options.submenuToggle) {
      $allmenus.prev(".submenu-toggle").attr("aria-expanded", false);
    } else {
      $allmenus.parent(".is-accordion-submenu-parent").attr("aria-expanded", false);
    }
    $target.slideUp(this.options.slideSpeed, () => {
      this.$element.trigger("up.zf.accordionMenu", [$target]);
    });
  }
  /**
   * Destroys an instance of accordion menu.
   * @fires AccordionMenu#destroyed
   */
  _destroy() {
    this.$element.find("[data-submenu]").slideDown(0).css("display", "");
    this.$element.find("a").off("click.zf.accordionMenu");
    this.$element.find("[data-is-parent-link]").detach();
    if (this.options.submenuToggle) {
      this.$element.find(".has-submenu-toggle").removeClass("has-submenu-toggle");
      this.$element.find(".submenu-toggle").remove();
    }
    Nest.Burn(this.$element, "accordion");
  }
}
AccordionMenu.defaults = {
  /**
   * Adds the parent link to the submenu.
   * @option
   * @type {boolean}
   * @default false
   */
  parentLink: false,
  /**
   * Amount of time to animate the opening of a submenu in ms.
   * @option
   * @type {number}
   * @default 250
   */
  slideSpeed: 250,
  /**
   * Adds a separate submenu toggle button. This allows the parent item to have a link.
   * @option
   * @example true
   */
  submenuToggle: false,
  /**
   * The text used for the submenu toggle if enabled. This is used for screen readers only.
   * @option
   * @example true
   */
  submenuToggleText: "Toggle menu",
  /**
   * Allow the menu to have multiple open panes.
   * @option
   * @type {boolean}
   * @default true
   */
  multiOpen: true
};
class OffCanvas extends Plugin {
  /**
   * Creates a new instance of an off-canvas wrapper.
   * @class
   * @name OffCanvas
   * @fires OffCanvas#init
   * @param {Object} element - jQuery object to initialize.
   * @param {Object} options - Overrides to the default plugin settings.
   */
  _setup(element, options) {
    this.className = "OffCanvas";
    this.$element = element;
    this.options = $.extend({}, OffCanvas.defaults, this.$element.data(), options);
    this.contentClasses = { base: [], reveal: [] };
    this.$lastTrigger = $();
    this.$triggers = $();
    this.position = "left";
    this.$content = $();
    this.nested = !!this.options.nested;
    this.$sticky = $();
    this.isInCanvas = false;
    $(["push", "overlap"]).each((index, val) => {
      this.contentClasses.base.push("has-transition-" + val);
    });
    $(["left", "right", "top", "bottom"]).each((index, val) => {
      this.contentClasses.base.push("has-position-" + val);
      this.contentClasses.reveal.push("has-reveal-" + val);
    });
    Triggers.init($);
    MediaQuery._init();
    this._init();
    this._events();
    Keyboard.register("OffCanvas", {
      "ESCAPE": "close"
    });
  }
  /**
   * Initializes the off-canvas wrapper by adding the exit overlay (if needed).
   * @function
   * @private
   */
  _init() {
    var id = this.$element.attr("id");
    this.$element.attr("aria-hidden", "true");
    if (this.options.contentId) {
      this.$content = $("#" + this.options.contentId);
    } else if (this.$element.siblings("[data-off-canvas-content]").length) {
      this.$content = this.$element.siblings("[data-off-canvas-content]").first();
    } else {
      this.$content = this.$element.closest("[data-off-canvas-content]").first();
    }
    if (!this.options.contentId) {
      this.nested = this.$element.siblings("[data-off-canvas-content]").length === 0;
    } else if (this.options.contentId && this.options.nested === null) {
      console.warn("Remember to use the nested option if using the content ID option!");
    }
    if (this.nested === true) {
      this.options.transition = "overlap";
      this.$element.removeClass("is-transition-push");
    }
    this.$element.addClass(`is-transition-${this.options.transition} is-closed`);
    this.$triggers = $(document).find('[data-open="' + id + '"], [data-close="' + id + '"], [data-toggle="' + id + '"]').attr("aria-expanded", "false").attr("aria-controls", id);
    this.position = this.$element.is(".position-left, .position-top, .position-right, .position-bottom") ? this.$element.attr("class").match(/position\-(left|top|right|bottom)/)[1] : this.position;
    if (this.options.contentOverlay === true) {
      var overlay = document.createElement("div");
      var overlayPosition = $(this.$element).css("position") === "fixed" ? "is-overlay-fixed" : "is-overlay-absolute";
      overlay.setAttribute("class", "js-off-canvas-overlay " + overlayPosition);
      this.$overlay = $(overlay);
      if (overlayPosition === "is-overlay-fixed") {
        $(this.$overlay).insertAfter(this.$element);
      } else {
        this.$content.append(this.$overlay);
      }
    }
    var revealOnRegExp = new RegExp(RegExpEscape(this.options.revealClass) + "([^\\s]+)", "g");
    var revealOnClass = revealOnRegExp.exec(this.$element[0].className);
    if (revealOnClass) {
      this.options.isRevealed = true;
      this.options.revealOn = this.options.revealOn || revealOnClass[1];
    }
    if (this.options.isRevealed === true && this.options.revealOn) {
      this.$element.first().addClass(`${this.options.revealClass}${this.options.revealOn}`);
      this._setMQChecker();
    }
    if (this.options.transitionTime) {
      this.$element.css("transition-duration", this.options.transitionTime);
    }
    this.$sticky = this.$content.find("[data-off-canvas-sticky]");
    if (this.$sticky.length > 0 && this.options.transition === "push") {
      this.options.contentScroll = false;
    }
    let inCanvasFor = this.$element.attr("class").match(/\bin-canvas-for-(\w+)/);
    if (inCanvasFor && inCanvasFor.length === 2) {
      this.options.inCanvasOn = inCanvasFor[1];
    } else if (this.options.inCanvasOn) {
      this.$element.addClass(`in-canvas-for-${this.options.inCanvasOn}`);
    }
    if (this.options.inCanvasOn) {
      this._checkInCanvas();
    }
    this._removeContentClasses();
  }
  /**
   * Adds event handlers to the off-canvas wrapper and the exit overlay.
   * @function
   * @private
   */
  _events() {
    this.$element.off(".zf.trigger .zf.offCanvas").on({
      "open.zf.trigger": this.open.bind(this),
      "close.zf.trigger": this.close.bind(this),
      "toggle.zf.trigger": this.toggle.bind(this),
      "keydown.zf.offCanvas": this._handleKeyboard.bind(this)
    });
    if (this.options.closeOnClick === true) {
      var $target = this.options.contentOverlay ? this.$overlay : this.$content;
      $target.on({ "click.zf.offCanvas": this.close.bind(this) });
    }
    if (this.options.inCanvasOn) {
      $(window).on("changed.zf.mediaquery", () => {
        this._checkInCanvas();
      });
    }
  }
  /**
   * Applies event listener for elements that will reveal at certain breakpoints.
   * @private
   */
  _setMQChecker() {
    var _this = this;
    this.onLoadListener = onLoad($(window), function() {
      if (MediaQuery.atLeast(_this.options.revealOn)) {
        _this.reveal(true);
      }
    });
    $(window).on("changed.zf.mediaquery", function() {
      if (MediaQuery.atLeast(_this.options.revealOn)) {
        _this.reveal(true);
      } else {
        _this.reveal(false);
      }
    });
  }
  /**
   * Checks if InCanvas on current breakpoint and adjust off-canvas accordingly
   * @private
   */
  _checkInCanvas() {
    this.isInCanvas = MediaQuery.atLeast(this.options.inCanvasOn);
    if (this.isInCanvas === true) {
      this.close();
    }
  }
  /**
   * Removes the CSS transition/position classes of the off-canvas content container.
   * Removing the classes is important when another off-canvas gets opened that uses the same content container.
   * @param {Boolean} hasReveal - true if related off-canvas element is revealed.
   * @private
   */
  _removeContentClasses(hasReveal) {
    if (typeof hasReveal !== "boolean") {
      this.$content.removeClass(this.contentClasses.base.join(" "));
    } else if (hasReveal === false) {
      this.$content.removeClass(`has-reveal-${this.position}`);
    }
  }
  /**
   * Adds the CSS transition/position classes of the off-canvas content container, based on the opening off-canvas element.
   * Beforehand any transition/position class gets removed.
   * @param {Boolean} hasReveal - true if related off-canvas element is revealed.
   * @private
   */
  _addContentClasses(hasReveal) {
    this._removeContentClasses(hasReveal);
    if (typeof hasReveal !== "boolean") {
      this.$content.addClass(`has-transition-${this.options.transition} has-position-${this.position}`);
    } else if (hasReveal === true) {
      this.$content.addClass(`has-reveal-${this.position}`);
    }
  }
  /**
   * Preserves the fixed behavior of sticky elements on opening an off-canvas with push transition.
   * Since the off-canvas container has got a transform scope in such a case, it is done by calculating position absolute values.
   * @private
   */
  _fixStickyElements() {
    this.$sticky.each((_, el) => {
      const $el = $(el);
      if ($el.css("position") === "fixed") {
        let topVal = parseInt($el.css("top"), 10);
        $el.data("offCanvasSticky", { top: topVal });
        let absoluteTopVal = $(document).scrollTop() + topVal;
        $el.css({ top: `${absoluteTopVal}px`, width: "100%", transition: "none" });
      }
    });
  }
  /**
   * Restores the original fixed styling of sticky elements after having closed an off-canvas that got pseudo fixed beforehand.
   * This reverts the changes of _fixStickyElements()
   * @private
   */
  _unfixStickyElements() {
    this.$sticky.each((_, el) => {
      const $el = $(el);
      let stickyData = $el.data("offCanvasSticky");
      if (typeof stickyData === "object") {
        $el.css({ top: `${stickyData.top}px`, width: "", transition: "" });
        $el.data("offCanvasSticky", "");
      }
    });
  }
  /**
   * Handles the revealing/hiding the off-canvas at breakpoints, not the same as open.
   * @param {Boolean} isRevealed - true if element should be revealed.
   * @function
   */
  reveal(isRevealed) {
    if (isRevealed) {
      this.close();
      this.isRevealed = true;
      this.$element.attr("aria-hidden", "false");
      this.$element.off("open.zf.trigger toggle.zf.trigger");
      this.$element.removeClass("is-closed");
    } else {
      this.isRevealed = false;
      this.$element.attr("aria-hidden", "true");
      this.$element.off("open.zf.trigger toggle.zf.trigger").on({
        "open.zf.trigger": this.open.bind(this),
        "toggle.zf.trigger": this.toggle.bind(this)
      });
      this.$element.addClass("is-closed");
    }
    this._addContentClasses(isRevealed);
  }
  /**
   * Stops scrolling of the body when OffCanvas is open on mobile Safari and other troublesome browsers.
   * @function
   * @private
   */
  _stopScrolling() {
    return false;
  }
  /**
   * Save current finger y-position
   * @param event
   * @private
   */
  _recordScrollable(event) {
    const elem = this;
    elem.lastY = event.touches[0].pageY;
  }
  /**
   * Prevent further scrolling when it hits the edges
   * @param event
   * @private
   */
  _preventDefaultAtEdges(event) {
    const elem = this;
    const _this = event.data;
    const delta = elem.lastY - event.touches[0].pageY;
    elem.lastY = event.touches[0].pageY;
    if (!_this._canScroll(delta, elem)) {
      event.preventDefault();
    }
  }
  /**
   * Handle continuous scrolling of scrollbox
   * Don't bubble up to _preventDefaultAtEdges
   * @param event
   * @private
   */
  _scrollboxTouchMoved(event) {
    const elem = this;
    const _this = event.data;
    const parent = elem.closest("[data-off-canvas], [data-off-canvas-scrollbox-outer]");
    const delta = elem.lastY - event.touches[0].pageY;
    parent.lastY = elem.lastY = event.touches[0].pageY;
    event.stopPropagation();
    if (!_this._canScroll(delta, elem)) {
      if (!_this._canScroll(delta, parent)) {
        event.preventDefault();
      } else {
        parent.scrollTop += delta;
      }
    }
  }
  /**
   * Detect possibility of scrolling
   * @param delta
   * @param elem
   * @returns boolean
   * @private
   */
  _canScroll(delta, elem) {
    const up = delta < 0;
    const down = delta > 0;
    const allowUp = elem.scrollTop > 0;
    const allowDown = elem.scrollTop < elem.scrollHeight - elem.clientHeight;
    return up && allowUp || down && allowDown;
  }
  /**
   * Opens the off-canvas menu.
   * @function
   * @param {Object} event - Event object passed from listener.
   * @param {jQuery} trigger - element that triggered the off-canvas to open.
   * @fires OffCanvas#opened
   * @todo also trigger 'open' event?
   */
  open(event, trigger) {
    if (this.$element.hasClass("is-open") || this.isRevealed || this.isInCanvas) {
      return;
    }
    var _this = this;
    if (trigger) {
      this.$lastTrigger = trigger;
    }
    if (this.options.forceTo === "top") {
      window.scrollTo(0, 0);
    } else if (this.options.forceTo === "bottom") {
      window.scrollTo(0, document.body.scrollHeight);
    }
    if (this.options.transitionTime && this.options.transition !== "overlap") {
      this.$element.siblings("[data-off-canvas-content]").css("transition-duration", this.options.transitionTime);
    } else {
      this.$element.siblings("[data-off-canvas-content]").css("transition-duration", "");
    }
    this.$element.addClass("is-open").removeClass("is-closed");
    this.$triggers.attr("aria-expanded", "true");
    this.$element.attr("aria-hidden", "false");
    this.$content.addClass("is-open-" + this.position);
    if (this.options.contentScroll === false) {
      $("body").addClass("is-off-canvas-open").on("touchmove", this._stopScrolling);
      this.$element.on("touchstart", this._recordScrollable);
      this.$element.on("touchmove", this, this._preventDefaultAtEdges);
      this.$element.on("touchstart", "[data-off-canvas-scrollbox]", this._recordScrollable);
      this.$element.on("touchmove", "[data-off-canvas-scrollbox]", this, this._scrollboxTouchMoved);
    }
    if (this.options.contentOverlay === true) {
      this.$overlay.addClass("is-visible");
    }
    if (this.options.closeOnClick === true && this.options.contentOverlay === true) {
      this.$overlay.addClass("is-closable");
    }
    if (this.options.autoFocus === true) {
      this.$element.one(transitionend(this.$element), function() {
        if (!_this.$element.hasClass("is-open")) {
          return;
        }
        var canvasFocus = _this.$element.find("[data-autofocus]");
        if (canvasFocus.length) {
          canvasFocus.eq(0).focus();
        } else {
          _this.$element.find("a, button").eq(0).focus();
        }
      });
    }
    if (this.options.trapFocus === true) {
      this.$content.attr("tabindex", "-1");
      Keyboard.trapFocus(this.$element);
    }
    if (this.options.transition === "push") {
      this._fixStickyElements();
    }
    this._addContentClasses();
    this.$element.trigger("opened.zf.offCanvas");
    this.$element.one(transitionend(this.$element), () => {
      this.$element.trigger("openedEnd.zf.offCanvas");
    });
  }
  /**
   * Closes the off-canvas menu.
   * @function
   * @param {Function} cb - optional cb to fire after closure.
   * @fires OffCanvas#close
   * @fires OffCanvas#closed
   */
  close() {
    if (!this.$element.hasClass("is-open") || this.isRevealed) {
      return;
    }
    this.$element.trigger("close.zf.offCanvas");
    this.$element.removeClass("is-open");
    this.$element.attr("aria-hidden", "true");
    this.$content.removeClass("is-open-left is-open-top is-open-right is-open-bottom");
    if (this.options.contentOverlay === true) {
      this.$overlay.removeClass("is-visible");
    }
    if (this.options.closeOnClick === true && this.options.contentOverlay === true) {
      this.$overlay.removeClass("is-closable");
    }
    this.$triggers.attr("aria-expanded", "false");
    this.$element.one(transitionend(this.$element), () => {
      this.$element.addClass("is-closed");
      this._removeContentClasses();
      if (this.options.transition === "push") {
        this._unfixStickyElements();
      }
      if (this.options.contentScroll === false) {
        $("body").removeClass("is-off-canvas-open").off("touchmove", this._stopScrolling);
        this.$element.off("touchstart", this._recordScrollable);
        this.$element.off("touchmove", this._preventDefaultAtEdges);
        this.$element.off("touchstart", "[data-off-canvas-scrollbox]", this._recordScrollable);
        this.$element.off("touchmove", "[data-off-canvas-scrollbox]", this._scrollboxTouchMoved);
      }
      if (this.options.trapFocus === true) {
        this.$content.removeAttr("tabindex");
        Keyboard.releaseFocus(this.$element);
      }
      this.$element.trigger("closed.zf.offCanvas");
    });
  }
  /**
   * Toggles the off-canvas menu open or closed.
   * @function
   * @param {Object} event - Event object passed from listener.
   * @param {jQuery} trigger - element that triggered the off-canvas to open.
   */
  toggle(event, trigger) {
    if (this.$element.hasClass("is-open")) {
      this.close(event, trigger);
    } else {
      this.open(event, trigger);
    }
  }
  /**
   * Handles keyboard input when detected. When the escape key is pressed, the off-canvas menu closes, and focus is restored to the element that opened the menu.
   * @function
   * @private
   */
  _handleKeyboard(e) {
    Keyboard.handleKey(e, "OffCanvas", {
      close: () => {
        this.close();
        this.$lastTrigger.focus();
        return true;
      },
      handled: () => {
        e.preventDefault();
      }
    });
  }
  /**
   * Destroys the OffCanvas plugin.
   * @function
   */
  _destroy() {
    this.close();
    this.$element.off(".zf.trigger .zf.offCanvas");
    this.$overlay.off(".zf.offCanvas");
    if (this.onLoadListener) $(window).off(this.onLoadListener);
  }
}
OffCanvas.defaults = {
  /**
   * Allow the user to click outside of the menu to close it.
   * @option
   * @type {boolean}
   * @default true
   */
  closeOnClick: true,
  /**
   * Adds an overlay on top of `[data-off-canvas-content]`.
   * @option
   * @type {boolean}
   * @default true
   */
  contentOverlay: true,
  /**
   * Target an off-canvas content container by ID that may be placed anywhere. If null the closest content container will be taken.
   * @option
   * @type {?string}
   * @default null
   */
  contentId: null,
  /**
   * Define the off-canvas element is nested in an off-canvas content. This is required when using the contentId option for a nested element.
   * @option
   * @type {boolean}
   * @default null
   */
  nested: null,
  /**
   * Enable/disable scrolling of the main content when an off canvas panel is open.
   * @option
   * @type {boolean}
   * @default true
   */
  contentScroll: true,
  /**
   * Amount of time the open and close transition requires, including the appropriate milliseconds (`ms`) or seconds (`s`) unit (e.g. `500ms`, `.75s`) If none selected, pulls from body style.
   * @option
   * @type {string}
   * @default null
   */
  transitionTime: null,
  /**
   * Type of transition for the OffCanvas menu. Options are 'push', 'detached' or 'slide'.
   * @option
   * @type {string}
   * @default push
   */
  transition: "push",
  /**
   * Force the page to scroll to top or bottom on open.
   * @option
   * @type {?string}
   * @default null
   */
  forceTo: null,
  /**
   * Allow the OffCanvas to remain open for certain breakpoints.
   * @option
   * @type {boolean}
   * @default false
   */
  isRevealed: false,
  /**
   * Breakpoint at which to reveal. JS will use a RegExp to target standard classes, if changing classnames, pass your class with the `revealClass` option.
   * @option
   * @type {?string}
   * @default null
   */
  revealOn: null,
  /**
   * Breakpoint at which the off-canvas gets moved into canvas content and acts as regular page element.
   * @option
   * @type {?string}
   * @default null
   */
  inCanvasOn: null,
  /**
   * Force focus to the offcanvas on open. If true, will focus the opening trigger on close.
   * @option
   * @type {boolean}
   * @default true
   */
  autoFocus: true,
  /**
   * Class used to force an OffCanvas to remain open. Foundation defaults for this are `reveal-for-large` & `reveal-for-medium`.
   * @option
   * @type {string}
   * @default reveal-for-
   * @todo improve the regex testing for this.
   */
  revealClass: "reveal-for-",
  /**
   * Triggers optional focus trapping when opening an OffCanvas. Sets tabindex of [data-off-canvas-content] to -1 for accessibility purposes.
   * @option
   * @type {boolean}
   * @default false
   */
  trapFocus: false
};
class Tooltip extends Positionable {
  /**
   * Creates a new instance of a Tooltip.
   * @class
   * @name Tooltip
   * @fires Tooltip#init
   * @param {jQuery} element - jQuery object to attach a tooltip to.
   * @param {Object} options - object to extend the default configuration.
   */
  _setup(element, options) {
    this.$element = element;
    this.options = $.extend({}, Tooltip.defaults, this.$element.data(), options);
    this.className = "Tooltip";
    this.isActive = false;
    this.isClick = false;
    Triggers.init($);
    this._init();
  }
  /**
   * Initializes the tooltip by setting the creating the tip element, adding it's text, setting private variables and setting attributes on the anchor.
   * @private
   */
  _init() {
    MediaQuery._init();
    var elemId = this.$element.attr("aria-describedby") || GetYoDigits(6, "tooltip");
    this.options.tipText = this.options.tipText || this.$element.attr("title");
    this.template = this.options.template ? $(this.options.template) : this._buildTemplate(elemId);
    if (this.options.allowHtml) {
      this.template.appendTo(document.body).html(this.options.tipText).hide();
    } else {
      this.template.appendTo(document.body).text(this.options.tipText).hide();
    }
    this.$element.attr({
      "title": "",
      "aria-describedby": elemId,
      "data-yeti-box": elemId,
      "data-toggle": elemId,
      "data-resize": elemId
    }).addClass(this.options.triggerClass);
    super._init();
    this._events();
  }
  _getDefaultPosition() {
    var elementClassName = this.$element[0].className;
    if (this.$element[0] instanceof SVGElement) {
      elementClassName = elementClassName.baseVal;
    }
    var position = elementClassName.match(/\b(top|left|right|bottom)\b/g);
    return position ? position[0] : "top";
  }
  _getDefaultAlignment() {
    return "center";
  }
  _getHOffset() {
    if (this.position === "left" || this.position === "right") {
      return this.options.hOffset + this.options.tooltipWidth;
    } else {
      return this.options.hOffset;
    }
  }
  _getVOffset() {
    if (this.position === "top" || this.position === "bottom") {
      return this.options.vOffset + this.options.tooltipHeight;
    } else {
      return this.options.vOffset;
    }
  }
  /**
   * builds the tooltip element, adds attributes, and returns the template.
   * @private
   */
  _buildTemplate(id) {
    var templateClasses = `${this.options.tooltipClass} ${this.options.templateClasses}`.trim();
    var $template = $("<div></div>").addClass(templateClasses).attr({
      "role": "tooltip",
      "aria-hidden": true,
      "data-is-active": false,
      "data-is-focus": false,
      "id": id
    });
    return $template;
  }
  /**
   * sets the position class of an element and recursively calls itself until there are no more possible positions to attempt, or the tooltip element is no longer colliding.
   * if the tooltip is larger than the screen width, default to full width - any user selected margin
   * @private
   */
  _setPosition() {
    super._setPosition(this.$element, this.template);
  }
  /**
   * reveals the tooltip, and fires an event to close any other open tooltips on the page
   * @fires Tooltip#closeme
   * @fires Tooltip#show
   * @function
   */
  show() {
    if (this.options.showOn !== "all" && !MediaQuery.is(this.options.showOn)) {
      return false;
    }
    var _this = this;
    this.template.css("visibility", "hidden").show();
    this._setPosition();
    this.template.removeClass("top bottom left right").addClass(this.position);
    this.template.removeClass("align-top align-bottom align-left align-right align-center").addClass("align-" + this.alignment);
    this.$element.trigger("closeme.zf.tooltip", this.template.attr("id"));
    this.template.attr({
      "data-is-active": true,
      "aria-hidden": false
    });
    _this.isActive = true;
    this.template.stop().hide().css("visibility", "").fadeIn(this.options.fadeInDuration, function() {
    });
    this.$element.trigger("show.zf.tooltip");
  }
  /**
   * Hides the current tooltip, and resets the positioning class if it was changed due to collision
   * @fires Tooltip#hide
   * @function
   */
  hide() {
    var _this = this;
    this.template.stop().attr({
      "aria-hidden": true,
      "data-is-active": false
    }).fadeOut(this.options.fadeOutDuration, function() {
      _this.isActive = false;
      _this.isClick = false;
    });
    this.$element.trigger("hide.zf.tooltip");
  }
  /**
   * adds event listeners for the tooltip and its anchor
   * TODO combine some of the listeners like focus and mouseenter, etc.
   * @private
   */
  _events() {
    const _this = this;
    const hasTouch = "ontouchstart" in window || typeof window.ontouchstart !== "undefined";
    var isFocus = false;
    if (hasTouch && this.options.disableForTouch) return;
    if (!this.options.disableHover) {
      this.$element.on("mouseenter.zf.tooltip", function() {
        if (!_this.isActive) {
          _this.timeout = setTimeout(function() {
            _this.show();
          }, _this.options.hoverDelay);
        }
      }).on("mouseleave.zf.tooltip", ignoreMousedisappear(function() {
        clearTimeout(_this.timeout);
        if (!isFocus || _this.isClick && !_this.options.clickOpen) {
          _this.hide();
        }
      }));
    }
    if (hasTouch) {
      this.$element.on("tap.zf.tooltip touchend.zf.tooltip", function() {
        _this.isActive ? _this.hide() : _this.show();
      });
    }
    if (this.options.clickOpen) {
      this.$element.on("mousedown.zf.tooltip", function() {
        if (_this.isClick) ;
        else {
          _this.isClick = true;
          if ((_this.options.disableHover || !_this.$element.attr("tabindex")) && !_this.isActive) {
            _this.show();
          }
        }
      });
    } else {
      this.$element.on("mousedown.zf.tooltip", function() {
        _this.isClick = true;
      });
    }
    this.$element.on({
      // 'toggle.zf.trigger': this.toggle.bind(this),
      // 'close.zf.trigger': this.hide.bind(this)
      "close.zf.trigger": this.hide.bind(this)
    });
    this.$element.on("focus.zf.tooltip", function() {
      isFocus = true;
      if (_this.isClick) {
        if (!_this.options.clickOpen) {
          isFocus = false;
        }
        return false;
      } else {
        _this.show();
      }
    }).on("focusout.zf.tooltip", function() {
      isFocus = false;
      _this.isClick = false;
      _this.hide();
    }).on("resizeme.zf.trigger", function() {
      if (_this.isActive) {
        _this._setPosition();
      }
    });
  }
  /**
   * adds a toggle method, in addition to the static show() & hide() functions
   * @function
   */
  toggle() {
    if (this.isActive) {
      this.hide();
    } else {
      this.show();
    }
  }
  /**
   * Destroys an instance of tooltip, removes template element from the view.
   * @function
   */
  _destroy() {
    this.$element.attr("title", this.template.text()).off(".zf.trigger .zf.tooltip").removeClass(this.options.triggerClass).removeClass("top right left bottom").removeAttr("aria-describedby data-disable-hover data-resize data-toggle data-tooltip data-yeti-box");
    this.template.remove();
  }
}
Tooltip.defaults = {
  /**
   * Time, in ms, before a tooltip should open on hover.
   * @option
   * @type {number}
   * @default 200
   */
  hoverDelay: 200,
  /**
   * Time, in ms, a tooltip should take to fade into view.
   * @option
   * @type {number}
   * @default 150
   */
  fadeInDuration: 150,
  /**
   * Time, in ms, a tooltip should take to fade out of view.
   * @option
   * @type {number}
   * @default 150
   */
  fadeOutDuration: 150,
  /**
   * Disables hover events from opening the tooltip if set to true
   * @option
   * @type {boolean}
   * @default false
   */
  disableHover: false,
  /**
   * Disable the tooltip for touch devices.
   * This can be useful to make elements with a tooltip on it trigger their
   * action on the first tap instead of displaying the tooltip.
   * @option
   * @type {booelan}
   * @default false
   */
  disableForTouch: false,
  /**
   * Optional addtional classes to apply to the tooltip template on init.
   * @option
   * @type {string}
   * @default ''
   */
  templateClasses: "",
  /**
   * Non-optional class added to tooltip templates. Foundation default is 'tooltip'.
   * @option
   * @type {string}
   * @default 'tooltip'
   */
  tooltipClass: "tooltip",
  /**
   * Class applied to the tooltip anchor element.
   * @option
   * @type {string}
   * @default 'has-tip'
   */
  triggerClass: "has-tip",
  /**
   * Minimum breakpoint size at which to open the tooltip.
   * @option
   * @type {string}
   * @default 'small'
   */
  showOn: "small",
  /**
   * Custom template to be used to generate markup for tooltip.
   * @option
   * @type {string}
   * @default ''
   */
  template: "",
  /**
   * Text displayed in the tooltip template on open.
   * @option
   * @type {string}
   * @default ''
   */
  tipText: "",
  touchCloseText: "Tap to close.",
  /**
   * Allows the tooltip to remain open if triggered with a click or touch event.
   * @option
   * @type {boolean}
   * @default true
   */
  clickOpen: true,
  /**
   * Position of tooltip. Can be left, right, bottom, top, or auto.
   * @option
   * @type {string}
   * @default 'auto'
   */
  position: "auto",
  /**
   * Alignment of tooltip relative to anchor. Can be left, right, bottom, top, center, or auto.
   * @option
   * @type {string}
   * @default 'auto'
   */
  alignment: "auto",
  /**
   * Allow overlap of container/window. If false, tooltip will first try to
   * position as defined by data-position and data-alignment, but reposition if
   * it would cause an overflow.  @option
   * @type {boolean}
   * @default false
   */
  allowOverlap: false,
  /**
   * Allow overlap of only the bottom of the container. This is the most common
   * behavior for dropdowns, allowing the dropdown to extend the bottom of the
   * screen but not otherwise influence or break out of the container.
   * Less common for tooltips.
   * @option
   * @type {boolean}
   * @default false
   */
  allowBottomOverlap: false,
  /**
   * Distance, in pixels, the template should push away from the anchor on the Y axis.
   * @option
   * @type {number}
   * @default 0
   */
  vOffset: 0,
  /**
   * Distance, in pixels, the template should push away from the anchor on the X axis
   * @option
   * @type {number}
   * @default 0
   */
  hOffset: 0,
  /**
   * Distance, in pixels, the template spacing auto-adjust for a vertical tooltip
   * @option
   * @type {number}
   * @default 14
   */
  tooltipHeight: 14,
  /**
   * Distance, in pixels, the template spacing auto-adjust for a horizontal tooltip
   * @option
   * @type {number}
   * @default 12
   */
  tooltipWidth: 12,
  /**
  * Allow HTML in tooltip. Warning: If you are loading user-generated content into tooltips,
  * allowing HTML may open yourself up to XSS attacks.
  * @option
  * @type {boolean}
  * @default false
  */
  allowHtml: false
};
class SmoothScroll extends Plugin {
  /**
   * Creates a new instance of SmoothScroll.
   * @class
   * @name SmoothScroll
   * @fires SmoothScroll#init
   * @param {Object} element - jQuery object to add the trigger to.
   * @param {Object} options - Overrides to the default plugin settings.
   */
  _setup(element, options) {
    this.$element = element;
    this.options = $.extend({}, SmoothScroll.defaults, this.$element.data(), options);
    this.className = "SmoothScroll";
    this._init();
  }
  /**
   * Initialize the SmoothScroll plugin
   * @private
   */
  _init() {
    const id = this.$element[0].id || GetYoDigits(6, "smooth-scroll");
    this.$element.attr({ id });
    this._events();
  }
  /**
   * Initializes events for SmoothScroll.
   * @private
   */
  _events() {
    this._linkClickListener = this._handleLinkClick.bind(this);
    this.$element.on("click.zf.smoothScroll", this._linkClickListener);
    this.$element.on("click.zf.smoothScroll", 'a[href^="#"]', this._linkClickListener);
  }
  /**
   * Handle the given event to smoothly scroll to the anchor pointed by the event target.
   * @param {*} e - event
   * @function
   * @private
   */
  _handleLinkClick(e) {
    if (!$(e.currentTarget).is('a[href^="#"]')) return;
    const arrival = e.currentTarget.getAttribute("href");
    this._inTransition = true;
    SmoothScroll.scrollToLoc(arrival, this.options, () => {
      this._inTransition = false;
    });
    e.preventDefault();
  }
  /**
   * Function to scroll to a given location on the page.
   * @param {String} loc - A properly formatted jQuery id selector. Example: '#foo'
   * @param {Object} options - The options to use.
   * @param {Function} callback - The callback function.
   * @static
   * @function
   */
  static scrollToLoc(loc, options = SmoothScroll.defaults, callback) {
    const $loc = $(loc);
    if (!$loc.length) return false;
    var scrollPos = Math.round($loc.offset().top - options.threshold / 2 - options.offset);
    $("html, body").stop(true).animate(
      { scrollTop: scrollPos },
      options.animationDuration,
      options.animationEasing,
      () => {
        if (typeof callback === "function") {
          callback();
        }
      }
    );
  }
  /**
   * Destroys the SmoothScroll instance.
   * @function
   */
  _destroy() {
    this.$element.off("click.zf.smoothScroll", this._linkClickListener);
    this.$element.off("click.zf.smoothScroll", 'a[href^="#"]', this._linkClickListener);
  }
}
SmoothScroll.defaults = {
  /**
   * Amount of time, in ms, the animated scrolling should take between locations.
   * @option
   * @type {number}
   * @default 500
   */
  animationDuration: 500,
  /**
   * Animation style to use when scrolling between locations. Can be `'swing'` or `'linear'`.
   * @option
   * @type {string}
   * @default 'linear'
   * @see {@link https://api.jquery.com/animate|Jquery animate}
   */
  animationEasing: "linear",
  /**
   * Number of pixels to use as a marker for location changes.
   * @option
   * @type {number}
   * @default 50
   */
  threshold: 50,
  /**
   * Number of pixels to offset the scroll of the page on item click if using a sticky nav bar.
   * @option
   * @type {number}
   * @default 0
   */
  offset: 0
};
class Sticky extends Plugin {
  /**
   * Creates a new instance of a sticky thing.
   * @class
   * @name Sticky
   * @param {jQuery} element - jQuery object to make sticky.
   * @param {Object} options - options object passed when creating the element programmatically.
   */
  _setup(element, options) {
    this.$element = element;
    this.options = $.extend({}, Sticky.defaults, this.$element.data(), options);
    this.className = "Sticky";
    Triggers.init($);
    this._init();
  }
  /**
   * Initializes the sticky element by adding classes, getting/setting dimensions, breakpoints and attributes
   * @function
   * @private
   */
  _init() {
    MediaQuery._init();
    var $parent = this.$element.parent("[data-sticky-container]"), id = this.$element[0].id || GetYoDigits(6, "sticky"), _this = this;
    if ($parent.length) {
      this.$container = $parent;
    } else {
      this.wasWrapped = true;
      this.$element.wrap(this.options.container);
      this.$container = this.$element.parent();
    }
    this.$container.addClass(this.options.containerClass);
    this.$element.addClass(this.options.stickyClass).attr({ "data-resize": id, "data-mutate": id });
    if (this.options.anchor !== "") {
      $("#" + _this.options.anchor).attr({ "data-mutate": id });
    }
    this.scrollCount = this.options.checkEvery;
    this.isStuck = false;
    this.onLoadListener = onLoad($(window), function() {
      _this.containerHeight = _this.$element.css("display") === "none" ? 0 : _this.$element[0].getBoundingClientRect().height;
      _this.$container.css("height", _this.containerHeight);
      _this.elemHeight = _this.containerHeight;
      if (_this.options.anchor !== "") {
        _this.$anchor = $("#" + _this.options.anchor);
      } else {
        _this._parsePoints();
      }
      _this._setSizes(function() {
        var scroll = window.pageYOffset;
        _this._calc(false, scroll);
        if (!_this.isStuck) {
          _this._removeSticky(scroll >= _this.topPoint ? false : true);
        }
      });
      _this._events(id.split("-").reverse().join("-"));
    });
  }
  /**
   * If using multiple elements as anchors, calculates the top and bottom pixel values the sticky thing should stick and unstick on.
   * @function
   * @private
   */
  _parsePoints() {
    var top = this.options.topAnchor === "" ? 1 : this.options.topAnchor, btm = this.options.btmAnchor === "" ? document.documentElement.scrollHeight : this.options.btmAnchor, pts = [top, btm], breaks = {};
    for (var i = 0, len = pts.length; i < len && pts[i]; i++) {
      var pt;
      if (typeof pts[i] === "number") {
        pt = pts[i];
      } else {
        var place = pts[i].split(":"), anchor = $(`#${place[0]}`);
        pt = anchor.offset().top;
        if (place[1] && place[1].toLowerCase() === "bottom") {
          pt += anchor[0].getBoundingClientRect().height;
        }
      }
      breaks[i] = pt;
    }
    this.points = breaks;
    return;
  }
  /**
   * Adds event handlers for the scrolling element.
   * @private
   * @param {String} id - pseudo-random id for unique scroll event listener.
   */
  _events(id) {
    var _this = this, scrollListener = this.scrollListener = `scroll.zf.${id}`;
    if (this.isOn) {
      return;
    }
    if (this.canStick) {
      this.isOn = true;
      $(window).off(scrollListener).on(scrollListener, function() {
        if (_this.scrollCount === 0) {
          _this.scrollCount = _this.options.checkEvery;
          _this._setSizes(function() {
            _this._calc(false, window.pageYOffset);
          });
        } else {
          _this.scrollCount--;
          _this._calc(false, window.pageYOffset);
        }
      });
    }
    this.$element.off("resizeme.zf.trigger").on("resizeme.zf.trigger", function() {
      _this._eventsHandler(id);
    });
    this.$element.on("mutateme.zf.trigger", function() {
      _this._eventsHandler(id);
    });
    if (this.$anchor) {
      this.$anchor.on("mutateme.zf.trigger", function() {
        _this._eventsHandler(id);
      });
    }
  }
  /**
   * Handler for events.
   * @private
   * @param {String} id - pseudo-random id for unique scroll event listener.
   */
  _eventsHandler(id) {
    var _this = this, scrollListener = this.scrollListener = `scroll.zf.${id}`;
    _this._setSizes(function() {
      _this._calc(false);
      if (_this.canStick) {
        if (!_this.isOn) {
          _this._events(id);
        }
      } else if (_this.isOn) {
        _this._pauseListeners(scrollListener);
      }
    });
  }
  /**
   * Removes event handlers for scroll and change events on anchor.
   * @fires Sticky#pause
   * @param {String} scrollListener - unique, namespaced scroll listener attached to `window`
   */
  _pauseListeners(scrollListener) {
    this.isOn = false;
    $(window).off(scrollListener);
    this.$element.trigger("pause.zf.sticky");
  }
  /**
   * Called on every `scroll` event and on `_init`
   * fires functions based on booleans and cached values
   * @param {Boolean} checkSizes - true if plugin should recalculate sizes and breakpoints.
   * @param {Number} scroll - current scroll position passed from scroll event cb function. If not passed, defaults to `window.pageYOffset`.
   */
  _calc(checkSizes, scroll) {
    if (checkSizes) {
      this._setSizes();
    }
    if (!this.canStick) {
      if (this.isStuck) {
        this._removeSticky(true);
      }
      return false;
    }
    if (!scroll) {
      scroll = window.pageYOffset;
    }
    if (scroll >= this.topPoint) {
      if (scroll <= this.bottomPoint) {
        if (!this.isStuck) {
          this._setSticky();
        }
      } else {
        if (this.isStuck) {
          this._removeSticky(false);
        }
      }
    } else {
      if (this.isStuck) {
        this._removeSticky(true);
      }
    }
  }
  /**
   * Causes the $element to become stuck.
   * Adds `position: fixed;`, and helper classes.
   * @fires Sticky#stuckto
   * @function
   * @private
   */
  _setSticky() {
    var _this = this, stickTo = this.options.stickTo, mrgn = stickTo === "top" ? "marginTop" : "marginBottom", notStuckTo = stickTo === "top" ? "bottom" : "top", css = {};
    css[mrgn] = `${this.options[mrgn]}em`;
    css[stickTo] = 0;
    css[notStuckTo] = "auto";
    this.isStuck = true;
    this.$element.removeClass(`is-anchored is-at-${notStuckTo}`).addClass(`is-stuck is-at-${stickTo}`).css(css).trigger(`sticky.zf.stuckto:${stickTo}`);
    this.$element.on("transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd", function() {
      _this._setSizes();
    });
  }
  /**
   * Causes the $element to become unstuck.
   * Removes `position: fixed;`, and helper classes.
   * Adds other helper classes.
   * @param {Boolean} isTop - tells the function if the $element should anchor to the top or bottom of its $anchor element.
   * @fires Sticky#unstuckfrom
   * @private
   */
  _removeSticky(isTop) {
    var stickTo = this.options.stickTo, stickToTop = stickTo === "top", css = {}, anchorPt = (this.points ? this.points[1] - this.points[0] : this.anchorHeight) - this.elemHeight, mrgn = stickToTop ? "marginTop" : "marginBottom", topOrBottom = isTop ? "top" : "bottom";
    css[mrgn] = 0;
    css.bottom = "auto";
    if (isTop) {
      css.top = 0;
    } else {
      css.top = anchorPt;
    }
    this.isStuck = false;
    this.$element.removeClass(`is-stuck is-at-${stickTo}`).addClass(`is-anchored is-at-${topOrBottom}`).css(css).trigger(`sticky.zf.unstuckfrom:${topOrBottom}`);
  }
  /**
   * Sets the $element and $container sizes for plugin.
   * Calls `_setBreakPoints`.
   * @param {Function} cb - optional callback function to fire on completion of `_setBreakPoints`.
   * @private
   */
  _setSizes(cb) {
    this.canStick = MediaQuery.is(this.options.stickyOn);
    if (!this.canStick) {
      if (cb && typeof cb === "function") {
        cb();
      }
    }
    var newElemWidth = this.$container[0].getBoundingClientRect().width, comp = window.getComputedStyle(this.$container[0]), pdngl = parseInt(comp["padding-left"], 10), pdngr = parseInt(comp["padding-right"], 10);
    if (this.$anchor && this.$anchor.length) {
      this.anchorHeight = this.$anchor[0].getBoundingClientRect().height;
    } else {
      this._parsePoints();
    }
    this.$element.css({
      "max-width": `${newElemWidth - pdngl - pdngr}px`
    });
    if (this.options.dynamicHeight || !this.containerHeight) {
      var newContainerHeight = this.$element[0].getBoundingClientRect().height || this.containerHeight;
      newContainerHeight = this.$element.css("display") === "none" ? 0 : newContainerHeight;
      this.$container.css("height", newContainerHeight);
      this.containerHeight = newContainerHeight;
    }
    this.elemHeight = this.containerHeight;
    if (!this.isStuck) {
      if (this.$element.hasClass("is-at-bottom")) {
        var anchorPt = (this.points ? this.points[1] - this.$container.offset().top : this.anchorHeight) - this.elemHeight;
        this.$element.css("top", anchorPt);
      }
    }
    this._setBreakPoints(this.containerHeight, function() {
      if (cb && typeof cb === "function") {
        cb();
      }
    });
  }
  /**
   * Sets the upper and lower breakpoints for the element to become sticky/unsticky.
   * @param {Number} elemHeight - px value for sticky.$element height, calculated by `_setSizes`.
   * @param {Function} cb - optional callback function to be called on completion.
   * @private
   */
  _setBreakPoints(elemHeight, cb) {
    if (!this.canStick) {
      if (cb && typeof cb === "function") {
        cb();
      } else {
        return false;
      }
    }
    var mTop = emCalc(this.options.marginTop), mBtm = emCalc(this.options.marginBottom), topPoint = this.points ? this.points[0] : this.$anchor.offset().top, bottomPoint = this.points ? this.points[1] : topPoint + this.anchorHeight, winHeight = window.innerHeight;
    if (this.options.stickTo === "top") {
      topPoint -= mTop;
      bottomPoint -= elemHeight + mTop;
    } else if (this.options.stickTo === "bottom") {
      topPoint -= winHeight - (elemHeight + mBtm);
      bottomPoint -= winHeight - mBtm;
    } else ;
    this.topPoint = topPoint;
    this.bottomPoint = bottomPoint;
    if (cb && typeof cb === "function") {
      cb();
    }
  }
  /**
   * Destroys the current sticky element.
   * Resets the element to the top position first.
   * Removes event listeners, JS-added css properties and classes, and unwraps the $element if the JS added the $container.
   * @function
   */
  _destroy() {
    this._removeSticky(true);
    this.$element.removeClass(`${this.options.stickyClass} is-anchored is-at-top`).css({
      height: "",
      top: "",
      bottom: "",
      "max-width": ""
    }).off("resizeme.zf.trigger").off("mutateme.zf.trigger");
    if (this.$anchor && this.$anchor.length) {
      this.$anchor.off("change.zf.sticky");
    }
    if (this.scrollListener) $(window).off(this.scrollListener);
    if (this.onLoadListener) $(window).off(this.onLoadListener);
    if (this.wasWrapped) {
      this.$element.unwrap();
    } else {
      this.$container.removeClass(this.options.containerClass).css({
        height: ""
      });
    }
  }
}
Sticky.defaults = {
  /**
   * Customizable container template. Add your own classes for styling and sizing.
   * @option
   * @type {string}
   * @default '&lt;div data-sticky-container&gt;&lt;/div&gt;'
   */
  container: "<div data-sticky-container></div>",
  /**
   * Location in the view the element sticks to. Can be `'top'` or `'bottom'`.
   * @option
   * @type {string}
   * @default 'top'
   */
  stickTo: "top",
  /**
   * If anchored to a single element, the id of that element.
   * @option
   * @type {string}
   * @default ''
   */
  anchor: "",
  /**
   * If using more than one element as anchor points, the id of the top anchor.
   * @option
   * @type {string}
   * @default ''
   */
  topAnchor: "",
  /**
   * If using more than one element as anchor points, the id of the bottom anchor.
   * @option
   * @type {string}
   * @default ''
   */
  btmAnchor: "",
  /**
   * Margin, in `em`'s to apply to the top of the element when it becomes sticky.
   * @option
   * @type {number}
   * @default 1
   */
  marginTop: 1,
  /**
   * Margin, in `em`'s to apply to the bottom of the element when it becomes sticky.
   * @option
   * @type {number}
   * @default 1
   */
  marginBottom: 1,
  /**
   * Breakpoint string that is the minimum screen size an element should become sticky.
   * @option
   * @type {string}
   * @default 'medium'
   */
  stickyOn: "medium",
  /**
   * Class applied to sticky element, and removed on destruction. Foundation defaults to `sticky`.
   * @option
   * @type {string}
   * @default 'sticky'
   */
  stickyClass: "sticky",
  /**
   * Class applied to sticky container. Foundation defaults to `sticky-container`.
   * @option
   * @type {string}
   * @default 'sticky-container'
   */
  containerClass: "sticky-container",
  /**
   * If true (by default), keep the sticky container the same height as the element. Otherwise, the container height is set once and does not change.
   * @option
   * @type {boolean}
   * @default true
   */
  dynamicHeight: true,
  /**
   * Number of scroll events between the plugin's recalculating sticky points. Setting it to `0` will cause it to recalc every scroll event, setting it to `-1` will prevent recalc on scroll.
   * @option
   * @type {number}
   * @default -1
   */
  checkEvery: -1
};
function emCalc(em) {
  return parseInt(window.getComputedStyle(document.body, null).fontSize, 10) * em;
}
class Toggler extends Plugin {
  /**
   * Creates a new instance of Toggler.
   * @class
   * @name Toggler
   * @fires Toggler#init
   * @param {Object} element - jQuery object to add the trigger to.
   * @param {Object} options - Overrides to the default plugin settings.
   */
  _setup(element, options) {
    this.$element = element;
    this.options = $.extend({}, Toggler.defaults, element.data(), options);
    this.className = "";
    this.className = "Toggler";
    Triggers.init($);
    this._init();
    this._events();
  }
  /**
   * Initializes the Toggler plugin by parsing the toggle class from data-toggler, or animation classes from data-animate.
   * @function
   * @private
   */
  _init() {
    var id = this.$element[0].id, $triggers = $(`[data-open~="${id}"], [data-close~="${id}"], [data-toggle~="${id}"]`);
    var input;
    if (this.options.animate) {
      input = this.options.animate.split(" ");
      this.animationIn = input[0];
      this.animationOut = input[1] || null;
      $triggers.attr("aria-expanded", !this.$element.is(":hidden"));
    } else {
      input = this.options.toggler;
      if (typeof input !== "string" || !input.length) {
        throw new Error(`The 'toggler' option containing the target class is required, got "${input}"`);
      }
      this.className = input[0] === "." ? input.slice(1) : input;
      $triggers.attr("aria-expanded", this.$element.hasClass(this.className));
    }
    $triggers.each((index, trigger) => {
      const $trigger = $(trigger);
      const controls = $trigger.attr("aria-controls") || "";
      const containsId = new RegExp(`\\b${RegExpEscape(id)}\\b`).test(controls);
      if (!containsId) $trigger.attr("aria-controls", controls ? `${controls} ${id}` : id);
    });
  }
  /**
   * Initializes events for the toggle trigger.
   * @function
   * @private
   */
  _events() {
    this.$element.off("toggle.zf.trigger").on("toggle.zf.trigger", this.toggle.bind(this));
  }
  /**
   * Toggles the target class on the target element. An event is fired from the original trigger depending on if the resultant state was "on" or "off".
   * @function
   * @fires Toggler#on
   * @fires Toggler#off
   */
  toggle() {
    this[this.options.animate ? "_toggleAnimate" : "_toggleClass"]();
  }
  _toggleClass() {
    this.$element.toggleClass(this.className);
    var isOn = this.$element.hasClass(this.className);
    if (isOn) {
      this.$element.trigger("on.zf.toggler");
    } else {
      this.$element.trigger("off.zf.toggler");
    }
    this._updateARIA(isOn);
    this.$element.find("[data-mutate]").trigger("mutateme.zf.trigger");
  }
  _toggleAnimate() {
    var _this = this;
    if (this.$element.is(":hidden")) {
      Motion.animateIn(this.$element, this.animationIn, function() {
        _this._updateARIA(true);
        this.trigger("on.zf.toggler");
        this.find("[data-mutate]").trigger("mutateme.zf.trigger");
      });
    } else {
      Motion.animateOut(this.$element, this.animationOut, function() {
        _this._updateARIA(false);
        this.trigger("off.zf.toggler");
        this.find("[data-mutate]").trigger("mutateme.zf.trigger");
      });
    }
  }
  _updateARIA(isOn) {
    var id = this.$element[0].id;
    $(`[data-open="${id}"], [data-close="${id}"], [data-toggle="${id}"]`).attr({
      "aria-expanded": isOn ? true : false
    });
  }
  /**
   * Destroys the instance of Toggler on the element.
   * @function
   */
  _destroy() {
    this.$element.off(".zf.toggler");
  }
}
Toggler.defaults = {
  /**
   * Class of the element to toggle. It can be provided with or without "."
   * @option
   * @type {string}
   */
  toggler: void 0,
  /**
   * Tells the plugin if the element should animated when toggled.
   * @option
   * @type {boolean}
   * @default false
   */
  animate: false
};
class Abide extends Plugin {
  /**
   * Creates a new instance of Abide.
   * @class
   * @name Abide
   * @fires Abide#init
   * @param {Object} element - jQuery object to add the trigger to.
   * @param {Object} options - Overrides to the default plugin settings.
   */
  _setup(element, options = {}) {
    this.$element = element;
    this.options = $.extend(true, {}, Abide.defaults, this.$element.data(), options);
    this.isEnabled = true;
    this.formnovalidate = null;
    this.className = "Abide";
    this._init();
  }
  /**
   * Initializes the Abide plugin and calls functions to get Abide functioning on load.
   * @private
   */
  _init() {
    this.$inputs = $.merge(
      // Consider as input to validate:
      this.$element.find("input").not('[type="submit"]'),
      // * all input fields expect submit
      this.$element.find("textarea, select")
      // * all textareas and select fields
    );
    this.$submits = this.$element.find('[type="submit"]');
    const $globalErrors = this.$element.find("[data-abide-error]");
    if (this.options.a11yAttributes) {
      this.$inputs.each((i, input) => this.addA11yAttributes($(input)));
      $globalErrors.each((i, error) => this.addGlobalErrorA11yAttributes($(error)));
    }
    this._events();
  }
  /**
   * Initializes events for Abide.
   * @private
   */
  _events() {
    this.$element.off(".abide").on("reset.zf.abide", () => {
      this.resetForm();
    }).on("submit.zf.abide", () => {
      return this.validateForm();
    });
    this.$submits.off("click.zf.abide keydown.zf.abide").on("click.zf.abide keydown.zf.abide", (e) => {
      if (!e.key || (e.key === " " || e.key === "Enter")) {
        e.preventDefault();
        this.formnovalidate = e.target.getAttribute("formnovalidate") !== null;
        this.$element.submit();
      }
    });
    if (this.options.validateOn === "fieldChange") {
      this.$inputs.off("change.zf.abide").on("change.zf.abide", (e) => {
        this.validateInput($(e.target));
      });
    }
    if (this.options.liveValidate) {
      this.$inputs.off("input.zf.abide").on("input.zf.abide", (e) => {
        this.validateInput($(e.target));
      });
    }
    if (this.options.validateOnBlur) {
      this.$inputs.off("blur.zf.abide").on("blur.zf.abide", (e) => {
        this.validateInput($(e.target));
      });
    }
  }
  /**
   * Calls necessary functions to update Abide upon DOM change
   * @private
   */
  _reflow() {
    this._init();
  }
  /**
   * Checks whether the submitted form should be validated or not, consodering formnovalidate and isEnabled
   * @returns {Boolean}
   * @private
   */
  _validationIsDisabled() {
    if (this.isEnabled === false) {
      return true;
    } else if (typeof this.formnovalidate === "boolean") {
      return this.formnovalidate;
    }
    return this.$submits.length ? this.$submits[0].getAttribute("formnovalidate") !== null : false;
  }
  /**
   * Enables the whole validation
   */
  enableValidation() {
    this.isEnabled = true;
  }
  /**
   * Disables the whole validation
   */
  disableValidation() {
    this.isEnabled = false;
  }
  /**
   * Checks whether or not a form element has the required attribute and if it's checked or not
   * @param {Object} element - jQuery object to check for required attribute
   * @returns {Boolean} Boolean value depends on whether or not attribute is checked or empty
   */
  requiredCheck($el) {
    if (!$el.attr("required")) return true;
    var isGood = true;
    switch ($el[0].type) {
      case "checkbox":
        isGood = $el[0].checked;
        break;
      case "select":
      case "select-one":
      case "select-multiple":
        var opt = $el.find("option:selected");
        if (!opt.length || !opt.val()) isGood = false;
        break;
      default:
        if (!$el.val() || !$el.val().length) isGood = false;
    }
    return isGood;
  }
  /**
   * Get:
   * - Based on $el, the first element(s) corresponding to `formErrorSelector` in this order:
   *   1. The element's direct sibling('s).
   *   2. The element's parent's children.
   * - Element(s) with the attribute `[data-form-error-for]` set with the element's id.
   *
   * This allows for multiple form errors per input, though if none are found, no form errors will be shown.
   *
   * @param {Object} $el - jQuery object to use as reference to find the form error selector.
   * @param {String[]} [failedValidators] - List of failed validators.
   * @returns {Object} jQuery object with the selector.
   */
  findFormError($el, failedValidators) {
    var id = $el.length ? $el[0].id : "";
    var $error = $el.siblings(this.options.formErrorSelector);
    if (!$error.length) {
      $error = $el.parent().find(this.options.formErrorSelector);
    }
    if (id) {
      $error = $error.add(this.$element.find(`[data-form-error-for="${id}"]`));
    }
    if (!!failedValidators) {
      $error = $error.not("[data-form-error-on]");
      failedValidators.forEach((v) => {
        $error = $error.add($el.siblings(`[data-form-error-on="${v}"]`));
        $error = $error.add(this.$element.find(`[data-form-error-for="${id}"][data-form-error-on="${v}"]`));
      });
    }
    return $error;
  }
  /**
   * Get the first element in this order:
   * 2. The <label> with the attribute `[for="someInputId"]`
   * 3. The `.closest()` <label>
   *
   * @param {Object} $el - jQuery object to check for required attribute
   * @returns {Boolean} Boolean value depends on whether or not attribute is checked or empty
   */
  findLabel($el) {
    var id = $el[0].id;
    var $label = this.$element.find(`label[for="${id}"]`);
    if (!$label.length) {
      return $el.closest("label");
    }
    return $label;
  }
  /**
   * Get the set of labels associated with a set of radio els in this order
   * 2. The <label> with the attribute `[for="someInputId"]`
   * 3. The `.closest()` <label>
   *
   * @param {Object} $el - jQuery object to check for required attribute
   * @returns {Boolean} Boolean value depends on whether or not attribute is checked or empty
   */
  findRadioLabels($els) {
    var labels = $els.map((i, el) => {
      var id = el.id;
      var $label = this.$element.find(`label[for="${id}"]`);
      if (!$label.length) {
        $label = $(el).closest("label");
      }
      return $label[0];
    });
    return $(labels);
  }
  /**
   * Get the set of labels associated with a set of checkbox els in this order
   * 2. The <label> with the attribute `[for="someInputId"]`
   * 3. The `.closest()` <label>
   *
   * @param {Object} $el - jQuery object to check for required attribute
   * @returns {Boolean} Boolean value depends on whether or not attribute is checked or empty
   */
  findCheckboxLabels($els) {
    var labels = $els.map((i, el) => {
      var id = el.id;
      var $label = this.$element.find(`label[for="${id}"]`);
      if (!$label.length) {
        $label = $(el).closest("label");
      }
      return $label[0];
    });
    return $(labels);
  }
  /**
   * Adds the CSS error class as specified by the Abide settings to the label, input, and the form
   * @param {Object} $el - jQuery object to add the class to
   * @param {String[]} [failedValidators] - List of failed validators.
   */
  addErrorClasses($el, failedValidators) {
    var $label = this.findLabel($el);
    var $formError = this.findFormError($el, failedValidators);
    if ($label.length) {
      $label.addClass(this.options.labelErrorClass);
    }
    if ($formError.length) {
      $formError.addClass(this.options.formErrorClass);
    }
    $el.addClass(this.options.inputErrorClass).attr({
      "data-invalid": "",
      "aria-invalid": true
    });
    if ($formError.filter(":visible").length) {
      this.addA11yErrorDescribe($el, $formError);
    }
  }
  /**
   * Adds [for] and [role=alert] attributes to all form error targetting $el,
   * and [aria-describedby] attribute to $el toward the first form error.
   * @param {Object} $el - jQuery object
   */
  addA11yAttributes($el) {
    let $errors = this.findFormError($el);
    let $labels = $errors.filter("label");
    if (!$errors.length) return;
    let $error = $errors.filter(":visible").first();
    if ($error.length) {
      this.addA11yErrorDescribe($el, $error);
    }
    if ($labels.filter("[for]").length < $labels.length) {
      let elemId = $el.attr("id");
      if (typeof elemId === "undefined") {
        elemId = GetYoDigits(6, "abide-input");
        $el.attr("id", elemId);
      }
      $labels.each((i, label) => {
        const $label = $(label);
        if (typeof $label.attr("for") === "undefined")
          $label.attr("for", elemId);
      });
    }
    $errors.each((i, label) => {
      const $label = $(label);
      if (typeof $label.attr("role") === "undefined")
        $label.attr("role", "alert");
    }).end();
  }
  addA11yErrorDescribe($el, $error) {
    if ($el.attr("type") === "hidden") return;
    if (typeof $el.attr("aria-describedby") !== "undefined") return;
    let errorId = $error.attr("id");
    if (typeof errorId === "undefined") {
      errorId = GetYoDigits(6, "abide-error");
      $error.attr("id", errorId);
    }
    $el.attr("aria-describedby", errorId).data("abide-describedby", true);
  }
  /**
   * Adds [aria-live] attribute to the given global form error $el.
   * @param {Object} $el - jQuery object to add the attribute to
   */
  addGlobalErrorA11yAttributes($el) {
    if (typeof $el.attr("aria-live") === "undefined")
      $el.attr("aria-live", this.options.a11yErrorLevel);
  }
  /**
   * Remove CSS error classes etc from an entire radio button group
   * @param {String} groupName - A string that specifies the name of a radio button group
   *
   */
  removeRadioErrorClasses(groupName) {
    var $els = this.$element.find(`:radio[name="${groupName}"]`);
    var $labels = this.findRadioLabels($els);
    var $formErrors = this.findFormError($els);
    if ($labels.length) {
      $labels.removeClass(this.options.labelErrorClass);
    }
    if ($formErrors.length) {
      $formErrors.removeClass(this.options.formErrorClass);
    }
    $els.removeClass(this.options.inputErrorClass).attr({
      "data-invalid": null,
      "aria-invalid": null
    });
  }
  /**
   * Remove CSS error classes etc from an entire checkbox group
   * @param {String} groupName - A string that specifies the name of a checkbox group
   *
   */
  removeCheckboxErrorClasses(groupName) {
    var $els = this.$element.find(`:checkbox[name="${groupName}"]`);
    var $labels = this.findCheckboxLabels($els);
    var $formErrors = this.findFormError($els);
    if ($labels.length) {
      $labels.removeClass(this.options.labelErrorClass);
    }
    if ($formErrors.length) {
      $formErrors.removeClass(this.options.formErrorClass);
    }
    $els.removeClass(this.options.inputErrorClass).attr({
      "data-invalid": null,
      "aria-invalid": null
    });
  }
  /**
   * Removes CSS error class as specified by the Abide settings from the label, input, and the form
   * @param {Object} $el - jQuery object to remove the class from
   */
  removeErrorClasses($el) {
    if ($el[0].type === "radio") {
      return this.removeRadioErrorClasses($el.attr("name"));
    } else if ($el[0].type === "checkbox") {
      return this.removeCheckboxErrorClasses($el.attr("name"));
    }
    var $label = this.findLabel($el);
    var $formError = this.findFormError($el);
    if ($label.length) {
      $label.removeClass(this.options.labelErrorClass);
    }
    if ($formError.length) {
      $formError.removeClass(this.options.formErrorClass);
    }
    $el.removeClass(this.options.inputErrorClass).attr({
      "data-invalid": null,
      "aria-invalid": null
    });
    if ($el.data("abide-describedby")) {
      $el.removeAttr("aria-describedby").removeData("abide-describedby");
    }
  }
  /**
   * Goes through a form to find inputs and proceeds to validate them in ways specific to their type.
   * Ignores inputs with data-abide-ignore, type="hidden" or disabled attributes set
   * @fires Abide#invalid
   * @fires Abide#valid
   * @param {Object} element - jQuery object to validate, should be an HTML input
   * @returns {Boolean} goodToGo - If the input is valid or not.
   */
  validateInput($el) {
    var clearRequire = this.requiredCheck($el), validator = $el.attr("data-validator"), failedValidators = [], manageErrorClasses = true;
    if (this._validationIsDisabled()) {
      return true;
    }
    if ($el.is("[data-abide-ignore]") || $el.is('[type="hidden"]') || $el.is("[disabled]")) {
      return true;
    }
    switch ($el[0].type) {
      case "radio":
        this.validateRadio($el.attr("name")) || failedValidators.push("required");
        break;
      case "checkbox":
        this.validateCheckbox($el.attr("name")) || failedValidators.push("required");
        manageErrorClasses = false;
        break;
      case "select":
      case "select-one":
      case "select-multiple":
        clearRequire || failedValidators.push("required");
        break;
      default:
        clearRequire || failedValidators.push("required");
        this.validateText($el) || failedValidators.push("pattern");
    }
    if (validator) {
      const required = $el.attr("required") ? true : false;
      validator.split(" ").forEach((v) => {
        this.options.validators[v]($el, required, $el.parent()) || failedValidators.push(v);
      });
    }
    if ($el.attr("data-equalto")) {
      this.options.validators.equalTo($el) || failedValidators.push("equalTo");
    }
    var goodToGo = failedValidators.length === 0;
    var message = (goodToGo ? "valid" : "invalid") + ".zf.abide";
    if (goodToGo) {
      const dependentElements = this.$element.find(`[data-equalto="${$el.attr("id")}"]`);
      if (dependentElements.length) {
        let _this = this;
        dependentElements.each(function() {
          if ($(this).val()) {
            _this.validateInput($(this));
          }
        });
      }
    }
    if (manageErrorClasses) {
      this.removeErrorClasses($el);
      if (!goodToGo) {
        this.addErrorClasses($el, failedValidators);
      }
    }
    $el.trigger(message, [$el]);
    return goodToGo;
  }
  /**
   * Goes through a form and if there are any invalid inputs, it will display the form error element
   * @returns {Boolean} noError - true if no errors were detected...
   * @fires Abide#formvalid
   * @fires Abide#forminvalid
   */
  validateForm() {
    var acc = [];
    var _this = this;
    var checkboxGroupName;
    if (!this.initialized) {
      this.initialized = true;
    }
    if (this._validationIsDisabled()) {
      this.formnovalidate = null;
      return true;
    }
    this.$inputs.each(function() {
      if ($(this)[0].type === "checkbox") {
        if ($(this).attr("name") === checkboxGroupName) return true;
        checkboxGroupName = $(this).attr("name");
      }
      acc.push(_this.validateInput($(this)));
    });
    var noError = acc.indexOf(false) === -1;
    this.$element.find("[data-abide-error]").each((i, elem) => {
      const $elem = $(elem);
      if (this.options.a11yAttributes) this.addGlobalErrorA11yAttributes($elem);
      $elem.css("display", noError ? "none" : "block");
    });
    this.$element.trigger((noError ? "formvalid" : "forminvalid") + ".zf.abide", [this.$element]);
    return noError;
  }
  /**
   * Determines whether or a not a text input is valid based on the pattern specified in the attribute. If no matching pattern is found, returns true.
   * @param {Object} $el - jQuery object to validate, should be a text input HTML element
   * @param {String} pattern - string value of one of the RegEx patterns in Abide.options.patterns
   * @returns {Boolean} Boolean value depends on whether or not the input value matches the pattern specified
   */
  validateText($el, pattern) {
    pattern = pattern || $el.attr("data-pattern") || $el.attr("pattern") || $el.attr("type");
    var inputText = $el.val();
    var valid = true;
    if (inputText.length) {
      if (this.options.patterns.hasOwnProperty(pattern)) {
        valid = this.options.patterns[pattern].test(inputText);
      } else if (pattern !== $el.attr("type")) {
        valid = new RegExp(pattern).test(inputText);
      }
    }
    return valid;
  }
  /**
   * Determines whether or a not a radio input is valid based on whether or not it is required and selected. Although the function targets a single `<input>`, it validates by checking the `required` and `checked` properties of all radio buttons in its group.
   * @param {String} groupName - A string that specifies the name of a radio button group
   * @returns {Boolean} Boolean value depends on whether or not at least one radio input has been selected (if it's required)
   */
  validateRadio(groupName) {
    var $group = this.$element.find(`:radio[name="${groupName}"]`);
    var valid = false, required = false;
    $group.each((i, e) => {
      if ($(e).attr("required")) {
        required = true;
      }
    });
    if (!required) valid = true;
    if (!valid) {
      $group.each((i, e) => {
        if ($(e).prop("checked")) {
          valid = true;
        }
      });
    }
    return valid;
  }
  /**
   * Determines whether or a not a checkbox input is valid based on whether or not it is required and checked. Although the function targets a single `<input>`, it validates by checking the `required` and `checked` properties of all checkboxes in its group.
   * @param {String} groupName - A string that specifies the name of a checkbox group
   * @returns {Boolean} Boolean value depends on whether or not at least one checkbox input has been checked (if it's required)
   */
  validateCheckbox(groupName) {
    var $group = this.$element.find(`:checkbox[name="${groupName}"]`);
    var valid = false, required = false, minRequired = 1, checked = 0;
    $group.each((i, e) => {
      if ($(e).attr("required")) {
        required = true;
      }
    });
    if (!required) valid = true;
    if (!valid) {
      $group.each((i, e) => {
        if ($(e).prop("checked")) {
          checked++;
        }
        if (typeof $(e).attr("data-min-required") !== "undefined") {
          minRequired = parseInt($(e).attr("data-min-required"), 10);
        }
      });
      if (checked >= minRequired) {
        valid = true;
      }
    }
    if (this.initialized !== true && minRequired > 1) {
      return true;
    }
    $group.each((i, e) => {
      if (!valid) {
        this.addErrorClasses($(e), ["required"]);
      } else {
        this.removeErrorClasses($(e));
      }
    });
    return valid;
  }
  /**
   * Determines if a selected input passes a custom validation function. Multiple validations can be used, if passed to the element with `data-validator="foo bar baz"` in a space separated listed.
   * @param {Object} $el - jQuery input element.
   * @param {String} validators - a string of function names matching functions in the Abide.options.validators object.
   * @param {Boolean} required - self explanatory?
   * @returns {Boolean} - true if validations passed.
   */
  matchValidation($el, validators, required) {
    required = required ? true : false;
    var clear = validators.split(" ").map((v) => {
      return this.options.validators[v]($el, required, $el.parent());
    });
    return clear.indexOf(false) === -1;
  }
  /**
   * Resets form inputs and styles
   * @fires Abide#formreset
   */
  resetForm() {
    var $form = this.$element, opts = this.options;
    $(`.${opts.labelErrorClass}`, $form).not("small").removeClass(opts.labelErrorClass);
    $(`.${opts.inputErrorClass}`, $form).not("small").removeClass(opts.inputErrorClass);
    $(`${opts.formErrorSelector}.${opts.formErrorClass}`).removeClass(opts.formErrorClass);
    $form.find("[data-abide-error]").css("display", "none");
    $(":input", $form).not(":button, :submit, :reset, :hidden, :radio, :checkbox, [data-abide-ignore]").val("").attr({
      "data-invalid": null,
      "aria-invalid": null
    });
    $(":input:radio", $form).not("[data-abide-ignore]").prop("checked", false).attr({
      "data-invalid": null,
      "aria-invalid": null
    });
    $(":input:checkbox", $form).not("[data-abide-ignore]").prop("checked", false).attr({
      "data-invalid": null,
      "aria-invalid": null
    });
    $form.trigger("formreset.zf.abide", [$form]);
  }
  /**
   * Destroys an instance of Abide.
   * Removes error styles and classes from elements, without resetting their values.
   */
  _destroy() {
    var _this = this;
    this.$element.off(".abide").find("[data-abide-error]").css("display", "none");
    this.$inputs.off(".abide").each(function() {
      _this.removeErrorClasses($(this));
    });
    this.$submits.off(".abide");
  }
}
Abide.defaults = {
  /**
   * The default event to validate inputs. Checkboxes and radios validate immediately.
   * Remove or change this value for manual validation.
   * @option
   * @type {?string}
   * @default 'fieldChange'
   */
  validateOn: "fieldChange",
  /**
   * Class to be applied to input labels on failed validation.
   * @option
   * @type {string}
   * @default 'is-invalid-label'
   */
  labelErrorClass: "is-invalid-label",
  /**
   * Class to be applied to inputs on failed validation.
   * @option
   * @type {string}
   * @default 'is-invalid-input'
   */
  inputErrorClass: "is-invalid-input",
  /**
   * Class selector to use to target Form Errors for show/hide.
   * @option
   * @type {string}
   * @default '.form-error'
   */
  formErrorSelector: ".form-error",
  /**
   * Class added to Form Errors on failed validation.
   * @option
   * @type {string}
   * @default 'is-visible'
   */
  formErrorClass: "is-visible",
  /**
   * If true, automatically insert when possible:
   * - `[aria-describedby]` on fields
   * - `[role=alert]` on form errors and `[for]` on form error labels
   * - `[aria-live]` on global errors `[data-abide-error]` (see option `a11yErrorLevel`).
   * @option
   * @type {boolean}
   * @default true
   */
  a11yAttributes: true,
  /**
   * [aria-live] attribute value to be applied on global errors `[data-abide-error]`.
   * Options are: 'assertive', 'polite' and 'off'/null
   * @option
   * @see https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/ARIA_Live_Regions
   * @type {string}
   * @default 'assertive'
   */
  a11yErrorLevel: "assertive",
  /**
   * Set to true to validate text inputs on any value change.
   * @option
   * @type {boolean}
   * @default false
   */
  liveValidate: false,
  /**
   * Set to true to validate inputs on blur.
   * @option
   * @type {boolean}
   * @default false
   */
  validateOnBlur: false,
  patterns: {
    alpha: /^[a-zA-Z]+$/,
    // eslint-disable-next-line camelcase
    alpha_numeric: /^[a-zA-Z0-9]+$/,
    integer: /^[-+]?\d+$/,
    number: /^[-+]?\d*(?:[\.\,]\d+)?$/,
    // amex, visa, diners
    card: /^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|(?:222[1-9]|2[3-6][0-9]{2}|27[0-1][0-9]|2720)[0-9]{12}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/,
    cvv: /^([0-9]){3,4}$/,
    // http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#valid-e-mail-address
    email: /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/,
    // From CommonRegexJS (@talyssonoc)
    // https://github.com/talyssonoc/CommonRegexJS/blob/e2901b9f57222bc14069dc8f0598d5f412555411/lib/commonregex.js#L76
    // For more restrictive URL Regexs, see https://mathiasbynens.be/demo/url-regex.
    url: /^((?:(https?|ftps?|file|ssh|sftp):\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\))+(?:\((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?\xab\xbb\u201c\u201d\u2018\u2019]))$/,
    // abc.de
    domain: /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,8}$/,
    datetime: /^([0-2][0-9]{3})\-([0-1][0-9])\-([0-3][0-9])T([0-5][0-9])\:([0-5][0-9])\:([0-5][0-9])(Z|([\-\+]([0-1][0-9])\:00))$/,
    // YYYY-MM-DD
    date: /(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))$/,
    // HH:MM:SS
    time: /^(0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9]){2}$/,
    dateISO: /^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/,
    // MM/DD/YYYY
    // eslint-disable-next-line camelcase
    month_day_year: /^(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.]\d{4}$/,
    // DD/MM/YYYY
    // eslint-disable-next-line camelcase
    day_month_year: /^(0[1-9]|[12][0-9]|3[01])[- \/.](0[1-9]|1[012])[- \/.]\d{4}$/,
    // #FFF or #FFFFFF
    color: /^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/,
    // Domain || URL
    website: {
      test: (text) => {
        return Abide.defaults.patterns.domain.test(text) || Abide.defaults.patterns.url.test(text);
      }
    }
  },
  /**
   * Optional validation functions to be used. `equalTo` being the only default included function.
   * Functions should return only a boolean if the input is valid or not. Functions are given the following arguments:
   * el : The jQuery element to validate.
   * @option
   */
  validators: {
    equalTo: function(el) {
      return $(`#${el.attr("data-equalto")}`).val() === el.val();
    }
  }
};
export {
  $,
  Accordion as A,
  Box as B,
  Dropdown as D,
  Foundation as F,
  GetYoDigits as G,
  Keyboard as K,
  Move as M,
  Nest as N,
  OffCanvas as O,
  RegExpEscape as R,
  SmoothScroll as S,
  Timer as T,
  Triggers as a,
  Touch as b,
  Motion as c,
  MediaQuery as d,
  onLoad as e,
  DropdownMenu as f,
  AccordionMenu as g,
  Tooltip as h,
  ignoreMousedisappear as i,
  Sticky as j,
  Toggler as k,
  Abide as l,
  onImagesLoaded as o,
  rtl as r,
  transitionend as t
};
//# sourceMappingURL=vendor-foundation.js.map
