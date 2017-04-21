jQuery(document).ready(function() {
  jQuery( ".likeme-container" ).each(function( index ) {

    var content_id = jQuery(this).data('content-id');
    var itemName = "likeme"+content_id;
    jQuery(this).find('.likeme-up').click(function () {
      if (localStorage.getItem(itemName)) {
        var vote = localStorage.getItem(itemName);
        likeme_vote(content_id, -vote);
      } else {
        var vote = jQuery(this).data('vote');
        likeme_vote(content_id, vote);
      }
    })
  });
});


function likeme_vote(ID, type) {
	const itemName = "likeme" + ID;
	
	const typeItemName = "likeme" + ID;
	localStorage.setItem(typeItemName, type);

	var data = {
		action: 'likeme_add_vote',
		postid: ID,
		type: type,
		nonce: likeme_ajax.nonce
	};
		
	jQuery.post(likeme_ajax.ajax_url, data, function(response) {		
		jQuery('#likeme-' + ID).find('.likeme-up strong').text(parseInt(jQuery('.likeme-up strong').text()) + type);
	});
}