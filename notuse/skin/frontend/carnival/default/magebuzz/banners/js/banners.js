
function bannerClicks(click_url, id_banner, block_id) {
  new Ajax.Request(click_url, {
    method: 'post',
    parameters: {banner_id: id_banner, block_id: block_id},
    onFailure: '',
    onSuccess: ''
  });
}