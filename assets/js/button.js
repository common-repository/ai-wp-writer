( ($) => {
	
	const button = {
		
		getTask: ( task_id ) => {
			return new Promise( async resolve => {
				while( true ){
					try{
						data = await button.request( { token: aiassist.token, gptkey: aiassist.gptkey, action: 'getTask', id: task_id }, aiassist.apiurl );
						
						if( data.content ){
							if( data.limit && $('#tokens-left').length )
								$('#tokens-left').text( data.limit );
							
							if( data.symbols && $('#aiassist-article-symbols').length ){
								let spent = $('#aiassist-article-symbols').text();
								spent = spent.replace(/[^0-9]/, '');
								spent = parseInt( spent );
								
								if( isNaN( spent ) )
									spent = 0;
								
								spent = spent + parseInt( data.symbols );
								
								$('#aiassist-article-symbols').text( spent );
								button.setCookie( 'spent', spent );
							}
						
							resolve( data )
							break;
						}
					} catch {}
					
					await button.sleep(5);
				}
			})
		},
		
		setCookie: ( key, value ) => {
			const expires = new Date();
			expires.setTime( expires.getTime() + (999 * 24 * 60 * 60 * 1000) );
			document.cookie = `${key}=${value};expires=${expires.toUTCString()};path=/`;
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
		
		sleep: ( s ) => {
			return new Promise( resolve => setTimeout( () => resolve(true), s * 1000) );
		},
		
		request: ( args = {}, url = false ) => {
			return new Promise( resolve => $.ajax({ url: url || aiassist.ajaxurl, type: 'POST', data: args, timeout: 120000, dataType: 'json', success: data => resolve( data ), error: error => resolve( true ) }) )
		}

	}

	tinymce.create('tinymce.plugins.key', {
		init: ( ed, url ) => {
			// if( ed.id != 'AIASSIST' )
				// return;
		
			ed.addButton('AIASSIST', {
				title : 'Перегенерация выделенного фрагмента текста',
				text: 'AI assist',
				icon: false,
				onclick: () => {
					$('#aiassist-regenerate-wrap').show();
					$('#aiassist-prom-regenerate').focus().blur();
				}
			});
			
			$(document).on('click', '#aiassist-regenerate', async () => {
				button.loader( true );
				
				$('#aiassist-regenerate-close').click();
				$('#aiassist-prom-regenerate').val();
				let task = await button.request( { content: ed.selection.getContent(), prom: $('#aiassist-prom-regenerate').val(), lang_id: parseInt( $('.aiassist-lang-promts-regenerate:first').val() ), token: aiassist.token, gptkey: aiassist.gptkey, action: 'reGenerateContent' }, aiassist.apiurl );
				
				data = await button.getTask( task.task_id );
				
				if( data.content )
					ed.selection.setContent( data.content );						
				
				button.loader();
			})
			
			$(document).on('click', '#aiassist-regenerate-close', () => $('#aiassist-regenerate-wrap').hide() );
		},
		
		createControl: (n, cm) => {
			return null;
		},
		
	});
	tinymce.PluginManager.add('button', tinymce.plugins.key);
	
} )( jQuery )