/**
 * todo: make it possible to count words instead of characters
 *
 **/
jQuery.fn.textLimiter = function(){
    return this.each(function(){
        if(typeof(nr) == "undefined") {
            nr = 0;
        }
        var counter_id = 'counter' +nr;
        var max = this.getAttribute('maxLength');

        var html_counter = '<div id="' +counter_id + '" class="counter"><span>' +max+ '</span></div>';
        $(this).after(html_counter);
        var jquery_pattern = '#' +counter_id +' > span';
        this.relatedElement = $(jquery_pattern)[0];
        nr++;

        var checker = function() {
            var maxLength     = this.getAttribute('maxLength');
            var currentLength = this.value.length;
            if(currentLength >= maxLength) {
                this.relatedElement.className = 'toomuch';
                this.value = this.value.substring(0, maxLength);
            } else {
                this.relatedElement.className = '';
            }
            var left_over = maxLength - currentLength;
            this.relatedElement.firstChild.nodeValue = left_over < 0 ? 0 : left_over;
        };

        $(this).bind('keyup', checker);
    });
};
