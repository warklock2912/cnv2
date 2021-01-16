var $j = jQuery.noConflict();
var arrType = ["jpg", "png", "gif", "jpeg"];

$j(document).ready(function () {
  // $j('#name').css('margin-left', '-80px')
  // $j('#status').css('margin-left', '-80px')
  $j('#magebuzz_input2').filer({
    limit: 20,
    maxSize: 5,
    extensions: arrType,
    changeInput: '<div class="jFiler-input-dragDrop"><div class="jFiler-input-inner")><div class="jFiler-input-icon"><i class=""></i></div><div class="jFiler-input-text" ><magebuzz-h3> Choose image(s) to upload </magebuzz-h3> </div></div></div>',
    showThumbs: true,
    appendTo: null,
    theme: "dragdropbox",
    templates: {
      box: '<ul class="jFiler-item-list"></ul>',
      item: '<li class="jFiler-item">\
                        <div class="jFiler-item-container">\
                            <div class="jFiler-item-inner">\
                                <div class="jFiler-item-thumb">\
                                    <div class="jFiler-item-status"></div>\
                                    <div class="jFiler-item-info">\
                                        <span class="jFiler-item-title"></span>\
                                    </div>\
                                    {{fi-image}}\
                                </div>\
                                <div class="jFiler-item-assets jFiler-row">\
                                    <ul class="list-inline pull-left">\
                                        <li>{{fi-progressBar}}</li>\
                                    </ul>\
                                    <ul class="list-inline pull-right">\
                                        <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                    </ul>\
                                </div>\
                            </div>\
                        </div>\
                    </li>',
      itemAppend: '<li class="jFiler-item">\
                        <div class="jFiler-item-container">\
                            <div class="jFiler-item-inner">\
                                <div class="jFiler-item-thumb">\
                                    <div class="jFiler-item-status"></div>\
                                    <div class="jFiler-item-info">\
                                        <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                    </div>\
                                    {{fi-image}}\
                                </div>\
                                <div class="jFiler-item-assets jFiler-row">\
                                    <ul class="list-inline pull-left">\
                                        <span class="jFiler-item-others">{{fi-icon}} {{fi-size2}}</span>\
                                    </ul>\
                                    <ul class="list-inline pull-right">\
                                        <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                    </ul>\
                                </div>\
                            </div>\
                        </div>\
                    </li>',
      progressBar: '<div class="bar"></div>',
      itemAppendToEnd: false,
      removeConfirmation: false,
      _selectors: {
        list: '.jFiler-item-list',
        item: '.jFiler-item',
        progressBar: '.bar',
        remove: '.jFiler-item-trash-action',
      }
    },
    dragDrop: {
      dragEnter: function () {
      },
      dragLeave: function () {
      },
      drop: function () {
      },
    },
    addMore: true,
    clipBoardPaste: true,
    excludeName: null,
    beforeShow: function () {
      return true
    },
    onSelect: function () {
    },
    afterShow: function () {
    },
    onRemove: function () {
    },
    onEmpty: function () {
    },
    captions: {
      button: "Choose Files",
      feedback: "Choose files To Upload",
      feedback2: "files were chosen",
      drop: "Drop file here to Upload",
      removeConfirmation: "Are you sure you want to remove this file?",
      errors: {
        filesLimit: "Only {{fi-limit}} files are allowed to be uploaded.",
        filesType: "Only Images are allowed to be uploaded.",
        filesSize: "{{fi-name}} is too large! Please upload file up to {{fi-maxSize}} MB.",
        filesSizeAll: "Files you've choosed are too large! Please upload files up to {{fi-maxSize}} MB."
      }
    }
  });
  $j('#magebuzz_input').filer({
    limit: 20,
    maxSize: 5,
    extensions: arrType,
    changeInput: '<div class="jFiler-input-dragDrop"><div class="jFiler-input-inner")><div class="jFiler-input-icon"><i class=""></i></div><div class="jFiler-input-text" ><magebuzz-h3> Choose image(s) to upload </magebuzz-h3> </div></div></div>',
    showThumbs: true,
    appendTo: null,
    theme: "dragdropbox",
    templates: {
      box: '<ul class="jFiler-item-list"></ul>',
      item: '<li class="jFiler-item">\
                        <div class="jFiler-item-container">\
                            <div class="jFiler-item-inner">\
                                <div class="jFiler-item-thumb">\
                                    <div class="jFiler-item-status"></div>\
                                    <div class="jFiler-item-info">\
                                        <span class="jFiler-item-title"></span>\
                                    </div>\
                                    {{fi-image}}\
                                </div>\
                                <div class="jFiler-item-assets jFiler-row">\
                                    <ul class="list-inline pull-left">\
                                        <li>{{fi-progressBar}}</li>\
                                    </ul>\
                                    <ul class="list-inline pull-right">\
                                        <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                    </ul>\
                                </div>\
                            </div>\
                        </div>\
                    </li>',
      itemAppend: '<li class="jFiler-item">\
                        <div class="jFiler-item-container">\
                            <div class="jFiler-item-inner">\
                                <div class="jFiler-item-thumb">\
                                    <div class="jFiler-item-status"></div>\
                                    <div class="jFiler-item-info">\
                                        <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                    </div>\
                                    {{fi-image}}\
                                </div>\
                                <div class="jFiler-item-assets jFiler-row">\
                                    <ul class="list-inline pull-left">\
                                        <span class="jFiler-item-others">{{fi-icon}} {{fi-size2}}</span>\
                                    </ul>\
                                    <ul class="list-inline pull-right">\
                                        <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                    </ul>\
                                </div>\
                            </div>\
                        </div>\
                    </li>',
      progressBar: '<div class="bar"></div>',
      itemAppendToEnd: false,
      removeConfirmation: false,
      _selectors: {
        list: '.jFiler-item-list',
        item: '.jFiler-item',
        progressBar: '.bar',
        remove: '.jFiler-item-trash-action',
      }
    },
    dragDrop: {
      dragEnter: function () {
      },
      dragLeave: function () {
      },
      drop: function () {
      },
    },
    addMore: true,
    clipBoardPaste: true,
    excludeName: null,
    beforeShow: function () {
      return true
    },
    onSelect: function () {
    },
    afterShow: function () {
    },
    onRemove: function () {
    },
    onEmpty: function () {
    },
    captions: {
      button: "Choose Files",
      feedback: "Choose files To Upload",
      feedback2: "files were chosen",
      drop: "Drop file here to Upload",
      removeConfirmation: "Are you sure you want to remove this file?",
      errors: {
        filesLimit: "Only {{fi-limit}} files are allowed to be uploaded.",
        filesType: "Only Images are allowed to be uploaded.",
        filesSize: "{{fi-name}} is too large! Please upload file up to {{fi-maxSize}} MB.",
        filesSizeAll: "Files you've choosed are too large! Please upload files up to {{fi-maxSize}} MB."
      }
    }
  });
});
function setImage(images, url) {
  for (i = 0; i < images.length; i++) {
    $j('#jFiler_item_list_images').append(' <li style="" data-jfiler-index="0" class="jFiler-item"><div id="' + images[i] + '" class="jFiler-item-container">  <div class="jFiler-item-thumb">          <div class="jFiler-item-status"></div>        \n\
\n\
 <div class="jFiler-item-info">\n\
 <span class="jFiler-item-title"></span> \n\
</div>    \n\
 <div class="jFiler-item-thumb-image"><a  ><img data="' + images[i] + '" id="old_' + i + '" src="' + url + images[i] + '" draggable="false"  ></a> </div>                              \n\
\n\
 </div>\n\
<div class="jFiler-item-assets jFiler-row"> \n\
<ul class="list-inline pull-left"><li></li>  </ul>  \n\
<ul class="list-inline pull-right"><li><a class="icon-jfi-trash jFiler-item-trash-action "  onclick=\"javascript:removeImage(\'' + images[i] + '\')\"></a></li></ul>   \n\
\n\
 </div> </div> </div></li> ');
  }

}
function setListImage(images, url) {
  for (i = 0; i < images.length; i++) {
    $j('#jFiler_item_postlist_images').append(' <li style="" data-jfiler-index="0" class="jFiler-item"><div id="list-' + images[i] + '" class="jFiler-item-container">  <div class="jFiler-item-thumb">          <div class="jFiler-item-status"></div>        \n\
\n\
 <div class="jFiler-item-info">\n\
 <span class="jFiler-item-title"></span> \n\
</div>    \n\
 <div class="jFiler-item-thumb-image"><a  ><img data="' + images[i] + '" id="old_list_' + i + '" src="' + url + images[i] + '" draggable="false"  ></a> </div>                              \n\
\n\
 </div>\n\
<div class="jFiler-item-assets jFiler-row"> \n\
<ul class="list-inline pull-left"><li></li>  </ul>  \n\
<ul class="list-inline pull-right"><li><a class="icon-jfi-trash jFiler-item-trash-action "  onclick=\"javascript:removeListImage(\'' + images[i] + '\')\"></a></li></ul>   \n\
\n\
 </div> </div> </div></li> ');
  }
}
function setTemplate(id) {

  src = document.getElementById(id).src;
  $j('#giftcard_image_background img').remove();
  $j('#giftcard_image_background').append("<img width=100%; height=300;   id='magebuzz-image' src=" + src + " />")

  document.getElementById('background').value = document.getElementById(id).getAttribute('data');

}
function setTemplateNew(id) {
  src = document.getElementById(id).children[0].src;
  $j('#giftcard_image_background img').remove();
  $j('#giftcard_image_background').append("<img class='magebuzz-img-responsive magebuzz-img-print' id='magebuzz-image' src=" + src + " />")
  document.getElementById('background').value = document.getElementById(id).getAttribute('data');
}