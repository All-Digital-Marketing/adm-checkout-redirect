jQuery(($) => {

  let url = new URL(location.href);
  let order_key = url.searchParams.get("key");

  $.ajax({
    type: "post",
    dataType: "json",
    url: adm.ajax_url,
    data: {
      action: 'adm_orr',
      order_key: order_key
    },
    success: function (res) {
      console.log(res);
      if (res?.redirect && res.url) {
        if (location.href !== res.url) {
          setTimeout(() => {
            if (res.key) {
              location.href = res.url + '?key=' + order_key
            } else {
              location.href = res.url
            }
          }, 3000)
        }
      }
    }
  });

})