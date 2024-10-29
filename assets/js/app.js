jQuery( document ).ready(function($){

	const app = {
		
		model: 'gpt3',
		
		init: () => {
			
			app.cron();
			app.events();
		
		},
		
		events: () => {
			window.addEventListener('beforeunload', function( event ){
				event.stopImmediatePropagation();
			});
			
			$(document).on('click', '.wpai-tab', app.tabs);
			$(document).on('click', '.aiassist-tab', app.wsTabs);
			$(document).on('submit', '#aiassist-sign', app.sign);
			$(document).on('submit', '#aiassist-stat', app.getStat);
			$(document).on('click', 'button[name="step"]', app.statStep);
			$(document).on('click', '#exclude_context', app.excludeContext);
			
			$(document).on('click', '.aiassist-buy', app.buy);
			$(document).on('submit', '#aiassist-custom-buy', app.buyForm);
			$(document).on('focus', '#out_summ', app.outSummFocus);
			$(document).on('blur', '#out_summ', app.outSummFocusOut);
			
			$(document).on('click', '#aiassist-addItemRewrite', app.addItemRewrite);
			$(document).on('click', '.aiassist-rewrite-item-close', app.rewriteItemClose);
			
			$(document).on('click', '#aiassist-addItemArticle', app.addItemArticle);
			$(document).on('click', '.aiassist-article-item-close', app.queueArticleClose);
			$(document).on('click', '#start-articles-generations', app.startArticlesGeneration);
			$(document).on('click', '#stop-articles-generations', app.stopArticlesGeneration);
			$(document).on('click', '#clear-articles-generations', app.clearArticlesGeneration);
			
			$(document).on('change', '.aiassist-auto-options', app.autoGenOptions);
			$(document).on('change', '.aiassist-rewrite-options', app.rewriteOptions);
			
			$(document).on('click', '#start-rewrite-generations', app.startRewriteGenerations);
			$(document).on('click', '#clear-rewrite-generations', app.clearRewritesGeneration);
			$(document).on('click', '#stop-rewrite-generations', app.stopRewriteGeneration);
			
			setTimeout( () => $('#AIASSIST-tmce').click(), 1000);
			$(document).on('tinymce-editor-setup', function(event, editor){
				if( editor.id != 'AIASSIST' )
					return;
				
				app.editor = tinymce.get( editor.id );
				$(document).on('click', '#aiassist-step-stop', app.stepStop);
				$(document).on('click', '#aiassist-theme-generate', app.generateHeader);
				$(document).on('click', '#aiassist-structure-generate', app.generateStructure);
				$(document).on('click', '#aiassist-content-generate', app.generateContent);
				$(document).on('click', '#aiassist-standart-generate', app.standartGenerateContent);
				$(document).on('click', '#aiassist-meta-generate', app.generateMeta);
				$(document).on('click', '#aiassist-save-content', app.saveContent);
				$(document).on('click', '#aiassist-images-generator-all-headers', app.checkAllHeaders);
				$(document).on('click', '.image-generate-item', app.imageGenerator);
				$(document).on('click', '#aiassist-images-generator-start', app.imagesGenerator);
				$(document).on('click', '.aiassist-images .aiassist-image', app.selectImage);
				$(document).on('change', '#aiassist-change-image-model', app.translatePromtsToImages);
				
			});
			
			$(document).on('input', '#aiassist-gpt-key', app.saveKey);
			$(document).on('change', '#aiassist-change-text-model', app.setTextModel);
			$(document).on('change', '#aiassist-change-image-model', app.setImageModel);
			$(document).on('change', '#aiassist-image-model', app.setAutoImageModel);

			
			$(document).on('click', '#aiassist-tiny-image-save', app.tinyMceImageSave);
			$(document).on('click', '#aiassist-generate-image-close', app.tonyMcePopUpHide );
			$(document).on('click', '#aiassist-tiny-image-translate', app.tinyMceTranslate );
			$(document).on('click', '#aiassist-tiny-image-generate', app.tinyMceImageGenerate );
			
			$(document).on('click', '#aiassist-clear-content', app.clearContent );
			$(document).on('click', '.aiassist-set-default-promts', app.setDefaultPromts );
			$(document).on('click', '.aiassist-set-default-promts-regenerate', app.setDefaultPromtsRegenerate );

			if( ( tab = app.getCookie('activeTab') ) && ! $('.wpai-warning-limits').length )
				$('.aiassist-tab[data-tab="'+ tab +'"]').click();
				
			if( $('.wpai-warning-limits').length && $('.aiassist-tab[data-tab="rates"]').length )
				$('.aiassist-tab[data-tab="rates"]').click();
			
			if( ! aiassist.token )
				$('.aiassist-tab[data-tab="settings"]').click();
				
			if( imgModel = app.getCookie('image-model') ){
				$('#aiassist-change-image-model').val( imgModel );
				app.translatePromtsToImages();
			}
				
			if( imgModelAuto = app.getCookie('image-model-auto') )
				$('#aiassist-image-model').val( imgModelAuto );
				
			if( textModel = app.getCookie('text-model') ){
				app.model = textModel;
				$('#aiassist-change-text-model').val( textModel );
			}
			
			$(document).on('click', '.ai-image', app.selectImageInBlock);
			
			$(document).on('click', '.aiassist-post-restore', app.postRestore);
			$(document).on('change', '#rewrite_all', app.rewriteAllSiteChecked);
			$(document).on('change', 'input[name*="rewrite_type"]', app.rewriteInputsChecked);
			$(document).on('change', 'select.cat-rewrite', app.disabledRewriteUrlArea);
			
			$(document).on('input', '.aiassist-prom', app.savePromt);
			$(document).on('change', 'select.aiassist-lang-promts', app.changeLangPromts);
			$(document).on('change', 'select.aiassist-lang-promts-regenerate', app.changeLangPromtsToRegenerate);
			
			$(document).on('click', '.pay-method', app.setPayMethod);
			$(document).on('click', '.aiassist-copy', app.copy);
			$(document).on('submit', '#aiassist-get-bonus', app.getBonus);

			
			if( window.location.hash == '#ai_assistant' && $('#ai_assistant').length )
				$('html, body').animate( { scrollTop: $('#ai_assistant').offset().top }, 1000);
			
		},
		
		getBonus: async function(event){
			event.preventDefault();

			let e = $(this);
			e.find('button').before('<div>Запрос на выплату отправлен</div>').addClass('disabled');
			let args = app.getFormData( e );
			e[0].reset();
			await app.request( Object.assign( args, { action: 'getBonus', nonce: aiassist.nonce } ) );
		},
		
		copy: function(){
			let e = $(this);
			
			e.addClass('active').delay(200).queue( next => {
				e.removeClass('active');
				next();
			})
			
			app.buffer( e.text() );
		},
		
		setPayMethod: function(){
			$('.pay-method').removeClass('active');
			$(this).addClass('active');
						
			$('.rates-block-robokassa, .rates-block-cryptocloud').addClass('hide');
			$('.rates-block-'+ $(this).find('.robokassa, .cryptocloud').attr('class') ).removeClass('hide');
		},
		
		savePromt: async function( event ){
			clearTimeout( app.t );
			
			let e = $(this);
			let promt = e.val();
			
			let lang_id = parseInt( $('.aiassist-lang-promts:visible:first').val() );
			
			if( isNaN( lang_id ) )
				lang_id = 0;
		
			switch( e.attr('id') ){
				case 'aiassist-rewrite-prom':
					aiassist.promts['rewrite'][ lang_id ] = promt;
				break;
				case 'aiassist-generation-prom':
					aiassist.promts['multi'][ lang_id ] = promt;
				break;
				case 'aiassist-title-prom-multi':
					aiassist.promts['multi_title'][ lang_id ] = promt;
				break;
				case 'aiassist-desc-prom-multi':
					aiassist.promts['multi_desc'][ lang_id ] = promt;
				break;
				case 'aiassist-article-prom':
					aiassist.promts['short'][ lang_id ] = promt;
				break;
				case 'aiassist-theme-prom':
					aiassist.promts['long_header'][ lang_id ] = promt;
				break;
				case 'aiassist-structure-prom':
					aiassist.promts['long_structure'][ lang_id ] = promt;
				break;
				case 'aiassist-content-prom':
					aiassist.promts['long'][ lang_id ] = promt;
				break;
				case 'aiassist-title-prom':
					aiassist.promts['long_title'][ lang_id ] = promt;
				break;
				case 'aiassist-desc-prom':
					aiassist.promts['long_desc'][ lang_id ] = promt;
				break;
				case 'aiassist-prom-regenerate':
					aiassist.promts['regenerate'][ parseInt( $('.aiassist-lang-promts-regenerate').val() ) ] = promt;
				break;	
			}
			
			app.t = setTimeout( async () => {
				await app.request( { val: aiassist.promts, act: 'promts', action: 'saveStep', nonce: aiassist.nonce } );
			}, 1500);
		},
		
		changeLangPromts: async function(){
			app.setLangPromts( $(this).val() )
		},
		
		setDefaultPromts: () => {
			if( ! confirm('Are you sure?') )
				return false;
			
			app.setLangPromts( $('.aiassist-lang-promts:visible:first').val(), true )
		},
		
		setDefaultPromtsRegenerate: async () => {
			if( ! confirm('Are you sure?') )
				return false;
			
			lang = parseInt( $('.aiassist-lang-promts-regenerate').val() );
			
			if( isNaN( lang ) )
				lang = 0;
			
			if( typeof aiassist.promts.regenerate[ lang ] !== 'undefined' ){	
				aiassist.promts.regenerate	= aiassist.info.promts.regenerate;
				$('#aiassist-prom-regenerate').val( aiassist.promts.regenerate[ lang ] )
				await app.request( { val: aiassist.promts, act: 'promts', action: 'saveStep', nonce: aiassist.nonce } );
			}
		},
		
		changeLangPromtsToRegenerate: async function(){
			let lang = parseInt( $(this).val() );
			
			if( typeof aiassist.promts.regenerate[ lang ] !== 'undefined' && $('#aiassist-prom-regenerate').is(':visible') ){
				aiassist.promts['regenerate_lang'] = lang;
				
				if( $('#aiassist-prom-regenerate').is(':visible') )
					$('#aiassist-prom-regenerate').val( aiassist.promts.regenerate[ lang ] )
				
				await app.request( { val: aiassist.promts, act: 'promts', action: 'saveStep', nonce: aiassist.nonce } );
			}
		},
		
		setLangPromts: async ( lang, def = false ) => {
			lang = parseInt( lang );
			
			if( isNaN( lang ) )
				lang = 0;
			
			if( typeof aiassist.promts.multi[ lang ] !== 'undefined' && $('#aiassist-generation-prom').is(':visible') ){	
				aiassist.promts['multi_lang'] = lang;
				
				if( def ){
					aiassist.promts.multi		= aiassist.info.promts.multi;
					aiassist.promts.multi_title	= aiassist.info.promts.multi_title;
					aiassist.promts.multi_desc	= aiassist.info.promts.multi_desc;
				}
			
				// if( $('#aiassist-generation-prom').is(':visible') )
					$('#aiassist-generation-prom').val( aiassist.promts.multi[ lang ] )
				
				// if( $('#aiassist-title-prom-multi').is(':visible') )
					$('#aiassist-title-prom-multi').val( aiassist.promts.multi_title[ lang ] )
				
				// if( $('#aiassist-desc-prom-multi').is(':visible') )
					$('#aiassist-desc-prom-multi').val( aiassist.promts.multi_desc[ lang ] )
			}
			
			
			if( typeof aiassist.promts.rewrite[ lang ] !== 'undefined' && $('#aiassist-rewrite-prom').is(':visible') ){
				aiassist.promts['rewrite_lang'] = lang;
				
				if( def )
					aiassist.promts.rewrite = aiassist.info.promts.rewrite;
				
				$('#aiassist-rewrite-prom').val( aiassist.promts.rewrite[ lang ] )
			}
			
			if( typeof aiassist.promts.short[ lang ] !== 'undefined' && $('#aiassist-article-prom').is(':visible') ){
				aiassist.promts['short_lang'] = lang;
				
				if( def )
					aiassist.promts.short = aiassist.info.promts.short;
				
				$('#aiassist-article-prom').val( aiassist.promts.short[ lang ] )
				
				// if( $('#aiassist-title-prom').is(':visible') )
					$('#aiassist-title-prom').val( aiassist.promts.long_title[ lang ] )
				
				// if( $('#aiassist-desc-prom').is(':visible') )
					$('#aiassist-desc-prom').val( aiassist.promts.long_desc[ lang ] )
			}
			
			if( typeof aiassist.promts.long_header[ lang ] !== 'undefined' && $('#aiassist-theme-prom').is(':visible') ){
				aiassist.promts['long_lang'] = lang;
				
				if( def ){
					aiassist.promts.long_header		= aiassist.info.promts.long_header;
					aiassist.promts.long_structure	= aiassist.info.promts.long_structure;
					aiassist.promts.long			= aiassist.info.promts.long;
					aiassist.promts.long_title		= aiassist.info.promts.long_title;
					aiassist.promts.long_desc		= aiassist.info.promts.long_desc;
				}
				
				// if( $('#aiassist-theme-prom').is(':visible') )
					$('#aiassist-theme-prom').val( aiassist.promts.long_header[ lang ] )
				
				// if( $('#aiassist-structure-prom').is(':visible') )
					$('#aiassist-structure-prom').val( aiassist.promts.long_structure[ lang ] )
				
				// if( $('#aiassist-content-prom').is(':visible') )
					$('#aiassist-content-prom').val( aiassist.promts.long[ lang ] )
				
				// if( $('#aiassist-title-prom').is(':visible') )
					$('#aiassist-title-prom').val( aiassist.promts.long_title[ lang ] )
				
				// if( $('#aiassist-desc-prom').is(':visible') )
					$('#aiassist-desc-prom').val( aiassist.promts.long_desc[ lang ] )
			}
			
			await app.request( { val: aiassist.promts, act: 'promts', action: 'saveStep', nonce: aiassist.nonce } );
		},
		
		disabledRewriteUrlArea: function(){
			let e = $(this);
			let area = $('.aiassist-rewrite-item, .aiassist-cats-item, #aiassist-addItemRewrite, .rewrite-block-type');
			
			if( e.val() > 0 )
				area.addClass('disabled');
			else
				area.removeClass('disabled');
		},
		
		postRestore: async function(){
			let e = $(this);
			
			if( ! confirm('Are you sure?') )
				return false;
			
			let status = e.closest('.aiassist-rewrite-queue').find('.aiassist-queue-status');
			e.remove();
			
			status.text('Восстановление...');
			await app.request( { action: 'postRestore', post_id: e.attr('post_id'), revision_id: e.attr('revision_id'), nonce: aiassist.nonce } );
			status.text('Восстановлено');
		},
		
		rewriteInputsChecked: function(){
			app.hideRewriteItems( $('input[name*="rewrite_type"]').is(':checked') );
		},
		
		rewriteAllSiteChecked: () => {
			let check = $('#rewrite_all').is(':checked');
			$('input[name*="rewrite_type"]').prop( { 'checked': check, 'disabled': check } );
			app.hideRewriteItems( check );
		},
		
		tonyMcePopUpHide: () => {
			$('#aiassist-generate-image').hide();
		},
		
		clearContent: async () => {
			if( ! confirm('Вы уверены что хотите очистить все поля от сгенерированного текста?') )
				return false;
		
			app.editor.setContent('');
			app.setCookie('spent', 0);
			app.setCookie('imgSpent', 0);
			
			$('.aiassist-headers').html('');
			$('#step1, #step2, #step3, #step4, #step6').hide();
			$('#aiassist-article-symbols, #images-article-symbols').text('0');
			$('#aiassist-theme, #aiassist-header, #aiassist-structure, #aiassist-title, #aiassist-desc').val('');
			
			await app.request( { action: 'clearContent', nonce: aiassist.nonce } );
		},
		
		tinyMceImageGenerate: async function(){
			let proccess = 0;
			let block = $('#aiassist-generate-image');
			let promt = $('#aiassist-tiny-image-promt').val();
			let model = $('#aiassist-tiny-image-model').val();
			
			if( ! promt.trim().length ){
				$('#aiassist-tiny-image-promt').addClass('aiassist-error');
				return false;
			}
			$('#aiassist-tiny-image-promt').removeClass('aiassist-error');
			
			$('#aiassist-tiny-image-save').hide();
			$('.aiassist-image-tiny-item').addClass('aiassist-images aiassist-proces disabled');
			
			if( promt.match(/[А-Яа-я]/g) && ( model == 'midjourney' || model == 'flux' ) ){
				let task = await app.request( { action: 'translate', token: aiassist.token, content: promt }, aiassist.apiurl );
				
				if( parseInt( task.limit ) < 1 )
					block.find('.aiassist-image-tiny-item').removeClass('aiassist-proces disabled').html('<span class="wpai-warning-limits">Закончились лимиты! Не закрывайте страницу, пополните баланс и нажмите снова "Сгенерировать". <a href="/wp-admin/admin.php?page=wpai-assistant" target="_blank">Пополнить баланс</a></span></span>');
				
				if( task.task_id ){
					let translate = await app.request( { action: 'getTask', token: aiassist.token, id: task.task_id }, aiassist.apiurl );
					
					if( translate.content ){
						promt = translate.content;
						$('#aiassist-tiny-image-promt').val( translate.content );
					}
				} else
					$('#aiassist-tiny-image-promt').val('Error translate promt');
			}
			
			let task = await app.request( { action: 'image_generator', token: aiassist.token, model: model, header: promt, format: 'jpg' }, aiassist.apiurl );
			
			if( parseInt( task.limit ) < 1 )
				block.find('.aiassist-images').removeClass('aiassist-proces disabled').html('<span class="wpai-warning-limits">Закончились лимиты! Не закрывайте страницу, пополните баланс и нажмите снова "Сгенерировать". <a href="/wp-admin/admin.php?page=wpai-assistant" target="_blank">Пополнить баланс</a></span></span>');
			
			if( task.task_id ){
				while( true ){
					let data = await app.request( { action: 'getTask', id: task.task_id, token: aiassist.token }, aiassist.apiurl );
					
					if( data.process ){
						if( data.process.progress > proccess ){
						
							if( ! block.find('.aiassist-progressImageUrl').length )
								block.find('.aiassist-images').html('<img src="'+ data.process.progressImageUrl +'" class="aiassist-progressImageUrl">');
							
							block.find('.aiassist-progressImageUrl').attr('src', data.process.progressImageUrl);
						}
						proccess = data.process.progress;
					}
					
					if( data.nsfw )
						return block.find('.aiassist-images').removeClass('aiassist-proces disabled').html('<span class="wpai-warning-limits">Промт попал под цензуру, одно или несколько слов не дают сгенерировать изображение. Попробуйте изменить промт!</span>');
					
					if( data.images ){
						block.find('.aiassist-images').html('');
					
						for( let k in data.images )
							block.find('.aiassist-images').removeClass('aiassist-proces disabled').append('<img src="'+ aiassist.apiurl +'?action=getImage&image='+ data.images[ k ] +'" class="ai-image">');
						
						block.find('.ai-image:first').click();
						break;
					}
					await app.sleep(5);
				}
			}
		},
		
		tinyMceTranslate: async function(){
			let e = $(this);
			let block = e.closest('#aiassist-generate-image');
			let title = block.find('#aiassist-tiny-image-promt').val();
			
			let task = await app.request( { action: 'translate', token: aiassist.token, content: title }, aiassist.apiurl );
			
			if( parseInt( task.limit ) < 1 )
				block.find('.aiassist-image-tiny-item').removeClass('aiassist-proces disabled').html('<span class="wpai-warning-limits">Закончились лимиты! Не закрывайте страницу, пополните баланс и нажмите снова "Сгенерировать". <a href="/wp-admin/admin.php?page=wpai-assistant" target="_blank">Пополнить баланс</a></span></span>');		
			
			if( task.task_id ){
				let translate = await app.request( { action: 'getTask', token: aiassist.token, id: task.task_id }, aiassist.apiurl );
				
				if( translate.content ){
					block.find('#aiassist-tiny-image-promt').val( translate.content );
					return;
				}
			}
			block.find('#aiassist-tiny-image-promt').val('Error translate promt');
		},
		
		tinyMceImageSave: async function(){
			let e = $(this);
			let block = e.closest('.aiassist-image-tiny');
			let title = block.find('#aiassist-tiny-image-promt').val();
			
			if( ! block.find('.ai-image.active').length )
				return false;
			
			block.find('#aiassist-tiny-image-save').hide();
			block.find('.aiassist-images').addClass('aiassist-proces disabled');
			
			if( block.find('.ai-image.active').length ){
				let images = [];
				
				block.find('.ai-image.active').each(function(){
					images.push( $(this).attr('src') );
				})
				
				let str = '';
				let post_id = null;
				
				if( $('#post_ID').length )
					post_id = $('#post_ID').val();
				
				if( ! post_id )
					post_id = wp.data.select('core/editor').getCurrentPostId();
				
				for( let k in images ){
					let load = await app.request( { action: 'loadImage', post_id: post_id, 'image[src]': images[ k ], 'image[title]': title, nonce: aiassist.nonce } );
					str += '<img class="alignnone size-full wp-image-'+ load.id +'" src="'+ load.url +'" title="'+ title +'" alt="'+( title +' фото' )+'" />';
				}

				tinyMCE.activeEditor.insertContent( str );
				$('#aiassist-generate-image').hide();
				block.find('.aiassist-images').removeClass('aiassist-proces disabled').html('');
			}
		},
		
		selectImageInBlock: function(){
			let e = $(this);
			let block = e.closest('.aiassist-image-block, #aiassist-generate-image');
			
			if( e.hasClass('active') )
				e.removeClass('active');
			else
				e.addClass('active');
				
			if( block.find('.ai-image.active').length )
				$('#aiassist-tiny-image-save, .aiassist-image-save').show();
			else
				$('#aiassist-tiny-image-save, .aiassist-image-save').hide();
		},
		
		cron: async () => {
			let args = await app.request( { action: 'aiassist_cron', nonce: aiassist.nonce } );
			let limit = await app.request( { action: 'getLimit', token: aiassist.token }, aiassist.apiurl );
			
			if( ! isNaN( parseInt( limit.limit ) ) && $('#wpai-symbols').length ){
				$('#wpai-symbols').text( app.number_format( limit.limit ) );
				
				if( limit.limit < 1 )
					$('#aiassist-generation-status, #aiassist-rewrite-status').html('<span class="wpai-warning-limits">Лимиты заканчились, для продолжения генерации пополните баланс!</span>');
			}
			
			if( $('#aiassist-settings').length && ! isNaN( parseInt( args.articles.publish ) ) ){
				
				if( $('#aiassist-count-publish').length )
					$('#aiassist-count-publish').text( args.articles.publish );
				
				if( args.articles.publish >= args.articles.count ){
					$('#stop-articles-generations').click();
					$('#aiassist-generation-status').text('Процесс генерации статей завершен.');
				}
				
				if( ! isNaN( parseInt( args.articles.limit ) ) ){
					$('#wpai-symbols').text( app.number_format( args.articles.limit ) );
					
					if( args.articles.limit < 1 )
						$('#aiassist-generation-status').html('<span class="wpai-warning-limits">Лимиты заканчились, для продолжения генерации пополните баланс!</span>');
				}
					
				if( args.articles.articles ){
					for( let k in args.articles.articles ){
					
						if( ! args.articles.articles[ k ].post_id )
							continue;
						
						$('.aiassist-article-queue').filter(function(){
							let e = $(this);
							
							if( e.find('.aiassist-queue-keyword').text().trim() == args.articles.articles[ k ].keywords.trim() && e.find('.aiassist-queue-keyword').length ){
								e.find('.aiassist-queue-status').text('Сгенерирована');
								e.find('.aiassist-queue-keyword').wrap('<a href="/wp-admin/post.php?post='+ args.articles.articles[ k ].post_id +'&action=edit" target="_blank" ></a>');
								e.find('.aiassist-queue-keyword').removeClass('aiassist-queue-keyword');
								e.removeClass('aiassist-queue').find('.aiassist-article-item-close').remove();
								e.next().find('.aiassist-queue-status').text('Идет генерация');
							}
						})
						
					}
				}
			}
			
			if( $('#aiassist-settings').length && args.rewrites.posts ){

				if( ! isNaN( parseInt( args.rewrites.limit ) ) ){
					$('#wpai-symbols').text( app.number_format( args.rewrites.limit ) );
					
					if( args.rewrites.limit < 1 )
						$('#aiassist-generation-status').html('<span class="wpai-warning-limits">Лимиты заканчились, для продолжения генерации (рерайта) пополните баланс!</span>');
				}

				if( args.rewrites.posts.length ){
					
					if( $('#aiassist-rewrite-count-publish').length )
						$('#aiassist-rewrite-count-publish').text( args.rewrites.counter );
					
					if( args.rewrites.counter >= args.rewrites.posts.length ){
						$('#stop-rewrite-generations').click();
						$('#aiassist-rewrite-status').text('Процесс рерайта статей завершен.');
					}
				
					for( let k in args.rewrites.posts ){
					
						if( ! args.rewrites.posts[ k ].post_id )
							continue;
						
						$('.aiassist-rewrite-queue').filter(function(){
							let e = $(this);
							let check = false;
							
							if( args.rewrites.posts[ k ].url != undefined )
								check = e.find('.aiassist-queue-rewrite').text().trim() == args.rewrites.posts[ k ].url.trim();
							
							if( args.rewrites.posts[ k ].title != undefined )
								check = e.find('.aiassist-queue-rewrite').text().trim() == args.rewrites.posts[ k ].title.trim();
							
							if( check && e.find('.aiassist-queue-rewrite').length ){
								e.find('.aiassist-queue-status').text('Сгенерирована');
								e.find('.aiassist-queue-rewrite').wrap('<a href="/wp-admin/post.php?post='+ args.rewrites.posts[ k ].post_id +'&action=edit" target="_blank" ></a>');
								e.find('.aiassist-queue-rewrite').removeClass('aiassist-queue-rewrite');
								e.next().find('.aiassist-queue-status').text('Идет генерация');
							}
						})
						
					}
				}
			
			}
			
			setTimeout( app.cron, 60000 );
		},
		
		number_format: ( number ) => {
			if( isNaN( parseInt( number ) ) )
				return '';
			
			return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
		},
		
		setTextModel: () => {
			app.model = $('#aiassist-change-text-model').val();
			app.setCookie('text-model', app.model);
		},
		
		setImageModel: function(){
			app.setCookie('image-model', $(this).val() );
		},
		
		setAutoImageModel: function(){
			app.setCookie('image-model-auto', $(this).val() );
		},
		
		autoGenOptions: () => {
			clearTimeout( app.t );
			
			let args = {
				nonce: aiassist.nonce,
				action: 'autoGenOptions',
				draft: $('#aiassist-auto-draft').prop('checked') ? 1 : 0,
				thumb: $('#aiassist-auto-thumb').prop('checked') ? 1 : 0,
				images: $('#aiassist-auto-images').prop('checked') ? 1 : 0,
				textModel: $('#aiassist-change-text-model').val(),
				imageModel: $('#aiassist-image-model').val(),
				publishInDay: $('#publish-article-in-day').val(),
			};	
			
			app.t = setTimeout( async () => {
				await app.request( args ); 
			}, 500);
		},
		
		clearArticlesGeneration: async () => {
			if( ! confirm('Вы уверены?') )
				return false;
			
			$('.aiassist-article-queue').remove();
			$('#stop-articles-generations').attr('disabled', true);
			$('#start-articles-generations').attr('disabled', false);
			$('#aiassist-count-publish').text('0');
			$('#aiassist-generation-status').text('');
			await app.request( { action: 'clearArticlesGen', nonce: aiassist.nonce } );
		},
		
		stopArticlesGeneration: async () => {
			$('#start-articles-generations').attr('disabled', false);
			$('#stop-articles-generations').attr('disabled', true);
			$('#aiassist-generation-status').text('Процесс генерации статей приостановлен.');
			await app.request( { action: 'stopArticlesGen', nonce: aiassist.nonce } ); 
		},
		
		startArticlesGeneration: async () => {
			$('#start-articles-generations').attr('disabled', true);
			$('#stop-articles-generations').attr('disabled', false);
			$('#aiassist-generation-status').text('Идет процесс генерации статей, информация обновляется автоматически. Если этого не происходит, обновите страницу браузера, чтобы увидеть актуальный список сгенерированных статей.');
			
			let items = $('.aiassist-article-item');
			
			if( ! items.find('.aiassist-keywords-item').val().trim().length ){
				await app.request( { action: 'startArticlesGen', nonce: aiassist.nonce } ); 
				return;
			}
			
			if( items.length ){
				let articles = {};
				
				let c = 0;
			
				items.each(function(){
					let e = $(this);
					let id = e.find('.cats-item').val();
					
					let item = e.find('.aiassist-keywords-item').val().trim();
					
					if( item == '' )	
						return;
					
					if( articles[ id ] == undefined )
						articles[ id ] = '';
					
					articles[ id ] += item +"\n";
					
					let keywords = item.split("\n");
					
					if( keywords.length ){
						for( let i in keywords ){ c++;
							$('.aiassist-articles-queue').append('<div class="aiassist-article-queue"><span class="aiassist-queue-keyword">'+ keywords[ i ] +'</span> <span class="aiassist-queue-status">'+( c==1 ? 'Идет генерация' : 'В очереди' )+'</span></div>');
						}
					}
				})
				
				let artPromt = $('#aiassist-generation-prom').val(),
					titlePromt = $('#aiassist-title-prom').val(),
					descPromt = $('#aiassist-desc-prom').val();
					imageModel = $('#aiassist-image-model').val();
					textModel = $('#aiassist-change-text-model').val();
				
				await app.request( { articles: articles, artPromt: artPromt, titlePromt: titlePromt, textModel: textModel, imageModel: imageModel, descPromt: descPromt, action: 'initArticlesGen', nonce: aiassist.nonce } ); 
				
				$('.aiassist-article-item:not(:first)').remove();
				app.addItemArticle();
				$('.aiassist-article-item:first, .aiassist-article-item-close').remove();
				$('#aiassist-generation-progress').html('Сгенерировано <span id="aiassist-count-publish">0</span> статей из '+ c );
			}
		},
		
		addItemArticle: () => {	
			let item = $('.aiassist-article-item:first').clone();
			$( item ).find('.aiassist-keywords-item').val('');
			$( item ).prepend('<div class="aiassist-article-item-close" />');
			$('.aiassist-article-items').append( item );
		},
		
		queueArticleClose: function(){
			if( ! confirm('Are you sure?') )
				return;

			let e = $(this);
			
			let id = e.attr('data-key');
			e.closest('.aiassist-article-queue').remove();
			app.request( { nonce: aiassist.nonce, id: id, action: 'removeQueueArticle' } ); 	
		},
		
		rewriteOptions: () => {
			clearTimeout( app.t );
			
			let args = {
				nonce: aiassist.nonce,
				action: 'rewriteOptions',
				split: $('#aiassist-rewrite-split').val(),
				thumb: $('#aiassist-rewrite-thumb').prop('checked') ? 1 : 0,
				draft: $('#aiassist-rewrite-draft').prop('checked') ? 1 : 0,
				images: $('#aiassist-rewrite-images').prop('checked') ? 1 : 0,
				textModel: $('#aiassist-rewrite-text-model').val(),
				imageModel: $('#aiassist-rewrite-image-model').val(),
			};	
			
			app.t = setTimeout( async () => {
				await app.request( args ); 
			}, 250);
		},
		
		startRewriteGenerations: async () => {
			$('#start-rewrite-generations').attr('disabled', true);
			$('#stop-rewrite-generations').attr('disabled', false);
			$('#aiassist-rewrite-status').text('Идет процесс рерайта статей, информация обновляется автоматически. Если этого не происходит, обновите страницу браузера, чтобы увидеть актуальный список статей по которым выполнен рерайт.');
			
			let items = $('.aiassist-rewrite-item-block');
						
			// if( ! items.find('.aiassist-rewrite-item').val().trim().length ){
				// await app.request( { action: 'startRewrite', nonce: aiassist.nonce } ); 
				// return;
			// }
			
			let args = {
				cats: [],
				types: [],
				links: {},
				split: $('#aiassist-rewrite-split').val(),
				promt: $('#aiassist-rewrite-prom').val(),
				textModel: $('#aiassist-change-text-model').val(),
				imageModel: $('#aiassist-image-model').val(),
				action: 'initRewrite', 
				nonce: aiassist.nonce
			};
			
			if( $('input[name*="rewrite_type"]:checked').length ){
				$('input[name*="rewrite_type"]:checked').each(function(){
					args.types.push( $(this).val() )
				})
			}
			
			if( $('.cat-rewrite').length ){
				$('.cat-rewrite').each(function(){
					args.cats.push( $(this).val() )
				})
			}
			
			if( items.length ){
				items.each(function(){
					let e = $(this);
					let id = e.find('.cats-item').val();
					let item = e.find('.aiassist-rewrite-item').val().trim();
					
					if( item == '' )	
						return;
					
					if( args.links[ id ] == undefined )
						args.links[ id ] = [];
					
					args.links[ id ] = item.split("\n");
				})
				
				let data = await app.request( args ); 
				
				let c = 0;
				let p = 0;
				
				if( data.posts.length ){
					$('.aiassist-rewrites-queue').html('');
					
					for( let i in data.posts ){ c++;
						let status = 'В очереди';
						let title = data.posts[ i ].title;
						
						if( data.posts[ i ].url )
							title = data.posts[ i ].url;
					
						if( data.posts[ i ].post_id )
							p++;
					
						if( data.posts[ i ].post_id )
							status = 'Сгенерирована';
							
						if( data.posts[ i ].task_id && ! data.posts[ i ].post_id )
							status = 'Идет генерация';
					
						$('.aiassist-rewrites-queue').append('<div class="aiassist-rewrite-queue"><span class="aiassist-queue-rewrite">'+ title +'</span> <span class="aiassist-queue-status">'+ status +'</span></div>');
					}
				}
				
				$('.aiassist-rewrite-item-block:not(:first)').remove();
				app.addItemRewrite();
				$('.aiassist-rewrite-item-block:first, .aiassist-rewrite-item-close').remove();
				$('#aiassist-rewrite-progress').html('Сгенерировано <span id="aiassist-rewrite-count-publish">'+ p +'</span> статей из '+ c );
			}
		},
		
		stopRewriteGeneration: async () => {
			$('#start-rewrite-generations').attr('disabled', false);
			$('#stop-rewrite-generations').attr('disabled', true);
			$('#aiassist-rewrite-status').text('Процесс генерации статей приостановлен.');
			await app.request( { action: 'stopRewrite', nonce: aiassist.nonce } ); 
		},
		
		clearRewritesGeneration: async () => {
			if( ! confirm('Вы уверены?') )
				return false;
			
			$('.aiassist-rewrite-queue').remove();
			$('#stop-rewrite-generations').attr('disabled', true);
			$('#start-rewrite-generations').attr('disabled', false);
			$('#aiassist-rewrite-count-publish').text('0');
			$('#aiassist-rewrite-status').text('');
			await app.request( { action: 'clearRewrite', nonce: aiassist.nonce } );
		},
		
		addItemRewrite: () => {	
			let item = $('.aiassist-rewrite-item-block:first').clone();
			$( item ).find('.aiassist-rewrite-item').val('');
			$( item ).prepend('<div class="aiassist-rewrite-item-close" />');
			$('.aiassist-rewrite-items').append( item );
		},
		
		hideRewriteItems: ( check ) => {
			if( check )
				$('.aiassist-rewrite-item-block, .aiassist-item-repeater').addClass('disabled');
			else
				$('.aiassist-rewrite-item-block, .aiassist-item-repeater').removeClass('disabled');
				
			$('.aiassist-rewrite-item-block:not(:first)').remove();
			app.addItemRewrite();
			$('.aiassist-rewrite-item-block:first, .aiassist-rewrite-item-close').remove();
		},
		
		rewriteItemClose: function(){
			$(this).closest('.aiassist-rewrite-item-block').remove();
		},
		
		selectImage: function(){
			let e = $(this);
			let block = e.closest('.aiassist-header-item');
			
			if( block.hasClass('aiassist-main-header') )
				block.find('.aiassist-image').removeClass('active');
			
			if( ! e.hasClass('active') ){
				e.addClass('active show').delay(1000).queue( next => {
					e.removeClass('show');
					next();
				});
			} else
				e.removeClass('active');
			
		},
		
		imageGenerator: async function(){
			let e = $(this);
			e.closest('.aiassist-header-item').find('label input[type="checkbox"]').prop('checked', true);
			app.generateImage( e );
		},
		
		imagesGenerator: async () => {
			if( $('.aiassist-header-item input:checked').length ){
			
				$('#aiassist-images-generator-start').hide();
				$('#aiassist-images-generator-start').attr('disabled', true);
				
				$('.aiassist-header-item input:checked').each(function(){
					app.generateImage( $(this) );
				})
			
			}
			$('#aiassist-images-generator-start').attr('disabled', false);
		},
		
		translatePromtsToImages: async () => {
			if( $('.aiassist-headers .aiassist-header-item').length ){
				
				if( $('#aiassist-change-image-model').val() == 'dalle' ){
					
					if( $('.aiassist-header-item').length ){
						let h1 = $('.aiassist-main-header label input').val();
						$('.aiassist-main-header .aiassist-translate-promt-image input').val( h1 );
						
						$('.aiassist-header-item:not(.aiassist-main-header)').each(function(){
							let e = $(this);
							let header = h1 +' '+ e.find('label input').val();
							
							e.find('.aiassist-translate-promt-image input').val( header );
						})
					}
					app.loader();
					return;
				}
				
				if( $('.aiassist-lang-promts:visible:first option:selected').val() != 1 )
					app.loader( true, 'Перевод промтов для изображений' );
				
				const items = $('.aiassist-headers .aiassist-header-item');

				let promts = await Promise.all(items.map(async ( k, e ) => {
					let en = $( e ).find('.aiassist-translate-promt-image input');
						
					if( en.attr('data-en') != undefined && en.attr('data-en').length ){
						en.val( en.attr('data-en') );
						return;
					}
				
					let header = $( e ).find('label input[type="checkbox"]').val();
					let text = header;
					
					if( ! $( e ).find('#aiassist-main').length )
						text = $('#aiassist-main').val() +' '+ header;
					
					if( $('.aiassist-lang-promts:visible:first option:selected').val() == 1 ){
						$( e ).find('.aiassist-translate-promt-image input').val( text ).attr('data-en', text);
						return;
					}
				
					let translate = await app.addTask( { action: 'translate', content: text } );
					
					$( e ).find('.aiassist-translate-promt-image input').val( translate.content ).attr('data-en', translate.content);
				
					return { act: header, val: translate.content };
				}));

				if( promts[0] )
					await app.request( { promts: promts, action: 'saveTranslateImagesPromts', nonce: aiassist.nonce } );
				
			}
			app.loader();
		},
		
		generateImage: async ( e ) => {
			
			let block = e.closest('.aiassist-header-item');
			let model = $('#aiassist-change-image-model').val();
			
			let imgBlock = $('<div class="aiassist-images '+ model +' aiassist-proces" />');
			block.append( imgBlock );
			let promt = block.find('.aiassist-translate-promt-image input').val();
			
			if( ! promt ){
				let header = $('.aiassist-main-header label input[type="checkbox"]').val();
				
				if( ! block.find('#aiassist-main').length )
					header = header +' '+ block.find('label input[type="checkbox"]').val();
				
				let translate = await app.addTask( { action: 'translate', content: header } );
				promt = translate.content;
				
				block.find('.aiassist-translate-promt-image input').val( promt );
				await app.request( { val: promt, act: header, action: 'saveStep', nonce: aiassist.nonce } );
			}
			
			let task = await app.request( { token: aiassist.token, gptkey: aiassist.gptkey, model: model, action: 'image_generator', header: promt, format: 'jpg' }, aiassist.apiurl );
			
			if( task.limit < 1 )
				block.find('.aiassist-proces').removeClass('aiassist-proces').html('<span class="wpai-warning-limits">Закончились лимиты! Не закрывайте страницу, пополните баланс и нажмите снова "Сгенерировать". <a href="/wp-admin/admin.php?page=wpai-assistant" target="_blank">Пополнить баланс</a></span></span>');

			
			if( task.task_id ){
				let proccess = 0;
				
				while( true ){
					let data = await app.request( { token: aiassist.token, gptkey: aiassist.gptkey, action: 'getTask', id: task.task_id }, aiassist.apiurl );
					
					if( data.limit && $('#tokens-left').length )
						$('#tokens-left').text( app.number_format( data.limit ) );
					
					if( data.limit < 1 ){
						block.find('.aiassist-proces').removeClass('aiassist-proces').html('<span class="wpai-warning-limits">Закончились лимиты! Не закрывайте страницу, пополните баланс и нажмите снова "Сгенерировать". <a href="/wp-admin/admin.php?page=wpai-assistant" target="_blank">Пополнить баланс</a></span></span>');
						break;
					}
					
					if( data.imgs_limit ){
						let imgSpent = parseInt( $('#images-article-symbols').text() );
						
						if( isNaN( imgSpent ) )
							imgSpent = 0;
						
						imgSpent += data.imgs_limit;
						
						$('#images-article-symbols').text( imgSpent );
						app.setCookie( 'imgSpent', imgSpent );
					}
					
					if( data.process ){
						if( data.process.progress > proccess ){
							if( ! imgBlock.find('.aiassist-progressImageUrl').length )
								imgBlock.append('<img src="'+ data.process.progressImageUrl +'" class="aiassist-progressImageUrl" />');
							
							imgBlock.find('.aiassist-progressImageUrl').attr('src', data.process.progressImageUrl);
						}
						proccess = data.process.progress;
					}
					
					if( data.nsfw )
						return block.find('.aiassist-images').removeClass('aiassist-proces disabled').html('<span class="wpai-warning-limits">Промт попал под цензуру, одно или несколько слов не дают сгенерировать изображение. Попробуйте изменить промт!</span>');
					
					if( data.images ){
						imgBlock.find('.aiassist-progressImageUrl').remove();
						block.find('.aiassist-image.active').removeClass('active');
						
						let active = true;
						for( let k in data.images ){
							if( active )		
								block.find('input[type="checkbox"]').attr('data-src', aiassist.apiurl +'?action=getImage&image='+ data.images[ k ]);
						
							imgBlock.append('<img src="'+ aiassist.apiurl +'?action=getImage&image='+ data.images[ k ] +'" class="aiassist-image '+( active ? 'active' : '' )+'" />');
							active = false;
						}
							
						imgBlock.removeClass('aiassist-proces');
						break;
					}
					await app.sleep( 10 );
				}
				
			}
			
		},
		
		checkAllHeaders: function(){
			let e = $(this);
			
			if( e.is(':checked') )
				$('.aiassist-header-item input').prop('checked', true);
			else
				$('.aiassist-header-item input').prop('checked', false);
		},
		
		statStep: async function( event ){
			let e = $(this);
			date = e.val().split('|');
			$('input[name="dateStart"]').val( date[0] );
			$('input[name="dateEnd"]').val( date[1] );
		},
		
		outSummFocus: async function( e ){
			$(this).removeAttr('placeholder');
		},
		
		outSummFocusOut: async function( e ){
			$(this).attr('placeholder', '5000 руб');
		},
		
		buyForm: async function( e ){
			e.preventDefault();
		},
		
		buy: async function (){
			$(this).attr('disabled', true);
			
			let summ = $('#out_summ').val();
			let crypto = $('.pay-method.active .cryptocloud').length;
			
			if( crypto )
				summ = $('#out_summ_usdt').val();
			
			let buy = await app.request( { 'out_summ': summ, action: 'aiassist_buy', promocode: $('.aiassist-promocode input[name="promocode"]').val(), type: $(this).data('type'), crypto: crypto, nonce: aiassist.nonce } );
			
			if( buy.error )
				alert( buy.error );
			
			if( buy.pay_url ){
				$('body').append('<a id="aiassistbuy" href="'+ buy.pay_url +'" target="_blank"></a>');

				setTimeout(() => {
					aiassistbuy.click();
					aiassistbuy.remove();
				}, 1)
			}
			$(this).attr('disabled', false);
		},
		
		getStat: async function( event ){
			event.preventDefault();
		
			let e = $(this);
			let args = app.getFormData( e );
			let stats = await app.request( Object.assign( args, { action: 'getStat', nonce: aiassist.nonce } ) );
			
			if( $('#tokens-stats').length )
				$('#area-chat').html('');
				$('#tokens-stats').remove();
			
			if( ! Object.keys( stats ).length ){
				e.after('<div id="tokens-stats"><h3>Данных не найдено!</h3></div>');
				return;
			}
			
			e.after('<div id="tokens-stats"><h3>Tokens: '+ stats.total +'</h3></div>');
			
			google.charts.load('current', {'packages':['corechart']});			
			google.charts.setOnLoadCallback( () => {
				let day = Object.keys( stats ).length < 3;
				args = [ [ 'Date', 'Symbols'] ];
				
				if( day )
					args = [ [ 'Host', 'Symbols'] ];
				
				for( k in stats ){
					if( k == 'total' )
						continue;
				
					if( day ){
						for( let i in stats[ k ] ){
							if( i == 'total' )
								continue;
							
							args.push( [ i, parseInt( stats[ k ][ i ] ) ] );
							
							$('#tokens-stats').append('<div>'+ i +': '+ stats[ k ][ i ] +'</div>');
						}
					} else {
						args.push( [ k, parseInt( stats[ k ].total ) ] );
						$('#tokens-stats').append('<div>'+ k +': '+ stats[ k ].total +'</div>');
					}
				}
				
				data = google.visualization.arrayToDataTable( args );
				
				new google.visualization.LineChart( document.getElementById('area-chat') ).draw(
					data, 
					{
					  title: '',
					  hAxis: {title: ' ',  titleTextStyle: {color: '#333'}},
					  vAxis: {minValue: 0}
					});
			});
		
			return false;
		},
		
		sign: async function( event ){
			event.preventDefault();
			
			let e = $(this);
			let args = app.getFormData( e );
			let act = e.attr('data-action');
			
			let auth = await app.request( Object.assign( args, { act: act, action: 'aiassist_sign', nonce: aiassist.nonce } ) );
			
			if( auth.message )
				$('#wpai-errors-messages').html( auth.message );
			
			if( auth.auth ){
				$('#wpai-errors-messages').addClass('success').text('Регистрация прошла успешно, вам отправлено письмо с ключом.');
				document.cookie = 'auth=true';
				
				setTimeout( () => {
					location.reload();
				}, 2000 );
			}
			return false;
		},
		
		wsTabs: function(){
			let e = $(this);
			
			$('.aiassist-tab').removeClass('active');
			$('.aiassist-tab-data').removeClass('active');
			$('.aiassist-tab-data[data-tab="'+ e.data('tab') +'"]').addClass('active');
			app.setCookie('activeTab', e.data('tab'));
			
			e.addClass('active');
		},
		
		tabs: function(){
			let e = $(this);
			
			$('#wpai-errors-messages').text('');
			$('#aiassist-sign').attr('data-action', e.data('action'))
			$('input[name="license"]').attr('required', e.data('action') == 'signUp');
			$('.wpai-tab').removeClass('active');
			e.addClass('active');
		},
		
		excludeContext: () => {
			app.setCookie('excludeContext', $('#exclude_context').is(':checked') ? 1 : 0 );
		},
		
		stepStop: async () => {
			app.loader();
		},
		
		saveKey: async () => {
			await app.request( { key: $('#aiassist-gpt-key').val(), action: 'saveKey', nonce: aiassist.nonce } );
		},
		
		saveContent: async () => {
			app.loader( true, 'Сохранение контента' );
			
			let post_id = null;
			
			if( $('#post_ID').length )
				post_id = $('#post_ID').val();
			
			if( ! post_id )
				post_id = wp.data.select('core/editor').getCurrentPostId();

			app.editor = tinymce.get('AIASSIST');
			let header = $('#aiassist-header').val();
			let content = app.editor.getContent();
			let title = $('#aiassist-title').val();
			let desc = $('#aiassist-desc').val();
			
			let imgs = [];
			let thumbnail = '';
			
			if( $('.aiassist-main-header .aiassist-image.active').length )
				thumbnail = $('.aiassist-main-header .aiassist-image.active').attr('src');
			
			if( $('.aiassist-images-generator input[type="checkbox"]:not(#aiassist-main, #aiassist-images-generator-all-headers):checked').length ){
				$('.aiassist-images-generator input[type="checkbox"]:not(#aiassist-main, #aiassist-images-generator-all-headers):checked').each(function(){
					let e = $(this);
					let block = e.closest('.aiassist-header-item');
					
					if( block.find('.aiassist-image.active').length ){
						block.find('.aiassist-image.active').each(function(){
							imgs.push( { title: e.val().trim(), src: $(this).attr('src') } )
						})
					}
				})
				
				for( let k in imgs ){
					app.loader( true, 'Загрузка изображения - '+ imgs[ k ].title );
					let load = await app.request( { post_id: post_id, image: imgs[ k ], action: 'loadImage', nonce: aiassist.nonce } );
					
					if( load.image )
						content = content.replace(new RegExp(imgs[ k ].title +'[^<]*(<\/h[1-6]>)', 'gi'), imgs[ k ].title +'$1'+ load.image);	
				}
			}
			
			app.loader( true, 'Завершение...');
			let data = await app.request( { post_id: post_id, header: header, content: content, title: title, desc: desc, thumbnail: thumbnail, action: 'saveContent', nonce: aiassist.nonce } );
			
			if( data.id ){
				app.setCookie('spent', 0 );
				app.setCookie('imgSpent', 0 );
				window.location.href = '/wp-admin/post.php?post='+ parseInt( data.id ) +'&action=edit';
			}
				
			app.loader();
		},
		
		generateHeader: async () => {
			app.loader( true, 'Генерация заголовка' );
			
			let theme = $('#aiassist-theme').val();
			let prom = $('#aiassist-theme-prom').val();
			
			let data = await app.addTask( { action: 'generateHeader', theme: theme, prom: prom, lang_id: parseInt( $('.aiassist-lang-promts:visible:first').val() ) } );

			$('#step1, #step5').show();
			
			if( data.content ){
				$('#aiassist-header').val( data.content );
				await app.request( { val: data.content, act: 'header', action: 'saveStep', nonce: aiassist.nonce } );
			} else
				app.errorLog('End limits!');
		
			app.loader();
		},
		
		generateStructure: async () => {
			app.loader( true, 'Генерация структуры' );
			
			let header = $('#aiassist-header').val();
			let prom = $('#aiassist-structure-prom').val();
			
			let data = await app.addTask( { action: 'generateStructure', header: header, prom: prom, lang_id: parseInt( $('.aiassist-lang-promts:visible:first').val() ) } );
		
			$('#step2').show();
			
			if( data.content ){
				$('#aiassist-structure').val( data.content ).removeClass('disabled');
				await app.request( { val: data.content, act: 'structure', action: 'saveStep', nonce: aiassist.nonce } );
			} else
				app.errorLog('End limits!');
		
			app.loader();
		},
		
		standartGenerateContent: async () => {
			let h1 = $('#aiassist-theme-standart').val();
			
			if( ! h1 ){
				$('#aiassist-theme-standart').addClass('aiassist-error');
				return false;
			}
			$('#aiassist-theme-standart').removeClass('aiassist-error');
		
			app.loader( true, 'Генерация текста' );
			
			let promt = $('#aiassist-article-prom').val().replace('{key}', h1);
			let data = await app.addTask( { action: 'generateStandartContent', prom: promt } );
			
			if( data.content ){
				$('#step3').show();
				$('.aiassist-headers .aiassist-header-item').remove();
				
				$('.aiassist-headers').append('<div class="aiassist-header-item aiassist-main-header"><div class="left">Изображение записи</div><label><input type="checkbox" id="aiassist-main" value="'+( h1 )+'" /><span>'+( h1 )+'</span></label><div class="aiassist-translate-promt-image">Promt: <input /> <div class="image-generate-item">Сгенерировать</div></div></div>');	
				
				if( headers = data.content.match(/<h[2-6][^>]*>([^<]+)<\/h[2-6]>/gi) ){
					for(let i in headers ){
						headers[ i ] = headers[ i ].replace(/<\/?h[2-6][^>]*>/gi, '');
						$('.aiassist-headers').append('<div class="aiassist-header-item"><label><input type="checkbox" value="'+( headers[ i ] )+'" /><span>'+( headers[ i ] )+'</span></label><div class="aiassist-translate-promt-image">Promt: <input /> <div class="image-generate-item">Сгенерировать</div></div></div>');
					}
				}
				$('#step6').show();
				
				app.editor.setContent( data.content );
				await app.request( { val: data.content, act: 'content', action: 'saveStep', nonce: aiassist.nonce } );
			} else {
				app.loader();
				app.errorLog('End limits!');
			}
			
			$('#step5').show();
			$('#aiassist-content').removeClass('disabled');
			app.translatePromtsToImages();
		},
		
		generateContent: async () => {
			app.loader( true, 'Генерация вступления' );
			
			let header = $('#aiassist-header').val();
			let structure = $('#aiassist-structure').val();
			
			if( $('.aiassist-headers .aiassist-header-item').length )
				$('.aiassist-headers .aiassist-header-item').remove();
			
			await app.request( { val: structure, act: 'structure', action: 'saveStep', nonce: aiassist.nonce } );
			
			structure = structure.split("\n");
			structure = structure.filter( e => e );

			if( structure.length ){
				$('#step3').show();
				
				let content = '';
				let prom = $('#aiassist-content-prom').val();
				$('.aiassist-headers').append('<div class="aiassist-header-item aiassist-main-header"><div class="left">Изображение записи</div><label><input type="checkbox" id="aiassist-main" value="'+( header )+'" /><span>'+( header )+'</span></label><div class="aiassist-translate-promt-image">Promt: <input value="" /> <div class="image-generate-item">Сгенерировать</div></div></div>');
				
				let data = await app.addTask( { action: 'generatePreContent', header: header, lang_id: parseInt( $('.aiassist-lang-promts:visible:first').val() ) } );
				
				if( data.content )
					app.editor.setContent( data.content );
				
				for( let k in structure ){
					subHeader = structure[ k ].replace(/<[\/]?[^>]*>/g, '');
					
					if( $('.aiassist-headers').length )
						$('.aiassist-headers').append('<div class="aiassist-header-item"><label><input type="checkbox" value="'+( subHeader )+'" /><span>'+( subHeader )+'</span></label><div class="aiassist-translate-promt-image">Promt: <input /> <div class="image-generate-item">Сгенерировать</div></div></div>');
					
					$('#step6').show();
					$('#aiassist-loader-info').text('Генерация пункта: '+ subHeader);
					
					if( ! $('#aiassist-progress-generator').length )
						$('#aiassist-loader').after('<div id="aiassist-progress-generator"></div>');
					$('#aiassist-progress-generator').text( Math.round( ( parseInt( k ) / structure.length ) * 100 ) +'%');
					
					let data = await app.addTask( { action: ( $('#exclude_context').is(':checked') ? 'generateContent' : 'generateContentItem' ), lang_id: parseInt( $('.aiassist-lang-promts:visible:first').val() ), header: header, item: subHeader, prom: prom, structure: structure, context: ( k > 0 ? app.editor.getContent() : null ) } );
					
					if( data.content ){
						let headItem = '<h2>'+ subHeader +'</h2>';
						
						if(  structure[ k ].match(/^<h/g) )
							headItem = structure[ k ];
						
						app.editor.setContent( app.editor.getContent() + headItem + data.content );
					} else
						app.errorLog('End limits!');
				}
				
				await app.request( { val: app.editor.getContent(), act: 'content', action: 'saveStep', nonce: aiassist.nonce } );
				$('#aiassist-content').removeClass('disabled');
			}
			app.translatePromtsToImages();
		},
		
		generateMeta: async () => {
			app.loader( true, 'Генерация meta title' );
			
			$('#step4').show();
			
			let header;
			if( $('.aiassist-tab[data-tab="standart"]').hasClass('active') )
				header = $('#aiassist-theme-standart').val();
			else
				header = $('#aiassist-header').val();
			
			let lang_id = parseInt( $('.aiassist-lang-promts:visible:first').val() );
			
			let data = await app.addTask( { action: 'generateTitle', prom: $('#aiassist-title-prom').val(), header: header, lang_id: lang_id } );
			
			if( data.content ){
				$('#aiassist-title').val( data.content );
				await app.request( { val: data.content, act: 'title', action: 'saveStep', nonce: aiassist.nonce } );
			}
			
			$('#aiassist-loader-info').text('Генерация meta description');
			data = await app.addTask( { action: 'generateDesc', prom: $('#aiassist-desc-prom').val(), header: header, lang_id: lang_id } );
			
			if( data.content ){
				$('#aiassist-desc').val( data.content );
				await app.request( { val: data.content, act: 'desc', action: 'saveStep', nonce: aiassist.nonce } );
			}
			
			app.loader();
		},
		
		errorLog: ( error ) => {
			if( $('#aiasist #error').length )
				$('#aiasist #error').remove();
			
			$('#aiasist').prepend('<div id="error">'+ error +'</div>').delay(500).queue( next => { 
				$('#aiasist #error').addClass('active');
				next();
			})
		},
		
		loader: ( e = false, info = '' ) => {
			if( $('#aiassist-loader-wrap').length ){
				$('#aiasist').removeClass('disabled');
				$('#aiassist-loader-wrap').remove();
			}
			
			if( e ){
				$('#aiasist').addClass('disabled');
				$('#aiasist').after('<div id="aiassist-loader-wrap"><div id="aiassist-loader-info">'+ info +'</div><div id="aiassist-loader"></div><div id="aiassist-step-stop">Отмена</div></div>');
			}
		},
		
		addTask: ( args ) => {
			app.limitMsg = false;
			
			if( ! aiassist.token ){
				app.loader();
				$('#aiasist').after('<div id="aiassist-loader-wrap"><div id="aiassist-loader-info"><span class="wpai-warning-limits">Вы не добавили API-ключ! Ключ после регистрации в плагине отправляется на почту. Зарегистрируйтесь и добавьте ключ из письма в специальное поле в настройках плагина и генерация станет доступна.</span></div><div id="aiassist-step-stop">Закрыть</div></div>');
				return;
			}
			
			return new Promise( async resolve => {
				try{
					while( true ){
						let task = await app.request( Object.assign( { token: aiassist.token, gptkey: aiassist.gptkey, model: app.model }, args ), aiassist.apiurl );
						
						if( task.limit && $('#tokens-left').length ){
							$('#tokens-left').text( app.number_format( task.limit ) );
							
							if( task.limit < 1 && ! app.limitMsg ){
								app.limitMsg = true;
								app.loader( true, '<span class="wpai-warning-limits">Закончились лимиты! Не закрывайте страницу, пополните баланс и генерация автоматически продолжится. <a href="/wp-admin/admin.php?page=wpai-assistant" target="_blank">Пополнить баланс</a></span>' );
							}
							
						}
						
						if( task.task_id ){
							let data = await app.getTask( task.task_id );
							resolve( data );
							break;
						} else
							await app.sleep( 5 );
					}
				} catch {}
			})
		},
		
		getTask: ( task_id ) => {
			app.limitMsg = false;
			
			return new Promise( async resolve => {
				while( true ){
					try{
						data = await app.request( { token: aiassist.token, gptkey: aiassist.gptkey, action: 'getTask', id: task_id }, aiassist.apiurl );
						
						if( data.limit && $('#tokens-left').length ){
							$('#tokens-left').text( app.number_format( data.limit ) );
							
							if( data.limit < 1 && ! app.limitMsg ){
								app.limitMsg = true;
								app.loader( true, '<span class="wpai-warning-limits">Закончились лимиты! Не закрывайте страницу, пополните баланс и генерация автоматически продолжится. <a href="/wp-admin/admin.php?page=wpai-assistant" target="_blank">Пополнить баланс</a></span></span>' );
							}
						}
						
						if( data.content ){
							if( data.symbols && $('#aiassist-article-symbols').length ){
								let spent = $('#aiassist-article-symbols').text();
								spent = spent.replace(/[^0-9]/, '');
								spent = parseInt( spent );
								
								if( isNaN( spent ) )
									spent = 0;
								
								spent = spent + parseInt( data.symbols );
								
								$('#aiassist-article-symbols').text( spent );
								app.setCookie( 'spent', spent );
							}
						
							resolve( data )
							break;
						}
					} catch {}
					
					await app.sleep(5);
				}
			})
		},
		
		buffer: ( data ) => {
			let temp = $('<textarea>');
			$('body').append( temp );
			temp.val( data ).select();
			document.execCommand('copy');
			temp.remove();
		},
		
		setCookie: ( key, value ) => {
			const expires = new Date();
			expires.setTime( expires.getTime() + (999 * 24 * 60 * 60 * 1000) );
			document.cookie = `${key}=${value};expires=${expires.toUTCString()};path=/`;
		},
		
		getCookie: ( key ) => {
			const cookies = document.cookie.split(';');
			
			for( const cookie of cookies ){
				const [ cookieName, cookieValue ] = cookie.split('=');
				
				if( cookieName.trim() === key )
					return cookieValue;
			}
			return null;
		},

		getFormData: ( e ) => {
			data = {};

			e.serializeArray().map(( e ) => {
				if( ( val = e.value.trim() ) )
					data[ e.name ] = val;
			});
			return data;
		},
		
		sleep: ( s ) => {
			return new Promise( resolve => setTimeout( () => resolve(true), s * 1000) );
		},
		
		request: ( args = {}, url = false ) => {
			return new Promise( resolve => $.ajax({ url: url || aiassist.ajaxurl, type: 'POST', data: args, timeout: 120000, dataType: 'json', success: data => resolve( data ), error: error => resolve( true ) }) )
		}

	}

	app.init();
	
});