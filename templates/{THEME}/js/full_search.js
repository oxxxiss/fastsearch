//================================================
 // Поиск на весь экран для Dle от oxxxiss
 //-----------------------------------------------
 // Автор: oxxxiss
 //-----------------------------------------------
 // Почта: oxxxiss69@mail.ru
 //-----------------------------------------------
 // skype: oxxxiss69
 //-----------------------------------------------
 // Назначение: Анимации и AJAX запросы хака
//================================================

	function poisk_open(){
		ShowLoading("");
		$(".search-to-window").css("opacity","1").css("pointerEvents","auto");
		HideLoading("");
	};
	
	document.onkeyup = function (e) {
		e = e || window.event;
		if (e.keyCode === 81 && e.ctrlKey) {		
			 poisk_open();			
		}
		return false;
	}
	
	$('#search-to-window-button-exit').click(function(){
		$(".search-to-window").css("opacity","").css("pointerEvents","");
		$(".h_btn").removeClass("open");
	});
	
	$("#searchLiser").bind("keydown",function(){
	
	var query =	$("#searchLiser").val();
	
		$.ajax({
		 type: "POST",
		 url: "/engine/ajax/search2.php",
		 data: ({query:query}),
		 dataType: "html",
		 success: function(data){
			$(".search-to-window-result").html(data);
		 }
		});
	
	});	
	
						
		$(function(){
			$(document).click(function(event) {
			if ($(event.target).closest("#searchLiser").length) return;
				$("input").removeClass('active');
				event.stopPropagation();
			  });
		});
		
		$("#searchLiser").click(function(e) {
		  e.preventDefault();
		  $("input").removeClass('active');
		  $(this).addClass('active');
		});