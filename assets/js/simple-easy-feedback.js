function ValidateSimpleEasyFeedback() {
	
	// Variable declaration
	var error = false;
	
	var name = document.forms["frontsimpleeasyfeedback"]["sf_name"].value;
	var subject = document.forms["frontsimpleeasyfeedback"]["sf_subject"].value;
	var email = document.forms["frontsimpleeasyfeedback"]["sf_email"].value;
	var comments = document.forms["frontsimpleeasyfeedback"]["sf_comments"].value;
	// Form field validation
	if (typeof name !== 'undefined') {
		if(name.length == 0){
			var error = true;
			jQuery('#name_error').fadeIn(500);
			return false;
		}else{
			jQuery('#name_error').fadeOut(500);
		}
	}
	
	if(typeof subject !== 'undefined') {
		if(subject.length == 0){
			var error = true;
			jQuery('#subject_error').fadeIn(500);
			return false;
		}else{
			jQuery('#subject_error').fadeOut(500);
		}
	}
	
	if(typeof email !== 'undefined') {
		var atpos = email.indexOf("@");
		var dotpos = email.lastIndexOf(".");
		if(email.length == 0) {
			var error = true;
			jQuery('#email_error').fadeIn(500);
			return false;
		} else if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length) {
			var error = true;
			jQuery('#email_error').text('Not a valid e-mail address.');
			jQuery('#email_error').fadeIn(500);
			return false;
		} else {
			jQuery('#email_error').fadeOut(500);
		}
	}
	
	if(typeof comments !== 'undefined') {		
		if(comments.length == 0){
			var error = true;
			jQuery('#comments_error').fadeIn(500);
			return false;
		}else{
			jQuery('#comments_error').fadeOut(500);
		}
	}																																																																							

}
