<div id="aiassist-settings">
	<div class="wpai-header">
		<div class="wpai-logo-block">
			<div class="f-left">
				<div id="wpai-logo"></div>
			</div>
		</div>
		
		<div class="wpai-info">
			<?php if( isset( $this->options->token ) ){ ?>
				<div class="wpai-symbols <?php echo (int) @$this->info->limit < 1000 ? 'wpai-warning-limits' : '' ?>">
					<div id="wpai-symbols-text">Осталось лимитов:</div>
					<div id="wpai-symbols"><?php echo number_format( (int) @$this->info->limit, 0, ' ', ' ' )?></div>
				</div>
			<?php } ?>
			
			<div class="wpai-telegram">
				<div id="wpai-doc"></div>
				
				<div class="help-block">
					<div id="wpai-title">Необходима помощь?</div>
					<div onclick="window.open('https://t.me/wpwriter', '_blank')" id="telegram">Напишите в Telegram</div>
				</div>
			</div>
		</div>
	</div>

	<div class="aiassist-tabs">
		<div class="aiassist-tab active" data-tab="settings">Настройки</div>
		<div class="aiassist-tab" data-tab="rates">Тарифы и оплата</div>
		<div class="aiassist-tab" data-tab="generations">Массовая генерация</div>
		<div class="aiassist-tab" data-tab="rewrite">Рерайт</div>
		<div class="aiassist-tab" data-tab="guide">Генерация в редакторе</div>
		<div class="aiassist-tab" data-tab="referrals">Партнерская программа</div>
	</div>
	
	<form id="aiassist-get-bonus" class="aiassist-tab-data" data-tab="referrals">
		
		<div class="aiassist-white-bg">
			Привлекайте в плагин новых пользователей и зарабатывайте на этом!<br />
			Новые пользователи которые введут реферальный код, получат скидку 15% при первом депозите, <br />
			а вам будет начисляться выплата 10% с первого и последующих депозитов вебмастера. <br />
			Чем больше привлечете активных пользователей, тем больше будет ваш заработок. <br />
			Выплата осуществляется по запросу, в течение суток, на кошелек USDT trc20. <br />
			При выплате удерживается комиссия сети в размере 1.4 usdt. <br />
			Участвуя в реферальной программе вы соглашаетесь с <a href="https://aiwpwriter.com/user-agreement/">правилами</a>.
		</div>
		<br /><br />
		
		<div class="aiassist-bonus-item">
			<b>Реферальный код:</b> <span id="aiassist-promocode"><?php echo esc_html( @$this->info->referral )?></span>
		</div>
		
		<div class="aiassist-bonus-item">
			<b>Ваш баланс:</b> <?php echo (float) @$this->info->bonus->amount ?> р. (<?php echo (float) @$this->info->bonus->amount_usdt ?>$)
		</div>
		
		<div class="aiassist-bonus-item">
			<b>Привлеченных рефералов:</b> <?php echo (int) @$this->info->bonus->count ?>
		</div>
		
		<div class="aiassist-bonus-item">
			<b>Способ для получения выплаты:</b>
			<div>
				<select name="method" required>
					<option value="usdt">USDT trc-20</option>
				</select>
			</div>
		</div>
		
		<div class="aiassist-bonus-item">
			<b>Укажите номер кошелька для получения платежа</b>
			<div>
				<input name="wallet" required />
			</div>
		</div>
		
		<div class="aiassist-bonus-item">
			<b>Укажите Telegram или email для связи, мы свяжемся если возникнут дополнительные вопросы.</b>
			<div>
				<input name="info" required />
			</div>
		</div>
		
		
		<div class="aiassist-bonus-item">
		
			<?php if( isset( $this->info->bonus->payment_request ) ){ ?>
				<div>Запрос на выплату принят: <?php echo date( 'd.m.Y H:i', $this->info->bonus->payment_request ) ?></div>
			<?php } ?>
			
			<?php if( (int) @$this->info->bonus->min_payment > (int) @$this->info->bonus->amount ){ ?>
				<div>Минимальный порог выплаты: <?php echo (int) @$this->info->bonus->min_payment ?> р</div>
			<?php } ?>
			
			<button class="aiassist-button <?php echo isset( $this->info->bonus->payment_request ) || $this->info->bonus->min_payment > (int) @$this->info->bonus->amount ? 'disabled' :'' ?>">Запросить выплату</button>
		</div>
	
	</form>
	
	<div class="aiassist-tab-data" data-tab="guide">
	
		<div class="aiassist-white-bg">
			Вы можете генерировать статьи непосредственно в редакторе Wordpress. Функционал плагина находится внизу страницы, под основным редактором. Так же можете генерировать изображения в любых статьях в произвольном месте через кнопку (или виджет в редакторе Gutenberg) AI image creator. Для перегенерации любого фрагмента текста в любых статьях используйте кнопку AI Assist, для этого выделите фрагмент текста, нажимите AI Assist и кнопку "Перегенерировать".
		</div>
		
		<div class="aiassist-guide-button">
			<a href="/wp-admin/post-new.php#ai_assistant" target="_blank" id="aiassist-new-post">Сгенерировать статью</a>
			<a href="/wp-admin/post-new.php?post_type=page#ai_assistant" target="_blank" id="aiassist-new-page">Сгенерировать страницу</a>
		</div>
	
	</div>
	
	<div class="aiassist-tab-data" data-tab="rewrite">
		
		<h2 class="generations-header">Рерайт</h2>
		
		<div class="aiassist-rewrite-items">
		
			<div class="center">Вы можете сделать рерайт всего своего сайта, отдельных страниц, категорий, а так же есть возможность рерайтить страницы сторонних сайтов по url. Мы постарались сделать так чтобы сторонние сайты рерайтились максимально качественно. При этом важно учитывать, что сторонние сайты имеют различную разметку, верстку и структуру, из-за этого иногда в статью после рерайта могут попадать нежелательные элементы. Рекомендуем для теста делать рерайт нескольких страниц. Если в статьи после рерайта попадает мусор, напишите нам в поддержку, мы готовы выполнить персональную адаптацию плагина под рерайт определенных сторонних сайтов для активных пользователей плагина.<br /></div>
			
			<div>Режим рерайта</div>
			<select name="rewrite-split" id="aiassist-rewrite-split" class="aiassist-rewrite-options">
				<option value="1" <?php echo esc_attr( @$rewrites['split'] == 1 ? 'selected' : '' )?>>Рерайт по абзацам</option>
				<option value="2" <?php echo esc_attr( @$rewrites['split'] == 2 ? 'selected' : '' )?>>Рерайт по сегментам между заголовками</option>
			</select>
			<br /><br />
			
			
			<div>
				<div>Категория рерайта</div>
				<select class="cat-rewrite">
					<option value="0">Категория</option>
					<?php if( $cats ){ ?>
						<?php foreach( $cats as $cat ){ ?>
							<option value="<?php echo esc_attr( $cat->term_id )?>"><?php echo esc_html( $cat->name )?></option>
						<?php } ?>
					<?php } ?>
				</select>
			</div>
			
			<div class="aiassist-rewrite-type-label">Укажите типы записей по которым нужно сделать рерайт:</div>
			
			<div class="mb-15 rewrite-block-type">
				<label><input type="checkbox" id="rewrite_all"/>Рерайт всех статей этого сайта</label>
				<?php if( $types = get_post_types( [ 'public' => true ] ) ){ unset( $types['attachment'] ); ?>
					<?php foreach( $types as $type ){?>
						<label><input type="checkbox" name="rewrite_type[]" value="<?php echo esc_attr( $type )?>" <?php echo esc_attr( @in_array($type, ( @$options->post_type ? @$options->post_type : [] ) ) ? 'checked' : '' )?> /> <?php echo esc_html( $type )?></label>
					<?php } ?>
				<?php } ?>
			</div>
			
			<div class="aiassist-rewrite-item-block">	

				<label>Или укажите список URL по которым нужно сделать рерайт. Можно добавить любые ссылки в том числе и на стороних ресурсах</label>
				
				<textarea class="aiassist-rewrite-item"></textarea>
				
				<div class="aiassist-cats-item">
					Выберите категорию в которую помещать статьи после рерайта:
					<select class="cats-item">
						<option value="0">Категория</option>
						<?php if( $cats ){ ?>
							<?php foreach( $cats as $cat ){ ?>
								<option value="<?php echo esc_attr( $cat->term_id )?>"><?php echo esc_html( $cat->name )?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</div>
				
			</div>
			
			
		</div>
		
		<div class="aiassist-item-repeater">
			<button id="aiassist-addItemRewrite">Добавить еще один список URL для еще одной категории</button>
		</div>
		
		<div class="aiassist-option-item">Промт для рерайта статей. По этому промту будет сделан рерайт заголовокв, абзацев, meta title и description.</div>
		
		<br /><br /><br />
		<div class="relative">
			<button type="button" class="aiassist-set-default-promts">Восстановить промпт по умолчанию</button>
		</div>
		
		<?php if( @$this->info->promts->lang ){ $lang_id = 0; ?>
			<div class="relative">
				<div class="aiassist-lang-promts-item">
					<label>Язык промпта: </label>
					<select class="aiassist-lang-promts">
						<?php foreach( $this->info->promts->lang as $k => $lang ){ ?>
							<?php
								if( @$this->steps['promts']['rewrite_lang'] == $k )
									$lang_id = (int) $k;
							?>
						
							<option value="<?php echo (int) $k ?>" <?php echo @$this->steps['promts']['rewrite_lang'] == $k ? 'selected' : '' ?> ><?php echo esc_html( $lang ) ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
		<?php } ?>
		
		<textarea class="aiassist-prom" id="aiassist-rewrite-prom"><?php echo esc_textarea( @$this->steps['promts']['rewrite'][ $lang_id ] ? trim( $this->steps['promts']['rewrite'][ $lang_id ] ) : @$this->info->promts->rewrite[ $lang_id ] )?></textarea>
		
		<div class="aiassist-option-item">
			Генерация изображение для статьи после рерайта на основе заголовков. Если оставить поустым, то рерайт будет делаться без изображений.
			
			<label class="aiassist-option-item">
				<input type="checkbox" class="aiassist-rewrite-options" id="aiassist-rewrite-images" <?php echo esc_attr( @$rewrites['images'] ? 'checked' : '' ) ?> /> Генерировать все возможные изображения для записи
			</label>
			
			<label class="aiassist-option-item">
				<input type="checkbox" class="aiassist-rewrite-options" id="aiassist-rewrite-thumb" <?php echo esc_attr( @$rewrites['thumb'] ? 'checked' : '' ) ?> /> Генерировать только изображение записи (миниатюру)
			</label>
			
			<label class="aiassist-option-item">
				<input type="checkbox" class="aiassist-rewrite-options" id="aiassist-rewrite-draft" <?php echo esc_attr( @$rewrites['draft'] ? 'checked' : '' ) ?> /> Отправлять сгенерированные статьи в черновик (только при рерайте сторонних сайтов)
			</label>
			
			<div>
				<div>Модель генерации текста</div>
				<select name="aiassist-text-model" class="aiassist-rewrite-options" id="aiassist-rewrite-text-model">
					<option value="gpt3" <?php echo @$rewrites['textModel'] == 'gpt3' ? 'selected' : '' ?>>GPT-4o mini</option>
					<option value="gpt4" <?php echo @$rewrites['textModel'] == 'gpt4' ? 'selected' : '' ?>>GPT-4o</option>
				</select>
				<a href="https://aiwpwriter.com/prices/" target="_blank" class="aiassist-small">Посмотреть тарифы</a>
			</div>
			
			<div>
				<div>Модель генерации изображения</div>
				<select name="aiassist-image-model" class="aiassist-rewrite-options" id="aiassist-rewrite-image-model">
					<option value="flux" <?php echo @$rewrites['imageModel'] == 'flux' ? 'selected' : '' ?>>FLUX schnell</option>
					<option value="dalle" <?php echo @$rewrites['imageModel'] == 'dalle' ? 'selected' : '' ?>>Dalle 3</option>
					<option value="midjourney" <?php echo @$rewrites['imageModel'] == 'midjourney' ? 'selected' : '' ?>>Midjourney</option>
				</select>
			</div>
			
		</div>
		
		<div>
			Текст в иходных статьях будет заменяться на текст после рерайта. Если делается рерайт страниц стороннего сайта, то создадутся новые статьи. Воспользоваться кнопками "Востановить оригинальные текста" можно только если делается рерайт статей на своем сайте.
		</div>
		
		<div class="aiassist-option-item">
			<button id="start-rewrite-generations" <?php echo @$rewrites['start'] ? 'disabled' : '' ?>>Запустить рерайт</button>
			<button id="stop-rewrite-generations" <?php echo ! @$rewrites['start'] ? 'disabled' : '' ?>>Остановить рерайт</button>
			<button id="clear-rewrite-generations">Очистить список URL</button>
			<button id="restore-rewrite-generations" class="aiassist-orange">Восстановить все оригинальные тексты</button>
		</div>
		
		
		<div id="aiassist-rewrite-status">
			<?php 
			
				if( ! @$this->options->token ){
					
					echo '<span class="wpai-warning-limits">Вы не добавили API-ключ! Ключ после регистрации в плагине отправляется на почту. Зарегистрируйтесь и добавьте ключ из письма в специальное поле в настройках плагина и генерация станет доступна.</span>';
					
				} elseif( (int) @$this->info->limit < 1 ){
					
					echo '<span class="wpai-warning-limits">Лимиты заканчились, для продолжения генерации (рерайта) пополните баланс!</span>';
					
				} else {
					
					if( @$rewrites['start'] ) 
						echo 'Идет процесс рерайта статей, информация обновляется автоматически. Если этого не происходит, обновите страницу браузера, чтобы увидеть актуальный список статей по которым выполнен рерайт.';
						
					elseif( ! @$rewrites['start'] && isset( $rewrites['posts'] ) && @$rewrites['counter'] < count( $rewrites['posts'] ) )
						echo 'Процесс рерайта статей приостановлен.';
					
					elseif( isset( $rewrites['posts'] ) && @$rewrites['counter'] >= count( $rewrites['posts'] ) )
						echo 'Процесс рерайта статей завершен.';
				}
			?>
		</div>
		<div class="aiassist-option-item <?php echo ! isset( $rewrites['start'] ) ? 'hidden' : ''?>" id="aiassist-rewrite-progress">Выполнен рерайт  <span id="aiassist-rewrite-count-publish"><?php echo (int) @$rewrites['publish'] ?></span>  статей из <?php echo isset( $rewrites['posts'] ) ? (int) count( @$rewrites['posts'] ) : 0 ?></div>
		
		<div class="aiassist-rewrites-queue">
			<?php if( ! empty( $rewrites['posts'] ) ){ $queue = false; ?>
				<?php foreach( $rewrites['posts'] as $rewrite ){ ?>
					<div title="<?php echo esc_attr( isset( $rewrite['url'] ) ? $rewrite['url'] : $rewrite['title'] ) ?>">
						<?php if( isset( $rewrite['post_id'] ) ){ ?>
							<?php $queue = false; ?>
							<div class="aiassist-rewrite-queue"><a href="<?php echo get_edit_post_link( $rewrite['post_id'] ) ?>" target="_blank"><?php echo esc_attr( isset( $rewrite['url'] ) ? $rewrite['url'] : $rewrite['title'] ) ?></a> 
								
								<span class="aiassist-queue-status">
									<?php if( isset( $rewrite['restore'] ) ){ ?>
										Восстановлена
									<?php } else { ?>
										Сгенерирована
									<?php } ?>
								</span> 
							
								<?php if( isset( $rewrite['revision_id'] ) && ! isset( $rewrite['restore'] ) ){ ?>
									<span class="aiassist-post-restore aiassist-orange" post_id="<?php echo (int) $rewrite['post_id'] ?>" revision_id="<?php echo (int) $rewrite['revision_id'] ?>">Восстановить оригинальный текст</span>
								<?php } ?>
							</div>
						
						<?php } else { ?>
							<div class="aiassist-rewrite-queue aiassist-queue"><span class="aiassist-queue-rewrite"><?php echo esc_attr( isset( $rewrite['url'] ) ? $rewrite['url'] : $rewrite['title'] ) ?></span> <span class="aiassist-queue-status"><?php echo ! $queue ? ( (int) @$rewrite['check'] < 60 && @$this->info->limit > 1 ? 'Идет генерация' : 'Приостановлено' ) : 'В очереди' ?></span> </div>
						<?php $queue = true; ?>
						<?php } ?>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	
	</div>

	<div class="aiassist-tab-data active" data-tab="settings">
		<form method="POST" class="wpai-form">
			<div class="license">
				<div class="input-block">
					<div class="title">Введите API ключ</div>
					<?php if( ! isset( $this->options->token ) ){ ?>
						<label class="title"><p style="font-size: 16px; line-height:1.5;">Ключ придет на email после регистрации, если письмо не пришло, проверьте папку спам. Один ключ можно использовать на множестве сайтов. Если возникли вопросы, напишите в <a href="https://t.me/wpwriter" target="_blank">Telegram</a>.</p></label>
					<?php } ?>
					
					<input name="token" value="<?php echo esc_attr( @$this->options->token ) ?>" /><br /><br /><br />
				</div>
				<div class="row">
					<button name="save">Сохранить</button>
				</div>
			</div>
		</form>

		<?php if( $this->options->token ){ ?>
			<div class="title">Статистика</div>
			<form id="aiassist-stat">
				<button name="step" value="<?php echo esc_attr( date('Y-m-d') )?>|<?php echo esc_html( date('Y-m-d') )?>">Day</button>
				<button name="step" value="<?php echo esc_attr( date('Y-m-d', time() - 60*60*24*7) )?>|<?php echo esc_html( date('Y-m-d') )?>">Week</button>
				<button name="step" value="<?php echo esc_attr( date('Y-m-d', time() - 60*60*24*30) )?>|<?php echo esc_html( date('Y-m-d') )?>">Month</button>
				<br />
				<input type="date" name="dateStart" />
				<input type="date" name="dateEnd" />
				<button>Показать отчет</button>
			</form>

			<div id="area-chat"></div>
		<?php } ?>
		
		<br/>
		<div style="border: 2px solid #42b3d5; padding: 10px;">
    <h3>Как использовать плагин</h3>
    <ul>
        <li>- После того как ввели ключ, под текстовым редактором Classic Editor и Gutenberg появляется виджет плагина.</li>
        <li>- Для генерации картинок в любом месте статьи, в том числе для старых статей, есть кнопка "AI image creator". В редакторе Gutenberg добавьте новый блок и введите в поиске AI image creator.</li>
        <li>- Кнопка "AI assist" поможет перегенерировать не понравившийся кусок текста. Выделите часть текста и нажмите AI assist. Использовать перегенерацию текста можно для любых статей, в том числе уже опубликованных.</li>
		<li>- Вкладка "Массовая генерация" используется для создания статей в больших объемах по списку ключевых фраз. Можно запланировать чтобы каждый день автоматически публиковалось определенное количество статей.</li>
		<li>- Вкладка "Рерайт" используется когда нужно переписать текст с сохранением смысла. Можно использовать как для статей на своем сайте, так и для рерайта статей на сторонних ресурсах по списку url.</li>
    </ul>
		</div>

		<?php if( ! $this->options->token ){ ?>
			<div class="wpai-tabs">
				<div class="wpai-tab active" data-action="signIn" >Вход</div>
				<div class="wpai-tab" data-action="signUp">Регистрация</div>
			</div>
			<form method="POST" class="wpai-form" id="aiassist-sign" data-action="signIn">
				<div id="wpai-errors-messages"></div>
				<div class="row">
					<div>Mail</div>
					<input type="email" name="email" required />
				</div>
				
				<div class="row">
					<div>Your password</div>
					<input type="password" name="password" required />
				</div>
				
				<div class="row password2">
					<div>Repeat password</div>
					<input type="password" name="password2" />
					
					<label> <input type="checkbox" name="license" /> Регистрируясь, вы соглашаетесь с <a href="https://aiwpwriter.com/privacy-policy/" target="_blank">политикой конфиденциальности</a>, <a href="https://aiwpwriter.com/publichnaja-oferta-o-zakljuchenii-dogovora-ob-okazanii-uslug/" target="_blank">офертой</a> и <a href="https://aiwpwriter.com/user-agreement/" target="_blank">пользовательским соглашением</a>. </label>
				</div>
				
				<div class="row">
					<button></button>
				</div>
				
			</form>
		<?php } ?>
	</div>
	
	
	<div class="aiassist-tab-data" data-tab="rates">
		
		<div class="pay-methods">
			<div class="pay-method active">
				<div class="robokassa"></div>
				<div class="pay-method-label visa">Visa, Mastercard, Мир, ЮMoney</div>
			</div>
			
			<div class="pay-method">
				<div class="cryptocloud"></div>
				<div class="pay-method-label">USDT, Bitcoin, Ethereum</div>
			</div>
		</div>
	
		<h2 class="rates-header">Тарифы и оплата</h2>
		
		<form method="POST" class="aiassist-promocode">
		
			<label>
				<span>Промокод:</span>
				<input name="promocode" value="<?php echo isset( $_POST['promocode'] ) ? esc_attr( $_POST['promocode'] ) : '' ?>" />
				<button id="aiassist-promocode-set">Применить</button>
				
				<?php if( isset( $_POST['promocode'] ) ){ ?>
					<div class="aiassist-promocode-status <?php echo ! isset( $this->info->rates->discount ) ? 'error-discount' : '' ?>"><?php echo isset( $this->info->rates->discount ) ? 'Промокод активирован!' : 'Промокод не верный!' ?> </div>
				<?php } ?>
				
			</label>			
		
		</form>
		
		<div class="rates-block-robokassa">
		
			<div class="rates-item">
				<div class="title">Пополнение баланса на произвольную сумму</div>
				<div class="title-label">Пополнить баланс на любую сумму. Лимиты не сгорают. Генерировать можете в любое время. Лимиты так же используются для генерации изображений.</div>
				
				<form id="aiassist-custom-buy" class="aiassist-buy-form">
					<div class="header">Купить лимиты</div>
					<div>Цена <b><?php echo (float) @$this->info->price ?> рублей за 1 000 лимитов.</b> Введите <b>любую</b> сумму для пополения баланса:</div>
					<input type="number" step="1" min="0" id="out_summ" placeholder="5000 руб" required />
					<button class="aiassist-buy" data-type="custom">Купить</button>
				</form>
			</div>
			
			<div class="rates-item">
				<div class="title">Тариф базовый</div>
				<div class="title-label">Для тех кто генерирует несколько статьей в день</div>
				
				<div class="aiassist-buy-form">
					<div class="header"><b><?php echo number_format( @$this->info->rates->base_rate, 0, ' ', ' ' )?> рублей</b> вместо <i><?php echo number_format( @$this->info->rates->base_rate_after, 0, ' ', ' ' ) ?></i> экономия <?php echo number_format( @$this->info->rates->base_rate_economy, 0, ' ', ' ' ) ?> р. </div>
					
					<div>На баланс будет зачислено <b><?php echo number_format( @$this->info->rates->base_rate_symbols, 0, ' ', ' ' )?> лимитов</b></div>
					<div>В рамках тарифа цена составит <b><?php echo (float) @$this->info->rates->base_rate_price ?> рубля за 1000 лимитов</b></div>
					<button type="button" class="aiassist-buy" data-type="base">Купить</button>
				</div>
			</div>
			
			<div class="rates-item">
				<div class="title">Тариф профессиональный</div>
				<div class="title-label">Для тех кто генерирует в больших объемах</div>
				
				<div class="aiassist-buy-form">
					<div class="header"><b><?php echo number_format( @$this->info->rates->prof_rate, 0, ' ', ' ' )?> рублей</b> вместо <i><?php echo number_format( @$this->info->rates->prof_rate_after, 0, ' ', ' ' ) ?></i> экономия <?php echo number_format( @$this->info->rates->prof_rate_economy, 0, ' ', ' ' ) ?> р. </div>
					<div>На баланс будет зачислено <b><?php echo number_format( @$this->info->rates->prof_rate_symbols, 0, ' ', ' ' )?> лимитов</b></div>
					<div>В рамках тарифа цена составит <b><?php echo (float) @$this->info->rates->prof_rate_price ?> рубля за 1000 лимитов</b></div>
					<button type="button" class="aiassist-buy" data-type="professional">Купить</button>
				</div>
			</div>
		
		</div>
		
		<div class="rates-block-cryptocloud hide">
		
			<div class="rates-item">
				<div class="title">Пополнение баланса на произвольную сумму</div>
				<div class="title-label">Пополнить баланс на любую сумму. Лимиты не сгорают. Генерировать можете в любое время. Лимиты так же используются для генерации изображений.</div>
				
				<form id="aiassist-custom-buy" class="aiassist-buy-form">
					<div class="header">Купить лимиты</div>
					<div>Цена <b><?php echo (float) @$this->info->price_usdt ?> USDT за 1 000 лимитов.</b> Введите <b>любую</b> сумму для пополения баланса:</div>
					<input type="number" step="1" min="0" id="out_summ_usdt" placeholder="50 USDT" required />
					<button class="aiassist-buy" data-type="custom">Купить</button>
				</form>
			</div>
			
			<div class="rates-item">
				<div class="title">Тариф базовый</div>
				<div class="title-label">Для тех кто генерирует несколько статьей в день</div>
				
				<div class="aiassist-buy-form">
					<div class="header"><b><?php echo number_format( @$this->info->rates->base_rate_usdt, 0, ' ', ' ' )?> USDT</b> вместо <i><?php echo number_format( @$this->info->rates->base_rate_after_usdt, 0, ' ', ' ' ) ?></i> экономия <?php echo number_format( @$this->info->rates->base_rate_economy_usdt, 0, ' ', ' ' ) ?> USDT </div>
					
					<div>На баланс будет зачислено <b><?php echo number_format( @$this->info->rates->base_rate_symbols_usdt, 0, ' ', ' ' )?> лимитов</b></div>
					<div>В рамках тарифа цена составит <b><?php echo (float) @$this->info->rates->base_rate_price_usdt ?> USDT за 1000 лимитов</b></div>
					<button type="button" class="aiassist-buy" data-type="base">Купить</button>
				</div>
			</div>
			
			<div class="rates-item">
				<div class="title">Тариф профессиональный</div>
				<div class="title-label">Для тех кто генерирует в больших объемах</div>
				
				<div class="aiassist-buy-form">
					<div class="header"><b><?php echo number_format( @$this->info->rates->prof_rate_usdt, 0, ' ', ' ' )?> USDT</b> вместо <i><?php echo number_format( @$this->info->rates->prof_rate_after_usdt, 0, ' ', ' ' ) ?></i> экономия <?php echo number_format( @$this->info->rates->prof_rate_economy_usdt, 0, ' ', ' ' ) ?> USDT </div>
					<div>На баланс будет зачислено <b><?php echo number_format( @$this->info->rates->prof_rate_symbols_usdt, 0, ' ', ' ' )?> лимитов</b></div>
					<div>В рамках тарифа цена составит <b><?php echo (float) @$this->info->rates->prof_rate_price_usdt ?> USDT за 1000 лимитов</b></div>
					<button type="button" class="aiassist-buy" data-type="professional">Купить</button>
				</div>
			</div>
		
		</div>
		
	</div>
	
	
	<div class="aiassist-tab-data" data-tab="generations">
		<h2 class="generations-header">Массовая генерация</h2>
		
		<div class="aiassist-article-items">
		
			<div class="aiassist-article-item">
				<div>Добавьте ключевые слова списком, каждая строка это новая статья. В строке можно указать одну или несколько ключевых фраз через запятую</div>
				<textarea class="aiassist-keywords-item"></textarea>
				
				<div class="aiassist-cats-item">
					Выберите категорию в которую публиковать статьи
					
					<select class="cats-item">
						<option value="0">Категория</option>
						<?php if( $cats ){ ?>
							<?php foreach( $cats as $cat ){ ?>
								<option value="<?php echo esc_attr( $cat->term_id )?>"><?php echo esc_html( $cat->name )?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</div>
			</div>
			
			
		</div>
		
		<div class="aiassist-item-repeater">
			<button id="aiassist-addItemArticle">Добавить еще один список ключей для еще одной категории</button>
		</div>
		
		
		<div class="aiassist-option-item">Промт для генерации статей. Вместо переменой {key} подставляются ключевые фразы из списка.</div>
		
		<br /><br /><br />
		<div class="relative">
			<button type="button" class="aiassist-set-default-promts">Восстановить промпты по умолчанию</button>
		</div>
			
		<?php if( @$this->info->promts->lang ){ $lang_id = 0; ?>
			<div class="relative">
				<div class="aiassist-lang-promts-item">
					<label>Язык промптов: </label>
					<select class="aiassist-lang-promts">
						<?php foreach( $this->info->promts->lang as $k => $lang ){ ?>
							<?php
								if( @$this->steps['promts']['multi_lang'] == $k )
									$lang_id = (int) $k;
							?>
						
							<option value="<?php echo (int) $k ?>" <?php echo @$this->steps['promts']['multi_lang'] == $k ? 'selected' : '' ?> ><?php echo esc_html( $lang ) ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
		<?php } ?>

		<textarea class="aiassist-prom" id="aiassist-generation-prom"><?php echo esc_textarea( @$this->steps['promts']['multi'][ $lang_id ] ? trim( $this->steps['promts']['multi'][ $lang_id ] ) : @$this->info->promts->multi[ $lang_id ] )?></textarea>
		
		<div class="aiassist-option-item">
			<div>Промт: <input id="aiassist-title-prom-multi" class="aiassist-prom" value="<?php echo esc_attr( @$this->steps['promts']['multi_title'][ $lang_id ] ? $this->steps['promts']['multi_title'][ $lang_id ] : @$this->info->promts->multi_title[ $lang_id ] )?>" /></div>
			<div>Промт: <input id="aiassist-desc-prom-multi" class="aiassist-prom" value="<?php echo esc_attr( @$this->steps['promts']['multi_desc'][ $lang_id ] ? $this->steps['promts']['multi_desc'][ $lang_id ] : @$this->info->promts->multi_desc[ $lang_id ] )?>" /></div>
		</div>
		
		<div class="aiassist-option-item">
			Сколько статей генерировать и публиковать в сутки. Если оставить пустым, то будут сгенерированы и опубликованы статьи по всем заданным ключам в кратчайшие сроки
			<input type="number" class="aiassist-auto-options" id="publish-article-in-day" value="<?php echo (int) @$autoGen['publishInDay'] ?>" placeholder="0" />
		</div>
		
		<div class="aiassist-option-item">
			Какие сгенерировать изображения для статьи. Если оставить пустым, то статьи будут генерироваться без изображений
			
			<label class="aiassist-option-item">
				<input type="checkbox" class="aiassist-auto-options" id="aiassist-auto-images" <?php echo esc_attr( @$autoGen['images'] ? 'checked' : '' ) ?> /> Генерировать все возможные изображения для записи
			</label>
			
			<label class="aiassist-option-item">
				<input type="checkbox" class="aiassist-auto-options" id="aiassist-auto-thumb" <?php echo esc_attr( @$autoGen['thumb'] ? 'checked' : '' ) ?> /> Генерировать только изображение записи (миниатюру)
			</label>
			
			<label class="aiassist-option-item">
				<input type="checkbox" class="aiassist-auto-options" id="aiassist-auto-draft" <?php echo esc_attr( @$autoGen['draft'] ? 'checked' : '' ) ?> /> Отправлять сгенерированные статьи в черновик
			</label>
			
			<div>
				<div>Модель генерации текста</div>
				<select name="aiassist-text-model" class="aiassist-auto-options" id="aiassist-change-text-model">
					<option value="gpt3">GPT-4o mini</option>
					<option value="gpt4">GPT-4o</option>
				</select>
				<a href="https://aiwpwriter.com/prices/" target="_blank" class="aiassist-small">Посмотреть тарифы</a>
			</div>
			
			<div>
				<div>Модель генерации изображения</div>
				<select name="aiassist-image-model" class="aiassist-auto-options" id="aiassist-image-model">
					<option value="flux" <?php echo @$autoGen['imageModel'] == 'flux' ? 'selected' : '' ?>>FLUX schnell</option>
					<option value="dalle" <?php echo @$autoGen['imageModel'] == 'dalle' ? 'selected' : '' ?>>Dalle 3</option>
					<option value="midjourney" <?php echo @$autoGen['imageModel'] == 'midjourney' ? 'selected' : '' ?>>Midjourney</option>
				</select>
			</div>
			
		</div>
		
		<div class="aiassist-option-item">
			<button id="start-articles-generations" <?php echo @$autoGen['start'] ? 'disabled' : '' ?>>Запустить генерацию статей</button>
			<button id="stop-articles-generations" <?php echo ! @$autoGen['start'] ? 'disabled' : '' ?>>Остановить генерацию</button>
			<button id="clear-articles-generations">Очистить весь список ключевых фраз</button>
		</div>
		
		
		<?php if( ! @$this->options->token ){ ?>
			
			<span class="wpai-warning-limits">Вы не добавили API-ключ! Ключ после регистрации в плагине отправляется на почту. Зарегистрируйтесь и добавьте ключ из письма в специальное поле в настройках плагина и генерация станет доступна.</span>
		
		<?php } elseif( (int) @$this->info->limit < 1 ){ ?>
		
			<span class="wpai-warning-limits">Лимиты заканчились, для продолжения генерации пополните баланс!</span>
		
		<?php } else { ?>
		
			<div id="aiassist-generation-status">
				<?php 				
					if( @$autoGen['start'] ) 
						echo 'Идет процесс генерации статей, информация обновляется автоматически. Если этого не происходит, обновите страницу браузера, чтобы увидеть актуальный список сгенерированных статей.';
						
					elseif( ! @$autoGen['start'] && @$autoGen['count'] && @$autoGen['publish'] <= @$autoGen['count'] )
						echo 'Процесс генерации статей приостановлен.';
					
					elseif( @$autoGen['publish'] >= @$autoGen['count'] )
						echo 'Процесс генерации статей завершен.';
				?>
			</div>
		<?php } ?>
		
		<div class="aiassist-option-item <?php echo ! isset( $autoGen['start'] ) ? 'hidden' : ''?>" id="aiassist-generation-progress">Сгенерировано  <span id="aiassist-count-publish"><?php echo (int) @$autoGen['publish'] ?></span>  статей из <?php echo (int) @$autoGen['count'] ?></div>
		
		<div class="aiassist-articles-queue">
			<?php if( ! empty( $autoGen['articles'] ) ){ $queue = false; ?>
				<?php foreach( $autoGen['articles'] as $id => $article ){ ?>
					<?php if( isset( $article['post_id'] ) ){ ?>
						<?php $queue = false; ?>
						<div class="aiassist-article-queue"><a href="<?php echo get_edit_post_link( $article['post_id'] ) ?>" target="_blank"><?php echo esc_attr( $article['keywords'] ) ?></a> <span class="aiassist-queue-status">Сгенерирована</span></div>
					<?php } else { ?>
						<div class="aiassist-article-queue aiassist-queue"><div class="aiassist-article-item-close" data-key="<?php echo (int) $id ?>"></div> <span class="aiassist-queue-keyword"><?php echo esc_attr( $article['keywords'] ) ?></span> <span class="aiassist-queue-status"><?php echo ! $queue ? ( (int) @$article['check'] < 60 && @$this->info->limit > 1 ? 'Идет генерация' : 'Приостановлено' ) : 'В очереди' ?></span></div>
					<?php $queue = true; ?>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>
		
	</div>
	

</div>