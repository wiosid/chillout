(function($) {
    $.fn.zfTable = function(url) {
        
        var initialized = false;
        
        function init($obj) {
            ajax($obj);
        }
        function ajax($obj){
             $obj.prepend('<div class="processing" style=""></div>')
             $.ajax({
                url:  url,
                data: $obj.find(':input').serialize(),
                type: 'POST',
                success: function(data) {
                    $obj.html('');
                    $obj.html(data);
                    initNavigation($obj);
                    $obj.find('.processing').hide();
                },
                dataType: 'html'
            });
        }
        function initNavigation($obj){
            $obj.find('table th').on('click',function(e){
                $obj.find('input[name="zfTableColumn"]').val($(this).data('column'));
                $obj.find('input[name="zfTableOrder"]').val($(this).data('order'));
                ajax($obj);
            });
            $obj.find('.pagination').find('a').on('click',function(e){
                $obj.find('input[name="zfTablePage"]').val($(this).data('page'));
                e.preventDefault();
                ajax($obj);
            });
            $obj.find('.itemPerPage').on('change',function(e){
                $obj.find('input[name="zfTableItemPerPage"]').val($(this).val());
                ajax($obj);
            });
            $obj.find('.quick-search').on('keypress',function(e){
               if(e.which === 13) {
                   e.preventDefault();
                   $obj.find('input[name="zfTableQuickSearch"]').val($(this).val());
                   ajax($obj);
               }
            });
        }
        return this.each(function() {
           var $this = $( this );
           if(!initialized){
              init($this); 
           }  
          
        });
    }
})(jQuery); 