/* ===========================================================
 * trumbowyg.colors.js v1.2
 * Colors picker plugin for Trumbowyg
 * http://alex-d.github.com/Trumbowyg
 * ===========================================================
 * Author : Alexandre Demode (Alex-D)
 *          Twitter : @AlexandreDemode
 *          Website : alex-d.fr
 */

(function ($) {
    'use strict';

    $.extend(true, $.trumbowyg, {
        langs: {
            // jshint camelcase:false
            en: {
                foreColor: 'Text color',
                backColor: 'Background color',
                foreColorRemove: 'Remove text color',
                backColorRemove: 'Remove background color',
                chooseColor: 'Choose a color'
            },
            az: {
                foreColor: 'Yazı rəngi',
                backColor: 'Arxa plan rəngi',
                foreColorRemove: 'Yazı rəngini sil',
                backColorRemove: 'Arxa plan rəngini sil',
                chooseColor: 'Bir renk seçin'
            },
            by: {
                foreColor: 'Колер тэксту',
                backColor: 'Колер фону тэксту',
                foreColorRemove: 'Выдаліць колер тэксту',
                backColorRemove: 'Выдаліць колер фону тэксту',
                chooseColor: 'Выберите цвет'
            },
            ca: {
                foreColor: 'Color del text',
                backColor: 'Color del fons',
                foreColorRemove: 'Eliminar color del text',
                backColorRemove: 'Eliminar color del fons',
                chooseColor: 'Elija un color'
            },
            cs: {
                foreColor: 'Barva textu',
                backColor: 'Barva pozadí',
                chooseColor: 'Choose a color'
            },
            da: {
                foreColor: 'Tekstfarve',
                backColor: 'Baggrundsfarve',
                chooseColor: 'Choose a color'
            },
            de: {
                foreColor: 'Textfarbe',
                backColor: 'Hintergrundfarbe',
                chooseColor: 'Eine Farbe wählen'
            },
            es: {
                foreColor: 'Color del texto',
                backColor: 'Color del fondo',
                foreColorRemove: 'Eliminar color del texto',
                backColorRemove: 'Eliminar color del fondo',
                chooseColor: 'Elija un color'
            },
            et: {
                foreColor: 'Teksti värv',
                backColor: 'Taustavärv',
                foreColorRemove: 'Eemalda teksti värv',
                backColorRemove: 'Eemalda taustavärv',
                chooseColor: 'Choose a color'
            },
            fr: {
                foreColor: 'Couleur du texte',
                backColor: 'Couleur de fond',
                foreColorRemove: 'Supprimer la couleur du texte',
                backColorRemove: 'Supprimer la couleur de fond',
                chooseColor: 'Choisir une couleur'
            },
            hu: {
                foreColor: 'Betű szín',
                backColor: 'Háttér szín',
                foreColorRemove: 'Betű szín eltávolítása',
                backColorRemove: 'Háttér szín eltávolítása',
                chooseColor: 'Choose a color'
            },
            ja: {
                foreColor: '文字色',
                backColor: '背景色',
                chooseColor: 'Choose a color'
            },
            ko: {
                foreColor: '글자색',
                backColor: '배경색',
                foreColorRemove: '글자색 지우기',
                backColorRemove: '배경색 지우기',
                chooseColor: 'Choose a color'
            },
            nl: {
                foreColor: 'Tekstkleur',
                backColor: 'Achtergrondkleur',
                chooseColor: 'Choose a color'
            },
            pt_br: {
                foreColor: 'Cor de fonte',
                backColor: 'Cor de fundo',
                chooseColor: 'Choose a color'
            },
            ru: {
                foreColor: 'Цвет текста',
                backColor: 'Цвет выделения текста',
                foreColorRemove: 'Очистить цвет текста',
                backColorRemove: 'Очистить цвет выделения текста',
                chooseColor: 'Выберите цвет'
            },
            sl: {
                foreColor: 'Barva teksta',
                backColor: 'Barva ozadja',
                foreColorRemove: 'Ponastavi barvo teksta',
                backColorRemove: 'Ponastavi barvo ozadja',
                chooseColor: 'Choose a color'
            },
            sk: {
                foreColor: 'Farba textu',
                backColor: 'Farba pozadia',
                chooseColor: 'Choose a color'
            },
            tr: {
                foreColor: 'Yazı rengi',
                backColor: 'Arka plan rengi',
                foreColorRemove: 'Yazı rengini kaldır',
                backColorRemove: 'Arka plan rengini kaldır',
                chooseColor: 'Choose a color'
            },
            zh_cn: {
                foreColor: '文字颜色',
                backColor: '背景颜色',
                chooseColor: 'Choose a color'
            },
            zh_tw: {
                foreColor: '文字顏色',
                backColor: '背景顏色',
                chooseColor: 'Choose a color'
            },
        }
    });

    // jshint camelcase:true


    function hex(x) {
        return ('0' + parseInt(x).toString(16)).slice(-2);
    }

    function colorToHex(rgb) {
        if (rgb.search('rgb') === -1) {
            return rgb.replace('#', '');
        } else if (rgb === 'rgba(0, 0, 0, 0)') {
            return 'transparent';
        } else {
            rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d?(.\d+)))?\)$/);
            if (rgb == null) {
                return 'transparent'; // No match, return transparent as unkown color
            }
            return hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
        }
    }

    function colorTagHandler(element, trumbowyg) {
        var tags = [];

        if (!element.style) {
            return tags;
        }

        // background color
        if (element.style.backgroundColor !== '') {
            var backColor = colorToHex(element.style.backgroundColor);
            if (trumbowyg.o.plugins.colors.colorList.indexOf(backColor) >= 0) {
                tags.push('backColor' + backColor);
            } else {
                tags.push('backColorFree');
            }
        }

        // text color
        var foreColor;
        if (element.style.color !== '') {
            foreColor = colorToHex(element.style.color);
        } else if (element.hasAttribute('color')) {
            foreColor = colorToHex(element.getAttribute('color'));
        }
        if (foreColor) {
            if (trumbowyg.o.plugins.colors.colorList.indexOf(foreColor) >= 0) {
                tags.push('foreColor' + foreColor);
            } else {
                tags.push('foreColorFree');
            }
        }

        return tags;
    }

    var defaultOptions = {
        colorList : [
          '000000', 'FFFFFF', '000080', '00008B', '0000CD', '0000FF', '4169E1', '1E90FF', '6495ED', '00BFFF', '87CEFA',
          '0C0C0C', '87CEEB', '00FFFF', '006400', '228B22', '2E8B57', '3CB371', '32CD32', '00E424', '00FF00', '00FF7F',
          '1D1B10', '7FFF00', '7CFC00', 'ADFF2F', '00FA9A', '90EE90', '98FB98', '00CED1', '20B2AA', '008B8B', '4682B4',
          '262626', '3F3F3F', '556B2F', '6B8E23', '9ACD32', '8FBC8F', '008080', '5F9EA0', '40E0D0', '66CDAA', '48D1CC',
          '7F7F7F', '2F4F4F', '494429', '008000', '938953', '808000', 'BDB76B', 'C4BD97', 'B0E0E6', 'ADD8E6', 'F0FFF0',
          '696969', '4B0082', '800000', '8B0000', 'A52A2A', '8B4513', 'A0522D', 'B8860B', 'CD853F', 'DAA520', '483D8B',
          'A5A5A5', '663399', '800080', '8B008B', '9932CC', '9400D3', '8A2BE2', '9370DB', '6A5ACD', '7B68EE', 'BA55D3',
          'A9A9A9', 'C71585', 'FF00FF', 'DA70D6', 'DDA0DD', 'DB7093', 'FF69B4', 'EE82EE', 'FF1493', 'FFB6C1', 'FFC0CB',
          '708090', 'FF8C00', 'FFA500', 'F4A460', 'E9967A', 'D2691E', 'DEB887', 'D2B48C', 'BC8F8F', 'D8BFD8', 'FFF0F5',
          '778899', 'B22222', 'CD5C5C', 'DC143C', 'FF0000', 'FF4500', 'FF6347', 'FA8072', 'FF7F50', 'FFA07A', 'F08080',
          'C0C0C0', '191970', 'FFE4B5', 'F5DEB3', 'FFE4C4', 'F0E68C', 'EEE8AA', 'F5F5DC', 'EEECE1', 'DDD9C3', 'FFEBCD',
          'BFBFBF', '595959', 'FFDEAD', 'FFDAB9', 'FAEBD7', 'FFEFD5', 'FDF5E6', 'FFFAFA', 'FFF5EE', 'E6E6FA', 'FFE4E1',
          'D8D8D8', 'FFD700', 'FFFF00', 'FFFFE0', 'FFFACD', 'FFF8DC', 'FFFFF0', 'FAFAD2', 'FFFAF0', 'F1F1F1', 'F2F2F2',
          'D3D3D3', 'DCDCDC', 'B0C4DE', 'AFEEEE', 'E0FFFF', 'F0F8FF', 'F0FFFF', 'F5FFFA', 'F5F5F5', 'F8F8FF', 'FAF0E6'
        ],
        foreColorList: null, // fallbacks on colorList
        backColorList: null, // fallbacks on colorList
        allowCustomForeColor: true,
        allowCustomBackColor: true,
        displayAsList: false,
    };

    // Add all colors in two dropdowns
    $.extend(true, $.trumbowyg, {
        plugins: {
            color: {
                init: function (trumbowyg) {
                    trumbowyg.o.plugins.colors = trumbowyg.o.plugins.colors || defaultOptions;
                    var dropdownClass = trumbowyg.o.plugins.colors.displayAsList ? trumbowyg.o.prefix + 'dropdown--color-list' : '';

                    var foreColorBtnDef = {
                        dropdown: buildDropdown('foreColor', trumbowyg),
                        dropdownClass: dropdownClass,
                    },
                    backColorBtnDef = {
                        dropdown: buildDropdown('backColor', trumbowyg),
                        dropdownClass: dropdownClass,
                    };

                    trumbowyg.addBtnDef('foreColor', foreColorBtnDef);
                    trumbowyg.addBtnDef('backColor', backColorBtnDef);
                },
                tagHandler: colorTagHandler
            }
        }
    });

    function buildDropdown(fn, trumbowyg) {
        var dropdown = [],
            trumbowygColorOptions = trumbowyg.o.plugins.colors,
            colorList = trumbowygColorOptions[fn + 'List'] || trumbowygColorOptions.colorList;

        $.each(colorList, function (i, color) {
            var btn = fn + color,
                btnDef = {
                    fn: fn,
                    forceCss: true,
                    hasIcon: false,
                    text: trumbowyg.lang['#' + color] || ('#' + color),
                    param: '#' + color,
                    style: 'background-color: #' + color + ';'
                };

            if (trumbowygColorOptions.displayAsList && fn === 'foreColor') {
                btnDef.style = 'color: #' + color + ' !important;';
            }

            trumbowyg.addBtnDef(btn, btnDef);
            dropdown.push(btn);
        });

        // Remove color
        var removeColorButtonName = fn + 'Remove',
            removeColorBtnDef = {
                fn: 'removeFormat',
                hasIcon: false,
                param: fn,
                style: 'background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAG0lEQVQIW2NkQAAfEJMRmwBYhoGBYQtMBYoAADziAp0jtJTgAAAAAElFTkSuQmCC);'
            };

        if (trumbowygColorOptions.displayAsList) {
            removeColorBtnDef.style = '';
        }

        trumbowyg.addBtnDef(removeColorButtonName, removeColorBtnDef);
        dropdown.push(removeColorButtonName);

        // Custom color
        if (trumbowygColorOptions['allowCustom' + fn.charAt(0).toUpperCase() + fn.substr(1)]) {
            // add free color btn
            var freeColorButtonName = fn + 'Free',
                freeColorBtnDef = {
                    fn: function () {
                        trumbowyg.openModalInsert(trumbowyg.lang[fn],
                            {
                                color: {
                                    label: fn,
                                    forceCss: true,
                                    type: 'color',
                                    value: '#FFFFFF'
                                }
                            },
                            // callback
                            function (values) {
                                trumbowyg.execCmd(fn, values.color);
                                return true;
                            }
                        );
                    },
                    hasIcon: false,
                    text: trumbowyg.lang.chooseColor,
                    // style adjust for displaying the text
                    style: 'text-indent: 20px; line-height: 20px; padding: 0 5px;'
                };

            trumbowyg.addBtnDef(freeColorButtonName, freeColorBtnDef);
            dropdown.push(freeColorButtonName);
        }

        return dropdown;
    }
})(jQuery);
