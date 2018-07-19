<?php

//	This is FormBuilder's configuration file.
//	For more information and examples, go to
//	http://www.zinescripts.com/tutorials/formbuilder-configuration/

$config = array();

//	Enter your name and email, where you wish to receive the submissions.
//	You can specify more than one recipient by adding them to the array.

$config['recipient']	= array(
	'Your Name'		=> 'k.kaplan1@earthlink.net'
);


//	Customize the header text. Will also appear in the received email.
$config['headerText']	= '';

//	Choose a color theme [light/dark]
$config['colorTheme']	= 'light';

//	Enable captcha in the form [true/false]
$config['captcha']		= false;

//	This message is displayed after the form has been submitted successfully.
$config['thankYouMessage']	=	'
	<h1>Thank you!</h1>
	<h2>I will get back to you shortly.</h2>
';


//	An array with your form fields. Follow the guide at 
//	http://www.zinescripts.com/tutorials/formbuilder-form-fields/
//	for more information and examples.

$config['fields']	= array(
	array(
		'label' 	=> 'Your name',
		'type'		=> 'textField',
		'required'	=> true,
		'errorText'	=> 'Please enter a valid name.',
		'fromName'	=> true
	),
	array(
		'label' 	=> 'Email',
		'type'		=> 'textField',
		'required'	=> true,
		'validation'=> 'email',
		'errorText'	=> 'Please enter a valid email.',
		'fromEmail'	=> true
	),
	array(
		'label'		=> 'Subject',
		'type'		=> 'select',
		'required'  => true,
		'default'	=> 0,
		'items'		=> array('Please Choose','Marketing/Advertising','Public Relations','Photography','General Inquiry'),
	),
	array(
		'label' 	=> 'Message',
		'type'		=> 'textArea',
		'required'	=> false,
		'errorText'	=> 'You are missing a message.'
	)
);


//	A more complex example for a "Request a Quote" form.
/*

$config['fields']	= array(
	array(
		'label' 	=> 'Your name',
		'type'		=> 'textField',
		'required'	=> true,
		'errorText'	=> 'Please enter a valid name.',
		'fromName'	=> true
	),
	array(
		'label' 	=> 'Email',
		'type'		=> 'textField',
		'required'	=> true,
		'validation'=> 'email',
		'errorText'	=> 'Please enter a valid email.',
		'fromEmail'	=> true
	),
	array(
		'label'		=> 'Project Type',
		'type'		=> 'select',
		'default'	=> 0,
		'value'		=> 0,
		'items'		=> array('Please Select','Design','Illustration','Development','Marketing','SEO'),
		'required'	=> true,
		'errorText'	=> 'Please choose the type for your project.'
	),
	array(
		'label'		=> 'Time Frame',
		'type'		=> 'select',
		'items'		=> array('Choose a time frame','A Month','Two to three weeks','A day','RIGHT NOW!'),
	),
	array(
		'label' 	=> 'Description',
		'type'		=> 'textArea',
		'required'	=> true,
		'errorText'	=> 'You should provide a description of your project.'
	),
	array(
		'label' 	=> 'Payment',
		'type'		=> 'radio',
		'items'		=> array('Upfront','Upon Completion'),
		'required'	=> true,
		'errorText'	=> 'Please choose a payment scheme.'
	),
	array(
		'label' 	=> 'Support Needed',
		'type'		=> 'checkBox'
	)
);

*/
?>