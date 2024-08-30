/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * script name: trumbowyg_init.js
 *
 * var Trumbowyg : object options for trumbowyg
 * launch_trumbowyg( Element, lang );
 *
 */

// options for trumbowyg
var Trumbowyg = {

  setup : {
		autogrow: true,
    autogrowOnEnter: true,
		resetCss: true,
		removeformatPasted: true,
    semantic: {
        'div': 'div'
    },
		tagsToRemove: ['script'],
    tagsToKeep: ['i'],
		btns: [
      ['viewHTML'],
      ['undo', 'redo'], // Only supported in Blink browsers
      ['customFormatting'],
      ['strong', 'em', 'underline', 'del'],
			['fontsize'],
			['foreColor', 'backColor'],
      ['base64'],
			['noembed'],
			['link'],
      ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
      ['unorderedList', 'orderedList'],
      ['table', 'tableCellBackgroundColor', 'tableBorderColor'],
      ['horizontalRule'],
      ['removeformat'],
      ['fullscreen']
		],
		btnsDef: {
      customFormatting: {
        dropdown: ['p', 'h2', 'h3', 'h4', 'blockquote'],
        ico: 'p'
      },
			base64: {
        ico: 'insertImage'
      }
		},
		plugins: {
			fontsize: {
        sizeList: [
            '16px',
            '18px',
            '20px',
						'22px',
						'24px',
						'1rem',
						'1.1rem',
						'1.2rem',
						'1.3rem',
						'1.4rem'
        ],
        allowCustomSize: true
      },
			resizimg: {
        minSize: 50,
        step: 16,
      },
      table: {
        borderWidth : 1
      }
    }
  }
};



/**
 * launch_trumbowyg( Element, lang );
 *
 * @param  {strig}  Element   HTML element to hook trumbowyg
 * @param  {string} lang      lang back -> if aviable in trumbowyg translations / 'en' by default
 * @return {trumbowyg editor} append trumbowyg editor
 */
function launch_trumbowyg( Element, lang ){

    Trumbowyg.setup.lang = lang;

    $(Element).trumbowyg(Trumbowyg.setup);
}
/**
 * launch_trumbowyg( Element, lang );
 */
