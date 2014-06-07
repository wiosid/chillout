// JavaScript Document

$(document).ready(function(){	
	
  
// responsive navigation js for 768px
var wid = $(window).width();

//  navigation start

$(".nav li").click(function(e){
	e.preventDefault();
	if($(this).hasClass('active'))
	{
		$(this).removeClass('active');
		$(this).children("ul").slideUp();
	}
	else
	{	
		$(".nav li").removeClass('active');		
		$(this).addClass('active');
		$(".nav li ul").slideUp();
		$(this).children("ul").slideDown();
	}
	$(".container").css("min-height","650px")
});

$(".nav li ul li a").click(function(e){
	$(".nav li ul li a").removeClass('this');
	$(this).toggleClass('this');	
	e.stopPropagation();
});
//  navigation end

// user area box
$(".user-area .box").click(function(){
	$('.info').slideToggle('fast');	
	
});

$(".info a").click(function(e){
	e.stopPropagation();
	
});

// datepicker
   $(function() {
        $( "#datepicker, #datepicker2" ).datepicker({ 
		defaultDate: +7,
		showOtherMonths:true,
		autoSize: true,
		appendText: '(dd-mm-yyyy)',
		dateFormat: 'dd-mm-yy'
	});
    });
	
	
// ----------------tabs	--------------

$('.tabcontent').hide();
$('.tabcontent:first').show();
$('.tabs a:first').addClass('active');

$(".tabs a").click(function(){
	$(".tabs a").removeClass('active');	
	$(this).addClass('active');		
	var id = $(this).attr('href');
	$('.tabcontent').hide();	
	$(id).show();	
	return false;	
});

// ----------------add more category start-------------
var $addcat = {
add : function(){
 $(".addcatBtn").click(function(){
	//alert("gi");
	$(this).closest('div').find('input').show();
	$(this).closest('div').find('span').show();
	$("input.addInLast").hide();
	$(this).hide();

});
},
remove : function(){
 $(".addMore .cancel").click(function(){	
	$(this).closest('div').find('input').hide();
	$(this).closest('div').find('a').show();
	$("input.addInLast").show();
	$(this).hide();

});
}
}
$addcat.add();
$addcat.remove();
// ----------------add more category end-------------


});




