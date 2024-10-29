<div id="aiasist">
	<div class="tokens-left <?php echo (int) @$this->info->limit < 1000 ? 'wpai-warning-limits' : '' ?>">
		Осталось лимитов: <span id="tokens-left"><?php echo number_format( (int) @$this->info->limit, 0, ' ', ' ' ) ?></span>
	</div>
	
	<label id="aiassist-text-gen-model">
		<div>Модель генерации</div>
		<select name="aiassist-text-model" id="aiassist-change-text-model">
			<option value="gpt3">GPT-4o mini</option>
			<option value="gpt4">GPT-4o</option>
		</select>
		<a href="https://aiwpwriter.com/prices/" target="_blank" class="aiassist-small">Посмотреть тарифы</a>
	</label>
	
	<div class="aiassist-tabs">
		<div class="aiassist-tab active" data-tab="standart">Генерация одним запросом</div>
		<div class="aiassist-tab" data-tab="long">Генерация статьи по плану (большая статья)</div>
	</div>
	
	<button type="button" class="aiassist-set-default-promts">Восстановить промпты по умолчанию</button>
	
	<div class="aiassist-tab-data active" data-tab="standart">
		
		<div class="aiassist-item center">
			<p>Введите ключевую фразу по которой хотите написать статью, она автоматически подставится в промпт. Для того чтобы сгенерировались метатеги и картинки, это поле должно быть обязательно заполнено.</p>
			<div>
				<input id="aiassist-theme-standart" class="aiassist-prom" placeholder="Введите тему..." value="<?php echo esc_attr( isset( $this->steps['aiassist-theme-standart'] ) ? $this->steps['aiassist-theme-standart'] : '' )?>" />
			</div>
			<p>Вы можете изменить промпт запроса по своему усмотрению, от него зависит какой получится статья. Вместо переменой {key} подставится ключевая фраза.</p>
			
			<?php if( @$this->info->promts->lang ){ $lang_id = 0; ?>
				<div class="aiassist-lang-block">
					<div class="aiassist-lang-promts-item">
						<div>Promt lang: </div>
						<select class="aiassist-lang-promts">
							<?php foreach( $this->info->promts->lang as $k => $lang ){ ?>
								<?php
									if( @$this->steps['promts']['short_lang'] == $k )
										$lang_id = (int) $k;
								?>
							
								<option value="<?php echo (int) $k ?>" <?php echo @$this->steps['promts']['short_lang'] == $k ? 'selected' : '' ?> ><?php echo esc_html( $lang ) ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			<?php } ?>
			
			<textarea id="aiassist-article-prom" class="aiassist-prom"><?php echo esc_textarea( @$this->steps['promts']['short'][ $lang_id ] ? trim( $this->steps['promts']['short'][ $lang_id ] ) : @$this->info->promts->short[ $lang_id ] )?></textarea>
		</div>
		
		<div class="next-step">
			<button type="button" id="aiassist-standart-generate">Создать текст статьи</button>
		</div>
		
	</div>
	
	
	<div class="aiassist-tab-data" data-tab="long">
		
		<label>
			<input type="checkbox" id="exclude_context" <?php echo esc_html( @$_COOKIE['excludeContext'] ? 'checked' : '' )?> />
			При генерации учитывать в контексте только план статьи и заголовок h1 <div class="aiassist-info" title="<?php echo esc_attr( $this->info->newGenerationInfo ) ?>">&#63;</div>
		</label>
	
		<div class="aiassist-item center">
			
			<?php if( @$this->info->promts->lang ){ $lang_id = 0; ?>
				<div class="aiassist-lang-block">
					<div class="aiassist-lang-promts-item">
						<div>Язык промптов: </div>
						<select class="aiassist-lang-promts">
							<?php foreach( $this->info->promts->lang as $k => $lang ){ ?>
								<?php
									if( @$this->steps['promts']['long_lang'] == $k )
										$lang_id = (int) $k;
								?>
							
								<option value="<?php echo (int) $k ?>" <?php echo @$this->steps['promts']['long_lang'] == $k ? 'selected' : '' ?> ><?php echo esc_html( $lang ) ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			<?php } ?>
			
			Промт: <input id="aiassist-theme-prom" class="aiassist-prom" value="<?php echo esc_attr( @$this->steps['promts']['long_header'][ $lang_id ] ? $this->steps['promts']['long_header'][ $lang_id ] : @$this->info->promts->long_header[ $lang_id ] )?>" />
			<br /><br />
			<div>
				<input id="aiassist-theme" class="aiassist-prom" placeholder="Введите тему..." value="<?php echo esc_attr( isset( $this->steps['aiassist-theme'] ) ? $this->steps['aiassist-theme'] : '' )?>" />
			</div>
			
			<div class="next-step">
				<button type="button" id="aiassist-theme-generate">Сгенерировать заголовок статьи</button>
			</div>
		</div>
		
		<div class="aiassist-item center step <?php echo esc_attr( isset( $this->steps['header'] ) ? 'active' : '' )?>" id="step1">
			<input name="aiassist_header" id="aiassist-header" value="<?php echo esc_attr( isset( $this->steps['header'] ) ? $this->steps['header'] : '' )?>" />
			<div class="next-step">
				Промт для плана статьи. Вместо переменой {key} подставится ключевая фраза. <textarea id="aiassist-structure-prom" class="aiassist-prom"><?php echo esc_attr( @$this->steps['promts']['long_structure'][ $lang_id ] ? $this->steps['promts']['long_structure'][ $lang_id ] : @$this->info->promts->long_structure[ $lang_id ] )?></textarea>
				<button type="button" id="aiassist-structure-generate">Создать структуру статьи</button>
			</div>
		</div>
		
		<div class="aiassist-item center step <?php echo esc_attr( isset( $this->steps['structure'] ) ? 'active' : '' )?>" id="step2">
			<textarea id="aiassist-structure"><?php echo esc_textarea( isset( $this->steps['structure'] ) ? $this->steps['structure'] : '' )?></textarea>
			<div class="next-step">
				Промт: <textarea id="aiassist-content-prom" class="aiassist-prom"><?php echo esc_attr( @$this->steps['promts']['long'][ $lang_id ] ? $this->steps['promts']['long'][ $lang_id ] : @$this->info->promts->long[ $lang_id ] )?></textarea>
				<button type="button" id="aiassist-content-generate">Создать текст статьи</button>
			</div>
		</div>

	</div>
	
	<div class="aiassist-item center step <?php echo esc_attr( isset( $this->steps['content'] ) ? 'active' : '' )?>" id="step3">
		<?php wp_editor( ( isset( $this->steps['content'] ) ? $this->steps['content'] : '' ), 'AIASSIST', [ 'textarea_name' => 'aiassist_content', 'media_buttons' => false, 'quicktags' => true ] ); ?>
		<div class="next-step">
			<div>Промт: <input id="aiassist-title-prom" class="aiassist-prom" value="<?php echo esc_attr( @$this->steps['promts']['long_title'][ $lang_id ] ? $this->steps['promts']['long_title'][ $lang_id ] : @$this->info->promts->long_title[ $lang_id ] )?>" /></div>
			<div>Промт: <input id="aiassist-desc-prom" class="aiassist-prom" value="<?php echo esc_attr( @$this->steps['promts']['long_desc'][ $lang_id ] ? $this->steps['promts']['long_desc'][ $lang_id ] : @$this->info->promts->long_desc[ $lang_id ] )?>" /></div>
			<button type="button" id="aiassist-meta-generate">Создать метатеги</button>
		</div>
	</div>
	
	<div class="aiassist-item center step <?php echo esc_attr( isset( $this->steps['title'] ) ? 'active' : '' )?>" id="step4">
		<input name="aiassist_title" id="aiassist-title" value="<?php echo esc_attr( isset( $this->steps['title'] ) ? $this->steps['title'] : '' )?>" />
		<textarea name="aiassist_desc" id="aiassist-desc"><?php echo esc_textarea( isset( $this->steps['desc'] ) ? $this->steps['desc'] : '' )?></textarea>
	</div>
	
	
	
	<div class="aiassist-item step aiassist-images-generator <?php echo esc_attr( isset( $this->steps['content'] ) ? 'active' : '' )?>" id="step6">
	
		<div class="aiassist-step-title center">Генерация изображений для статьи. Выберите модель:</div>
		
		<select name="aiassist-image-model" id="aiassist-change-image-model">
			<option value="flux">FLUX schnell</option>
			<option value="midjourney">Midjourney</option>
			<option value="dalle">Dalle 3</option>
		</select>
		<a href="https://aiwpwriter.com/prices/" target="_blank" class="aiassist-small aiassist-after-change-image-model">Посмотреть тарифы</a>
		
		<div class="aiassist-step-desc">Для каких заголовков генерировать изображения:</div>
	
		<label>
			<input type="checkbox" id="aiassist-images-generator-all-headers" /> Для всех
		</label>
	
		<div class="aiassist-headers">
			<?php 
				if( ! @$this->steps['header'] )
					$this->steps['header'] = @$this->steps['aiassist-theme-standart'];
			?>
			<?php if( $this->steps['header'] ){ ?>
				<div class="aiassist-header-item aiassist-main-header">
					<div class="left">Изображение записи</div>
					<label><input type="checkbox" value="<?php echo esc_attr( @$this->steps['header'] )?>" /><span><?php echo esc_html( @$this->steps['header'] )?></span></label>
					<div class="aiassist-translate-promt-image">Promt: <input id="aiassist-main" value="<?php echo esc_attr( @$this->steps['header'] )?>" data-en="<?php echo esc_attr( @$this->steps[ @$this->steps['header'] ] )?>" /> <div class="image-generate-item">Сгенерировать</div></div>
				</div>
			<?php } ?>
			
			<?php if( @preg_match_all('/<h[2-6][^>]*>([^<]+)<\/h[2-6]>/is', @$this->steps['content'], $headers ) ){ ?>
				<?php foreach( $headers[1] as $k => $header ){ ?>
					<div class="aiassist-header-item">
						<label><input type="checkbox" value="<?php echo esc_attr( $header )?>" /><span><?php echo esc_html( $header )?></span></label>
						<div class="aiassist-translate-promt-image">Promt: <input value="<?php echo esc_attr( @$this->steps['header'] .' '. $header )?>" data-en="<?php echo esc_attr( @$this->steps[ @$header ] )?>" /> <div class="image-generate-item">Сгенерировать</div></div>
					</div>
				<?php } ?>
			<?php } ?>
		</div>

	
		<div class="next-step">
			<button name="aiassist_generate_image" type="button" id="aiassist-images-generator-start">Сгенерировать</button>
		</div>
	</div>
	
	
	
	
	<div class="next-step" id="step5">
		<div>На статью потрачено: <span id="aiassist-article-symbols"><?php echo esc_html( isset( $_COOKIE['spent'] ) ? (int) $_COOKIE['spent'] : 0 )?></span> лимитов</div>
		<div>На генерацию изображений потрачено: <span id="images-article-symbols"><?php echo esc_html( isset( $_COOKIE['imgSpent'] ) ? (int) $_COOKIE['imgSpent'] : 0 )?></span> лимитов</div>
		
		<button type="button" id="aiassist-clear-content">Очистить</button>
		<button name="aiassist_save" type="button" id="aiassist-save-content">Сохранить</button>
	</div>
	
	<div id="aiassist-regenerate-wrap">
		<div id="aiassist-regenerate-close">&#10006;</div>
		
		<button type="button" class="aiassist-set-default-promts-regenerate">Восстановить промпты по умолчанию</button>
		
		<?php if( @$this->info->promts->lang ){ $lang_id = 0; ?>
			<select class="aiassist-lang-promts-regenerate">
				<?php foreach( $this->info->promts->lang as $k => $lang ){ ?>
					<?php
						if( @$this->steps['promts']['regenerate_lang'] == $k )
							$lang_id = (int) $k;
					?>
				
					<option value="<?php echo (int) $k ?>" <?php echo @$this->steps['promts']['regenerate_lang'] == $k ? 'selected' : '' ?> ><?php echo esc_html( $lang ) ?></option>
				<?php } ?>
			</select>
		<?php } ?>
		
		Промт: <input id="aiassist-prom-regenerate" class="aiassist-prom" value="<?php echo esc_textarea( @$this->steps['promts']['regenerate'][ $lang_id ] ? trim( $this->steps['promts']['regenerate'][ $lang_id ] ) : @@$this->info->promts->regenerate[ $lang_id ] )?>" />
		
		<button type="button" id="aiassist-regenerate">Сгенерировать</button>
	</div>
	
	<div id="aiassist-generate-image">
		<div id="aiassist-generate-image-close">&#10006;</div>
		<div class="aiassist-image-tiny">
			<select id="aiassist-tiny-image-model" name="aiassist-image-model">
				<option value="flux">FLUX schnell</option>
				<option value="midjourney">Midjourney</option>
				<option value="dalle">Dalle</option>
			</select>
			<input type="text" name="aiassist-image-promt" id="aiassist-tiny-image-promt" placeholder="Input promt" />
			<button type="button" name="aiassist-generate" id="aiassist-tiny-image-generate">Generate</button>
			<button type="button" name="aiassist-translate" id="aiassist-tiny-image-translate">Translate</button>
			
			<a href="https://aiwpwriter.com/prices/" target="_blank" class="aiassist-small">Посмотреть тарифы</a>
			
			<div class="aiassist-image-tiny-item"></div>
			<div class="aiassist-image-tiny-save-button-wrap">
				<button type="button" name="aiassist-save" id="aiassist-tiny-image-save">Save</button>
			</div>
		</div>
	</div>
		
</div>