const aiImageBlcokEl = wp.element.createElement;

wp.blocks.registerBlockType('ai-image-creator/ai-image-creator', {

	icon: 'format-image',
	
	title: 'AI Image Creator',
	
	category: 'common',
	
	priority: 5,
	
	attributes: {
		promt: { type: 'string', default: '' },
		model: { type: 'string', default: 'midjourney' },
		
		images: {
			type: 'array',
			default: [],
			items: {
				type: 'object',
				properties: {
					id: { type: 'number', default: 0 },
					alt: { type: 'string', default: '' },
					url: { type: 'string', default: '' },
					title: { type: 'string', default: ''},
				},
			}
		},
	},
	
	edit: function( props ){
		const $ = jQuery;
		
		const request = ( args = {}, url = false ) => {
			return new Promise( resolve => $.ajax({ url: url || aiassist.ajaxurl, type: 'POST', data: args, timeout: 120000, dataType: 'json', success: data => resolve( data ), error: error => resolve( true ) }) )
		}
		
		const sleep = ( s ) => {
			return new Promise( resolve => setTimeout( () => resolve(true), s * 1000) );
		}
		
		async function image_generator( event ){
			let proccess = 0;
			let block = $( event.currentTarget ).closest('.aiassist-image-block');
			
			if( ! props.attributes.promt.trim().length ){
				block.find('.aiassist-image-promt').addClass('aiassist-error');
				return false;
			}
			block.find('.aiassist-image-promt').removeClass('aiassist-error');
			
			block.find('.aiassist-image-save').hide();
			block.find('.aiassist-image-item').addClass('aiassist-images aiassist-proces disabled');
			
			if( props.attributes.promt.match(/[А-Яа-я]/g) && ( props.attributes.model == 'midjourney' || props.attributes.model == 'flux' ) ){
				let task = await request( { action: 'translate', token: aiassist.token, content: props.attributes.promt }, aiassist.apiurl );
				
				if( task.task_id ){
					let translate = await request( { action: 'getTask', token: aiassist.token, id: task.task_id }, aiassist.apiurl );
					
					if( translate.content ){
						props.attributes.promt = translate.content;
						block.find('.aiassist-image-promt').val( translate.content );
						wp.data.dispatch('core/block-editor').updateBlockAttributes( event.target.closest('.wp-block').getAttribute('data-block'), { promt: translate.content } );
					}
				} else
					block.find('.aiassist-image-promt').val('Error translate promt');
			}
			
			let task = await request( { action: 'image_generator', token: aiassist.token, model: props.attributes.model, header: props.attributes.promt, format: 'jpg' }, aiassist.apiurl );
			
			if( parseInt( task.limit ) < 1 )
				block.find('.aiassist-images').removeClass('aiassist-proces disabled').html('<span class="wpai-warning-limits">Закончились лимиты! Не закрывайте страницу, пополните баланс и генерация автоматически продолжится. <a href="/wp-admin/admin.php?page=wpai-assistant" target="_blank">Пополнить баланс</a></span></span>');
				
			if( task.task_id ){
				while( true ){
					let data = await request( { action: 'getTask', id: task.task_id, token: aiassist.token }, aiassist.apiurl );
					
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
					await sleep(5);
				}
			}
		}
		
		return aiImageBlcokEl(
			'div',
			{
				class: 'aiassist-image-block',
			},
			
			aiImageBlcokEl(
				'select',
				{
					class: 'aiassist-image-model',
					name: 'aiassist-image-model',
					value: props.attributes.model,
					onChange: ( event ) => {
						props.setAttributes({ model: event.target.value });
						$( event.currentTarget ).closest('.aiassist-image-block').find('.aiassist-image-item').removeClass('dalle midjourney flux').addClass( event.target.value );
					},
				},
				aiImageBlcokEl(
					'option',
					{ value: 'midjourney' },
					'Midjourney'
				),
				aiImageBlcokEl(
					'option',
					{ value: 'dalle' },
					'Dalle'
				),
				aiImageBlcokEl(
					'option',
					{ value: 'flux' },
					'FLUX schnell'
				),
			),
			
			aiImageBlcokEl(
				'input',
				{
					type: 'text',
					class: 'aiassist-image-promt',
					name: 'aiassist-image-promt',
					placeholder: 'Input promt',
					onInput: ( event ) => {
						wp.data.dispatch('core/block-editor').updateBlockAttributes( event.target.closest('.wp-block').getAttribute('data-block'), { promt: event.target.value } );
					},
				}
			),
			
			aiImageBlcokEl(
				'button',
				{
					name: 'aiassist-generate',
					class: 'aiassist-image-generate',
					onClick: image_generator,
					value: props.attributes.promt,
				},
				'Generate',
			),
			
			aiImageBlcokEl(
				'button',
				{
					name: 'aiassist-translate',
					class: 'aiassist-image-translate',
					onClick: async ( event ) => {
						let e = $( event.currentTarget );
						let block = e.closest('.aiassist-image-block');
						let title = block.find('.aiassist-image-promt').val();
						
						let task = await request( { action: 'translate', token: aiassist.token, content: title }, aiassist.apiurl );
						
						if( parseInt( task.limit ) < 1 )
							block.find('.aiassist-image-item').removeClass('aiassist-proces disabled').html('<span class="wpai-warning-limits">Закончились лимиты! Не закрывайте страницу, пополните баланс и генерация автоматически продолжится. <a href="/wp-admin/admin.php?page=wpai-assistant" target="_blank">Пополнить баланс</a></span></span>');
						
						if( task.task_id ){
							let translate = await request( { action: 'getTask', token: aiassist.token, id: task.task_id }, aiassist.apiurl );
							
							if( translate.content ){
								block.find('.aiassist-image-promt').val( translate.content );
								wp.data.dispatch('core/block-editor').updateBlockAttributes( event.target.closest('.wp-block').getAttribute('data-block'), { promt: translate.content } );
								
								return;
							}
						}
						block.find('.aiassist-image-promt').val('Error translate promt');
					},
				},
				'Translate',
			),
			
			aiImageBlcokEl(
				'a',
				{ href: 'https://aiwpwriter.com/prices/', target: '_blank', class: 'aiassist-small' },
				'Посмотреть тарифы',
			),
			
			aiImageBlcokEl(
				'div',
				{},
				props.attributes.images.map( ( image, index ) => {
					return aiImageBlcokEl(
						'img',
						{
							key: index,
							alt: image.alt,
							class: 'progressImageUrl',
							src: image.url,
							style: { width: '100%' }
						}
					);
				})
			),
			
			aiImageBlcokEl(
				'div',
				{
					class: 'aiassist-image-item',
				}
			),
			
			aiImageBlcokEl(
				'div',
				{
					class: 'aiassist-image-save-button-wrap',
				},
				aiImageBlcokEl(
					'button',
					{
						name: 'aiassist-save',
						class: 'aiassist-image-save',
						onClick: async ( event ) => {
							let e = $( event.currentTarget );
							let block = e.closest('.aiassist-image-block');
							let title = block.find('.aiassist-image-promt').val();
							
							block.find('.aiassist-image-save').hide();
							block.find('.aiassist-images').addClass('aiassist-proces disabled');
							
							if( block.find('.ai-image.active').length ){
								let images = [];
								let data = props.attributes.images;
								
								block.find('.ai-image.active').each(function(){
									images.push( $(this).attr('src') );
								})
								
								for( let k in images ){
									let load = await request( { action: 'loadImage', post_id: wp.data.select('core/editor').getCurrentPostId(), 'image[src]': images[ k ], 'image[title]': title, nonce: aiassist.nonce } );
									
									data = [
										...data,
										{
											id: load.id,
											url: load.url,
											title: title,
											alt: title +' фото',
										}
									];
								}

								wp.data.dispatch('core/block-editor').updateBlockAttributes( event.target.closest('.wp-block').getAttribute('data-block'), { images: data });
								block.find('.aiassist-images').removeClass('aiassist-proces disabled').html('');
							}
						},
					},
					'Save',
				),
			),
			
		);
		
	},
	
	save: function( props ){
		const images = props.attributes.images.map( image => {
			return aiImageBlcokEl(
				'img',
				{
					src: image.url,
					class: 'size-full wp-image-'+ image.id +' aligncenter',
					alt: image.alt,
					title: image.title,
				}
			);
		});
		return aiImageBlcokEl( 'div', {}, images );
	}
	
});