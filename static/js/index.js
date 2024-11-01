jQuery(document).on("click", ".rating-combo .rating-toggle",function(e) {
	var _self = jQuery(this);
	if ( _self.parent().hasClass('is-active')){
		 _self.parent().removeClass('is-active')
	} else {
		 _self.parent().addClass('is-active')
	}
	return false;
});

jQuery(document).on("click", ".rating-combo ul li",function() {
	var _self = jQuery(this),
	score = 5 - _self.index(),
	id = _self.parent().parent().data("post-id"),
	rateHolder = jQuery(this).parent().parent().parent(),
	history = rateHolder.html(),
	ajax_data = {
		action: "add_post_star",
		id: id,
		score: score
	};
	rateHolder.html('loading..');
	jQuery.ajax({
			url: fancyratings_ajax_url,
			type: "POST",
			data: ajax_data,
			dataType: "json",
			success: function(data) {
				if (data.status == 200) {

					var item = new Object();
					item = data.data;
					jQuery(rateHolder).html('<div class="post-rate"><span class="rating-stars" title="评分 ' + item.average + ', 满分 5 星" style="width:' + item.percent + '%"></span></div><div class="piao">' + item.raters + ' 票</div>');
								
				} else {

					jQuery(rateHolder).html(history);
					console.log(data.status);

				}
			}
		});

	return false;
});