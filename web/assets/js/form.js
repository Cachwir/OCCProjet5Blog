
function process_form($form, transition, preprocessData, postRenderTemplate) {

	postRenderTemplate = postRenderTemplate || $.noop;

	handle_qtips($form);

	$form.on('submit', function(evt, moreData){
		evt.preventDefault();
		var data = $form.serializeArray();

		if(moreData) {
			for (var k in moreData) {
				var v = moreData[k];
				data.push({name:k, value:v});
			}
		}

		if(preprocessData) {
			preprocessData(data);
		}

		var url = $form.attr('action');
		ajax_load_content(url, transition, postRenderTemplate, data, 'post');

		return false;
	});

}

function handle_qtips($form) {
	$('label.error, label.success', $form).each(function(){
		var $message = $(this);
		var type = $message.hasClass('error') ? 'error' : 'success';
		var color = type == 'error' ? 'red' : 'green';

		var $el = $('#'+$message.attr('for'), $form);
		if(!$el.length) {
			$el = $('input[name="'+$message.attr('for')+'"], select[name="'+$message.attr('for')+'"]', $form).first();
		}
		if(!$el.length) {
			$el = null
		}

		var $w = $el && $el.data('widget'); // deport error on the widget

		if(!$el || ($el.attr('type') == 'hidden' && !$w)) {
			$message.remove();

			// do something to notify the user about global errors
			// notify = new PNotify({
			// 	title: 'Notification',
			// 	text: $message.text(),
			// 	type: "danger",
			// 	addclass: 'stack-topleft',
			// 	delay: 20000
			// });
		} else {
			if(!$w) {
				$w = $el; // else on the input itself
			}
			$w.addClass(type);
			if (type == 'error') {
				$w.attr('aria-invalid', 'true');
			}
			//$el.after($message);
			$message.detach();
			$w.qtip({
				content:{
					text: $message
				},
				show: true,
				hide: 'change',
				position: $el.is(':checkbox') ? {my:'top left', at:'bottom right', adjust:{method:'shift'}, container:$('#page')} : {my:'top center', at:'bottom center', adjust:{method:'shift'}, container:$('#page')},
				style: {
					classes:'qtip-'+color+' qtip-rounded qtip-shadow'
				}
			});

			function dismissTooltip() {
				$w.qtip('api').destroy();
				$w.removeClass('error success');
				$el.off('change.once', dismissTooltip); // one work only once for all widgets, dunno why
			}

			if($el.is(':checkbox') || $el.is(':radio')) {
				$(':input[name="'+$el.attr('name')+'"]').on('change.once', dismissTooltip);
			} else {
				$el.on('change.once', dismissTooltip);
			}
//			setTimeout(function(){
//				$el.qtip('api').show(); // fuck it
//			}, 1);
		}

	});
}

function ajax_load_content(url, transition, postRenderTemplate, data, method) {
	method = method || 'get';
	data = data || {};
	postRenderTemplate = postRenderTemplate || $.noop;

	var requester;
	if (method == 'get') {
		data.mode = 'ajax'; // trigger an ajax response from the server
		requester = $.get;
	} else {
		requester = $.post;
	}

	var $body = $('body');

	requester(url, data, function(data, textStatus, xhr) {
		// destroy all qtips
		$('[data-hasqtip]').qtip('destroy', true);

		var current_page = $body.attr('data-page');
		
		if(transition === "nope")
		{
			postRenderTemplate(data);
		}
		else if((current_page == data.page && $(data.content).find('label.error').length) || !transition)
		{
			$('#content').replaceWith(data.content);
			postRenderTemplate($body.attr('data-page'));
		}
		else
		{ // transition
			if(false && matchMedia('(max-width: 641px)').matches) { // generic mobile transition

				$body.addClass(data.page).attr('data-page', data.page);
				var $current = $('#content');
				var h1 = $current.height();
				var w1 = $current.width();
				var $new = $($.parseHTML(data.content, true)).filter('#content');
				var $container = $('#container').css('overflow', 'hidden');
				$new.css({
					position:'absolute',
					left: w1,
					width:w1,
					top:0
				});
				$current.after($new);
				var h2 = $new.height();
				$container.css('height', h1).animate({height:h2}, 990);
				$current.css({position:'relative', left:0, width:w1}).animate({left:-w1}, 1000);
				$new.animate({left:0}, 1000, function(){
					$current.remove();
					$new.css('position', '');
					$container.css('height', '');
					$body.removeClass(current_page);
					postRenderTemplate(data.page);
					$(window).trigger('resize'); // trigger the eventual adaptation JS
				});

			} else {
				var bodyClass = null;
				// console.log(data);
				if (data.body_class) {
					bodyClass = data.body_class;
				}
				transition(data.content, function(){ // user transition

					$body.removeClass(current_page).addClass(data.page);
					$body.attr('data-page', data.page);
					
					if ($body.hasClass('connected') && !data.connected) {
						$body.removeClass('connected')
					} else if (!$body.hasClass('connected') && data.connected) {
						$body.addClass('connected')
					}
				}, postRenderTemplate.bind(data.page), bodyClass, current_page, data.page);
			}
		}
	}, 'json');
}

function transition(content, transCssBefore, transCssAfter, bodyClass){
    $('body').trigger('OnTransitionStart'); // use this if you want to trigger before the content is replaced
	$('#content').css('opacity', 1); // .css('overflow', 'hidden') // 2016.04.21: overflow désactivé car provoque un bug lors de la transition sur certaines applis, à réactiver si des problèmes surviennent sur d'autres jeux
	$('#content').animate({opacity:0}, 500, function(){
		transCssBefore && transCssBefore();
		$('#content').replaceWith(content).promise().done(function() {
			$('body').trigger('OnContentReplacement'); // use this if you want to trigger on the exact moment the content is replaced
		});
		$('#content').css('opacity', 0);
		if (bodyClass !== null) {
			$('body').removeClass();
			$('body').addClass(bodyClass);
		}
		$('#content').animate({opacity:1}, 500, function(){ // .css('overflow', 'hidden') // 2016.04.21: overflow désactivé car provoque un bug lors de la transition sur certaines appli, à réactiver si des problèmes surviennent sur d'autres jeux
			$('#content').css('overflow', '');
            $('body').trigger('OnTransitionDone'); // use this if you want to trigger after the content is replaced
		});
		transCssAfter && transCssAfter();
	});
}

function generic_process_form($form) {
	if (!$form) {
		$form = $('form');
	}
	$form.each(function () {
		var $form = $(this);
		process_form($form, transition);
	});
}

function ajax_links() {
	$('a.ajax-link').not('.linked').each(function () {
		var $a = $(this);
		$a.on('click', function (evt) {
			// $('.lean-overlay').fadeOut(); // cleanup modal stuff
			evt.preventDefault();
			// console.log("loading");
			ajax_load_content($a.attr('href'), transition);
			return false;
		});
		$a.addClass('linked');
	});
}



