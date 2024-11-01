/*
 * Image preview script 
 * powered by jQuery (http://www.jquery.com)
 * 
 * written by Alen Grakalic (http://cssglobe.com)
 * 
 * for more info visit http://cssglobe.com/post/1695/easiest-tooltip-and-image-preview-using-jquery
 *
 */
this.imagePreview = function(){
	// these 2 variable determine popup's distance from the cursor
	xOffset = 10;
	yOffset = 30;

	jQuery("a.preview").hover(function(e){
		this.t = this.title;
		this.title = "";	
		var c = (this.t != "") ? "<br/>" + this.t : "";
		jQuery("body").append("<p id='preview'><img src='"+ this.href +"' alt='Image preview' width='250px'/>"+ c +"</p>"); 
		jQuery("#preview")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");
    },
	function(){
		this.title = this.t;
		jQuery("#preview").remove();
    });	
	jQuery("a.preview").mousemove(function(e){
		jQuery("#preview")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px");
	});
};


jQuery(document).ready(function () {
	/* Display errors */
	jQuery('.alert-message').each(function(){ 
		var t = setTimeout("jQuery('.alert-message').hide('slow')", 2500);
	});
	jQuery('.fade').each(function(){ 
		var t = setTimeout("jQuery('.fade').hide('slow')", 5000);
	});
	
	/* Display big image on mouse hover */
	imagePreview();
});
